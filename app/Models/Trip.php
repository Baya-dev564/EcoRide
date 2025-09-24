<?php
/**
 * Modèle Trip avec système de modération admin + workflow de notation
 * Les trajets créés sont en attente de validation + Gestion des statuts pour notation
 * Méthodes pour le workflow de notation post-trajet et correction du bug d'affichage de l'heure
 */

class Trip
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Je crée un nouveau trajet en attente de modération admin
     */
    public function creerTrajet($data)
    {
        // Je valide les données avant insertion
        $validation = $this->validerDonneesTrajet($data);
        if (!$validation['valide']) {
            return ['succes' => false, 'erreurs' => $validation['erreurs']];
        }
        
        // Je vérifie les prérequis utilisateur
        if (!$this->utilisateurAPermis($data['conducteur_id'])) {
            return ['succes' => false, 'erreurs' => ['Vous devez avoir le permis de conduire pour proposer un trajet.']];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // J'utilise la distance calculée par le contrôleur ou je la calcule
            $distance = $data['distance_km'] ?? $this->calculerDistanceEstimative($data['lieu_depart'], $data['lieu_arrivee']);
            $prixCalcule = $this->calculerPrix($distance, $data['vehicule_electrique'] ?? false);
            
            // Je requête SQL avec les nouvelles colonnes GPS + statut pour notation
            $sql = "INSERT INTO trajets (
                        conducteur_id, vehicule_id, lieu_depart, code_postal_depart, 
                        lieu_arrivee, code_postal_arrivee, date_depart, heure_depart,
                        places, prix, commission, vehicule_electrique, distance_km, 
                        statut, commentaire, depart_latitude, depart_longitude, 
                        arrivee_latitude, arrivee_longitude, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ouvert', ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $resultat = $stmt->execute([
                $data['conducteur_id'],
                $data['vehicule_id'] ?? null,
                $data['lieu_depart'],
                $data['code_postal_depart'],
                $data['lieu_arrivee'],
                $data['code_postal_arrivee'],
                $data['date_depart'],
                $data['heure_depart'],
                $data['places'],
                $prixCalcule,
                2.00, // Commission fixe EcoRide
                $data['vehicule_electrique'] ? 1 : 0,
                $distance,
                $data['commentaire'] ?? null,
                // Nouvelles colonnes GPS
                $data['depart_latitude'] ?? null,
                $data['depart_longitude'] ?? null,
                $data['arrivee_latitude'] ?? null,
                $data['arrivee_longitude'] ?? null
            ]);
            
            if ($resultat) {
                $trajetId = $this->pdo->lastInsertId();
                
                // J'ajoute le statut_moderation après avec UPDATE
                try {
                    $sqlUpdate = "UPDATE trajets SET statut_moderation = 'en_attente' WHERE id = ?";
                    $stmtUpdate = $this->pdo->prepare($sqlUpdate);
                    $stmtUpdate->execute([$trajetId]);
                } catch (Exception $e) {
                    // Si ça échoue, on continue quand même
                    error_log("Statut modération non ajouté : " . $e->getMessage());
                }
                
                $this->pdo->commit();
                
                return [
                    'succes' => true,
                    'message' => 'Trajet créé avec succès ! Il sera visible après validation par un administrateur.',
                    'trajet_id' => $trajetId,
                    'prix_calcule' => $prixCalcule,
                    'distance' => $distance
                ];
            } else {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreurs' => ['Erreur lors de la création du trajet.']];
            }
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur création trajet : " . $e->getMessage());
            return ['succes' => false, 'erreurs' => ['Erreur technique : ' . $e->getMessage()]];
        }
    }

    /**
     * Je marque un trajet comme terminé
     * Permet au conducteur de déclencher le workflow de notation
     */
    public function marquerCommeTermine($trajetId)
    {
        try {
            // Je vérifie que le trajet existe et est en cours
            $sql = "SELECT statut FROM trajets WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$trajetId]);
            $trajet = $stmt->fetch();
            
            if (!$trajet) {
                return ['succes' => false, 'erreur' => 'Trajet non trouvé.'];
            }
            
            if ($trajet['statut'] === 'termine') {
                return ['succes' => false, 'erreur' => 'Ce trajet est déjà terminé.'];
            }
            
            if ($trajet['statut'] === 'annule') {
                return ['succes' => false, 'erreur' => 'Ce trajet est annulé.'];
            }
            
            // Je marque le trajet comme terminé
            $sqlUpdate = "UPDATE trajets SET statut = 'termine', date_fin = NOW() WHERE id = ?";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $resultat = $stmtUpdate->execute([$trajetId]);
            
            if ($resultat && $stmtUpdate->rowCount() > 0) {
                return ['succes' => true, 'message' => 'Trajet marqué comme terminé avec succès.'];
            } else {
                return ['succes' => false, 'erreur' => 'Erreur lors de la mise à jour du statut.'];
            }
            
        } catch (Exception $e) {
            error_log("Erreur marquerCommeTermine : " . $e->getMessage());
            return ['succes' => false, 'erreur' => 'Erreur technique lors de la terminaison.'];
        }
    }

    /**
     * Je récupère les trajets qu'un utilisateur peut noter
     * Trajets terminés où l'utilisateur était conducteur ou passager et n'a pas encore noté
     */
    public function getTrajetsANoter($userId)
    {
        try {
            $sql = "SELECT DISTINCT t.*, 
                           u.pseudo as conducteur_pseudo,
                           u.nom as conducteur_nom,
                           u.prenom as conducteur_prenom,
                           CASE 
                               WHEN t.conducteur_id = ? THEN 'conducteur'
                               ELSE 'passager'
                           END as role_utilisateur
                    FROM trajets t
                    JOIN utilisateurs u ON t.conducteur_id = u.id
                    LEFT JOIN reservations r ON t.id = r.trajet_id 
                        AND r.passager_id = ? 
                        AND r.statut = 'confirme'
                    WHERE t.statut = 'termine' 
                    AND (t.conducteur_id = ? OR r.passager_id = ?)
                    AND t.id NOT IN (
                        SELECT DISTINCT avis.trajet_id 
                        FROM avis_mongodb avis 
                        WHERE avis.utilisateur_id = ?
                    )
                    ORDER BY t.date_depart DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // J'enrichis les données pour chaque trajet
            foreach ($trajets as &$trajet) {
                // Correction bug heure : je combine date + heure correctement
                $trajet = $this->enrichirDonneesTrajet($trajet);
                $trajet['peut_noter'] = true;
                
                // Je détermine qui il peut noter
                if ($trajet['role_utilisateur'] === 'conducteur') {
                    // Le conducteur peut noter ses passagers
                    $trajet['a_noter'] = 'passagers';
                    $trajet['nb_a_noter'] = $this->getNombrePassagers($trajet['id']);
                } else {
                    // Le passager peut noter le conducteur
                    $trajet['a_noter'] = 'conducteur';
                    $trajet['nb_a_noter'] = 1;
                }
            }
            
            return $trajets;
            
        } catch (Exception $e) {
            error_log("Erreur getTrajetsANoter : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Je compte le nombre de passagers d'un trajet
     * Utile pour savoir combien d'avis le conducteur doit donner
     */
    private function getNombrePassagers($trajetId)
    {
        try {
            $sql = "SELECT COUNT(DISTINCT passager_id) 
                    FROM reservations 
                    WHERE trajet_id = ? AND statut = 'confirme'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$trajetId]);
            
            return (int)$stmt->fetchColumn();
            
        } catch (Exception $e) {
            error_log("Erreur getNombrePassagers : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Je récupère les trajets terminés récemment (pour notifications)
     * Utile pour envoyer des rappels de notation
     */
    public function getTrajetsTerminesRecents($jours = 7)
    {
        try {
            $sql = "SELECT t.*, 
                           u.pseudo as conducteur_pseudo,
                           u.email as conducteur_email,
                           COUNT(r.id) as nb_passagers
                    FROM trajets t
                    JOIN utilisateurs u ON t.conducteur_id = u.id
                    LEFT JOIN reservations r ON t.id = r.trajet_id AND r.statut = 'confirme'
                    WHERE t.statut = 'termine' 
                    AND t.date_fin >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    AND t.date_fin <= DATE_SUB(NOW(), INTERVAL 1 DAY)
                    GROUP BY t.id
                    ORDER BY t.date_fin DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$jours]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur getTrajetsTerminesRecents : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Je vérifie si un trajet peut être noté par un utilisateur
     * Vérifie que le trajet est terminé et que l'utilisateur y a participé
     */
    public function peutNoterTrajet($trajetId, $userId)
    {
        try {
            // Je vérifie le statut du trajet
            $sql = "SELECT t.statut, t.conducteur_id,
                           COUNT(r.id) as est_passager
                    FROM trajets t
                    LEFT JOIN reservations r ON t.id = r.trajet_id 
                        AND r.passager_id = ? 
                        AND r.statut = 'confirme'
                    WHERE t.id = ?
                    GROUP BY t.id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $trajetId]);
            $trajetInfo = $stmt->fetch();
            
            if (!$trajetInfo) {
                return ['peut_noter' => false, 'raison' => 'Trajet non trouvé'];
            }
            
            if ($trajetInfo['statut'] !== 'termine') {
                return ['peut_noter' => false, 'raison' => 'Trajet non terminé'];
            }
            
            $estConducteur = ($trajetInfo['conducteur_id'] == $userId);
            $estPassager = ($trajetInfo['est_passager'] > 0);
            
            if (!$estConducteur && !$estPassager) {
                return ['peut_noter' => false, 'raison' => 'Vous n\'avez pas participé à ce trajet'];
            }
            
            return [
                'peut_noter' => true,
                'role' => $estConducteur ? 'conducteur' : 'passager',
                'conducteur_id' => $trajetInfo['conducteur_id']
            ];
            
        } catch (Exception $e) {
            error_log("Erreur peutNoterTrajet : " . $e->getMessage());
            return ['peut_noter' => false, 'raison' => 'Erreur technique'];
        }
    }
    
    /**
     * Je recherche seulement les trajets validés par l'admin
     */
    public function rechercherTrajets($criteres = [], $page = 1, $limit = 10)
    {
        try {
            // Modification : ne montre que les trajets validés
            $sql = "SELECT t.*, 
                           u.pseudo as conducteur_pseudo, 
                           u.nom as conducteur_nom,
                           u.prenom as conducteur_prenom, 
                           u.note as conducteur_note,
                           u.photo_profil as conducteur_photo,
                           v.marque as vehicule_marque, 
                           v.modele as vehicule_modele,
                           v.plaque_immatriculation as vehicule_immatriculation,
                           v.couleur as vehicule_couleur,
                           v.electrique as vehicule_electrique_detail,
                           t.places as places_disponibles
                    FROM trajets t
                    JOIN utilisateurs u ON t.conducteur_id = u.id
                    LEFT JOIN vehicules v ON t.vehicule_id = v.id
                    WHERE t.statut = 'ouvert' 
                    AND t.statut_moderation = 'valide' 
                    AND DATE(t.date_depart) >= CURDATE()";
            
            $params = [];
            
            // Je garde tous les filtres existants
            if (!empty($criteres['lieu_depart'])) {
                $sql .= " AND (t.lieu_depart LIKE ? OR t.code_postal_depart LIKE ?)";
                $params[] = '%' . $criteres['lieu_depart'] . '%';
                $params[] = '%' . $criteres['lieu_depart'] . '%';
            }
            
            if (!empty($criteres['lieu_arrivee'])) {
                $sql .= " AND (t.lieu_arrivee LIKE ? OR t.code_postal_arrivee LIKE ?)";
                $params[] = '%' . $criteres['lieu_arrivee'] . '%';
                $params[] = '%' . $criteres['lieu_arrivee'] . '%';
            }
            
            if (!empty($criteres['date_depart'])) {
                $sql .= " AND DATE(t.date_depart) = ?";
                $params[] = $criteres['date_depart'];
            }
            
            if (!empty($criteres['vehicule_electrique'])) {
                $sql .= " AND t.vehicule_electrique = 1";
            }
            
            // Je garde la pagination
            $offset = ($page - 1) * $limit;
            $sql .= " ORDER BY t.date_depart ASC LIMIT $limit OFFSET $offset";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Correction bug heure : j'utilise enrichirDonneesTrajet
            foreach ($trajets as &$trajet) {
                $trajet = $this->enrichirDonneesTrajet($trajet);
            }
            
            return [
                'succes' => true,
                'trajets' => $trajets,
                'pagination' => [
                    'page_actuelle' => $page,
                    'total_trajets' => $this->compterTrajets($criteres)
                ]
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur recherche trajets : " . $e->getMessage());
            return ['succes' => false, 'erreur' => 'Erreur lors de la recherche.'];
        }
    }
    
    /**
     * Je récupère tous les trajets de l'utilisateur avec statut de modération
     * Amélioration : avec information sur possibilité de terminer/noter
     */
    public function getTrajetsUtilisateur($userId)
    {
        try {
            // Modification : j'ajoute le statut de modération + infos notation
            $sql = "SELECT t.*, 
                           COUNT(r.id) as nb_reservations,
                           SUM(r.nb_places) as places_reservees,
                           v.marque as vehicule_marque, v.modele as vehicule_modele,
                           v.plaque_immatriculation as vehicule_immatriculation,
                           COALESCE(v.electrique, t.vehicule_electrique) as vehicule_electrique,
                           CASE t.statut_moderation 
                               WHEN 'en_attente' THEN 'En attente de validation'
                               WHEN 'valide' THEN 'Validé et publié'
                               WHEN 'refuse' THEN 'Refusé par l\'administration'
                           END as statut_moderation_texte,
                           CASE 
                               WHEN t.statut = 'ouvert' AND DATE(t.date_depart) < CURDATE() THEN 'peut_terminer'
                               WHEN t.statut = 'termine' THEN 'peut_noter'
                               ELSE t.statut
                           END as action_possible
                    FROM trajets t
                    LEFT JOIN reservations r ON t.id = r.trajet_id AND r.statut = 'confirme'
                    LEFT JOIN vehicules v ON t.vehicule_id = v.id
                    WHERE t.conducteur_id = ?
                    GROUP BY t.id
                    ORDER BY t.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Correction bug heure : j'enrichis chaque trajet
            foreach ($trajets as &$trajet) {
                $trajet = $this->enrichirDonneesTrajet($trajet);
            }
            
            return $trajets;
            
        } catch (PDOException $e) {
            error_log("Erreur getTrajetsUtilisateur : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Pour l'admin - je récupère tous les trajets en attente
     */
    public function getTrajetsEnAttente()
    {
        try {
            // Back to basic - sans jointure pour éviter les erreurs
            $sql = "SELECT * FROM trajets 
                    WHERE statut_moderation = 'en_attente' 
                    ORDER BY created_at ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // J'ajoute les infos conducteur après si je trouve des trajets
            if (!empty($trajets)) {
                foreach ($trajets as &$trajet) {
                    // Je récupère simplement le conducteur
                    $sqlUser = "SELECT pseudo, email, nom, prenom FROM utilisateurs WHERE id = ?";
                    $stmtUser = $this->pdo->prepare($sqlUser);
                    $stmtUser->execute([$trajet['conducteur_id']]);
                    $conducteur = $stmtUser->fetch(PDO::FETCH_ASSOC);
                    
                    if ($conducteur) {
                        $trajet['conducteur_pseudo'] = $conducteur['pseudo'];
                        $trajet['conducteur_email'] = $conducteur['email'];
                        $trajet['conducteur_nom'] = $conducteur['nom'];
                        $trajet['conducteur_prenom'] = $conducteur['prenom'];
                    }
                    
                    // Correction bug heure ici aussi
                    $trajet = $this->enrichirDonneesTrajet($trajet);
                }
            }
            
            return $trajets;
            
        } catch (Exception $e) {
            error_log("Erreur getTrajetsEnAttente : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Pour l'admin - je modère un trajet (valide ou refuse)
     */
    public function modererTrajet($trajetId, $decision, $motif = null)
    {
        try {
            if (!in_array($decision, ['valide', 'refuse'])) {
                return ['succes' => false, 'erreur' => 'Décision invalide.'];
            }
            
            $sql = "UPDATE trajets 
                    SET statut_moderation = ?, motif_refus = ?
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $resultat = $stmt->execute([$decision, $motif, $trajetId]);
            
            if ($resultat && $stmt->rowCount() > 0) {
                return ['succes' => true];
            } else {
                return ['succes' => false, 'erreur' => 'Trajet non trouvé ou déjà modéré.'];
            }
            
        } catch (Exception $e) {
            error_log("Erreur modererTrajet : " . $e->getMessage());
            return ['succes' => false, 'erreur' => 'Erreur technique.'];
        }
    }

    /**
     * Je fournis les statistiques de modération pour l'admin
     */
    public function getStatsModeration()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_trajets,
                        SUM(CASE WHEN statut_moderation = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                        SUM(CASE WHEN statut_moderation = 'valide' THEN 1 ELSE 0 END) as valides,
                        SUM(CASE WHEN statut_moderation = 'refuse' THEN 1 ELSE 0 END) as refuses,
                        SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as termines
                    FROM trajets 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur getStatsModeration : " . $e->getMessage());
            return ['total_trajets' => 0, 'en_attente' => 0, 'valides' => 0, 'refuses' => 0, 'termines' => 0];
        }
    }

    /**
     * Pour l'admin - je récupère tous les trajets (pas seulement en attente)
     */
    public function getTousLesTrajets()
    {
        try {
            // Sans jointure
            $sql = "SELECT * FROM trajets ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // J'ajoute les infos conducteur
            foreach ($trajets as &$trajet) {
                $sqlUser = "SELECT pseudo, email, nom, prenom FROM utilisateurs WHERE id = ?";
                $stmtUser = $this->pdo->prepare($sqlUser);
                $stmtUser->execute([$trajet['conducteur_id']]);
                $conducteur = $stmtUser->fetch(PDO::FETCH_ASSOC);
                
                if ($conducteur) {
                    $trajet['conducteur_pseudo'] = $conducteur['pseudo'];
                    $trajet['conducteur_email'] = $conducteur['email'];
                    $trajet['conducteur_nom'] = $conducteur['nom'];
                    $trajet['conducteur_prenom'] = $conducteur['prenom'];
                }
                
                // Je texte lisible du statut
                $trajet['statut_moderation_texte'] = match($trajet['statut_moderation'] ?? '') {
                    'en_attente' => 'En attente',
                    'valide' => 'Validé',
                    'refuse' => 'Refusé',
                    default => 'Non défini'
                };
                
                // Correction bug heure ici aussi
                $trajet = $this->enrichirDonneesTrajet($trajet);
            }
            
            return $trajets;
            
        } catch (Exception $e) {
            error_log("Erreur getTousLesTrajets : " . $e->getMessage());
            return [];
        }
    }
    
    // Modification du compteur pour ne compter que les trajets validés
    private function compterTrajets($criteres)
    {
        try {
            $sql = "SELECT COUNT(*) FROM trajets t
                    WHERE t.statut = 'ouvert' 
                    AND t.statut_moderation = 'valide' 
                    AND DATE(t.date_depart) >= CURDATE()";
            
            $params = [];
            
            // Je garde tous les filtres
            if (!empty($criteres['lieu_depart'])) {
                $sql .= " AND (t.lieu_depart LIKE ? OR t.code_postal_depart LIKE ?)";
                $params[] = '%' . $criteres['lieu_depart'] . '%';
                $params[] = '%' . $criteres['lieu_depart'] . '%';
            }
            
            if (!empty($criteres['lieu_arrivee'])) {
                $sql .= " AND (t.lieu_arrivee LIKE ? OR t.code_postal_arrivee LIKE ?)";
                $params[] = '%' . $criteres['lieu_arrivee'] . '%';
                $params[] = '%' . $criteres['lieu_arrivee'] . '%';
            }
            
            if (!empty($criteres['date_depart'])) {
                $sql .= " AND DATE(t.date_depart) = ?";
                $params[] = $criteres['date_depart'];
            }
            
            if (!empty($criteres['vehicule_electrique'])) {
                $sql .= " AND t.vehicule_electrique = 1";
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Erreur compterTrajets : " . $e->getMessage());
            return 0;
        }
    }

    // Je garde toutes les méthodes existantes inchangées
    private function construireClauseTri($criteres)
    {
        $tri = $criteres['tri'] ?? 'date_depart';
        $direction = strtoupper($criteres['direction'] ?? 'ASC');
        
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        
        $trisAutorises = [
            'date_depart' => 't.date_depart',
            'prix' => 't.prix',
            'note' => 'u.note_moyenne',
            'ecologique' => 'COALESCE(v.electrique, t.vehicule_electrique) DESC, t.prix'
        ];
        
        if (isset($trisAutorises[$tri])) {
            if ($tri === 'ecologique') {
                return " ORDER BY " . $trisAutorises[$tri] . " ASC";
            } else {
                return " ORDER BY " . $trisAutorises[$tri] . " " . $direction;
            }
        }
        
        return " ORDER BY t.date_depart ASC";
    }
    
    /**
     * Je méthode qui enrichit les données et corrige le bug d'heure
     */
    private function enrichirDonneesTrajet($trajet)
    {
        // Correction bug heure : je combine date + heure correctement
        if (!empty($trajet['heure_depart'])) {
            // J'extrais juste la date (sans l'heure 00:00:00)
            $dateSeule = date('Y-m-d', strtotime($trajet['date_depart']));
            
            // Je combine avec l'heure stockée dans heure_depart
            $dateTimeComplete = $dateSeule . ' ' . $trajet['heure_depart'];
            
            $trajet['date_depart_formatee'] = date('d/m/Y à H:i', strtotime($dateTimeComplete));
        } else {
            // Fallback si pas d'heure
            $trajet['date_depart_formatee'] = date('d/m/Y', strtotime($trajet['date_depart']));
        }
        
        $trajet['distance_estimee'] = round($trajet['distance_km'] ?? 0) . ' km';
        $trajet['duree_estimee'] = $this->calculerDureeEstimee($trajet['distance_km'] ?? 0) . 'h';
        $trajet['presque_complet'] = ($trajet['places_disponibles'] ?? 0) <= 1;
        $trajet['co2_economise'] = round(($trajet['distance_km'] ?? 0) * 0.12, 1) . ' kg';
        
        if (empty($trajet['conducteur_photo'])) {
            $trajet['conducteur_photo'] = null;
        }
        
        return $trajet;
    }
    
    /**
     * Je getTrajetDetails avec correction du bug d'heure
     */
    public function getTrajetDetails($trajetId)
    {
        try {
            $sql = "SELECT t.*, 
                           u.pseudo as conducteur_pseudo, 
                           u.nom as conducteur_nom,
                           u.prenom as conducteur_prenom, 
                           u.note as conducteur_note,
                           u.photo_profil as conducteur_photo,
                           u.telephone as conducteur_telephone,
                           u.email as conducteur_email,
                           v.marque as vehicule_marque, 
                           v.modele as vehicule_modele,
                           v.plaque_immatriculation as vehicule_immatriculation,
                           v.couleur as vehicule_couleur,
                           v.electrique as vehicule_electrique_detail,
                           v.nb_places as vehicule_nb_places
                    FROM trajets t
                    JOIN utilisateurs u ON t.conducteur_id = u.id
                    LEFT JOIN vehicules v ON t.vehicule_id = v.id
                    WHERE t.id = ?";
           
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$trajetId]);
            $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($trajet) {
                // Correction ici aussi : j'utilise enrichirDonneesTrajet
                $trajet = $this->enrichirDonneesTrajet($trajet);
                
                $trajet['duree_estimee'] = ($trajet['duree_estimee'] ? $trajet['duree_estimee'] . ' min' : 'N/A');
                $trajet['energie_vehicule'] = $trajet['vehicule_electrique_detail'] ? 'Électrique' : 'Thermique';
                $trajet['places_disponibles'] = $this->calculerPlacesDisponibles($trajetId, $trajet['places']);
            }
            
            return $trajet;
            
        } catch (PDOException $e) {
            error_log("Erreur getTrajetDetails : " . $e->getMessage());
            return false;
        }
    }

    private function calculerPlacesDisponibles($trajetId, $placesInitiales)
    {
        try {
            $sql = "SELECT COALESCE(SUM(nb_places), 0) as places_reservees 
                    FROM reservations 
                    WHERE trajet_id = ? AND statut = 'confirme'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$trajetId]);
            $placesReservees = $stmt->fetchColumn();
            
            return max(0, $placesInitiales - $placesReservees);
            
        } catch (PDOException $e) {
            error_log("Erreur calcul places disponibles : " . $e->getMessage());
            return $placesInitiales;
        }
    }
    
    private function calculerDureeEstimee($distance)
    {
        return round($distance / 80, 1);
    }
    
    private function calculerDistanceEstimative($depart, $arrivee)
    {
        $cp_depart = substr($depart, -5);
        $cp_arrivee = substr($arrivee, -5);
        
        if (preg_match('/^\d{5}$/', $cp_depart) && preg_match('/^\d{5}$/', $cp_arrivee)) {
            $dept_depart = intval(substr($cp_depart, 0, 2));
            $dept_arrivee = intval(substr($cp_arrivee, 0, 2));
            
            $distance_base = abs($dept_arrivee - $dept_depart) * 50;
            return max(10, min(1000, $distance_base + rand(-20, 50)));
        }
        
        return rand(50, 500);
    }
    
    private function calculerPrix($distance, $electrique = false)
    {
        $prix = $distance * 0.15;
        
        if ($electrique) {
            $prix *= 0.9;
        }
        
        return max(5, min(150, ceil($prix)));
    }
    
    private function validerDonneesTrajet($data)
    {
        $erreurs = [];
        
        if (empty($data['lieu_depart'])) {
            $erreurs[] = 'Le lieu de départ est obligatoire.';
        }
        
        if (empty($data['lieu_arrivee'])) {
            $erreurs[] = 'Le lieu d\'arrivée est obligatoire.';
        }
        
        if (empty($data['code_postal_depart']) || !preg_match('/^\d{5}$/', $data['code_postal_depart'])) {
            $erreurs[] = 'Le code postal de départ doit contenir 5 chiffres.';
        }
        
        if (empty($data['code_postal_arrivee']) || !preg_match('/^\d{5}$/', $data['code_postal_arrivee'])) {
            $erreurs[] = 'Le code postal d\'arrivée doit contenir 5 chiffres.';
        }
        
        if (empty($data['date_depart'])) {
            $erreurs[] = 'La date de départ est obligatoire.';
        } elseif (strtotime($data['date_depart']) < strtotime('today')) {
            $erreurs[] = 'La date de départ doit être dans le futur.';
        }
        
        if (empty($data['heure_depart'])) {
            $erreurs[] = 'L\'heure de départ est obligatoire.';
        }
        
        if (empty($data['places']) || !is_numeric($data['places']) || $data['places'] < 1 || $data['places'] > 8) {
            $erreurs[] = 'Le nombre de places doit être entre 1 et 8.';
        }
        
        if (!empty($data['commentaire']) && strlen($data['commentaire']) > 500) {
            $erreurs[] = 'Le commentaire ne peut pas dépasser 500 caractères.';
        }
        
        if ($this->trajetExiste($data)) {
            $erreurs[] = 'Vous avez déjà un trajet similaire prévu à cette date et heure.';
        }
        
        return [
            'valide' => empty($erreurs),
            'erreurs' => $erreurs
        ];
    }
    
    private function utilisateurAPermis($userId)
    {
        try {
            $sql = "SELECT permis_conduire FROM utilisateurs WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            return (bool)$stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Erreur vérification permis : " . $e->getMessage());
            return false;
        }
    }
    
    private function trajetExiste($data)
    {
        try {
            $sql = "SELECT COUNT(*) FROM trajets 
                    WHERE conducteur_id = ? AND date_depart = ? AND heure_depart = ? 
                    AND lieu_depart = ? AND lieu_arrivee = ? AND statut != 'annule'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['conducteur_id'],
                $data['date_depart'],
                $data['heure_depart'],
                $data['lieu_depart'],
                $data['lieu_arrivee']
            ]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            error_log("Erreur vérification doublon : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Je récupère les trajets avec les statuts des réservations
     * Cela permet d'afficher les bons boutons selon l'état du workflow
     */
    public function getTrajetsUtilisateurAvecStatuts($userId)
    {
        try {
            $sql = "SELECT t.*, 
                           v.marque as vehicule_marque, 
                           v.modele as vehicule_modele,
                           -- Je compte les réservations par statut avec les nouvelles colonnes
                           SUM(CASE WHEN r.statut = 'confirme' AND r.date_debut_trajet IS NULL THEN 1 ELSE 0 END) as nb_reservations_confirmees,
                           SUM(CASE WHEN r.statut = 'confirme' AND r.date_debut_trajet IS NOT NULL AND r.date_fin_trajet IS NULL THEN 1 ELSE 0 END) as nb_reservations_en_cours,
                           SUM(CASE WHEN r.statut = 'termine' THEN 1 ELSE 0 END) as nb_reservations_terminees,
                           COUNT(r.id) as nb_reservations,
                           SUM(CASE WHEN r.statut = 'confirme' OR r.statut = 'termine' THEN 1 ELSE 0 END) as places_reservees
                    FROM trajets t 
                    LEFT JOIN vehicules v ON t.vehicule_id = v.id
                    LEFT JOIN reservations r ON t.id = r.trajet_id AND r.statut IN ('confirme', 'termine')
                    WHERE t.conducteur_id = ? 
                    GROUP BY t.id, t.lieu_depart, t.lieu_arrivee, t.date_depart, t.heure_depart, 
                             t.places, t.prix, t.vehicule_electrique, t.commentaire, t.statut, 
                             t.created_at, v.marque, v.modele
                    ORDER BY t.date_depart DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            // Je m'assure que les compteurs sont des entiers
            foreach ($trajets as &$trajet) {
                $trajet['nb_reservations_confirmees'] = (int)($trajet['nb_reservations_confirmees'] ?? 0);
                $trajet['nb_reservations_en_cours'] = (int)($trajet['nb_reservations_en_cours'] ?? 0);
                $trajet['nb_reservations_terminees'] = (int)($trajet['nb_reservations_terminees'] ?? 0);
                $trajet['nb_reservations'] = (int)($trajet['nb_reservations'] ?? 0);
                $trajet['places_reservees'] = (int)($trajet['places_reservees'] ?? 0);
            }
            
            return $trajets;
            
        } catch (Exception $e) {
            error_log("Erreur getTrajetsUtilisateurAvecStatuts : " . $e->getMessage());
            return [];
        }
    }
}
?>

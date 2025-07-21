<?php
/**
 * Modèle Trip pour la gestion complète des trajets 
 * Gère toutes les opérations CRUD des trajets avec validation,
 * recherche avancée, filtres et calculs automatiques
 */

class Trip
{
    private $pdo;
    
    /**
     * Constructeur - Injection de dépendance PDO
     * 
     * @param PDO $pdo Instance de connexion à la base de données
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Crée un nouveau trajet avec validation complète
     * 
     * @param array $data Données du trajet à créer
     * @return array Résultat avec succès/erreurs et informations
     */
    public function creerTrajet($data)
    {
        // Validation des données avant insertion
        $validation = $this->validerDonneesTrajet($data);
        if (!$validation['valide']) {
            return ['succes' => false, 'erreurs' => $validation['erreurs']];
        }
        
        // Vérification des prérequis utilisateur
        if (!$this->utilisateurAPermis($data['conducteur_id'])) {
            return ['succes' => false, 'erreurs' => ['Vous devez avoir le permis de conduire pour proposer un trajet.']];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Calculs automatiques pour le trajet
            $distance = $this->calculerDistanceEstimative($data['lieu_depart'], $data['lieu_arrivee']);
            $prixCalcule = $this->calculerPrix($distance, $data['vehicule_electrique'] ?? false);
            
            // Insertion du nouveau trajet
            $sql = "INSERT INTO trajets (
                        conducteur_id, vehicule_id, lieu_depart, code_postal_depart, 
                        lieu_arrivee, code_postal_arrivee, date_depart, heure_depart,
                        places, prix, commission, vehicule_electrique, distance_km, 
                        statut, commentaire, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ouvert', ?, NOW())";
            
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
                $data['commentaire'] ?? null
            ]);
            
            if ($resultat) {
                $trajetId = $this->pdo->lastInsertId();
                $this->pdo->commit();
                
                return [
                    'succes' => true,
                    'message' => 'Trajet créé avec succès !',
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
     * Recherche des trajets avec filtres avancés et tri dynamique
     * 
     * @param array $criteres Critères de recherche (lieux, date, prix, etc.)
     * @param int $page Numéro de page pour la pagination
     * @param int $limit Nombre de résultats par page
     * @return array Résultats avec trajets et informations de pagination
     */
    public function rechercherTrajets($criteres = [], $page = 1, $limit = 10)
{
    try {
        
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
                WHERE t.statut = 'ouvert' AND DATE(t.date_depart) >= CURDATE()";
        
        $params = [];
        
        // Filtres selon les colonnes de la table
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
        
        // Pagination
        $offset = ($page - 1) * $limit;
        $sql .= " ORDER BY t.date_depart ASC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Enrichissement des données pour la vue 
        foreach ($trajets as &$trajet) {
            $trajet['date_depart_formatee'] = date('d/m/Y à H:i', strtotime($trajet['date_depart']));
            $trajet['distance_estimee'] = round($trajet['distance_km']) . ' km';
            $trajet['duree_estimee'] = ($trajet['duree_estimee'] ? $trajet['duree_estimee'] . ' min' : 'N/A');
            $trajet['presque_complet'] = $trajet['places_disponibles'] <= 1;
            $trajet['co2_economise'] = round($trajet['distance_km'] * 0.12, 1) . ' kg';
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
     *Construit la clause ORDER BY selon les critères de tri
      
     * @param array $criteres Critères incluant tri et direction
     * @return string Clause ORDER BY SQL
     */
    private function construireClauseTri($criteres)
    {
        $tri = $criteres['tri'] ?? 'date_depart';
        $direction = strtoupper($criteres['direction'] ?? 'ASC');
        
        // Validation de la direction
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        
        // Mapping des tris autorisés
        $trisAutorises = [
            'date_depart' => 't.date_depart',
            'prix' => 't.prix',
            'note' => 'u.note_moyenne',
            'ecologique' => 'COALESCE(v.electrique, t.vehicule_electrique) DESC, t.prix'
        ];
        
        if (isset($trisAutorises[$tri])) {
            if ($tri === 'ecologique') {
                // Tri spécial : électriques d'abord, puis par prix
                return " ORDER BY " . $trisAutorises[$tri] . " ASC";
            } else {
                return " ORDER BY " . $trisAutorises[$tri] . " " . $direction;
            }
        }
        
        // Tri par défaut
        return " ORDER BY t.date_depart ASC";
    }
    
    /**
     * Enrichit les données d'un trajet pour la vue
     * @param array $trajet Données brutes du trajet
     * @return array Données enrichies pour l'affichage
     */
    private function enrichirDonneesTrajet($trajet)
    {
        // Formatage de la date
        $trajet['date_depart_formatee'] = date('d/m/Y à H:i', strtotime($trajet['date_depart'] . ' ' . $trajet['heure_depart']));
        
        // Distance et durée estimées
        $trajet['distance_estimee'] = round($trajet['distance_km']) . ' km';
        $trajet['duree_estimee'] = $this->calculerDureeEstimee($trajet['distance_km']) . 'h';
        
        // Indicateurs visuels
        $trajet['presque_complet'] = $trajet['places_disponibles'] <= 1;
        $trajet['co2_economise'] = round($trajet['distance_km'] * 0.12, 1) . ' kg';
        
        // Gestion de la photo par défaut
        if (empty($trajet['conducteur_photo'])) {
            $trajet['conducteur_photo'] = null;
        }
        
        return $trajet;
    }
    
    /**
     * Récupère les détails complets d'un trajet spécifique
     * 
     * @param int $trajetId ID du trajet
     * @return array|false Détails du trajet ou false si non trouvé
     */
    /**
 * Récupère les détails complets d'un trajet
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
            // Enrichissement des données pour l'affichage
            $trajet['date_depart_formatee'] = date('d/m/Y à H:i', strtotime($trajet['date_depart']));
            $trajet['distance_estimee'] = round($trajet['distance_km']) . ' km';
            $trajet['duree_estimee'] = ($trajet['duree_estimee'] ? $trajet['duree_estimee'] . ' min' : 'N/A');
            $trajet['co2_economise'] = round($trajet['distance_km'] * 0.12, 1) . ' kg';
            $trajet['energie_vehicule'] = $trajet['vehicule_electrique_detail'] ? 'Électrique' : 'Thermique';
            
            // Calculer les places disponibles en temps réel
            $trajet['places_disponibles'] = $this->calculerPlacesDisponibles($trajetId, $trajet['places']);
        }
        
        return $trajet;
        
    } catch (PDOException $e) {
        error_log("Erreur getTrajetDetails : " . $e->getMessage());
        return false;
    }
}

/**
 * Calcule les places disponibles en temps réel
 */
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
        return $placesInitiales; // Retour par défaut
    }
}

    
    /**
     * Récupère les trajets d'un utilisateur spécifique
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des trajets de l'utilisateur
     */
    public function getTrajetsUtilisateur($userId)
    {
        try {
            $sql = "SELECT t.*, 
                           COUNT(r.id) as nb_reservations,
                           SUM(r.nb_places) as places_reservees,
                           v.marque as vehicule_marque, v.modele as vehicule_modele,
                           v.plaque_immatriculation as vehicule_immatriculation,
                           COALESCE(v.electrique, t.vehicule_electrique) as vehicule_electrique
                    FROM trajets t
                    LEFT JOIN reservations r ON t.id = r.trajet_id AND r.statut = 'confirme'
                    LEFT JOIN vehicules v ON t.vehicule_id = v.id
                    WHERE t.conducteur_id = ?
                    GROUP BY t.id
                    ORDER BY t.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getTrajetsUtilisateur : " . $e->getMessage());
            return [];
        }
    }
    
    /** 
     * @param array $criteres Mêmes critères que la recherche
     * @return int Nombre total de trajets correspondants
     */
    private function compterTrajets($criteres)
{
    try {
        $sql = "SELECT COUNT(*) FROM trajets t
                WHERE t.statut = 'ouvert' AND DATE(t.date_depart) >= CURDATE()";
        
        $params = [];
        
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

    
    /**
     * MÉTHODES UTILITAIRES PRIVÉES
     */
    
    /**
     * Calcule la durée estimée d'un trajet
     * 
     * @param float $distance Distance en kilomètres
     * @return float Durée en heures
     */
    private function calculerDureeEstimee($distance)
    {
        // Estimation : 80 km/h en moyenne (autoroute + ville)
        return round($distance / 80, 1);
    }
    
    /**
     * Calcul de distance estimative sans géolocalisation
     * 
     * @param string $depart Lieu de départ
     * @param string $arrivee Lieu d'arrivée
     * @return int Distance estimée en km
     */
    private function calculerDistanceEstimative($depart, $arrivee)
    {
        // Extraction des codes postaux pour estimation
        $cp_depart = substr($depart, -5);
        $cp_arrivee = substr($arrivee, -5);
        
        if (preg_match('/^\d{5}$/', $cp_depart) && preg_match('/^\d{5}$/', $cp_arrivee)) {
            $dept_depart = intval(substr($cp_depart, 0, 2));
            $dept_arrivee = intval(substr($cp_arrivee, 0, 2));
            
            // Estimation basée sur la différence de départements
            $distance_base = abs($dept_arrivee - $dept_depart) * 50;
            return max(10, min(1000, $distance_base + rand(-20, 50)));
        }
        
        // Distance aléatoire si pas de codes postaux valides
        return rand(50, 500);
    }
    
    /**
     * Calcule le prix automatique d'un trajet
     * 
     * @param int $distance Distance en km
     * @param bool $electrique Véhicule électrique ou non
     * @return int Prix en crédits EcoRide
     */
    private function calculerPrix($distance, $electrique = false)
    {
        $prix = $distance * 0.15; // 0.15 crédit par km
        
        if ($electrique) {
            $prix *= 0.9; // 10% de réduction écologique
        }
        
        return max(5, min(150, ceil($prix))); // Entre 5 et 150 crédits
    }
    
    /**
     * Va2lidation complète des données de trajet
     * 
     * @param array $data Données à valider
     * @return array Résultat de validation avec erreurs
     */
    private function validerDonneesTrajet($data)
    {
        $erreurs = [];
        
        // Validation des lieux obligatoires
        if (empty($data['lieu_depart'])) {
            $erreurs[] = 'Le lieu de départ est obligatoire.';
        }
        
        if (empty($data['lieu_arrivee'])) {
            $erreurs[] = 'Le lieu d\'arrivée est obligatoire.';
        }
        
        // Validation des codes postaux
        if (empty($data['code_postal_depart']) || !preg_match('/^\d{5}$/', $data['code_postal_depart'])) {
            $erreurs[] = 'Le code postal de départ doit contenir 5 chiffres.';
        }
        
        if (empty($data['code_postal_arrivee']) || !preg_match('/^\d{5}$/', $data['code_postal_arrivee'])) {
            $erreurs[] = 'Le code postal d\'arrivée doit contenir 5 chiffres.';
        }
        
        // Validation de la date et heure
        if (empty($data['date_depart'])) {
            $erreurs[] = 'La date de départ est obligatoire.';
        } elseif (strtotime($data['date_depart']) < strtotime('today')) {
            $erreurs[] = 'La date de départ doit être dans le futur.';
        }
        
        if (empty($data['heure_depart'])) {
            $erreurs[] = 'L\'heure de départ est obligatoire.';
        }
        
        // Validation du nombre de places
        if (empty($data['places']) || !is_numeric($data['places']) || $data['places'] < 1 || $data['places'] > 8) {
            $erreurs[] = 'Le nombre de places doit être entre 1 et 8.';
        }
        
        // Validation du commentaire
        if (!empty($data['commentaire']) && strlen($data['commentaire']) > 500) {
            $erreurs[] = 'Le commentaire ne peut pas dépasser 500 caractères.';
        }
        
        // Vérification des doublons
        if ($this->trajetExiste($data)) {
            $erreurs[] = 'Vous avez déjà un trajet similaire prévu à cette date et heure.';
        }
        
        return [
            'valide' => empty($erreurs),
            'erreurs' => $erreurs
        ];
    }
    
    /**
     * Vérifie si l'utilisateur a le permis de conduire
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool True si l'utilisateur a le permis
     */
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
    
    /**
     * Vérifie si un trajet similaire existe déjà
     * 
     * @param array $data Données du trajet à vérifier
     * @return bool True si un trajet similaire existe
     */
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
}
?>

<?php
/**
 * Contrôleur pour la gestion du profil utilisateur EcoRide
 * 
 * Ce contrôleur gère toutes les opérations liées au profil utilisateur :
 * - Affichage du profil avec statistiques personnalisées et impact écologique
 * - Modification des informations personnelles avec validation sécurisée
 * - Gestion complète des véhicules (ajout, modification, suppression)
 * - Historique des activités (trajets proposés et réservations effectuées)
 * - Calcul de l'impact écologique personnel (CO₂ économisé)
 * - API AJAX pour une expérience utilisateur fluide
 * 
 * Architecture : Utilise la connexion PDO centralisée depuis config/database.php
 */

class UserController
{
    /**
     * Affiche le profil complet de l'utilisateur connecté
     */
    public function profil()
    {
        // Vérification que l'utilisateur est connecté (sécurité obligatoire)
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour accéder à votre profil.';
            header('Location: /connexion');
            exit;
        }
        
        // CORRECTION : Utilisation de l'architecture centralisée
        // Inclusion de la configuration de base de données centralisée
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/User.php';
        
        // Récupération de la connexion PDO globale
        global $pdo;
        
        // Instanciation du modèle User avec la connexion PDO
        // IMPORTANT : Passage de $pdo au constructeur (architecture corrigée)
        $userModel = new User($pdo);
        
        // Récupération des données utilisateur actualisées depuis la base
        // Important pour avoir les crédits et informations à jour
        $userData = $userModel->getUserById($_SESSION['user']['id']);
        
        if (!$userData) {
            $_SESSION['message'] = 'Erreur lors du chargement de votre profil.';
            header('Location: /');
            exit;
        }
        
        // Mise à jour des données de session avec les informations actuelles
        // Garantit que les crédits affichés sont corrects
        $_SESSION['user'] = $userData;
        
        // Variables pour la vue Bootstrap 5
        $title = "Mon profil | EcoRide - Votre espace personnel";
        $user = $userData;
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']); // Nettoyage après affichage
        
        // Calcul des statistiques personnalisées pour l'affichage
        // Selon l'énoncé : montrer l'impact écologique de l'utilisateur
        $stats = $this->getStatistiquesUtilisateur($userData['id']);
        
        // Affichage de la vue profil avec Bootstrap 5 et JavaScript
        require __DIR__ . '/../Views/user/profil.php';
    }
    
    /**
     * Traite la modification du profil utilisateur via AJAX
     * 
     * Cette méthode gère la modification des informations personnelles
     * de l'utilisateur avec validation côté serveur et mi2se à jour sécurisée.
     * Elle vérifie l'unicité du pseudo et de l'email .
     */
    public function modifierProfil()
    {
        // Vérification que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        // Vérification que c'est une requête POST pour sécurité
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée.']);
            return;
        }
        
        // Récupération et nettoyage des données du formulaire
        $data = [
            'pseudo' => trim($_POST['pseudo'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'nom' => trim($_POST['nom'] ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'ville' => trim($_POST['ville'] ?? ''),
            'code_postal' => trim($_POST['code_postal'] ?? ''),
            'adresse' => trim($_POST['adresse'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'permis_conduire' => isset($_POST['permis_conduire']) ? 1 : 0
        ];
        
        
        // Validation des données côté serveur (sécurité)
        $erreurs = $this->validerDonneesProfil($data, $_SESSION['user']['id']);
        
        if (!empty($erreurs)) {
            echo json_encode(['succes' => false, 'erreurs' => $erreurs]);
            return;
        }
        
        // CORRECTION : Utilisation du modèle avec architecture centralisée
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/User.php';
        
        global $pdo;
        $userModel = new User($pdo);
        
        // Mise à jour via le modèle User (logique métier dans le modèle)
        $resultat = $userModel->mettreAJourProfil($_SESSION['user']['id'], $data);
        
        if ($resultat['succes']) {
            // Mise à jour des données de session avec les nouvelles informations
            foreach ($data as $key => $value) {
                $_SESSION['user'][$key] = $value;
            }
            
            // Réponse JSON de succès pour le JavaScript
            echo json_encode([
                'succes' => true,
                'message' => 'Profil mis à jour avec succès !'
            ]);
        } else {
            // Réponse JSON d'erreur pour le JavaScript
            echo json_encode([
                'succes' => false,
                'erreur' => $resultat['erreur']
            ]);
        }
    }
    
    /**
     * Affiche l'historique complet des activités de l'utilisateur
     * 
     * Cette méthode récupère et affiche l'historique complet des activités
     * de l'utilisateur selon l'énoncé EcoRide :
     * - Trajets proposés avec statistiques de réservation
     * - Réservations effectuées avec détails des trajets
     * - Calcul de l'impact écologique total
     * 
     * Route : GET /historique
     */
    public function historique()
    {
        // Vérification que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour voir votre historique.';
            header('Location: /connexion');
            exit;
        }
        
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Trip.php';
        require_once __DIR__ . '/../Models/Reservation.php';
        
        global $pdo;
        $tripModel = new Trip($pdo);
        $reservationModel = new Reservation($pdo);
        
        // Récupération de l'historique complet
        $trajetsProposés = $tripModel->getTrajetsUtilisateur($_SESSION['user']['id']);
        $reservations = $reservationModel->getReservationsUtilisateur($_SESSION['user']['id']);
        
        // Variables pour la vue Bootstrap 5
        $title = "Mon historique | EcoRide - Votre impact écologique";
        $user = $_SESSION['user'];
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        
        // Affichage de la vue historique avec Bootstrap 5
        require __DIR__ . '/../Views/user/historique.php';
    }
    
    /**
     * Ajoute un véhicule pour l'utilisateur connecté via AJAX
     * 
     * Cette méthode gère l'ajout d'un nouveau véhicule dans le profil
     * utilisateur selon l'énoncé EcoRide. Elle supporte les véhicules
     * électriques avec badge spécial pour encourager l'éco-mobilité.
     */
    public function ajouterVehicule()
    {
        // Vérification que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        // Vérification que c'est une requête POST pour sécurité
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée.']);
            return;
        }
        
        // Récupération des données du formulaire véhicule
        $data = [
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'plaque_immatriculation' => trim($_POST['plaque_immatriculation'] ?? ''),
            'places_disponibles' => intval($_POST['places_disponibles'] ?? 4),
            'electrique' => isset($_POST['electrique']) ? 1 : 0
        ];
        
        // Validation des données véhicule
        $erreurs = $this->validerDonneesVehicule($data);
        
        if (!empty($erreurs)) {
            echo json_encode(['succes' => false, 'erreurs' => $erreurs]);
            return;
        }
        
        try {
            //Utilisation de la connexion centralisée
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Insertion du véhicule en base de données avec toutes les colonnes nécessaires
          
          $sql = "INSERT INTO vehicules (utilisateur_id, marque, modele, plaque_immatriculation, couleur, electrique, nb_places, created_at) 
           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";


            $stmt = $pdo->prepare($sql);
            $resultat = 
            $stmt->execute([
            $_SESSION['user']['id'],
            $data['marque'],
            $data['modele'],
            $data['plaque_immatriculation'],
            $data['couleur'],
            $data['electrique'],
            $data['places_disponibles'] // Le formulaire envoie places_disponibles mais on l'insère dans nb_places
             ]);


            if ($resultat) {
                // Message de succès avec encouragement écologique
                $message = 'Véhicule ajouté avec succès !';
                if ($data['electrique']) {
                    $message .= ' Merci de contribuer à la mobilité écologique ! 🌱';
                }
                
                echo json_encode([
                    'succes' => true,
                    'message' => $message
                ]);
            } else {
                echo json_encode([
                    'succes' => false,
                    'erreur' => 'Erreur lors de l\'ajout du véhicule.'
                ]);
            }
            
        } catch (PDOException $e) {
            error_log("Erreur ajout véhicule EcoRide : " . $e->getMessage());
            echo json_encode([
                'succes' => false,
                'erreur' => 'Erreur technique lors de l\'ajout : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Récupère les véhicules de l'utilisateur connecté via AJAX
     * 
     * Cette méthode retourne la liste complète des véhicules de l'utilisateur
     * au format JSON pour l'affichage dynamique dans le profil avec JavaScript.
     */
    public function mesVehicules()
    {
        // Vérification que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        try {
            // CORRECTION : Utilisation de la connexion centralisée
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Récupération des véhicules de l'utilisateur avec tri par date
            $sql = "SELECT * FROM vehicules WHERE utilisateur_id = ? ORDER BY created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user']['id']]);
            
            $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Enrichissement des données pour l'affichage
            foreach ($vehicules as &$vehicule) {
                $vehicule['date_ajout_formatee'] = date('d/m/Y', strtotime($vehicule['created_at']));
                $vehicule['badge_ecologique'] = $vehicule['electrique'] ? 'Véhicule électrique' : null;
            }
            
            // Réponse JSON avec la liste des véhicules
            echo json_encode([
                'succes' => true,
                'vehicules' => $vehicules
            ]);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération véhicules EcoRide : " . $e->getMessage());
            echo json_encode([
                'succes' => false,
                'erreur' => 'Erreur technique lors de la récupération.'
            ]);
        }
    }

    /**
     * Supprime un véhicule de l'utilisateur via AJAX
     * 
     * Cette méthode gère la suppression sécurisée d'un véhicule du profil
     * utilisateur avec vérification de propriété pour éviter les abus.
     */
    public function supprimerVehicule($vehiculeId)
    {
        // Vérification que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        // Validation de l'ID du véhicule
        $vehiculeId = intval($vehiculeId);
        if ($vehiculeId <= 0) {
            echo json_encode(['succes' => false, 'erreur' => 'ID de véhicule invalide.']);
            return;
        }
        
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Suppression sécurisée : vérification de propriété dans la requête SQL
            // Sécurité : un utilisateur ne peut supprimer que ses propres véhicules
            $sql = "DELETE FROM vehicules WHERE id = ? AND utilisateur_id = ?";
            $stmt = $pdo->prepare($sql);
            $resultat = $stmt->execute([$vehiculeId, $_SESSION['user']['id']]);
            
            if ($resultat && $stmt->rowCount() > 0) {
                echo json_encode([
                    'succes' => true,
                    'message' => 'Véhicule supprimé avec succès !'
                ]);
            } else {
                echo json_encode([
                    'succes' => false,
                    'erreur' => 'Véhicule non trouvé ou non autorisé.'
                ]);
            }
            
        } catch (PDOException $e) {
            error_log("Erreur suppression véhicule EcoRide : " . $e->getMessage());
            echo json_encode([
                'succes' => false,
                'erreur' => 'Erreur technique lors de la suppression.'
            ]);
        }
    }
    
    /**
     * Modifie un véhicule existant via AJAX
     * 
     * Cette méthode permet de modifier les caractéristiques d'un véhicule
     * existant avec validation et vérification de propriété.
     */
    public function modifierVehicule($vehiculeId)
    {
        // Vérification que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        // Vérification que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée.']);
            return;
        }
        
        // Validation de l'ID du véhicule
        $vehiculeId = intval($vehiculeId);
        if ($vehiculeId <= 0) {
            echo json_encode(['succes' => false, 'erreur' => 'ID de véhicule invalide.']);
            return;
        }
        
        // Récupération des données de modification
        $data = [
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'plaque_immatriculation' => trim($_POST['plaque_immatriculation'] ?? ''),
            'places_disponibles' => intval($_POST['places_disponibles'] ?? 4),
            'electrique' => isset($_POST['electrique']) ? 1 : 0
        ];
        
        // Validation des données
        $erreurs = $this->validerDonneesVehicule($data);
        
        if (!empty($erreurs)) {
            echo json_encode(['succes' => false, 'erreurs' => $erreurs]);
            return;
        }
        
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Mise à jour sécurisée avec vérification de propriété
            $sql = "UPDATE vehicules 
                    SET marque = ?, modele = ?, couleur = ?, plaque_immatriculation = ?, places_disponibles = ?, electrique = ?, updated_at = NOW() 
                    WHERE id = ? AND utilisateur_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $resultat = $stmt->execute([
                $data['marque'],
                $data['modele'],
                $data['couleur'],
                $data['plaque_immatriculation'],
                $data['places_disponibles'],
                $data['electrique'],
                $vehiculeId,
                $_SESSION['user']['id']
            ]);
            
            if ($resultat && $stmt->rowCount() > 0) {
                echo json_encode([
                    'succes' => true,
                    'message' => 'Véhicule modifié avec succès !'
                ]);
            } else {
                echo json_encode([
                    'succes' => false,
                    'erreur' => 'Véhicule non trouvé ou non autorisé.'
                ]);
            }
            
        } catch (PDOException $e) {
            error_log("Erreur modification véhicule EcoRide : " . $e->getMessage());
            echo json_encode([
                'succes' => false,
                'erreur' => 'Erreur technique lors de la modification.'
            ]);
        }
    }
    
    /**
     * Récupère les statistiques de crédits de l'utilisateur via AJAX
     * 
     * Cette méthode retourne l'historique détaillé des transactions de crédits
     * pour affichage dans le profil utilisateur.
     */
    public function historiqueCredits()
    {
        // Vérification que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        try {
            // CORRECTION : Utilisation de la connexion centralisée
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Récupération de l'historique des transactions de crédits
            $sql = "SELECT tc.*, t.lieu_depart, t.lieu_arrivee 
                    FROM transactions_credits tc
                    LEFT JOIN trajets t ON tc.trajet_id = t.id
                    WHERE tc.utilisateur_id = ?
                    ORDER BY tc.date_transaction DESC
                    LIMIT 20";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user']['id']]);
            
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Enrichissement des données pour l'affichage
            foreach ($transactions as &$transaction) {
                $transaction['date_formatee'] = date('d/m/Y H:i', strtotime($transaction['date_transaction']));
                $transaction['type_libelle'] = $transaction['type_transaction'] === 'credit' ? 'Crédit' : 'Débit';
                $transaction['montant_formate'] = ($transaction['type_transaction'] === 'credit' ? '+' : '-') . abs($transaction['montant']);
            }
            
            echo json_encode([
                'succes' => true,
                'transactions' => $transactions
            ]);
            
        } catch (PDOException $e) {
            error_log("Erreur historique crédits EcoRide : " . $e->getMessage());
            echo json_encode([
                'succes' => false,
                'erreur' => 'Erreur technique lors de la récupération.'
            ]);
        }
    }
    
    /**
     * Récupère les statistiques personnalisées de l'utilisateur
     * 
     * Cette méthode calcule les statistiques personnelles de l'utilisateur
     *  trajets proposés, réservations, crédits, impact écologique.
     * Elle utilise la connexion centralisée pour toutes les requêtes.
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Statistiques complètes pour l'affichage
     */
    private function getStatistiquesUtilisateur($userId)
    {
        try {
            
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Nombre de trajets proposés par l'utilisateur
            $sql = "SELECT COUNT(*) FROM trajets WHERE conducteur_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $trajetsProposés = $stmt->fetchColumn();
            
            // Nombre de réservations effectuées par l'utilisateur
            $sql = "SELECT COUNT(*) FROM reservations WHERE passager_id = ? AND statut = 'confirme'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $reservationsEffectuées = $stmt->fetchColumn();
            
            // Total des crédits gagnés (revenus des trajets)
            $sql = "SELECT COALESCE(SUM(montant), 0) FROM transactions_credits 
                    WHERE utilisateur_id = ? AND type_transaction = 'credit' AND source != 'inscription'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $creditsGagnés = $stmt->fetchColumn();
            
            // Total des crédits dépensés (réservations)
            $sql = "SELECT COALESCE(SUM(ABS(montant)), 0) FROM transactions_credits 
                    WHERE utilisateur_id = ? AND type_transaction = 'debit'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $creditsDépensés = $stmt->fetchColumn();
            
            // Date d'inscription pour calculer l'ancienneté
            $sql = "SELECT created_at FROM utilisateurs WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $dateInscription = $stmt->fetchColumn();
            
            // Calcul de l'impact écologique personnel 
            $impactEcologique = $this->calculerImpactEcologique($userId);
            
            return [
                'trajets_proposés' => $trajetsProposés,
                'reservations_effectuées' => $reservationsEffectuées,
                'credits_gagnés' => $creditsGagnés,
                'credits_dépensés' => $creditsDépensés,
                'date_inscription' => $dateInscription,
                'membre_depuis' => $this->calculerDureeInscription($dateInscription),
                'co2_economise' => $impactEcologique['co2_economise'],
                'km_partages' => $impactEcologique['km_partages'],
                'carburant_economise' => $impactEcologique['carburant_economise']
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur statistiques utilisateur EcoRide : " . $e->getMessage());
            
            // Retour de statistiques par défaut en cas d'erreur
            return [
                'trajets_proposés' => 0,
                'reservations_effectuées' => 0,
                'credits_gagnés' => 0,
                'credits_dépensés' => 0,
                'date_inscription' => null,
                'membre_depuis' => 'Inconnu',
                'co2_economise' => 0,
                'km_partages' => 0,
                'carburant_economise' => 0
            ];
        }
    }
    
    /**
     * Calcule l'impact écologique personnel de l'utilisateur
     * 
     * Cette méthode calcule l'impact écologique :
     * - CO₂ économisé grâce au covoiturage (120g/km)
     * - Kilomètres partagés (trajets + réservations)
     * - Carburant économisé (7L/100km)
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Impact écologique calculé
     */
    private function calculerImpactEcologique($userId)
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Calcul des kilomètres partagés via les trajets proposés
            $sql = "SELECT COALESCE(SUM(t.distance_km * COALESCE(r.nb_places_total, 0)), 0) as km_trajets
                    FROM trajets t
                    LEFT JOIN (
                        SELECT trajet_id, SUM(nb_places) as nb_places_total
                        FROM reservations 
                        WHERE statut = 'confirme'
                        GROUP BY trajet_id
                    ) r ON t.id = r.trajet_id
                    WHERE t.conducteur_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $kmTrajets = $stmt->fetchColumn();
            
            // Calcul des kilomètres partagés via les réservations effectuées
            $sql = "SELECT COALESCE(SUM(t.distance_km * r.nb_places), 0) as km_reservations
                    FROM reservations r
                    JOIN trajets t ON r.trajet_id = t.id
                    WHERE r.passager_id = ? AND r.statut = 'confirme'";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $kmReservations = $stmt->fetchColumn();
            
            // Total des kilomètres partagés
            $kmPartages = $kmTrajets + $kmReservations;
            
            // Calculs écologiques selon l'énoncé EcoRide
            $co2Economise = round($kmPartages * 0.12, 1); // 120g CO₂/km
            $carburantEconomise = round($kmPartages * 0.07, 1); // 7L/100km
            
            return [
                'km_partages' => $kmPartages,
                'co2_economise' => $co2Economise,
                'carburant_economise' => $carburantEconomise
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur calcul impact écologique : " . $e->getMessage());
            
            return [
                'km_partages' => 0,
                'co2_economise' => 0,
                'carburant_economise' => 0
            ];
        }
    }
    
    /**
     * Valide les données de modification du profil
     * 
     * Cette méthode effectue une validation complète des données du profil.
     * 
     * @param array $data Données à valider
     * @param int $userId ID de l'utilisateur actuel (pour exclure de l'unicité)
     * @return array Erreurs de validation
     */
    private function validerDonneesProfil($data, $userId)
    {
        $erreurs = [];
        
        // Validation du pseudo 
        if (empty($data['pseudo'])) {
            $erreurs[] = 'Le pseudo est obligatoire.';
        } elseif (strlen($data['pseudo']) < 3) {
            $erreurs[] = 'Le pseudo doit contenir au moins 3 caractères.';
        } elseif (strlen($data['pseudo']) > 50) {
            $erreurs[] = 'Le pseudo ne peut pas dépasser 50 caractères.';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['pseudo'])) {
            $erreurs[] = 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.';
        }
        
        // Validation de l'email
        if (empty($data['email'])) {
            $erreurs[] = 'L\'adresse email est obligatoire.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'L\'adresse email n\'est pas valide.';
        } elseif (strlen($data['email']) > 255) {
            $erreurs[] = 'L\'adresse email est trop longue.';
        }
        
        // Validation du téléphone (optionnel)
        if (!empty($data['telephone']) && !preg_match('/^[0-9+\-\s\.]{10,15}$/', $data['telephone'])) {
            $erreurs[] = 'Le numéro de téléphone n\'est pas valide.';
        }
        
        // Validation de la bio (optionnelle)
        if (!empty($data['bio']) && strlen($data['bio']) > 500) {
            $erreurs[] = 'La biographie ne peut pas dépasser 500 caractères.';
        }
        
        // Validation du code postal (optionnel)
        if (!empty($data['code_postal']) && !preg_match('/^\d{5}$/', $data['code_postal'])) {
            $erreurs[] = 'Le code postal doit contenir exactement 5 chiffres.';
        }
        
        // Vérification de l'unicité du pseudo et email (sauf pour l'utilisateur actuel)
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Vérification unicité du pseudo
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE pseudo = ? AND id != ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['pseudo'], $userId]);
            if ($stmt->fetchColumn() > 0) {
                $erreurs[] = 'Ce pseudo est déjà utilisé par un autre utilisateur.';
            }
            
            // Vérification unicité de l'email
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = ? AND id != ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['email'], $userId]);
            if ($stmt->fetchColumn() > 0) {
                $erreurs[] = 'Cette adresse email est déjà utilisée par un autre utilisateur.';
            }
            
        } catch (PDOException $e) {
            error_log("Erreur validation profil : " . $e->getMessage());
            $erreurs[] = 'Erreur lors de la validation des données.';
        }
        
        return $erreurs;
    }
    
    /**
     * Valide les données d'un véhicule
     * 
     * Cette méthode valide les données d'un véhicule 
     * avec support spécial pour les véhicules électriques.
     * 
     * @param array $data Données à valider
     * @return array Erreurs de validation
     */
    private function validerDonneesVehicule($data)
    {
        $erreurs = [];
        
        // Validation de la marque (obligatoire)
        if (empty($data['marque'])) {
            $erreurs[] = 'La marque est obligatoire.';
        } elseif (strlen($data['marque']) < 2) {
            $erreurs[] = 'La marque doit contenir au moins 2 caractères.';
        } elseif (strlen($data['marque']) > 50) {
            $erreurs[] = 'La marque ne peut pas dépasser 50 caractères.';
        }
        
        // Validation du modèle (obligatoire)
        if (empty($data['modele'])) {
            $erreurs[] = 'Le modèle est obligatoire.';
        } elseif (strlen($data['modele']) < 1) {
            $erreurs[] = 'Le modèle doit contenir au moins 1 caractère.';
        } elseif (strlen($data['modele']) > 50) {
            $erreurs[] = 'Le modèle ne peut pas dépasser 50 caractères.';
        }
        
        // Validation de la plaque d'immatriculation (obligatoire)
        if (empty($data['plaque_immatriculation'])) {
            $erreurs[] = 'La plaque d\'immatriculation est obligatoire.';
        } elseif (!preg_match('/^[A-Z]{2}-\d{3}-[A-Z]{2}$/', $data['plaque_immatriculation'])) {
            $erreurs[] = 'La plaque d\'immatriculation doit respecter le format français : AB-123-CD.';
        }
        
        // Validation de la couleur (optionnelle)
        if (!empty($data['couleur']) && strlen($data['couleur']) > 30) {
            $erreurs[] = 'La couleur ne peut pas dépasser 30 caractères.';
        }
        
        // Validation du nombre de places
        if (!isset($data['places_disponibles']) || $data['places_disponibles'] < 1 || $data['places_disponibles'] > 8) {
            $erreurs[] = 'Le nombre de places disponibles doit être entre 1 et 8.';
        }
        
        // Validation du type électrique (booléen)
        if (!isset($data['electrique']) || !in_array($data['electrique'], [0, 1])) {
            $data['electrique'] = 0; // Valeur par défaut
        }
        
        return $erreurs;
    }
    
    /**
     * Calcule la durée d'inscription de l'utilisateur
     * 
     * Cette méthode calcule depuis quand l'utilisateur est membre d'EcoRide
     * pour affichage dans le profil.
     * 
     * @param string $dateInscription Date d'inscription au format MySQL
     * @return string Durée formatée pour l'affichage
     */
    private function calculerDureeInscription($dateInscription)
    {
        if (!$dateInscription) {
            return 'Inconnu';
        }
        
        try {
            $inscription = new DateTime($dateInscription);
            $maintenant = new DateTime();
            $duree = $inscription->diff($maintenant);
            
            // Formatage selon la durée
            if ($duree->y > 0) {
                return $duree->y . ' an' . ($duree->y > 1 ? 's' : '');
            } elseif ($duree->m > 0) {
                return $duree->m . ' mois';
            } elseif ($duree->d > 0) {
                return $duree->d . ' jour' . ($duree->d > 1 ? 's' : '');
            } else {
                return 'Aujourd\'hui';
            }
            
        } catch (Exception $e) {
            error_log("Erreur calcul durée inscription : " . $e->getMessage());
            return 'Inconnu';
        }
    }
}
?>

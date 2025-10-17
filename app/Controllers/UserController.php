<?php
/**
 * UserController - Contrôleur pour la gestion du profil utilisateur EcoRide
 * 
 * Ce contrôleur gère toutes les opérations liées au profil utilisateur :
 * - Affichage du profil avec statistiques personnalisées et impact écologique
 * - Modification des informations personnelles avec validation sécurisée
 * - Gestion complète des véhicules (ajout, modification, suppression)
 * - Historique des activités (trajets proposés et réservations effectuées)
 * - Calcul de l'impact écologique personnel (CO₂ économisé)
 * - API AJAX pour une expérience utilisateur fluide
 */

class UserController
{
    /**
     * J'affiche le profil complet de l'utilisateur connecté
     */
    public function profil()
    {
        // Je vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour accéder à votre profil.';
            header('Location: /connexion');
            exit;
        }
        
        // J'utilise l'architecture centralisée
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/User.php';
        
        // Je récupère la connexion PDO globale
        global $pdo;
        
        // J'instancie le modèle User avec la connexion PDO
        $userModel = new User($pdo);
        
        // Je récupère les données utilisateur actualisées depuis la base
        $userData = $userModel->getUserById($_SESSION['user']['id']);
        
        if (!$userData) {
            $_SESSION['message'] = 'Erreur lors du chargement de votre profil.';
            header('Location: /');
            exit;
        }
        
        // Je mets à jour les données de session avec les informations actuelles
        $_SESSION['user'] = $userData;
        
        // Je prépare les variables pour la vue Bootstrap 5
        $title = "Mon profil | EcoRide - Votre espace personnel";
        $user = $userData;
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']); // Je nettoie après affichage
        
        // Je calcule les statistiques personnalisées pour l'affichage
        $stats = $this->getStatistiquesUtilisateur($userData['id']);
        
        // J'affiche la vue profil avec Bootstrap 5 et JavaScript
        require __DIR__ . '/../Views/user/profil.php';
    }
    
    /**
     * Je traite la modification du profil utilisateur via AJAX
     */
    public function modifierProfil()
    {
        // Je vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        // Je vérifie que c'est une requête POST pour sécurité
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée.']);
            return;
        }
        
        // Je récupère et nettoie les données du formulaire
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
        
        // Je valide les données côté serveur pour la sécurité
        $erreurs = $this->validerDonneesProfil($data, $_SESSION['user']['id']);
        
        if (!empty($erreurs)) {
            echo json_encode(['succes' => false, 'erreurs' => $erreurs]);
            return;
        }
        
        // J'utilise le modèle avec architecture centralisée
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/User.php';
        
        global $pdo;
        $userModel = new User($pdo);
        
        // Je mets à jour via le modèle User
        $resultat = $userModel->mettreAJourProfil($_SESSION['user']['id'], $data);
        
        if ($resultat['succes']) {
            // Je mets à jour les données de session avec les nouvelles informations
            foreach ($data as $key => $value) {
                $_SESSION['user'][$key] = $value;
            }
            
            // Je retourne une réponse JSON de succès pour le JavaScript
            echo json_encode([
                'succes' => true,
                'message' => 'Profil mis à jour avec succès !'
            ]);
        } else {
            // Je retourne une réponse JSON d'erreur pour le JavaScript
            echo json_encode([
                'succes' => false,
                'erreur' => $resultat['erreur']
            ]);
        }
    }
    
    /**
     * J'affiche l'historique complet des activités de l'utilisateur
     */
   /**
 * J'affiche l'historique complet des activités de l'utilisateur
 */
public function historique()
{
    // Je vérifie que l'utilisateur est connecté
    if (!isset($_SESSION['user'])) {
        $_SESSION['message'] = 'Vous devez être connecté pour voir votre historique.';
        header('Location: /EcoRide/public/connexion');
        exit;
    }
    
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../Models/Trip.php';
    
    global $pdo;
    $tripModel = new Trip($pdo);
    
    // Je récupère l'historique complet avec les nouvelles méthodes
    $trajetsProposesTermines = $tripModel->getHistoriqueTrajetsProposesUtilisateur($_SESSION['user']['id']);
    $reservationsTerminees = $tripModel->getHistoriqueReservationsUtilisateur($_SESSION['user']['id']);
    
    // Je calcule les statistiques d'historique
    $stats = [
        'total_trajets_proposes' => count($trajetsProposesTermines),
        'total_reservations' => count($reservationsTerminees),
        'total_km_conduits' => array_sum(array_column($trajetsProposesTermines, 'distance_km')),
        'total_km_voyages' => array_sum(array_column($reservationsTerminees, 'distance_km')),
        'credits_gagnes' => array_sum(array_column($trajetsProposesTermines, 'credits_gagnes')),
        'credits_depenses' => array_sum(array_column($reservationsTerminees, 'credits_depenses'))
    ];
    
    // Je calcule l'impact écologique total
    $totalKm = $stats['total_km_conduits'] + $stats['total_km_voyages'];
    $stats['co2_economise'] = round($totalKm * 0.12, 1);
    $stats['carburant_economise'] = round($totalKm * 0.07, 1);
    
    // Je prépare les variables pour la vue Bootstrap 5
    $title = "Mon historique | EcoRide - Votre impact écologique";
    $user = $_SESSION['user'];
    $message = $_SESSION['message'] ?? '';
    unset($_SESSION['message']);
    
    // J'affiche la vue historique avec Bootstrap 5
    require __DIR__ . '/../Views/user/historique.php';
}

    /**
     * J'ajoute un véhicule pour l'utilisateur connecté via AJAX
     */
    public function ajouterVehicule()
    {
        // Je vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        // Je vérifie que c'est une requête POST pour sécurité
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée.']);
            return;
        }
        
        // Je récupère les données du formulaire véhicule
        $data = [
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'plaque_immatriculation' => trim($_POST['plaque_immatriculation'] ?? ''),
            'places_disponibles' => intval($_POST['places_disponibles'] ?? 4),
            'electrique' => isset($_POST['electrique']) ? 1 : 0
        ];
        
        // Je valide les données véhicule
        $erreurs = $this->validerDonneesVehicule($data);
        
        if (!empty($erreurs)) {
            echo json_encode(['succes' => false, 'erreurs' => $erreurs]);
            return;
        }
        
        try {
            // J'utilise la connexion centralisée
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // J'insère le véhicule en base de données avec toutes les colonnes nécessaires
            $sql = "INSERT INTO vehicules (utilisateur_id, marque, modele, plaque_immatriculation, couleur, electrique, nb_places, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql);
            $resultat = $stmt->execute([
                $_SESSION['user']['id'],
                $data['marque'],
                $data['modele'],
                $data['plaque_immatriculation'],
                $data['couleur'],
                $data['electrique'],
                $data['places_disponibles'] // Le formulaire envoie places_disponibles mais je l'insère dans nb_places
            ]);

            if ($resultat) {
                // Je prépare un message de succès avec encouragement écologique
                $message = 'Véhicule ajouté avec succès !';
                if ($data['electrique']) {
                    $message .= ' Merci de contribuer à la mobilité écologique !';
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
     * Je récupère les véhicules de l'utilisateur connecté via AJAX
     */
    public function mesVehicules()
    {
        // Je vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        try {
            // J'utilise la connexion centralisée
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Je récupère les véhicules de l'utilisateur avec tri par date
            $sql = "SELECT * FROM vehicules WHERE utilisateur_id = ? ORDER BY created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user']['id']]);
            
            $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // J'enrichis les données pour l'affichage
            foreach ($vehicules as &$vehicule) {
                $vehicule['date_ajout_formatee'] = date('d/m/Y', strtotime($vehicule['created_at']));
                $vehicule['badge_ecologique'] = $vehicule['electrique'] ? 'Véhicule électrique' : null;
            }
            
            // Je retourne une réponse JSON avec la liste des véhicules
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
     * Je supprime un véhicule de l'utilisateur via AJAX
     */
    public function supprimerVehicule($vehiculeId)
    {
        // Je vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        // Je valide l'ID du véhicule
        $vehiculeId = intval($vehiculeId);
        if ($vehiculeId <= 0) {
            echo json_encode(['succes' => false, 'erreur' => 'ID de véhicule invalide.']);
            return;
        }
        
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Je supprime de façon sécurisée avec vérification de propriété
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
     * Je modifie un véhicule existant via AJAX
     */
    public function modifierVehicule($vehiculeId)
    {
        // Je vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        // Je vérifie que c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée.']);
            return;
        }
        
        // Je valide l'ID du véhicule
        $vehiculeId = intval($vehiculeId);
        if ($vehiculeId <= 0) {
            echo json_encode(['succes' => false, 'erreur' => 'ID de véhicule invalide.']);
            return;
        }
        
        // Je récupère les données de modification
        $data = [
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'plaque_immatriculation' => trim($_POST['plaque_immatriculation'] ?? ''),
            'places_disponibles' => intval($_POST['places_disponibles'] ?? 4),
            'electrique' => isset($_POST['electrique']) ? 1 : 0
        ];
        
        // Je valide les données
        $erreurs = $this->validerDonneesVehicule($data);
        
        if (!empty($erreurs)) {
            echo json_encode(['succes' => false, 'erreurs' => $erreurs]);
            return;
        }
        
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Je mets à jour de façon sécurisée avec vérification de propriété
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
     * Je récupère les statistiques de crédits de l'utilisateur via AJAX
     */
    public function historiqueCredits()
    {
        // Je vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            return;
        }
        
        try {
            // J'utilise la connexion centralisée
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Je récupère l'historique des transactions de crédits
            $sql = "SELECT tc.*, t.lieu_depart, t.lieu_arrivee 
                    FROM transactions_credits tc
                    LEFT JOIN trajets t ON tc.trajet_id = t.id
                    WHERE tc.utilisateur_id = ?
                    ORDER BY tc.date_transaction DESC
                    LIMIT 20";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user']['id']]);
            
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // J'enrichis les données pour l'affichage
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
     * Je récupère les statistiques personnalisées de l'utilisateur
     */
    private function getStatistiquesUtilisateur($userId)
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Je compte le nombre de trajets proposés par l'utilisateur
            $sql = "SELECT COUNT(*) FROM trajets WHERE conducteur_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $trajetsProposés = $stmt->fetchColumn();
            
            // Je compte le nombre de réservations effectuées par l'utilisateur
            $sql = "SELECT COUNT(*) FROM reservations WHERE passager_id = ? AND statut = 'confirme'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $reservationsEffectuées = $stmt->fetchColumn();
            
            // Je calcule le total des crédits gagnés
            $sql = "SELECT COALESCE(SUM(montant), 0) FROM transactions_credits 
                    WHERE utilisateur_id = ? AND type_transaction = 'credit' AND source != 'inscription'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $creditsGagnés = $stmt->fetchColumn();
            
            // Je calcule le total des crédits dépensés
            $sql = "SELECT COALESCE(SUM(ABS(montant)), 0) FROM transactions_credits 
                    WHERE utilisateur_id = ? AND type_transaction = 'debit'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $creditsDépensés = $stmt->fetchColumn();
            
            // Je récupère la date d'inscription pour calculer l'ancienneté
            $sql = "SELECT created_at FROM utilisateurs WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $dateInscription = $stmt->fetchColumn();
            
            // Je calcule l'impact écologique personnel
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
            
            // Je retourne des statistiques par défaut en cas d'erreur
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
     * Je calcule l'impact écologique personnel de l'utilisateur
     */
    private function calculerImpactEcologique($userId)
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Je calcule les kilomètres partagés via les trajets proposés
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
            
            // Je calcule les kilomètres partagés via les réservations effectuées
            $sql = "SELECT COALESCE(SUM(t.distance_km * r.nb_places), 0) as km_reservations
                    FROM reservations r
                    JOIN trajets t ON r.trajet_id = t.id
                    WHERE r.passager_id = ? AND r.statut = 'confirme'";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $kmReservations = $stmt->fetchColumn();
            
            // Je calcule le total des kilomètres partagés
            $kmPartages = $kmTrajets + $kmReservations;
            
            // Je calcule l'impact écologique selon l'énoncé EcoRide
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
     * Je valide les données de modification du profil
     */
    private function validerDonneesProfil($data, $userId)
    {
        $erreurs = [];
        
        // Je valide le pseudo
        if (empty($data['pseudo'])) {
            $erreurs[] = 'Le pseudo est obligatoire.';
        } elseif (strlen($data['pseudo']) < 3) {
            $erreurs[] = 'Le pseudo doit contenir au moins 3 caractères.';
        } elseif (strlen($data['pseudo']) > 50) {
            $erreurs[] = 'Le pseudo ne peut pas dépasser 50 caractères.';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['pseudo'])) {
            $erreurs[] = 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.';
        }
        
        // Je valide l'email
        if (empty($data['email'])) {
            $erreurs[] = 'L\'adresse email est obligatoire.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'L\'adresse email n\'est pas valide.';
        } elseif (strlen($data['email']) > 255) {
            $erreurs[] = 'L\'adresse email est trop longue.';
        }
        
        // Je valide le téléphone (optionnel)
        if (!empty($data['telephone']) && !preg_match('/^[0-9+\-\s\.]{10,15}$/', $data['telephone'])) {
            $erreurs[] = 'Le numéro de téléphone n\'est pas valide.';
        }
        
        // Je valide la bio (optionnelle)
        if (!empty($data['bio']) && strlen($data['bio']) > 500) {
            $erreurs[] = 'La biographie ne peut pas dépasser 500 caractères.';
        }
        
        // Je valide le code postal (optionnel)
        if (!empty($data['code_postal']) && !preg_match('/^\d{5}$/', $data['code_postal'])) {
            $erreurs[] = 'Le code postal doit contenir exactement 5 chiffres.';
        }
        
        // Je vérifie l'unicité du pseudo et email
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Je vérifie l'unicité du pseudo
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE pseudo = ? AND id != ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['pseudo'], $userId]);
            if ($stmt->fetchColumn() > 0) {
                $erreurs[] = 'Ce pseudo est déjà utilisé par un autre utilisateur.';
            }
            
            // Je vérifie l'unicité de l'email
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
     * Je valide les données d'un véhicule
     */
    private function validerDonneesVehicule($data)
    {
        $erreurs = [];
        
        // Je valide la marque
        if (empty($data['marque'])) {
            $erreurs[] = 'La marque est obligatoire.';
        } elseif (strlen($data['marque']) < 2) {
            $erreurs[] = 'La marque doit contenir au moins 2 caractères.';
        } elseif (strlen($data['marque']) > 50) {
            $erreurs[] = 'La marque ne peut pas dépasser 50 caractères.';
        }
        
        // Je valide le modèle
        if (empty($data['modele'])) {
            $erreurs[] = 'Le modèle est obligatoire.';
        } elseif (strlen($data['modele']) < 1) {
            $erreurs[] = 'Le modèle doit contenir au moins 1 caractère.';
        } elseif (strlen($data['modele']) > 50) {
            $erreurs[] = 'Le modèle ne peut pas dépasser 50 caractères.';
        }
        
        // Je valide la plaque d'immatriculation
        if (empty($data['plaque_immatriculation'])) {
            $erreurs[] = 'La plaque d\'immatriculation est obligatoire.';
        } elseif (!preg_match('/^[A-Z]{2}-\d{3}-[A-Z]{2}$/', $data['plaque_immatriculation'])) {
            $erreurs[] = 'La plaque d\'immatriculation doit respecter le format français : AB-123-CD.';
        }
        
        // Je valide la couleur
        if (!empty($data['couleur']) && strlen($data['couleur']) > 30) {
            $erreurs[] = 'La couleur ne peut pas dépasser 30 caractères.';
        }
        
        // Je valide le nombre de places
        if (!isset($data['places_disponibles']) || $data['places_disponibles'] < 1 || $data['places_disponibles'] > 8) {
            $erreurs[] = 'Le nombre de places disponibles doit être entre 1 et 8.';
        }
        
        // Je valide le type électrique
        if (!isset($data['electrique']) || !in_array($data['electrique'], [0, 1])) {
            $data['electrique'] = 0; // Valeur par défaut
        }
        
        return $erreurs;
    }
    
    /**
     * Je calcule la durée d'inscription de l'utilisateur
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
            
            // Je formate selon la durée
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

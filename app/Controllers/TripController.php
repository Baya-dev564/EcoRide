<?php
/**
 * TripController - Contrôleur pour la gestion des trajets de covoiturage EcoRide
 * Je gère tous les trajets avec géolocalisation, système de notation et workflow complet
 */

class TripController
{
    private $tripModel;
    
    /**
     * Je constructeur avec injection de dépendance
     * J'initialise le service de géolocalisation et la connexion base de données
     */
    public function __construct()
    {
        // J'inclus le service de géolocalisation pour les coordonnées GPS
        require_once __DIR__ . '/../Services/GeolocationService.php';
        
        // J'initialise la connexion base de données
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Trip.php';
        
        global $pdo;
        $this->tripModel = new Trip($pdo);
    }
    
    /**
     * J'affiche la page de recherche avec tous les filtres fonctionnels
     * Page de recherche des trajets avec résultats filtrés, tri dynamique et pagination
     */
    public function index()
    {
        // Je récupère tous les critères de recherche
        $criteres = [
            'lieu_depart' => $this->validerLieu($_GET['lieu_depart'] ?? ''),
            'lieu_arrivee' => $this->validerLieu($_GET['lieu_arrivee'] ?? ''),
            'date_depart' => $this->validerDate($_GET['date_depart'] ?? ''),
            'vehicule_electrique' => isset($_GET['vehicule_electrique']) ? true : false,
            // Je gère les filtres avancés
            'prix_min' => !empty($_GET['prix_min']) ? (int)$_GET['prix_min'] : '',
            'prix_max' => !empty($_GET['prix_max']) ? (int)$_GET['prix_max'] : '',
            'note_min' => !empty($_GET['note_min']) ? (float)$_GET['note_min'] : '',
            // Je paramètre le tri dynamique
            'tri' => $_GET['tri'] ?? 'date_depart',
            'direction' => $_GET['direction'] ?? 'ASC'
        ];
        
        // Je gère la pagination avec validation
        $page = max(1, min(100, (int)($_GET['page'] ?? 1)));
        $limit = 8; // Je définis le nombre de trajets par page
        
        try {
            // Je recherche des trajets via le modèle
            $resultats = $this->tripModel->rechercherTrajets($criteres, $page, $limit);
            
            if (!$resultats['succes']) {
                throw new Exception($resultats['erreur'] ?? 'Erreur de recherche');
            }
            
            $trajets = $resultats['trajets'];
            $pagination = $resultats['pagination'];
            
            // Je calcule la pagination
            if ($pagination) {
                $pagination['total_pages'] = ceil($pagination['total_trajets'] / $limit);
                $pagination['page_actuelle'] = $page;
            }
            
            // Je calcule les statistiques pour l'affichage
            $stats = $this->calculerStatistiques($trajets, $pagination['total_trajets']);
            
        } catch (Exception $e) {
            // Je gère les erreurs de façon robuste avec logs
            error_log("Erreur recherche trajets : " . $e->getMessage());
            $trajets = [];
            $pagination = null;
            $stats = ['total_trajets' => 0, 'trajets_electriques' => 0, 'prix_moyen' => 0];
            $_SESSION['erreur'] = 'Erreur lors de la recherche des trajets.';
        }
        
        // J'affiche la vue avec toutes les variables
        $this->afficherVueIndex($criteres, $trajets, $pagination, $stats);
    }
    
    /**
     * Je centralise la préparation des variables pour la vue index
     */
    private function afficherVueIndex($criteres, $trajets, $pagination, $stats)
    {
        // Je définis les métadonnées de la page
        $title = "Recherche de trajets | EcoRide - Covoiturage écologique";
        
        // J'indique les indicateurs pour la vue
        $hasSearch = !empty($criteres['lieu_depart']) || !empty($criteres['lieu_arrivee']) || !empty($criteres['date_depart']);
        
        // Je récupère les messages système
        $message = $_SESSION['message'] ?? '';
        $erreur = $_SESSION['erreur'] ?? '';
        unset($_SESSION['message'], $_SESSION['erreur']);
        
        require __DIR__ . '/../Views/trips/index.php';
    }
    
    /**
     * Je calcule les statistiques d'affichage
     */
    private function calculerStatistiques($trajets, $totalTrajets)
    {
        $stats = [
            'total_trajets' => $totalTrajets,
            'trajets_electriques' => 0,
            'prix_moyen' => 0
        ];
        
        if (!empty($trajets)) {
            // Je compte les trajets écologiques
            $stats['trajets_electriques'] = count(array_filter($trajets, function($t) { 
                return $t['vehicule_electrique']; 
            }));
            
            // Je calcule le prix moyen
            $prix_total = array_sum(array_column($trajets, 'prix'));
            $stats['prix_moyen'] = $prix_total > 0 ? round($prix_total / count($trajets)) : 0;
        }
        
        return $stats;
    }
    
    /**
     * J'affiche les détails complets d'un trajet avec bouton "Noter ce trajet" si terminé
     */
    public function details($trajetId = null)
    {
        // Si je n'ai pas d'ID passé en paramètre, j'extrais de l'URL
        if ($trajetId === null) {
            $trajetId = $this->extraireIdDepuisUrl('trajet');
        }
        
        if (!$trajetId) {
            $_SESSION['erreur'] = 'Trajet non trouvé.';
            header('Location: /EcoRide/public/trajets');
            exit;
        }
        
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Trip.php';
        
        global $pdo;
        $tripModel = new Trip($pdo);
        
        $trajet = $tripModel->getTrajetDetails($trajetId);
        
        if (!$trajet) {
            $_SESSION['erreur'] = 'Ce trajet n\'existe pas ou n\'est plus disponible.';
            header('Location: /EcoRide/public/trajets');
            exit;
        }
        
        $userConnecte = $_SESSION['user'] ?? null;
        $peutReserver = false;
        
        // Je vérifie si l'utilisateur peut noter ce trajet
        $peutNoter = false;
        $dejaNote = false;
        
        if ($userConnecte) {
            $peutReserver = ($userConnecte['id'] != $trajet['conducteur_id']) 
                         && ($trajet['places_disponibles'] > 0)
                         && ($userConnecte['credit'] >= $trajet['prix'])
                         && !$this->aDejaReserve($trajetId, $userConnecte['id']);
            
            // Je vérifie les conditions pour noter
            if ($trajet['statut'] === 'termine') {
                // Je peux noter si : passager de ce trajet OU conducteur peut noter les passagers
                $peutNoter = $this->aParticiipeAuTrajet($trajetId, $userConnecte['id']);
                $dejaNote = $this->aDejaNote($trajetId, $userConnecte['id']);
            }
        }
        
        $message = $_SESSION['message'] ?? '';
        $erreur = $_SESSION['erreur'] ?? '';
        unset($_SESSION['message'], $_SESSION['erreur']);
        
        $title = "Trajet {$trajet['lieu_depart']} → {$trajet['lieu_arrivee']} | EcoRide";
        
        require __DIR__ . '/../Views/trips/details.php';
    }

    /**
     * J'affiche les trajets que l'utilisateur peut noter
     */
    public function trajetsANoter()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour voir vos trajets à noter.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
        
        $userId = $_SESSION['user']['id'];
        
        try {
            // Je récupère les trajets terminés que l'utilisateur peut noter
            $trajets = $this->tripModel->getTrajetsANoter($userId);
            
            // Je prépare les variables pour la vue
            $title = "Trajets à noter | EcoRide - Donnez votre avis";
            $user = $_SESSION['user'];
            $message = $_SESSION['message'] ?? '';
            $erreur = $_SESSION['erreur'] ?? '';
            unset($_SESSION['message'], $_SESSION['erreur']);
            
            require __DIR__ . '/../Views/trips/trajets-a-noter.php';
            
        } catch (Exception $e) {
            error_log("Erreur trajetsANoter : " . $e->getMessage());
            $_SESSION['erreur'] = 'Erreur lors de la récupération des trajets à noter.';
            header('Location: /EcoRide/public/mes-trajets');
            exit;
        }
    }

    /**
     * Je marque un trajet comme terminé (pour le conducteur)
     */
    public function terminerTrajet($trajetId = null)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['erreur'] = 'Vous devez être connecté.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /EcoRide/public/mes-trajets');
            exit;
        }
        
        if ($trajetId === null) {
            $trajetId = $this->extraireIdDepuisUrl('trajet');
        }
        
        if (!$trajetId) {
            $_SESSION['erreur'] = 'Trajet non trouvé.';
            header('Location: /EcoRide/public/mes-trajets');
            exit;
        }
        
        try {
            // Je vérifie que c'est bien le conducteur du trajet
            $trajet = $this->tripModel->getTrajetDetails($trajetId);
            
            if (!$trajet || $trajet['conducteur_id'] != $_SESSION['user']['id']) {
                $_SESSION['erreur'] = 'Trajet non trouvé ou vous n\'êtes pas le conducteur.';
                header('Location: /EcoRide/public/mes-trajets');
                exit;
            }
            
            if ($trajet['statut'] === 'termine') {
                $_SESSION['message'] = 'Ce trajet est déjà marqué comme terminé.';
                header('Location: /EcoRide/public/mes-trajets');
                exit;
            }
            
            // Je marque le trajet comme terminé
            $resultat = $this->tripModel->marquerCommeTermine($trajetId);
            
            if ($resultat['succes']) {
                $_SESSION['message'] = 'Trajet marqué comme terminé ! Les passagers peuvent maintenant vous noter.';
            } else {
                $_SESSION['erreur'] = $resultat['erreur'] ?? 'Erreur lors de la terminaison du trajet.';
            }
            
        } catch (Exception $e) {
            error_log("Erreur terminerTrajet : " . $e->getMessage());
            $_SESSION['erreur'] = 'Erreur technique lors de la terminaison du trajet.';
        }
        
        header('Location: /EcoRide/public/mes-trajets');
        exit;
    }

    /**
     * Je redirige vers le formulaire de notation avec les bonnes données
     */
    public function noterTrajet($trajetId = null)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour noter un trajet.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
        
        if ($trajetId === null) {
            $trajetId = $this->extraireIdDepuisUrl('noter-trajet');
        }
        
        if (!$trajetId) {
            $_SESSION['erreur'] = 'Trajet non trouvé.';
            header('Location: /EcoRide/public/mes-trajets-a-noter');
            exit;
        }
        
        try {
            $userId = $_SESSION['user']['id'];
            
            // Je vérifie que l'utilisateur peut noter ce trajet
            $trajet = $this->tripModel->getTrajetDetails($trajetId);
            
            if (!$trajet) {
                $_SESSION['erreur'] = 'Trajet non trouvé.';
                header('Location: /EcoRide/public/mes-trajets-a-noter');
                exit;
            }
            
            if ($trajet['statut'] !== 'termine') {
                $_SESSION['erreur'] = 'Ce trajet n\'est pas encore terminé.';
                header('Location: /EcoRide/public/trajet/' . $trajetId);
                exit;
            }
            
            // Je vérifie que l'utilisateur a participé à ce trajet
            if (!$this->aParticiipeAuTrajet($trajetId, $userId)) {
                $_SESSION['erreur'] = 'Vous n\'avez pas participé à ce trajet.';
                header('Location: /EcoRide/public/mes-trajets-a-noter');
                exit;
            }
            
            // Je vérifie qu'il n'a pas déjà noté
            if ($this->aDejaNote($trajetId, $userId)) {
                $_SESSION['message'] = 'Vous avez déjà noté ce trajet.';
                header('Location: /EcoRide/public/mes-trajets-a-noter');
                exit;
            }
            
            // Je détermine qui il doit noter
            $conducteur_id = ($userId == $trajet['conducteur_id']) ? null : $trajet['conducteur_id'];
            
            // Je redirige vers le formulaire d'avis avec les paramètres
            $params = http_build_query([
                'trajet_id' => $trajetId,
                'conducteur_id' => $conducteur_id,
                'lieu_depart' => $trajet['lieu_depart'],
                'lieu_arrivee' => $trajet['lieu_arrivee'],
                'date_trajet' => $trajet['date_depart']
            ]);
            
            header('Location: /EcoRide/public/donner-avis?' . $params);
            exit;
            
        } catch (Exception $e) {
            error_log("Erreur noterTrajet : " . $e->getMessage());
            $_SESSION['erreur'] = 'Erreur technique lors de l\'accès au formulaire de notation.';
            header('Location: /EcoRide/public/mes-trajets-a-noter');
            exit;
        }
    }

    /**
     * Je vérifie si un utilisateur a participé à un trajet
     */
    private function aParticiipeAuTrajet($trajetId, $userId)
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            // Je vérifie s'il est conducteur
            $sql = "SELECT conducteur_id FROM trajets WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$trajetId]);
            $trajet = $stmt->fetch();
            
            if ($trajet && $trajet['conducteur_id'] == $userId) {
                return true; // Il est le conducteur
            }
            
            // Je vérifie s'il est passager avec réservation confirmée
            $sql = "SELECT COUNT(*) FROM reservations 
                    WHERE trajet_id = ? AND passager_id = ? AND statut = 'confirme'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$trajetId, $userId]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            error_log("Erreur aParticiipeAuTrajet : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Je vérifie si un utilisateur a déjà noté un trajet
     */
    private function aDejaNote($trajetId, $userId)
    {
        try {
            // J'inclus le modèle MongoDB pour les avis
            require_once __DIR__ . '/../Models/avis-mongo.php';
            
            $avisMongo = new AvisMongo();
            
            // J'utilise getAvisParTrajet pour voir s'il y a des avis
            $resultats = $avisMongo->getAvisParTrajet($trajetId);
            
            if ($resultats['success']) {
                // Je cherche si cet utilisateur a déjà noté ce trajet
                foreach ($resultats['avis'] as $avis) {
                    if (isset($avis['utilisateur_id']) && $avis['utilisateur_id'] == $userId) {
                        return true; // Il a déjà noté ce trajet
                    }
                }
            }
            
            return false; // Pas d'avis trouvé de cet utilisateur pour ce trajet
            
        } catch (Exception $e) {
            error_log("Erreur aDejaNote : " . $e->getMessage());
            return false; // En cas d'erreur, je permets la notation
        }
    }

    /**
     * J'affiche les trajets avec les statuts des réservations
     */
    public function mesTrajets()
{
    // Je vérifie l'authentification
    if (!isset($_SESSION['user'])) {
        $_SESSION['message'] = "Vous devez être connecté pour voir vos trajets.";
        header("Location: EcoRide/public/connexion");
        exit;
    }
    
    // J'appelle la méthode du modèle Trip.php
    $trajets = $this->tripModel->getTrajetsUtilisateurAvecStatuts($_SESSION['user']['id']);
    
    // Je prépare les variables pour la vue
    $title = "Mes trajets EcoRide - Gestion de vos trajets proposés";
    $user = $_SESSION['user'];
    
    // Je récupère le message de session s'il existe
    $message = $_SESSION['message'] ?? null;
    unset($_SESSION['message']);
    
    require __DIR__ . '/../Views/trips/mes-trajets.php';
}


    /**
     * J'affiche le formulaire de création avec gestion des véhicules
     */
    public function nouveauTrajet()
    {
        // Je vérifie l'authentification obligatoire
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour proposer un trajet.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
        
        if (empty($_SESSION['user']['permis_conduire'])) {
            $_SESSION['erreur'] = 'Vous devez avoir un permis de conduire validé pour proposer un trajet.';
            header('Location: /EcoRide/public/profil');
            exit;
        }
        
        // Je récupère les véhicules de l'utilisateur
        $vehicules = $this->recupererVehiculesUtilisateur($_SESSION['user']['id']);
        
        // Je prépare les variables pour la vue avec gestion des erreurs
        $title = "Proposer un trajet | EcoRide - Partagez votre trajet";
        $user = $_SESSION['user'];
        $erreurs = $_SESSION['erreurs_trajet'] ?? [];
        $donnees = $_SESSION['donnees_trajet'] ?? [];
        $message = $_SESSION['message'] ?? '';
        
        // Je nettoie les variables de session
        unset($_SESSION['erreurs_trajet'], $_SESSION['donnees_trajet'], $_SESSION['message']);
        
        require __DIR__ . '/../Views/trips/nouveau-trajet.php';
    }

    /**
     * API : Je recherche des lieux pour l'autocomplete
     */
    public function apiSearchPlaces(): void
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètre q manquant']);
            return;
        }
        
        try {
            $placesService = new \App\Services\PlacesService();
            $places = $placesService->searchPlaces($query);
            
            header('Content-Type: application/json');
            echo json_encode($places);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }

    /**
     * API : Je récupère les détails d'un lieu spécifique
     */
    public function apiPlaceDetails()
    {
        header('Content-Type: application/json');
        
        try {
            $placeId = $_GET['id'] ?? '';
            
            if (empty($placeId)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID manquant']);
                exit;
            }
            
            // Pour les lieux API, je retourne les infos de base
            if (strpos($placeId, 'api_') === 0) {
                echo json_encode([
                    'id' => $placeId,
                    'status' => 'API place - détails limités'
                ]);
            } else {
                echo json_encode(['error' => 'Lieu non trouvé']);
            }
            
        } catch (Exception $e) {
            error_log("Erreur API détails lieu : " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur technique']);
        }
        
        exit;
    }
    
    /**
     * Je récupère les véhicules d'un utilisateur
     */
    private function recupererVehiculesUtilisateur($userId)
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            $sql = "SELECT id, marque, modele, couleur, electrique, plaque_immatriculation 
                    FROM vehicules 
                    WHERE utilisateur_id = ? 
                    ORDER BY marque, modele";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération véhicules : " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Je traite la création avec validation centralisée et géolocalisation
     */
    public function creerTrajet()
    {
        // Je vérifie les prérequis
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour proposer un trajet.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
        
        if (empty($_SESSION['user']['permis_conduire'])) {
            $_SESSION['erreur'] = 'Vous devez avoir un permis de conduire validé pour proposer un trajet.';
            header('Location: /EcoRide/public/profil');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /EcoRide/public/nouveau-trajet');
            exit;
        }
        
        // Je valide les données
        $data = $this->extraireDonneesTrajet($_POST);
        $erreurs = $this->validerDonneesControleur($data);
        
        if (!empty($erreurs)) {
            $_SESSION['erreurs_trajet'] = $erreurs;
            $_SESSION['donnees_trajet'] = $data;
            header('Location: /EcoRide/public/nouveau-trajet');
            exit;
        }
        
        // Je géocode les adresses pour obtenir les coordonnées GPS
        $geoService = new GeolocationService();
        $departCoords = $geoService->geocodeAddress($data['lieu_depart'], $data['code_postal_depart']);
        $arriveeCoords = $geoService->geocodeAddress($data['lieu_arrivee'], $data['code_postal_arrivee']);
        
        // J'ajoute les coordonnées aux données si je les ai trouvées
        if ($departCoords) {
            $data['depart_latitude'] = $departCoords['latitude'];
            $data['depart_longitude'] = $departCoords['longitude'];
        }
        
        if ($arriveeCoords) {
            $data['arrivee_latitude'] = $arriveeCoords['latitude'];
            $data['arrivee_longitude'] = $arriveeCoords['longitude'];
        }
        
        // Je calcule la distance si j'ai les deux coordonnées
        if ($departCoords && $arriveeCoords) {
            $data['distance_km'] = $this->calculerDistance(
                $departCoords['latitude'], $departCoords['longitude'],
                $arriveeCoords['latitude'], $arriveeCoords['longitude']
            );
        }
        
        // Je crée le trajet via le modèle
        $resultat = $this->tripModel->creerTrajet($data);
        
        if ($resultat['succes']) {
            $_SESSION['message'] = "Votre trajet est en cours de modération par l'administrateur. Prix calculé : {$resultat['prix_calcule']} crédits.";
            header('Location: /EcoRide/public/mes-trajets');
            exit;
        } else {
            $_SESSION['erreurs_trajet'] = $resultat['erreurs'];
            $_SESSION['donnees_trajet'] = $data;
            header('Location: /EcoRide/public/nouveau-trajet');
            exit;
        }
    }
    
    /**
     * Je calcule la distance entre deux coordonnées GPS avec la formule de Haversine
     */
    private function calculerDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Je convertis les degrés en radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        
        // Je calcule les différences
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        
        // J'applique la formule de Haversine
        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        // Je retourne la distance en kilomètres (rayon terrestre = 6371 km)
        return round(6371 * $c, 2);
    }
    
    /**
     * J'extrais et nettoie les données du formulaire
     */
    private function extraireDonneesTrajet($post)
    {
        return [
            'conducteur_id' => $_SESSION['user']['id'],
            'vehicule_id' => intval($post['vehicule_id'] ?? 0) ?: null,
            'lieu_depart' => trim($post['lieu_depart'] ?? ''),
            'code_postal_depart' => trim($post['code_postal_depart'] ?? ''),
            'lieu_arrivee' => trim($post['lieu_arrivee'] ?? ''),
            'code_postal_arrivee' => trim($post['code_postal_arrivee'] ?? ''),
            'date_depart' => $post['date_depart'] ?? '',
            'heure_depart' => $post['heure_depart'] ?? '',
            'places' => intval($post['places'] ?? 1),
            'vehicule_electrique' => isset($post['vehicule_electrique']),
            'commentaire' => trim($post['commentaire'] ?? '')
        ];
    }
    
    /**
     * Je valide côté contrôleur (validation rapide)
     */
    private function validerDonneesControleur($data)
    {
        $erreurs = [];
        
        // Je fais les validations essentielles côté contrôleur
        if (empty($data['lieu_depart'])) {
            $erreurs[] = 'Le lieu de départ est obligatoire.';
        }
        
        if (empty($data['lieu_arrivee'])) {
            $erreurs[] = 'Le lieu d\'arrivée est obligatoire.';
        }
        
        if (empty($data['date_depart']) || empty($data['heure_depart'])) {
            $erreurs[] = 'La date et l\'heure de départ sont obligatoires.';
        }
        
        if ($data['places'] < 1 || $data['places'] > 8) {
            $erreurs[] = 'Le nombre de places doit être entre 1 et 8.';
        }
        
        return $erreurs;
    }
    
    /**
     * API pour la recherche AJAX avec gestion complète
     */
    public function apiRecherche()
    {
        // Je paramètre les headers pour API JSON
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        // Je récupère tous les critères
        $criteres = [
            'lieu_depart' => $this->validerLieu($_GET['lieu_depart'] ?? ''),
            'lieu_arrivee' => $this->validerLieu($_GET['lieu_arrivee'] ?? ''),
            'date_depart' => $this->validerDate($_GET['date_depart'] ?? ''),
            'vehicule_electrique' => isset($_GET['vehicule_electrique']),
            'prix_min' => !empty($_GET['prix_min']) ? (int)$_GET['prix_min'] : '',
            'prix_max' => !empty($_GET['prix_max']) ? (int)$_GET['prix_max'] : '',
            'note_min' => !empty($_GET['note_min']) ? (float)$_GET['note_min'] : ''
        ];
        
        try {
            $resultats = $this->tripModel->rechercherTrajets($criteres, 1, 20);
            
            if ($resultats['succes']) {
                echo json_encode([
                    'success' => true,
                    'trajets' => $resultats['trajets'],
                    'total' => $resultats['pagination']['total_trajets'],
                    'timestamp' => time()
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erreur lors de la recherche des trajets'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Erreur API recherche : " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Erreur technique'
            ]);
        }
        
        exit;
    }
    
    /**
     * J'extrais l'ID depuis l'URL pour le routing
     */
    private function extraireIdDepuisUrl($route)
    {
        $uri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', trim($uri, '/'));
        
        foreach ($segments as $key => $segment) {
            if ($segment === $route && isset($segments[$key + 1])) {
                return (int)$segments[$key + 1];
            }
        }
        
        return null;
    }
    
    /**
     * Je vérifie si un utilisateur a déjà réservé un trajet
     */
    private function aDejaReserve($trajetId, $userId)
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            $sql = "SELECT COUNT(*) FROM reservations 
                    WHERE trajet_id = ? AND passager_id = ? AND statut = 'confirme'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$trajetId, $userId]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            error_log("Erreur vérification réservation : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Je vérifie si l'utilisateur peut réserver un trajet
     */
    private function peutReserverTrajet($trajet, $user)
    {
        if (!$user) {
            return false; // Je refuse si utilisateur non connecté
        }
        
        if ($user['id'] == $trajet['conducteur_id']) {
            return false; // Je refuse si conducteur veut réserver son propre trajet
        }
        
        if ($trajet['places_disponibles'] <= 0) {
            return false; // Je refuse si plus de places disponibles
        }
        
        if ($this->aDejaReserve($trajet['id'], $user['id'])) {
            return false; // Je refuse si déjà réservé
        }
        
        return true;
    }
    
    /**
     * Je valide et nettoie un lieu de recherche
     */
    private function validerLieu($lieu)
    {
        $lieu = trim($lieu);
        return strlen($lieu) >= 2 && strlen($lieu) <= 100 ? $lieu : '';
    }
    
    /**
     * Je valide une date de recherche
     */
    private function validerDate($date)
    {
        if (empty($date)) return '';
        
        $timestamp = strtotime($date);
        return $timestamp && $timestamp >= strtotime('today') ? $date : '';
    }
}
?>

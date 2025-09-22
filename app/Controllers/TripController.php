<?php
/**
 * Contrôleur pour la gestion des trajets de covoiturage EcoRide avec géolocalisation
 * Je gère tous les trajets avec coordonnées GPS et calcul automatique des distances
 */

class TripController
{
    private $tripModel;
    
    /**
     * Constructeur avec injection de dépendance
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
     * Page de recherche avec tous les filtres fonctionnels
     * J'affiche la page de recherche des trajets avec résultats filtrés,
     * tri dynamique et pagination complète
     */
    public function index()
    {
        // Je récupère complètement tous les critères de recherche
        $criteres = [
            'lieu_depart' => $this->validerLieu($_GET['lieu_depart'] ?? ''),
            'lieu_arrivee' => $this->validerLieu($_GET['lieu_arrivee'] ?? ''),
            'date_depart' => $this->validerDate($_GET['date_depart'] ?? ''),
            'vehicule_electrique' => isset($_GET['vehicule_electrique']) ? true : false,
            // Je gère les filtres avancés fonctionnels
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
            
            // Je calcule complètement la pagination
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
     * Méthode dédiée pour afficher la vue index
     * Je centralise la préparation des variables pour la vue
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
     * 
     * @param array $trajets Liste des trajets trouvés
     * @param int $totalTrajets Nombre total de trajets
     * @return array Statistiques formatées
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
     * J'affiche les détails complets d'un trajet
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
        
        if ($userConnecte) {
            $peutReserver = ($userConnecte['id'] != $trajet['conducteur_id']) 
                         && ($trajet['places_disponibles'] > 0)
                         && ($userConnecte['credit'] >= $trajet['prix'])
                         && !$this->aDejaReserve($trajetId, $userConnecte['id']);
        }
        
        $message = $_SESSION['message'] ?? '';
        $erreur = $_SESSION['erreur'] ?? '';
        unset($_SESSION['message'], $_SESSION['erreur']);
        
        $title = "Trajet {$trajet['lieu_depart']} → {$trajet['lieu_arrivee']} | EcoRide";
        
        require __DIR__ . '/../Views/trips/details.php';
    }

    /**
     * Je vérifie si l'utilisateur peut réserver un trajet
     * 
     * @param array $trajet Données du trajet
     * @param array|null $user Utilisateur connecté
     * @return bool True si la réservation est possible
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
     * J'affiche les trajets de l'utilisateur connecté
     * Je gère la page de gestion personnelle des trajets proposés
     */
    public function mesTrajets()
    {
        // Je vérifie l'authentification
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour voir vos trajets.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
        
        // Je récupère les trajets de l'utilisateur
        $trajets = $this->tripModel->getTrajetsUtilisateur($_SESSION['user']['id']);
        
        // Je prépare les variables pour la vue
        $title = "Mes trajets | EcoRide - Gestion de vos trajets proposés";
        $user = $_SESSION['user'];
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        
        require __DIR__ . '/../Views/trips/mes-trajets.php';
    }
    
    /**
     * J'affiche le formulaire de création avec gestion des véhicules
     * Je montre le formulaire de création d'un nouveau trajet
     * avec récupération des véhicules de l'utilisateur
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
 * Endpoint : /api/places/search
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
 * Endpoint : /api/places/details
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
        // (Dans un vrai projet, on pourrait faire un appel détaillé)
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
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des véhicules
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
     * Je traite la soumission du formulaire de création de trajet avec coordonnées GPS
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
        
        // Je valide centralement les données
        $data = $this->extraireDonneesTrajet($_POST);
        $erreurs = $this->validerDonneesControleur($data);
        
        if (!empty($erreurs)) {
            $_SESSION['erreurs_trajet'] = $erreurs;
            $_SESSION['donnees_trajet'] = $data;
            header('Location: /EcoRide/public/nouveau-trajet');
            exit;
        }
        
        // ✅ JE GÉOCODE LES ADRESSES POUR OBTENIR LES COORDONNÉES GPS
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
            $_SESSION['message'] = $resultat['message'] . " Prix calculé : {$resultat['prix_calcule']} crédits.";
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
     * 
     * @param float $lat1 Latitude départ
     * @param float $lon1 Longitude départ
     * @param float $lat2 Latitude arrivée
     * @param float $lon2 Longitude arrivée
     * @return float Distance en kilomètres
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
     * 
     * @param array $post Données POST
     * @return array Données nettoyées
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
     * 
     * @param array $data Données à valider
     * @return array Erreurs trouvées
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
     * J'API pour la recherche AJAX avec gestion complète
     * J'endpoint pour les recherches en temps réel depuis JavaScript
     */
    public function apiRecherche()
    {
        // Je paramètre les headers pour API JSON
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        // Je récupère complètement les critères
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
     * MÉTHODES UTILITAIRES PRIVÉES
     */
    
    /**
     * J'extrais l'ID depuis l'URL pour le routing
     * 
     * @param string $route Nom de la route
     * @return int|null ID extrait ou null
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
     * 
     * @param int $trajetId ID du trajet
     * @param int $userId ID de l'utilisateur
     * @return bool True si déjà réservé
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
     * Je valide et nettoie un lieu de recherche
     * 
     * @param string $lieu Lieu à valider
     * @return string Lieu validé ou chaîne vide
     */
    private function validerLieu($lieu)
    {
        $lieu = trim($lieu);
        return strlen($lieu) >= 2 && strlen($lieu) <= 100 ? $lieu : '';
    }
    
    /**
     * Je valide une date de recherche
     * 
     * @param string $date Date à valider
     * @return string Date validée ou chaîne vide
     */
    private function validerDate($date)
    {
        if (empty($date)) return '';
        
        $timestamp = strtotime($date);
        return $timestamp && $timestamp >= strtotime('today') ? $date : '';
    }

    

}
?>

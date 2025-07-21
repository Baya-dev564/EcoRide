<?php
// public/index.php
// Point d'entrée principal EcoRide avec routeur unifié

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrage de session
session_start();

// Inclusion de la base de données
require_once __DIR__ . '/../config/database.php';

// Récupération de l'URI sans modification
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = str_replace('/EcoRide/public', '', $uri);
$path = strtok($path, '?') ?: '/';

// Préservation de la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Routage principal
switch ($path) {
    // === PAGE D'ACCUEIL ===
    case '/':
        require_once __DIR__ . '/../app/Controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
        
    // === AUTHENTIFICATION ===
    case '/inscription':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->inscription();
        break;
        
    case '/connexion':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->connexion();
        break;

    case '/deconnexion':
        require_once __DIR__ . '/../app/Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->deconnexion();
        break;

    case '/api/inscription':
        header('Content-Type: application/json');
        if ($method === 'POST') {
            require_once __DIR__ . '/../app/Controllers/AuthController.php';
            $controller = new AuthController();
            $controller->apiInscription();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    case '/api/connexion':
        header('Content-Type: application/json');
        if ($method === 'POST') {
            require_once __DIR__ . '/../app/Controllers/AuthController.php';
            $controller = new AuthController();
            $controller->apiConnexion();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;
        
    // === TRAJETS ===
    case '/trajets':
        require_once __DIR__ . '/../app/Controllers/TripController.php';
        $controller = new TripController();
        $controller->index();
        break;

    case '/nouveau-trajet':
        require_once __DIR__ . '/../app/Controllers/TripController.php';
        $controller = new TripController();
        if ($method === 'POST') {
            $controller->creerTrajet();
        } else {
            $controller->nouveauTrajet();
        }
        break;

    case '/mes-trajets':
        require_once __DIR__ . '/../app/Controllers/TripController.php';
        $controller = new TripController();
        $controller->mesTrajets();
        break;

    // === RÉSERVATIONS ===
    case '/mes-reservations':
        require_once __DIR__ . '/../app/Controllers/ReservationController.php';
        $controller = new ReservationController();
        $controller->mesReservations();
        break;

    case '/reserver-trajet':
        require_once __DIR__ . '/../app/Controllers/ReservationController.php';
        $controller = new ReservationController();
        $controller->reserver();
        break;

    case '/annuler-reservation':
        require_once __DIR__ . '/../app/Controllers/ReservationController.php';
        $controller = new ReservationController();
        $controller->annuler();
        break;

    case '/api/reserver':
        header('Content-Type: application/json');
        if ($method === 'POST') {
            require_once __DIR__ . '/../app/Controllers/ReservationController.php';
            $controller = new ReservationController();
            $controller->reserver();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    // === PROFIL UTILISATEUR ===
    case '/profil':
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->profil();
        break;

    case '/api/modifier-profil':
        header('Content-Type: application/json');
        if ($method === 'POST') {
            require_once __DIR__ . '/../app/Controllers/UserController.php';
            $controller = new UserController();
            $controller->modifierProfil();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    case '/api/ajouter-vehicule':
        header('Content-Type: application/json');
        if ($method === 'POST') {
            require_once __DIR__ . '/../app/Controllers/UserController.php';
            $controller = new UserController();
            $controller->ajouterVehicule();
        }
        break;

    case '/api/mes-vehicules':
        header('Content-Type: application/json');
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $controller = new UserController();
        $controller->mesVehicules();
        break;

    // === AVIS (NOSQL JSON) ===
    case '/avis':
    case '/mes-avis':
        require_once __DIR__ . '/../app/Controllers/AvisController.php';
        $controller = new AvisController();
        $controller->index();
        break;

    case '/donner-avis':
        require_once __DIR__ . '/../app/Controllers/AvisController.php';
        $controller = new AvisController();
        $controller->create();
        break;

     case '/api/avis':
    header('Content-Type: application/json');
    if ($method === 'POST') {
        require_once __DIR__ . '/../app/Controllers/AvisController.php';
        $controller = new AvisController();
        $controller->apiStore();
    } else {
        http_response_code(405);
        echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
    }
    break;


    // === ADMINISTRATION ===
    case '/admin':
    case '/admin/dashboard':
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $controller = new AdminController();
        $controller->dashboard();
        break;

    case '/admin/utilisateurs':
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $controller = new AdminController();
        $controller->utilisateurs();
        break;

    case '/admin/avis':
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $controller = new AdminController();
        $controller->avis();
        break;

    // === ROUTES DYNAMIQUES ===
    default:
        // Route détail trajet : /trajet/123
        if (preg_match('/^\/trajet\/(\d+)$/', $path, $matches)) {
            require_once __DIR__ . '/../app/Controllers/TripController.php';
            $controller = new TripController();
            $controller->details($matches[1]);
        } 
        // Route réserver trajet : /reserver/123
        elseif (preg_match('/^\/reserver\/(\d+)$/', $path, $matches)) {
            require_once __DIR__ . '/../app/Controllers/ReservationController.php';
            $controller = new ReservationController();
            $controller->reserver($matches[1]);
        }
        // Route voir avis d'un utilisateur : /avis/123
        elseif (preg_match('/^\/avis\/(\d+)$/', $path, $matches)) {
            require_once __DIR__ . '/../app/Controllers/AvisController.php';
            $controller = new AvisController();
            $controller->show($matches[1]);
        }
        // Route création avis : /avis/create
        elseif (preg_match('/^\/avis\/create$/', $path)) {
            require_once __DIR__ . '/../app/Controllers/AvisController.php';
            $controller = new AvisController();
            $controller->create();
        }
        // Route détail réservation : /reservation/123
        elseif (preg_match('/^\/reservation\/(\d+)$/', $path, $matches)) {
            require_once __DIR__ . '/../app/Controllers/ReservationController.php';
            $controller = new ReservationController();
            $controller->details($matches[1]);
        }
        // Route API supprimer véhicule : /api/supprimer-vehicule/123
        elseif (preg_match('/^\/api\/supprimer-vehicule\/(\d+)$/', $path, $matches)) {
            header('Content-Type: application/json');
            require_once __DIR__ . '/../app/Controllers/UserController.php';
            $controller = new UserController();
            $controller->supprimerVehicule($matches[1]);
        }
        // Route API annuler réservation : /api/annuler-reservation/123
        elseif (preg_match('/^\/api\/annuler-reservation\/(\d+)$/', $path, $matches)) {
            header('Content-Type: application/json');
            require_once __DIR__ . '/../app/Controllers/ReservationController.php';
            $controller = new ReservationController();
            $controller->annuler();
        }
        // Page 404
        else {
            http_response_code(404);
            echo "<h1>Page non trouvée</h1>";
            echo "<p>Chemin demandé : " . htmlspecialchars($path) . "</p>";
            echo '<a href="/">← Retour accueil</a>';
        }
        break;
}
?>

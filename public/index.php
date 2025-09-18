<?php
/**
 * Point d'entrée principal EcoRide avec routeur unifié
 * Version RÉPARÉE avec chargement direct des contrôleurs
 */

// J'active l'affichage des erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Je démarre la session pour toute l'application
session_start();

// J'inclus la configuration de base de données et crée la connexion PDO globale
require_once __DIR__ . '/../config/database.php';

// J'instancie la classe DatabaseConfig pour créer $pdo global
$databaseConfig = new DatabaseConfig();
$pdo = $databaseConfig->getConnection();

// ✅ JE CHARGE DIRECTEMENT TOUS LES CONTRÔLEURS (PAS D'AUTOLOADER)
require_once __DIR__ . '/../app/Controllers/TripController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/ReservationController.php';
require_once __DIR__ . '/../app/Controllers/AvisController.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';

// Je récupère l'URI sans modification
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = str_replace('/EcoRide/public', '', $uri);
$path = strtok($path, '?') ?: '/';

// Je préserve la méthode HTTP pour les APIs
$method = $_SERVER['REQUEST_METHOD'];

// ✅ JE GÈRE LES ROUTES ADMIN EN PREMIER (AVANT LE SWITCH)
if (strpos($path, '/admin') === 0) {
    $controller = new AdminController();
    
    // Routes admin spécifiques
    if ($path === '/admin' || $path === '/admin/dashboard') {
        $controller->dashboard();
    } elseif ($path === '/admin/trajets') {
        // ✅ ROUTE MANQUANTE AJOUTÉE !
        $controller->trajets();
    } elseif ($path === '/admin/utilisateurs') {
        $controller->utilisateurs();
    } elseif ($path === '/admin/avis') {
        $controller->avis();
    } elseif ($path === '/admin/support') {
        $controller->support();
    } elseif ($path === '/admin/test') {
        $controller->testConnexions();
    } elseif ($path === '/admin/api/moderer-trajet' && $method === 'POST') {
        // ✅ API MODÉRATION TRAJETS
        header('Content-Type: application/json');
        $controller->modererTrajet();
    } elseif ($path === '/admin/api/credits' && $method === 'POST') {
        header('Content-Type: application/json');
        $controller->modifierCredits();
    } elseif ($path === '/admin/api/user-status' && $method === 'POST') {
        header('Content-Type: application/json');
        $controller->toggleUserStatus();
    } elseif ($path === '/admin/api/avis-status' && $method === 'POST') {
        header('Content-Type: application/json');
        $controller->modifierStatutAvis();
    } elseif ($path === '/admin/api/stats-moderation' && $method === 'GET') {
        header('Content-Type: application/json');
        $controller->getStatsModeration();
    } elseif ($path === '/admin/export') {
        $controller->exportRapport();
        } elseif (preg_match('/^\/admin\/trajets\/(\d+)$/', $path, $matches)) {
    // ✅ PAGE DÉTAILS TRAJET : /admin/trajets/123
    $controller->detailsTrajet($matches[1]);


    } else {
        // 404 pour routes admin inconnues
        http_response_code(404);
        echo "Page admin non trouvée";
    }
    exit; // ✅ IMPORTANT : Sortir après traitement admin
}

// Je démarre le routage principal pour les routes publiques
switch ($path) {
    // === PAGE D'ACCUEIL ===
    case '/':
        $controller = new HomeController();
        $controller->index();
        break;
        
    // === AUTHENTIFICATION ===
    case '/inscription':
        $controller = new AuthController();
        $controller->inscription();
        break;
        
    case '/connexion':
        $controller = new AuthController();
        $controller->connexion();
        break;

    case '/deconnexion':
        $controller = new AuthController();
        $controller->deconnexion();
        break;

    case '/api/inscription':
        header('Content-Type: application/json');
        if ($method === 'POST') {
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
            $controller = new AuthController();
            $controller->apiConnexion();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;
        
    // === TRAJETS ===
    case '/trajets':
        $controller = new TripController();
        $controller->index();
        break;

    case '/nouveau-trajet':
        $controller = new TripController();
        if ($method === 'POST') {
            $controller->creerTrajet();
        } else {
            $controller->nouveauTrajet();
        }
        break;

    case '/mes-trajets':
        $controller = new TripController();
        $controller->mesTrajets();
        break;

    case '/demarrer-trajet':
        $controller = new TripController();
        $controller->demarrerTrajet();
        break;

    case '/terminer-trajet':
        $controller = new TripController();
        $controller->terminerTrajet();
        break;

    case '/signaler-probleme':
        $controller = new TripController();
        $controller->signalerProbleme();
        break;

    // === RÉSERVATIONS ===
    case '/mes-reservations':
        $controller = new ReservationController();
        $controller->mesReservations();
        break;

    case '/reserver-trajet':
        $controller = new ReservationController();
        $controller->reserver();
        break;

    case '/annuler-reservation':
        $controller = new ReservationController();
        $controller->annuler();
        break;

    case '/valider-trajet':
        $controller = new ReservationController();
        $controller->validerTrajet();
        break;

    case '/api/reserver':
        header('Content-Type: application/json');
        if ($method === 'POST') {
            $controller = new ReservationController();
            $controller->reserver();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    // === PROFIL UTILISATEUR ===
    case '/profil':
        $controller = new UserController();
        $controller->profil();
        break;

    case '/api/modifier-profil':
        header('Content-Type: application/json');
        if ($method === 'POST') {
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
            $controller = new UserController();
            $controller->ajouterVehicule();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    case '/api/mes-vehicules':
        header('Content-Type: application/json');
        $controller = new UserController();
        $controller->mesVehicules();
        break;

    // === AVIS (NOSQL MONGODB) ===
    case '/avis':
    case '/mes-avis':
        $controller = new AvisController();
        $controller->index();
        break;

    case '/donner-avis':
        $controller = new AvisController();
        $controller->create();
        break;

    case '/api/avis':
        header('Content-Type: application/json');
        if ($method === 'POST') {
            $controller = new AvisController();
            $controller->ajouterAvis();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    // === ROUTES DYNAMIQUES ===
    default:
        // Route détail trajet : /trajet/123
        if (preg_match('/^\/trajet\/(\d+)$/', $path, $matches)) {
            $controller = new TripController();
            $controller->details($matches[1]);
        } 
        // Route réserver trajet : /reserver/123
        elseif (preg_match('/^\/reserver\/(\d+)$/', $path, $matches)) {
            $controller = new ReservationController();
            $controller->reserver($matches[1]);
        }
        // Page 404
        else {
            http_response_code(404);
            
            $title = "Page non trouvée | EcoRide";
            $error404 = true;
            
            ob_start();
            ?>
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <h1 class="display-1 text-primary">404</h1>
                        <h2 class="mb-4">Page non trouvée</h2>
                        <p class="lead text-muted mb-4">
                            Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
                        </p>
                        <div class="alert alert-info">
                            <strong>Chemin demandé :</strong> 
                            <code><?= htmlspecialchars($path) ?></code>
                        </div>
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="/" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>
                                Retour à l'accueil
                            </a>
                            <a href="/trajets" class="btn btn-outline-primary">
                                <i class="fas fa-route me-2"></i>
                                Voir les trajets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            
            if (file_exists(__DIR__ . '/../app/Views/layouts/main.php')) {
                include __DIR__ . '/../app/Views/layouts/main.php';
            } else {
                echo $content;
            }
        }
        break;
}
?>

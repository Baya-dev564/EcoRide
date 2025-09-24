<?php
/**
 * Point d'entrée principal EcoRide avec routeur unifié
 * Version COMPLÈTE avec interface Admin + Messagerie NoSQL MongoDB + NOUVELLES FONCTIONNALITÉS USER ADMIN + WORKFLOW NOTATION
 * 
 * FONCTIONNALITÉS INCLUSES :
 * - Authentification complète + VÉRIFICATION EMAIL  
 * - Gestion des trajets
 * - Système de réservations
 * - Interface administration MongoDB
 * - Messagerie temps réel NoSQL
 * - Système d'avis MongoDB
 * - Gestion des profils utilisateurs
 * - Notifications de messages non lus
 * - Statistiques utilisateur avancées (AdminUserController séparé)
 * - Modification utilisateur complète (AdminUserController séparé)
 * - APIs AJAX pour gestion utilisateurs (AdminUserController séparé)
 * - NOUVEAU : Workflow complet de notation post-trajet
 * - NOUVEAU : Système de vérification email complet
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

// Je charge directement tous les contrôleurs (approche simple)
require_once __DIR__ . '/../app/Controllers/TripController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';
require_once __DIR__ . '/../app/Controllers/AdminUserController.php'; // NOUVEAU CONTRÔLEUR SÉPARÉ
require_once __DIR__ . '/../app/Controllers/ReservationController.php';
require_once __DIR__ . '/../app/Controllers/AvisController.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';
require_once __DIR__ . '/../app/Controllers/MessagerieController.php'; // Messagerie MongoDB

// Je récupère l'URI et nettoie le chemin
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = str_replace('/EcoRide/public', '', $uri);
$path = strtok($path, '?') ?: '/';

// Je préserve la méthode HTTP pour les APIs
$method = $_SERVER['REQUEST_METHOD'];

// Je gère les routes admin en premier (sécurité prioritaire)
if (strpos($path, '/admin') === 0) {
    
    // Je sépare les nouvelles fonctionnalités des anciennes
    if (preg_match('/^\/admin\/api\/avis-delete\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
        $avisId = $matches[1];
        header('Content-Type: application/json');
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $controller = new AdminController();
        $controller->supprimerAvis($avisId);
        exit;
    }
    
    // NOUVELLES ROUTES POUR LA GESTION AVANCÉE DES UTILISATEURS (CONTRÔLEUR SÉPARÉ)
    if (preg_match('/^\/admin\/user-stats\/(\d+)$/', $path, $matches)) {
        // Page statistiques d'un utilisateur : /admin/user-stats/123
        $userController = new AdminUserController();
        $userController->userStats($matches[1]);
    } 
    elseif (preg_match('/^\/admin\/user-edit\/(\d+)$/', $path, $matches)) {
        // Page modification d'un utilisateur : /admin/user-edit/123
        $userController = new AdminUserController();
        $userController->editUser($matches[1]);
    } 
    elseif (preg_match('/^\/admin\/user-update\/(\d+)$/', $path, $matches)) {
        // Traitement modification utilisateur : /admin/user-update/123
        if ($method === 'POST') {
            $userController = new AdminUserController();
            $userController->updateUser($matches[1]);
        } else {
            header('Location: /admin/utilisateurs');
        }
    }
    // NOUVELLES APIs AJAX POUR LA GESTION UTILISATEURS (CONTRÔLEUR SÉPARÉ)
    elseif ($path === '/admin/modifier-credits' && $method === 'POST') {
        // API : Modifier les crédits d'un utilisateur
        header('Content-Type: application/json');
        $userController = new AdminUserController();
        $userController->modifierCredits();
    } 
    elseif ($path === '/admin/toggle-user-status' && $method === 'POST') {
        // API : Suspendre/Activer un utilisateur
        header('Content-Type: application/json');
        $userController = new AdminUserController();
        $userController->toggleUserStatus();
    }
    
    // ROUTES ADMIN EXISTANTES (TON AdminController ORIGINAL NON MODIFIÉ)
    else {
        $controller = new AdminController();
        
        // Je route toutes les pages admin existantes
        if ($path === '/admin' || $path === '/admin/dashboard') {
            $controller->dashboard();
        } elseif ($path === '/admin/trajets') {
            // Page de gestion des trajets
            $controller->trajets();
        } elseif ($path === '/admin/utilisateurs') {
            // Page de gestion des utilisateurs
            $controller->utilisateurs();
        } elseif ($path === '/admin/avis') {
            // Page de modération des avis MongoDB
            $controller->avis();
        } elseif ($path === '/admin/support') {
            // Page de support et FAQ
            $controller->support();
        } elseif ($path === '/admin/test') {
            // Page de test des connexions (développement)
            $controller->testConnexions();
        } 
        elseif ($path === '/admin/moderer-trajet' && $method === 'POST') {
            // API : Modérer un trajet (valider/refuser) - Ton code existant
            header('Content-Type: application/json');
            $controller->modererTrajet();
        }
        // ROUTES ADMIN EXISTANTES CONSERVÉES INTACTES
        
        elseif ($path === '/admin/api/moderer-trajet' && $method === 'POST') {
            // API : Modérer un trajet (valider/refuser) - Route alternative
            header('Content-Type: application/json');
            $controller->modererTrajet();
        } elseif ($path === '/admin/api/credits' && $method === 'POST') {
            // API : Modifier les crédits d'un utilisateur - Route alternative (si tu l'as)
            header('Content-Type: application/json');
            $controller->modifierCredits();
        } elseif ($path === '/admin/api/user-status' && $method === 'POST') {
            // API : Suspendre/Activer un utilisateur - Route alternative (si tu l'as)
            header('Content-Type: application/json');
            $controller->toggleUserStatus();
        } elseif ($path === '/admin/api/avis-status' && $method === 'POST') {
            // API : Modérer un avis (approuver/rejeter)
            header('Content-Type: application/json');
            $controller->modifierStatutAvis();
        } elseif ($path === '/admin/api/stats-moderation' && $method === 'GET') {
            // API : Récupérer les statistiques de modération
            header('Content-Type: application/json');
            $controller->getStatsModeration();
        } elseif ($path === '/admin/export') {
            // Exporter un rapport admin (PDF/CSV)
            $controller->exportRapport();
        } elseif (preg_match('/^\/admin\/trajets\/(\d+)$/', $path, $matches)) {
            // Page détails d'un trajet pour admin : /admin/trajets/123
            $controller->detailsTrajet($matches[1]);
        } else {
            // 404 pour routes admin inconnues
            http_response_code(404);
            echo "Page admin non trouvée : " . htmlspecialchars($path);
        }
    }
    
    exit; // IMPORTANT : Je sors après traitement admin
}

// Je démarre le routage principal pour les routes publiques
switch ($path) {
    // PAGE D'ACCUEIL
    case '/':
        $controller = new HomeController();
        $controller->index();
        break;
        
    case '/mentions-legales':
        $title = "Mentions Légales | EcoRide";
        ob_start();
        include __DIR__ . '/../app/Views/legal/mentions-legales.php';
        $content = ob_get_clean();
        require __DIR__ . '/../app/Views/layouts/main.php';
        break;

    case '/confidentialite':
        $title = "Politique de Confidentialité | EcoRide";
        ob_start();
        include __DIR__ . '/../app/Views/legal/confidentialite.php';
        $content = ob_get_clean();
        require __DIR__ . '/../app/Views/layouts/main.php';
        break;

    // SYSTÈME D'AUTHENTIFICATION + VÉRIFICATION EMAIL
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

    // NOUVELLES ROUTES POUR LA VÉRIFICATION EMAIL
    case '/inscription-confirmation':
        // Page de confirmation après inscription
        $controller = new AuthController();
        $controller->inscriptionConfirmation();
        break;

    case '/api/inscription':
        // API : Créer un nouveau compte utilisateur
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
        // API : Connexion utilisateur
        header('Content-Type: application/json');
        if ($method === 'POST') {
            $controller = new AuthController();
            $controller->apiConnexion();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;
        
    // GESTION DES TRAJETS + WORKFLOW NOTATION
    case '/trajets':
        // Je liste tous les trajets disponibles
        $controller = new TripController();
        $controller->index();
        break;

    case '/nouveau-trajet':
        // Je gère la création de trajet (GET = formulaire, POST = création)
        $controller = new TripController();
        if ($method === 'POST') {
            $controller->creerTrajet();
        } else {
            $controller->nouveauTrajet();
        }
        break;

    case '/mes-trajets':
        // Je liste les trajets de l'utilisateur connecté
        $controller = new TripController();
        $controller->mesTrajets();
        break;

    // NOUVELLES ROUTES POUR LE WORKFLOW DE NOTATION
    case '/mes-trajets-a-noter':
        // Page listant tous les trajets terminés que l'utilisateur peut noter
        $controller = new TripController();
        $controller->trajetsANoter();
        break;

    // SYSTÈME DE RÉSERVATIONS
    case '/mes-reservations':
        // Je liste les réservations de l'utilisateur
        $controller = new ReservationController();
        $controller->mesReservations();
        break;

    case '/reserver-trajet':
        // Je réserve une place sur un trajet
        $controller = new ReservationController();
        $controller->reserver();
        break;

    case '/annuler-reservation':
        // J'annule une réservation
        $controller = new ReservationController();
        $controller->annuler();
        break;

    case '/valider-trajet':
        // Je valide qu'un trajet s'est bien passé
        $controller = new ReservationController();
        $controller->validerTrajet();
        break;

    case '/api/reserver':
        // API : Réserver un trajet
        header('Content-Type: application/json');
        if ($method === 'POST') {
            $controller = new ReservationController();
            $controller->reserver();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    // GESTION DU PROFIL UTILISATEUR
    case '/profil':
        // Je gère le profil utilisateur
        $controller = new UserController();
        $controller->profil();
        break;

    case '/api/modifier-profil':
        // API : Modifier les infos du profil
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
        // API : Ajouter un véhicule au profil
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
        // API : Lister les véhicules de l'utilisateur
        header('Content-Type: application/json');
        $controller = new UserController();
        $controller->mesVehicules();
        break;

    // SYSTÈME D'AVIS (NOSQL MONGODB)
    case '/avis':
    case '/mes-avis':
        // Je gère les avis utilisateurs (stockés en MongoDB)
        $controller = new AvisController();
        $controller->index();
        break;

    case '/donner-avis':
        // Je donne un avis sur un trajet/utilisateur
        $controller = new AvisController();
        $controller->create();
        break;

    case '/api/avis':
        // API : Ajouter un nouvel avis
        header('Content-Type: application/json');
        if ($method === 'POST') {
            $controller = new AvisController();
            $controller->ajouterAvis();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    // MESSAGERIE TEMPS RÉEL (NOSQL MONGODB)
    case '/messages':
        // Je gère la page principale de messagerie
        $controller = new MessagerieController();
        $controller->index();
        break;

    case '/api/messages/send':
        // API : Envoyer un message dans une conversation
        header('Content-Type: application/json');
        if ($method === 'POST') {
            $controller = new MessagerieController();
            $controller->envoyerMessage();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    case '/api/messages/new':
        // API : Créer une nouvelle conversation
        header('Content-Type: application/json');
        if ($method === 'POST') {
            $controller = new MessagerieController();
            $controller->nouvelleConversation();
        } else {
            http_response_code(405);
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
        }
        break;

    // API : Compter les messages non lus
    case '/api/messages/unread-count':
        header('Content-Type: application/json');
        $controller = new MessagerieController();
        $controller->getUnreadCount();
        break;

    // ROUTES MESSAGERIE SUPPLÉMENTAIRES
    case '/api/users/search':
        // API : Rechercher des utilisateurs par pseudo
        header('Content-Type: application/json');
        $controller = new MessagerieController();
        $controller->rechercherUtilisateurs();
        break;

    case '/api/messages/motifs':
        // API : Obtenir les motifs de contact
        header('Content-Type: application/json');
        $controller = new MessagerieController();
        $controller->getMotifs();
        break;

    // API RECHERCHE DE LIEUX
    case '/api/places/search':
        // J'ajoute cette nouvelle route API
        header('Content-Type: application/json');
        $controller = new TripController();
        $controller->apiSearchPlaces();
        break;
        
    case '/api/places/details':
        $controller = new TripController();
        $controller->apiPlaceDetails();
        break;

    case '/demarrer-trajet-reservations':
        $controller = new ReservationController();
        $controller->demarrerTrajetReservations();
        break;

    case '/terminer-trajet-reservations':
        $controller = new ReservationController();
        $controller->terminerTrajetReservations();
        break;

    // ROUTES DYNAMIQUES AVEC REGEX + WORKFLOW NOTATION
    default:
        // NOUVELLE ROUTE : Vérifier le token email : /verifier-email/{token}
        if (preg_match('/^\/verifier-email\/([a-f0-9]{64})$/', $path, $matches)) {
            $token = $matches[1]; // Récupère le token de l'URL
            $controller = new AuthController();
            $controller->verifierEmail($token);
        }
        // NOUVELLE ROUTE : Terminer un trajet (POST) : /trajet/123/terminer
        elseif (preg_match('/^\/trajet\/(\d+)\/terminer$/', $path, $matches)) {
            if ($method === 'POST') {
                $controller = new TripController();
                $controller->terminerTrajet($matches[1]);
            } else {
                // Si ce n'est pas POST, je redirige vers les détails du trajet
                header("Location: /trajet/{$matches[1]}");
            }
        }
        // NOUVELLE ROUTE : Noter un trajet (GET) : /noter-trajet/123
        elseif (preg_match('/^\/noter-trajet\/(\d+)$/', $path, $matches)) {
            $controller = new TripController();
            $controller->noterTrajet($matches[1]);
        }
        // Route détail trajet : /trajet/123
        elseif (preg_match('/^\/trajet\/(\d+)$/', $path, $matches)) {
            $controller = new TripController();
            $controller->details($matches[1]);
        } 
        // Route réserver trajet : /reserver/123
        elseif (preg_match('/^\/reserver\/(\d+)$/', $path, $matches)) {
            $controller = new ReservationController();
            $controller->reserver($matches[1]);
        }
        // Route conversation messagerie : /messages/conversation/abc123
        elseif (preg_match('/^\/messages\/conversation\/([a-zA-Z0-9]+)$/', $path, $matches)) {
            $controller = new MessagerieController();
            $controller->conversation($matches[1]);
        }
        // API nouveaux messages : /messages/conversation/abc123/new
        elseif (preg_match('/^\/messages\/conversation\/([a-zA-Z0-9]+)\/new$/', $path, $matches)) {
            header('Content-Type: application/json');
            $controller = new MessagerieController();
            $controller->getNewMessages($matches[1]);
        }
        // PAGE 404 PERSONNALISÉE
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
                            Désolé, la page que tu recherches n'existe pas ou a été déplacée.
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
                            <a href="/messages" class="btn btn-outline-success">
                                <i class="fas fa-comments me-2"></i>
                                Mes messages
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            
            // Je charge le layout principal si il existe
            if (file_exists(__DIR__ . '/../app/Views/layouts/main.php')) {
                include __DIR__ . '/../app/Views/layouts/main.php';
            } else {
                echo $content;
           }
        }
        break;
}
?>

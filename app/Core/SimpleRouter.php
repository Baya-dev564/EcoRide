<?php
// app/Core/SimpleRouter.php

class SimpleRouter
{
    public function route()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Nettoyage de l'URL
        $path = str_replace('/EcoRide/public', '', $uri);
        $path = strtok($path, '?');
        $path = $path ?: '/';
        
        // Routage direct avec switch
        switch ($path) {
            case '/':
                require_once __DIR__ . '/../Controllers/HomeController.php';
                $controller = new HomeController();
                $controller->index();
                break;
                
            case '/inscription':
                require_once __DIR__ . '/../Controllers/AuthController.php';
                $controller = new AuthController();
                $controller->inscription();
                break;
                
            case '/connexion':
                require_once __DIR__ . '/../Controllers/AuthController.php';
                $controller = new AuthController();
                $controller->connexion();
                break;

                case '/trajets':
                require_once __DIR__ . '/../Controllers/TripController.php';
                $controller = new TripController();
                $controller->index();
                break;

            case '/mes-trajets':
                require_once __DIR__ . '/../Controllers/TripController.php';
                $controller = new TripController();
                $controller->mesTrajets();
                break;

                
            default:
                http_response_code(404);
                echo "<h1>Page non trouvée</h1>";
                echo "<p>Chemin demandé : $path</p>";
                echo "<a href='/EcoRide/public/'>← Retour accueil</a>";
                break;

                case '/api/connexion':
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/../Controllers/AuthController.php';
        $controller = new AuthController();
        $controller->apiConnexion();
    } else {
        http_response_code(405);
        echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée']);
    }
    break;

        }
    }
}
?>


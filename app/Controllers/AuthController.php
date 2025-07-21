<?php
/**
 * Contrôleur d'authentification 
 */

class AuthController
{
    /**
     * Affiche le formulaire d'inscription
     * Route : GET /inscription
     */
    public function inscription()
    {
        // Démarrer la session de manière sécurisée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérification si l'utilisateur est déjà connecté
        if (isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous êtes déjà connecté à EcoRide !';
            header('Location: /EcoRide/public/');
            exit;
        }
        
        // Récupération des messages de session
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        
        // Données pour la vue
        $title = "Inscription | EcoRide - Rejoignez la communauté écologique";
        
        // Affichage du formulaire d'inscription
        require __DIR__ . '/../Views/auth/inscription.php';
    }
    
    /**
     * Affiche le formulaire de connexion
     * Route : GET /connexion
     */
    public function connexion()
    {
        // Démarrer la session de manière sécurisée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérification si l'utilisateur est déjà connecté
        if (isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous êtes déjà connecté à EcoRide !';
            header('Location: /EcoRide/public/');
            exit;
        }
        
        // Récupération des messages de session
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        
        // Données pour la vue
        $title = "Connexion | EcoRide - Accédez à votre compte";
        
        // Affichage du formulaire de connexion
        require __DIR__ . '/../Views/auth/connexion.php';
    }
    
    /**
     * Déconnecte l'utilisateur et détruit la session
     * Route : GET /deconnexion
     */
    public function deconnexion()
    {
        // Démarrer la session si pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Détruire toutes les données de session
        $_SESSION = [];
        
        // Détruire la session côté client (cookie)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Détruire la session côté serveur
        session_destroy();
        
        // Redémarrer une nouvelle session pour le message
        session_start();
        $_SESSION['message'] = 'Vous avez été déconnecté avec succès. À bientôt sur EcoRide !';
        
        // Redirection vers l'accueil
        header('Location: /EcoRide/public/');
        exit;
    }
    
    // ========== API AJAX POUR L'AUTHENTIFICATION ==========
    
    /**
     * API d'inscription pour les requêtes AJAX
     * Route : POST /api/inscription
     */
    public function apiInscription()
    {
        //  Démarrer la session de manière sécurisée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        //  Header JSON pour toutes les réponses API
        header('Content-Type: application/json');
        
        // Vérifier que c'est bien une requête AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['succes' => false, 'erreur' => 'Requête invalide - AJAX requis']);
            return;
        }
        
        // R2écupération des données du formulaire AJAX
        $data = [
            'pseudo' => trim($_POST['pseudo'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
            'confirmer_mot_de_passe' => $_POST['confirmer_mot_de_passe'] ?? '',
            'nom' => trim($_POST['nom'] ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'ville' => trim($_POST['ville'] ?? ''),
            'code_postal' => trim($_POST['code_postal'] ?? ''),
            'adresse' => trim($_POST['adresse'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'permis_conduire' => isset($_POST['permis_conduire']),
            'consentement_rgpd' => isset($_POST['consentement_rgpd'])
        ];
        
        // Inclusion des fichiers nécessaires
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/User.php';
        
        // Récupération de la connexion PDO
        global $pdo;
        $userModel = new User($pdo);
        
        // Traitement via le modèle User
        $resultat = $userModel->creerCompte($data);
        
        // Retour de la réponse en JSON
        if ($resultat['succes']) {
            echo json_encode([
                'succes' => true,
                'message' => $resultat['message'],
                'redirect' => '/EcoRide/public/connexion',
                'user_id' => $resultat['user_id'] ?? null
            ]);
        } else {
            echo json_encode([
                'succes' => false,
                'erreurs' => $resultat['erreurs']
            ]);
        }
    }
    
    /**
     * API de connexion pour les requêtes AJAX
     * + Ajout des variables de session pour le système d'avis
     */
    public function apiConnexion()
    /**  Démarrer la session AVANT tout traitement*/
        {if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        //  Header JSON pour toutes les réponses API
        header('Content-Type: application/json');
        
        // Vérifier que c'est bien une requête AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['succes' => false, 'erreur' => 'Requête invalide - AJAX requis']);
            return;
        }
        
        // Récupération des données du formulaire
        $email = trim($_POST['email'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        
        // Validation basique côté serveur
        if (empty($email) || empty($motDePasse)) {
            echo json_encode([
                'succes' => false, 
                'erreur' => 'Email et mot de passe sont obligatoires.'
            ]);
            return;
        }
        
        // Inclusion des fichiers nécessaires
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/User.php';
        
        // Récupération de la connexion PDO
        global $pdo;
        $userModel = new User($pdo);
        
        // Tentative d'authentification
        $resultat = $userModel->authentifier($email, $motDePasse);
        
        if ($resultat['succes']) {
            // Création complète de la session utilisateur
            // Nécessaire pour le système d'avis NoSQL qui utilise user_id et pseudo
            $_SESSION['user'] = $resultat['user'];
            $_SESSION['user_id'] = $resultat['user']['id'];        // Pour les avis (clé étrangère)
            $_SESSION['pseudo'] = $resultat['user']['pseudo'];     // Pour l'affichage dans les avis
            
            // Réponse JSON de succès
            echo json_encode([
                'succes' => true,
                'message' => $resultat['message'],
                'redirect' => '/EcoRide/public/',
                'user' => [
                    'id' => $resultat['user']['id'],
                    'pseudo' => $resultat['user']['pseudo'],
                    'credit' => $resultat['user']['credit'],
                    'permis_conduire' => $resultat['user']['permis_conduire'] ?? false
                ]
            ]);
        } else {
            // Erreur de connexion
            echo json_encode([
                'succes' => false,
                'erreur' => $resultat['erreur']
            ]);
        }
    }
}
?>

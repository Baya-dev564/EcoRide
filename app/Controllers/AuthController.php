<?php
/**
 * AuthController - Contrôleur d'authentification pour EcoRide
 * Gère l'inscription, la connexion et la déconnexion des utilisateurs
 */

class AuthController
{
    /**
     * J'affiche le formulaire d'inscription
     */
    public function inscription()
    {
        // Je démarre la session de façon sécurisée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Je vérifie si l'utilisateur est déjà connecté
        if (isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous êtes déjà connecté à EcoRide !';
            header('Location: /');
            exit;
        }
        
        // Je récupère les messages de session pour affichage
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']); // Je nettoie le message après récupération
        
        // Je définis le titre de la page pour le SEO
        $title = "Inscription | EcoRide - Rejoignez la communauté écologique";
        
        // Je charge la vue d'inscription
        require __DIR__ . '/../Views/auth/inscription.php';
    }
    
    /**
     * J'affiche le formulaire de connexion
     */
    public function connexion()
    {
        // Je démarre la session de façon sécurisée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Je vérifie si l'utilisateur est déjà connecté
        if (isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous êtes déjà connecté à EcoRide !';
            header('Location: /');
            exit;
        }
        
        // Je récupère les messages de session
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        
        // Je définis le titre de la page pour le SEO
        $title = "Connexion | EcoRide - Accédez à votre compte";
        
        // Je charge la vue de connexion
        require __DIR__ . '/../Views/auth/connexion.php';
    }
    
    /**
     * Je déconnecte l'utilisateur et détruis sa session
     */
    public function deconnexion()
    {
        // Je démarre la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Je vide complètement le tableau de session
        $_SESSION = [];
        
        // Je supprime le cookie de session côté client
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Je détruis la session côté serveur
        session_destroy();
        
        // Je redémarre une nouvelle session pour le message de déconnexion
        session_start();
        $_SESSION['message'] = 'Vous avez été déconnecté avec succès. À bientôt sur EcoRide !';
        
        // Je redirige vers la page d'accueil
        header('Location: /');
        exit;
    }
    
    /**
     * API d'inscription - Je traite les données du formulaire via AJAX
     */
    public function apiInscription()
    {
        // Je démarre la session de façon sécurisée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Je définis le header JSON pour toutes les réponses API
        header('Content-Type: application/json');
        
        // Je vérifie que c'est bien une requête AJAX pour la sécurité
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['succes' => false, 'erreur' => 'Requête invalide - AJAX requis']);
            return;
        }
        
        // Je récupère et nettoie les données du formulaire
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
            'permis_conduire' => isset($_POST['permis_conduire']), // Checkbox true/false
            'consentement_rgpd' => isset($_POST['consentement_rgpd'])
        ];
        
        // Je me connecte à la base de données MySQL
        require_once __DIR__ . '/../../config/database.php';
        $db = new DatabaseConfig();
        $pdo = $db->getConnection();
        
        // Je charge le modèle User et lui passe la connexion PDO
        require_once __DIR__ . '/../Models/User.php';
        $userModel = new User($pdo);
        
        // Je traite l'inscription via le modèle
        $resultat = $userModel->creerCompte($data);
        
        // Je retourne la réponse en JSON selon le résultat
        if ($resultat['succes']) {
            // Inscription réussie - je redirige vers la connexion
            echo json_encode([
                'succes' => true,
                'message' => $resultat['message'],
                'redirect' => '/connexion',
                'user_id' => $resultat['user_id'] ?? null
            ]);
        } else {
            // Erreurs de validation - je les affiche à l'utilisateur
            echo json_encode([
                'succes' => false,
                'erreurs' => $resultat['erreurs']
            ]);
        }
    }
    
    /**
     * API de connexion - J'authentifie l'utilisateur via AJAX
     */
    public function apiConnexion()
    {
        // Je démarre la session AVANT tout traitement
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Je définis le header JSON pour toutes les réponses API
        header('Content-Type: application/json');
        
        // Je vérifie la sécurité AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['succes' => false, 'erreur' => 'Requête invalide - AJAX requis']);
            return;
        }
        
        // Je récupère les identifiants de connexion
        $email = trim($_POST['email'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';
        
        // Je valide les données côté serveur
        if (empty($email) || empty($motDePasse)) {
            echo json_encode([
                'succes' => false, 
                'erreur' => 'Email et mot de passe sont obligatoires.'
            ]);
            return;
        }
        
        // Je me connecte à la base de données MySQL
        require_once __DIR__ . '/../../config/database.php';
        $db = new DatabaseConfig();
        $pdo = $db->getConnection();
        
        // Je charge le modèle User avec la connexion PDO
        require_once __DIR__ . '/../Models/User.php';
        $userModel = new User($pdo);
        
        // Je tente l'authentification
        $resultat = $userModel->authentifier($email, $motDePasse);
        
        if ($resultat['succes']) {
            // Connexion réussie - je crée la session utilisateur complète
            $_SESSION['user'] = $resultat['user']; // Toutes les données utilisateur
            $_SESSION['user_id'] = $resultat['user']['id']; // ID pour l'application
            $_SESSION['pseudo'] = $resultat['user']['pseudo']; // Pseudo pour affichage
            
            // Je retourne une réponse JSON de succès avec données utilisateur
            echo json_encode([
                'succes' => true,
                'message' => $resultat['message'],
                'redirect' => '/',
                'user' => [
                    'id' => $resultat['user']['id'],
                    'pseudo' => $resultat['user']['pseudo'],
                    'credit' => $resultat['user']['credit'],
                    'permis_conduire' => $resultat['user']['permis_conduire'] ?? false
                ]
            ]);
        } else {
            // Erreur de connexion - identifiants incorrects
            echo json_encode([
                'succes' => false,
                'erreur' => $resultat['erreur']
            ]);
        }
    }
}
?>

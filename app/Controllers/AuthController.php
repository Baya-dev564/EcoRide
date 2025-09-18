<?php
/**
 * Contrôleur d'authentification pour EcoRide
 * Gère l'inscription, la connexion et la déconnexion des utilisateurs
 * Projet TP - Développement Web
 */

class AuthController
{
    /**
     * Affiche le formulaire d'inscription
     * Route : GET /inscription
     */
    public function inscription()
    {
        // Démarrage sécurisé de la session PHP
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est déjà connecté
        // Si oui, on le redirige vers l'accueil
        if (isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous êtes déjà connecté à EcoRide !';
            header('Location: /');
            exit;
        }
        
        // Récupération des messages de session (succès/erreur)
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']); // Nettoyer le message après affichage
        
        // Titre de la page pour le SEO
        $title = "Inscription | EcoRide - Rejoignez la communauté écologique";
        
        // Charger la vue d'inscription
        require __DIR__ . '/../Views/auth/inscription.php';
    }
    
    /**
     * Affiche le formulaire de connexion
     * Route : GET /connexion
     */
    public function connexion()
    {
        // Démarrage sécurisé de la session PHP
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est déjà connecté
        if (isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous êtes déjà connecté à EcoRide !';
            header('Location: /');
            exit;
        }
        
        // Récupération des messages de session
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        
        // Titre de la page pour le SEO
        $title = "Connexion | EcoRide - Accédez à votre compte";
        
        // Charger la vue de connexion
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
        
        // Vider complètement le tableau de session
        $_SESSION = [];
        
        // Supprimer le cookie de session côté client
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Détruire la session côté serveur
        session_destroy();
        
        // Redémarrer une nouvelle session pour le message de déconnexion
        session_start();
        $_SESSION['message'] = 'Vous avez été déconnecté avec succès. À bientôt sur EcoRide !';
        
        // Redirection vers la page d'accueil
        header('Location: /');
        exit;
    }
    
    // ========== API AJAX POUR L'AUTHENTIFICATION ==========
    
    /**
     * API d'inscription - Traite les données du formulaire via AJAX
     * Route : POST /api/inscription
     * Retourne du JSON pour la gestion côté JavaScript
     */
    public function apiInscription()
    {
        // Démarrage sécurisé de la session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Définir le header JSON pour toutes les réponses API
        header('Content-Type: application/json');
        
        // Vérifier que c'est bien une requête AJAX (sécurité)
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['succes' => false, 'erreur' => 'Requête invalide - AJAX requis']);
            return;
        }
        
        // Récupération et nettoyage des données du formulaire
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
            'permis_conduire' => isset($_POST['permis_conduire']), // Checkbox = true/false
            'consentement_rgpd' => isset($_POST['consentement_rgpd'])
        ];
        
        // Connexion à la base de données MySQL
        // IMPORTANT : Initialiser PDO avant de créer le modèle User
        require_once __DIR__ . '/../../config/database.php';
        $db = new DatabaseConfig(); // Créer l'objet de config DB
        $pdo = $db->getConnection(); // Obtenir la connexion PDO active
        
        // Charger le modèle User et lui passer la connexion PDO
        require_once __DIR__ . '/../Models/User.php';
        $userModel = new User($pdo); // CORRECTION : Passer PDO au constructeur
        
        // Traitement de l'inscription via le modèle
        $resultat = $userModel->creerCompte($data);
        
        // Retourner la réponse en JSON selon le résultat
        if ($resultat['succes']) {
            // Inscription réussie - redirection vers connexion
            echo json_encode([
                'succes' => true,
                'message' => $resultat['message'],
                'redirect' => '/connexion', // Redirection Docker (sans /EcoRide/public)
                'user_id' => $resultat['user_id'] ?? null
            ]);
        } else {
            // Erreurs de validation - les afficher à l'utilisateur
            echo json_encode([
                'succes' => false,
                'erreurs' => $resultat['erreurs']
            ]);
        }
    }
    
    /**
     * API de connexion - Authentifie l'utilisateur via AJAX
     * Route : POST /api/connexion
     * Crée la session utilisateur nécessaire pour les avis NoSQL
     */
    public function apiConnexion()
    {
        // Démarrer la session AVANT tout traitement
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Header JSON pour toutes les réponses API
        header('Content-Type: application/json');
        
        // Vérification sécurité AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            http_response_code(400);
            echo json_encode(['succes' => false, 'erreur' => 'Requête invalide - AJAX requis']);
            return;
        }
        
        // Récupération des identifiants de connexion
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
        
        // Connexion à la base de données MySQL
        // IMPORTANT : Même correction que pour l'inscription
        require_once __DIR__ . '/../../config/database.php';
        $db = new DatabaseConfig(); // Créer l'objet de config DB
        $pdo = $db->getConnection(); // Obtenir la connexion PDO active
        
        // Charger le modèle User avec la connexion PDO
        require_once __DIR__ . '/../Models/User.php';
        $userModel = new User($pdo); // CORRECTION : Passer PDO au constructeur
        
        // Tentative d'authentification
        $resultat = $userModel->authentifier($email, $motDePasse);
        
        if ($resultat['succes']) {
            // Connexion réussie - Création complète de la session utilisateur
            // IMPORTANT : Ces variables de session sont utilisées par le système d'avis NoSQL
            $_SESSION['user'] = $resultat['user']; // Toutes les données utilisateur
            $_SESSION['user_id'] = $resultat['user']['id']; // ID pour les avis MongoDB
            $_SESSION['pseudo'] = $resultat['user']['pseudo']; // Pseudo pour affichage avis
            
            // Réponse JSON de succès avec données utilisateur
            echo json_encode([
                'succes' => true,
                'message' => $resultat['message'],
                'redirect' => '/', // Redirection Docker (sans /EcoRide/public)
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

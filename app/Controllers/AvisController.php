<?php
/**
 * AvisController - Contrôleur des avis pour EcoRide
 * Système NoSQL MongoDB pour la gestion des avis utilisateurs
 * Architecture MVC hybride MySQL + MongoDB
 */

// J'inclus le modèle MongoDB pour les avis
require_once __DIR__ . '../app/Models/avis-mongo.php';

class AvisController 
{
    // Je propriété pour le modèle MongoDB des avis
    private $avisMongo;
    
    /**
     * Constructeur - J'initialise la connexion MongoDB
     */
    public function __construct() 
    {
        $this->avisMongo = new AvisMongo();
    }
    
    /**
     * J'affiche la page principale des avis
     * Je récupère tous les avis validés par l'administrateur
     */
    public function index() 
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        try {
            // Je récupère tous les avis validés depuis MongoDB
            error_log("DEBUG AvisController: Récupération de tous les avis validés");
            $resultat = $this->avisMongo->getTousLesAvisValidés();
            
            if ($resultat['success']) {
                $avisArray = $resultat['avis'];
                error_log("DEBUG AvisController: " . count($avisArray) . " avis récupérés avec succès");
                
                // Je transforme les données MongoDB en objets Avis compatibles avec la vue
                $avis = [];
                foreach ($avisArray as $avisData) {
                    // Je crée un objet avec les données MongoDB formatées
                    $avisFormatted = [
                        '_id' => $avisData['id'] ?? uniqid('avis_'),
                        'user_id' => $avisData['utilisateur_id'] ?? null,
                        'pseudo' => $avisData['nom_utilisateur'] ?? 'Utilisateur',
                        'trajet_id' => $avisData['trajet_id'] ?? '',
                        'conducteur_id' => $avisData['conducteur_id'] ?? '',
                        'note_globale' => $avisData['note'] ?? 5,
                        'criteres' => [
                            'ponctualite' => $avisData['note'] ?? 5,
                            'conduite' => $avisData['note'] ?? 5,
                            'proprete' => $avisData['note'] ?? 5,
                            'ambiance' => $avisData['note'] ?? 5
                        ], // J'utilise la note globale pour tous les critères
                        'commentaire' => $avisData['commentaire'] ?? '',
                        'tags' => [], // Pas encore implémenté dans MongoDB
                        'date_creation' => $avisData['date_creation'] ?? date('Y-m-d H:i:s'),
                        'statut' => 'validé'
                    ];
                    
                    // Je crée un objet Avis pour la vue
                    require_once __DIR__ . '../app/Models/avis-mongo.php';
                    $avis[] = new Avis($avisFormatted);
                }
                
                error_log("DEBUG AvisController: " . count($avis) . " objets Avis créés pour la vue");
                
            } else {
                error_log("DEBUG AvisController: Erreur récupération avis - " . ($resultat['error'] ?? 'Inconnue'));
                $avis = []; // Je retourne un tableau vide si erreur MongoDB
            }
            
        } catch (Exception $e) {
            error_log("Erreur récupération avis index : " . $e->getMessage());
            $avis = [];
        }
        
        // Je définis les variables pour la vue
        $pageTitle = "Avis des utilisateurs - EcoRide";
        
        // Je charge la vue avec les avis MongoDB
        include __DIR__ . '../app/Views/avis/index.php';
    }

    /**
     * J'affiche le formulaire pour créer un nouvel avis
     */
    public function create() 
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour donner un avis.';
            header('Location: /connexion');
            exit;
        }
        
        // Je récupère l'ID du trajet depuis l'URL ou les paramètres
        $trajet_id = $_GET['trajet_id'] ?? '1';
        $conducteur_id = $_GET['conducteur_id'] ?? '1';
        
        $title = "Donner un avis | EcoRide";
        
        // Je charge la vue avec les variables définies
        include __DIR__ . '../app/Views/avis/create.php';
    }
    
    /**
     * J'affiche un avis spécifique par son ID MongoDB
     */
    public function show($id = null) 
    {
        // Je vérifie que l'ID est fourni
        if (empty($id)) {
            // Je redirige vers la liste des avis si pas d'ID
            header('Location: /avis');
            exit;
        }
        
        try {
            // Je récupère l'avis depuis MongoDB avec l'ID fourni
            $avis = $this->avisMongo->getAvisParId($id);
            
            // Si l'avis n'existe pas, je redirige vers la liste
            if ($avis === null) {
                header('Location: /avis');
                exit;
            }
            
            // Je définis le titre de la page
            $title = "Détail de l'avis | EcoRide";
            
            // Je charge la vue détail d'un avis
            include __DIR__ . '../app/Views/avis/show.php';
            
        } catch (Exception $e) {
            // En cas d'erreur MongoDB, je redirige
            error_log("Erreur show avis : " . $e->getMessage());
            header('Location: /avis');
            exit;
        }
    }
    
    /**
     * API pour ajouter un nouvel avis dans MongoDB
     */
    public function ajouterAvis() 
    {
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
            return;
        }

        try {
            // Je récupère le vrai pseudo depuis la session
            $donnees = [
                'trajet_id' => $_POST['trajet_id'] ?? null,
                'utilisateur_id' => $_SESSION['user_id'],
                'nom_utilisateur' => $_SESSION['pseudo'] ?? $_SESSION['username'] ?? $_SESSION['nom'] ?? 'Utilisateur',
                'note' => isset($_POST['note']) ? (int)$_POST['note'] : 5,
                'commentaire' => $_POST['commentaire'] ?? ''
            ];

            // Je log pour vérifier le pseudo récupéré
            error_log('DEBUG ajouterAvis - pseudo utilisé : ' . $donnees['nom_utilisateur']);

            if (empty($donnees['trajet_id'])) {
                echo json_encode(['success' => false, 'error' => 'ID du trajet manquant']);
                return;
            }

            if ($donnees['note'] < 1 || $donnees['note'] > 5) {
                echo json_encode(['success' => false, 'error' => 'La note doit être entre 1 et 5 étoiles']);
                return;
            }

            // J'appelle le modèle MongoDB pour sauvegarder
            $resultat = $this->avisMongo->ajouterAvis($donnees);

            echo json_encode($resultat);

        } catch (Exception $e) {
            error_log("Erreur ajout avis MongoDB : " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur technique lors de l\'ajout de l\'avis'
            ]);
        }
    }

    /**
     * API pour récupérer les avis d'un trajet spécifique
     */
    public function getAvis($trajet_id) 
    {
        // Je définis le header JSON pour la réponse API
        header('Content-Type: application/json');
        
        // Je vérifie que l'ID du trajet est fourni
        if (empty($trajet_id)) {
            echo json_encode(['success' => false, 'error' => 'ID du trajet manquant']);
            return;
        }
        
        try {
            // Je récupère les avis depuis MongoDB pour ce trajet
            $resultat = $this->avisMongo->getAvisParTrajet($trajet_id);
            
            // Je retourne la réponse JSON avec les avis trouvés
            echo json_encode($resultat);
            
        } catch (Exception $e) {
            // Je gère les erreurs MongoDB
            error_log("Erreur récupération avis MongoDB : " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur lors de la récupération des avis'
            ]);
        }
    }
    
    /**
     * API pour récupérer tous les avis d'un utilisateur
     */
    public function getAvisUtilisateur($utilisateur_id = null) 
    {
        header('Content-Type: application/json');
        
        // Si pas d'ID fourni, je prends celui de la session
        if (empty($utilisateur_id)) {
            // Je démarre la session pour récupérer l'ID utilisateur
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $utilisateur_id = $_SESSION['user_id'] ?? null;
        }
        
        // Je vérifie qu'on a bien un ID utilisateur
        if (empty($utilisateur_id)) {
            echo json_encode(['success' => false, 'error' => 'Utilisateur non identifié']);
            return;
        }
        
        try {
            // J'utilise la méthode MongoDB appropriée
            $resultat = $this->avisMongo->getAvisParUtilisateur($utilisateur_id);
            
            // Je retourne la réponse JSON
            echo json_encode($resultat);
            
        } catch (Exception $e) {
            // Je gère les erreurs MongoDB
            error_log("Erreur récupération avis utilisateur : " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur lors de la récupération des avis utilisateur'
            ]);
        }
    }

    /**
     * API pour calculer la note moyenne d'un trajet
     */
    public function getMoyenneTrajet($trajet_id) 
    {
        header('Content-Type: application/json');
        
        if (empty($trajet_id)) {
            echo json_encode(['success' => false, 'error' => 'ID du trajet manquant']);
            return;
        }
        
        try {
            // Je calcule la moyenne avec MongoDB via agrégation
            $resultat = $this->avisMongo->calculerNoteMoyenne($trajet_id);
            
            echo json_encode($resultat);
            
        } catch (Exception $e) {
            error_log("Erreur calcul moyenne : " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Erreur lors du calcul de la moyenne'
            ]);
        }
    }

    /**
     * Je récupère le nom d'utilisateur depuis MySQL
     */
    private function getNomUtilisateur($user_id) 
    {
        try {
            // Je me connecte à MySQL
            global $pdo;
            
            $stmt = $pdo->prepare("SELECT pseudo, nom, prenom FROM utilisateurs WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user) {
                return $user['pseudo'] ?? ($user['prenom'] . ' ' . $user['nom']) ?? 'Utilisateur';
            }
            
            return 'Utilisateur';
            
        } catch (Exception $e) {
            error_log("Erreur récupération nom utilisateur : " . $e->getMessage());
            return 'Utilisateur';
        }
    }
}
?>

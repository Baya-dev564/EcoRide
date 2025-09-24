<?php
/**
 * AdminUserController - Contrôleur pour les fonctionnalités avancées de gestion des utilisateurs
 * Contrôleur séparé pour les statistiques et modifications utilisateur depuis l'administration
 */

class AdminUserController
{
    private $pdo;
    private $adminModel;
    
    public function __construct()
    {
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Admin.php';
        
        global $pdo;
        $this->pdo = $pdo;
        $this->adminModel = new Admin();
    }
    
    /**
     * Je vérifie que l'utilisateur connecté est un administrateur
     */
    private function verifierAdminConnecte()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /EcoRide/public/connexion?error=admin_required');
            exit;
        }
        
        return true;
    }
    
    /**
     * Je nettoie et sécurise les données statistiques pour éviter les erreurs
     */
    private function nettoyerDonneesStats($stats)
    {
        // Je m'assure que toutes les valeurs numériques sont du bon type
        $statsNettoyes = [
            // Statistiques de base avec valeurs par défaut sécurisées
            'nb_trajets_proposes' => (int)($stats['nb_trajets_proposes'] ?? 0),
            'nb_trajets_termines' => (int)($stats['nb_trajets_termines'] ?? 0),
            'nb_reservations' => (int)($stats['nb_reservations'] ?? 0),
            'nb_reservations_terminees' => (int)($stats['nb_reservations_terminees'] ?? 0),
            'places_totales' => (int)($stats['places_totales'] ?? 0),
            
            // Valeurs financières - toujours en float pour number_format()
            'distance_totale' => (float)($stats['distance_totale'] ?? 0.0),
            'revenus_totaux' => (float)($stats['revenus_totaux'] ?? 0.0),
            'credits_depenses' => (int)($stats['credits_depenses'] ?? 0),
            'prix_moyen_km' => (float)($stats['prix_moyen_km'] ?? 0.0),
            
            // Moyennes et ratios
            'note_moyenne' => (float)($stats['note_moyenne'] ?? 0.0),
            'taux_completion' => (float)($stats['taux_completion'] ?? 0.0),
            'places_moyenne_par_trajet' => (float)($stats['places_moyenne_par_trajet'] ?? 0.0),
            
            // Dates si présentes
            'dernier_trajet' => $stats['dernier_trajet'] ?? null,
            'membre_depuis' => $stats['membre_depuis'] ?? null,
            
            // Evolution mensuelle avec tableau vide par défaut
            'evolution' => [
                'trajets' => $stats['evolution']['trajets'] ?? [],
                'reservations' => $stats['evolution']['reservations'] ?? []
            ],
            
            // Données supplémentaires
            'nb_vehicules' => (int)($stats['nb_vehicules'] ?? 0),
            'nb_avis_recus' => (int)($stats['nb_avis_recus'] ?? 0),
            'nb_avis_donnes' => (int)($stats['nb_avis_donnes'] ?? 0)
        ];
        
        return $statsNettoyes;
    }
    
    /**
     * J'affiche les statistiques détaillées d'un utilisateur
     */
    public function userStats($userId)
    {
        if (!$this->verifierAdminConnecte()) {
            return;
        }
        
        try {
            $user = $this->adminModel->obtenirUtilisateurParId($userId);
            if (!$user) {
                $_SESSION['error'] = "Utilisateur introuvable";
                header('Location: /admin/utilisateurs');
                exit;
            }
            
            // Je récupère les statistiques brutes
            $statsRaw = $this->adminModel->calculerStatistiquesUtilisateur($userId);
            
            // Je nettoie et sécurise les données
            $stats = $this->nettoyerDonneesStats($statsRaw);
            
            // Je prépare les variables pour la vue
            $title = "Statistiques de " . htmlspecialchars($user['pseudo']) . " - Admin EcoRide";
            $currentPage = 'utilisateurs';
            $userData = $user;
            $userStats = $stats;
            
            // Je charge la vue avec le système de template
            $content = $this->renderView('admin/utilisateurs-stat', compact('userData', 'userStats'));
            
        } catch (Exception $e) {
            error_log("Erreur stats admin: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors du chargement des statistiques : " . $e->getMessage();
            header('Location: /admin/utilisateurs');
            exit;
        }
    }
    
    /**
     * Je rends une vue sans layout complet
     */
    private function renderView($viewPath, $variables = [])
    {
        // J'extrais les variables pour la vue
        extract($variables);
        
        // Je démarre le buffer de sortie
        ob_start();
        
        // J'inclus seulement la vue
        include __DIR__ . "/../Views/{$viewPath}.php";
        
        // Je retourne le contenu
        $content = ob_get_clean();
        
        // Je vérifie si un layout admin existe
        if (file_exists(__DIR__ . '/../Views/layouts/admin-layout.php')) {
            include __DIR__ . '/../Views/layouts/admin-layout.php';
        } else {
            // Sinon j'affiche directement le contenu
            echo $content;
        }
    }

    /**
     * J'affiche le formulaire de modification d'un utilisateur
     */
    public function editUser($userId)
    {
        if (!$this->verifierAdminConnecte()) {
            return;
        }
        
        try {
            $user = $this->adminModel->obtenirUtilisateurParId($userId);
            if (!$user) {
                $_SESSION['error'] = "Utilisateur introuvable";
                header('Location: /admin/utilisateurs');
                exit;
            }
            
            // Je nettoie les données utilisateur pour éviter les erreurs d'affichage
            $userData = [
                'id' => (int)$user['id'],
                'pseudo' => (string)($user['pseudo'] ?? ''),
                'email' => (string)($user['email'] ?? ''),
                'prenom' => (string)($user['prenom'] ?? ''),
                'nom' => (string)($user['nom'] ?? ''),
                'telephone' => (string)($user['telephone'] ?? ''),
                'adresse' => (string)($user['adresse'] ?? ''),
                'code_postal' => (string)($user['code_postal'] ?? ''),
                'ville' => (string)($user['ville'] ?? ''),
                'date_naissance' => $user['date_naissance'] ?? null,
                'bio' => (string)($user['bio'] ?? ''),
                'credit' => (int)($user['credit'] ?? 0),
                'role' => (string)($user['role'] ?? 'user'),
                'statut' => (string)($user['statut'] ?? 'actif'),
                'created_at' => $user['created_at'] ?? null,
                'updated_at' => $user['updated_at'] ?? null,
                
                // Statistiques de base pour l'affichage
                'nb_trajets_proposes' => (int)($user['nb_trajets_proposes'] ?? 0),
                'nb_reservations' => (int)($user['nb_reservations'] ?? 0),
                'nb_vehicules' => (int)($user['nb_vehicules'] ?? 0),
                'note' => (float)($user['note_moyenne'] ?? 0.0)
            ];
            
            // J'utilise le même système de rendu que pour les statistiques
            $this->renderView('admin/utilisateurs-edit', compact('userData'));
            
        } catch (Exception $e) {
            error_log("Erreur edit admin: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors du chargement du formulaire : " . $e->getMessage();
            header('Location: /admin/utilisateurs');
            exit;
        }
    }

    /**
     * Je traite la modification d'un utilisateur
     */
    public function updateUser($userId)
    {
        if (!$this->verifierAdminConnecte()) {
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/utilisateurs');
            exit;
        }
        
        try {
            // Je nettoie et valide les données POST
            $donneesNettoyees = $this->validerDonneesModification($_POST);
            
            $success = $this->adminModel->modifierUtilisateur($userId, $donneesNettoyees);
            
            if ($success) {
                $_SESSION['success'] = "Utilisateur modifié avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la modification";
            }
            
        } catch (Exception $e) {
            error_log("Erreur update admin: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
        
        header('Location: /admin/utilisateurs');
        exit;
    }
    
    /**
     * Je valide et nettoie les données de modification utilisateur
     */
    private function validerDonneesModification($donnees)
    {
        return [
            'pseudo' => trim($donnees['pseudo'] ?? ''),
            'email' => trim($donnees['email'] ?? ''),
            'prenom' => trim($donnees['prenom'] ?? ''),
            'nom' => trim($donnees['nom'] ?? ''),
            'telephone' => trim($donnees['telephone'] ?? ''),
            'adresse' => trim($donnees['adresse'] ?? ''),
            'code_postal' => trim($donnees['code_postal'] ?? ''),
            'ville' => trim($donnees['ville'] ?? ''),
            'date_naissance' => !empty($donnees['date_naissance']) ? $donnees['date_naissance'] : null,
            'bio' => trim($donnees['bio'] ?? ''),
            'credit' => max(0, (int)($donnees['credit'] ?? 0)), // Minimum 0
            'role' => in_array($donnees['role'] ?? '', ['user', 'admin']) ? $donnees['role'] : 'user',
            'statut' => in_array($donnees['statut'] ?? '', ['actif', 'suspendu', 'banni']) ? $donnees['statut'] : 'actif'
        ];
    }

    /**
     * Je modifie les crédits d'un utilisateur via API
     */
    public function modifierCredits()
    {
        if (!$this->verifierAdminConnecte()) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Données JSON invalides');
            }
            
            $userId = (int)($input['user_id'] ?? 0);
            $nouveauxCredits = max(0, (int)($input['nouveaux_credits'] ?? 0)); // Minimum 0
            
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }
            
            if ($nouveauxCredits > 9999) {
                throw new Exception('Maximum 9999 crédits autorisés');
            }
            
            $success = $this->adminModel->modifierCreditsUtilisateur($userId, $nouveauxCredits);
            
            if ($success) {
                echo json_encode([
                    'success' => true, 
                    'message' => "Crédits mis à jour : {$nouveauxCredits} crédits",
                    'nouveaux_credits' => $nouveauxCredits
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
            }
            
        } catch (Exception $e) {
            error_log("Erreur modification crédits: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Je change le statut d'un utilisateur via API
     */
    public function toggleUserStatus()
    {
        if (!$this->verifierAdminConnecte()) {
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Données JSON invalides');
            }
            
            $userId = (int)($input['user_id'] ?? 0);
            $action = $input['action'] ?? '';
            
            if ($userId <= 0) {
                throw new Exception('ID utilisateur invalide');
            }
            
            // Je valide l'action
            $statutsValides = ['suspend', 'activate', 'ban'];
            if (!in_array($action, $statutsValides)) {
                throw new Exception('Action non autorisée');
            }
            
            // Je détermine le nouveau statut
            switch ($action) {
                case 'suspend':
                    $nouveauStatut = 'suspendu';
                    $message = 'Utilisateur suspendu avec succès';
                    break;
                case 'ban':
                    $nouveauStatut = 'banni';
                    $message = 'Utilisateur banni avec succès';
                    break;
                case 'activate':
                default:
                    $nouveauStatut = 'actif';
                    $message = 'Utilisateur réactivé avec succès';
                    break;
            }
            
            $success = $this->adminModel->changerStatutUtilisateur($userId, $nouveauStatut);
            
            if ($success) {
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'nouveau_statut' => $nouveauStatut
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du changement de statut']);
            }
            
        } catch (Exception $e) {
            error_log("Erreur changement statut: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Je log les actions administrateur pour traçabilité
     */
    private function loggerActionAdmin($action, $userId, $details = '')
    {
        $adminId = $_SESSION['user']['id'] ?? 0;
        $adminPseudo = $_SESSION['user']['pseudo'] ?? 'Inconnu';
        
        error_log("ADMIN ACTION - Admin: {$adminPseudo} (ID: {$adminId}) | Action: {$action} | User: {$userId} | Details: {$details}");
    }
}
?>

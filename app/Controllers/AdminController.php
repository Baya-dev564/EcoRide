<?php
/**
 * Contrôleur d'administration EcoRide 
 */

class AdminController {
    
    /**
     * Vérification admin intégrée dans chaque méthode
     */
    private function verifierAdmin() {
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
        
        // Vérifier si l'utilisateur est admin
        if ($_SESSION['user']['role'] !== 'admin') {
            $_SESSION['message'] = 'Accès refusé. Seul l\'administrateur peut accéder à cette page.';
            header('Location: /EcoRide/public/');
            exit;
        }
    }
    
    /**
     * Tableau de bord administrateur
     * Page d'accueil avec statistiques principales
     */
    public function dashboard() {
        // Vérification admin directe
        $this->verifierAdmin();
        
        // Récupérer les statistiques de base
        $stats = $this->getStatistiques();
        
        // Récupérer les informations admin
        $admin = $_SESSION['user'];
        
        // Données pour la vue
        $pageTitle = "Administration EcoRide - Tableau de bord";
        
        // Afficher la vue
        require '../app/views/admin/dashboard.php';
    }
    
    /**
     * Gestion des utilisateurs
     * Liste de tous les utilisateurs avec actions possibles
     */
    public function utilisateurs() {
        // Vérification admin directe
        $this->verifierAdmin();
        
        // Récupérer tous les utilisateurs depuis la base
        require_once '../app/models/User.php';
        require_once '../config/database.php';
        
        global $pdo;
        
        // Requête pour récupérer tous les utilisateurs
        $sql = "SELECT id, pseudo, nom, prenom, email, credit, permis_conduire, 
                       created_at, updated_at, role 
                FROM utilisateurs 
                ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les informations admin
        $admin = $_SESSION['user'];
        
        // Données pour la vue
        $pageTitle = "Gestion des utilisateurs - Admin EcoRide";
        
        // Afficher la vue
        require '../app/views/admin/utilisateurs.php';
    }
    
    /**
     * Modération des avis
     * Gestion des avis avec possibilité de modération
     */
    public function avis() {
        // Vérification admin directe
        $this->verifierAdmin();
        
        // Récupérer tous les avis du système NoSQL
        require_once '../app/models/Avis.php';
        $avis = Avis::getAll();
        
        // Trier les avis par date (plus récents en premier)
        usort($avis, function($a, $b) {
            return strtotime($b->getDateCreation()) - strtotime($a->getDateCreation());
        });
        
        // Récupérer les informations admin
        $admin = $_SESSION['user'];
        
        // Données pour la vue
        $pageTitle = "Modération des avis - Admin EcoRide";
        
        // Afficher la vue
        require '../app/views/admin/avis.php';
    }
    
    /**
     * Récupérer les statistiques de la plateforme
     * Méthode privée pour calculer les stats principales
     * @return array Tableau des statistiques
     */
    private function getStatistiques() {
        require_once '../config/database.php';
        global $pdo;
        
        $stats = [];
        
        try {
            // Nombre total d'utilisateurs (sans l'admin)
            $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role != 'admin'");
            $stats['total_users'] = $stmt->fetchColumn();
            
            // Utilisateurs actifs (connectés dans les 30 derniers jours)
            $stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs 
                                WHERE role != 'admin' 
                                AND updated_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['users_actifs'] = $stmt->fetchColumn();
            
            // Nombre total de trajets
            $stmt = $pdo->query("SELECT COUNT(*) FROM trajets");
            $stats['total_trajets'] = $stmt->fetchColumn();
            
            // Nombre total de réservations
            $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
            $stats['total_reservations'] = $stmt->fetchColumn();
            
            // Nombre total d'avis (depuis le fichier JSON)
            require_once '../app/models/Avis.php';
            $avis = Avis::getAll();
            $stats['total_avis'] = count($avis);
            
            // Crédits totaux en circulation
            $stmt = $pdo->query("SELECT SUM(credit) FROM utilisateurs WHERE role != 'admin'");
            $stats['credits_total'] = $stmt->fetchColumn() ?: 0;
            
        } catch (Exception $e) {
            // En cas d'erreur, mettre des valeurs par défaut
            $stats = [
                'total_users' => 0,
                'users_actifs' => 0,
                'total_trajets' => 0,
                'total_reservations' => 0,
                'total_avis' => 0,
                'credits_total' => 0
            ];
        }
        
        return $stats;
    }
}
?>

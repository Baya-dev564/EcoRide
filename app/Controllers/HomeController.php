<?php
/**
 * HomeController - Contrôleur pour la page d'accueil EcoRide
 */

class HomeController
{
    /**
     * J'affiche la page d'accueil EcoRide
     * 
     * Cette méthode gère l'affichage de la page d'accueil avec :
     * - Présentation des avantages du covoiturage écologique
     * - Statistiques de la plateforme EcoRide
     * - Interface adaptée selon l'état de connexion
     * - Liens vers les fonctionnalités principales
     */
    public function index()
    {
        // Je vérifie l'utilisateur connecté pour adapter l'affichage
        // Si connecté : affichage personnalisé avec crédits et actions rapides
        // Si non connecté : affichage général avec incitation à l'inscription
        $user = $_SESSION['user'] ?? null;
        
        // Je récupère et nettoie les messages de session
        // Messages de confirmation après connexion, déconnexion, etc.
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']); // Je nettoie après affichage
        
        // Je définis les données pour la vue Bootstrap
        $title = "Accueil | EcoRide - Covoiturage Écologique";
        
        // Je prépare les statistiques de la plateforme EcoRide pour la page d'accueil
        // Dans un vrai projet, ces données viendraient de la base de données
        // Ici, j'utilise des données statiques représentatives
        $stats = [
            'trajets_total' => 1250,           // Nombre total de trajets proposés
            'co2_economise' => '2.5T',         // CO₂ économisé grâce au covoiturage
            'utilisateurs_actifs' => 850,      // Nombre d'utilisateurs actifs
            'vehicules_electriques' => 45      // Pourcentage de véhicules électriques
        ];
        
        // J'appelle la vue d'accueil avec Bootstrap 5
        // La vue adaptera l'affichage selon l'état de connexion
        require __DIR__ . '/../Views/home/index.php';
    }
}
?>

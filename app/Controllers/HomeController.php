<?php
/**
 * Contrôleur pour la page d'accueil EcoRide
 */

class HomeController
{
    /**
     * Affiche la page d'accueil EcoRide
     * 
     * Cette méthode gère l'affichage de la page d'accueil avec :
     * - Présentation des avantages du covoiturage écologique
     * - Statistiques de la plateforme EcoRide
     * - Interface adaptée selon l'état de connexion
     * - Liens vers les fonctionnalités principales
     * 
     * Route : GET /
     */
    public function index()
    {
        // Vérification de l'utilisateur connecté
        // Si connecté : affichage personnalisé avec crédits et actions rapides
        // Si non connecté : affichage général avec incitation à l'inscription
        $user = $_SESSION['user'] ?? null;
        
        // Récupération et nettoyage des messages de session
        // Messages de confirmation (ex: après connexion, déconnexion, etc.)
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']); // Nettoyage après affichage
        
        // Données pour la vue Bootstrap
        $title = "Accueil | EcoRide - Covoiturage Écologique";
        
        // Statistiques de la plateforme EcoRide pour la page d'accueil
        // Dans un vrai projet, ces données viendraient de la base de données
        // Ici,  j'utilise des données statiques 
        $stats = [
            'trajets_total' => 1250,           // Nombre total de trajets proposés
            'co2_economise' => '2.5T',         // CO₂ économisé grâce au covoiturage
            'utilisateurs_actifs' => 850,      // Nombre d'utilisateurs actifs
            'vehicules_electriques' => 45      // Pourcentage de véhicules électriques
        ];
        
        // Appel de la vue d'accueil avec Bootstrap 5
        // La vue adaptera l'affichage selon l'état de connexion ($user)
        require __DIR__ . '/../Views/home/index.php';
    }
}
?>

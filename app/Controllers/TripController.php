<?php
/**
 * Contrôleur pour la gestion des trajets de covoiturage EcoRide
 */

class TripController
{
    private $tripModel;
    
    /**
     * Constructeur avec injection de dépendance
     */
    public function __construct()
    {
        // Initialisation de la connexion base de données
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Trip.php';
        
        global $pdo;
        $this->tripModel = new Trip($pdo);
    }
    
    /**
     * Page de recherche avec tous les filtres fonctionnels
     * 
     * Affiche la page de recherche des trajets avec résultats filtrés,
     * tri dynamique et pagination complète
     */
    public function index()
    {
        //  Récupération complète de tous les critères
        $criteres = [
            'lieu_depart' => $this->validerLieu($_GET['lieu_depart'] ?? ''),
            'lieu_arrivee' => $this->validerLieu($_GET['lieu_arrivee'] ?? ''),
            'date_depart' => $this->validerDate($_GET['date_depart'] ?? ''),
            'vehicule_electrique' => isset($_GET['vehicule_electrique']) ? true : false,
            //  Filtres avancés fonctionnels
            'prix_min' => !empty($_GET['prix_min']) ? (int)$_GET['prix_min'] : '',
            'prix_max' => !empty($_GET['prix_max']) ? (int)$_GET['prix_max'] : '',
            'note_min' => !empty($_GET['note_min']) ? (float)$_GET['note_min'] : '',
            //  Paramètres de tri dynamique
            'tri' => $_GET['tri'] ?? 'date_depart',
            'direction' => $_GET['direction'] ?? 'ASC'
        ];
        
        // Gestion de la pagination avec validation
        $page = max(1, min(100, (int)($_GET['page'] ?? 1)));
        $limit = 8; // Nombre de trajets par page
        
        try {
            // Recherche des trajets 
            $resultats = $this->tripModel->rechercherTrajets($criteres, $page, $limit);
            
            if (!$resultats['succes']) {
                throw new Exception($resultats['erreur'] ?? 'Erreur de recherche');
            }
            
            $trajets = $resultats['trajets'];
            $pagination = $resultats['pagination'];
            
            //  Calcul complet de la pagination
            if ($pagination) {
                $pagination['total_pages'] = ceil($pagination['total_trajets'] / $limit);
                $pagination['page_actuelle'] = $page;
            }
            
            //  Calcul des statistiques pour l'affichage
            $stats = $this->calculerStatistiques($trajets, $pagination['total_trajets']);
            
        } catch (Exception $e) {
            // Gestion d'erreur robuste avec logs
            error_log("Erreur recherche trajets : " . $e->getMessage());
            $trajets = [];
            $pagination = null;
            $stats = ['total_trajets' => 0, 'trajets_electriques' => 0, 'prix_moyen' => 0];
            $_SESSION['erreur'] = 'Erreur lors de la recherche des trajets.';
        }
        
        //  Variables complètes pour la vue
        $this->afficherVueIndex($criteres, $trajets, $pagination, $stats);
    }
    
    /**
     * Méthode dédiée pour afficher la vue index
     * 
     * Centralise la préparation des variables pour la vue
     */
    private function afficherVueIndex($criteres, $trajets, $pagination, $stats)
    {
        // Métadonnées de la page
        $title = "Recherche de trajets | EcoRide - Covoiturage écologique";
        
        // Indicateurs pour la vue
        $hasSearch = !empty($criteres['lieu_depart']) || !empty($criteres['lieu_arrivee']) || !empty($criteres['date_depart']);
        
        // Messages système
        $message = $_SESSION['message'] ?? '';
        $erreur = $_SESSION['erreur'] ?? '';
        unset($_SESSION['message'], $_SESSION['erreur']);
        
    
        require __DIR__ . '/../Views/trips/index.php';
    }
    
    /**
     *  Calcule les statistiques d'affichage
     * 
     * @param array $trajets Liste des trajets trouvés
     * @param int $totalTrajets Nombre total de trajets
     * @return array Statistiques formatées
     */
    private function calculerStatistiques($trajets, $totalTrajets)
    {
        $stats = [
            'total_trajets' => $totalTrajets,
            'trajets_electriques' => 0,
            'prix_moyen' => 0
        ];
        
        if (!empty($trajets)) {
            // Comptage des trajets écologiques
            $stats['trajets_electriques'] = count(array_filter($trajets, function($t) { 
                return $t['vehicule_electrique']; 
            }));
            
            // Calcul du prix moyen
            $prix_total = array_sum(array_column($trajets, 'prix'));
            $stats['prix_moyen'] = $prix_total > 0 ? round($prix_total / count($trajets)) : 0;
        }
        
        return $stats;
    }
    
    /**
     * Affichage des détails avec vue dédiée
     * 
     * Affiche les détails complets d'un trajet spécifique
     * avec toutes les informations nécessaires
     */
    /**
 * Affiche les détails complets d'un trajet
 */
public function details($trajetId = null)
{
    // Si pas d'ID passé en paramètre, extraire de l'URL
    if ($trajetId === null) {
        $trajetId = $this->extraireIdDepuisUrl('trajet');
    }
    
    if (!$trajetId) {
        $_SESSION['erreur'] = 'Trajet non trouvé.';
        header('Location: /EcoRide/public/trajets');
        exit;
    }
    
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../Models/Trip.php';
    
    global $pdo;
    $tripModel = new Trip($pdo);
    
    $trajet = $tripModel->getTrajetDetails($trajetId);
    
    if (!$trajet) {
        $_SESSION['erreur'] = 'Ce trajet n\'existe pas ou n\'est plus disponible.';
        header('Location: /EcoRide/public/trajets');
        exit;
    }
    
    $userConnecte = $_SESSION['user'] ?? null;
    $peutReserver = false;
    
    if ($userConnecte) {
        $peutReserver = ($userConnecte['id'] != $trajet['conducteur_id']) 
                     && ($trajet['places_disponibles'] > 0)
                     && ($userConnecte['credit'] >= $trajet['prix'])
                     && !$this->aDejaReserve($trajetId, $userConnecte['id']);
    }
    
    $message = $_SESSION['message'] ?? '';
    $erreur = $_SESSION['erreur'] ?? '';
    unset($_SESSION['message'], $_SESSION['erreur']);
    
    $title = "Trajet {$trajet['lieu_depart']} → {$trajet['lieu_arrivee']} | EcoRide";
    
    require __DIR__ . '/../Views/trips/details.php';
}

    /**
     *  Vérifie si l'utilisateur peut réserver un trajet
     * 
     * @param array $trajet Données du trajet
     * @param array|null $user Utilisateur connecté
     * @return bool True si la réservation est possible
     */
    private function peutReserverTrajet($trajet, $user)
    {
        if (!$user) {
            return false; // Utilisateur non connecté
        }
        
        if ($user['id'] == $trajet['conducteur_id']) {
            return false; // Conducteur ne peut pas réserver son propre trajet
        }
        
        if ($trajet['places_disponibles'] <= 0) {
            return false; // Plus de places disponibles
        }
        
        if ($this->aDejaReserve($trajet['id'], $user['id'])) {
            return false; // Déjà réservé
        }
        
        return true;
    }
    
    /**
     * Affiche les trajets de l'utilisateur connecté
     * 
     * Page de gestion personnelle des trajets proposés
     */
    public function mesTrajets()
    {
        // Vérification de l'authentification
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour voir vos trajets.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
        
        // Récupération des trajets de l'utilisateur
        $trajets = $this->tripModel->getTrajetsUtilisateur($_SESSION['user']['id']);
        
        // Préparation des variables pour la vue
        $title = "Mes trajets | EcoRide - Gestion de vos trajets proposés";
        $user = $_SESSION['user'];
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        
        require __DIR__ . '/../Views/trips/mes-trajets.php';
    }
    
    /**
     * Affichage du formulaire de création avec gestion des véhicules
     * 
     * Affiche le formulaire de création d'un nouveau trajet
     * avec récupération des véhicules de l'utilisateur
     */
    public function nouveauTrajet()
    {
        // Vérification de l'authentification obligatoire
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour proposer un trajet.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
               if (empty($_SESSION['user']['permis_conduire'])) {
        $_SESSION['erreur'] = 'Vous devez avoir un permis de conduire validé pour proposer un trajet.';
        header('Location: /EcoRide/public/profil');
        exit;
    
        }
        
        // Récupération des véhicules de l'utilisateur
        $vehicules = $this->recupererVehiculesUtilisateur($_SESSION['user']['id']);
        
        // Variables pour la vue avec gestion des erreurs
        $title = "Proposer un trajet | EcoRide - Partagez votre trajet";
        $user = $_SESSION['user'];
        $erreurs = $_SESSION['erreurs_trajet'] ?? [];
        $donnees = $_SESSION['donnees_trajet'] ?? [];
        $message = $_SESSION['message'] ?? '';
        
        // Nettoyage des variables de session
        unset($_SESSION['erreurs_trajet'], $_SESSION['donnees_trajet'], $_SESSION['message']);
        
        require __DIR__ . '/../Views/trips/nouveau-trajet.php';
    }
    
    /**
     *  Récupère les véhicules d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des véhicules
     */
    private function recupererVehiculesUtilisateur($userId)
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            $sql = "SELECT id, marque, modele, couleur, electrique, plaque_immatriculation 
                    FROM vehicules 
                    WHERE utilisateur_id = ? 
                    ORDER BY marque, modele";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération véhicules : " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Traitement de création avec validation centralisée
     * 
     * Traite la soumission du formulaire de création de trajet
     */
    public function creerTrajet()
    {
        // Vérifications préliminaires
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour proposer un trajet.';
            header('Location: /EcoRide/public/connexion');
            exit;
        }
           if (empty($_SESSION['user']['permis_conduire'])) {
        $_SESSION['erreur'] = 'Vous devez avoir un permis de conduire validé pour proposer un trajet.';
        header('Location: /EcoRide/public/profil');
        exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /EcoRide/public/nouveau-trajet');
            exit;
        }
        
        //  Validation centralisée des données
        $data = $this->extraireDonneesTrajet($_POST);
        $erreurs = $this->validerDonneesControleur($data);
        
        if (!empty($erreurs)) {
            $_SESSION['erreurs_trajet'] = $erreurs;
            $_SESSION['donnees_trajet'] = $data;
            header('Location: /EcoRide/public/nouveau-trajet');
            exit;
        }
        
        // Création du trajet via le modèle
        $resultat = $this->tripModel->creerTrajet($data);
        
        if ($resultat['succes']) {
            $_SESSION['message'] = $resultat['message'] . " Prix calculé : {$resultat['prix_calcule']} crédits.";
            header('Location: /EcoRide/public/mes-trajets');
            exit;
        } else {
            $_SESSION['erreurs_trajet'] = $resultat['erreurs'];
            $_SESSION['donnees_trajet'] = $data;
            header('Location: /EcoRide/public/nouveau-trajet');
            exit;
        }
    }
    
    /**
     *   Extrait et nettoie les données du formulaire
     * 
     * @param array $post Données POST
     * @return array Données nettoyées
     */
    private function extraireDonneesTrajet($post)
    {
        return [
            'conducteur_id' => $_SESSION['user']['id'],
            'vehicule_id' => intval($post['vehicule_id'] ?? 0) ?: null,
            'lieu_depart' => trim($post['lieu_depart'] ?? ''),
            'code_postal_depart' => trim($post['code_postal_depart'] ?? ''),
            'lieu_arrivee' => trim($post['lieu_arrivee'] ?? ''),
            'code_postal_arrivee' => trim($post['code_postal_arrivee'] ?? ''),
            'date_depart' => $post['date_depart'] ?? '',
            'heure_depart' => $post['heure_depart'] ?? '',
            'places' => intval($post['places'] ?? 1),
            'vehicule_electrique' => isset($post['vehicule_electrique']),
            'commentaire' => trim($post['commentaire'] ?? '')
        ];
    }
    
    /**
     *  Validation côté contrôleur (validation rapide)
     * 
     * @param array $data Données à valider
     * @return array Erreurs trouvées
     */
    private function validerDonneesControleur($data)
    {
        $erreurs = [];
        
        // Validations essentielles côté contrôleur
        if (empty($data['lieu_depart'])) {
            $erreurs[] = 'Le lieu de départ est obligatoire.';
        }
        
        if (empty($data['lieu_arrivee'])) {
            $erreurs[] = 'Le lieu d\'arrivée est obligatoire.';
        }
        
        if (empty($data['date_depart']) || empty($data['heure_depart'])) {
            $erreurs[] = 'La date et l\'heure de départ sont obligatoires.';
        }
        
        if ($data['places'] < 1 || $data['places'] > 8) {
            $erreurs[] = 'Le nombre de places doit être entre 1 et 8.';
        }
        
        return $erreurs;
    }
    
    /**
     *  API pour la recherche AJAX avec gestion complète
     * 
     * Endpoint pour les recherches en temps réel depuis JavaScript
     */
    public function apiRecherche()
    {
        // Headers pour API JSON
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        //  Récupération complète des critères
        $criteres = [
            'lieu_depart' => $this->validerLieu($_GET['lieu_depart'] ?? ''),
            'lieu_arrivee' => $this->validerLieu($_GET['lieu_arrivee'] ?? ''),
            'date_depart' => $this->validerDate($_GET['date_depart'] ?? ''),
            'vehicule_electrique' => isset($_GET['vehicule_electrique']),
            'prix_min' => !empty($_GET['prix_min']) ? (int)$_GET['prix_min'] : '',
            'prix_max' => !empty($_GET['prix_max']) ? (int)$_GET['prix_max'] : '',
            'note_min' => !empty($_GET['note_min']) ? (float)$_GET['note_min'] : ''
        ];
        
        try {
            $resultats = $this->tripModel->rechercherTrajets($criteres, 1, 20);
            
            if ($resultats['succes']) {
                echo json_encode([
                    'success' => true,
                    'trajets' => $resultats['trajets'],
                    'total' => $resultats['pagination']['total_trajets'],
                    'timestamp' => time()
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Erreur lors de la recherche des trajets'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Erreur API recherche : " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Erreur technique'
            ]);
        }
        
        exit;
    }
    
    /**
     * MÉTHODES UTILITAIRES PRIVÉES
     */
    
    /**
     * Extrait l'ID depuis l'URL pour le routing
     * 
     * @param string $route Nom de la route
     * @return int|null ID extrait ou null
     */
    private function extraireIdDepuisUrl($route)
    {
        $uri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', trim($uri, '/'));
        
        foreach ($segments as $key => $segment) {
            if ($segment === $route && isset($segments[$key + 1])) {
                return (int)$segments[$key + 1];
            }
        }
        
        return null;
    }
    
    /**
     * Vérifie si un utilisateur a déjà réservé un trajet
     * 
     * @param int $trajetId ID du trajet
     * @param int $userId ID de l'utilisateur
     * @return bool True si déjà réservé
     */
    private function aDejaReserve($trajetId, $userId)
    {
        try {
            require_once __DIR__ . '/../../config/database.php';
            global $pdo;
            
            $sql = "SELECT COUNT(*) FROM reservations 
                    WHERE trajet_id = ? AND passager_id = ? AND statut = 'confirme'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$trajetId, $userId]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            error_log("Erreur vérification réservation : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valide et nettoie un lieu de recherche
     * 
     * @param string $lieu Lieu à valider
     * @return string Lieu validé ou chaîne vide
     */
    private function validerLieu($lieu)
    {
        $lieu = trim($lieu);
        return strlen($lieu) >= 2 && strlen($lieu) <= 100 ? $lieu : '';
    }
    
    /**
     * Valide une date de recherche
     * 
     * @param string $date Date à valider
     * @return string Date validée ou chaîne vide
     */
    private function validerDate($date)
    {
        if (empty($date)) return '';
        
        $timestamp = strtotime($date);
        return $timestamp && $timestamp >= strtotime('today') ? $date : '';
    }
}
?>

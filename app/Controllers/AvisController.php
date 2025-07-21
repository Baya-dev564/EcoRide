<?php
/**
 * Contrôleur principal pour les avis EcoRide (MVC + NoSQL-JSON)
 */

// Inclusion du modèle Avis
require_once '../app/models/Avis.php';

class AvisController {

    /** Page liste de tous les avis **/
    public function index() {
        $avis = Avis::getAll();                     // Récupère tous les avis du JSON
        $pageTitle = "Avis des utilisateurs - EcoRide";
        require '../app/views/avis/index.php';      // Inclut la vue liste (index)
    }

    /** Formulaire de création d'avis **/
    public function create() {
        $trajet_id = $_GET['trajet_id'] ?? '';
        $conducteur_id = $_GET['conducteur_id'] ?? '';
        $pageTitle = "Donner un avis - EcoRide";
        $errors = [];
        require '../app/views/avis/create.php';
    }

    /** API POST AJAX : reçoit un avis et le stocke en JSON (NoSQL) **/
public function apiStore() {
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
}
 // toujours tout en haut
    header('Content-Type: application/json');

    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) throw new Exception("Aucune donnée reçue ou JSON mal formé");

        // Récupération user, fusion des datas...
        $user_id = $_SESSION['user_id'] ?? null;
        $pseudo = $_SESSION['pseudo'] ?? null;
        if (!$user_id || !$pseudo) throw new Exception("Utilisateur non connecté");
        $data['user_id'] = $user_id;
        $data['pseudo'] = $pseudo;

        $avis = new Avis($data);
        if (!$avis->save()) throw new Exception("Erreur lors de l'enregistrement");

        echo json_encode(['succes' => true, 'message' => "Avis enregistré !"]);
        exit;
    } catch (Exception $e) {
        // Toujours en JSON strict !
        http_response_code(400);
        echo json_encode(['succes' => false, 'message' => $e->getMessage()]);
        exit;
    }
    if (empty($_SESSION['user_id']) || empty($_SESSION['pseudo'])) {
    http_response_code(401);
    echo json_encode(['succes' => false, 'message' => "Vous devez être connecté pour publier un avis (session vide)."]);
    exit;
}

}


    /** Affichage des avis d'un conducteur (profil) **/
    public function show($conducteur_id) {
        $avis = Avis::getByConducteur($conducteur_id);   // Filtre les avis pour ce conducteur
        // Calcul de la note moyenne si au moins 1 avis
        $note_moyenne = 0;
        if (count($avis) > 0) {
            $total = 0;
            foreach ($avis as $a) { $total += $a->getNoteGlobale(); }
            $note_moyenne = $total / count($avis);
        }
        $pageTitle = "Avis du conducteur - EcoRide";
        require '../app/views/avis/show.php';            // Affiche la vue profil conducteur
    }
}
?>

<?php
/**
 * Contrôleur pour la gestion des réservations EcoRide
 
 */

class ReservationController
{
    /**
     * Traite la réservation d'un trajet
     */
    public function reserver()
    {
        // Vérification de l'authentification
        if (!isset($_SESSION['user'])) {
            $_SESSION['erreur'] = 'Vous devez être connecté pour réserver un trajet.';
            header('Location: /connexion');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['erreur'] = 'Méthode non autorisée.';
            header('Location: /trajets');
            exit;
        }
        
        $trajetId = (int)($_POST['trajet_id'] ?? 0);
        $userId = $_SESSION['user']['id'];
        
        // Données optionnelles selon votre structure
        $messagePassager = trim($_POST['message_passager'] ?? '');
        $telephoneContact = trim($_POST['telephone_contact'] ?? '');
        
        if (!$trajetId) {
            $_SESSION['erreur'] = 'Trajet non spécifié.';
            header('Location: /trajets');
            exit;
        }
        
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Reservation.php';
        
        global $pdo;
        $reservationModel = new Reservation($pdo);
        
        $resultat = $reservationModel->creerReservation(
            $trajetId, 
            $userId, 
            $messagePassager ?: null, 
            $telephoneContact ?: null
        );
        
        if ($resultat['succes']) {
            $_SESSION['message'] = $resultat['message'];
            header('Location: /mes-reservations');
        } else {
            $_SESSION['erreur'] = $resultat['erreur'];
            header('Location: /trajet/' . $trajetId);
        }
        exit;
    }
    
    /**
     * Affiche les réservations de l'utilisateur
     */
    public function mesReservations()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Vous devez être connecté pour voir vos réservations.';
            header('Location: /connexion');
            exit;
        }
        
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Reservation.php';
        
        global $pdo;
        $reservationModel = new Reservation($pdo);
        
        $reservations = $reservationModel->getReservationsUtilisateur($_SESSION['user']['id']);
        
        $title = "Mes réservations | EcoRide - Gestion de vos réservations";
        $user = $_SESSION['user'];
        $message = $_SESSION['message'] ?? '';
        $erreur = $_SESSION['erreur'] ?? '';
        unset($_SESSION['message'], $_SESSION['erreur']);
        
        require __DIR__ . '/../Views/reservations/mes-reservations.php';
    }
    
   /**
 * Annule une réservation avec validation renforcée
 */
public function annuler()
{
    if (!isset($_SESSION['user'])) {
        // Si c'est AJAX, retourner JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['succes' => false, 'erreur' => 'Vous devez être connecté.']);
            exit;
        }
        $_SESSION['erreur'] = 'Vous devez être connecté.';
        header('Location: /connexion');
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['succes' => false, 'erreur' => 'Méthode non autorisée.']);
            exit;
        }
        $_SESSION['erreur'] = 'Méthode non autorisée.';
        header('Location: /mes-reservations');
        exit;
    }
    
    $reservationId = (int)($_POST['reservation_id'] ?? 0);
    $motifAnnulation = trim($_POST['motif_annulation'] ?? '');
    
    if (!$reservationId) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['succes' => false, 'erreur' => 'Réservation non spécifiée.']);
            exit;
        }
        $_SESSION['erreur'] = 'Réservation non spécifiée.';
        header('Location: /mes-reservations');
        exit;
    }
    
    // Validation du motif d'annulation
    if (strlen($motifAnnulation) > 500) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['succes' => false, 'erreur' => 'Le motif d\'annulation est trop long (500 caractères maximum).']);
            exit;
        }
        $_SESSION['erreur'] = 'Le motif d\'annulation est trop long (500 caractères maximum).';
        header('Location: /mes-reservations');
        exit;
    }
    
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../Models/Reservation.php';
    
    global $pdo;
    $reservationModel = new Reservation($pdo);
    
    $resultat = $reservationModel->annulerReservation(
        $reservationId, 
        $_SESSION['user']['id'], 
        $motifAnnulation ?: null
    );
    
    // IMPORTANT : Différencier réponse AJAX vs redirection normale
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // Requête AJAX : retourner JSON
        header('Content-Type: application/json');
        if ($resultat['succes']) {
            echo json_encode(['succes' => true, 'message' => $resultat['message']]);
        } else {
            echo json_encode(['succes' => false, 'erreur' => $resultat['erreur']]);
        }
        exit;
    } else {
        // Requête normale : redirection avec message en session
        if ($resultat['succes']) {
            $_SESSION['message'] = $resultat['message'];
        } else {
            $_SESSION['erreur'] = $resultat['erreur'];
        }
        header('Location: /mes-reservations');
        exit;
    }
}

    /**
    *  Détails d'une réservation spécifique
     */
    public function details($reservationId = null)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['erreur'] = 'Vous devez être connecté.';
            header('Location: /connexion');
            exit;
        }
        
        if (!$reservationId) {
            $_SESSION['erreur'] = 'Réservation non spécifiée.';
            header('Location: /mes-reservations');
            exit;
        }
        
        require_once __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Reservation.php';
        
        global $pdo;
        $reservationModel = new Reservation($pdo);
        
        // Récupérer les détails complets de la réservation
        try {
            $sql = "SELECT r.*, 
                           t.lieu_depart, t.lieu_arrivee, t.date_depart, t.prix, t.commentaire,
                           u.pseudo as conducteur_pseudo, u.telephone as conducteur_telephone,
                           u.email as conducteur_email
                    FROM reservations r
                    JOIN trajets t ON r.trajet_id = t.id
                    JOIN utilisateurs u ON t.conducteur_id = u.id
                    WHERE r.id = ? AND r.passager_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$reservationId, $_SESSION['user']['id']]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                $_SESSION['erreur'] = 'Réservation non trouvée.';
                header('Location: /mes-reservations');
                exit;
            }
            
            $title = "Détail de la réservation | EcoRide";
            $user = $_SESSION['user'];
            $message = $_SESSION['message'] ?? '';
            $erreur = $_SESSION['erreur'] ?? '';
            unset($_SESSION['message'], $_SESSION['erreur']);
            
            require __DIR__ . '/../Views/reservations/details.php';
            
        } catch (PDOException $e) {
            error_log("Erreur détails réservation : " . $e->getMessage());
            $_SESSION['erreur'] = 'Erreur lors de la récupération des détails.';
            header('Location: /mes-reservations');
            exit;
        }
    }
    /**
 * Validation du trajet par le passager après fin du trajet
 */
public function validerTrajet()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Méthode non autorisée';
        exit;
    }

    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo 'Vous devez être connecté.';
        exit;
    }

    $reservationId = (int)($_POST['reservation_id'] ?? 0);
    if (!$reservationId) {
        $_SESSION['erreur'] = 'Réservation non spécifiée.';
        header('Location: /mes-reservations');
        exit;
    }

    require_once __DIR__ . '/../Models/Reservation.php';
    global $pdo;
    $reservationModel = new Reservation($pdo);

    $resultat = $reservationModel->validerTrajet($reservationId, $_SESSION['user']['id']);

    if ($resultat['succes']) {
        $_SESSION['message'] = 'Validation prise en compte. Merci pour votre retour !';
    } else {
        $_SESSION['erreur'] = $resultat['erreur'] ?? 'Erreur lors de la validation.';
    }

    header('Location: /mes-reservations');
    exit;
}

}
?>

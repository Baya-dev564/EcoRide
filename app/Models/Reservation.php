<?php
/**
 * Modèle Reservation pour la gestion des réservations EcoRide
 */

class Reservation
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function creerReservation($trajetId, $passagerId, $messagePassager = null, $telephoneContact = null)
    {
        try {
            $this->pdo->beginTransaction();
            
            //  Requête SQL avec jointure appropriée
            $sql = "SELECT t.*, u.credit FROM trajets t 
                    JOIN utilisateurs u ON t.conducteur_id = u.id
                    WHERE t.id = ? AND t.statut = 'ouvert' AND t.places > 0";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$trajetId]);
            $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$trajet) {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreur' => 'Trajet non disponible.'];
            }
            
            // Vérification séparée du crédit du passager
            $sql = "SELECT credit FROM utilisateurs WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$passagerId]);
            $passager = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$passager) {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreur' => 'Utilisateur non trouvé.'];
            }
            
            // Vérifications métier
            if ($trajet['conducteur_id'] == $passagerId) {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreur' => 'Vous ne pouvez pas réserver votre propre trajet.'];
            }
            
            // Utiliser le crédit du passager, pas du conducteur
            if ($passager['credit'] < $trajet['prix']) {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreur' => 'Crédit insuffisant pour cette réservation.'];
            }
            
            // Vérifier si déjà réservé
            $sql = "SELECT COUNT(*) FROM reservations 
                    WHERE trajet_id = ? AND passager_id = ? AND statut = 'confirme'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$trajetId, $passagerId]);
            
            if ($stmt->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreur' => 'Vous avez déjà réservé ce trajet.'];
            }
            
            // Créer la réservation 
            $sql = "INSERT INTO reservations (
                        trajet_id, passager_id, nb_places, statut, 
                        date_reservation, credits_utilises, message_passager, telephone_contact
                    ) VALUES (?, ?, 1, 'confirme', NOW(), ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $trajetId, 
                $passagerId, 
                $trajet['prix'],  // credits_utilises
                $messagePassager, 
                $telephoneContact
            ]);
            
            $reservationId = $this->pdo->lastInsertId();
            
            // Déduire les crédits de l'utilisateur
            $sql = "UPDATE utilisateurs SET credit = credit - ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$trajet['prix'], $passagerId]);
            
            // Diminuer les places disponibles du trajet
            $sql = "UPDATE trajets SET places = places - 1 WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$trajetId]);
            
            // Mettre à jour le crédit en session
            $_SESSION['user']['credit'] -= $trajet['prix'];
            
            $this->pdo->commit();
            
            return [
                'succes' => true,
                'message' => 'Réservation confirmée ! Vous avez été débité de ' . $trajet['prix'] . ' crédits.',
                'reservation_id' => $reservationId
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur création réservation : " . $e->getMessage());
            return ['succes' => false, 'erreur' => 'Erreur technique lors de la réservation.'];
        }
    }
    
    /**
     * Récupère les réservations d'un utilisateur avec enrichissement des données
     */
    public function getReservationsUtilisateur($userId)
    {
        try {
            $sql = "SELECT r.*, 
                           t.lieu_depart, t.lieu_arrivee, t.date_depart, t.prix,
                           u.pseudo as conducteur_pseudo, u.telephone as conducteur_telephone
                    FROM reservations r
                    JOIN trajets t ON r.trajet_id = t.id
                    JOIN utilisateurs u ON t.conducteur_id = u.id
                    WHERE r.passager_id = ?
                    ORDER BY r.date_reservation DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            //  Enrichir les données pour l'affichage
            foreach ($reservations as &$reservation) {
                $reservation['date_depart_formatee'] = date('d/m/Y à H:i', strtotime($reservation['date_depart']));
                $reservation['date_reservation_formatee'] = date('d/m/Y à H:i', strtotime($reservation['date_reservation']));
                
                // Calculer le statut d'affichage
                $reservation['peut_annuler'] = ($reservation['statut'] === 'confirme');
                $reservation['est_passe'] = (strtotime($reservation['date_depart']) < time());
            }
            
            return $reservations;
            
        } catch (PDOException $e) {
            error_log("Erreur getReservationsUtilisateur : " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Annule une réservation avec remboursement
     */
    public function annulerReservation($reservationId, $userId, $motifAnnulation = null)
    {
        try {
            $this->pdo->beginTransaction();
            
            // Vérifier que la réservation appartient à l'utilisateur et peut être annulée
            $sql = "SELECT r.*, t.prix, t.date_depart FROM reservations r
                    JOIN trajets t ON r.trajet_id = t.id
                    WHERE r.id = ? AND r.passager_id = ? AND r.statut = 'confirme'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$reservationId, $userId]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$reservation) {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreur' => 'Réservation non trouvée ou déjà annulée.'];
            }
            
            //  Vérifier si le trajet n'est pas déjà passé
            if (strtotime($reservation['date_depart']) < time()) {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreur' => 'Impossible d\'annuler un trajet déjà effectué.'];
            }
            
            // Mettre à jour la réservation 
            $sql = "UPDATE reservations 
                    SET statut = 'annule', date_annulation = NOW(), motif_annulation = ?
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$motifAnnulation, $reservationId]);
            
            // Rembourser les crédits
            $sql = "UPDATE utilisateurs SET credit = credit + ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$reservation['credits_utilises'], $userId]);
            
            // Remettre la place disponible
            $sql = "UPDATE trajets SET places = places + 1 WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$reservation['trajet_id']]);
            
            //  Mettre à jour le crédit en session
            if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] == $userId) {
                $_SESSION['user']['credit'] += $reservation['credits_utilises'];
            }
            
            $this->pdo->commit();
            
            return [
                'succes' => true,
                'message' => 'Réservation annulée. Vous avez été remboursé de ' . $reservation['credits_utilises'] . ' crédits.'
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur annulation réservation : " . $e->getMessage());
            return ['succes' => false, 'erreur' => 'Erreur lors de l\'annulation.'];
        }
    }
}
?>

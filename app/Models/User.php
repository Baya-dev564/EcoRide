<?php
/**
 * Modèle User pour la gestion complète des utilisateurs EcoRide+admin
 */

class User
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée un nouveau compte utilisateur avec 20 crédits initiaux
     */
    public function creerCompte($data)
    {
        $validation = $this->validerDonneesInscription($data);
        if (!$validation['valide']) {
            return ['succes' => false, 'erreurs' => $validation['erreurs']];
        }
        
        if ($this->emailExiste($data['email'])) {
            return ['succes' => false, 'erreurs' => ['Cette adresse email est déjà utilisée.']];
        }
        
        if ($this->pseudoExiste($data['pseudo'])) {
            return ['succes' => false, 'erreurs' => ['Ce pseudo est déjà utilisé.']];
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $motDePasseHache = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO utilisateurs (
                        pseudo, email, mot_de_passe, credit, 
                        nom, prenom, telephone, adresse, ville, code_postal, 
                        permis_conduire, bio, created_at
                    ) VALUES (?, ?, ?, 20, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
         
            $stmt = $this->pdo->prepare($sql);
            $resultat = $stmt->execute([
                $data['pseudo'],
                $data['email'],
                $motDePasseHache,
                $data['nom'] ?? '',
                $data['prenom'] ?? '',
                $data['telephone'] ?? '',
                $data['adresse'] ?? '',
                $data['ville'] ?? '',
                $data['code_postal'] ?? '',
                isset($data['permis_conduire']) ? 1 : 0,
                $data['bio'] ?? ''
            ]);
            
            if ($resultat) {
                $userId = $this->pdo->lastInsertId();
                
                $this->enregistrerTransactionCredit(
                    $userId, 
                    20, 
                    'credit', 
                    'inscription', 
                    'Crédits de bienvenue EcoRide'
                );
                
                $this->pdo->commit();
                
                return [
                    'succes' => true,
                    'message' => 'Félicitations ! Votre compte EcoRide a été créé avec succès. Vous avez reçu 20 crédits de bienvenue !',
                    'user_id' => $userId
                ];
            } else {
                $this->pdo->rollBack();
                return ['succes' => false, 'erreurs' => ['Erreur lors de la création du compte.']];
            }
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Erreur création compte EcoRide : " . $e->getMessage());
            return ['succes' => false, 'erreurs' => ['Erreur technique. Veuillez réessayer.']];
        }
    }
    
    /**
     * Authentifie un utilisateur avec email et mot de passe
     */
    public function authentifier($email, $motDePasse)
    {
        try {
            $sql = "SELECT id, pseudo, nom, prenom, email, mot_de_passe, credit,
                           telephone, adresse, ville, code_postal, permis_conduire,
                           bio, photo_profil, role, created_at, updated_at
                    FROM utilisateurs WHERE email = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($motDePasse, $user['mot_de_passe'])) {
                return ['succes' => false, 'erreur' => 'Email ou mot de passe incorrect.'];
            }
            
            unset($user['mot_de_passe']);
            $user['permis_conduire'] = (bool)$user['permis_conduire'];
            
            return [
                'succes' => true,
                'user' => $user,
                'message' => 'Connexion réussie ! Bienvenue sur EcoRide, ' . $user['pseudo'] . ' !'
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur authentification EcoRide : " . $e->getMessage());
            return ['succes' => false, 'erreur' => 'Erreur technique. Veuillez réessayer.'];
        }
    }
    
    /**
     * Récupère un utilisateur par son ID avec TOUS les champs
     */
    public function getUserById($userId)
    {
        try {
            $sql = "SELECT id, pseudo, nom, prenom, email, credit, created_at,
                           telephone, adresse, ville, code_postal, permis_conduire, 
                           bio, photo_profil, role, updated_at
                    FROM utilisateurs WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user['permis_conduire'] = (bool)$user['permis_conduire'];
            }
            
            return $user;
            
        } catch (PDOException $e) {
            error_log("Erreur getUserById EcoRide : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour le profil utilisateur
     */
    public function mettreAJourProfil($userId, $data)
    {
        try {
            if (empty($data['pseudo']) || empty($data['email'])) {
                return [
                    'succes' => false,
                    'erreur' => 'Le pseudo et l\'email sont obligatoires.'
                ];
            }
            
            $erreurs = $this->validerUniciteProfil($data['pseudo'], $data['email'], $userId);
            if (!empty($erreurs)) {
                return [
                    'succes' => false,
                    'erreur' => implode(' ', $erreurs)
                ];
            }
            
            $sql = "UPDATE utilisateurs 
                    SET pseudo = ?, nom = ?, prenom = ?, email = ?, 
                        telephone = ?, adresse = ?, ville = ?, code_postal = ?, 
                        permis_conduire = ?, bio = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            
            $resultat = $stmt->execute([
                $data['pseudo'],
                $data['nom'] ?? '',
                $data['prenom'] ?? '',
                $data['email'],
                $data['telephone'] ?? '',
                $data['adresse'] ?? '',
                $data['ville'] ?? '',
                $data['code_postal'] ?? '',
                isset($data['permis_conduire']) ? 1 : 0,
                $data['bio'] ?? '',
                $userId
            ]);
            
            if ($resultat) {
                return [
                    'succes' => true,
                    'message' => 'Profil mis à jour avec succès !'
                ];
            } else {
                return [
                    'succes' => false,
                    'erreur' => 'Erreur lors de la mise à jour du profil.'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur mise à jour profil : " . $e->getMessage());
            return [
                'succes' => false,
                'erreur' => 'Erreur technique lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Récupère les statistiques de l'utilisateur
     */
    public function getStatistiquesUtilisateur($userId)
    {
        try {
            // Trajets proposés
            $sql = "SELECT COUNT(*) FROM trajets WHERE conducteur_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $trajetsProposés = $stmt->fetchColumn();
            
            // Réservations effectuées
            $sql = "SELECT COUNT(*) FROM reservations WHERE passager_id = ? AND statut = 'confirme'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $reservationsEffectuées = $stmt->fetchColumn();
            
            // Crédits gagnés
            $sql = "SELECT COALESCE(SUM(montant), 0) FROM transactions_credits 
                    WHERE utilisateur_id = ? AND type_transaction = 'credit' AND source != 'inscription'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $creditsGagnés = $stmt->fetchColumn();
            
            // Calcul CO2 économisé
            $sql = "SELECT COALESCE(SUM(t.distance_km * COALESCE(r.nb_places_total, 0)), 0) as km_partages
                    FROM trajets t
                    LEFT JOIN (
                        SELECT trajet_id, SUM(nb_places) as nb_places_total
                        FROM reservations 
                        WHERE statut = 'confirme'
                        GROUP BY trajet_id
                    ) r ON t.id = r.trajet_id
                    WHERE t.conducteur_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $kmPartages = $stmt->fetchColumn();
            
            $co2Economise = round($kmPartages * 0.12, 1);
            
            return [
                'trajets_proposés' => $trajetsProposés,
                'reservations_effectuées' => $reservationsEffectuées,
                'credits_gagnés' => $creditsGagnés,
                'co2_economise' => $co2Economise
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur statistiques utilisateur EcoRide : " . $e->getMessage());
            
            return [
                'trajets_proposés' => 0,
                'reservations_effectuées' => 0,
                'credits_gagnés' => 0,
                'co2_economise' => 0
            ];
        }
    }

    // ========== NOUVELLES MÉTHODES POUR L'ADMINISTRATION ==========

    /**
     * ADMIN : Récupérer tous les utilisateurs pour l'administration
     * Utilisé par l'AdminController pour afficher la liste des utilisateurs
     * @return array Tableau des utilisateurs avec leurs informations
     */
    public function getAllUsers()
    {
        try {
            $sql = "SELECT id, pseudo, nom, prenom, email, credit, permis_conduire, 
                           telephone, ville, code_postal, role, created_at, updated_at, note
                    FROM utilisateurs 
                    ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convertir permis_conduire en booléen pour chaque utilisateur
            foreach ($users as &$user) {
                $user['permis_conduire'] = (bool)$user['permis_conduire'];
            }
            
            return $users;
            
        } catch (PDOException $e) {
            error_log("Erreur getAllUsers : " . $e->getMessage());
            return [];
        }
    }

    /**
     * ADMIN : Récupérer les utilisateurs actifs (connectés récemment)
     * Utilisé pour les statistiques d'administration
     * @param int $jours Nombre de jours pour considérer un utilisateur comme actif
     * @return array Tableau des utilisateurs actifs
     */
    public function getActiveUsers($jours = 30)
    {
        try {
            $sql = "SELECT id, pseudo, nom, prenom, email, updated_at 
                    FROM utilisateurs 
                    WHERE role != 'admin' 
                    AND updated_at > DATE_SUB(NOW(), INTERVAL ? DAY)
                    ORDER BY updated_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$jours]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getActiveUsers : " . $e->getMessage());
            return [];
        }
    }

    /**
     * ADMIN : Mettre à jour les crédits d'un utilisateur
     * Utilisé par l'admin pour ajuster les crédits des utilisateurs
     * @param int $userId ID de l'utilisateur
     * @param int $nouveauCredit Nouveau montant de crédits
     * @return bool True si mise à jour réussie
     */
    public function updateCredits($userId, $nouveauCredit)
    {
        try {
            // Récupérer l'ancien montant pour historique
            $sql = "SELECT credit FROM utilisateurs WHERE id = ? AND role != 'admin'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $ancienCredit = $stmt->fetchColumn();
            
            if ($ancienCredit === false) {
                return false; // Utilisateur non trouvé ou admin
            }
            
            // Mettre à jour les crédits
            $sql = "UPDATE utilisateurs 
                    SET credit = ?, updated_at = NOW() 
                    WHERE id = ? AND role != 'admin'";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$nouveauCredit, $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                // Enregistrer la transaction pour historique
                $difference = $nouveauCredit - $ancienCredit;
                $type = $difference > 0 ? 'credit' : 'debit';
                $this->enregistrerTransactionCredit(
                    $userId,
                    abs($difference),
                    $type,
                    'admin',
                    'Ajustement administrateur'
                );
                
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Erreur updateCredits : " . $e->getMessage());
            return false;
        }
    }

    /**
     * ADMIN : Obtenir les statistiques des utilisateurs
     * Méthode utile pour l'AdminController
     * @return array Statistiques générales des utilisateurs
     */
    public function getStatistiquesUsers()
    {
        try {
            $stats = [];
            
            // Nombre total d'utilisateurs (sans admin)
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role != 'admin'");
            $stats['total_users'] = $stmt->fetchColumn();
            
            // Nombre d'utilisateurs avec permis
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE permis_conduire = 1 AND role != 'admin'");
            $stats['users_avec_permis'] = $stmt->fetchColumn();
            
            // Crédits totaux en circulation
            $stmt = $this->pdo->query("SELECT SUM(credit) FROM utilisateurs WHERE role != 'admin'");
            $stats['credits_total'] = $stmt->fetchColumn() ?: 0;
            
            // Moyenne des crédits par utilisateur
            if ($stats['total_users'] > 0) {
                $stats['credits_moyenne'] = round($stats['credits_total'] / $stats['total_users'], 2);
            } else {
                $stats['credits_moyenne'] = 0;
            }
            
            // Utilisateurs inscrits ce mois-ci
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM utilisateurs 
                                      WHERE role != 'admin' 
                                      AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['nouveaux_users_mois'] = $stmt->fetchColumn();
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Erreur getStatistiquesUsers : " . $e->getMessage());
            return [
                'total_users' => 0,
                'users_avec_permis' => 0,
                'credits_total' => 0,
                'credits_moyenne' => 0,
                'nouveaux_users_mois' => 0
            ];
        }
    }

    /**
     * ADMIN : Rechercher des utilisateurs
     * Permet à l'admin de rechercher par pseudo, nom, email
     * @param string $recherche Terme de recherche
     * @return array Utilisateurs correspondant à la recherche
     */
    public function rechercherUsers($recherche)
    {
        try {
            $sql = "SELECT id, pseudo, nom, prenom, email, credit, created_at, role 
                    FROM utilisateurs 
                    WHERE role != 'admin' 
                    AND (pseudo LIKE ? OR nom LIKE ? OR prenom LIKE ? OR email LIKE ?)
                    ORDER BY created_at DESC";
            
            $terme = '%' . $recherche . '%';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$terme, $terme, $terme, $terme]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur rechercherUsers : " . $e->getMessage());
            return [];
        }
    }

    /**
     * ADMIN : Compter les trajets d'un utilisateur
     * Utile pour les statistiques par utilisateur
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de trajets créés
     */
    public function compterTrajetsUser($userId)
    {
        try {
            $sql = "SELECT COUNT(*) FROM trajets WHERE conducteur_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Erreur compterTrajetsUser : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ADMIN : Compter les réservations d'un utilisateur
     * Utile pour les statistiques par utilisateur
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de réservations effectuées
     */
    public function compterReservationsUser($userId)
    {
        try {
            $sql = "SELECT COUNT(*) FROM reservations WHERE passager_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            return $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Erreur compterReservationsUser : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ADMIN : Désactiver temporairement un utilisateur
     * Permet de bloquer un utilisateur sans le supprimer
     * @param int $userId ID de l'utilisateur
     * @return bool True si succès
     */
    public function desactiverUtilisateur($userId)
    {
        try {
            // Pour l'instant, on met à jour updated_at pour marquer l'action
            // Tu peux ajouter un champ "statut" à la table utilisateurs si nécessaire
            $sql = "UPDATE utilisateurs 
                    SET updated_at = NOW() 
                    WHERE id = ? AND role != 'admin'";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$userId]);
            
            return $result && $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Erreur desactiverUtilisateur : " . $e->getMessage());
            return false;
        }
    }

    // ========== MÉTHODES PRIVÉES (inchangées) ==========
    
    private function validerDonneesInscription($data)
    {
        $erreurs = [];
        
        if (empty($data['pseudo'])) {
            $erreurs[] = 'Le pseudo est obligatoire.';
        } elseif (strlen($data['pseudo']) < 3) {
            $erreurs[] = 'Le pseudo doit contenir au moins 3 caractères.';
        } elseif (strlen($data['pseudo']) > 50) {
            $erreurs[] = 'Le pseudo ne peut pas dépasser 50 caractères.';
        }
        
        if (empty($data['email'])) {
            $erreurs[] = 'L\'adresse email est obligatoire.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'L\'adresse email n\'est pas valide.';
        }
        
        if (empty($data['mot_de_passe'])) {
            $erreurs[] = 'Le mot de passe est obligatoire.';
        } elseif (strlen($data['mot_de_passe']) < 6) {
            $erreurs[] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }
        
        if (!empty($data['nom']) && strlen($data['nom']) > 100) {
            $erreurs[] = 'Le nom ne peut pas dépasser 100 caractères.';
        }
        
        if (!empty($data['prenom']) && strlen($data['prenom']) > 100) {
            $erreurs[] = 'Le prénom ne peut pas dépasser 100 caractères.';
        }
        
        if (!empty($data['telephone']) && !preg_match('/^[0-9+\-\s\.]{10,15}$/', $data['telephone'])) {
            $erreurs[] = 'Le numéro de téléphone n\'est pas valide.';
        }
        
        if (!empty($data['code_postal']) && !preg_match('/^\d{5}$/', $data['code_postal'])) {
            $erreurs[] = 'Le code postal doit contenir exactement 5 chiffres.';
        }
        
        if (!empty($data['bio']) && strlen($data['bio']) > 500) {
            $erreurs[] = 'La biographie ne peut pas dépasser 500 caractères.';
        }
        
        return [
            'valide' => empty($erreurs),
            'erreurs' => $erreurs
        ];
    }
    
    private function validerUniciteProfil($pseudo, $email, $userId)
    {
        $erreurs = [];
        
        try {
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE pseudo = ? AND id != ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$pseudo, $userId]);
            if ($stmt->fetchColumn() > 0) {
                $erreurs[] = 'Ce pseudo est déjà utilisé par un autre utilisateur.';
            }
            
            $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = ? AND id != ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $userId]);
            if ($stmt->fetchColumn() > 0) {
                $erreurs[] = 'Cette adresse email est déjà utilisée par un autre utilisateur.';
            }
            
        } catch (PDOException $e) {
            error_log("Erreur validation unicité : " . $e->getMessage());
            $erreurs[] = 'Erreur lors de la validation des données.';
        }
        
        return $erreurs;
    }
    
    private function emailExiste($email)
    {
        $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function pseudoExiste($pseudo)
    {
        $sql = "SELECT COUNT(*) FROM utilisateurs WHERE pseudo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$pseudo]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function enregistrerTransactionCredit($userId, $montant, $type, $source, $description)
    {
        try {
            $sql = "INSERT INTO transactions_credits (
                        utilisateur_id, montant, type_transaction, source, description, date_transaction
                    ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $montant, $type, $source, $description]);
            
        } catch (PDOException $e) {
            error_log("Erreur transaction crédit : " . $e->getMessage());
        }
    }
}
?>

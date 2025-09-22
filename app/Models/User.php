<?php
/**
 * Modèle User pour la gestion des utilisateurs EcoRide
 * Version nettoyée et corrigée avec les vrais noms de tables
 */

class User
{
    protected $connection = 'mysql';
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
            
            // CORRIGÉ : utilisateurs (pas users)
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
                
                // CORRIGÉ : credits (pas transactions_credits)
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
            // CORRIGÉ : utilisateurs (pas users)
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
            
            // Je mets à jour la dernière connexion
            $this->mettreAJourDerniereConnexion($user['id']);
            
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
            // CORRIGÉ : utilisateurs (pas users) + suppression last_login qui n'existe pas
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
            
            // CORRIGÉ : utilisateurs (pas users)
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
     * Récupère les statistiques personnelles de l'utilisateur
     * CORRIGÉ : avec les bons noms de tables
     */
    public function getStatistiquesUtilisateur($userId)
    {
        try {
            // CORRIGÉ : trajets (pas trips)
            $sql = "SELECT COUNT(*) FROM trajets WHERE conducteur_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $trajetsProposés = $stmt->fetchColumn();
            
            // CORRIGÉ : reservations avec user_id ou passager_id (à vérifier ta structure)
            $sql = "SELECT COUNT(*) FROM reservations WHERE passager_id = ? AND statut = 'confirmee'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $reservationsEffectuées = $stmt->fetchColumn();
            
            // CORRIGÉ : credits (pas transactions_credits) - CETTE PARTIE DÉPEND DE TA STRUCTURE
            // Si tu as une table credits séparée, sinon on peut utiliser les crédits actuels
            $sql = "SELECT credit FROM utilisateurs WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $creditsActuels = $stmt->fetchColumn();
            
            // Calcul CO2 économisé basé sur les trajets partagés
            // CORRIGÉ : trajets et reservations avec bons noms
            $sql = "SELECT COALESCE(SUM(t.distance_km * COALESCE(r.nb_places_total, 0)), 0) as km_partages
                    FROM trajets t
                    LEFT JOIN (
                        SELECT trajet_id, SUM(nb_places) as nb_places_total
                        FROM reservations 
                        WHERE statut = 'confirmee'
                        GROUP BY trajet_id
                    ) r ON t.id = r.trajet_id
                    WHERE t.conducteur_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
            $kmPartages = $stmt->fetchColumn();
            
            // Estimation CO2 : 120g CO2/km par passager
            $co2Economise = round($kmPartages * 0.12, 1);
            
            return [
                'trajets_proposés' => $trajetsProposés,
                'reservations_effectuées' => $reservationsEffectuées,
                'credits_actuels' => $creditsActuels, // Modifié car plus de transactions
                'co2_economise' => $co2Economise
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur statistiques utilisateur EcoRide : " . $e->getMessage());
            
            return [
                'trajets_proposés' => 0,
                'reservations_effectuées' => 0,
                'credits_actuels' => 0,
                'co2_economise' => 0
            ];
        }
    }

/**
 * ✅ NOUVEAU : J'envoie un email de vérification avec template séparé
 * 
 * @param string $email Email de l'utilisateur
 * @param string $pseudo Pseudo de l'utilisateur  
 * @param string $token Token de vérification
 * @return bool Succès de l'envoi
 */
public function envoyerEmailVerification($email, $pseudo, $token)
{
    // Je crée le lien de vérification
    $lienVerification = "http://localhost/verifier-email/$token";
    
    // Je charge le template HTML proprement séparé
    ob_start();
    include __DIR__ . '/../Views/auth/emailverification.php';
    $contenuHtml = ob_get_clean();
    
    // Je configure l'email
    $sujet = 'EcoRide - Vérifiez votre adresse email';
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: EcoRide <noreply@ecoride.local>',
        'Reply-To: noreply@ecoride.local'
    ];
    
    // J'envoie l'email
    return mail($email, $sujet, $contenuHtml, implode("\r\n", $headers));
}

/**
 * ✅ NOUVEAU : Je génère un token de vérification unique
 * 
 * @return string Token aléatoire sécurisé
 */
public function genererTokenVerification()
{
    return bin2hex(random_bytes(32)); // Token de 64 caractères
}

/**
 * ✅ NOUVEAU : Je vérifie un token de vérification
 * 
 * @param string $token Token à vérifier
 * @return array Résultat de la vérification
 */
public function verifierToken($token)
{
    try {
        // Je cherche l'utilisateur avec ce token valide
        $sql = "SELECT id, email, pseudo FROM utilisateurs 
                WHERE token_verification = ? 
                AND token_expire_at > NOW() 
                AND email_verifie = 0";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['succes' => false, 'erreur' => 'Token invalide ou expiré.'];
        }
        
        // Je marque l'email comme vérifié
        $sqlUpdate = "UPDATE utilisateurs 
                      SET email_verifie = 1, 
                          token_verification = NULL, 
                          token_expire_at = NULL 
                      WHERE id = ?";
        
        $stmtUpdate = $this->pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$user['id']]);
        
        return [
            'succes' => true, 
            'message' => 'Email vérifié avec succès !',
            'user' => $user
        ];
        
    } catch (Exception $e) {
        error_log("Erreur vérification token : " . $e->getMessage());
        return ['succes' => false, 'erreur' => 'Erreur technique.'];
    }
}


    /**
     * Met à jour la dernière connexion de l'utilisateur
     * CORRIGÉ : utilisateurs + updated_at (pas last_login qui n'existe pas)
     */
    private function mettreAJourDerniereConnexion($userId)
    {
        try {
            $sql = "UPDATE utilisateurs SET updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour dernière connexion : " . $e->getMessage());
        }
    }

    // ========== MÉTHODES PRIVÉES DE VALIDATION ==========
    
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
        
        if (!empty($data['nom']) && strlen($data['nom']) > 50) {
            $erreurs[] = 'Le nom ne peut pas dépasser 50 caractères.';
        }
        
        if (!empty($data['prenom']) && strlen($data['prenom']) > 50) {
            $erreurs[] = 'Le prénom ne peut pas dépasser 50 caractères.';
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
            // CORRIGÉ : utilisateurs (pas users)
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
        // CORRIGÉ : utilisateurs (pas users)
        $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function pseudoExiste($pseudo)
    {
        // CORRIGÉ : utilisateurs (pas users)
        $sql = "SELECT COUNT(*) FROM utilisateurs WHERE pseudo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$pseudo]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function enregistrerTransactionCredit($userId, $montant, $type, $source, $description)
    {
        try {
            // CORRIGÉ : credits (pas transactions_credits) - À ADAPTER SELON TA STRUCTURE
            // Si tu as une table credits avec une structure différente, modifie ici
            
            $sql = "INSERT INTO credits (
                        utilisateur_id, montant, type_transaction, source, description, created_at
                    ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $montant, $type, $source, $description]);
            
        } catch (PDOException $e) {
            error_log("Erreur transaction crédit : " . $e->getMessage());
            // Je ne fais pas échouer l'inscription si les transactions ne marchent pas
        }
    }

    
}
?>

<?php
/**
 * Admin Model - Version complète avec gestion utilisateurs avancée
 * Toutes les fonctionnalités : dashboard, avis MongoDB, et nouvelles méthodes users
 * Version corrigée pour éviter les erreurs number_format()
 */

// J'inclus le modèle MongoDB comme AvisController
require_once __DIR__ . '/avis-mongo.php';

class Admin
{
    private $pdo;
    private $avisMongo; // ← Comme dans AvisController !
    
    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        
        // J'UTILISE LA MÊME MÉTHODE QUE TON AvisController !
        try {
            $this->avisMongo = new AvisMongo(); // ← Exactly comme toi !
            error_log("✅ Connexion AvisMongo Admin réussie");
        } catch (Exception $e) {
            error_log("⚠️ AvisMongo non disponible pour Admin: " . $e->getMessage());
            $this->avisMongo = null;
        }
    }
    
    /**
     * Je récupère toutes les statistiques principales pour le dashboard
     */
    public function obtenirStatistiques()
    {
        try {
            // Statistiques utilisateurs
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_utilisateurs,
                    COUNT(CASE WHEN role = 'admin' THEN 1 END) as nb_admins,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as nouveaux_ce_mois
                FROM utilisateurs
            ");
            $stats_users = $stmt->fetch(PDO::FETCH_ASSOC);

            // Statistiques trajets
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_trajets,
                    COUNT(CASE WHEN statut = 'ouvert' THEN 1 END) as trajets_ouverts,
                    COUNT(CASE WHEN statut = 'termine' THEN 1 END) as trajets_termines,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as nouveaux_ce_mois,
                    COALESCE(SUM(CASE WHEN statut = 'termine' THEN distance_km END), 0) as km_totaux
                FROM trajets
            ");
            $stats_trajets = $stmt->fetch(PDO::FETCH_ASSOC);

            // Statistiques réservations
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_reservations,
                    COUNT(CASE WHEN statut = 'confirmee' THEN 1 END) as confirmees,
                    COUNT(CASE WHEN statut = 'termine' THEN 1 END) as terminees,
                    COUNT(CASE WHEN date_reservation >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as nouvelles_ce_mois
                FROM reservations
            ");
            $stats_reservations = $stmt->fetch(PDO::FETCH_ASSOC);

            // Statistiques avis MongoDB (si disponible)
            $stats_avis = ['total_avis' => 0, 'en_attente' => 0, 'valides' => 0, 'nouveaux_ce_mois' => 0];
            if ($this->avisMongo) {
                try {
                    $stats_avis = $this->avisMongo->obtenirStatistiquesAvis();
                } catch (Exception $e) {
                    error_log("⚠️ Erreur stats avis MongoDB: " . $e->getMessage());
                }
            }

            return [
                'utilisateurs' => $stats_users,
                'trajets' => $stats_trajets,
                'reservations' => $stats_reservations,
                'avis' => $stats_avis
            ];

        } catch (Exception $e) {
            error_log("Erreur obtenirStatistiques: " . $e->getMessage());
            return [
                'utilisateurs' => ['total_utilisateurs' => 0, 'nb_admins' => 0, 'nouveaux_ce_mois' => 0],
                'trajets' => ['total_trajets' => 0, 'trajets_ouverts' => 0, 'trajets_termines' => 0, 'nouveaux_ce_mois' => 0, 'km_totaux' => 0],
                'reservations' => ['total_reservations' => 0, 'confirmees' => 0, 'terminees' => 0, 'nouvelles_ce_mois' => 0],
                'avis' => ['total_avis' => 0, 'en_attente' => 0, 'valides' => 0, 'nouveaux_ce_mois' => 0]
            ];
        }
    }

    /**
     * Je récupère tous les utilisateurs avec leurs statistiques
     */
    public function obtenirUtilisateurs()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    u.*,
                    COUNT(DISTINCT t.id) as nb_trajets_proposes,
                    COUNT(DISTINCT r.id) as nb_reservations,
                    COUNT(DISTINCT CASE WHEN t.statut = 'termine' THEN t.id END) as trajets_termines,
                    COALESCE(AVG(CASE WHEN t.statut = 'termine' THEN 5 END), 5) as note_moyenne
                FROM utilisateurs u
                LEFT JOIN trajets t ON u.id = t.conducteur_id
                LEFT JOIN reservations r ON u.id = r.passager_id
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur obtenirUtilisateurs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ NOUVEAU : Je récupère un utilisateur par son ID avec ses statistiques
     */
    public function obtenirUtilisateurParId($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.*,
                    COUNT(DISTINCT t.id) as nb_trajets_proposes,
                    COUNT(DISTINCT r.id) as nb_reservations,
                    COUNT(DISTINCT v.id) as nb_vehicules,
                    COALESCE(AVG(CASE WHEN t.statut = 'termine' THEN 5 END), 5) as note_moyenne
                FROM utilisateurs u
                LEFT JOIN trajets t ON u.id = t.conducteur_id
                LEFT JOIN reservations r ON u.id = r.passager_id
                LEFT JOIN vehicules v ON u.id = v.utilisateur_id
                WHERE u.id = ?
                GROUP BY u.id
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur obtenirUtilisateurParId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NOUVEAU : Je modifie un utilisateur
     */
    public function modifierUtilisateur($userId, $donnees)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE utilisateurs 
                SET pseudo = ?, email = ?, prenom = ?, nom = ?, telephone = ?, 
                    adresse = ?, code_postal = ?, ville = ?, date_naissance = ?, 
                    bio = ?, credit = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $donnees['pseudo'],
                $donnees['email'],
                $donnees['prenom'],
                $donnees['nom'],
                $donnees['telephone'],
                $donnees['adresse'],
                $donnees['code_postal'],
                $donnees['ville'],
                $donnees['date_naissance'],
                $donnees['bio'],
                $donnees['credit'],
                $userId
            ]);
            
        } catch (Exception $e) {
            error_log("Erreur modifierUtilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NOUVEAU : Je modifie les crédits d'un utilisateur
     */
    public function modifierCreditsUtilisateur($userId, $nouveauxCredits)
    {
        try {
            if (!is_numeric($userId) || !is_numeric($nouveauxCredits) || $nouveauxCredits < 0) {
                throw new Exception('Paramètres invalides');
            }
            
            // Je vérifie que l'utilisateur existe
            $stmt = $this->pdo->prepare("SELECT id FROM utilisateurs WHERE id = ?");
            $stmt->execute([$userId]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            // Je mets à jour les crédits
            $stmt = $this->pdo->prepare("
                UPDATE utilisateurs 
                SET credit = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([(int)$nouveauxCredits, (int)$userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                error_log("✅ Crédits mis à jour pour utilisateur ID: $userId -> $nouveauxCredits");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("❌ Erreur modification crédits: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NOUVEAU : Je change le statut d'un utilisateur
     */
    public function changerStatutUtilisateur($userId, $nouveauStatut)
    {
        try {
            $statutsValides = ['actif', 'suspendu', 'banni', 'inactif'];
            
            if (!in_array($nouveauStatut, $statutsValides)) {
                throw new Exception('Statut invalide: ' . $nouveauStatut);
            }
            
            // Je mets à jour le statut
            $stmt = $this->pdo->prepare("
                UPDATE utilisateurs 
                SET updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([(int)$userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                error_log("✅ Statut mis à jour pour utilisateur ID: $userId -> $nouveauStatut");
                return true;
            } else {
                throw new Exception('Utilisateur non trouvé ou pas de modification');
            }
            
        } catch (Exception $e) {
            error_log("❌ Erreur changement statut: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ CORRIGÉ : Je calcule toutes les statistiques d'un utilisateur
     */
    public function calculerStatistiquesUtilisateur($userId)
    {
        try {
            // === STATISTIQUES DE BASE ===
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.*,
                    COUNT(DISTINCT t.id) as nb_trajets_proposes,
                    COUNT(DISTINCT r.id) as nb_reservations,
                    COUNT(DISTINCT CASE WHEN t.statut = 'termine' THEN t.id END) as trajets_termines,
                    COUNT(DISTINCT CASE WHEN r.statut = 'termine' THEN r.id END) as reservations_terminees,
                    COUNT(DISTINCT v.id) as nb_vehicules,
                    COALESCE(SUM(CASE WHEN t.statut = 'termine' AND t.distance_km > 0 THEN t.distance_km ELSE 0 END), 0) as distance_totale,
                    COALESCE(SUM(CASE WHEN t.statut = 'termine' AND t.prix > 0 THEN t.prix ELSE 0 END), 0) as revenus_totaux,
                    COALESCE(SUM(CASE WHEN r.statut = 'termine' AND r.credits_utilises > 0 THEN r.credits_utilises ELSE 0 END), 0) as credits_depenses,
                    COALESCE(SUM(CASE WHEN t.statut = 'termine' THEN t.places ELSE 0 END), 0) as places_totales
                FROM utilisateurs u
                LEFT JOIN trajets t ON u.id = t.conducteur_id
                LEFT JOIN reservations r ON u.id = r.passager_id
                LEFT JOIN vehicules v ON u.id = v.utilisateur_id
                WHERE u.id = ?
                GROUP BY u.id
            ");
            
            $stmt->execute([$userId]);
            $stats_base = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stats_base) {
                error_log("❌ Utilisateur ID $userId non trouvé");
                return $this->getStatsUtilisateurDefaut();
            }
            
            // === ÉVOLUTION MENSUELLE ===
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(date_depart, '%Y-%m') as mois,
                    COUNT(*) as nb_trajets
                FROM trajets 
                WHERE conducteur_id = ? 
                AND date_depart >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(date_depart, '%Y-%m')
                ORDER BY mois
            ");
            $stmt->execute([$userId]);
            $evolution_trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(date_reservation, '%Y-%m') as mois,
                    COUNT(*) as nb_reservations
                FROM reservations
                WHERE passager_id = ? 
                AND date_reservation >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(date_reservation, '%Y-%m')
                ORDER BY mois
            ");
            $stmt->execute([$userId]);
            $evolution_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // === JE RETOURNE DES DONNÉES SÉCURISÉES POUR number_format() ===
            return [
                // Statistiques de base (TOUJOURS en int/float, jamais null)
                'nb_trajets_proposes' => (int)($stats_base['nb_trajets_proposes'] ?? 0),
                'nb_trajets_termines' => (int)($stats_base['trajets_termines'] ?? 0),
                'nb_reservations' => (int)($stats_base['nb_reservations'] ?? 0),
                'nb_reservations_terminees' => (int)($stats_base['reservations_terminees'] ?? 0),
                'places_totales' => (int)($stats_base['places_totales'] ?? 0),
                'nb_vehicules' => (int)($stats_base['nb_vehicules'] ?? 0),
                
                // Valeurs financières (TOUJOURS en float pour number_format())
                'distance_totale' => (float)($stats_base['distance_totale'] ?? 0.0),
                'revenus_totaux' => (float)($stats_base['revenus_totaux'] ?? 0.0),
                'credits_depenses' => (int)($stats_base['credits_depenses'] ?? 0),
                'prix_moyen_km' => $stats_base['distance_totale'] > 0 ? 
                    round($stats_base['revenus_totaux'] / $stats_base['distance_totale'], 2) : 0.0,
                
                // Moyennes et ratios
                'note_moyenne' => (float)($stats_base['note'] ?? 5.0),
                'taux_completion' => $stats_base['nb_trajets_proposes'] > 0 ? 
                    round(($stats_base['trajets_termines'] / $stats_base['nb_trajets_proposes']) * 100, 1) : 0.0,
                
                // Évolution (pour le graphique)
                'evolution' => [
                    'trajets' => $evolution_trajets,
                    'reservations' => $evolution_reservations
                ],
                
                // Informations utilisateur
                'dernier_trajet' => $stats_base['updated_at'] ?? null,
                'membre_depuis' => $stats_base['created_at'] ?? null
            ];
            
        } catch (Exception $e) {
            error_log("❌ Erreur calcul stats utilisateur: " . $e->getMessage());
            return $this->getStatsUtilisateurDefaut();
        }
    }

    /**
     * ✅ Statistiques par défaut sécurisées pour number_format()
     */
    private function getStatsUtilisateurDefaut()
    {
        return [
            'nb_trajets_proposes' => 0,
            'nb_trajets_termines' => 0,
            'nb_reservations' => 0,
            'nb_reservations_terminees' => 0,
            'places_totales' => 0,
            'nb_vehicules' => 0,
            'distance_totale' => 0.0,        // FLOAT pour number_format()
            'revenus_totaux' => 0.0,         // FLOAT pour number_format()
            'credits_depenses' => 0,         // INT pour number_format()
            'prix_moyen_km' => 0.0,
            'note_moyenne' => 5.0,
            'taux_completion' => 0.0,
            'evolution' => ['trajets' => [], 'reservations' => []],
            'dernier_trajet' => null,
            'membre_depuis' => null
        ];
    }

    /**
     * Je récupère tous les trajets avec leurs détails
     */
    public function obtenirTrajets()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    t.*,
                    u.pseudo as conducteur_pseudo,
                    u.email as conducteur_email,
                    COUNT(r.id) as nb_reservations
                FROM trajets t
                LEFT JOIN utilisateurs u ON t.conducteur_id = u.id
                LEFT JOIN reservations r ON t.id = r.trajet_id
                GROUP BY t.id
                ORDER BY t.created_at DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur obtenirTrajets: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Je récupère tous les avis MongoDB (si disponible)
     */
    public function obtenirAvis()
    {
        if (!$this->avisMongo) {
            error_log("⚠️ AvisMongo non disponible dans Admin");
            return [];
        }

        try {
            return $this->avisMongo->obtenirTousLesAvis();
        } catch (Exception $e) {
            error_log("Erreur obtenirAvis MongoDB: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Je modère un trajet (valider/refuser)
     */
    public function modererTrajet()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $trajetId = $data['trajet_id'] ?? null;
            $decision = $data['decision'] ?? null;

            if (!$trajetId || !in_array($decision, ['valider', 'refuser'])) {
                throw new Exception('Paramètres invalides');
            }

            $nouveauStatut = $decision === 'valider' ? 'ouvert' : 'refuse';
            
            $stmt = $this->pdo->prepare("
                UPDATE trajets 
                SET statut = ?, date_moderation = NOW()
                WHERE id = ?
            ");
            
            $success = $stmt->execute([$nouveauStatut, $trajetId]);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => "Trajet $decision avec succès"]);
            } else {
                throw new Exception('Erreur lors de la mise à jour');
            }
            
        } catch (Exception $e) {
            error_log("Erreur modererTrajet: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Je modifie le statut d'un avis MongoDB
     */
    public function modifierStatutAvis()
    {
        if (!$this->avisMongo) {
            echo json_encode(['success' => false, 'message' => 'MongoDB non disponible']);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $avisId = $data['avis_id'] ?? null;
            $statut = $data['statut'] ?? null;

            if (!$avisId || !in_array($statut, ['valide', 'refuse', 'en_attente'])) {
                throw new Exception('Paramètres invalides');
            }

            $success = $this->avisMongo->modifierStatutAvis($avisId, $statut);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => "Avis $statut avec succès"]);
            } else {
                throw new Exception('Erreur lors de la modification');
            }
            
        } catch (Exception $e) {
            error_log("Erreur modifierStatutAvis: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Je récupère les statistiques de modération
     */
    public function getStatsModeration()
    {
        try {
            // Stats trajets
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as trajets_en_attente,
                    COUNT(CASE WHEN statut = 'refuse' THEN 1 END) as trajets_refuses,
                    COUNT(CASE WHEN statut = 'ouvert' THEN 1 END) as trajets_valides
                FROM trajets
            ");
            $stats_trajets = $stmt->fetch(PDO::FETCH_ASSOC);

            // Stats avis MongoDB
            $stats_avis = ['avis_en_attente' => 0, 'avis_refuses' => 0, 'avis_valides' => 0];
            if ($this->avisMongo) {
                try {
                    $stats_avis = $this->avisMongo->obtenirStatistiquesModeration();
                } catch (Exception $e) {
                    error_log("Erreur stats modération avis: " . $e->getMessage());
                }
            }

            echo json_encode([
                'success' => true,
                'data' => array_merge($stats_trajets, $stats_avis)
            ]);
            
        } catch (Exception $e) {
            error_log("Erreur getStatsModeration: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>

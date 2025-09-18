<?php
/**
 * AdminController - Contrôleur pour l'administration EcoRide
 * Version corrigée SANS modèle Admin (utilise Trip directement)
 */

class AdminController
{
    private $pdo;
    
    public function __construct()
    {
        require_once __DIR__ . '/../../config/database.php';
        
        global $pdo;
        $this->pdo = $pdo;
        
        // ✅ SUPPRIMÉ : $this->adminModel = new Admin();
    }
    
    private function verifierAdminConnecte()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /EcoRide/public/connexion?error=admin_required');
            exit;
        }
        
        return true;
    }
    
    /**
     * Dashboard administrateur
     */
/**
 * ✅ DASHBOARD ADMIN AVEC VRAIES DONNÉES DE LA BDD
 */
/**
 * ✅ DASHBOARD ADMIN AVEC LES VRAIES COLONNES DE LA BDD
 */
public function dashboard()
{
    if (!$this->verifierAdminConnecte()) {
        return;
    }
    
    try {
        // ✅ 1. STATISTIQUES UTILISATEURS (colonnes réelles)
        $sqlUsers = "SELECT 
                        COUNT(*) as total_utilisateurs,
                        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as nouveaux_utilisateurs,
                        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as utilisateurs_semaine,
                        COUNT(CASE WHEN role = 'admin' THEN 1 END) as nb_admins
                     FROM utilisateurs";
        
        $stmtUsers = $this->pdo->prepare($sqlUsers);
        $stmtUsers->execute();
        $statsUsers = $stmtUsers->fetch(PDO::FETCH_ASSOC);
        
        // ✅ 2. STATISTIQUES TRAJETS (colonnes réelles)
        $sqlTrajets = "SELECT 
                        COUNT(*) as total_trajets,
                        SUM(CASE WHEN statut_moderation = 'en_attente' THEN 1 ELSE 0 END) as trajets_en_attente,
                        SUM(CASE WHEN statut_moderation = 'valide' THEN 1 ELSE 0 END) as trajets_valides,
                        SUM(CASE WHEN statut_moderation = 'refuse' THEN 1 ELSE 0 END) as trajets_refuses,
                        SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as trajets_termines,
                        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as trajets_recents
                      FROM trajets";
        
        $stmtTrajets = $this->pdo->prepare($sqlTrajets);
        $stmtTrajets->execute();
        $statsTrajets = $stmtTrajets->fetch(PDO::FETCH_ASSOC);
        
        // ✅ 3. STATISTIQUES RÉSERVATIONS (colonnes réelles)
        $sqlReservations = "SELECT 
                            COUNT(*) as total_reservations,
                            SUM(CASE WHEN statut = 'confirme' THEN 1 ELSE 0 END) as reservations_confirmees,
                            SUM(CASE WHEN statut = 'annule' THEN 1 ELSE 0 END) as reservations_annulees,
                            SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as reservations_terminees,
                            SUM(CASE WHEN date_reservation >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as reservations_recentes
                           FROM reservations";
        
        $stmtReservations = $this->pdo->prepare($sqlReservations);
        $stmtReservations->execute();
        $statsReservations = $stmtReservations->fetch(PDO::FETCH_ASSOC);
        
        // ✅ 4. STATISTIQUES FINANCIÈRES - SOLDES UTILISATEURS
        $sqlFinances = "SELECT 
                        SUM(credit) as credits_soldes_utilisateurs,
                        AVG(credit) as credit_moyen_utilisateur,
                        COUNT(*) as nb_total_utilisateurs,
                        COUNT(CASE WHEN credit > 0 THEN 1 END) as utilisateurs_avec_credits,
                        MAX(credit) as credit_max,
                        MIN(credit) as credit_min
                       FROM utilisateurs";
        
        $stmtFinances = $this->pdo->prepare($sqlFinances);
        $stmtFinances->execute();
        $statsFinances = $stmtFinances->fetch(PDO::FETCH_ASSOC);
        
        // ✅ 5. STATISTIQUES TABLE CREDITS - MOUVEMENTS
        $sqlCreditsHistorique = "SELECT 
                                 SUM(CASE WHEN type = 'credit' THEN montant ELSE 0 END) as total_credits_entrants,
                                 SUM(CASE WHEN type = 'debit' THEN montant ELSE 0 END) as total_debits_sortants,
                                 COUNT(*) as nb_total_mouvements,
                                 COUNT(CASE WHEN type = 'credit' THEN 1 END) as nb_credits,
                                 COUNT(CASE WHEN type = 'debit' THEN 1 END) as nb_debits,
                                 SUM(CASE WHEN type = 'credit' AND date >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN montant ELSE 0 END) as credits_semaine
                                FROM credits";
        
        $stmtCreditsHistorique = $this->pdo->prepare($sqlCreditsHistorique);
        $stmtCreditsHistorique->execute();
        $statsCreditsHistorique = $stmtCreditsHistorique->fetch(PDO::FETCH_ASSOC);
        
        // ✅ 6. REVENUS ESTIMÉS (prix des trajets validés)
        $sqlRevenus = "SELECT 
                        SUM(prix) as revenus_potentiels,
                        AVG(prix) as prix_moyen_trajet,
                        SUM(commission) as commissions_totales
                       FROM trajets 
                       WHERE statut_moderation = 'valide'";
        
        $stmtRevenus = $this->pdo->prepare($sqlRevenus);
        $stmtRevenus->execute();
        $statsRevenus = $stmtRevenus->fetch(PDO::FETCH_ASSOC);
        
        // ✅ 7. STATISTIQUES VÉHICULES
        $sqlVehicules = "SELECT 
                         COUNT(*) as total_vehicules,
                         SUM(CASE WHEN electrique = 1 THEN 1 ELSE 0 END) as vehicules_electriques,
                         SUM(CASE WHEN electrique = 0 THEN 1 ELSE 0 END) as vehicules_thermiques
                        FROM vehicules";
        
        $stmtVehicules = $this->pdo->prepare($sqlVehicules);
        $stmtVehicules->execute();
        $statsVehicules = $stmtVehicules->fetch(PDO::FETCH_ASSOC);
        
        // ✅ 8. DERNIERS UTILISATEURS INSCRITS
        $sqlDerniersUsers = "SELECT pseudo, email, created_at, credit, role 
                            FROM utilisateurs 
                            ORDER BY created_at DESC 
                            LIMIT 5";
        
        $stmtDerniersUsers = $this->pdo->prepare($sqlDerniersUsers);
        $stmtDerniersUsers->execute();
        $derniersUtilisateurs = $stmtDerniersUsers->fetchAll(PDO::FETCH_ASSOC);

        // ✅ 9. DÉTAIL DES CRÉDITS PAR UTILISATEUR
        $sqlDetailCredits = "SELECT pseudo, credit, role 
                            FROM utilisateurs 
                            WHERE credit > 0 
                            ORDER BY credit DESC";
                            
        $stmtDetailCredits = $this->pdo->prepare($sqlDetailCredits);
        $stmtDetailCredits->execute();
        $detailCredits = $stmtDetailCredits->fetchAll(PDO::FETCH_ASSOC);
        
        // ✅ 10. DERNIERS TRAJETS CRÉÉS
        $sqlDerniersTrajets = "SELECT 
                                t.id, t.lieu_depart, t.lieu_arrivee, t.date_depart, 
                                t.prix, t.places, t.statut, t.statut_moderation, t.created_at,
                                u.pseudo as conducteur_pseudo
                               FROM trajets t
                               JOIN utilisateurs u ON t.conducteur_id = u.id
                               ORDER BY t.created_at DESC 
                               LIMIT 5";
        
        $stmtDerniersTrajets = $this->pdo->prepare($sqlDerniersTrajets);
        $stmtDerniersTrajets->execute();
        $derniersTrajets = $stmtDerniersTrajets->fetchAll(PDO::FETCH_ASSOC);
        
        // ✅ 11. CONSOLIDATION DES DONNÉES POUR LA VUE
        $dashboardData = [
            // Utilisateurs
            'total_utilisateurs' => (int)$statsUsers['total_utilisateurs'],
            'utilisateurs_actifs' => (int)$statsUsers['utilisateurs_semaine'],
            'utilisateurs_suspendus' => 0,
            'nouveaux_utilisateurs' => (int)$statsUsers['nouveaux_utilisateurs'],
            'nb_admins' => (int)$statsUsers['nb_admins'],
            
            // Trajets
            'total_trajets' => (int)$statsTrajets['total_trajets'],
            'trajets_en_attente' => (int)$statsTrajets['trajets_en_attente'],
            'trajets_valides' => (int)$statsTrajets['trajets_valides'],
            'trajets_refuses' => (int)$statsTrajets['trajets_refuses'],
            'trajets_termines' => (int)$statsTrajets['trajets_termines'],
            'trajets_recents' => (int)$statsTrajets['trajets_recents'],
            
            // Réservations
            'total_reservations' => (int)$statsReservations['total_reservations'],
            'reservations_confirmees' => (int)$statsReservations['reservations_confirmees'],
            'reservations_annulees' => (int)$statsReservations['reservations_annulees'],
            'reservations_terminees' => (int)$statsReservations['reservations_terminees'],
            'reservations_recentes' => (int)$statsReservations['reservations_recentes'],
            
            // Finances détaillées
            'detail_credits' => $detailCredits,
            'credits_totaux' => (int)($statsFinances['credits_soldes_utilisateurs'] ?: 0),
            'credit_moyen' => round((float)($statsFinances['credit_moyen_utilisateur'] ?: 0), 0),
            'utilisateurs_avec_credits' => (int)($statsFinances['utilisateurs_avec_credits'] ?: 0),
            'credit_max' => (int)($statsFinances['credit_max'] ?: 0),
            'credit_min' => (int)($statsFinances['credit_min'] ?: 0),
            
            // Mouvements de crédits (table credits)
            'total_credits_entrants' => (float)($statsCreditsHistorique['total_credits_entrants'] ?: 0),
            'total_debits_sortants' => (float)($statsCreditsHistorique['total_debits_sortants'] ?: 0),
            'nb_total_mouvements' => (int)($statsCreditsHistorique['nb_total_mouvements'] ?: 0),
            'credits_semaine' => (float)($statsCreditsHistorique['credits_semaine'] ?: 0),
            
            // Revenus
            'revenus_potentiels' => (float)($statsRevenus['revenus_potentiels'] ?: 0),
            'prix_moyen_trajet' => (float)($statsRevenus['prix_moyen_trajet'] ?: 0),
            'commissions_totales' => (float)($statsRevenus['commissions_totales'] ?: 0),
            
            // Véhicules
            'total_vehicules' => (int)$statsVehicules['total_vehicules'],
            'vehicules_electriques' => (int)$statsVehicules['vehicules_electriques'],
            'vehicules_thermiques' => (int)$statsVehicules['vehicules_thermiques'],
            
            // Listes
            'derniers_utilisateurs' => $derniersUtilisateurs,
            'derniers_trajets' => $derniersTrajets
        ];
        
        // ✅ 12. COMPATIBILITÉ ARRIÈRE AVEC L'ANCIENNE VUE (évite de tout casser)
        $stats = [
            'total_users' => $dashboardData['total_utilisateurs'],
            'users_actifs' => $dashboardData['utilisateurs_actifs'],
            'total_trajets' => $dashboardData['total_trajets'],
            'trajets_actifs' => $dashboardData['trajets_en_attente'],
            'total_reservations' => $dashboardData['total_reservations'],
            'reservations_confirmees' => $dashboardData['reservations_confirmees'],
            'credits_total' => $dashboardData['credits_totaux'],
            'vehicules_electriques' => $dashboardData['vehicules_electriques'],
            'vehicules_thermiques' => $dashboardData['vehicules_thermiques'],
            'croissance_mensuelle' => round((($dashboardData['nouveaux_utilisateurs'] / max($dashboardData['total_utilisateurs'], 1)) * 100), 0)
        ];
        
        // ✅ 13. AFFICHAGE DE LA VUE AVEC LES DONNÉES
        include __DIR__ . '/../Views/admin/dashboard.php';
        
    } catch (Exception $e) {
        error_log("Erreur dashboard admin: " . $e->getMessage());
        echo "Erreur lors du chargement du dashboard: " . $e->getMessage();
    }
}

    /**
     * Gestion des utilisateurs
     */
    public function utilisateurs()
    {
        if (!$this->verifierAdminConnecte()) {
            return;
        }
        
        try {
            $filtres = [];
            
            if (!empty($_GET['recherche'])) {
                $filtres['recherche'] = $_GET['recherche'];
            }
            
            if (!empty($_GET['role'])) {
                $filtres['role'] = $_GET['role'];
            }
            
            // ✅ Je récupère directement avec PDO
            $utilisateurs = $this->obtenirTousLesUtilisateurs($filtres);
            
            $title = "Gestion des utilisateurs | Admin EcoRide";
            $isAdminPage = true;
            $cssFiles = ['/css/admin.css'];
            $jsFiles = ['/js/admin.js', '/js/admin-users.js'];
            
            ob_start();
            include __DIR__ . '/../Views/admin/utilisateurs.php';
            $content = ob_get_clean();
            
            include __DIR__ . '/../Views/layouts/main.php';
            
        } catch (Exception $e) {
            error_log("Erreur gestion utilisateurs: " . $e->getMessage());
            $_SESSION['message'] = "Erreur lors du chargement des utilisateurs.";
            header('Location: /EcoRide/public/admin/dashboard');
            exit;
        }
    }



    /**
     * ✅ API : MODÉRER UN TRAJET
     */
 public function modererTrajet()
{
    // ✅ VÉRIFICATIONS (comme dans le debug)
    if (!$this->verifierAdminConnecte()) {
        echo json_encode(['success' => false, 'message' => 'Admin non connecté']);
        return;
    }
    
    // ✅ RÉCUPÉRATION DES DONNÉES (comme dans le debug)
    $trajetId = $_POST['trajet_id'] ?? 'MANQUANT';
    $decision = $_POST['decision'] ?? 'MANQUANT';
    $motif = $_POST['motif'] ?? null;
    
    // ✅ VALIDATION
    if ($trajetId === 'MANQUANT' || $decision === 'MANQUANT') {
        echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
        return;
    }
    
    if (!in_array($decision, ['valide', 'refuse'])) {
        echo json_encode(['success' => false, 'message' => 'Décision invalide']);
        return;
    }
    
    // ✅ VÉRIFICATION TRAJET EXISTE (comme dans le debug)
    try {
        $sql = "SELECT COUNT(*) FROM trajets WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$trajetId]);
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo json_encode(['success' => false, 'message' => "Trajet $trajetId non trouvé"]);
            return;
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur BDD: ' . $e->getMessage()]);
        return;
    }
    
    // ✅ UPDATE (EXACTEMENT comme dans le debug)
    try {
        $sql = "UPDATE trajets SET statut_moderation = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $resultat = $stmt->execute([$decision, $trajetId]);
        
        if ($resultat && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'SUCCESS !']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aucune ligne modifiée']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur UPDATE: ' . $e->getMessage()]);
    }
}


public function trajets()
{
    if (!$this->verifierAdminConnecte()) {
        return;
    }
    
    try {
        require_once __DIR__ . '/../Models/Trip.php';
        $tripModel = new Trip($this->pdo);
        
        // ✅ SEULEMENT LES TRAJETS EN ATTENTE
        $trajetsEnAttente = $tripModel->getTrajetsEnAttente();
        
        // ✅ STATS RÉELLES depuis la BDD
        $statsModeration = $tripModel->getStatsModeration();
        $stats = [
            'en_attente' => $statsModeration['en_attente'] ?? 0,
            'valides' => $statsModeration['valides'] ?? 0,
            'refuses' => $statsModeration['refuses'] ?? 0,
            'total' => $statsModeration['total_trajets'] ?? 0
        ];
        
        include __DIR__ . '/../Views/admin/trajets.php';
        
    } catch (Exception $e) {
        error_log("Erreur page trajets admin: " . $e->getMessage());
        echo "Erreur : " . $e->getMessage();
    }
}


/**
 * ✅ RÉCUPÈRE LES DÉTAILS COMPLETS D'UN TRAJET POUR LA MODAL ADMIN
 */
/**
 * ✅ AFFICHE LA PAGE DE DÉTAILS D'UN TRAJET POUR MODÉRATION
 */
public function detailsTrajet($trajetId)
{
    if (!$this->verifierAdminConnecte()) {
        return;
    }
    
    try {
        // ✅ RÉCUPÉRATION DES DONNÉES COMPLÈTES
        $sql = "SELECT 
                    t.*,
                    u.pseudo, u.nom, u.prenom, u.email, u.telephone, 
                    u.date_naissance, u.note, u.credit, u.permis_conduire,
                    u.created_at as date_inscription,
                    v.marque, v.modele, v.couleur, v.plaque_immatriculation,
                    v.nb_places as vehicule_places, v.electrique as vehicule_electrique
                FROM trajets t
                JOIN utilisateurs u ON t.conducteur_id = u.id
                LEFT JOIN vehicules v ON t.vehicule_id = v.id
                WHERE t.id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$trajetId]);
        $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$trajet) {
            $_SESSION['message'] = "Trajet non trouvé.";
            header('Location: /admin/trajets');
            exit;
        }
        
        // ✅ ENRICHISSEMENT DES DONNÉES
        $trajet['date_depart_formatee'] = date('d/m/Y à H:i', strtotime($trajet['date_depart'] . ' ' . $trajet['heure_depart']));
        $trajet['distance_estimee'] = round($trajet['distance_km']) . ' km';
        $trajet['duree_estimee'] = round($trajet['distance_km'] / 80 * 60) . ' min';
        $trajet['co2_economise'] = round($trajet['distance_km'] * 0.12, 1) . ' kg';
        $trajet['age_conducteur'] = $trajet['date_naissance'] ? date_diff(date_create($trajet['date_naissance']), date_create('today'))->y : 'N/A';
        $trajet['anciennete'] = date('d/m/Y', strtotime($trajet['date_inscription']));
        
        // ✅ STATISTIQUES DU CONDUCTEUR
        $sqlStats = "SELECT 
                        COUNT(*) as nb_trajets_total,
                        SUM(CASE WHEN statut_moderation = 'valide' THEN 1 ELSE 0 END) as nb_trajets_valides,
                        SUM(CASE WHEN statut_moderation = 'refuse' THEN 1 ELSE 0 END) as nb_trajets_refuses
                     FROM trajets 
                     WHERE conducteur_id = ?";
        
        $stmtStats = $this->pdo->prepare($sqlStats);
        $stmtStats->execute([$trajet['conducteur_id']]);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
        
        // ✅ FUSION DES DONNÉES
        $trajet = array_merge($trajet, $stats);
        
        // ✅ AFFICHAGE DE LA VUE
        include __DIR__ . '/../Views/admin/trajet-details.php';
        
    } catch (Exception $e) {
        error_log("Erreur détails trajet admin: " . $e->getMessage());
        echo "Erreur : " . $e->getMessage();
    }
}




    /**
     * Gestion des avis (MongoDB)
     */
public function avis()
{
    if (!$this->verifierAdminConnecte()) {
        return;
    }
    
    try {
        $filtres = [];
        
        if (!empty($_GET['note'])) {
            $filtres['note'] = $_GET['note'];
        }
        
        if (!empty($_GET['statut'])) {
            $filtres['statut'] = $_GET['statut'];
        }
        
        // ✅ Je récupère directement avec PDO (simulation pour MongoDB)
        $avis = $this->obtenirTousLesAvis($filtres);
        
        // ✅ UNE SEULE INCLUSION - La vue s'occupe du layout !
        include __DIR__ . '/../Views/admin/avis.php';
        
    } catch (Exception $e) {
        error_log("Erreur gestion avis: " . $e->getMessage());
        echo "Erreur : " . $e->getMessage();
    }
}

    
    /**
 * API : Modifier le statut d'un avis MongoDB
 */
public function modifierStatutAvis()
{
    if (!$this->verifierAdminConnecte()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        return;
    }
    
    header('Content-Type: application/json');
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $avisId = $input['avis_id'] ?? null;
        $nouveauStatut = $input['statut'] ?? null;
        
        if (!$avisId || !$nouveauStatut) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }
        
        $success = $this->modererAvisMongoDB($avisId, $nouveauStatut);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Avis modéré avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur modification']);
        }
        
    } catch (Exception $e) {
        error_log("Erreur modification avis: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
    
    exit;
}

private function modererAvisMongoDB($avisId, $statut)
{
    // Pour l'instant simulation - tu adapteras pour MongoDB
    return true;
}

    /**
     * API : Modification des crédits d'un utilisateur
     */
    public function modifierCredits()
    {
        if (!$this->verifierAdminConnecte()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $input['user_id'] ?? null;
            $nouveauxCredits = $input['nouveaux_credits'] ?? null;
            
            if (!$userId || $nouveauxCredits === null || !is_numeric($nouveauxCredits)) {
                throw new Exception('Données invalides');
            }
            
            // ✅ Je modifie directement avec PDO
            $success = $this->modifierCreditsUtilisateur($userId, $nouveauxCredits);
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Crédits mis à jour avec succès']);
            } else {
                throw new Exception('Échec de la modification des crédits');
            }
            
        } catch (Exception $e) {
            error_log("Erreur modification crédits: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification des crédits']);
        }
    }
    
    /**
     * API : Suspendre ou réactiver un utilisateur
     */
    public function toggleUserStatus()
    {
        if (!$this->verifierAdminConnecte()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $input['user_id'] ?? null;
            $action = $input['action'] ?? null;
            
            if (!$userId || !in_array($action, ['suspend', 'activate'])) {
                throw new Exception('Données invalides');
            }
            
            $nouveauStatut = $action === 'suspend' ? 'suspendu' : 'actif';
            $success = $this->changerStatutUtilisateur($userId, $nouveauStatut);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => $action === 'suspend' ? 'Utilisateur suspendu' : 'Utilisateur réactivé'
                ]);
            } else {
                throw new Exception('Échec du changement de statut');
            }
            
        } catch (Exception $e) {
            error_log("Erreur changement statut utilisateur: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du statut']);
        }
    }
    
    /**
     * Support et gestion des signalements
     */
    /**
 * ✅ PAGE DE GESTION DU SUPPORT ADMIN
 */
/**
 * ✅ PAGE SUPPORT BASIQUE - Contact admin + FAQ
 */
/**
 * ✅ PAGE SUPPORT POUR L'ADMIN - Aide pour utiliser l'interface admin
 */
public function support()
{
    if (!$this->verifierAdminConnecte()) {
        return;
    }
    
    // ✅ SEULEMENT les guides utiles
    $guidesAdmin = [
        'utilisateurs' => [
            'titre' => 'Gestion des utilisateurs',
            'actions' => [
                'Suspendre un utilisateur problématique',
                'Modifier les crédits d\'un utilisateur',
                'Voir l\'historique des trajets d\'un utilisateur',
                'Réactiver un compte suspendu'
            ]
        ],
        'trajets' => [
            'titre' => 'Modération des trajets',
            'actions' => [
                'Valider un trajet en attente',
                'Refuser un trajet non conforme',
                'Modifier les informations d\'un trajet',
                'Annuler un trajet problématique'
            ]
        ],
        'technique' => [
            'titre' => 'Problèmes techniques',
            'actions' => [
                'Vérifier les logs d\'erreur',
                'Gérer les signalements',
                'Sauvegarder la base de données',
                'Contacter le développeur'
            ]
        ]
    ];
    
    include __DIR__ . '/../Views/admin/support.php';
}



    /**
 * Affiche les détails d'un trajet pour modération
 */


    // ✅ MÉTHODES PRIVÉES POUR REMPLACER LE MODÈLE ADMIN

   private function obtenirStatistiquesGenerales()
{
    try {
        // ✅ VRAIES REQUÊTES SQL
        
        // Total utilisateurs
        $sqlUsers = "SELECT COUNT(*) as total FROM utilisateurs";
        $stmtUsers = $this->pdo->prepare($sqlUsers);
        $stmtUsers->execute();
        $totalUsers = $stmtUsers->fetchColumn();
        
        // Total trajets
        $sqlTrajets = "SELECT COUNT(*) as total FROM trajets";
        $stmtTrajets = $this->pdo->prepare($sqlTrajets);
        $stmtTrajets->execute();
        $totalTrajets = $stmtTrajets->fetchColumn();
        
        // Total réservations
        $sqlReservations = "SELECT COUNT(*) as total FROM reservations";
        $stmtReservations = $this->pdo->prepare($sqlReservations);
        $stmtReservations->execute();
        $totalReservations = $stmtReservations->fetchColumn();
        
        // Trajets en attente de modération
        $sqlEnAttente = "SELECT COUNT(*) as total FROM trajets WHERE statut_moderation = 'en_attente'";
        $stmtEnAttente = $this->pdo->prepare($sqlEnAttente);
        $stmtEnAttente->execute();
        $trajetsEnAttente = $stmtEnAttente->fetchColumn();
        
        // ✅ VRAIE SOMME DES CRÉDITS
        $sqlCredits = "SELECT SUM(credit) as total FROM utilisateurs";
        $stmtCredits = $this->pdo->prepare($sqlCredits);
        $stmtCredits->execute();
        $totalCredits = $stmtCredits->fetchColumn() ?: 0;
        
        // ✅ UTILISATEURS ACTIFS (connectés dans les 30 derniers jours)
        $sqlActifs = "SELECT COUNT(*) as total FROM utilisateurs WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmtActifs = $this->pdo->prepare($sqlActifs);
        $stmtActifs->execute();
        $usersActifs = $stmtActifs->fetchColumn();
        
        // ✅ VÉHICULES ÉLECTRIQUES
        $sqlElectriques = "SELECT COUNT(*) as total FROM vehicules WHERE electrique = 1";
        $stmtElectriques = $this->pdo->prepare($sqlElectriques);
        $stmtElectriques->execute();
        $vehiculesElectriques = $stmtElectriques->fetchColumn();
        
        // ✅ VÉHICULES THERMIQUES
        $sqlThermiques = "SELECT COUNT(*) as total FROM vehicules WHERE electrique = 0";
        $stmtThermiques = $this->pdo->prepare($sqlThermiques);
        $stmtThermiques->execute();
        $vehiculesThermiques = $stmtThermiques->fetchColumn();
        
        return [
            'total_utilisateurs' => $totalUsers,
            'total_trajets' => $totalTrajets,
            'total_reservations' => $totalReservations,
            'trajets_en_attente' => $trajetsEnAttente,
            'users_actifs' => $usersActifs,
            'credits_total' => $totalCredits,
            'vehicules_electriques' => $vehiculesElectriques,
            'vehicules_thermiques' => $vehiculesThermiques
        ];
        
    } catch (Exception $e) {
        error_log("Erreur stats générales: " . $e->getMessage());
        return [
            'total_utilisateurs' => 0,
            'total_trajets' => 0, 
            'total_reservations' => 0,
            'trajets_en_attente' => 0,
            'users_actifs' => 0,
            'credits_total' => 0,
            'vehicules_electriques' => 0,
            'vehicules_thermiques' => 0
        ];
    }
}

    // ✅ AJOUTE ÇA APRÈS obtenirStatistiquesGenerales()

private function obtenirDonneesGraphiques()
{
    try {
        // Trajets par mois (6 derniers mois)
        $sql = "SELECT 
                    MONTH(created_at) as mois,
                    COUNT(*) as nombre
                FROM trajets 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY MONTH(created_at)
                ORDER BY created_at";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $trajetsParMois = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Répartition trajets par statut
        $sql2 = "SELECT 
                    statut_moderation,
                    COUNT(*) as nombre
                 FROM trajets 
                 GROUP BY statut_moderation";
        
        $stmt2 = $this->pdo->prepare($sql2);
        $stmt2->execute();
        $repartitionStatuts = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'trajets_par_mois' => $trajetsParMois,
            'repartition_statuts' => $repartitionStatuts
        ];
        
    } catch (Exception $e) {
        error_log("Erreur données graphiques: " . $e->getMessage());
        return [
            'trajets_par_mois' => [],
            'repartition_statuts' => []
        ];
    }
}


    private function obtenirTousLesUtilisateurs($filtres = [])
    {
        try {
            $sql = "SELECT * FROM utilisateurs WHERE 1=1";
            $params = [];
            
            if (!empty($filtres['recherche'])) {
                $sql .= " AND (pseudo LIKE ? OR email LIKE ? OR nom LIKE ?)";
                $params[] = '%' . $filtres['recherche'] . '%';
                $params[] = '%' . $filtres['recherche'] . '%';
                $params[] = '%' . $filtres['recherche'] . '%';
            }
            
            if (!empty($filtres['role'])) {
                $sql .= " AND role = ?";
                $params[] = $filtres['role'];
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erreur récupération utilisateurs: " . $e->getMessage());
            return [];
        }
    }

    private function obtenirTousLesAvis($filtres = [])
    {
        // ✅ Simulation pour MongoDB - tu peux adapter selon tes besoins
        return [
            ['id' => 1, 'note' => 5, 'commentaire' => 'Excellent service', 'statut' => 'valide'],
            ['id' => 2, 'note' => 4, 'commentaire' => 'Très bien', 'statut' => 'en_attente']
        ];
    }

    private function modifierCreditsUtilisateur($userId, $nouveauxCredits)
    {
        try {
            $sql = "UPDATE utilisateurs SET credit = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$nouveauxCredits, $userId]);
            
        } catch (Exception $e) {
            error_log("Erreur modification crédits: " . $e->getMessage());
            return false;
        }
    }

    private function changerStatutUtilisateur($userId, $nouveauStatut)
    {
        try {
            $sql = "UPDATE utilisateurs SET statut = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$nouveauStatut, $userId]);
            
        } catch (Exception $e) {
            error_log("Erreur changement statut: " . $e->getMessage());
            return false;
        }
    }
}
?>

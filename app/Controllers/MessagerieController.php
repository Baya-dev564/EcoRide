<?php

require_once __DIR__ . '/../Models/MessagerieMongoDB.php';

class MessagerieController
{
    private $messagerieMongoDB;
    
    public function __construct()
    {
        $this->messagerieMongoDB = new MessagerieMongoDB();
    }
    
    /**
     * ✅ J'affiche la page principale de messagerie - CORRIGÉ
     */
    public function index()
    {
        // Je vérifie que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: /connexion');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // Je récupère les conversations de l'utilisateur
            $conversations = $this->messagerieMongoDB->getConversationsUtilisateur($userId);
            
            // 🔍 DEBUG AJOUTÉ ICI
            error_log("DEBUG contrôleur - conversations récupérées: " . count($conversations));
            
            // Ajoute ce debug pour voir le contenu !
            foreach ($conversations as $conv) {
                error_log("DEBUG - Conversation: " . json_encode($conv));
            }
            
            // ✅ CORRIGÉ : Je transforme les OBJETS pour l'affichage
            $conversationsFormatees = [];
            foreach ($conversations as $conversation) {
                // ✅ UTILISE -> POUR LES OBJETS MONGODB !
                $conversationsFormatees[] = [
                    'id' => (string)$conversation->_id,
                    'participants' => $conversation->participants, // Déjà un array d'objets
                    'derniere_activite' => $conversation->derniere_activite->toDateTime(),
                    'messages_non_lus' => isset($conversation->messages_non_lus) ? 
                                         (array)$conversation->messages_non_lus : []
                ];
            }
            
            error_log("DEBUG contrôleur - conversations formatées: " . count($conversationsFormatees));
            
            $pageTitle = "Mes Messages - EcoRide";
            include __DIR__ . '/../Views/messagerie/index.php';
            
        } catch (Exception $e) {
            error_log("ERREUR contrôleur index: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors du chargement des conversations : " . $e->getMessage();
            header('Location: /');
            exit;
        }
    }
    
    /**
     * ✅ J'affiche une conversation spécifique - CORRIGÉ AVEC NOTIFICATIONS
     */
    public function conversation($conversationId)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /connexion');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // ✅ NOUVEAU : Je marque les messages comme lus dès que j'ouvre la conversation
            $this->messagerieMongoDB->marquerMessagesCommuLus($conversationId, $userId);
            
            // Je récupère les messages de la conversation
            $messages = $this->messagerieMongoDB->getMessages($conversationId);
            
            // ✅ CORRIGÉ : Je transforme les OBJETS pour l'affichage
            $messagesFormats = [];
            foreach ($messages as $message) {
                $messagesFormats[] = [
                    'id' => (string)$message->_id,
                    'expediteur' => $message->expediteur, // Déjà un objet
                    'contenu' => $message->contenu,
                    'created_at' => $message->created_at->toDateTime(),
                    'lu' => isset($message->lu) ? $message->lu : false
                ];
            }
            
            $pageTitle = "Conversation - EcoRide";
            $conversationIdForView = $conversationId;
            
            include __DIR__ . '/../Views/messagerie/conversation.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors du chargement de la conversation : " . $e->getMessage();
            header('Location: /messages');
            exit;
        }
    }

    /**
     * ✅ J'envoie un nouveau message - CORRIGÉ AVEC NOTIFICATIONS
     */
    public function envoyerMessage()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non connecté']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['conversation_id']) || !isset($input['contenu'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            exit;
        }
        
        $conversationId = $input['conversation_id'];
        $contenu = trim($input['contenu']);
        $userId = $_SESSION['user_id'];
        $pseudoExpediteur = $_SESSION['pseudo'] ?? $_SESSION['username'] ?? 'Utilisateur';
        
        if (empty($contenu)) {
            http_response_code(400);
            echo json_encode(['error' => 'Message vide']);
            exit;
        }
        
        try {
            // J'envoie le message
            $this->messagerieMongoDB->envoyerMessage($conversationId, $userId, $pseudoExpediteur, $contenu);
            
            // ✅ NOUVEAU : J'incrémente le compteur pour le destinataire
            $conversation = $this->messagerieMongoDB->getConversation($conversationId);
            $participants = $conversation->participants ?? [];
            
            foreach ($participants as $participant) {
                if ($participant->user_id !== $userId) {
                    $this->messagerieMongoDB->incrementerMessagesNonLus($conversationId, $participant->user_id);
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Message envoyé'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * ✅ Je crée une nouvelle conversation
     */
    public function nouvelleConversation()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non connecté']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['destinataire_pseudo']) || !isset($input['message_initial'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            exit;
        }
        
        $user1Id = $_SESSION['user_id'];
        $pseudo1 = $_SESSION['pseudo'] ?? $_SESSION['username'] ?? 'Utilisateur';
        $destinatairePseudo = $input['destinataire_pseudo'];
        $messageInitial = trim($input['message_initial']);
        $trajetId = $input['trajet_id'] ?? null;
        
        if (empty($messageInitial)) {
            http_response_code(400);
            echo json_encode(['error' => 'Message initial requis']);
            exit;
        }
        
        try {
            // ✅ RECHERCHE DU VRAI USER_ID DANS LA BASE SQL
            if (!isset($input['destinataire_id'])) {
                // Chercher l'utilisateur par pseudo
                global $pdo;
                $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE pseudo = ?");
                $stmt->execute([$destinatairePseudo]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Utilisateur non trouvé']);
                    exit;
                }
                
                $user2Id = $user['id'];
            } else {
                $user2Id = $input['destinataire_id'];
            }
            
            // ✅ Je crée la conversation
            $conversationId = $this->messagerieMongoDB->creerConversation($user1Id, $user2Id, $pseudo1, $destinatairePseudo, $trajetId);
            
            // ✅ J'envoie le message initial
            $this->messagerieMongoDB->envoyerMessage((string)$conversationId, $user1Id, $pseudo1, $messageInitial);
            
            // ✅ NOUVEAU : J'incrémente le compteur pour le destinataire
            $this->messagerieMongoDB->incrementerMessagesNonLus((string)$conversationId, $user2Id);
            
            echo json_encode([
                'success' => true,
                'conversation_id' => (string)$conversationId,
                'message' => 'Conversation créée et message envoyé'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * ✅ Je récupère les nouveaux messages (AJAX) - CORRIGÉ
     */
    public function getNewMessages($conversationId)
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non connecté']);
            exit;
        }
        
        try {
            $messages = $this->messagerieMongoDB->getMessages($conversationId);
            
            // ✅ CORRIGÉ : Gestion des objets MongoDB
            $messagesFormats = [];
            foreach ($messages as $message) {
                $messagesFormats[] = [
                    'id' => (string)$message->_id,
                    'expediteur' => $message->expediteur, // Déjà un objet
                    'contenu' => $message->contenu,
                    'created_at' => $message->created_at->toDateTime()->format('Y-m-d H:i:s'),
                    'lu' => isset($message->lu) ? $message->lu : false
                ];
            }
            
            echo json_encode(['messages' => $messagesFormats]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * ✅ API pour chercher des utilisateurs par pseudo
     */
    public function rechercherUtilisateurs()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non connecté']);
            exit;
        }
        
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            echo json_encode(['users' => []]);
            return;
        }
        
        try {
            global $pdo;
            
            if (!$pdo) {
                throw new Exception('Pas de connexion base de données');
            }
            
            $sql = "SELECT id, pseudo, nom, prenom 
                    FROM utilisateurs 
                    WHERE pseudo LIKE ? 
                    AND id != ? 
                    LIMIT 10";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['%' . $query . '%', $_SESSION['user_id']]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $suggestions = [];
            foreach ($users as $user) {
                $suggestions[] = [
                    'id' => (int)$user['id'],
                    'pseudo' => $user['pseudo'],
                    'nom_complet' => trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')),
                    'avatar' => '/images/avatars/default.png'
                ];
            }
            
            echo json_encode(['users' => $suggestions]);
            
        } catch (Exception $e) {
            error_log('Erreur recherche utilisateurs: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur de recherche: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ API pour obtenir les motifs de contact prédéfinis
     */
    public function getMotifs()
    {
        $motifs = [
            [
                'id' => 'trajet_partage',
                'libelle' => '🚗 Partage de trajet',
                'description' => 'Organiser un covoiturage'
            ],
            [
                'id' => 'question_trajet',
                'libelle' => '❓ Question sur un trajet',
                'description' => 'Demander des informations'
            ],
            [
                'id' => 'modification_horaire',
                'libelle' => '⏰ Modification d\'horaire',
                'description' => 'Changer les horaires convenus'
            ],
            [
                'id' => 'annulation',
                'libelle' => '❌ Annulation',
                'description' => 'Annuler un trajet prévu'
            ],
            [
                'id' => 'feedback',
                'libelle' => '⭐ Retour d\'expérience',
                'description' => 'Partager un avis'
            ],
            [
                'id' => 'probleme_technique',
                'libelle' => '🔧 Problème technique',
                'description' => 'Signaler un bug ou dysfonctionnement'
            ],
            [
                'id' => 'proposition_reguliere',
                'libelle' => '🔄 Trajet régulier',
                'description' => 'Proposer des trajets récurrents'
            ],
            [
                'id' => 'autre',
                'libelle' => '💬 Autre sujet',
                'description' => 'Discussion libre'
            ]
        ];
        
        echo json_encode(['motifs' => $motifs]);
    }

    /**
     * ✅ Je compte les messages non lus pour l'utilisateur connecté
     */
    public function getUnreadCount()
    {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['count' => 0]);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // Je récupère toutes les conversations de l'utilisateur
            $conversations = $this->messagerieMongoDB->getConversationsUtilisateur($userId);
            
            $totalUnread = 0;
            foreach ($conversations as $conversation) {
                // Je compte les messages non lus de cette conversation
                $unreadCount = $conversation->messages_non_lus->{$userId} ?? 0;
                $totalUnread += $unreadCount;
            }
            
            echo json_encode(['count' => $totalUnread]);
            
        } catch (Exception $e) {
            error_log("Erreur getUnreadCount : " . $e->getMessage());
            echo json_encode(['count' => 0]);
        }
    }
}

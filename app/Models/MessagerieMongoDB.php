<?php
/**
 * Gestion de la messagerie MongoDB - Version .env
 *  Compatible Docker local ET production Hostinger via .env
 */

// Je charge les variables d'environnement
require_once __DIR__ . '/../../config/env.php';

class MessagerieMongoDB
{
    private $manager;
    private $database;
    private $collection_conversations;
    private $collection_messages;
    
    public function __construct()
    {
        try {
            // Je récupère l'URI MongoDB depuis les variables d'environnement
            $mongoUri = getenv('MONGODB_URI');
            
            if (!$mongoUri) {
                throw new Exception('MONGODB_URI non définie dans le fichier .env');
            }
            
            $this->manager = new MongoDB\Driver\Manager($mongoUri);
            
            // Je définis la base et les collections
            $this->database = 'EcoRide';
            $this->collection_conversations = 'conversations';
            $this->collection_messages = 'messages';
            
            error_log("DEBUG: Connexion MongoDB messagerie établie sur base EcoRide");
            
        } catch (Exception $e) {
            error_log("ERREUR - Connexion MongoDB messagerie échouée: " . $e->getMessage());
            throw new Exception("Impossible de se connecter à MongoDB: " . $e->getMessage());
        }
    }
    
    /**
     * Je crée une nouvelle conversation 100% NoSQL
     * Je stocke directement les pseudos dans MongoDB (pas de jointure MySQL)
     */
    public function creerConversation($user1Id, $user2Id, $pseudo1, $pseudo2, $trajetId = null)
    {
        try {
            $conversation = [
                'type' => 'user_user',
                'participants' => [
                    [
                        'user_id' => (int)$user1Id,
                        'pseudo' => $pseudo1
                    ],
                    [
                        'user_id' => (int)$user2Id,
                        'pseudo' => $pseudo2
                    ]
                ],
                'trajet_id' => $trajetId ? (int)$trajetId : null,
                'date_creation' => new MongoDB\BSON\UTCDateTime(),
                'dernier_message' => null,
                'date_dernier_message' => null,
                'messages_non_lus' => [
                    (int)$user1Id => 0,
                    (int)$user2Id => 0
                ]
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $conversationId = $bulk->insert($conversation);
            
            $this->manager->executeBulkWrite(
                $this->database . '.' . $this->collection_conversations,
                $bulk
            );
            
            error_log("DEBUG: Conversation créée - ID: " . (string)$conversationId);
            
            return [
                'success' => true,
                'conversation_id' => (string)$conversationId,
                'message' => 'Conversation créée avec succès'
            ];
            
        } catch (Exception $e) {
            error_log("ERREUR creerConversation MongoDB: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de la création de la conversation'
            ];
        }
    }
    
    /**
     * Je récupère toutes les conversations d'un utilisateur
     */
    public function getConversationsUtilisateur($userId)
    {
        try {
            $filter = [
                'participants.user_id' => (int)$userId
            ];
            
            $options = [
                'sort' => ['date_dernier_message' => -1]
            ];
            
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery(
                $this->database . '.' . $this->collection_conversations,
                $query
            );
            
            $conversations = [];
            foreach ($cursor as $conv) {
                $conversations[] = [
                    'id' => (string)$conv->_id,
                    'participants' => $conv->participants,
                    'trajet_id' => $conv->trajet_id ?? null,
                    'dernier_message' => $conv->dernier_message ?? '',
                    'date_dernier_message' => isset($conv->date_dernier_message) 
                        ? $conv->date_dernier_message->toDateTime()->format('Y-m-d H:i:s')
                        : null,
                    'messages_non_lus' => $conv->messages_non_lus->{(string)$userId} ?? 0
                ];
            }
            
            error_log("DEBUG: " . count($conversations) . " conversations pour user $userId");
            
            return [
                'success' => true,
                'conversations' => $conversations,
                'total' => count($conversations)
            ];
            
        } catch (Exception $e) {
            error_log("ERREUR getConversationsUtilisateur: " . $e->getMessage());
            return [
                'success' => false,
                'conversations' => [],
                'error' => 'Erreur lors de la récupération des conversations'
            ];
        }
    }
    
    /**
     * Je récupère une conversation par son ID
     */
    public function getConversationParId($conversationId)
    {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $query = new MongoDB\Driver\Query($filter);
            
            $cursor = $this->manager->executeQuery(
                $this->database . '.' . $this->collection_conversations,
                $query
            );
            
            $convArray = iterator_to_array($cursor);
            
            if (!empty($convArray)) {
                $conv = $convArray[0];
                return [
                    'success' => true,
                    'conversation' => [
                        'id' => (string)$conv->_id,
                        'participants' => $conv->participants,
                        'trajet_id' => $conv->trajet_id ?? null,
                        'dernier_message' => $conv->dernier_message ?? '',
                        'date_dernier_message' => isset($conv->date_dernier_message)
                            ? $conv->date_dernier_message->toDateTime()->format('Y-m-d H:i:s')
                            : null
                    ]
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Conversation non trouvée'
            ];
            
        } catch (Exception $e) {
            error_log("ERREUR getConversationParId: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de la récupération de la conversation'
            ];
        }
    }
    
    /**
     * J'envoie un message dans une conversation
     */
    public function envoyerMessage($conversationId, $senderId, $contenu)
    {
        try {
            $message = [
                'conversation_id' => new MongoDB\BSON\ObjectId($conversationId),
                'sender_id' => (int)$senderId,
                'contenu' => $contenu,
                'date_envoi' => new MongoDB\BSON\UTCDateTime(),
                'lu' => false
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $messageId = $bulk->insert($message);
            
            $this->manager->executeBulkWrite(
                $this->database . '.' . $this->collection_messages,
                $bulk
            );
            
            // Je mets à jour la conversation avec le dernier message
            $this->mettreAJourDernierMessage($conversationId, $contenu, $senderId);
            
            error_log("DEBUG: Message envoyé - ID: " . (string)$messageId);
            
            return [
                'success' => true,
                'message_id' => (string)$messageId,
                'message' => 'Message envoyé avec succès'
            ];
            
        } catch (Exception $e) {
            error_log("ERREUR envoyerMessage: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de l\'envoi du message'
            ];
        }
    }
    
    /**
     * Je récupère tous les messages d'une conversation
     */
    public function getMessagesConversation($conversationId, $limit = 100)
    {
        try {
            $filter = [
                'conversation_id' => new MongoDB\BSON\ObjectId($conversationId)
            ];
            
            $options = [
                'sort' => ['date_envoi' => 1],
                'limit' => $limit
            ];
            
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery(
                $this->database . '.' . $this->collection_messages,
                $query
            );
            
            $messages = [];
            foreach ($cursor as $msg) {
                $messages[] = [
                    'id' => (string)$msg->_id,
                    'sender_id' => $msg->sender_id,
                    'contenu' => $msg->contenu,
                    'date_envoi' => $msg->date_envoi->toDateTime()->format('Y-m-d H:i:s'),
                    'lu' => $msg->lu ?? false
                ];
            }
            
            return [
                'success' => true,
                'messages' => $messages,
                'total' => count($messages)
            ];
            
        } catch (Exception $e) {
            error_log("ERREUR getMessagesConversation: " . $e->getMessage());
            return [
                'success' => false,
                'messages' => [],
                'error' => 'Erreur lors de la récupération des messages'
            ];
        }
    }
    
    /**
     * Je marque les messages comme lus
     */
    public function marquerMessagesCommelus($conversationId, $userId)
    {
        try {
            $filter = [
                'conversation_id' => new MongoDB\BSON\ObjectId($conversationId),
                'sender_id' => ['$ne' => (int)$userId],
                'lu' => false
            ];
            
            $update = [
                '$set' => ['lu' => true]
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update, ['multi' => true]);
            
            $result = $this->manager->executeBulkWrite(
                $this->database . '.' . $this->collection_messages,
                $bulk
            );
            
            // Je réinitialise le compteur de messages non lus dans la conversation
            $this->resetCompteurNonLus($conversationId, $userId);
            
            return [
                'success' => true,
                'messages_marques' => $result->getModifiedCount()
            ];
            
        } catch (Exception $e) {
            error_log("ERREUR marquerMessagesCommelus: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors du marquage des messages'
            ];
        }
    }
    
    /**
     * Je compte les messages non lus pour un utilisateur
     */
    public function compterMessagesNonLus($userId)
    {
        try {
            $filter = [
                'participants.user_id' => (int)$userId
            ];
            
            $query = new MongoDB\Driver\Query($filter);
            $cursor = $this->manager->executeQuery(
                $this->database . '.' . $this->collection_conversations,
                $query
            );
            
            $totalNonLus = 0;
            foreach ($cursor as $conv) {
                $totalNonLus += $conv->messages_non_lus->{(string)$userId} ?? 0;
            }
            
            return [
                'success' => true,
                'total_non_lus' => $totalNonLus
            ];
            
        } catch (Exception $e) {
            error_log("ERREUR compterMessagesNonLus: " . $e->getMessage());
            return [
                'success' => false,
                'total_non_lus' => 0
            ];
        }
    }
    
    /**
     * Méthode privée : Je mets à jour le dernier message dans la conversation
     */
    private function mettreAJourDernierMessage($conversationId, $contenu, $senderId)
    {
        try {
            // Je récupère les participants pour incrémenter le bon compteur
            $convResult = $this->getConversationParId($conversationId);
            
            if (!$convResult['success']) {
                return false;
            }
            
            $participants = $convResult['conversation']['participants'];
            $receiverId = null;
            
            foreach ($participants as $p) {
                if ($p->user_id != $senderId) {
                    $receiverId = $p->user_id;
                    break;
                }
            }
            
            $filter = ['_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $update = [
                '$set' => [
                    'dernier_message' => $contenu,
                    'date_dernier_message' => new MongoDB\BSON\UTCDateTime()
                ],
                '$inc' => [
                    "messages_non_lus.$receiverId" => 1
                ]
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update);
            
            $this->manager->executeBulkWrite(
                $this->database . '.' . $this->collection_conversations,
                $bulk
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("ERREUR mettreAJourDernierMessage: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Méthode privée : Je réinitialise le compteur de messages non lus
     */
    private function resetCompteurNonLus($conversationId, $userId)
    {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $update = [
                '$set' => [
                    "messages_non_lus.$userId" => 0
                ]
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update);
            
            $this->manager->executeBulkWrite(
                $this->database . '.' . $this->collection_conversations,
                $bulk
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log("ERREUR resetCompteurNonLus: " . $e->getMessage());
            return false;
        }
    }
}
?>

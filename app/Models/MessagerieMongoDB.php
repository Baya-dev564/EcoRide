<?php
/**
 * Modèle MessagerieMongoDB pour EcoRide - Système NoSQL complet
 * Gestion de la messagerie avec MongoDB pour EcoRide
 */

class MessagerieMongoDB 
{
    private $manager;
    private $database = 'EcoRide';
    private $collection_conversations = 'conversations';
    private $collection_messages = 'messages';

    /**
     * Je constructeur - J'établis la connexion MongoDB simple
     */
    public function __construct() 
    {
        try {
            // Je connecte MongoDB avec les mêmes paramètres que les avis
            $this->manager = new MongoDB\Driver\Manager(
                "mongodb://ecoride:ecoride123@mongo:27017/EcoRide?authSource=admin"
            );
            
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
                'derniere_activite' => new MongoDB\BSON\UTCDateTime(),
                'messages_non_lus' => [
                    (string)$user1Id => 0,
                    (string)$user2Id => 0
                ],
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $id = $bulk->insert($conversation);
            
            $this->manager->executeBulkWrite($this->database . '.' . $this->collection_conversations, $bulk);
            
            return $id;
            
        } catch (Exception $e) {
            error_log("Erreur creerConversation : " . $e->getMessage());
            throw new Exception("Erreur lors de la création de la conversation");
        }
    }
    
    /**
     * J'envoie un message 100% NoSQL
     */
    public function envoyerMessage($conversationId, $expediteurId, $expediteurPseudo, $contenu) 
    {  
        try {
            // Je crée le message
            $message = [
                'conversation_id' => $conversationId,
                'expediteur' => [
                    'user_id' => (int)$expediteurId,
                    'pseudo' => $expediteurPseudo
                ],
                'contenu' => $contenu,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'lu_par' => [(int)$expediteurId]
            ];
            
            // J'utilise Manager + BulkWrite comme dans creerConversation
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert($message);
            
            $result = $this->manager->executeBulkWrite($this->database . '.' . $this->collection_messages, $bulk);
            
            if ($result->getInsertedCount() === 0) {
                throw new Exception("Erreur lors de l'insertion du message");
            }
            
            // Je mets à jour la conversation avec Manager aussi
            $bulkUpdate = new MongoDB\Driver\BulkWrite;
            $bulkUpdate->update(
                ['_id' => new MongoDB\BSON\ObjectId($conversationId)],
                ['$set' => ['derniere_activite' => new MongoDB\BSON\UTCDateTime()]]
            );
            
            $this->manager->executeBulkWrite($this->database . '.' . $this->collection_conversations, $bulkUpdate);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur envoyerMessage: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Je récupère toutes les conversations d'un utilisateur
     */
    public function getConversationsUtilisateur($userId)
    {
        try {
            error_log("DEBUG getConversationsUtilisateur - userId: $userId");
            
            // Je caste en integer ici
            $userIdInt = (int)$userId;
            error_log("DEBUG getConversationsUtilisateur - userId casted: $userIdInt");
            
            $filter = [
                'participants.user_id' => $userIdInt  // Integer au lieu de string
            ];
            
            $options = ['sort' => ['derniere_activite' => -1]];
            $query = new MongoDB\Driver\Query($filter, $options);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection_conversations, $query);
            
            $conversations = [];
            foreach ($cursor as $conversation) {
                $conversations[] = $conversation;
            }
            
            error_log("DEBUG getConversationsUtilisateur - conversations trouvées: " . count($conversations));
            
            return $conversations;
            
        } catch (Exception $e) {
            error_log("Erreur getConversationsUtilisateur : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Je récupère tous les messages d'une conversation
     */
    public function getMessages($conversationId)
    {
        try {
            $filter = ['conversation_id' => $conversationId];
            $options = ['sort' => ['created_at' => 1]];
            $query = new MongoDB\Driver\Query($filter, $options);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection_messages, $query);
            
            $messages = [];
            foreach ($cursor as $message) {
                $messages[] = $message;
            }
            
            return $messages;
            
        } catch (Exception $e) {
            error_log("Erreur getMessages : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Je récupère une conversation spécifique
     */
    public function getConversation($conversationId)
    {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $query = new MongoDB\Driver\Query($filter);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection_conversations, $query);
            
            foreach ($cursor as $conversation) {
                return $conversation; // Je retourne le premier (et seul) résultat
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erreur getConversation : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Je marque les messages comme lus pour un utilisateur
     */
    public function marquerMessagesCommuLus($conversationId, $userId)
    {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update(
                ['_id' => new MongoDB\BSON\ObjectId($conversationId)],
                [
                    '$set' => [
                        'messages_non_lus.' . $userId => 0,
                        'derniere_activite' => new MongoDB\BSON\UTCDateTime()
                    ]
                ]
            );
            
            $result = $this->manager->executeBulkWrite($this->database . '.' . $this->collection_conversations, $bulk);
            
            return $result->getModifiedCount() > 0;
            
        } catch (Exception $e) {
            error_log("Erreur marquerMessagesCommuLus : " . $e->getMessage());
            return false;
        }
    }

    /**
     * J'incrémente le compteur de messages non lus
     */
    public function incrementerMessagesNonLus($conversationId, $userId)
    {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update(
                ['_id' => new MongoDB\BSON\ObjectId($conversationId)],
                [
                    '$inc' => ['messages_non_lus.' . $userId => 1],
                    '$set' => ['derniere_activite' => new MongoDB\BSON\UTCDateTime()]
                ]
            );
            
            $result = $this->manager->executeBulkWrite($this->database . '.' . $this->collection_conversations, $bulk);
            
            return $result->getModifiedCount() > 0;
            
        } catch (Exception $e) {
            error_log("Erreur incrementerMessagesNonLus : " . $e->getMessage());
            return false;
        }
    }
}

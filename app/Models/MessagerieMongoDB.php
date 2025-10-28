<?php
/**
 * Gestion de la messagerie MongoDB - Version Docker
 * Compatible avec les variables d'environnement Docker
 */

class MessagerieMongoDB
{
    private $manager;
    private $database;
    private $conversations;
    private $messages;
    
    public function __construct()
    {
        try {
            // Je récupère l'URI MongoDB depuis les variables d'environnement Docker
            $mongoUri = getenv('MONGODB_URI');
            
            if (!$mongoUri) {
                throw new Exception('MONGODB_URI non définie');
            }
            
            $this->manager = new MongoDB\Driver\Manager($mongoUri);
            $this->database = 'EcoRide';
            $this->conversations = $this->database . '.conversations';
            $this->messages = $this->database . '.messages';
            
            error_log("DEBUG: Connexion MongoDB messagerie établie sur base EcoRide");
            
        } catch (Exception $e) {
            error_log("ERREUR - Connexion MongoDB messagerie échouée: " . $e->getMessage());
            throw new Exception("Impossible de se connecter à MongoDB: " . $e->getMessage());
        }
    }
    
    /**
     * Je crée une nouvelle conversation
     */
    public function creerConversation($user1Id, $user2Id, $pseudo1, $pseudo2, $trajetId = null)
    {
        try {
            $conversation = [
                'participants' => [
                    ['user_id' => (int)$user1Id, 'pseudo' => $pseudo1],
                    ['user_id' => (int)$user2Id, 'pseudo' => $pseudo2]
                ],
                'trajet_id' => $trajetId ? (int)$trajetId : null,
                'derniere_activite' => new MongoDB\BSON\UTCDateTime(),
                'messages_non_lus' => [
                    (string)$user1Id => 0,
                    (string)$user2Id => 0
                ]
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $conversationId = $bulk->insert($conversation);
            
            $this->manager->executeBulkWrite($this->conversations, $bulk);
            
            error_log("DEBUG: Conversation créée - ID: " . (string)$conversationId);
            return $conversationId;
            
        } catch (Exception $e) {
            error_log("ERREUR creerConversation: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Je récupère les conversations d'un utilisateur
     */
    public function getConversationsUtilisateur($userId)
    {
        try {
            $filter = ['participants.user_id' => (int)$userId];
            $options = ['sort' => ['derniere_activite' => -1]];
            
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->conversations, $query);
            
            return iterator_to_array($cursor);
            
        } catch (Exception $e) {
            error_log("ERREUR getConversationsUtilisateur: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Je récupère une conversation par ID
     */
    public function getConversation($conversationId)
    {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $query = new MongoDB\Driver\Query($filter);
            
            $cursor = $this->manager->executeQuery($this->conversations, $query);
            $result = iterator_to_array($cursor);
            
            return !empty($result) ? $result[0] : null;
            
        } catch (Exception $e) {
            error_log("ERREUR getConversation: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * J'envoie un message
     */
    public function envoyerMessage($conversationId, $userId, $pseudo, $contenu)
    {
        try {
            $message = [
                'conversation_id' => new MongoDB\BSON\ObjectId($conversationId),
                'expediteur' => ['user_id' => (int)$userId, 'pseudo' => $pseudo],
                'contenu' => $contenu,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'lu' => false
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $messageId = $bulk->insert($message);
            
            $this->manager->executeBulkWrite($this->messages, $bulk);
            
            // Je mets à jour la dernière activité de la conversation
            $this->mettreAJourDerniereActivite($conversationId);
            
            return $messageId;
            
        } catch (Exception $e) {
            error_log("ERREUR envoyerMessage: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Je récupère les messages d'une conversation
     */
    public function getMessages($conversationId)
    {
        try {
            $filter = ['conversation_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $options = ['sort' => ['created_at' => 1]];
            
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->messages, $query);
            
            return iterator_to_array($cursor);
            
        } catch (Exception $e) {
            error_log("ERREUR getMessages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Je marque les messages comme lus
     */
    public function marquerMessagesCommuLus($conversationId, $userId)
    {
        try {
            $filter = [
                'conversation_id' => new MongoDB\BSON\ObjectId($conversationId),
                'expediteur.user_id' => ['$ne' => (int)$userId],
                'lu' => false
            ];
            
            $update = ['$set' => ['lu' => true]];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update, ['multi' => true]);
            
            $this->manager->executeBulkWrite($this->messages, $bulk);
            
            // Je réinitialise le compteur
            $this->resetMessagesNonLus($conversationId, $userId);
            
            return true;
            
        } catch (Exception $e) {
            error_log("ERREUR marquerMessagesCommuLus: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * J'incrémente le compteur de messages non lus
     */
    public function incrementerMessagesNonLus($conversationId, $userId)
    {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $update = ['$inc' => ['messages_non_lus.' . (string)$userId => 1]];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update);
            
            $this->manager->executeBulkWrite($this->conversations, $bulk);
            
            return true;
            
        } catch (Exception $e) {
            error_log("ERREUR incrementerMessagesNonLus: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Je réinitialise le compteur de messages non lus
     */
    private function resetMessagesNonLus($conversationId, $userId)
    {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $update = ['$set' => ['messages_non_lus.' . (string)$userId => 0]];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update);
            
            $this->manager->executeBulkWrite($this->conversations, $bulk);
            
            return true;
            
        } catch (Exception $e) {
            error_log("ERREUR resetMessagesNonLus: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Je mets à jour la dernière activité
     */
    private function mettreAJourDerniereActivite($conversationId)
    {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($conversationId)];
            $update = ['$set' => ['derniere_activite' => new MongoDB\BSON\UTCDateTime()]];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update);
            
            $this->manager->executeBulkWrite($this->conversations, $bulk);
            
            return true;
            
        } catch (Exception $e) {
            error_log("ERREUR mettreAJourDerniereActivite: " . $e->getMessage());
            return false;
        }
    }
}
?>

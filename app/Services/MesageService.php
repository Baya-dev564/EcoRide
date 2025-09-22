<?php

namespace App\Services;

use MongoDB\Driver\Manager;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Query;
use MongoDB\BSON\UTCDateTime;

class MessageService
{
    private Manager $mongo;
    private string $database = 'ecoride';
    
    public function __construct()
    {
        // J'utilise la même connexion qui marche
        $this->mongo = new Manager("mongodb://ecoride:ecoride123@172.18.0.3:27017");
    }
    
    public function sendMessage(int $tripId, int $senderId, int $receiverId, string $message): bool
    {
        try {
            $bulk = new BulkWrite;
            
            $document = [
                'trip_id' => $tripId,
                'sender_id' => $senderId, 
                'receiver_id' => $receiverId,
                'message' => $message,
                'created_at' => new UTCDateTime(),
                'read' => false
            ];
            
            $bulk->insert($document);
            $result = $this->mongo->executeBulkWrite($this->database . '.messages', $bulk);
            
            return $result->getInsertedCount() > 0;
            
        } catch (\Exception $e) {
            error_log("Erreur envoi message: " . $e->getMessage());
            return false;
        }
    }
    
    public function getConversation(int $tripId, int $userId1, int $userId2): array
    {
        try {
            $filter = [
                'trip_id' => $tripId,
                '$or' => [
                    ['sender_id' => $userId1, 'receiver_id' => $userId2],
                    ['sender_id' => $userId2, 'receiver_id' => $userId1]
                ]
            ];
            
            $options = ['sort' => ['created_at' => 1]];
            $query = new Query($filter, $options);
            
            $cursor = $this->mongo->executeQuery($this->database . '.messages', $query);
            
            return $cursor->toArray();
            
        } catch (\Exception $e) {
            error_log("Erreur récupération conversation: " . $e->getMessage());
            return [];
        }
    }
}

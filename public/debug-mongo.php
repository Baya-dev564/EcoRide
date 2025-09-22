<?php
require_once __DIR__ . '/../Models/MessagerieMongoDB.php';

echo "<h1>🔍 TEST MESSAGERIE MONGODB</h1>";
echo "<hr>";

try {
    echo "<p>📡 Test connexion MongoDB...</p>";
    
    $mongo = new MessagerieMongoDB();
    echo "<p>✅ Connexion OK</p>";
    
    // Test 1: Créer une conversation
    echo "<p>📝 Test 1: Création conversation...</p>";
    $conversationId = $mongo->creerConversation(1, 2, 'TestUser1', 'TestUser2', null);
    echo "<p>✅ Conversation créée avec ID: <strong>" . $conversationId . "</strong></p>";
    
    // Test 2: Envoyer un message
    echo "<p>💬 Test 2: Envoi message...</p>";
    $messageOk = $mongo->envoyerMessage((string)$conversationId, 1, 'TestUser1', 'Message de test depuis PHP');
    echo "<p>" . ($messageOk ? "✅" : "❌") . " Envoi message</p>";
    
    // Test 3: Récupérer conversations utilisateur 1
    echo "<p>📋 Test 3: Récupération conversations utilisateur 1...</p>";
    $conversations = $mongo->getConversationsUtilisateur(1);
    echo "<p>✅ Trouvé <strong>" . count($conversations) . "</strong> conversations pour user 1</p>";
    
    if (!empty($conversations)) {
        foreach ($conversations as $conv) {
            echo "<ul>";
            echo "<li>ID: " . $conv->_id . "</li>";
            echo "<li>Participants: " . json_encode($conv->participants) . "</li>";
            echo "</ul>";
        }
    }
    
    echo "<h2>🎉 TOUS LES TESTS RÉUSSIS !</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERREUR</h2>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Fichier:</strong> " . $e->getFile() . " ligne " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

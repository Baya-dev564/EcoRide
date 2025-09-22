<?php
try {
    // URL avec authentification
    $mongo = new MongoDB\Driver\Manager("mongodb://ecoride:ecoride123@172.18.0.3:27017");
    
    // Test simple
    $query = new MongoDB\Driver\Query([]);
    $cursor = $mongo->executeQuery('test.test', $query);
    
    echo "✅ MongoDB connexion OK !";
    
} catch (Exception $e) {
    echo "❌ Erreur MongoDB : " . $e->getMessage();
}
?>

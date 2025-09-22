<?php
/**
 * Script de diagnostic MongoDB pour EcoRide
 * Vérifie la présence des avis dans la base
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test MongoDB - Diagnostic des avis</h2>";

try {
    // Test 1 : Connexion MongoDB
    echo "<h3>1. Test de connexion</h3>";
    $manager = new MongoDB\Driver\Manager("mongodb://ecoride:ecoride123@mongo:27017/EcoRide?authSource=admin");
    echo "✅ Connexion MongoDB OK<br>";
    
    // Test 2 : Compter TOUS les documents dans la collection
    echo "<h3>2. Nombre total de documents</h3>";
    $filter = []; // Pas de filtre = tout récupérer
    $query = new MongoDB\Driver\Query($filter);
    $cursor = $manager->executeQuery('EcoRide.avis', $query);
    
    $count = 0;
    $documents = [];
    foreach ($cursor as $document) {
        $count++;
        $documents[] = $document;
    }
    
    echo "📊 Nombre total d'avis dans MongoDB : <strong>$count</strong><br>";
    
    // Test 3 : Afficher les 5 derniers avis
    if ($count > 0) {
        echo "<h3>3. Derniers avis (bruts)</h3>";
        $recentFilter = [];
        $recentOptions = ['sort' => ['_id' => -1], 'limit' => 5];
        $recentQuery = new MongoDB\Driver\Query($recentFilter, $recentOptions);
        $recentCursor = $manager->executeQuery('EcoRide.avis', $recentQuery);
        
        foreach ($recentCursor as $avis) {
            echo "<div style='border:1px solid #ccc; margin:10px; padding:10px;'>";
            echo "<strong>ID:</strong> " . (string)$avis->_id . "<br>";
            echo "<strong>Utilisateur:</strong> " . ($avis->nom_utilisateur ?? 'NON DÉFINI') . "<br>";
            echo "<strong>Trajet ID:</strong> " . ($avis->trajet_id ?? 'NON DÉFINI') . "<br>";
            echo "<strong>Note:</strong> " . ($avis->note ?? 'NON DÉFINI') . "<br>";
            echo "<strong>Commentaire:</strong> " . ($avis->commentaire ?? 'NON DÉFINI') . "<br>";
            echo "<strong>Statut:</strong> " . ($avis->statut ?? 'NON DÉFINI') . "<br>";
            echo "<strong>Date:</strong> " . ($avis->date_creation ? $avis->date_creation->toDateTime()->format('Y-m-d H:i:s') : 'NON DÉFINI') . "<br>";
            echo "</div>";
        }
    } else {
        echo "❌ <strong>Aucun avis trouvé dans MongoDB !</strong><br>";
        echo "➡️ Le problème vient de l'insertion qui ne fonctionne pas.<br>";
    }
    
    // Test 4 : Test avec filtre trajet_id = 1 
    echo "<h3>4. Test avec filtre trajet_id = 1</h3>";
    $filterTrajet = ['trajet_id' => 1, 'statut' => 'actif'];
    $queryTrajet = new MongoDB\Driver\Query($filterTrajet);
    $cursorTrajet = $manager->executeQuery('EcoRide.avis', $queryTrajet);
    
    $countTrajet = 0;
    foreach ($cursorTrajet as $doc) {
        $countTrajet++;
    }
    echo "📊 Avis avec trajet_id=1 et statut=actif : <strong>$countTrajet</strong><br>";
    
    echo "<h3>5. Conclusion</h3>";
    if ($count == 0) {
        echo "🚨 <strong>PROBLÈME : Aucune donnée dans MongoDB</strong><br>";
        echo "➡️ Vérifiez votre méthode ajouterAvis() dans AvisController<br>";
        echo "➡️ Ajoutez des logs pour voir si l'insertion échoue silencieusement<br>";
    } elseif ($countTrajet == 0) {
        echo "🚨 <strong>PROBLÈME : Données présentes mais filtre incorrect</strong><br>";
        echo "➡️ Vérifiez les valeurs de trajet_id et statut lors de l'insertion<br>";
    } else {
        echo "✅ <strong>Données OK dans MongoDB</strong><br>";
        echo "➡️ Le problème vient de la méthode getAvisParTrajet() ou du contrôleur index()<br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Erreur MongoDB :</strong> " . $e->getMessage() . "<br>";
}
?>

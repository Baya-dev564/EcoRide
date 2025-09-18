<?php
/**
 * Script pour voir tous les avis avec leurs trajet_id et statut
 */

echo "<h2>Diagnostic complet des avis MongoDB</h2>";

try {
    $manager = new MongoDB\Driver\Manager("mongodb://ecoride:ecoride123@mongo:27017/EcoRide?authSource=admin");
    
    // Récupérer TOUS les avis sans filtre
    $query = new MongoDB\Driver\Query([]);
    $cursor = $manager->executeQuery('EcoRide.avis', $query);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Trajet ID</th><th>Statut</th><th>Utilisateur</th><th>Note</th><th>Commentaire</th></tr>";
    
    $count = 0;
    foreach ($cursor as $avis) {
        $count++;
        echo "<tr>";
        echo "<td>" . (string)$avis->_id . "</td>";
        echo "<td><strong>" . ($avis->trajet_id ?? 'NON DÉFINI') . "</strong></td>";
        echo "<td><strong>" . ($avis->statut ?? 'NON DÉFINI') . "</strong></td>";
        echo "<td>" . ($avis->nom_utilisateur ?? 'N/A') . "</td>";
        echo "<td>" . ($avis->note ?? 'N/A') . "</td>";
        echo "<td>" . substr($avis->commentaire ?? '', 0, 50) . "...</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p><strong>Total : $count avis</strong></p>";
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

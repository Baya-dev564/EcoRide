<?php
try {
    $pdo = new PDO('mysql:host=mysql;dbname=EcoRide', 'ecoride', 'ecoridepass');
    echo 'Connexion MySQL réussie !';
} catch (PDOException $e) {
    echo 'Erreur : ' . $e->getMessage();
}
?>

<?php
// Connexion à la base de données EcoRide
$host = 'mysql';        // Service Docker
$db   = 'EcoRide';      // Base configurée  
$user = 'ecoride';      // Utilisateur dédié
$pass = 'ecoridepass';  // Mot de passe défini dans docker-compose.yml
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Connexion réussie !";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

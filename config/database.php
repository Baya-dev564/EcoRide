<?php
class DatabaseConfig 
{
    private $host = 'mysql';           // Nom du service Docker
    private $username = 'ecoride';     // MYSQL_USER de ton docker-compose.yml
    private $password = 'ecoridepass'; // MYSQL_PASSWORD de ton docker-compose.yml  
    private $database = 'EcoRide';     // MYSQL_DATABASE
    
    public function getConnection() 
    {
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            return $pdo;
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
}
?>

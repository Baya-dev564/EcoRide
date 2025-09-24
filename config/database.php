<?php
class DatabaseConfig 
{
    // Utilisation des variables d'environnement Render/Supabase
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;
    
    public function __construct() {
        // Je récupère les variables d'environnement que tu as définies dans Render
        $this->host = $_ENV['DB_HOST'] ?? 'db.yglcyhmfhhcwgcjiyxoq.supabase.co';
        $this->username = $_ENV['DB_USERNAME'] ?? 'postgres';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'ton-mot-de-passe';
        $this->database = $_ENV['DB_DATABASE'] ?? 'postgres';
        $this->port = $_ENV['DB_PORT'] ?? '5432';
    }
    
    public function getConnection() 
    {
        try {
            // Je change de MySQL vers PostgreSQL
            $pdo = new PDO(
                "pgsql:host={$this->host};port={$this->port};dbname={$this->database}",
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

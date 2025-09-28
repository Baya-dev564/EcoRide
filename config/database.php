<?php
final class DatabaseConfig
{
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $database;
    private ?\PDO  $pdo = null;

    public function __construct()
    {
        $this->host     = getenv('MYSQL_HOST')     ?: 'mysql';
        $this->port     = (int)(getenv('MYSQL_PORT') ?: 3306);
        $this->username = (string)getenv('MYSQL_USER');
        $this->password = (string)getenv('MYSQL_PASSWORD');
        $this->database = getenv('MYSQL_DATABASE') ?: 'EcoRide';
    }

    public function getConnection(): \PDO
    {
        if ($this->pdo instanceof \PDO) {
            return $this->pdo; // reuse the same handle
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->host, $this->port, $this->database
        );

        try {
            $this->pdo = new \PDO($dsn, $this->username, $this->password, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ]);
            return $this->pdo;
        } catch (\PDOException $e) {
            error_log('[DB] Connection failed: ' . $e->getMessage());
            die('Erreur de connexion à la base de données.');
        }
    }
}
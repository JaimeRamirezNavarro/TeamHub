<?php
class Database {
    private static $instance = null;
    private $connection;
    
    // Credenciales de Producción (InfinityFree)
    private $host = 'sql100.infinityfree.com';
    private $database = 'if0_41170654_teamhub';
    private $username = 'if0_41170654';
    private $password = 'QePABRE3ole7q3n';

    private function __construct() {
        // Detectar si estamos en Docker (Local)
        if (file_exists('/.dockerenv') || getenv('IS_DOCKER')) {
            $this->host = 'db';
            $this->database = 'proyecto_dual';
            $this->username = 'root';
            $this->password = 'root';
        }

        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión ({$this->host}): " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}
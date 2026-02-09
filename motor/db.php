<?php

class Database {
    private static $instance = null;
    private $conn;

    private $host = 'db';
    private $user = 'root';
    private $pass = 'root';
    private $name = 'proyecto_dual';

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Error de conexión a la base de datos.");
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function __clone() {}
    public function __wakeup() {}
}

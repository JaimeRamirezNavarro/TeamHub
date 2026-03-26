<?php
class Database {
    private static $instance = null;
    private $connection;

    private $host;
    private $database;
    private $username;
    private $password;

    private function __construct() {

        /* ============================================================
           1. Cargar credenciales desde .env si existe
        ============================================================ */
        $envPath = __DIR__ . '/../../.env';

        if (file_exists($envPath)) {
            $env = parse_ini_file($envPath);

            $this->host     = $env['DB_HOST'] ?? 'localhost';
            $this->database = $env['DB_NAME'] ?? 'proyecto_dual';
            $this->username = $env['DB_USER'] ?? 'root';
            $this->password = $env['DB_PASS'] ?? 'root';
        } else {
            // Valores por defecto si no hay .env
            $this->host     = 'localhost';
            $this->database = 'proyecto_dual';
            $this->username = 'root';
            $this->password = 'root';
        }

        /* ============================================================
           2. Detectar si estamos en Docker (entorno local)
        ============================================================ */
        if (file_exists('/.dockerenv') || getenv('IS_DOCKER')) {
            $this->host     = 'db';
            $this->database = 'proyecto_dual';
            $this->username = 'root';
            $this->password = 'root';
        }

        /* ============================================================
           3. Intentar conectar con PDO
        ============================================================ */
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );

        } catch (PDOException $e) {

            /* ============================================================
               4. Manejo profesional de errores
            ============================================================ */

            // Registrar error en log seguro
            error_log("[DB ERROR] " . $e->getMessage());

            // ¡MODIFICADO PARA DEBUGGING EN AWARDSPACE!
            die("Error REAL de la base de datos: " . $e->getMessage() . "<br><br>Host intentado: " . $this->host . "<br>Usuario: " . $this->username);
        }
    }

    /* ============================================================
       Singleton
    ============================================================ */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    /* ============================================================
       Método rápido para ejecutar consultas
    ============================================================ */
    public function run($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

<?php
// modelo/consultas.php
require_once __DIR__ . '/../motor/db.php';

class Consultas {
    private $db;

    public function verificarlogin($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if($user && $password === $user['password']){
            return $user;
        }
        return false;
    }
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Para el seguimiento de empleados
    public function obtenerUsuarios() {
        $query = $this->db->query("SELECT id, username, status FROM users");
        return $query ? $query->fetchAll() : []; 
    }

    public function actualizarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    // Para el dashboard de proyectos
    public function obtenerTareas() {
        $query = $this->db->query("SELECT title, status FROM tasks");
        return $query ? $query->fetchAll() : [];
    }
}
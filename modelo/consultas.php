<?php
// modelo/consultas.php
require_once __DIR__ . '/../motor/db.php';

class Consultas {
    private $db;

    public function verificarlogin($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Verificamos si la contraseña coincide (hash o texto plano por compatibilidad temporal)
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            return $user;
        }
        return false;
    }

    public function registrarUsuario($username, $email, $password) {
        // Verificar si el email ya existe
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false; // El usuario ya existe
        }

        // Hashear contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, status) VALUES (?, ?, ?, 'Oficina')");
        return $stmt->execute([$username, $email, $hashed_password]);
    }
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // GESTIÓN DE EQUIPOS (PROYECTOS)
    public function obtenerTodosLosEquipos() {
        $query = $this->db->query("SELECT * FROM teams");
        return $query ? $query->fetchAll() : [];
    }
    
    public function unirseEquipo($user_id, $team_id) {
        // Verificar si ya es miembro
        if ($this->esMiembro($user_id, $team_id)) return false;
        
        $stmt = $this->db->prepare("INSERT INTO team_members (user_id, team_id, role) VALUES (?, ?, 'member')");
        return $stmt->execute([$user_id, $team_id]);
    }
    
    public function salirEquipo($user_id, $team_id) {
        $stmt = $this->db->prepare("DELETE FROM team_members WHERE user_id = ? AND team_id = ?");
        return $stmt->execute([$user_id, $team_id]);
    }
    
    public function esMiembro($user_id, $team_id) {
        $stmt = $this->db->prepare("SELECT 1 FROM team_members WHERE user_id = ? AND team_id = ?");
        $stmt->execute([$user_id, $team_id]);
        return (bool) $stmt->fetch();
    }
    
    public function obtenerMiembrosEquipo($team_id) {
        $stmt = $this->db->prepare("
            SELECT u.username, u.status 
            FROM users u 
            JOIN team_members tm ON u.id = tm.user_id 
            WHERE tm.team_id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll();
    }

    // Para el seguimiento de empleados (GLOBAL - Deprecated for specific teams but kept for legacy views if needed)
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
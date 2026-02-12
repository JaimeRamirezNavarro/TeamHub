<?php
// modelo/consultas.php
require_once __DIR__ . '/../motor/db.php';

class Consultas {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

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

    public function obtenerUsuario($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
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

    // GESTIÓN DE EQUIPOS (PROYECTOS)
    public function obtenerTodosLosEquipos() {
        $query = $this->db->query("SELECT * FROM teams");
        return $query ? $query->fetchAll() : [];
    }

    public function obtenerEquipo($id) {
        $stmt = $this->db->prepare("SELECT * FROM teams WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function actualizarEstadoEquipo($team_id, $status) {
        $stmt = $this->db->prepare("UPDATE teams SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $team_id]);
    }

    
    public function unirseEquipo($user_id, $team_id) {
        if ($this->esMiembro($user_id, $team_id)) return false;
        $stmt = $this->db->prepare("INSERT INTO team_members (user_id, team_id, role) VALUES (?, ?, 'member')");
        return $stmt->execute([$user_id, $team_id]);
    }
    
    public function salirEquipo($user_id, $team_id) {
        $stmt = $this->db->prepare("DELETE FROM team_members WHERE user_id = ? AND team_id = ?");
        return $stmt->execute([$user_id, $team_id]);
    }
    
    public function esMiembro($user_id, $team_id) {
        if ($this->esGlobalAdmin($user_id)) return true;
        
        $stmt = $this->db->prepare("SELECT 1 FROM team_members WHERE user_id = ? AND team_id = ?");
        $stmt->execute([$user_id, $team_id]);
        return (bool) $stmt->fetch();
    }

    public function obtenerRolUsuario($user_id, $team_id) {
        if ($this->esGlobalAdmin($user_id)) return 'admin'; // Ghost Admin is always admin

        $stmt = $this->db->prepare("SELECT role FROM team_members WHERE user_id = ? AND team_id = ?");
        $stmt->execute([$user_id, $team_id]);
        return $stmt->fetchColumn(); 
    }

    private function esGlobalAdmin($user_id) {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $role = $stmt->fetchColumn();
        return $role === 'admin';
    }
    
    public function obtenerMiembrosEquipo($team_id) {
        $stmt = $this->db->prepare("
            SELECT u.username, u.status, tm.role 
            FROM users u 
            JOIN team_members tm ON u.id = tm.user_id 
            WHERE tm.team_id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll();
    }

    // Para el seguimiento de empleados (Legacy/Global)
    public function obtenerUsuarios() {
        $query = $this->db->query("SELECT id, username, status FROM users");
        return $query ? $query->fetchAll() : []; 
    }

    public function actualizarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public function obtenerTareas() {
        $query = $this->db->query("SELECT title, status FROM tasks");
        return $query ? $query->fetchAll() : [];
    }

    // ==========================================
    // INTEGRACIÓN CON GATHER
    // ==========================================
    
    /**
     * Vincular un proyecto con un espacio de Gather
     */
    public function vincularGatherSpace($team_id, $space_id, $space_url) {
        $stmt = $this->db->prepare("
            UPDATE teams 
            SET gather_space_id = ?, 
                gather_space_url = ?,
                gather_enabled = TRUE
            WHERE id = ?
        ");
        return $stmt->execute([$space_id, $space_url, $team_id]);
    }
    
    /**
     * Obtener información de Gather de un proyecto
     */
    public function obtenerGatherInfo($team_id) {
        $stmt = $this->db->prepare("
            SELECT gather_space_id, gather_space_url, gather_enabled 
            FROM teams 
            WHERE id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetch();
    }
    
    /**
     * Desactivar integración de Gather para un proyecto
     */
    public function desactivarGather($team_id) {
        $stmt = $this->db->prepare("
            UPDATE teams 
            SET gather_enabled = FALSE 
            WHERE id = ?
        ");
        return $stmt->execute([$team_id]);
    }
    
    /**
     * Activar integración de Gather para un proyecto
     */
    public function activarGather($team_id) {
        $stmt = $this->db->prepare("
            UPDATE teams 
            SET gather_enabled = TRUE 
            WHERE id = ?
        ");
        return $stmt->execute([$team_id]);
    }
    
    /**
     * Obtener miembros con emails para sincronización con Gather
     */
    public function obtenerMiembrosConEmail($team_id) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email, u.status, tm.role 
            FROM users u 
            JOIN team_members tm ON u.id = tm.user_id 
            WHERE tm.team_id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll();
    }
}
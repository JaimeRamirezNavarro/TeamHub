<?php
// modelo/consultas.php
require_once __DIR__ . '/../motor/db.php';

class Consultas {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function verificarlogin($identifier, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();
        
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
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, status) VALUES (?, ?, ?, 'Oficina')");
        return $stmt->execute([$username, $email, $hashed_password]);
    }
    
    /**
     * Registrar usuario automáticamente desde Gather
     * Crea cuenta con rol 'user' (trabajador) por defecto
     */
    public function registrarUsuarioDesdeGather($username, $email, $auto_password) {
        // Verificar si ya existe
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false;
        }

        $hashed_password = password_hash($auto_password, PASSWORD_DEFAULT);
        
        // Insertar con rol 'user' (trabajador)
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, role, status) 
            VALUES (?, ?, ?, 'user', 'Oficina')
        ");
        return $stmt->execute([$username, $email, $hashed_password]);
    }

    // ==========================================
    // SISTEMA REMEMBER ME
    // ==========================================
    
    public function guardarTokenRecordar($user_id, $token) {
        $token_hash = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 días
        
        $stmt = $this->db->prepare("UPDATE users SET remember_token = ?, remember_token_expiry = ? WHERE id = ?");
        return $stmt->execute([$token_hash, $expiry, $user_id]);
    }
    
    public function obtenerUsuarioPorToken($token) {
        $token_hash = hash('sha256', $token);
        
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE remember_token = ? 
            AND remember_token_expiry > NOW()
        ");
        $stmt->execute([$token_hash]);
        return $stmt->fetch();
    }
    
    public function limpiarToken($user_id) {
        $stmt = $this->db->prepare("UPDATE users SET remember_token = NULL, remember_token_expiry = NULL WHERE id = ?");
        return $stmt->execute([$user_id]);
    }

    // ==========================================
    // TRACKING DE ACTIVIDAD
    // ==========================================
    
    public function actualizarUltimaActividad($user_id) {
        $stmt = $this->db->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
        return $stmt->execute([$user_id]);
    }
    
    public function obtenerUsuariosOnline($minutes = 5) {
        $query = $this->db->query("
            SELECT id, username, email, status, last_activity 
            FROM users 
            WHERE last_activity >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)
            AND role != 'admin'
            ORDER BY last_activity DESC
        ");
        return $query ? $query->fetchAll() : [];
    }
    
    public function estaOnline($user_id, $minutes = 5) {
        $stmt = $this->db->prepare("
            SELECT 1 FROM users 
            WHERE id = ? 
            AND last_activity >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)
        ");
        $stmt->execute([$user_id]);
        return (bool) $stmt->fetch();
    }

    // ==========================================
    // GESTIÓN DE EQUIPOS (PROYECTOS)
    // ==========================================
    
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
        if ($this->esGlobalAdmin($user_id)) return 'admin';

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
            SELECT u.id, u.username, u.status, u.last_activity, tm.role 
            FROM users u 
            JOIN team_members tm ON u.id = tm.user_id 
            WHERE tm.team_id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll();
    }

    public function obtenerUsuarios() {
        $query = $this->db->query("SELECT id, username, status, last_activity FROM users");
        return $query ? $query->fetchAll() : []; 
    }

    public function actualizarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE users SET status = ?, last_activity = NOW() WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public function obtenerTareas() {
        $query = $this->db->query("SELECT title, status FROM tasks");
        return $query ? $query->fetchAll() : [];
    }

    // ==========================================
    // INTEGRACIÓN CON GATHER
    // ==========================================
    
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
    
    public function obtenerGatherInfo($team_id) {
        $stmt = $this->db->prepare("
            SELECT gather_space_id, gather_space_url, gather_enabled 
            FROM teams 
            WHERE id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetch();
    }
    
    public function desactivarGather($team_id) {
        $stmt = $this->db->prepare("UPDATE teams SET gather_enabled = FALSE WHERE id = ?");
        return $stmt->execute([$team_id]);
    }
    
    public function activarGather($team_id) {
        $stmt = $this->db->prepare("UPDATE teams SET gather_enabled = TRUE WHERE id = ?");
        return $stmt->execute([$team_id]);
    }
    
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
<?php
require_once __DIR__ . '/../Database/Database.php';

class TeamModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /* ============================
       MÉTODOS EXISTENTES (TUS ORIGINALES)
       ============================ */

    public function obtenerTodos() {
        return $this->db->query("SELECT * FROM teams")->fetchAll();
    }

    public function obtener($id) {
        $stmt = $this->db->prepare("SELECT * FROM teams WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function actualizarEstado($team_id, $status) {
        $valid = ['En Progreso','Completado','Pausado','Cancelado'];
        if (!in_array($status, $valid)) return false;

        $stmt = $this->db->prepare("UPDATE teams SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $team_id]);
    }

    public function vincularGitHub($team_id, $repo) {
        $stmt = $this->db->prepare("UPDATE teams SET github_repo = ? WHERE id = ?");
        return $stmt->execute([$repo, $team_id]);
    }

    public function obtenerMiembros($team_id) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.status, u.last_activity, tm.role
            FROM users u
            JOIN team_members tm ON u.id = tm.user_id
            WHERE tm.team_id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll();
    }

    public function esMiembro($user_id, $team_id) {
        $stmt = $this->db->prepare("
            SELECT 1 FROM team_members 
            WHERE user_id = ? AND team_id = ?
        ");
        $stmt->execute([$user_id, $team_id]);
        return (bool) $stmt->fetch();
    }

    public function unirse($user_id, $team_id) {
        if ($this->esMiembro($user_id, $team_id)) return false;

        $stmt = $this->db->prepare("
            INSERT INTO team_members (user_id, team_id, role)
            VALUES (?, ?, 'member')
        ");
        return $stmt->execute([$user_id, $team_id]);
    }

    public function salir($user_id, $team_id) {
        $stmt = $this->db->prepare("
            DELETE FROM team_members 
            WHERE user_id = ? AND team_id = ?
        ");
        return $stmt->execute([$user_id, $team_id]);
    }

    /* ============================
       NUEVOS MÉTODOS PARA ROADMAP
       ============================ */

    // Alias limpio para obtener equipo (compatibilidad con RoadmapService)
    public function getTeam($id) {
        return $this->obtener($id);
    }

    // Guardar roadmap generado por IA
    public function saveRoadmap($team_id, $roadmap) {
        $stmt = $this->db->prepare("
            UPDATE teams SET ai_roadmap = ? WHERE id = ?
        ");
        return $stmt->execute([
            json_encode($roadmap, JSON_UNESCAPED_UNICODE),
            $team_id
        ]);
    }
}

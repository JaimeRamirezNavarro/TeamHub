<?php
require_once __DIR__ . '/../Database/Database.php';

class TeamModel
{
    public $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /* ============================================================
       OBTENER EQUIPOS SEGÚN ROL
       ============================================================ */
    public function obtenerEquiposPorUsuario($user_id, $role)
    {
        // Admin o manager: solo los equipos que ellos crearon
        if ($role === 'admin' || $role === 'manager') {
            $stmt = $this->db->prepare("
            SELECT *
            FROM teams
            WHERE created_by = ?
        ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Usuarios normales: equipos donde participan
        $stmt = $this->db->prepare("
        SELECT t.*
        FROM teams t
        INNER JOIN team_members tm ON tm.team_id = t.id
        WHERE tm.user_id = ?
    ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ============================================================
       OBTENER TODOS LOS EQUIPOS
       ============================================================ */
    public function obtenerTodos()
    {
        return $this->db->query("SELECT * FROM teams")->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============================================================
       OBTENER UN EQUIPO
       ============================================================ */
    public function obtener($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM teams WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ============================================================
       ACTUALIZAR ESTADO DEL EQUIPO
       ============================================================ */
    public function actualizarEstado($team_id, $status)
    {
        $valid = ['En Progreso', 'Completado', 'Pausado', 'Cancelado'];
        if (!in_array($status, $valid)) return false;

        $stmt = $this->db->prepare("UPDATE teams SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $team_id]);
    }

    /* ============================================================
       VINCULAR REPOSITORIO GITHUB
       ============================================================ */
    public function vincularGitHub($team_id, $repo)
    {
        $stmt = $this->db->prepare("UPDATE teams SET github_repo = ? WHERE id = ?");
        return $stmt->execute([$repo, $team_id]);
    }

    /* ============================================================
       OBTENER MIEMBROS DEL EQUIPO
       ============================================================ */
    public function obtenerMiembros($team_id)
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email, u.status, u.last_activity, tm.role
            FROM users u
            JOIN team_members tm ON u.id = tm.user_id
            WHERE tm.team_id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============================================================
       OBTENER ROL DEL USUARIO EN EL EQUIPO
       ============================================================ */
    public function obtenerRol($user_id, $team_id)
    {
        $stmt = $this->db->prepare("
            SELECT role 
            FROM team_members 
            WHERE user_id = ? AND team_id = ?
            LIMIT 1
        ");
        $stmt->execute([$user_id, $team_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['role'] ?? null;
    }

    public function usuariosDisponibles($team_id)
    {
        $stmt = $this->db->prepare("
        SELECT u.id, u.username, u.email
        FROM users u
        WHERE u.id NOT IN (
            SELECT user_id FROM team_members WHERE team_id = ?
        )
    ");
        $stmt->execute([$team_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============================================================
       COMPROBAR SI ES MIEMBRO
       ============================================================ */
    public function esMiembro($user_id, $team_id)
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM team_members 
            WHERE user_id = ? AND team_id = ?
        ");
        $stmt->execute([$user_id, $team_id]);
        return (bool) $stmt->fetch();
    }

    /* ============================================================
       UNIRSE A UN EQUIPO
       ============================================================ */
    public function unirse($user_id, $team_id)
    {
        if ($this->esMiembro($user_id, $team_id)) return false;

        $stmt = $this->db->prepare("
            INSERT INTO team_members (user_id, team_id, role)
            VALUES (?, ?, 'member')
        ");
        return $stmt->execute([$user_id, $team_id]);
    }

    /* ============================================================
       SALIR DE UN EQUIPO
       ============================================================ */
    public function salir($user_id, $team_id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM team_members 
            WHERE user_id = ? AND team_id = ?
        ");
        return $stmt->execute([$user_id, $team_id]);
    }

    /* ============================================================
       ROADMAP IA
       ============================================================ */
    public function getTeam($id)
    {
        return $this->obtener($id);
    }

    public function saveRoadmap($team_id, $roadmap)
    {
        $stmt = $this->db->prepare("
            UPDATE teams SET ai_roadmap = ? WHERE id = ?
        ");
        return $stmt->execute([
            json_encode($roadmap, JSON_UNESCAPED_UNICODE),
            $team_id
        ]);
    }

    /* ============================================================
       CREAR EQUIPO + AÑADIR CREADOR COMO ADMIN
       ============================================================ */
    public function crear($data)
    {
        // Crear equipo
        $stmt = $this->db->prepare("
            INSERT INTO teams (name, description, status, created_by)
            VALUES (?, ?, 'En Progreso', ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['created_by']
        ]);

        $teamId = $this->db->lastInsertId();

        // Añadir al creador como admin del equipo
        $stmt = $this->db->prepare("
            INSERT INTO team_members (user_id, team_id, role)
            VALUES (?, ?, 'admin')
        ");
        $stmt->execute([$data['created_by'], $teamId]);

        return $teamId;
    }

    public function eliminar($team_id)
    {
        // Eliminar miembros del equipo
        $stmt = $this->db->prepare("DELETE FROM team_members WHERE team_id = ?");
        $stmt->execute([$team_id]);

        // Eliminar el equipo
        $stmt = $this->db->prepare("DELETE FROM teams WHERE id = ?");
        return $stmt->execute([$team_id]);
    }
}

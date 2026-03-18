<?php
require_once __DIR__ . '/../Database/Database.php';


class GatherModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function vincular($team_id, $space_id, $space_url) {
        $stmt = $this->db->prepare("
            UPDATE teams
            SET gather_space_id = ?, gather_space_url = ?, gather_enabled = TRUE
            WHERE id = ?
        ");
        return $stmt->execute([$space_id, $space_url, $team_id]);
    }

    public function obtener($team_id) {
        $stmt = $this->db->prepare("
            SELECT gather_space_id, gather_space_url, gather_enabled
            FROM teams
            WHERE id = ?
        ");
        $stmt->execute([$team_id]);
        return $stmt->fetch();
    }

    public function activar($team_id) {
        return $this->db->prepare("
            UPDATE teams SET gather_enabled = TRUE WHERE id = ?
        ")->execute([$team_id]);
    }

    public function desactivar($team_id) {
        return $this->db->prepare("
            UPDATE teams SET gather_enabled = FALSE WHERE id = ?
        ")->execute([$team_id]);
    }
}

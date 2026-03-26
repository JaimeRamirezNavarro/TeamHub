<?php
require_once __DIR__ . '/../Database/Database.php';


class TaskModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerTodas() {
        return $this->db->query("
            SELECT id, title, description, status, user_id, team_id, created_at
            FROM tasks
        ")->fetchAll();
    }
}

<?php
require_once __DIR__ . '/../Database/Database.php';


class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerUsuario($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function registrar($username, $email, $password, $role = 'user') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "email_invalido";
        if (strlen($username) < 3) return "username_corto";

        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) return "email_duplicado";

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, role, status)
            VALUES (?, ?, ?, ?, 'Oficina')
        ");
        return $stmt->execute([$username, $email, $hashed, $role]);
    }

    public function actualizarEstado($id, $estado) {
        $valid = ['Oficina','Teletrabajo','Ausente','Reunión','Desconectado'];
        if (!in_array($estado, $valid)) return false;

        $stmt = $this->db->prepare("
            UPDATE users 
            SET status = ?, last_activity = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$estado, $id]);
    }

    public function actualizarActividad($id) {
        $stmt = $this->db->prepare("
            UPDATE users SET last_activity = NOW() WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    public function obtenerOnline($minutes = 5) {
        $stmt = $this->db->prepare("
            SELECT id, username, email, status, last_activity
            FROM users
            WHERE last_activity >= (NOW() - INTERVAL ? MINUTE)
            AND role != 'admin'
            ORDER BY last_activity DESC
        ");
        $stmt->execute([$minutes]);
        return $stmt->fetchAll();
    }
}

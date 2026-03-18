<?php
require_once __DIR__ . '/../Database/Database.php';


class AuthModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($identifier, $password) {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE email = ? OR username = ?
        ");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function guardarToken($user_id, $token) {
        $token_hash = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60);

        $stmt = $this->db->prepare("
            UPDATE users 
            SET remember_token = ?, remember_token_expiry = ?
            WHERE id = ?
        ");
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
        $stmt = $this->db->prepare("
            UPDATE users 
            SET remember_token = NULL, remember_token_expiry = NULL
            WHERE id = ?
        ");
        return $stmt->execute([$user_id]);
    }
}

<?php

class UserController {

    private $users;

    public function __construct() {
        $this->users = new UserModel();
    }

    public function obtener($id) {
        return $this->users->obtenerUsuario($id);
    }

    public function registrar($username, $email, $password) {
        return $this->users->registrar($username, $email, $password, 'user');
    }

    public function registrarDesdeGather($username, $email, $password) {
        return $this->users->registrar($username, $email, $password, 'user');
    }

    public function actualizarActividad($user_id) {
        return $this->users->actualizarActividad($user_id);
    }

    public function online($minutes = 5) {
        return $this->users->obtenerOnline($minutes);
    }

    public function actualizarEstado($id, $estado) {
        return $this->users->actualizarEstado($id, $estado);
    }
}

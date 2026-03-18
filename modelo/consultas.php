<?php

require_once __DIR__ . '/../app/Models/AuthModel.php';
require_once __DIR__ . '/../app/Models/UserModel.php';
require_once __DIR__ . '/../app/Models/TeamModel.php';
require_once __DIR__ . '/../app/Models/TaskModel.php';
require_once __DIR__ . '/../app/Models/GatherModel.php';


class Consultas {

    private $auth;
    private $users;
    private $teams;
    private $tasks;
    private $gather;

    public function __construct() {
        $this->auth   = new AuthModel();
        $this->users  = new UserModel();
        $this->teams  = new TeamModel();
        $this->tasks  = new TaskModel();
        $this->gather = new GatherModel();
    }

    /* ============================================================
       LOGIN / AUTENTICACIÓN
    ============================================================ */

    public function verificarlogin($identifier, $password) {
        return $this->auth->login($identifier, $password);
    }

    public function guardarTokenRecordar($user_id, $token) {
        return $this->auth->guardarToken($user_id, $token);
    }

    public function obtenerUsuarioPorToken($token) {
        return $this->auth->obtenerUsuarioPorToken($token);
    }

    public function limpiarToken($user_id) {
        return $this->auth->limpiarToken($user_id);
    }

    /* ============================================================
       USUARIOS
    ============================================================ */

    public function obtenerUsuario($id) {
        return $this->users->obtenerUsuario($id);
    }

    public function registrarUsuario($username, $email, $password) {
        return $this->users->registrar($username, $email, $password, 'user');
    }

    public function registrarUsuarioDesdeGather($username, $email, $auto_password) {
        return $this->users->registrar($username, $email, $auto_password, 'user');
    }

    public function actualizarUltimaActividad($user_id) {
        return $this->users->actualizarActividad($user_id);
    }

    public function obtenerUsuariosOnline($minutes = 5) {
        return $this->users->obtenerOnline($minutes);
    }

    public function estaOnline($user_id, $minutes = 5) {
        $online = $this->users->obtenerOnline($minutes);
        foreach ($online as $u) {
            if ($u['id'] == $user_id) return true;
        }
        return false;
    }

    public function obtenerUsuarios() {
        return $this->users->obtenerOnline(99999); // equivalente a obtener todos
    }

    public function actualizarEstado($id, $estado) {
        return $this->users->actualizarEstado($id, $estado);
    }

    /* ============================================================
       EQUIPOS
    ============================================================ */

    public function obtenerTodosLosEquipos() {
        return $this->teams->obtenerTodos();
    }

    public function obtenerEquipo($id) {
        return $this->teams->obtener($id);
    }

    public function vincularGitHub($team_id, $github_repo) {
        return $this->teams->vincularGitHub($team_id, $github_repo);
    }

    public function actualizarEstadoEquipo($team_id, $status) {
        return $this->teams->actualizarEstado($team_id, $status);
    }

    public function unirseEquipo($user_id, $team_id) {
        return $this->teams->unirse($user_id, $team_id);
    }

    public function salirEquipo($user_id, $team_id) {
        return $this->teams->salir($user_id, $team_id);
    }

    public function esMiembro($user_id, $team_id) {
        return $this->teams->esMiembro($user_id, $team_id);
    }

    public function obtenerRolUsuario($user_id, $team_id) {
        if ($this->users->obtenerUsuario($user_id)['role'] === 'admin') {
            return 'admin';
        }

        $miembros = $this->teams->obtenerMiembros($team_id);
        foreach ($miembros as $m) {
            if ($m['id'] == $user_id) return $m['role'];
        }

        return null;
    }

    public function obtenerMiembrosEquipo($team_id) {
        return $this->teams->obtenerMiembros($team_id);
    }

    public function obtenerMiembrosConEmail($team_id) {
        return $this->teams->obtenerMiembros($team_id);
    }

    /* ============================================================
       TAREAS
    ============================================================ */

    public function obtenerTareas() {
        return $this->tasks->obtenerTodas();
    }

    /* ============================================================
       GATHER
    ============================================================ */

    public function vincularGatherSpace($team_id, $space_id, $space_url) {
        return $this->gather->vincular($team_id, $space_id, $space_url);
    }

    public function obtenerGatherInfo($team_id) {
        return $this->gather->obtener($team_id);
    }

    public function desactivarGather($team_id) {
        return $this->gather->desactivar($team_id);
    }

    public function activarGather($team_id) {
        return $this->gather->activar($team_id);
    }
}

<?php

class DashboardController {

    private $user;
    private $teams;
    private $auth;

    public function __construct() {
        $this->user  = new UserController();
        $this->teams = new TeamController();
        $this->auth  = new AuthController();
    }

    public function cargarDashboard($user_id, $selected_team_id = null) {

        $currentUser = $this->user->obtener($user_id);

        $equipos = $this->teams->obtenerTodos();

        if (!$selected_team_id && count($equipos) > 0) {
            $selected_team_id = $equipos[0]['id'];
        }

        $selected_team = $selected_team_id ? $this->teams->obtener($selected_team_id) : null;

        $miembros = $selected_team ? $this->teams->miembros($selected_team_id) : [];
        $user_role = $selected_team ? $this->teams->obtenerRol($user_id, $selected_team_id) : null;
        $es_miembro = $selected_team ? $this->teams->esMiembro($user_id, $selected_team_id) : false;

        return [
            "usuario"        => $currentUser,
            "equipos"        => $equipos,
            "selected_team"  => $selected_team,
            "miembros"       => $miembros,
            "user_role"      => $user_role,
            "es_miembro"     => $es_miembro,
            "selected_team_id" => $selected_team_id
        ];
    }
}

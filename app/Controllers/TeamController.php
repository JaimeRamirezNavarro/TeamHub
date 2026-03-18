<?php

class TeamController
{

    private $teams;
    private $users;

    public function __construct()
    {
        $this->teams = new TeamModel();
        $this->users = new UserModel();
    }

    /* ============================
       EQUIPOS
    ============================ */

    public function obtenerTodos()
    {
        return $this->teams->obtenerTodos();
    }

    public function obtener($team_id)
    {
        return $this->teams->obtener($team_id);
    }

    /* ============================
       MIEMBROS
    ============================ */

    public function miembros($team_id)
    {
        return $this->teams->obtenerMiembros($team_id);
    }

    public function esMiembro($user_id, $team_id)
    {
        return $this->teams->esMiembro($user_id, $team_id);
    }

    /* ============================
       ROLES
    ============================ */

    public function obtenerRol($user_id, $team_id)
    {

        // Si es admin global
        $user = $this->users->obtenerUsuario($user_id);
        if ($user && $user['role'] === 'admin') {
            return 'admin';
        }

        // Si es miembro del equipo
        $miembros = $this->teams->obtenerMiembros($team_id);
        foreach ($miembros as $m) {
            if ($m['id'] == $user_id) {
                return $m['role'];
            }
        }

        return null;
    }

    /* ============================
       ACCIONES
    ============================ */

    public function unirse($user_id, $team_id)
    {
        return $this->teams->unirse($user_id, $team_id);
    }

    public function salir($user_id, $team_id)
    {
        return $this->teams->salir($user_id, $team_id);
    }

    public function actualizarEstado($team_id, $estado)
    {
        return $this->teams->actualizarEstado($team_id, $estado);
    }

    public function vincularGitHub($team_id, $repo)
    {
        return $this->teams->vincularGitHub($team_id, $repo);
    }
}

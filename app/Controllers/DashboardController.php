<?php

require_once __DIR__ . '/../Models/AuthModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/TeamModel.php';

class DashboardController
{
    private $auth;
    private $users;
    private $teams;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->auth = new AuthModel();

        if (!$this->auth->estaLogueado()) {
            header("Location: " . BASE_PATH . "/login");
            exit;
        }

        $this->users = new UserModel();
        $this->teams = new TeamModel();
    }

    public function index()
    {
        // Procesar unirse o abandonar equipo
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $team_id = intval($_POST['team_id']);
            $user_id = $_SESSION['user']['id'];

            if (isset($_POST['join_team'])) {
                $this->teams->unirse($user_id, $team_id);
            }

            if (isset($_POST['leave_team'])) {
                $this->teams->salir($user_id, $team_id);
            }

            header("Location: " . BASE_PATH . "/dashboard?team_id=" . $team_id);
            exit;
        }

        // Usuario actual
        $user_id = $_SESSION['user']['id'];
        $usuario = $this->users->obtenerUsuario($user_id);

        // Equipos del usuario (NECESARIO PARA EL SIDEBAR)
        $equipos = $this->teams->obtenerEquiposPorUsuario(
            $user_id,
            $_SESSION['user']['role']
        );

        // Equipo seleccionado
        $selected_team_id = $_GET['team_id'] ?? ($equipos[0]['id'] ?? null);
        $selected_team = $selected_team_id ? $this->teams->obtener($selected_team_id) : null;

        $miembros = $selected_team ? $this->teams->obtenerMiembros($selected_team_id) : [];
        $user_role = $selected_team ? $this->teams->obtenerRol($user_id, $selected_team_id) : null;
        $es_miembro = $selected_team ? $this->teams->esMiembro($user_id, $selected_team_id) : false;

        // Título
        $title = "Dashboard";

        // Cargar vista
        ob_start();
        require __DIR__ . '/../Views/pages/dashboard.php';
        $content = ob_get_clean();

        // Cargar layout principal
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function teamSettings()
    {
        if (!isset($_GET['team_id'])) {
            die("Falta team_id");
        }

        $team_id = intval($_GET['team_id']);

        $team = $this->teams->obtener($team_id);
        $miembros = $this->teams->obtenerMiembros($team_id);

        // Equipos para el sidebar
        $equipos = $this->teams->obtenerEquiposPorUsuario(
            $_SESSION['user']['id'],
            $_SESSION['user']['role']
        );

        $title = "Ajustes del equipo";

        ob_start();
        require __DIR__ . '/../Views/pages/team_settings.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function onlineUsers()
    {
        $online = $this->users->obtenerOnline(5);

        // Equipos para el sidebar
        $equipos = $this->teams->obtenerEquiposPorUsuario(
            $_SESSION['user']['id'],
            $_SESSION['user']['role']
        );

        $title = "Usuarios Online";

        ob_start();
        require __DIR__ . '/../Views/pages/online_users.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}

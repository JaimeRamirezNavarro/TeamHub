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

            $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : (isset($_GET['team_id']) ? intval($_GET['team_id']) : null);
            $user_id = $_SESSION['user']['id'];

            if (isset($_POST['join_team'])) {
                $this->teams->unirse($user_id, $team_id);
            }

            if (isset($_POST['leave_team'])) {
                $this->teams->salir($user_id, $team_id);
            }

            // Handle User Status Update (Neo-Brutalist Sidebar)
            if (isset($_POST['update_user_status'])) {
                $new_user_status = $_POST['new_user_status'] ?? '';
                if ($new_user_status) {
                    $this->users->actualizarEstado($user_id, $new_user_status);
                }
            }

            if (isset($_POST['update_github'])) {
                $github_repo = $_POST['github_repo'] ?? '';
                $this->teams->vincularGitHub($team_id, $github_repo);
            }

            if (isset($_POST['update_status'])) {
                $new_status = $_POST['new_status'] ?? '';
                if ($new_status) {
                    $this->teams->actualizarEstado($team_id, $new_status);
                    
                    if ($new_status === 'Completado') {
                        $team = $this->teams->obtener($team_id);
                        if (!empty($team['ai_roadmap'])) {
                            $roadmap = json_decode($team['ai_roadmap'], true);
                            if (is_array($roadmap)) {
                                foreach ($roadmap as &$phase) {
                                    $phase['avance'] = 100;
                                    $phase['completado'] = true;
                                }
                                $this->teams->saveRoadmap($team_id, $roadmap);
                            }
                        }
                    }
                }
            }

            if ($team_id) {
                header("Location: " . BASE_PATH . "/dashboard?team_id=" . $team_id);
            } else {
                header("Location: " . BASE_PATH . "/dashboard");
            }
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

        // Global Supreme Admin override
        if ($_SESSION['user']['role'] === 'admin') {
            $user_role = 'admin';
            $es_miembro = true; // Permite al admin "Abandonar" si lo programamos, o simplemente considerarlo del equipo
        }

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

        require __DIR__ . '/../Views/pages/online_users.php';
    }
}

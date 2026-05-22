<?php

require_once __DIR__ . '/../Models/TeamModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Middleware/Auth.php';

class TeamController
{
    private $teams;
    private $users;

    public function __construct()
    {
        Auth::requireLogin(); // Protección global
        $this->teams = new TeamModel();
        $this->users = new UserModel();
    }

    /* ============================
       LISTAR SOLO MIS EQUIPOS
    ============================ */
    public function index()
    {
        $user = $_SESSION['user'];

        // Equipos donde el usuario participa
        $equipos = $this->teams->obtenerEquiposPorUsuario(
            $user['id'],
            $user['role']
        );

        $title = "Mis Equipos";

        ob_start();
        require __DIR__ . '/../Views/pages/teams/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    /* ============================
       LISTAR TODOS LOS EQUIPOS (ADMIN/MANAGER)
    ============================ */
    public function all()
    {
        Auth::requireRole(['admin', 'manager']);

        $user = $_SESSION['user'];

        // Equipos para el sidebar
        $sidebarEquipos = $this->teams->obtenerEquiposPorUsuario(
            $user['id'],
            $user['role']
        );

        // Todos los equipos del sistema
        $equipos = $this->teams->obtenerTodos();

        $title = "Todos los Equipos";

        ob_start();
        require __DIR__ . '/../Views/pages/teams/all.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    /* ============================
       VER EQUIPO
    ============================ */
    public function show($team_id)
    {
        $user = $_SESSION['user'];

        // Verificar que el equipo existe
        $team = $this->teams->obtener($team_id);
        if (!$team) {
            http_response_code(404);
            die("El equipo no existe.");
        }

        // Admin y managers pueden ver cualquier equipo
        if (!in_array($user['role'], ['admin', 'manager'])) {
            if (!$this->teams->esMiembro($user['id'], $team_id)) {
                die("No tienes permiso para ver este equipo.");
            }
        }

        $miembros = $this->teams->obtenerMiembros($team_id);

        // Equipos para el sidebar
        $equipos = $this->teams->obtenerEquiposPorUsuario(
            $user['id'],
            $user['role']
        );

        $title = "Equipo: " . $team['name'];

        ob_start();
        require __DIR__ . '/../Views/pages/teams/show.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    /* ============================
       CREAR EQUIPO (solo admin/manager)
    ============================ */
    public function create()
    {
        Auth::requireRole(['admin', 'manager']);

        $usuario = $_SESSION['user'];

        // Equipos para el sidebar
        $equipos = $this->teams->obtenerEquiposPorUsuario(
            $usuario['id'],
            $usuario['role']
        );

        $title = "Crear Equipo";

        ob_start();
        require __DIR__ . '/../Views/pages/teams/create.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }


    /* ============================
       GUARDAR EQUIPO
    ============================ */
    public function store()
    {
        Auth::requireRole(['admin', 'manager']);

        if (empty($_POST['name'])) {
            die("El nombre del equipo es obligatorio.");
        }

        $user = $_SESSION['user'];

        $this->teams->crear([
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? null,
            'created_by' => $user['id']
        ]);

        header("Location: " . BASE_PATH . "/teams");
        exit;
    }

    /* ============================
   ELIMINAR EQUIPO
============================ */
    public function delete()
    {
        // Solo admin o manager pueden eliminar equipos
        Auth::requireRole(['admin', 'manager']);

        // Asegurar que viene por POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $team_id = $_POST['team_id'] ?? null;

        if (!$team_id) {
            die("ID de equipo no proporcionado.");
        }

        // Verificar que el equipo existe
        if (!$this->teams->obtener($team_id)) {
            die("El equipo no existe.");
        }

        // Eliminar equipo (y sus miembros)
        $this->teams->eliminar($team_id);

        // Redirigir a la lista de todos los equipos
        header("Location: " . BASE_PATH . "/teams/all");
        exit;
    }

    public function addMemberForm($team_id)
    {
        Auth::requireRole(['admin', 'manager']);

        $team = $this->teams->obtener($team_id);
        if (!$team) die("Equipo no encontrado.");

        // Obtener usuarios que NO están en el equipo
        $users = $this->teams->usuariosDisponibles($team_id);

        require __DIR__ . '/../Views/pages/teams/add-member.php';
    }

    public function addMemberStore($team_id)
    {
        Auth::requireRole(['admin', 'manager']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        $user_ids = $_POST['user_ids'] ?? [];

        if (empty($user_ids)) {
            die("No seleccionaste ningún usuario.");
        }

        foreach ($user_ids as $uid) {
            $this->teams->unirse($uid, $team_id);
        }

        header("Location: " . BASE_PATH . "/teams/$team_id");
        exit;
    }




    /* ============================
       ACTUALIZAR ESTADO
    ============================ */
    public function updateStatus()
    {
        Auth::requireRole(['admin', 'manager']);

        $team_id = $_POST['team_id'];
        $status = $_POST['status'];

        if (!$this->teams->obtener($team_id)) {
            die("El equipo no existe.");
        }

        $this->teams->actualizarEstado($team_id, $status);

        header("Location: " . BASE_PATH . "/teams/$team_id");
        exit;
    }

    public function removeMember($team_id, $user_id)
    {
        Auth::requireRole(['admin', 'manager']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die("Método no permitido.");
        }

        // Verificar que el usuario pertenece al equipo
        if (!$this->teams->esMiembro($user_id, $team_id)) {
            die("El usuario no pertenece a este equipo.");
        }

        // Eliminar del equipo
        $this->teams->salir($user_id, $team_id);

        // Redirigir de vuelta al equipo
        header("Location: " . BASE_PATH . "/teams/$team_id");
        exit;
    }

    /* ============================
       VINCULAR GITHUB
    ============================ */
    public function linkGithub()
    {
        Auth::requireRole(['admin', 'manager']);

        $team_id = $_POST['team_id'];
        $repo = $_POST['github_repo'];

        if (!$this->teams->obtener($team_id)) {
            die("El equipo no existe.");
        }

        $this->teams->vincularGitHub($team_id, $repo);

        header("Location: " . BASE_PATH . "/teams/$team_id");
        exit;
    }

    public function saveGithubToken()
    {
        Auth::requireRole(['admin', 'manager']);

        $team_id = $_POST['team_id'] ?? null;
        $token = trim($_POST['github_token'] ?? '');

        if (!$team_id) {
            die("Team ID no proporcionado.");
        }

        $team = $this->teams->obtener($team_id);

        if (!$team) {
            die("El equipo no existe.");
        }

        // Si el admin quiere eliminar el token
        if (isset($_POST['remove_github_token'])) {
            $token = null;
        }

        // Guardar o eliminar token
        $this->teams->guardarTokenGithub($team_id, $token);

        // Redirigir de vuelta al panel del equipo
        header("Location: " . BASE_PATH . "/teams/" . $team_id);
        exit;
    }


    /* ============================
       UNIRSE A UN EQUIPO
    ============================ */
    public function join($team_id)
    {
        $user = $_SESSION['user'];

        if (!$this->teams->obtener($team_id)) {
            die("El equipo no existe.");
        }

        if ($this->teams->esMiembro($user['id'], $team_id)) {
            header("Location: " . BASE_PATH . "/teams/$team_id");
            exit;
        }

        $this->teams->unirse($user['id'], $team_id);

        header("Location: " . BASE_PATH . "/teams/$team_id");
        exit;
    }

    /* ============================
       SALIR DE UN EQUIPO
    ============================ */
    public function leave($team_id)
    {
        $user = $_SESSION['user'];

        if (!$this->teams->obtener($team_id)) {
            die("El equipo no existe.");
        }

        if (!$this->teams->esMiembro($user['id'], $team_id)) {
            die("No puedes salir de un equipo al que no perteneces.");
        }

        $this->teams->salir($user['id'], $team_id);

        header("Location: " . BASE_PATH . "/teams");
        exit;
    }
}

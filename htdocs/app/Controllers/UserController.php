<?php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Middleware/Auth.php';


class UserController
{
    private $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    /* ============================================================
       LISTAR USUARIOS (ruta: /users)
    ============================================================ */
    public function index()
    {
        // Obtener todos los usuarios
        $usuarios = $this->users->obtenerTodos();

        $title = "Gestión de usuarios";

        ob_start();
        require __DIR__ . '/../Views/pages/users/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    /* ============================================================
       FORMULARIO DE CREACIÓN (ruta: /users/create)
    ============================================================ */
    public function create()
    {
        $title = "Crear usuario";

        ob_start();
        require __DIR__ . '/../Views/pages/users/create.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    /* ============================================================
       GUARDAR NUEVO USUARIO (ruta: /users/store)
    ============================================================ */
    public function store()
    {
        $username = $_POST['username'] ?? null;
        $email    = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$username || !$email || !$password) {
            die("Faltan datos para crear el usuario");
        }

        $this->users->registrar($username, $email, $password, 'user');

        header("Location: " . BASE_PATH . "/users");
        exit;
    }

    /* ============================================================
       MÉTODOS EXISTENTES (compatibilidad con tu sistema)
    ============================================================ */

    public function obtener($id)
    {
        return $this->users->obtenerUsuario($id);
    }

    public function registrar($username, $email, $password)
    {
        return $this->users->registrar($username, $email, $password, 'user');
    }

    public function registrarDesdeGather($username, $email, $password)
    {
        return $this->users->registrar($username, $email, $password, 'user');
    }

    public function actualizarActividad($user_id)
    {
        return $this->users->actualizarActividad($user_id);
    }

    public function online($minutes = 5)
    {
        return $this->users->obtenerOnline($minutes);
    }

    public function actualizarEstado($id, $estado)
    {
        return $this->users->actualizarEstado($id, $estado);
    }

    public function editRole($user_id)
    {
        Auth::requireRole(['admin']);

        $usuario = $_SESSION['user'];
        $usuarioEditar = $this->users->obtenerUsuario($user_id);

        if (!$usuarioEditar) {
            die("Usuario no encontrado.");
        }

        $title = "Cambiar rol";

        ob_start();
        require __DIR__ . '/../Views/pages/users/edit_role.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function updateRole()
    {
        Auth::requireRole(['admin']);

        $user_id = $_POST['user_id'];
        $role = $_POST['role'];

        if (!in_array($role, ['user', 'manager', 'admin'])) {
            die("Rol inválido.");
        }

        $this->users->actualizarRol($user_id, $role);

        header("Location: " . BASE_PATH . "/users");
        exit;
    }
}

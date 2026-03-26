<?php

require_once __DIR__ . '/../Models/AuthModel.php';
require_once __DIR__ . '/../Models/UserModel.php';

class AuthController
{
    private $auth;
    private $users;

    public function __construct()
    {
        $this->auth = new AuthModel();
        $this->users = new UserModel();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /* ============================
       MOSTRAR LOGIN
    ============================ */
    public function showLogin()
    {
        $error = '';
        $success = '';
        $active_tab = 'login';

        // Si ya está logueado → redirigir
        if (isset($_SESSION['user'])) {
            header("Location: " . BASE_PATH . "/dashboard");
            exit;
        }

        // Remember me
        if (!isset($_SESSION['user']) && isset($_COOKIE['teamhub_remember'])) {
            $token = $_COOKIE['teamhub_remember'];
            $user = $this->auth->obtenerUsuarioPorToken($token);

            if ($user) {

                // Guardar sesión completa
                $_SESSION['user'] = [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'role'     => $user['role']
                ];

                $_SESSION['user_id'] = $user['id']; // ← NECESARIO

                $this->users->actualizarActividad($user['id']);
                header("Location: " . BASE_PATH . "/dashboard");
                exit;
            }
        }

        // POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            /* ============================
               LOGIN
            ============================ */
            if (isset($_POST['login'])) {

                $identifier = trim($_POST['identifier']);
                $password = trim($_POST['password']);

                $user = $this->auth->login($identifier, $password);

                if ($user) {
                    $this->loginUser($user, isset($_POST['remember_me']));
                } else {
                    $error = 'Credenciales incorrectas';
                }
            }

            /* ============================
               REGISTRO
            ============================ */ elseif (isset($_POST['register'])) {

                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = trim($_POST['password']);

                if ($this->users->registrar($username, $email, $password)) {
                    $success = '¡Cuenta creada con éxito! Por favor inicia sesión.';
                    $active_tab = 'login';
                } else {
                    $error = 'El correo ya está registrado.';
                    $active_tab = 'register';
                }
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    /* ============================
       PROCESAR LOGIN
    ============================ */
    private function loginUser($user, $remember)
    {
        // Guardar datos en sesión
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role']
        ];

        // Compatibilidad con endpoints antiguos
        $_SESSION['user_id'] = $user['id'];

        // Remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->auth->guardarToken($user['id'], $token);

            setcookie(
                'teamhub_remember',
                $token,
                time() + (30 * 24 * 60 * 60), // 30 días
                '/',
                '',
                false,
                true
            );
        }

        // Actualizar estado y actividad
        $this->users->actualizarEstado($user['id'], 'Oficina');
        $this->users->actualizarActividad($user['id']);

        header("Location: " . BASE_PATH . "/dashboard");
        exit;
    }

    /* ============================
       LOGOUT
    ============================ */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Borrar cookie remember me
        if (isset($_COOKIE['teamhub_remember'])) {
            setcookie('teamhub_remember', '', time() - 3600, '/', '', false, true);
        }

        session_unset();
        session_destroy();

        header("Location: " . BASE_PATH . "/login");
        exit;
    }
}

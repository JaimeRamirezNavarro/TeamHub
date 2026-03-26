<?php

class Auth
{
    /**
     * Inicia la sesión si no está iniciada.
     */
    private static function ensureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Requiere que el usuario haya iniciado sesión.
     * Si no, lo envía al login.
     */
    public static function requireLogin()
    {
        self::ensureSession();

        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_PATH . "/login");
            exit;
        }
    }

    /**
     * Requiere que el usuario tenga uno de los roles indicados.
     * Si no, muestra error 403.
     */
    public static function requireRole($roles)
    {
        self::requireLogin(); // ya valida sesión

        $userRole = $_SESSION['user']['role'] ?? 'user';

        if (!in_array($userRole, $roles)) {
            http_response_code(403);
            die("No tienes permisos para acceder a esta sección.");
        }
    }

    /**
     * Comprueba si el usuario tiene uno de los roles indicados.
     * Útil para mostrar/ocultar botones en las vistas.
     */
    public static function hasRole($roles)
    {
        self::ensureSession();

        $userRole = $_SESSION['user']['role'] ?? 'user';
        return in_array($userRole, $roles);
    }

    /**
     * Devuelve el usuario actual (o null si no hay sesión)
     */
    public static function user()
    {
        self::ensureSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Cierra la sesión del usuario
     */
    public static function logout()
    {
        self::ensureSession();

        session_unset();
        session_destroy();

        header("Location: " . BASE_PATH . "/login");
        exit;
    }
}

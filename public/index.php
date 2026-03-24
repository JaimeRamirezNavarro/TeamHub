<?php

// ============================================================
// INICIAR SESIÓN GLOBAL
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../bootstrap.php';

// ============================================================
// OBTENER RUTA LIMPIA (Subfolder aware)
// ============================================================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace(BASE_PATH, '', $uri);
$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';

// ============================================================
// RUTAS
// ============================================================
switch ($uri) {

    // --------------------------------------------------------
    // HOME → redirige según sesión
    // --------------------------------------------------------
    case '/':
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_PATH . "/login");
            exit;
        }
        header("Location: " . BASE_PATH . "/dashboard");
        exit;

        // --------------------------------------------------------
        // AUTENTICACIÓN
        // --------------------------------------------------------
    case '/login':
        (new AuthController())->showLogin();
        break;

    case '/logout':
        (new AuthController())->logout();
        break;

    // --------------------------------------------------------
    // DASHBOARD
    // --------------------------------------------------------
    case '/dashboard':
        (new DashboardController())->index();
        break;

    case '/team-settings':
        (new DashboardController())->teamSettings();
        break;

    case '/online-users':
        (new DashboardController())->onlineUsers();
        break;

    // --------------------------------------------------------
    // API ENDPOINTS
    // --------------------------------------------------------
    case '/api/online-users':
        require __DIR__ . '/endpoints/get_online_users.php';
        break;

    case '/api/heartbeat':
        require __DIR__ . '/endpoints/heartbeat.php';
        break;

    case '/api/roadmap':
        require __DIR__ . '/endpoints/roadmap_generator.php';
        break;

    case '/api/github':
        require __DIR__ . '/endpoints/github_proxy.php';
        break;

    // --------------------------------------------------------
    // EQUIPOS
    // --------------------------------------------------------
    case '/teams':
        (new TeamController())->index();
        break;

    case '/teams/all':   // 🔥 NUEVA RUTA PARA VER TODOS LOS EQUIPOS
        (new TeamController())->all();
        break;

    case '/teams/create':
        (new TeamController())->create();
        break;

    case '/teams/store':
        (new TeamController())->store();
        break;

    case '/teams/update-status':
        (new TeamController())->updateStatus();
        break;

    case '/teams/link-github':
        (new TeamController())->linkGithub();
        break;

    // --------------------------------------------------------
    // ADMINISTRACIÓN
    // --------------------------------------------------------
    case '/admin':
        (new AdminController())->index();
        break;

    // --------------------------------------------------------
    // EQUIPOS DINÁMICOS
    // --------------------------------------------------------
    default:

        // Ver equipo
        if (preg_match('#^/teams/([0-9]+)$#', $uri, $m)) {
            (new TeamController())->show($m[1]);
            break;
        }

        // Unirse
        if (preg_match('#^/teams/([0-9]+)/join$#', $uri, $m)) {
            (new TeamController())->join($m[1]);
            break;
        }

        // Salir
        if (preg_match('#^/teams/([0-9]+)/leave$#', $uri, $m)) {
            (new TeamController())->leave($m[1]);
            break;
        }

        // --------------------------------------------------------
        // USUARIOS (solo admin)
        // --------------------------------------------------------
        if ($uri === '/users') {
            (new UserController())->index();
            break;
        }

        if ($uri === '/users/create') {
            (new UserController())->create();
            break;
        }

        if ($uri === '/users/store') {
            (new UserController())->store();
            break;
        }

        // --------------------------------------------------------
        // 404
        // --------------------------------------------------------
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        break;
}

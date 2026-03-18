<?php

session_start();
require_once __DIR__ . '/../bootstrap.php';


// Procesar logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    $controller = new AuthController();
    $controller->logout();
}


// API: Roadmap
if (isset($_GET['api']) && $_GET['api'] === 'roadmap') {
    require __DIR__ . '/../endpoints/roadmap_generator.php';
    exit;
}

// API: Heartbeat
if (isset($_GET['api']) && $_GET['api'] === 'heartbeat') {
    require __DIR__ . '/../endpoints/heartbeat.php';
    exit;
}

// API: Online Users (JSON)
if (isset($_GET['api']) && $_GET['api'] === 'online_users') {
    require __DIR__ . '/../endpoints/get_online_users.php';
    exit;
}

// API: GitHub Proxy
if (isset($_GET['api']) && $_GET['api'] === 'github') {
    require __DIR__ . '/../endpoints/github_proxy.php';
    exit;
}

// PAGE: Team Settings
if (isset($_GET['page']) && $_GET['page'] === 'team_settings') {

    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $selected_team_id = $_GET['team_id'] ?? null;

    $controller = new DashboardController();
    $data = $controller->cargarDashboard($user_id, $selected_team_id);
    extract($data);

    $extra_css = "/assets/css/team_settings.css";

    ob_start();
    include __DIR__ . '/../app/Views/pages/team_settings.php';
    $content = ob_get_clean();

    include __DIR__ . '/../app/Views/layouts/main.php';
    exit;
}


// PAGE: Online Users (HTML)
if (isset($_GET['page']) && $_GET['page'] === 'online_users') {

    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }


    $user_id = $_SESSION['user_id'];
    $selected_team_id = $_GET['team_id'] ?? null;

    // Cargar los mismos datos que usa el dashboard
    $controller = new DashboardController();
    $data = $controller->cargarDashboard($user_id, $selected_team_id);
    extract($data);

    // CSS específico
    $extra_css = "/assets/css/online_users.css";

    ob_start();
    include __DIR__ . '/../app/Views/pages/online_users.php';
    $content = ob_get_clean();

    include __DIR__ . '/../app/Views/layouts/main.php';
    exit;
}



// Asegurar login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}


$user_id = $_SESSION['user_id'];
$selected_team_id = $_GET['team_id'] ?? null;


// Unirse a equipo
if (isset($_POST['join_team']) && isset($_POST['team_id'])) {
    $teamId = intval($_POST['team_id']);
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("INSERT IGNORE INTO team_members (team_id, user_id) VALUES (?, ?)");
    $stmt->execute([$teamId, $user_id]);

    header("Location: /?team_id=$teamId");
    exit;
}

// Salir de equipo
if (isset($_POST['leave_team']) && isset($_POST['team_id'])) {
    $teamId = intval($_POST['team_id']);
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
    $stmt->execute([$teamId, $user_id]);

    header("Location: /?team_id=$teamId");
    exit;
}

// Guardar repositorio GitHub
if (isset($_POST['save_github_repo']) && isset($_POST['team_id'])) {
    $repo = trim($_POST['github_repo']);

    $teamsModel = new TeamModel();
    $teamsModel->vincularGitHub($_POST['team_id'], $repo);

    header("Location: /?page=team_settings&team_id=" . $_POST['team_id']);
    exit;
}

// Eliminar repositorio GitHub
if (isset($_POST['remove_github_repo']) && isset($_POST['team_id'])) {
    $teamsModel = new TeamModel();
    $teamsModel->vincularGitHub($_POST['team_id'], null);

    header("Location: /?page=team_settings&team_id=" . $_POST['team_id']);
    exit;
}


// Dashboard
$controller = new DashboardController();
$data = $controller->cargarDashboard($user_id, $selected_team_id);
extract($data);

ob_start();
include __DIR__ . '/../app/Views/pages/dashboard.php';
$content = ob_get_clean();

include __DIR__ . '/../app/Views/layouts/main.php';
exit;

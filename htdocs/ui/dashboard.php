<?php
session_start();
require_once __DIR__ . '/../modelo/consultas.php';

// Auth Check
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$consultas = new Consultas();
$user_id = $_SESSION['user_id'];

// Fetch latest user data
$currentUser = $consultas->obtenerUsuario($user_id);
$username = $currentUser['username'];
$userStatus = $currentUser['status'];

// Handle Actions (Join/Leave/Update Status/Logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        // Clear Remember Me Token in DB
        if (isset($_SESSION['user_id'])) {
            $consultas->limpiarToken($_SESSION['user_id']);
        }
        
        // Clear Cookie
        if (isset($_COOKIE['teamhub_remember'])) {
            setcookie('teamhub_remember', '', time() - 3600, '/');
            unset($_COOKIE['teamhub_remember']);
        }

        session_destroy();
        header("Location: login.php");
        exit;
    }
    
    // User Status Update
    if (isset($_POST['update_user_status'])) {
        $consultas->actualizarEstado($user_id, $_POST['new_user_status']);
        // Redirect to same page with query params to avoid form resubmission warning
        $params = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
        header("Location: dashboard.php" . $params);
        exit;
    }

    // Status Update (Manager Only)
    if (isset($_POST['update_status']) && isset($_POST['team_id']) && isset($_POST['new_status'])) {
        // Double check permissions serverside
        $role = $consultas->obtenerRolUsuario($user_id, $_POST['team_id']);
        if ($role === 'admin') {
            $consultas->actualizarEstadoEquipo($_POST['team_id'], $_POST['new_status']);
        }
    }

    // Join/Leave
    if (isset($_POST['join_team'])) {
        $consultas->unirseEquipo($user_id, $_POST['team_id']);
    } elseif (isset($_POST['leave_team'])) {
        $consultas->salirEquipo($user_id, $_POST['team_id']);
    }
    
    // Update GitHub Repo (Manager Only)
    if (isset($_POST['update_github']) && isset($_POST['team_id'])) {
        $role = $consultas->obtenerRolUsuario($user_id, $_POST['team_id']);
        if ($role === 'admin') {
            $repo = trim($_POST['github_repo']);
            if (empty($repo)) $repo = null;
            $consultas->vincularGitHub($_POST['team_id'], $repo);
        }
    }
    
    // Redirect to avoid resubmission, keeping the selected team
    $redirect_team = isset($_POST['team_id']) ? "?team_id=" . $_POST['team_id'] : "";
    header("Location: dashboard.php" . $redirect_team);
    exit;
}

// Data Handling
$equipos = $consultas->obtenerTodosLosEquipos();
$selected_team_id = isset($_GET['team_id']) ? $_GET['team_id'] : (count($equipos) > 0 ? $equipos[0]['id'] : null);

$selected_team = null;
$miembros = [];
$user_role = null;
$es_miembro = false;

if ($selected_team_id) {
    $selected_team = $consultas->obtenerEquipo($selected_team_id);
    if ($selected_team) {
        $miembros = $consultas->obtenerMiembrosEquipo($selected_team_id);
        $user_role = $consultas->obtenerRolUsuario($user_id, $selected_team_id);
        $es_miembro = $consultas->esMiembro($user_id, $selected_team_id);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TeamHub | Dashboard</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Enterprise Color Palette */
            --bg-color: #f8fafc;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --accent-color: #3b82f6;
            --accent-hover: #2563eb;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --border-color: #e2e8f0;
            --border-light: #f1f5f9;
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
            --radius-md: 10px;
            --radius-lg: 16px;
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        /* Dark Theme Override */
        body.dark-theme {
            --bg-color: #0f172a;
            --sidebar-bg: #1e293b;
            --card-bg: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --border-color: #334155;
            --border-light: #475569;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.5);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.5), 0 2px 4px -1px rgba(0, 0, 0, 0.4);
            
            /* Status overrides for better contrast in dark mode */
            --success-color: #10b981;
            --warning-color: #f59e0b;
        }

        body.dark-theme .project-link.active {
            background-color: rgba(37, 99, 235, 0.2);
            color: #60a5fa;
        }
        body.dark-theme .project-link:hover {
            color: #f8fafc;
        }
        body.dark-theme .btn-danger {
            background: transparent;
        }
        body.dark-theme .logout-btn {
            background: transparent;
        }
        body.dark-theme .logout-btn:hover {
            background: rgba(220, 38, 38, 0.1);
        }
        body.dark-theme .status-En\.Progreso { 
            background: rgba(37, 99, 235, 0.2); 
            color: #93c5fd; 
            border-color: rgba(37, 99, 235, 0.3); 
        }
        body.dark-theme .status-Completado { 
            background: rgba(22, 163, 74, 0.2); 
            color: #86efac; 
            border-color: rgba(22, 163, 74, 0.3); 
        }
        body.dark-theme .status-Pausado { 
            background: rgba(217, 119, 6, 0.2); 
            color: #fcd34d; 
            border-color: rgba(217, 119, 6, 0.3); 
        }
        body.dark-theme .status-Cancelado { 
            background: rgba(220, 38, 38, 0.2); 
            color: #fca5a5; 
            border-color: rgba(220, 38, 38, 0.3); 
        }
        body.dark-theme .form-input, body.dark-theme .form-select, body.dark-theme .status-select {
            background: #0f172a;
            color: var(--text-primary);
        }
        body.dark-theme .role-badge {
            background: #334155;
            color: #cbd5e1;
        }
        body.dark-theme .role-admin {
            background: rgba(217, 119, 6, 0.2);
            color: #fcd34d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            margin: 0;
            height: 100vh;
            display: grid;
            grid-template-columns: 280px 1fr;
            background-color: var(--bg-color);
            color: var(--text-primary);
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            z-index: 10;
        }

        .brand {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 32px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.025em;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: var(--bg-color);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            margin-bottom: 24px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--accent-color);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: var(--shadow-sm);
        }

        .status-select {
            background: #ffffff;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.75rem;
            margin-top: 6px;
            width: 100%;
            cursor: pointer;
            transition: border-color 0.2s;
            font-family: var(--font-family);
        }
        
        .status-select:hover, .status-select:focus {
            border-color: var(--accent-color);
            outline: none;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 12px;
            margin-top: 16px;
        }

        .project-list {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .project-item {
            margin-bottom: 4px;
        }

        .project-link {
            display: block;
            padding: 10px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .project-link:hover {
            background-color: var(--bg-color);
            color: var(--text-primary);
        }

        .project-link.active {
            background-color: #eff6ff;
            color: var(--accent-color);
            font-weight: 600;
        }

        .logout-btn {
            margin-top: auto;
            background: #ffffff;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 10px;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
            width: 100%;
            font-family: var(--font-family);
        }

        .logout-btn:hover {
            background: #fef2f2;
            border-color: #fca5a5;
            color: var(--danger-color);
        }

        /* Main Content Styles */
        .main-content {
            padding: 40px 48px;
            overflow-y: auto;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
        }

        .project-title {
            font-size: 1.875rem;
            font-weight: 700;
            margin: 0 0 12px 0;
            color: var(--text-primary);
            letter-spacing: -0.025em;
        }

        .project-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            border: 1px solid transparent;
        }

        .status-En.Progreso { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .status-Completado { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
        .status-Pausado { background: #fffbeb; color: #b45309; border-color: #fde68a; }
        .status-Cancelado { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 32px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .card-title {
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 12px;
        }

        .description-text {
            line-height: 1.6;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Members List */
        .member-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .member-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: var(--bg-color);
            border-radius: var(--radius-md);
            border: 1px solid transparent;
            transition: border-color 0.2s;
        }
        
        .member-item:hover {
            border-color: var(--border-color);
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .member-name {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
        }

        .role-badge {
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 9999px;
            font-weight: 600;
            background: #f3f4f6;
            color: #4b5563;
        }

        .role-admin {
            background: #fff7ed;
            color: #c2410c;
        }
        
        /* User Status Dot */
        .status-dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin-left: 8px; flex-shrink: 0; }
        .user-status-Oficina { background-color: var(--success-color); }
        .user-status-Teletrabajo { background-color: var(--accent-color); }
        .user-status-Reunión { background-color: var(--warning-color); }
        .user-status-Ausente { background-color: var(--danger-color); }
        .user-status-Desconectado { background-color: var(--text-muted); }
        .user-status-En-Gather { background-color: #8b5cf6; }

        /* Action Buttons */
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s;
            font-family: var(--font-family);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary { 
            background: var(--accent-color); 
            color: white; 
            box-shadow: 0 1px 2px rgba(37, 99, 235, 0.3);
        }
        .btn-primary:hover { background: var(--accent-hover); }

        .btn-danger { background: #ffffff; border: 1px solid var(--danger-color); color: var(--danger-color); }
        .btn-danger:hover { background: #fef2f2; }
        
        .btn-dark { background: #111827; color: white; }
        .btn-dark:hover { background: #1f2937; }

        /* Form Elements */
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: #ffffff;
            color: var(--text-primary);
            font-family: var(--font-family);
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .status-form {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--bg-color);
            padding: 16px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-light);
        }

        /* GitHub Widget Styles Enterprise */
        .github-widget {
            margin-top: 24px;
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        .github-header {
            background: var(--bg-color);
            padding: 16px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
        }
        .github-tabs {
            display: flex;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
        }
        .github-tab {
            padding: 12px 20px;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 500;
            transition: 0.2s;
            border-bottom: 2px solid transparent;
        }
        .github-tab.active {
            color: var(--accent-color);
            border-bottom-color: var(--accent-color);
        }
        .github-tab:hover:not(.active) {
            color: var(--text-primary);
            background: var(--bg-color);
        }
        .github-content {
            padding: 0;
            max-height: 350px;
            overflow-y: auto;
            font-size: 0.9rem;
            background: var(--card-bg);
        }
        .gh-item {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
            transition: background 0.2s;
        }
        .gh-item:hover {
            background: var(--bg-color);
        }
        .gh-item:last-child {
            border-bottom: none;
        }
        .gh-title { font-weight: 500; color: var(--text-primary); text-decoration: none; display: block; margin-bottom: 4px; line-height: 1.4; }
        .gh-title:hover { color: var(--accent-color); }
        .gh-meta { font-size: 0.8rem; color: var(--text-muted); }
        .gh-loader { text-align: center; color: var(--text-secondary); padding: 30px; font-size: 0.9rem; }
        
        .gh-badge {
            display: inline-flex;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 6px;
            vertical-align: middle;
        }
        .gh-badge.open { background: #dcfce7; color: #166534; }
        .gh-badge.closed { background: #fee2e2; color: #991b1b; }
        .gh-badge.merged { background: #f3e8ff; color: #6b21a8; }
        
        /* Roadmap Widget Horizontal */
        .roadmap-widget {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 32px;
            box-shadow: var(--shadow-sm);
            margin-top: 24px;
        }
        .roadmap-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 16px;
        }
        .roadmap-container {
            display: flex;
            position: relative;
            justify-content: space-between;
            align-items: flex-start;
        }
        .roadmap-container::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 5%;
            right: 5%;
            height: 2px;
            background: var(--border-color);
            z-index: 0;
        }
        .roadmap-phase {
            position: relative;
            flex: 1;
            text-align: center;
            padding: 0 16px;
            z-index: 1;
        }
        .phase-dot {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 4px solid var(--border-color);
            margin: 0 auto 16px auto;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--text-muted);
            box-shadow: 0 0 0 4px var(--card-bg);
        }
        .roadmap-phase.active .phase-dot {
            border-color: var(--accent-color);
            background: #eff6ff;
            color: var(--accent-color);
            box-shadow: 0 0 0 4px var(--card-bg), 0 0 0 8px rgba(37, 99, 235, 0.1);
        }
        body.dark-theme .roadmap-phase.active .phase-dot {
            background: rgba(37, 99, 235, 0.2);
        }
        .roadmap-phase.completed .phase-dot {
            border-color: var(--success-color);
            background: var(--success-color);
            color: white;
        }
        .phase-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .phase-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.4;
            margin-bottom: 12px;
            min-height: 40px;
        }
        .phase-progress-bar {
            height: 6px;
            background: var(--border-light);
            border-radius: 3px;
            overflow: hidden;
            width: 80%;
            margin: 0 auto;
        }
        .phase-progress-fill {
            height: 100%;
            background: var(--text-muted);
            transition: width 0.5s ease;
        }
        .roadmap-phase.completed .phase-progress-fill {
            background: var(--success-color);
        }
        .roadmap-phase.active .phase-progress-fill {
            background: var(--accent-color);
        }

        /* Online Users Widget */
        .online-widget {
            display: block;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 16px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }
        .online-widget:hover {
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .online-widget-header {
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            margin-bottom: 8px;
        }
        .online-widget-title {
            font-weight: 600; 
            font-size: 0.95rem;
        }
        .online-widget-count {
            background: var(--success-color); 
            color: white; 
            font-size: 0.75rem; 
            padding: 2px 8px; 
            border-radius: 9999px; 
            font-weight: 600;
        }
        .online-widget-footer {
            font-size: 0.8rem; 
            color: var(--text-muted); 
            display: flex; 
            align-items: center; 
            gap: 6px;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-muted);
            text-align: center;
        }

    </style>
</head>
<body>
<script>
    // Theme toggle logic - Execute immediately to prevent flash
    const savedTheme = localStorage.getItem('teamhub-theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark-theme');
        document.body.classList.add('dark-theme');
    }
</script>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="brand">
            TeamHub
            <button id="theme-toggle" class="btn" style="margin-left:auto; padding:6px; background:transparent; border:1px solid var(--border-color); color:var(--text-secondary);" title="Cambiar Tema">
                <!-- Icon will be injected by JS -->
                <svg id="theme-icon-light" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg id="theme-icon-dark" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>
        </div>
        
        <div class="user-profile">
            <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
            <div style="flex:1;">
                <div style="font-weight:600"><?= htmlspecialchars($username) ?></div>
                
                <form method="POST">
                    <input type="hidden" name="update_user_status" value="1">
                    <select name="new_user_status" class="status-select" onchange="this.form.submit()">
                        <option value="Oficina" <?= $userStatus == 'Oficina' ? 'selected' : '' ?>>Oficina</option>
                        <option value="Teletrabajo" <?= $userStatus == 'Teletrabajo' ? 'selected' : '' ?>>Teletrabajo</option>
                        <option value="Reunión" <?= $userStatus == 'Reunión' ? 'selected' : '' ?>>Reunión</option>
                        <option value="Desconectado" <?= $userStatus == 'Desconectado' ? 'selected' : '' ?>>Desconectado</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Gather Widget -->
        <div id="gather-presence-widget-container" style="margin-bottom: 20px;">
           <?php include __DIR__ . '/components/widget_online_users.html'; ?>
        </div>

        <div class="sidebar-section-title">Proyectos</div>
        
        <ul class="project-list">
            <?php foreach ($equipos as $equipo): ?>
                <li class="project-item">
                    <a href="?team_id=<?= $equipo['id'] ?>" class="project-link <?= $selected_team_id == $equipo['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($equipo['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <form method="POST">
            <button type="submit" name="logout" class="logout-btn">Cerrar Sesión</button>
        </form>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <?php if ($selected_team): ?>
            <div class="main-container">
                <div class="header" style="margin-bottom:0px;">
                    <div>
                        <h1 class="project-title"><?= htmlspecialchars($selected_team['name']) ?></h1>
                        
                        <?php 
                            $statusClass = str_replace(' ', '.', $selected_team['status'] ?? 'En.Progreso'); 
                        ?>
                        <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                            <span class="project-status status-<?= $statusClass ?>">
                                <?= htmlspecialchars($selected_team['status'] ?? 'En Progreso') ?>
                            </span>
                            
                            <?php if ($es_miembro): ?>
                                <span style="font-size:0.85rem; color:var(--text-secondary); font-weight:500;">Mismo equipo</span>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- Admin Action: Update Status -->
                <?php if ($user_role === 'admin'): ?>
                    <div class="status-modifier">
                        <!-- Manager Controls could go here, putting them in 'Actions' card instead for cleaner header -->
                    </div>
                <?php endif; ?>
            </div>

            <div class="content-grid">
                
                <!-- Left Column: Details & Actions -->
                <div style="display:flex; flex-direction:column; gap:24px;">
                    <div class="card">
                        <h3 class="card-title">Descripción del Proyecto</h3>
                        <div class="description-text">
                            <?= nl2br(htmlspecialchars($selected_team['description'])) ?>
                        </div>
                    </div>

                    <!-- Roadmap Widget -->
                    <div class="roadmap-widget" id="roadmap-widget" style="margin-top:0;">
                        <div class="roadmap-title" style="justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--accent-color)"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                Hoja de Ruta del Proyecto
                                <button id="btn-refresh-roadmap" class="btn btn-dark" style="margin-left: 10px; padding: 4px 10px; font-size: 0.75rem; background:#334155; border:none; display:flex; align-items:center; gap:4px; height: 26px;" title="Regenerar Hoja de Ruta">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                    Actualizar
                                </button>
                            </div>

                        </div>
                        <div id="roadmap-content">
                            <div class="gh-loader" style="padding: 40px 0;">
                                <div class="spinner" style="border-top-color:#8b5cf6; width:40px; height:40px; border-width:4px;"></div>
                                <br><span style="background: linear-gradient(90deg, #8b5cf6, #3b82f6); -webkit-background-clip: text; color: transparent; font-weight:600; font-size:1.1rem; display:inline-block; margin-top:16px;">Se esta generando la hoja de ruta</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($user_role === 'admin'): ?>
                        <div class="card">
                            <h3 class="card-title">Gestión del Proyecto (Manager)</h3>
                            
                            <form method="POST" class="status-form">
                                <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                                <div style="display:flex; align-items:center; gap:12px; flex-grow:1;">
                                    <label for="status" class="form-label" style="margin-bottom:0;">Estado:</label>
                                    <select name="new_status" id="status" class="form-select" style="max-width:200px;">
                                        <option value="En Progreso" <?= ($selected_team['status'] ?? '') == 'En Progreso' ? 'selected' : '' ?>>En Progreso</option>
                                        <option value="Completado" <?= ($selected_team['status'] ?? '') == 'Completado' ? 'selected' : '' ?>>Completado</option>
                                        <option value="Pausado" <?= ($selected_team['status'] ?? '') == 'Pausado' ? 'selected' : '' ?>>Pausado</option>
                                        <option value="Cancelado" <?= ($selected_team['status'] ?? '') == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-primary">Actualizar</button>
                            </form>

                            <hr style="border-top:1px solid var(--border-light); margin:24px 0;">

                            <h4 style="margin: 0 0 8px 0; color:var(--text-primary); font-size:0.95rem;">Repositorio Externo (GitHub)</h4>
                            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:12px;">Desarrollo en tiempo real. Formato: <code>usuario/repo</code></p>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                                <input type="hidden" name="update_github" value="1">
                                <input type="text" name="github_repo" class="form-input" placeholder="Ej. facebook/react" value="<?= htmlspecialchars($selected_team['github_repo'] ?? '') ?>">
                                <button type="submit" class="btn btn-dark">Guardar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Members & Join -->
                <div style="display:flex; flex-direction:column; gap:24px;">
                    
                    <!-- Join/Leave Actions -->
                    <div class="card">
                        <h3 class="card-title">Acceso y Participación</h3>
                        <form method="POST">
                            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                            <?php if ($es_miembro): ?>
                                <button type="submit" name="leave_team" class="btn btn-danger" style="width:100%">Abandonar Proyecto</button>
                            <?php else: ?>
                                <button type="submit" name="join_team" class="btn btn-primary" style="width:100%">Unirse al Proyecto</button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="card">
                        <h3 class="card-title">Miembros (<?= count($miembros) ?>)</h3>
                        <?php if (empty($miembros)): ?>
                            <p style="color:var(--text-muted); font-size: 0.9rem;">No hay miembros aún.</p>
                        <?php else: ?>
                            <div class="member-list">
                                <?php foreach ($miembros as $m): ?>
                                    <div class="member-item">
                                        <div class="member-info">
                                            <div class="user-avatar" style="width:28px; height:28px; font-size:0.75rem;">
                                                <?= strtoupper(substr($m['username'], 0, 1)) ?>
                                            </div>
                                            <div class="member-name">
                                                <?= htmlspecialchars($m['username']) ?>
                                                <?php $statusClass = str_replace(' ', '-', $m['status']); ?>
                                                <span class="status-dot user-status-<?= $statusClass ?>" title="<?= $m['status'] ?>"></span>
                                            </div>
                                        </div>
                                        <?php if ($m['role'] === 'admin'): ?>
                                            <span class="role-badge role-admin">Jefe</span>
                                        <?php else: ?>
                                            <span class="role-badge">Trabajador</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- GitHub Widget Display -->
                    <?php if (!empty($selected_team['github_repo'])): ?>
                    <div class="github-widget" data-repo="<?= htmlspecialchars($selected_team['github_repo']) ?>">
                        <div class="github-header">
                            <svg height="18" viewBox="0 0 16 16" version="1.1" width="18" aria-hidden="true" fill="currentColor"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
                            <a href="https://github.com/<?= htmlspecialchars($selected_team['github_repo']) ?>" target="_blank" style="color:var(--text-primary); text-decoration:none;"><?= htmlspecialchars($selected_team['github_repo']) ?></a>
                        </div>
                        <div class="github-tabs">
                            <div class="github-tab active" data-target="commits" style="display:flex; align-items:center; gap:8px;">
                                Commits
                                <select id="gh-branch-selector" style="margin:0; padding:2px 20px 2px 8px; font-size:0.75rem; display:none; max-width:140px; background-color: var(--bg-color); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 4px; outline: none; cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 6px top 50%; background-size: 8px auto;">
                                    <option value="">Cargando ramas...</option>
                                </select>
                            </div>
                            <div class="github-tab" data-target="pulls">Pull Requests</div>
                            <div class="github-tab" data-target="issues">Issues</div>
                        </div>
                        <div class="github-content" id="gh-content-box">
                            <!-- JS Inject -->
                            <div class="gh-loader"><div class="spinner"></div><br>Cargando datos...</div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

            </div> <!-- END content-grid -->

            </div> <!-- END main-container -->

        <?php else: ?>
            <div style="display:flex; justify-content:center; align-items:center; height:100%; color:#555;">
                <h2>Selecciona un proyecto de la izquierda para ver detalles</h2>
            </div>
        <?php endif; ?>
    </div>

<script src="js/heartbeat.js"></script>

<!-- GitHub Widget Logic -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ghWidget = document.querySelector('.github-widget');
    if (!ghWidget) return;

    const repo = ghWidget.dataset.repo;
    const tabs = document.querySelectorAll('.github-tab');
    const contentBox = document.getElementById('gh-content-box');
    const branchSelect = document.getElementById('gh-branch-selector');
    let loadedBranches = false;

    // Helper to fetch and populate branches
    const loadBranchesDropdown = async () => {
        if (loadedBranches) return;
        try {
            const res = await fetch(`../endpoints/github_proxy.php?action=branches&repo=${repo}`);
            if (!res.ok) throw new Error('API Error');
            const data = await res.json();
            if (Array.isArray(data) && data.length > 0) {
                let html = '';
                data.forEach(b => {
                    const isMain = b.name === 'main' || b.name === 'master';
                    html += `<option value="${b.name}" ${isMain ? 'selected' : ''}>${b.name}</option>`;
                });
                branchSelect.innerHTML = html;
                branchSelect.style.display = 'inline-block';
                loadedBranches = true;
                
                // If the user hasn't switched away from commits tab yet, trigger load again with specific branch
                if (document.querySelector('.github-tab[data-target="commits"]').classList.contains('active')) {
                    loadGitHubData('commits', branchSelect.value);
                }
            } else {
                 branchSelect.style.display = 'none';
            }
        } catch(e) {
            branchSelect.style.display = 'none';
        }
    };

    const loadGitHubData = async (action, branch = null) => {
        contentBox.innerHTML = '<div class="gh-loader"><div class="spinner"></div><br>Cargando...</div>';
        try {
            let url = `../endpoints/github_proxy.php?action=${action}&repo=${repo}`;
            if (action === 'commits' && branch) {
                url += `&branch=${encodeURIComponent(branch)}`;
            }

            const res = await fetch(url);
            if (!res.ok) throw new Error('API Error');
            const data = await res.json();
            
            if (!Array.isArray(data) || data.length === 0) {
                contentBox.innerHTML = '<div class="empty-state"><span>No hay elementos recientes</span></div>';
                return;
            }

            let html = '';
            data.forEach(item => {
                if (action === 'commits') {
                    const msg = item.commit.message.split('\n')[0];
                    const author = item.commit.author.name;
                    const date = new Date(item.commit.author.date).toLocaleDateString(undefined, {month: 'short', day: 'numeric'});
                    html += `
                        <div class="gh-item">
                            <a href="${item.html_url}" target="_blank" class="gh-title">${msg}</a>
                            <div class="gh-meta">Commit por <span style="font-weight:500;color:var(--text-secondary)">${author}</span> el ${date}</div>
                        </div>`;
                } else if (action === 'pulls') {
                    const title = item.title;
                    const user = item.user.login;
                    const stateBadge = item.state === 'open' ? '<span class="gh-badge open">Abierto</span>' : '<span class="gh-badge merged">Fusionado</span>';
                    html += `
                        <div class="gh-item">
                            <a href="${item.html_url}" target="_blank" class="gh-title">${title}</a>
                            <div class="gh-meta">${stateBadge} #${item.number} por ${user}</div>
                        </div>`;
                } else if (action === 'issues') {
                    if (item.pull_request) return; // GitHub includes PRs in issues endpoint
                    const title = item.title;
                    const user = item.user.login;
                    const stateBadge = item.state === 'open' ? '<span class="gh-badge open">Abierto</span>' : '<span class="gh-badge closed">Cerrado</span>';
                    html += `
                        <div class="gh-item">
                            <a href="${item.html_url}" target="_blank" class="gh-title">${title}</a>
                            <div class="gh-meta">${stateBadge} #${item.number} por ${user}</div>
                        </div>`;
                } else if (action === 'branches') {
                    const name = item.name;
                    html += `
                        <div class="gh-item" style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div class="gh-title" style="margin-bottom:0;">
                                    <svg viewBox="0 0 16 16" width="14" height="14" style="fill: currentColor; vertical-align: middle; margin-right: 4px;"><path fill-rule="evenodd" d="M11.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122V6A2.5 2.5 0 0110 8.5H6a1 1 0 00-1 1v1.128a2.251 2.251 0 11-1.5 0V5.372a2.25 2.25 0 111.5 0v1.836A2.492 2.492 0 016 7h4a1 1 0 001-1v-1.378A2.25 2.25 0 019.5 3.25zM4.25 12a.75.75 0 100 1.5.75.75 0 000-1.5zM3.5 3.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0z"></path></svg>
                                    ${name}
                                </div>
                            </div>
                        </div>`;
                }
            });
            contentBox.innerHTML = html;
        } catch (err) {
            contentBox.innerHTML = '<div class="gh-loader" style="color:#ff5252;">Error o Repositorio no encontrado (o privado).</div>';
        }
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            // Stop propagation if click is on the dropdown
            if (e.target.tagName === 'SELECT' || e.target.tagName === 'OPTION') return;
            
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            const target = tab.dataset.target;
            if (target === 'commits' && branchSelect && branchSelect.style.display !== 'none') {
                loadGitHubData(target, branchSelect.value);
            } else {
                loadGitHubData(target);
            }
        });
    });

    if (branchSelect) {
        branchSelect.addEventListener('change', () => {
             loadGitHubData('commits', branchSelect.value);
        });
    }

    // Load initial data
    loadGitHubData('commits');
    loadBranchesDropdown();
});

// Roadmap Logic
let roadmapIsUnloading = false;
window.addEventListener('beforeunload', () => { roadmapIsUnloading = true; });

document.addEventListener('DOMContentLoaded', async () => {
    const roadmapWidget = document.getElementById('roadmap-widget');
    if (!roadmapWidget) return;
    
    const teamId = <?= json_encode($selected_team['id'] ?? null) ?>;
    if (!teamId) return;

    const contentBox = document.getElementById('roadmap-content');
    const refreshBtn = document.getElementById('btn-refresh-roadmap');
    
    const fetchRoadmap = async (forceRefresh = false) => {
        contentBox.innerHTML = `
            <div class="gh-loader" style="padding: 40px 0;">
                <div class="spinner" style="border-top-color:#8b5cf6; width:40px; height:40px; border-width:4px;"></div>
                <br><span style="background: linear-gradient(90deg, #8b5cf6, #3b82f6); -webkit-background-clip: text; color: transparent; font-weight:600; font-size:1.1rem; display:inline-block; margin-top:16px;">Se esta generando la hoja de ruta</span>
            </div>
        `;
        if (refreshBtn) refreshBtn.disabled = true;

        try {
            const endpoint = forceRefresh ? `../endpoints/roadmap_generator.php?team_id=${teamId}&force_refresh=true` : `../endpoints/roadmap_generator.php?team_id=${teamId}`;
            const res = await fetch(endpoint);
        if (!res.ok) {
            const errText = await res.text();
            throw new Error(`HTTP ${res.status}: ${errText}`);
        }
        const data = await res.json();
        
        if (data.error) throw new Error(data.error);
        
        const roadmap = data.roadmap;

        let html = '<div class="roadmap-container">';
        
        let hasActivePhase = false;

        // Render phases
        Object.keys(roadmap).forEach((key, index) => {
            const phase = roadmap[key];
            let statusClass = '';
            
            if (phase.completado) {
                statusClass = 'completed';
            } else if (!hasActivePhase && phase.avance > 0) {
                statusClass = 'active';
                hasActivePhase = true;
            } else if (!hasActivePhase && phase.avance === 0) {
                 // The first one that is 0 and not passed an active phase becomes the active phase visually if previous was 100%
                 statusClass = 'active';
                 hasActivePhase = true;
            }

            html += `
                <div class="roadmap-phase ${statusClass}">
                    <div class="phase-dot">${phase.completado ? '✓' : index + 1}</div>
                    <div class="phase-content">
                        <div class="phase-title">${phase.nombre}</div>
                        <div class="phase-desc">${phase.desc}</div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div class="phase-progress-bar">
                                <div class="phase-progress-fill" style="width: ${phase.avance}%"></div>
                            </div>
                            <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">${phase.avance}%</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        // Add stats footer if GitHub is connected
        if (data.github) {
            html += `
            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed var(--border-color); display:flex; gap:16px; font-size:0.8rem;">
                 <div style="color:var(--text-secondary)"><strong style="color:var(--text-primary)">${data.github.commits}</strong> Commits Recientes</div>
                 <div style="color:var(--text-secondary)"><strong style="color:var(--text-primary)">${data.github.prs_closed}</strong> PRs Cerrados</div>
                 ${data.github.active ? '<div style="color:var(--success-color); font-weight:600;">Proyecto Activo</div>' : ''}
            </div>`;
        }
        
        contentBox.innerHTML = html;
        if (refreshBtn) refreshBtn.disabled = false;
        
    } catch(err) {
        if (!roadmapIsUnloading) {
            contentBox.innerHTML = `<div class="empty-state">No se pudo cargar la hoja de ruta.<br><small style="color:red; font-size:0.8rem; margin-top:8px; display:inline-block;">${err.message}</small></div>`;
        }
        if (refreshBtn) refreshBtn.disabled = false;
    }
    }; // End fetchRoadmap

    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            fetchRoadmap(true);
        });
    }

    // Initial Load
    fetchRoadmap(false);
});

// Theme Toggle Functionality
document.addEventListener('DOMContentLoaded', () => {
    const themeBtn = document.getElementById('theme-toggle');
    const iconLight = document.getElementById('theme-icon-light');
    const iconDark = document.getElementById('theme-icon-dark');
    
    function updateIcon() {
        if (document.body.classList.contains('dark-theme')) {
            iconLight.style.display = 'block';
            iconDark.style.display = 'none';
        } else {
            iconLight.style.display = 'none';
            iconDark.style.display = 'block';
        }
    }
    
    // Initial icon state
    updateIcon();
    
    themeBtn.addEventListener('click', () => {
        document.body.classList.toggle('dark-theme');
        document.documentElement.classList.toggle('dark-theme');
        
        const isDark = document.body.classList.contains('dark-theme');
        localStorage.setItem('teamhub-theme', isDark ? 'dark' : 'light');
        
        updateIcon();
    });
});
</script>

</body>
</html>
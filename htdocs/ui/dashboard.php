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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { base: '#F2F0E9', neon: '#CCFF00', neonSec: '#00FF00' }, fontFamily: { serif: ['Times New Roman', 'Georgia', 'serif'], mono: ['Courier New', 'Courier', 'monospace'] } } }
        }
    </script>
    <style>
        :root {

            /* Neo-Brutalist Color Palette */
            --bg-color: #F2F0E9;
            --sidebar-bg: #F2F0E9;
            --card-bg: #ffffff;
            --text-primary: #000000;
            --text-secondary: #000000;
            --text-muted: #444444;
            --accent-color: #CCFF00;
            --accent-hover: #00FF00;
            --danger-color: #ff0000;
            --success-color: #00FF00;
            --warning-color: #CCFF00;
            --border-color: #000000;
            --border-light: #000000;
            --shadow-sm: 4px 4px 0px 0px rgba(0,0,0,1);
            --shadow-md: 8px 8px 0px 0px rgba(0,0,0,1);
            --shadow-lg: 8px 8px 0px 0px rgba(0,0,0,1);
            --radius-md: 0px;
            --radius-lg: 0px;
            --font-family: 'Courier New', Courier, monospace;
        }

        /* Dark Theme Override (Neo-Brutalist High Contrast Inverted - Softened) */
        body.dark-theme {
            --bg-color: #050505;
            --sidebar-bg: #050505;
            --card-bg: #050505;
            --text-primary: #a8dba8; /* Soft green */
            --text-secondary: #e6e6e6;
            --text-muted: #888888;
            --border-color: #a8dba8;
            --border-light: #a8dba8;
            --shadow-sm: 4px 4px 0px 0px #a8dba8;
            --shadow-md: 8px 8px 0px 0px #a8dba8;
            --accent-color: #79c753; /* Slightly darker green for accents */
            --accent-hover: #ffffff;
            --danger-color: #d9534f;
            --success-color: #79c753;
            --warning-color: #f0ad4e;
        }

        /* Brutalist Global Overrides */
        body {
            background-image: linear-gradient(to right, rgba(0,0,0,0.1) 1px, transparent 1px), linear-gradient(to bottom, rgba(0,0,0,0.1) 1px, transparent 1px) !important;
            background-size: 20px 20px !important;
            color: var(--text-primary);
        }
        body.dark-theme {
            background-image: linear-gradient(to right, rgba(168,219,168,0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(168,219,168,0.15) 1px, transparent 1px) !important;
        }

        .card, .sidebar, .btn, .form-input, .form-select, .github-widget, .roadmap-widget, .status-select, .online-widget, .user-profile, .brand {
            border: 2px solid var(--border-color) !important;
            box-shadow: var(--shadow-sm) !important;
            transition: none !important;
        }
        .sidebar { box-shadow: 4px 0px 0px 0px var(--border-color) !important; border-right: 4px solid var(--border-color) !important; }
        
        .btn:active, .online-widget:active, .project-link:active {
            transform: translate(2px, 2px) !important;
            box-shadow: none !important;
        }
        .btn-primary {
            background: #000000 !important;
            color: #CCFF00 !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
        }
        body.dark-theme .btn-primary {
            background: var(--text-primary) !important;
            color: #000000 !important;
        }
        .btn-primary:hover {
            background: #CCFF00 !important;
            color: #000000 !important;
        }
        body.dark-theme .btn-primary:hover {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .project-link { border: 2px solid transparent !important; }
        .project-link.active {
            background-color: var(--accent-color) !important;
            color: #000000 !important;
            border: 2px solid var(--border-color) !important;
            box-shadow: 2px 2px 0px 0px var(--border-color) !important;
        }
        body.dark-theme .project-link.active {
            box-shadow: 2px 2px 0px 0px var(--text-primary) !important;
            background-color: var(--text-primary) !important;
        }
        .user-avatar {
            border: 2px solid var(--border-color) !important;
            box-shadow: 2px 2px 0px 0px var(--border-color) !important;
            border-radius: 0 !important;
            background: var(--text-primary) !important;
            color: var(--bg-color) !important;
            overflow: hidden !important;
            padding: 0 !important;
        }
        .project-title { font-family: 'Times New Roman', Georgia, serif !important; font-weight: 900 !important; text-transform: uppercase !important; }
        
        * { cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='white' stroke='black' stroke-width='1.5' d='M0,0 L0,18 L6,12 L9,19.5 L12,18 L9,10.5 L15,10.5 Z'/%3E%3C/svg%3E") 0 0, auto; }
        a, button, [role="button"], .cursor-pointer, .project-link, .tab, .btn, select, .gh-title, .status-select { cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='white' stroke='black' stroke-width='1.5' d='M12,0 L12,7.5 L16.5,7.5 L16.5,10.5 L12,10.5 L12,18 L9,18 L9,10.5 L4.5,10.5 L4.5,7.5 L9,7.5 L9,0 Z M0,9 L4.5,9 L4.5,12 L0,12 Z M16.5,9 L21,9 L21,12 L16.5,12 Z'/%3E%3C/svg%3E") 12 12, pointer !important; }
        input, textarea { cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='24' viewBox='0 0 12 24'%3E%3Crect x='4.5' y='0' width='3' height='24' fill='black'/%3E%3Crect x='0' y='0' width='12' height='3' fill='black'/%3E%3Crect x='0' y='21' width='12' height='3' fill='black'/%3E%3C/svg%3E") 6 12, text !important; }

        /* End Neo-Brutalist */

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
            font-family: 'Inter', sans-serif;
            margin: 0;
            height: 100vh;
            display: grid;
            grid-template-columns: 280px 1fr; /* Sidebar | Main */
            background-color: var(--bg-color);
            color: var(--text-primary);
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .status-select {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid #444;
            border-radius: 4px;
            padding: 2px 5px;
            font-size: 0.8rem;
            margin-top: 5px;
            width: 100%;
            cursor: pointer;
        }
        
        .status-select:hover {
            border-color: #666;
            color: var(--text-primary);
        }

        .project-list {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .project-item {
            margin-bottom: 5px;
        }

        .project-link {
            display: block;
            padding: 12px 15px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .project-link:hover {
            background-color: rgba(255,255,255,0.05);
            color: var(--text-primary);
        }

        .project-link.active {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--accent-color);
            border-left-color: var(--accent-color);
        }

        .logout-btn {
            margin-top: auto;
            background: none;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
            width: 100%;
        }

        .logout-btn:hover {
            background: var(--danger-color);
            color: white;
        }

        /* Main Content Styles */
        .main-content {
            padding: 40px;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .project-title {
            font-size: 2rem;
            margin: 0 0 10px 0;
        }

        .project-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-En.Progreso { background: rgba(33, 150, 243, 0.2); color: #64B5F6; }
        .status-Completado { background: rgba(76, 175, 80, 0.2); color: #81C784; }
        .status-Pausado { background: rgba(255, 193, 7, 0.2); color: #FFD54F; }
        .status-Cancelado { background: rgba(244, 67, 54, 0.2); color: #E57373; }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.1rem;
            color: var(--text-secondary);
        }

        .description-text {
            line-height: 1.6;
            color: #d0d0d0;
        }

        /* Members List */
        .member-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .member-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .role-badge {
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 4px;
            background: #333;
            color: #aaa;
        }

        .role-admin {
            background: rgba(255, 152, 0, 0.2);
            color: #FFB74D;
        }
        
        /* User Status Dot */
        .status-dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .user-status-Oficina { background-color: #4CAF50; }
        .user-status-Teletrabajo { background-color: #2196F3; }
        .user-status-Reunión { background-color: #FFC107; }
        .user-status-Ausente { background-color: #FF5722; }
        .user-status-Desconectado { background-color: #9E9E9E; }
        .user-status-En-Gather { background-color: #9C27B0; box-shadow: 0 0 5px #9C27B0; }



        /* Action Buttons */
        .action-area {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-primary { background: var(--accent-color); color: white; }
        .btn-primary:hover { background: #1976D2; }

        .btn-danger { background: var(--danger-color); color: white; }
        .btn-danger:hover { background: #D32F2F; }

        /* Status Select Form */
        .status-form {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0,0,0,0.2);
            padding: 10px;
            border-radius: 8px;
            margin-top: 20px;
        }

        select {
            background: #333;
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
            color: black; 
            font-size: 0.75rem; 
            padding: 2px 8px; 
            border: 2px solid black;
            border-radius: 0px; 
            font-weight: 800;
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

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="brand">TeamHub</div>
        
        <div class="user-profile" style="margin-bottom: 24px;">
            <div class="user-avatar" style="width: 48px; height: 48px; border-radius: 0; padding: 0;">
                <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?= urlencode($username) ?>" style="width:100%; height:100%; object-fit:cover;" alt="Avatar">
            </div>
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

        <div style="margin-bottom:10px; font-weight:600; color:var(--text-secondary); font-size:0.9rem;">PROYECTOS</div>
        
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
            <div class="header">
                <div>
                    <h1 class="project-title"><?= htmlspecialchars($selected_team['name']) ?></h1>
                    
                    <?php 
                        $statusClass = str_replace(' ', '.', $selected_team['status'] ?? 'En.Progreso'); 
                    ?>
                    <span class="project-status status-<?= $statusClass ?>">
                        <?= htmlspecialchars($selected_team['status'] ?? 'En Progreso') ?>
                    </span>
                    
                    <?php if ($es_miembro): ?>
                        <span style="font-size:0.9rem; color:#4CAF50; margin-left:10px;">Eres miembro</span>
                    <?php endif; ?>
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
                <div style="display:flex; flex-direction:column; gap:30px;">
                    <div class="card">
                        <h3>Descripción del Proyecto</h3>
                        <div class="description-text">
                            <?= nl2br(htmlspecialchars($selected_team['description'])) ?>
                        </div>
                    </div>

                    <?php if ($user_role === 'admin'): ?>
                        <div class="card" style="border-color: #444;">
                            <h3 style="color:var(--accent-color);">Gestión del Proyecto (Manager)</h3>
                            <p style="font-size:0.9rem; color:#aaa;">Como jefe de proyecto, puedes cambiar el estado actual.</p>
                            
                            <form method="POST" class="status-form">
                                <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                                <label for="status">Estado:</label>
                                <select name="new_status" id="status">
                                    <option value="En Progreso" <?= ($selected_team['status'] ?? '') == 'En Progreso' ? 'selected' : '' ?>>En Progreso</option>
                                    <option value="Completado" <?= ($selected_team['status'] ?? '') == 'Completado' ? 'selected' : '' ?>>Completado</option>
                                    <option value="Pausado" <?= ($selected_team['status'] ?? '') == 'Pausado' ? 'selected' : '' ?>>Pausado</option>
                                    <option value="Cancelado" <?= ($selected_team['status'] ?? '') == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary" style="padding:8px 15px;">Actualizar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Members & Join -->
                <div style="display:flex; flex-direction:column; gap:30px;">
                    
                    <!-- Join/Leave Actions -->
                    <div class="card">
                        <h3>Acciones</h3>
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
                        <h3>👥 Miembros (<?= count($miembros) ?>)</h3>
                        <?php if (empty($miembros)): ?>
                            <p style="color:#666; font-style:italic;">No hay miembros aún.</p>
                        <?php else: ?>
                            <div class="member-list">
                                <?php foreach ($miembros as $m): ?>
                                    <div class="member-item">
                                        <div class="member-info">
                                            <div class="user-avatar" style="width:36px; height:36px; padding:0; overflow:hidden;">
                                                <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?= urlencode($m['username']) ?>" style="width:100%; height:100%; object-fit:cover;" alt="Avatar">
                                            </div>
                                            <div>
                                                <span><?= htmlspecialchars($m['username']) ?></span>
                                                <!-- Status Dot for Members -->
                                                <?php $statusClass = str_replace(' ', '-', $m['status']); ?>
                                                <span class="status-dot user-status-<?= $statusClass ?>" title="<?= $m['status'] ?>" style="margin-left:5px;"></span>
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
                </div>

            </div>

        <?php else: ?>
            <div style="display:flex; justify-content:center; align-items:center; height:100%; color:#555;">
                <h2>Selecciona un proyecto de la izquierda para ver detalles</h2>
            </div>
        <?php endif; ?>
    </div>

<script src="js/heartbeat.js"></script>
</body>
</html>
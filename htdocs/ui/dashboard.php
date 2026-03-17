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
        if (isset($_SESSION['user_id'])) $consultas->limpiarToken($_SESSION['user_id']);
        if (isset($_COOKIE['teamhub_remember'])) {
            setcookie('teamhub_remember', '', time() - 3600, '/');
            unset($_COOKIE['teamhub_remember']);
        }
        session_destroy();
        header("Location: login.php");
        exit;
    }
    if (isset($_POST['update_user_status'])) {
        $consultas->actualizarEstado($user_id, $_POST['new_user_status']);
        $params = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
        header("Location: dashboard.php" . $params);
        exit;
    }
    if (isset($_POST['update_status']) && isset($_POST['team_id']) && isset($_POST['new_status'])) {
        $role = $consultas->obtenerRolUsuario($user_id, $_POST['team_id']);
        if ($role === 'admin') $consultas->actualizarEstadoEquipo($_POST['team_id'], $_POST['new_status']);
    }
    if (isset($_POST['join_team'])) {
        $consultas->unirseEquipo($user_id, $_POST['team_id']);
    } elseif (isset($_POST['leave_team'])) {
        $consultas->salirEquipo($user_id, $_POST['team_id']);
    }
    // Update GitHub Repo (Admin Only)
    if (isset($_POST['update_github']) && isset($_POST['team_id'])) {
        $role = $consultas->obtenerRolUsuario($user_id, $_POST['team_id']);
        if ($role === 'admin') {
            $repo = trim($_POST['github_repo']);
            if (empty($repo)) $repo = null;
            $consultas->vincularGitHub($_POST['team_id'], $repo);
        }
    }
    $redirect_team = isset($_POST['team_id']) ? "?team_id=" . $_POST['team_id'] : "";
    header("Location: dashboard.php" . $redirect_team);
    exit;
}

// Data
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamHub | Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #EBE8E6;
            color: #000000;
            height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
            overflow: hidden;
            --bg: #EBE8E6;
            --surface: #ffffff;
            --border: #d4d0cd;
            --text: #000000;
            --text-muted: #4a4a4a;
            --text-faint: #7a7a7a;
            --accent: #052DD4;
            --accent-alt: #4FC59F;
            --lime: #CAFB04;
            --sidebar-bg: rgba(255,255,255,0.85);
            --card-hover-border: #b0acaa;
            --divider: #e8e4e1;
        }
        body.dark {
            background: #000000;
            color: #EBE8E6;
            --bg: #000000;
            --surface: #111111;
            --border: #2a2a2a;
            --text: #EBE8E6;
            --text-muted: #a0a0a0;
            --text-faint: #666666;
            --accent: #052DD4;
            --accent-alt: #4FC59F;
            --lime: #CAFB04;
            --sidebar-bg: rgba(17,17,17,0.9);
            --card-hover-border: #3a3a3a;
            --divider: #1a1a1a;
        }

        /* ---------- SIDEBAR ---------- */
        .sidebar {
            background: var(--sidebar-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 20px 16px;
            overflow-y: auto;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            margin-bottom: 20px;
        }
        .brand-icon {
            width: 32px; height: 32px;
            background: #052DD4;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .brand-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .user-avatar-wrap { position: relative; flex-shrink: 0; }
        .user-avatar-img { width: 36px; height: 36px; border-radius: 10px; }
        .user-status-pip {
            position: absolute;
            bottom: -1px; right: -1px;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #4FC59F;
            border: 2px solid white;
        }
        .user-name { font-size: 0.875rem; font-weight: 600; color: var(--text); }
        .user-status-select {
            font-size: 0.75rem;
            color: #71717a;
            background: none;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 0;
            width: 100%;
            margin-top: 2px;
        }

        /* Section label */
        .section-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-faint);
            padding: 0 10px;
            margin-bottom: 6px;
            margin-top: 8px;
        }

        /* Project nav */
        .project-list { list-style: none; flex: 1; overflow-y: auto; }
        .project-item { margin-bottom: 2px; }
        .project-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted);
            transition: background 0.15s, color 0.15s;
        }
        .project-link:hover { background: var(--divider); color: var(--text); }
        .project-link.active { background: rgba(5,45,212,0.08); color: #052DD4; font-weight: 600; }

        /* Logout */
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 10px 12px;
            margin-top: auto;
            background: none;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            transition: border-color 0.15s, color 0.15s, background 0.15s;
        }
        .logout-btn:hover { border-color: #CAFB04; color: #000000; background: rgba(202,251,4,0.12); }

        /* ---------- MAIN CONTENT ---------- */
        .main-content {
            padding: 32px;
            overflow-y: auto;
        }

        /* Header */
        .content-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e4e4e7;
            gap: 16px;
        }
        .project-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.03em;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-green { background: rgba(79,197,159,0.15); color: #2a8a6e; border: 1px solid rgba(79,197,159,0.3); }
        .badge-blue  { background: rgba(5,45,212,0.1); color: #052DD4; border: 1px solid rgba(5,45,212,0.2); }
        .badge-amber { background: rgba(202,251,4,0.15); color: #7a8a00; border: 1px solid rgba(202,251,4,0.3); }
        .badge-red   { background: rgba(239,68,68,0.1);  color: #dc2626; border: 1px solid rgba(239,68,68,0.2); }
        .badge-zinc  { background: rgba(0,0,0,0.06); color: #4a4a4a; border: 1px solid rgba(0,0,0,0.12); }

        /* Bento Grid */
        .bento-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .bento-col { display: flex; flex-direction: column; gap: 20px; }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.03), 0 2px 4px rgba(0,0,0,0.04), 0 8px 20px rgba(0,0,0,0.04);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .card:hover { border-color: var(--card-hover-border); }
        .card-title {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--text-muted);
            margin-bottom: 16px;
        }
        .card-body { font-size: 0.9rem; color: var(--text-muted); line-height: 1.65; }

        /* Members */
        .member-list { display: flex; flex-direction: column; gap: 12px; }
        .member-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--divider);
        }
        .member-item:last-child { border-bottom: none; padding-bottom: 0; }
        .member-info { display: flex; align-items: center; gap: 10px; }
        .member-avatar { width: 30px; height: 30px; border-radius: 8px; }
        .member-name { font-size: 0.875rem; font-weight: 500; color: var(--text); }
        .role-tag {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .role-admin { background: rgba(202,251,4,0.2); color: #5a6600; border: 1px solid rgba(202,251,4,0.4); }
        .role-member { background: rgba(0,0,0,0.05); color: var(--text-muted); border: 1px solid var(--border); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 20px;
            border: none;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
        }
        .btn:active { transform: scale(0.96); }
        .btn-primary { background: #000000; color: white; }
        .btn-primary:hover { background: #1a1a1a; }
        .btn-brand { background: #052DD4; color: white; }
        .btn-brand:hover { background: #0424a8; }
        .btn-danger { background: white; color: #ef4444; border: 1px solid #fca5a5; }
        .btn-danger:hover { background: #fef2f2; }
        .btn-full { width: 100%; }

        /* Status select in form */
        .status-form { display: flex; align-items: center; gap: 10px; margin-top: 12px; }
        .form-select {
            flex: 1;
            padding: 9px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.875rem;
            color: var(--text);
            background: var(--surface);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-select:focus { border-color: #052DD4; box-shadow: 0 0 0 3px rgba(5,45,212,0.1); }

        /* Online widget */
        .online-widget-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            padding: 16px 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 12px;
        }
        .online-widget-link:hover { border-color: rgba(5,45,212,0.3); box-shadow: 0 4px 12px rgba(5,45,212,0.08); }
        .online-widget-left { display: flex; align-items: center; gap: 10px; }
        .online-pulse {
            width: 8px; height: 8px;
            background: #4FC59F;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity:1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.85); }
        }
        .online-widget-title { font-size: 0.875rem; font-weight: 600; color: var(--text); }
        .online-count-badge {
            background: rgba(5,45,212,0.08);
            color: #052DD4;
            border: 1px solid rgba(5,45,212,0.2);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 2px 10px;
        }
        .online-widget-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }

        /* Dark mode toggle */
        .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 10px;
            margin-bottom: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted);
        }
        .toggle-track {
            width: 36px; height: 20px;
            background: #e4e4e7;
            border-radius: 999px;
            position: relative;
            cursor: pointer;
            transition: background 0.2s;
            border: none;
            outline: none;
        }
        .toggle-track.on { background: #052DD4; }
        .toggle-thumb {
            position: absolute;
            top: 2px; left: 2px;
            width: 16px; height: 16px;
            background: white;
            border-radius: 50%;
            transition: transform 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
        .toggle-track.on .toggle-thumb { transform: translateX(16px); }
        body.dark .main-content { background: #000000; }

        .member-status-dot { width: 6px; height: 6px; border-radius: 50%; display:inline-block; margin-left: 4px; }
        .user-status-Oficina { background:#4FC59F; }
        .user-status-Teletrabajo { background:#052DD4; }
        .user-status-Reunión, .user-status-Reunion { background:#CAFB04; }
        .user-status-Ausente { background:#f97316; }
        .user-status-Desconectado { background:#888888; }

                /* Roadmap */
                .roadmap-container {
                    display: flex;
                    position: relative;
                    justify-content: space-between;
                    align-items: flex-start;
                    padding-top: 8px;
                }
                .roadmap-container::before {
                    content: '';
                    position: absolute;
                    top: 33px;
                    left: 5%; right: 5%;
                    height: 2px;
                    background: var(--border);
                    z-index: 0;
                }
                .roadmap-phase { position:relative; flex:1; text-align:center; padding:0 12px; z-index:1; }
                .phase-dot {
                    width: 44px; height: 44px;
                    border-radius: 50%;
                    background: var(--surface);
                    border: 3px solid var(--border);
                    margin: 0 auto 12px;
                    display: flex; align-items:center; justify-content:center;
                    font-weight: 700; font-size: 0.85rem;
                    color: var(--text-muted);
                    box-shadow: 0 0 0 4px var(--surface);
                    transition: all 0.3s;
                }
                .roadmap-phase.active .phase-dot { border-color:#052DD4; background:rgba(5,45,212,0.08); color:#052DD4; box-shadow:0 0 0 4px var(--surface), 0 0 0 8px rgba(5,45,212,0.12); }
                .roadmap-phase.completed .phase-dot { border-color:#4FC59F; background:#4FC59F; color:white; }
                .phase-title { font-weight:600; font-size:0.85rem; color:var(--text); margin-bottom:4px; }
                .phase-desc { font-size:0.75rem; color:var(--text-muted); line-height:1.4; margin-bottom:8px; min-height:36px; }
                .phase-progress-bar { height:4px; background:var(--border); border-radius:2px; overflow:hidden; width:80%; margin:0 auto; }
                .phase-progress-fill { height:100%; background:var(--text-muted); transition:width 0.5s ease; }
                .roadmap-phase.completed .phase-progress-fill { background:#4FC59F; }
                .roadmap-phase.active .phase-progress-fill { background:#052DD4; }

                /* GitHub widget */
                .github-widget { margin-top:0; border-radius:16px; border:1px solid var(--border); overflow:hidden; background:var(--surface); }
                .github-header { background:var(--surface); padding:14px 20px; font-weight:600; font-size:0.85rem; display:flex; align-items:center; gap:8px; color:var(--text); border-bottom:1px solid var(--border); }
                .github-tabs { display:flex; background:var(--surface); border-bottom:1px solid var(--border); }
                .github-tab { padding:10px 18px; cursor:pointer; color:var(--text-muted); font-size:0.82rem; font-weight:500; transition:0.15s; border-bottom:2px solid transparent; }
                .github-tab.active { color:#052DD4; border-bottom-color:#052DD4; }
                .github-tab:hover:not(.active) { color:var(--text); background:var(--divider); }
                .github-content { max-height:280px; overflow-y:auto; background:var(--surface); }
                .gh-item { padding:14px 20px; border-bottom:1px solid var(--divider); transition:background 0.15s; }
                .gh-item:hover { background:var(--divider); }
                .gh-item:last-child { border-bottom:none; }
                .gh-title { font-weight:500; font-size:0.85rem; color:var(--text); text-decoration:none; display:block; margin-bottom:3px; }
                .gh-title:hover { color:#052DD4; }
                .gh-meta { font-size:0.75rem; color:var(--text-muted); }
                .gh-loader { text-align:center; color:var(--text-muted); padding:24px; font-size:0.875rem; }
                .gh-badge { display:inline-flex; font-size:0.7rem; padding:2px 6px; border-radius:4px; font-weight:600; text-transform:uppercase; margin-right:5px; vertical-align:middle; }
                .gh-badge.open { background:rgba(79,197,159,0.2); color:#1a6b4a; }
                .gh-badge.closed { background:#fee2e2; color:#991b1b; }
                .gh-badge.merged { background:rgba(5,45,212,0.1); color:#052DD4; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
            </div>
            <span class="brand-name">TeamHub</span>
        </div>

        <!-- User Profile -->
        <div class="user-profile">
            <div class="user-avatar-wrap">
                <img src="https://api.dicebear.com/7.x/thumbs/svg?seed=<?= urlencode($username) ?>&backgroundColor=b6e3f4,c0aede,d1d4f9&radius=12"
                     class="user-avatar-img" alt="Avatar">
                <span class="user-status-pip"></span>
            </div>
            <div style="flex:1; min-width:0;">
                <div class="user-name"><?= htmlspecialchars($username) ?></div>
                <form method="POST">
                    <input type="hidden" name="update_user_status" value="1">
                    <select name="new_user_status" class="user-status-select" onchange="this.form.submit()">
                        <option value="Oficina" <?= $userStatus == 'Oficina' ? 'selected' : '' ?>>Oficina</option>
                        <option value="Teletrabajo" <?= $userStatus == 'Teletrabajo' ? 'selected' : '' ?>>Teletrabajo</option>
                        <option value="Reunión" <?= $userStatus == 'Reunión' ? 'selected' : '' ?>>Reunión</option>
                        <option value="Desconectado" <?= $userStatus == 'Desconectado' ? 'selected' : '' ?>>Desconectado</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Online users widget -->
        <a href="online_users.php" class="online-widget-link">
            <div class="online-widget-left">
                <span class="online-pulse"></span>
                <div>
                    <div class="online-widget-title">Activos ahora</div>
                    <div class="online-widget-sub" id="widget-status-text">Cargando...</div>
                </div>
            </div>
            <span class="online-count-badge" id="widget-online-count">—</span>
        </a>

        <!-- Projects -->
        <div class="section-label">Proyectos</div>
        <ul class="project-list">
            <?php foreach ($equipos as $equipo): ?>
            <li class="project-item">
                <a href="?team_id=<?= $equipo['id'] ?>" class="project-link <?= $selected_team_id == $equipo['id'] ? 'active' : '' ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:0.6">
                        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 2H8L2 7h20z"/>
                    </svg>
                    <?= htmlspecialchars($equipo['name']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Logout -->
        <form method="POST" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--divider);">
            <div class="theme-toggle" style="margin-bottom: 8px;">
                <span>Modo oscuro</span>
                <button type="button" class="toggle-track" id="themeBtn" onclick="toggleTheme()">
                    <span class="toggle-thumb"></span>
                </button>
            </div>
            <button type="submit" name="logout" class="logout-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Cerrar sesión
            </button>
        </form>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <?php if ($selected_team): ?>

            <!-- Header -->
            <div class="content-header">
                <div>
                    <h1 class="project-title"><?= htmlspecialchars($selected_team['name']) ?></h1>
                    <?php
                        $s = $selected_team['status'] ?? 'En Progreso';
                        $bclass = match(true) {
                            str_contains($s, 'Comple') => 'badge-green',
                            str_contains($s, 'Progres') => 'badge-blue',
                            str_contains($s, 'Pagus') || str_contains($s, 'Pausad') => 'badge-amber',
                            str_contains($s, 'Cancel') => 'badge-red',
                            default => 'badge-zinc'
                        };
                    ?>
                    <span class="status-badge <?= $bclass ?>"><?= htmlspecialchars($s) ?></span>
                    <?php if ($es_miembro): ?>
                        <span style="margin-left:10px; font-size:0.8rem; color:#4FC59F; font-weight:500;">● Eres miembro</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bento Grid -->
            <div class="bento-grid">

                <!-- Left column -->
                <div class="bento-col">
                    <!-- Description -->
                    <div class="card">
                        <div class="card-title">Descripción</div>
                        <div class="card-body"><?= nl2br(htmlspecialchars($selected_team['description'])) ?></div>
                    </div>

                    <!-- Roadmap Widget -->
                    <div class="card" id="roadmap-widget" style="padding-bottom:20px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#052DD4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                                <span class="card-title" style="margin-bottom:0;">Hoja de Ruta</span>
                            </div>
                            <button id="btn-refresh-roadmap" style="display:flex; align-items:center; gap:5px; padding:5px 12px; background:var(--surface); border:1px solid var(--border); border-radius:8px; font-size:0.75rem; font-weight:500; color:var(--text-muted); cursor:pointer; transition:all 0.15s;" onmouseover="this.style.borderColor='#052DD4';this.style.color='#052DD4'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                Actualizar
                            </button>
                        </div>
                        <div id="roadmap-content">
                            <div class="gh-loader"><div style="width:32px;height:32px;border:3px solid var(--border);border-top-color:#052DD4;border-radius:50%;animation:spin 0.7s linear infinite;margin:0 auto;"></div><br>Generando hoja de ruta...</div>
                        </div>
                    </div>

                    <!-- Admin controls -->
                    <?php if ($user_role === 'admin'): ?>
                    <div class="card">
                        <div class="card-title">Gestión del proyecto</div>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:12px;">Cambia el estado del proyecto como administrador.</p>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                            <select name="new_status" class="form-select">
                                <option value="En Progreso" <?= ($selected_team['status'] ?? '') == 'En Progreso' ? 'selected' : '' ?>>En Progreso</option>
                                <option value="Completado" <?= ($selected_team['status'] ?? '') == 'Completado' ? 'selected' : '' ?>>Completado</option>
                                <option value="Pausado" <?= ($selected_team['status'] ?? '') == 'Pausado' ? 'selected' : '' ?>>Pausado</option>
                                <option value="Cancelado" <?= ($selected_team['status'] ?? '') == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Actualizar</button>
                        </form>

                        <hr style="border:none; border-top:1px solid var(--border); margin:20px 0;">
                        <div style="font-size:0.85rem; font-weight:600; color:var(--text); margin-bottom:4px;">Repositorio GitHub</div>
                        <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:10px;">Formato: <code style="background:var(--divider); padding:1px 5px; border-radius:4px;">usuario/repo</code></p>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                            <input type="hidden" name="update_github" value="1">
                            <input type="text" name="github_repo" class="form-select" placeholder="Ej. facebook/react" value="<?= htmlspecialchars($selected_team['github_repo'] ?? '') ?>">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right column -->
                <div class="bento-col">
                    <!-- Actions -->
                    <div class="card">
                        <div class="card-title">Acciones</div>
                        <form method="POST">
                            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                            <?php if ($es_miembro): ?>
                                <button type="submit" name="leave_team" class="btn btn-danger btn-full">Abandonar proyecto</button>
                            <?php else: ?>
                                <button type="submit" name="join_team" class="btn btn-brand btn-full">Unirse al proyecto</button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Members -->
                    <div class="card">
                        <div class="card-title">Miembros (<?= count($miembros) ?>)</div>
                        <?php if (empty($miembros)): ?>
                            <p style="font-size:0.875rem; color:var(--text-muted);">No hay miembros aún.</p>
                        <?php else: ?>
                            <div class="member-list">
                                <?php foreach ($miembros as $m): ?>
                                <div class="member-item">
                                    <div class="member-info">
                                        <img src="https://api.dicebear.com/7.x/thumbs/svg?seed=<?= urlencode($m['username']) ?>&backgroundColor=b6e3f4,c0aede,d1d4f9&radius=12"
                                             class="member-avatar" alt="Avatar">
                                        <span class="member-name"><?= htmlspecialchars($m['username']) ?></span>
                                        <?php $sc = 'user-status-' . str_replace(' ', '-', $m['status']); ?>
                                        <span class="member-status-dot <?= $sc ?>"></span>
                                    </div>
                                    <?php if ($m['role'] === 'admin'): ?>
                                        <span class="role-tag role-admin">Jefe</span>
                                    <?php else: ?>
                                        <span class="role-tag role-member">Miembro</span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- GitHub Widget -->
                    <?php if (!empty($selected_team['github_repo'])): ?>
                    <div class="github-widget" data-repo="<?= htmlspecialchars($selected_team['github_repo']) ?>">
                        <div class="github-header">
                            <svg height="16" viewBox="0 0 16 16" width="16" fill="currentColor"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
                            <a href="https://github.com/<?= htmlspecialchars($selected_team['github_repo']) ?>" target="_blank" style="color:var(--text); text-decoration:none; font-size:0.85rem;"><?= htmlspecialchars($selected_team['github_repo']) ?></a>
                        </div>
                        <div class="github-tabs">
                            <div class="github-tab active" data-target="commits">Commits</div>
                            <div class="github-tab" data-target="pulls">Pull Requests</div>
                            <div class="github-tab" data-target="issues">Issues</div>
                        </div>
                        <div class="github-content" id="gh-content-box"><div class="gh-loader">Cargando...</div></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d4d4d8" stroke-width="1.5" style="margin: 0 auto 12px;"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 2H8L2 7h20z"/></svg>
                <p style="font-weight:600; color:#52525b;">Selecciona un proyecto</p>
                <p style="margin-top:4px; font-size:0.875rem;">Elige un proyecto del panel lateral para ver sus detalles.</p>
            </div>
        <?php endif; ?>
    </main>

<script>
    // Dark mode
    function applyTheme(dark) {
        document.body.classList.toggle('dark', dark);
        const btn = document.getElementById('themeBtn');
        if (btn) btn.classList.toggle('on', dark);
        localStorage.setItem('th', dark ? '1' : '0');
    }
    function toggleTheme() { applyTheme(!document.body.classList.contains('dark')); }
    (function() {
        const saved = localStorage.getItem('th');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(saved !== null ? saved === '1' : prefersDark);
    })();

    // Online widget
    fetch('/endpoints/get_online_users.php')
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                document.getElementById('widget-online-count').textContent = (d.online_count || 0);
                document.getElementById('widget-status-text').textContent = 'Ver detalles →';
            }
        }).catch(() => {});

    // GitHub Widget
    document.addEventListener('DOMContentLoaded', () => {
        const ghWidget = document.querySelector('.github-widget');
        if (!ghWidget) return;
        const repo = ghWidget.dataset.repo;
        const tabs = document.querySelectorAll('.github-tab');
        const contentBox = document.getElementById('gh-content-box');

        const loadGitHubData = async (action) => {
            contentBox.innerHTML = '<div class="gh-loader">Cargando...</div>';
            try {
                const res = await fetch(`../endpoints/github_proxy.php?action=${action}&repo=${repo}`);
                if (!res.ok) throw new Error();
                const data = await res.json();
                if (!Array.isArray(data) || data.length === 0) { contentBox.innerHTML = '<div class="gh-loader">No hay elementos recientes</div>'; return; }
                let html = '';
                data.forEach(item => {
                    if (action === 'commits') {
                        const msg = item.commit.message.split('\n')[0];
                        const author = item.commit.author.name;
                        const date = new Date(item.commit.author.date).toLocaleDateString(undefined, {month:'short',day:'numeric'});
                        html += `<div class="gh-item"><a href="${item.html_url}" target="_blank" class="gh-title">${msg}</a><div class="gh-meta">por <strong>${author}</strong> &middot; ${date}</div></div>`;
                    } else if (action === 'pulls') {
                        const badge = item.state === 'open' ? '<span class="gh-badge open">Abierto</span>' : '<span class="gh-badge merged">Fusionado</span>';
                        html += `<div class="gh-item"><a href="${item.html_url}" target="_blank" class="gh-title">${item.title}</a><div class="gh-meta">${badge} #${item.number} por ${item.user.login}</div></div>`;
                    } else if (action === 'issues') {
                        if (item.pull_request) return;
                        const badge = item.state === 'open' ? '<span class="gh-badge open">Abierto</span>' : '<span class="gh-badge closed">Cerrado</span>';
                        html += `<div class="gh-item"><a href="${item.html_url}" target="_blank" class="gh-title">${item.title}</a><div class="gh-meta">${badge} #${item.number} por ${item.user.login}</div></div>`;
                    }
                });
                contentBox.innerHTML = html || '<div class="gh-loader">Sin resultados</div>';
            } catch { contentBox.innerHTML = '<div class="gh-loader" style="color:#ef4444;">Error cargando datos (repositorio privado o no encontrado)</div>'; }
        };

        tabs.forEach(tab => tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            loadGitHubData(tab.dataset.target);
        }));
        loadGitHubData('commits');
    });

    // Roadmap
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
            contentBox.innerHTML = `<div class="gh-loader"><div style="width:32px;height:32px;border:3px solid var(--border);border-top-color:#6366F1;border-radius:50%;animation:spin 0.7s linear infinite;margin:0 auto;"></div><br>Generando hoja de ruta...</div>`;
            if (refreshBtn) refreshBtn.disabled = true;
            try {
                const url = forceRefresh
                    ? `../endpoints/roadmap_generator.php?team_id=${teamId}&force_refresh=true`
                    : `../endpoints/roadmap_generator.php?team_id=${teamId}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();
                if (data.error) throw new Error(data.error);
                const roadmap = data.roadmap;
                let html = '<div class="roadmap-container">';
                let hasActive = false;
                Object.keys(roadmap).forEach((key, i) => {
                    const phase = roadmap[key];
                    let cls = '';
                    if (phase.completado) { cls = 'completed'; }
                    else if (!hasActive) { cls = 'active'; hasActive = true; }
                    html += `<div class="roadmap-phase ${cls}"><div class="phase-dot">${phase.completado ? '✓' : i+1}</div><div class="phase-title">${phase.nombre}</div><div class="phase-desc">${phase.desc}</div><div class="phase-progress-bar"><div class="phase-progress-fill" style="width:${phase.avance}%"></div></div><div style="font-size:0.7rem;color:var(--text-muted);font-weight:600;margin-top:4px;">${phase.avance}%</div></div>`;
                });
                html += '</div>';
                if (data.github) {
                    html += `<div style="margin-top:16px;padding-top:12px;border-top:1px dashed var(--border);display:flex;gap:16px;font-size:0.78rem;color:var(--text-muted);"><span><strong style="color:var(--text);">${data.github.commits}</strong> Commits</span><span><strong style="color:var(--text);">${data.github.prs_closed}</strong> PRs cerrados</span>${data.github.active ? '<span style="color:#10b981;font-weight:600;">● Activo</span>' : ''}</div>`;
                }
                contentBox.innerHTML = html;
            } catch(err) {
                if (!roadmapIsUnloading) contentBox.innerHTML = `<div style="font-size:0.85rem;color:var(--text-muted);padding:20px 0;">No se pudo cargar la hoja de ruta.<br><small style="color:#ef4444;">${err.message}</small></div>`;
            }
            if (refreshBtn) refreshBtn.disabled = false;
        };

        if (refreshBtn) refreshBtn.addEventListener('click', () => fetchRoadmap(true));
        fetchRoadmap(false);
    });
</script>
<script src="js/heartbeat.js"></script>
</body>
</html>
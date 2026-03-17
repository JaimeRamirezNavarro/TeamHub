<?php
/**
 * TeamHub - Unified Login Page
 * Handles Normal Login, Registration, and Gather Auto-Registration
 */

session_start();
require_once __DIR__ . '/../modelo/consultas.php';

// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$consultas = new Consultas();
$error = '';
$success = '';
$active_tab = 'login';


// 2. Handle "Remember Me" Cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['teamhub_remember'])) {
    $token = $_COOKIE['teamhub_remember'];
    $user = $consultas->obtenerUsuarioPorToken($token);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $consultas->actualizarUltimaActividad($user['id']);
        header("Location: dashboard.php");
        exit;
    }
}

// 3. Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CASE A: Normal Login
    if (isset($_POST['login'])) {
        $identifier = trim($_POST['identifier']);
        $password = trim($_POST['password']);
        
        $user = $consultas->verificarlogin($identifier, $password);
        
        if ($user) {
            loginUser($user, isset($_POST['remember_me']), $consultas);
        } else {
            $error = 'Credenciales incorrectas';
        }
    }
    
    // CASE B: New Account Registration
    elseif (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        if ($consultas->registrarUsuario($username, $email, $password)) {
            $success = '¡Cuenta creada con éxito! Por favor inicia sesión.';
            $active_tab = 'login';
        } else {
            $error = 'El correo ya está registrado.';
            $active_tab = 'register';
        }
    }
    

}

/**
 * Helper to Handle User Login Session
 */
function loginUser($user, $remember, $consultas, $isWelcome = false) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $consultas->guardarTokenRecordar($user['id'], $token);
        setcookie('teamhub_remember', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }
    
    $consultas->actualizarEstado($user['id'], 'Oficina');
    $consultas->actualizarUltimaActividad($user['id']);
    
    $redirect = "dashboard.php";
    if ($isWelcome) $redirect .= "?welcome=1";
    
    header("Location: $redirect");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamHub | Acceso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#052DD4',
                        panel: '#FFFFFF',
                        background: '#EBE8E6',
                    },
                    borderRadius: { '2xl': '1.5rem' },
                    boxShadow: {
                        'bento': '0 0 0 1px rgba(0,0,0,0.05), 0 2px 4px rgba(0,0,0,0.05), 0 12px 24px rgba(0,0,0,0.05)',
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        :root {
            --bg: #EBE8E6;
            --surface: #ffffff;
            --border: #d4d0cd;
            --text: #000000;
            --text-muted: #4a4a4a;
            --text-faint: #7a7a7a;
            --accent: #052DD4;
            --lime: #CAFB04;
            --input-bg: white;
        }
        body {
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
        }
        body.dark {
            --bg: #000000;
            --surface: #111111;
            --border: #2a2a2a;
            --text: #EBE8E6;
            --text-muted: #a0a0a0;
            --text-faint: #666666;
            --accent: #052DD4;
            --lime: #CAFB04;
            --input-bg: #1a1a1a;
        }
        .form-section { display: none; }
        .form-section.active { display: block; }
        .tab-btn { position: relative; padding-bottom: 12px; font-size: 0.875rem; font-weight: 500; color: var(--text-muted); border: none; background: none; cursor: pointer; }
        .tab-btn::after { content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 2px; background: #052DD4; transform: scaleX(0); transition: transform 0.2s ease; border-radius: 2px; }
        .tab-btn.active { color: #052DD4; font-weight: 600; }
        .tab-btn.active::after { transform: scaleX(1); }
        /* theme toggle corner */
        .theme-corner {
            position: fixed; top: 16px; right: 16px;
            display: flex; align-items: center; gap: 8px;
            font-size: 0.75rem; color: var(--text-faint);
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background: radial-gradient(ellipse at 60% 0%, rgba(5,45,212,0.07) 0%, var(--bg) 65%);">

    <!-- Dark mode toggle -->
    <div class="theme-corner">
        <span id="themeLabel">🌙</span>
        <button type="button" class="toggle-track" id="themeBtn" onclick="toggleTheme()">
            <span class="toggle-thumb"></span>
        </button>
    </div>

    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand mb-4" style="box-shadow: 0 0 0 1px rgba(0,0,0,0.05), 0 4px 12px rgba(5,45,212,0.3);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">TeamHub</h1>
            <p class="text-sm text-zinc-500 mt-1">Tu espacio de trabajo compartido</p>
        </div>

        <!-- Card -->
        <div style="background: var(--surface); border-radius:24px; padding:32px; box-shadow: 0 0 0 1px rgba(0,0,0,0.05), 0 2px 4px rgba(0,0,0,0.05), 0 12px 24px rgba(0,0,0,0.05);">

            <?php if($error): ?>
            <div style="display:flex; align-items:center; gap:8px; padding:12px 16px; margin-bottom:20px; background: #fef2f2; border: 1px solid #fecaca; border-radius:12px; color:#dc2626; font-size:0.875rem; font-weight:500;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="0.5" fill="currentColor"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            <?php if($success): ?>
            <div style="display:flex; align-items:center; gap:8px; padding:12px 16px; margin-bottom:20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius:12px; color:#15803d; font-size:0.875rem; font-weight:500;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div style="display:flex; border-bottom: 1px solid var(--border); margin-bottom:24px; gap:20px;">
                <button onclick="switchTab('login')" id="tab-login" class="tab-btn <?= $active_tab == 'login' ? 'active' : '' ?>">Iniciar sesión</button>
                <button onclick="switchTab('register')" id="tab-register" class="tab-btn <?= $active_tab == 'register' ? 'active' : '' ?>">Crear cuenta</button>
            </div>

            <!-- Login Form -->
            <div id="login-form" class="form-section <?= $active_tab == 'login' ? 'active' : '' ?>">
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-zinc-400 mb-1.5">Usuario o email</label>
                        <input type="text" name="identifier" placeholder="nombre@empresa.com" required
                            style="width:100%; padding: 11px 16px; border: 1px solid var(--border); border-radius: 12px; font-size: 0.875rem; color: var(--text); background: var(--input-bg); outline: none; transition: box-shadow 0.15s, border-color 0.15s;"
                            onfocus="this.style.borderColor='#052DD4'; this.style.boxShadow='0 0 0 3px rgba(5,45,212,0.15)'"
                            onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-zinc-400 mb-1.5">Contraseña</label>
                        <input type="password" name="password" placeholder="••••••••" required
                            style="width:100%; padding: 11px 16px; border: 1px solid var(--border); border-radius: 12px; font-size: 0.875rem; color: var(--text); background: var(--input-bg); outline: none; transition: box-shadow 0.15s, border-color 0.15s;"
                            onfocus="this.style.borderColor='#052DD4'; this.style.boxShadow='0 0 0 3px rgba(5,45,212,0.15)'"
                            onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-sm text-zinc-500 cursor-pointer select-none">
                            <input type="checkbox" name="remember_me" checked style="width:15px; height:15px; accent-color: #052DD4; cursor:pointer;">
                            Recordarme
                        </label>
                        <a href="#" class="text-xs text-brand font-medium transition-colors" style="color:#052DD4;">¿Olvidaste tu contraseña?</a>
                    </div>
                    <button type="submit" name="login"
                        style="width:100%; padding: 12px; background: #000000; color: white; border: none; border-radius: 12px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s, transform 0.1s; margin-top: 4px;"
                        onmouseover="this.style.background='#1a1a1a'"
                        onmouseout="this.style.background='#000000'"
                        onmousedown="this.style.transform='scale(0.97)'"
                        onmouseup="this.style.transform='scale(1)'">
                        Iniciar sesión
                    </button>
                </form>
            </div>

            <!-- Register Form -->
            <div id="register-form" class="form-section <?= $active_tab == 'register' ? 'active' : '' ?>">
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-zinc-400 mb-1.5">Nombre de usuario</label>
                        <input type="text" name="username" placeholder="john_doe" required
                            style="width:100%; padding: 11px 16px; border: 1px solid var(--border); border-radius: 12px; font-size: 0.875rem; color: var(--text); background: var(--input-bg); outline: none; transition: box-shadow 0.15s, border-color 0.15s;"
                            onfocus="this.style.borderColor='#052DD4'; this.style.boxShadow='0 0 0 3px rgba(5,45,212,0.15)'"
                            onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-zinc-400 mb-1.5">Email</label>
                        <input type="email" name="email" placeholder="nombre@empresa.com" required
                            style="width:100%; padding: 11px 16px; border: 1px solid var(--border); border-radius: 12px; font-size: 0.875rem; color: var(--text); background: var(--input-bg); outline: none; transition: box-shadow 0.15s, border-color 0.15s;"
                            onfocus="this.style.borderColor='#052DD4'; this.style.boxShadow='0 0 0 3px rgba(5,45,212,0.15)'"
                            onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                    </div>
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wider text-zinc-400 mb-1.5">Contraseña</label>
                        <input type="password" name="password" placeholder="••••••••" required
                            style="width:100%; padding: 11px 16px; border: 1px solid var(--border); border-radius: 12px; font-size: 0.875rem; color: var(--text); background: var(--input-bg); outline: none; transition: box-shadow 0.15s, border-color 0.15s;"
                            onfocus="this.style.borderColor='#052DD4'; this.style.boxShadow='0 0 0 3px rgba(5,45,212,0.15)'"
                            onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                    </div>
                    <button type="submit" name="register"
                        style="width:100%; padding: 12px; background: #052DD4; color: white; border: none; border-radius: 12px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s, transform 0.1s; margin-top: 4px;"
                        onmouseover="this.style.background='#0424a8'"
                        onmouseout="this.style.background='#052DD4'"
                        onmousedown="this.style.transform='scale(0.97)'"
                        onmouseup="this.style.transform='scale(1)'">
                        Crear cuenta
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-xs text-zinc-400 mt-6">© <?= date('Y') ?> TeamHub. Todos los derechos reservados.</p>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            document.querySelectorAll('.form-section').forEach(f => f.classList.remove('active'));
            document.getElementById(tab + '-form').classList.add('active');
        }
        function applyTheme(dark) {
            document.body.classList.toggle('dark', dark);
            const btn = document.getElementById('themeBtn');
            if (btn) btn.classList.toggle('on', dark);
            document.getElementById('themeLabel').textContent = dark ? '☀️' : '🌙';
            localStorage.setItem('th', dark ? '1' : '0');
        }
        function toggleTheme() { applyTheme(!document.body.classList.contains('dark')); }
        (function() {
            const saved = localStorage.getItem('th');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(saved !== null ? saved === '1' : prefersDark);
        })();
    </script>
</body>
</html>

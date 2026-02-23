<?php
/**
 * TeamHub - Unified Login Page
 * Handles Normal Login, Registration, and Gather Auto-Registration
 */

session_start();
require_once __DIR__ . '/../modelo/consultas.php';

// 1. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: final_index.php"); // Will be renamed to dashboard.php via router
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: #121212;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            color: white; 
        }
        .container { 
            background: #1e1e1e; 
            padding: 2.5rem; 
            border-radius: 16px; 
            border: 1px solid #333; 
            width: 100%;
            max-width: 400px; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.5); 
        }
        .logo {
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8rem;
            font-weight: 700;
            color: #2196F3;
        }
        .logo span { color: white; }
        
        .tabs { 
            display: flex; 
            margin-bottom: 25px; 
            background: #252525;
            padding: 4px;
            border-radius: 8px;
        }
        .tab { 
            flex: 1; 
            padding: 10px; 
            text-align: center; 
            cursor: pointer; 
            color: #888; 
            font-weight: 600; 
            border-radius: 6px;
            transition: 0.2s;
            font-size: 0.9rem;
        }
        .tab.active { 
            background: #333;
            color: white; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .form-section { display: none; animation: fadeIn 0.3s ease; }
        .form-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        input { 
            width: 100%; 
            padding: 14px; 
            margin: 8px 0; 
            background: #2a2a2a; 
            border: 1px solid #444; 
            color: white; 
            box-sizing: border-box; 
            border-radius: 8px; 
            font-size: 0.95rem;
            transition: 0.2s;
        }
        input:focus { outline: none; border-color: #2196F3; background: #333; }
        
        .btn { 
            width: 100%; 
            padding: 14px; 
            margin-top: 20px; 
            border: none; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 1rem; 
            cursor: pointer; 
            transition: 0.2s; 
        }
        .btn-primary { background: #2196F3; color: white; }
        .btn-primary:hover { background: #1976D2; transform: translateY(-1px); }


        .msg { 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 0.9rem; 
        }
        .error { background: rgba(255, 82, 82, 0.1); color: #ff5252; border: 1px solid rgba(255, 82, 82, 0.2); }
        .success { background: rgba(76, 175, 80, 0.1); color: #4CAF50; border: 1px solid rgba(76, 175, 80, 0.2); }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo"><span>TeamHub</span></div>

        <?php if($error) echo "<div class='msg error'>$error</div>"; ?>
        <?php if($success) echo "<div class='msg success'>$success</div>"; ?>
        
            <!-- NORMAL MODE -->
            <div class="tabs">
                <div class="tab <?= $active_tab == 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Iniciar Sesión</div>
                <div class="tab <?= $active_tab == 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Registrarse</div>
            </div>

            <div id="login-form" class="form-section <?= $active_tab == 'login' ? 'active' : '' ?>">
                <form method="POST">
                    <input type="text" name="identifier" placeholder="Correo electrónico o Usuario" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    
                    <div style="display: flex; align-items: center; margin: 15px 0; color: #aaa; font-size: 0.9rem;">
                        <input type="checkbox" name="remember_me" id="remember_me" checked style="width: auto; margin-right: 8px;">
                        <label for="remember_me">No cerrar sesión</label>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary">Entrar</button>
                </form>
            </div>

            <div id="register-form" class="form-section <?= $active_tab == 'register' ? 'active' : '' ?>">
                <form method="POST">
                    <input type="text" name="username" placeholder="Nombre de usuario" required>
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit" name="register" class="btn btn-primary" style="background: #4CAF50;">Crear Cuenta</button>
                </form>
            </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            document.querySelectorAll('.form-section').forEach(f => f.classList.remove('active'));
            document.getElementById(tab + '-form').classList.add('active');
        }
    </script>
</body>
</html>

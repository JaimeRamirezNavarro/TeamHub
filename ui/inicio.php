<?php
session_start();
require_once __DIR__ . '/../modelo/consultas.php';

// Si ya está logueado, ir al Dashboard
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$consultas = new Consultas();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // LOGIN
    if (isset($_POST['login'])) {
        $user = $consultas->verificarlogin($_POST['email'], $_POST['password']);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Credenciales incorrectas';
            $active_tab = 'login';
        }
    }
    // REGISTER
    elseif (isset($_POST['register'])) {
        $role = $_POST['role'] ?? 'user';
        if ($consultas->registrarUsuario($_POST['username'], $_POST['email'], $_POST['password'], $role)) {
            $success = '¡Cuenta creada! Por favor inicia sesión.';
            $active_tab = 'login'; // Ir al login tras registro
        } else {
            $error = 'El correo ya está registrado.';
            $active_tab = 'register';
        }
    }
}

// Determinar pestaña activa por defecto
$active_tab = isset($active_tab) ? $active_tab : 'login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TeamHub | Inicio</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #121212; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; color: white; }
        .container { background: #1e1e1e; padding: 2rem; border-radius: 12px; border: 1px solid #333; width: 350px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        .tabs { display: flex; margin-bottom: 20px; border-bottom: 2px solid #333; }
        .tab { flex: 1; padding: 10px; text-align: center; cursor: pointer; color: #888; font-weight: bold; transition: 0.3s; }
        .tab.active { color: #2196F3; border-bottom: 2px solid #2196F3; margin-bottom: -2px; }
        .form-section { display: none; }
        .form-section.active { display: block; }
        h2 { text-align: center; margin-top: 0; }
        input { width: 100%; padding: 12px; margin: 8px 0; background: #2a2a2a; border: 1px solid #444; color: white; box-sizing: border-box; border-radius: 6px; }
        input:focus { outline: none; border-color: #2196F3; }
        button { width: 100%; padding: 12px; margin-top: 15px; border: none; color: white; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 1rem; transition: 0.3s; }
        .btn-login { background: #2196F3; }
        .btn-login:hover { background: #1976D2; }
        .btn-register { background: #4CAF50; }
        .btn-register:hover { background: #388E3C; }
        .error { color: #ff5252; font-size: 0.9em; text-align: center; margin-bottom: 15px; background: rgba(255, 82, 82, 0.1); padding: 10px; border-radius: 4px;}
        .success { color: #4CAF50; font-size: 0.9em; text-align: center; margin-bottom: 15px; background: rgba(76, 175, 80, 0.1); padding: 10px; border-radius: 4px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab <?= $active_tab == 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Iniciar Sesión</div>
            <div class="tab <?= $active_tab == 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Registrarse</div>
        </div>

        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <?php if($success) echo "<p class='success'>$success</p>"; ?>

        <!-- Login Form -->
        <div id="login-form" class="form-section <?= $active_tab == 'login' ? 'active' : '' ?>">
            <h2>Bienvenido</h2>
            <form method="POST">
                <input type="email" name="email" placeholder="Correo electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit" name="login" class="btn-login">Entrar</button>
            </form>
        </div>

        <!-- Register Form -->
        <div id="register-form" class="form-section <?= $active_tab == 'register' ? 'active' : '' ?>">
            <h2>Crear Cuenta</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Nombre de usuario" required>
                <input type="email" name="email" placeholder="Correo electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <select name="role" required style="width: 100%; padding: 12px; margin: 8px 0; background: #2a2a2a; border: 1px solid #444; color: white; border-radius: 6px;">
                    <option value="user">Trabajador</option>
                    <option value="admin">Jefe</option>
                </select>
                <button type="submit" name="register" class="btn-register">Registrarse</button>
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

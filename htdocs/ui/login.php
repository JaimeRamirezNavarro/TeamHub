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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        base: '#F2F0E9',
                        neon: '#a8dba8', /* Soft retro green */
                        neonSec: '#79c753', /* Muted accent green */
                    },
                    fontFamily: {
                        serif: ['Times New Roman', 'Georgia', 'serif'],
                        mono: ['Courier New', 'Courier', 'monospace'],
                    },
                    boxShadow: {
                        'brutal-lg': '8px 8px 0px 0px rgba(0,0,0,1)',
                        'brutal-md': '4px 4px 0px 0px rgba(0,0,0,1)',
                        'brutal-sm': '1px 1px 0px 0px rgba(0,0,0,1)',
                    }
                }
            }
        }
    </script>
    <style>
        /* Base y Reseteo Brutalista */
        body { 
            background: #F2F0E9;
            color: black;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            background-image: linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px), linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px);
            background-size: 20px 20px;
        }

        /* Cursores Pixelados Retro (Windows 95) */
        * {
            cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='white' stroke='black' stroke-width='1.5' d='M0,0 L0,18 L6,12 L9,19.5 L12,18 L9,10.5 L15,10.5 Z'/%3E%3C/svg%3E") 0 0, auto;
        }
        a, button, [role="button"], .cursor-pointer, input[type="submit"], input[type="button"], input[type="checkbox"], label {
            cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'%3E%3Cpath fill='white' stroke='black' stroke-width='1.5' d='M12,0 L12,7.5 L16.5,7.5 L16.5,10.5 L12,10.5 L12,18 L9,18 L9,10.5 L4.5,10.5 L4.5,7.5 L9,7.5 L9,0 Z M0,9 L4.5,9 L4.5,12 L0,12 Z M16.5,9 L21,9 L21,12 L16.5,12 Z'/%3E%3C/svg%3E") 12 12, pointer !important;
        }
        input:not([type="checkbox"]), textarea {
            cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='24' viewBox='0 0 12 24'%3E%3Crect x='4.5' y='0' width='3' height='24' fill='black'/%3E%3Crect x='0' y='0' width='12' height='3' fill='black'/%3E%3Crect x='0' y='21' width='12' height='3' fill='black'/%3E%3C/svg%3E") 6 12, text !important;
        }

        .tab.active { 
            background: #CCFF00;
            color: black; 
        }

        /* Animación de formularios */
        .form-section { display: none; }
        .form-section.active { display: block; }

        input:not([type="checkbox"]) {
            transition: none;
        }
        input:not([type="checkbox"]):focus {
            background-color: #CCFF00;
        }

        .checkbox-group input {
            accent-color: black;
            width: 16px;
            height: 16px;
            border: 2px solid black;
            border-radius: 0;
        }
    </style>
</head>
<body class="font-mono">
    <div class="container border-2 border-black rounded-none shadow-brutal-lg bg-base w-full max-w-md p-8 relative">
        <div class="logo font-serif font-black text-3xl uppercase text-center mb-8 text-black border-b-4 border-black pb-4 inline-block w-full">TeamHub</div>

        <?php if($error) echo "<div class='p-3 mb-5 border-2 border-black bg-white text-black font-bold uppercase shadow-brutal-sm text-sm text-center'>⚠️ $error</div>"; ?>
        <?php if($success) echo "<div class='p-3 mb-5 border-2 border-black bg-neonSec text-black font-bold uppercase shadow-brutal-sm text-sm text-center'>✨ $success</div>"; ?>
        
        <div class="tabs flex mb-6 border-2 border-black shadow-brutal-sm bg-white">
            <div class="tab flex-1 p-2 text-center border-r-2 border-black font-bold uppercase text-xs cursor-pointer hover:bg-neon <?= $active_tab == 'login' ? 'active' : '' ?>" onclick="switchTab('login')">LOGIN.EXE</div>
            <div class="tab flex-1 p-2 text-center font-bold uppercase text-xs cursor-pointer hover:bg-neon <?= $active_tab == 'register' ? 'active' : '' ?>" onclick="switchTab('register')">NEW_USER.BAT</div>
        </div>

        <div id="login-form" class="form-section <?= $active_tab == 'login' ? 'active' : '' ?>">
            <form method="POST">
                <input type="text" name="identifier" placeholder="USER_ID / EMAIL" required class="w-full p-3 mb-4 border-2 border-black bg-white focus:outline-none placeholder-black placeholder-opacity-40 text-black font-bold uppercase text-sm shadow-brutal-sm">
                <input type="password" name="password" placeholder="PASSWORD" required class="w-full p-3 mb-4 border-2 border-black bg-white focus:outline-none placeholder-black placeholder-opacity-40 text-black font-bold uppercase text-sm shadow-brutal-sm">
                
                <div class="flex justify-between items-center mb-6 text-xs font-bold uppercase font-mono mt-2">
                    <label class="flex items-center cursor-pointer hover:text-neonSec">
                        <input type="checkbox" name="remember_me" id="remember_me" checked class="mr-2 border-2 border-black rounded-none cursor-pointer">
                        <span class="mt-1">SAVE_STATE</span>
                    </label>
                    <a href="#" class="text-black hover:bg-black hover:text-white px-1 py-0.5 border border-transparent hover:border-black transition-none">SYS_RECOVERY?</a>
                </div>
                
                <button type="submit" name="login" class="w-full p-3 border-2 border-black bg-black text-neon font-bold uppercase hover:bg-neon hover:text-black active:translate-x-[2px] active:translate-y-[2px] shadow-brutal-md active:shadow-none transition-none tracking-widest text-lg">
                    > EXECUTE
                </button>
            </form>
        </div>

        <div id="register-form" class="form-section <?= $active_tab == 'register' ? 'active' : '' ?>">
            <form method="POST">
                <input type="text" name="username" placeholder="NEW_USER_ID" required class="w-full p-3 mb-4 border-2 border-black bg-white focus:outline-none placeholder-black placeholder-opacity-40 text-black font-bold uppercase text-sm shadow-brutal-sm">
                <input type="email" name="email" placeholder="CONTACT_ADDRESS" required class="w-full p-3 mb-4 border-2 border-black bg-white focus:outline-none placeholder-black placeholder-opacity-40 text-black font-bold uppercase text-sm shadow-brutal-sm">
                <input type="password" name="password" placeholder="SECURE_KEY" required class="w-full p-3 mb-6 border-2 border-black bg-white focus:outline-none placeholder-black placeholder-opacity-40 text-black font-bold uppercase text-sm shadow-brutal-sm">
                
                <button type="submit" name="register" class="w-full p-3 border-2 border-black bg-white text-black font-bold uppercase hover:bg-neonSec hover:text-black active:translate-x-[2px] active:translate-y-[2px] shadow-brutal-md active:shadow-none transition-none tracking-widest text-lg mt-2">
                    > COMPILE
                </button>
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
        
        // Efectos de sonido (opcional, como pedía la guía)
        const clickSound = new Audio('data:audio/mp3;base64,//OExAAAAANIAAAAAExBTUUzLjEwMKqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq'); // Placeholder mp3 silent
        document.querySelectorAll('button, .tab, input[type="checkbox"]').forEach(el => {
            el.addEventListener('mousedown', () => {
                // Play sound if you have real audios
            });
        });
    </script>
</body>
</html>

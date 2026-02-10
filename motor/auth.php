<?php
require_once __DIR__ . '/../modelo/consultas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consulta = new Consultas();

    // LOGIN
    if (isset($_POST['login'])) {
        $user = $consulta->verificarlogin($_POST['email'], $_POST['password']);
        if ($user) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../ui/index.php");
            exit;
        } else {
            header("Location: ../ui/inicio.php?error=login");
            exit;
        }
    }

    // REGISTER
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($consulta->registrarUsuario($username, $email, $password)) {
            // Registro exitoso, redirigir al login
            header("Location: ../ui/inicio.php?registered=1");
            exit;
        } else {
            // Fallo en el registro (ej: email duplicado)
            header("Location: ../ui/inicio.php?error=register_exists");
            exit;
        }
    }
}

// Y en la función checkAuth
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if(!isset($_SESSION['user_id'])){
        header("Location: ../ui/inicio.php");
        exit;
    }
}

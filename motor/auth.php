<?php
require_once __DIR__ . '/../modelo/consultas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $consulta = new Consultas();
    $user = $consulta->verificarlogin($_POST['email'], $_POST['password']);
    if ($user) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    header("Location: ../ui/index.php"); // Sube un nivel y entra en ui
    exit;
} else {
    header("Location: ../ui/login.php?error=1");
    exit;
}
}

// Y en la función checkAuth
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if(!isset($_SESSION['user_id'])){
        header("Location: ../ui/login.php");
        exit;
    }
}

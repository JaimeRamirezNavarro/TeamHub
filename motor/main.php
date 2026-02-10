<?php
// motor/main.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../modelo/consultas.php';

$consultas = new Consultas();

// Manejar Acciones POST (Unirse/Salir/Cambiar Estado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../ui/inicio.php");
        exit;
    }
    $user_id = $_SESSION['user_id'];
    
    if (isset($_POST['join_team'])) {
        $consultas->unirseEquipo($user_id, $_POST['team_id']);
    } elseif (isset($_POST['leave_team'])) {
        $consultas->salirEquipo($user_id, $_POST['team_id']);
    } elseif (isset($_POST['cambiar_estado'])) {
        $consultas->actualizarEstado($user_id, $_POST['nuevo_estado']);
    }
    
    // Evitar reenvío de formulario al recargar
    header("Location: ../ui/index.php"); 
    exit;
}

// Preparamos los datos para la UI
// Ahora obtenemos EQUIPOS, no tareas
$equipos = $consultas->obtenerTodosLosEquipos();
<?php
// motor/main.php
require_once __DIR__ . '/../modelo/consultas.php';

$consultas = new Consultas();

$proyectos = $consultas->obtenerTareas();

$empleados = $consultas->obtenerUsuarios();

// Si el usuario cambia su estado desde la UI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $consultas->actualizarEstado($_POST['user_id'], $_POST['nuevo_estado']);
    header("Location: ../ui/index.php"); // Recarga la UI
    exit;
}

// Preparamos los datos para la UI
$empleados = $consultas->obtenerUsuarios();
$tareas = $consultas->obtenerTareas();
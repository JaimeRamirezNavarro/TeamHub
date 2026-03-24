<?php
// Evita que cualquier warning o espacio rompa el JSON
ob_start();

// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../modelo/consultas.php';

try {

    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $consultas = new Consultas();

    // Actualizar actividad del usuario
    $consultas->actualizarUltimaActividad($_SESSION['user_id']);

    // Limpiar cualquier salida previa
    ob_clean();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {

    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

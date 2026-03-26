<?php
// Evita que cualquier warning o espacio rompa el JSON
ob_start();

// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../app/Models/UserModel.php';

try {

    if (!isset($_SESSION['user']['id'])) {
        ob_clean();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $usersModel = new UserModel();

    // Actualizar actividad del usuario
    $usersModel->actualizarUltimaActividad($_SESSION['user']['id']);

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

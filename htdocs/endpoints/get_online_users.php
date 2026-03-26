<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Models/UserModel.php';

try {

    if (!isset($_SESSION['user']['id'])) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => 'No autenticado'
        ]);
        exit;
    }

    $usersModel = new UserModel();

    // Usuarios online últimos 10 minutos
    $onlineUsers = $usersModel->obtenerOnline(10);

    // Formatear igual que tu JS espera
    $formatted = array_map(function($u) {

        // Calcular minutos inactivos
        $mins = 0;
        if (!empty($u['last_activity'])) {
            $mins = (int) ((time() - strtotime($u['last_activity'])) / 60);
        }

        return [
            'name' => $u['username'],
            'email' => $u['email'] ?? '',
            'status' => $mins <= 5 ? 'online' : 'away',
            'customStatus' => $mins <= 5 ? 'Activo' : "Ausente ({$mins} min)",
            'last_activity' => $u['last_activity']
        ];
    }, $onlineUsers);

    ob_clean();
    echo json_encode([
        'success' => true,
        'online_count' => count($formatted),
        'users' => $formatted
    ]);

} catch (Exception $e) {

    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

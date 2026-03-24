<?php
ob_start(); // Evita que cualquier warning o espacio rompa el JSON

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../app/Services/RoadmapService.php';


try {

    // Validar sesión
    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    // Obtener parámetros
    $team_id = $_GET['team_id'] ?? null;
    $force   = isset($_GET['force_refresh']);

    if (!$team_id) {
        ob_clean();
        echo json_encode(['error' => 'Falta team_id']);
        exit;
    }

    // Ejecutar servicio
    $service = new RoadmapService();
    $result = $service->generateRoadmap($team_id, $force);

    // Limpiar cualquier salida previa
    ob_clean();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {

    ob_clean();
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

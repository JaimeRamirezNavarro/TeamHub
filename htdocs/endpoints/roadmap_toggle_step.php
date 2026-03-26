<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../app/Models/TeamModel.php';

try {
    if (!isset($_SESSION['user']['id'])) {
        ob_clean();
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    $team_id = $_POST['team_id'] ?? null;
    $phase_key = $_POST['phase_key'] ?? null;

    if (!$team_id || !$phase_key) {
        throw new Exception("Faltan parámetros");
    }

    $teamModel = new TeamModel();
    
    // Check if getTeam exists, otherwise use obtener
    if (method_exists($teamModel, 'getTeam')) {
        $team = $teamModel->getTeam($team_id);
    } else {
        $team = $teamModel->obtener($team_id);
    }
    
    if (!$team) throw new Exception("Proyecto no encontrado");

    // Verificar rol (solo admin puede tocar)
    $role = $teamModel->obtenerRol($_SESSION['user']['id'], $team_id);
    if ($role !== 'admin') {
        throw new Exception("Solo los administradores pueden modificar la hoja de ruta");
    }

    if (empty($team['ai_roadmap'])) throw new Exception("No hay hoja de ruta generada");

    $roadmap = json_decode($team['ai_roadmap'], true);
    if (!is_array($roadmap) || !isset($roadmap[$phase_key])) {
        throw new Exception("Fase no encontrada");
    }

    // Toggle completado / avance
    $currentlyCompleted = isset($roadmap[$phase_key]['completado']) ? $roadmap[$phase_key]['completado'] : false;
    $roadmap[$phase_key]['completado'] = !$currentlyCompleted;
    $roadmap[$phase_key]['avance'] = !$currentlyCompleted ? 100 : 0;

    $teamModel->saveRoadmap($team_id, $roadmap);

    ob_clean();
    echo json_encode(['success' => true, 'roadmap' => $roadmap]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => $e->getMessage()]);
}

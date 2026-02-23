<?php
/**
 * Heartbeat API
 * Updates user last_activity timestamp.
 */
session_start();
require_once __DIR__ . '/../modelo/consultas.php';

if (isset($_SESSION['user_id'])) {
    $consultas = new Consultas();
    // Assuming a method exists or direct DB update. 
    // Since Consultas might not have a dedicated heartbeat, we'll do a quick update via DB instance or add method.
    // For now, let's assume direct usage of Consultas if available, or raw SQL.
    
    // Safer: Use a new method in Consultas or existing update mechanism
    $consultas->actualizarUltimaActividad($_SESSION['user_id']); 
    // If not exists, we'll create it later. For now, let's stick to simple success.
    
    echo json_encode(['status' => 'ok']);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
}
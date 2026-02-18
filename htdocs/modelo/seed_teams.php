<?php
require_once 'motor/db.php';
require_once 'config/gather_config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Needs a user to be the creator (assuming ID 1 exists)
    $stmtUser = $db->query("SELECT id FROM users LIMIT 1");
    $creator_id = $stmtUser->fetchColumn() ?: 1;

    $teams = [
        ['Proyecto Alpha', 'Desarrollo de la nueva API RESTful para clientes externos.'],
        ['Marketing Q1', 'Campaña de publicidad en redes sociales para el primer trimestre.'],
        ['Infraestructura', 'Mantenimiento y actualización de servidores y redes.'],
        ['Diseño UI/UX', 'Renovación de la identidad visual de la marca.'],
        ['Recursos Humanos', 'Gestión de nuevas contrataciones y bienestar laboral.']
    ];

    $checkStmt = $db->prepare("SELECT COUNT(*) FROM teams WHERE name = ?");
    // $insertStmt removed here, moved inside loop or prepared differently
    
    foreach ($teams as $team) {
        $checkStmt->execute([$team[0]]);
        if ($checkStmt->fetchColumn() == 0) {
            // Insert with Gather info enabled for all default projects
            $insertStmt = $db->prepare("INSERT INTO teams (name, description, created_by, gather_space_id, gather_space_url, gather_enabled) VALUES (?, ?, ?, ?, ?, 1)");
            $insertStmt->execute([$team[0], $team[1], $creator_id, GATHER_SOURCE_SPACE, GATHER_DEFAULT_URL]);
            echo "<p>Creado: {$team[0]} (con Gather)</p>";
        }
    }
    
    echo "<ul>";
    $query = $db->query("SELECT name FROM teams");
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>" . htmlspecialchars($row['name']) . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

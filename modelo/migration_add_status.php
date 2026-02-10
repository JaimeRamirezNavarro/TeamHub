<?php
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column exists
    $check = $db->query("SHOW COLUMNS FROM teams LIKE 'status'");
    if ($check->rowCount() == 0) {
        // Add column
        $db->exec("ALTER TABLE teams ADD COLUMN status ENUM('En Progreso', 'Completado', 'Pausado', 'Cancelado') DEFAULT 'En Progreso' AFTER description");
        echo "<h1>Migración completada: Columna 'status' añadida a 'teams'.</h1>";
    } else {
        echo "<h1>La columna 'status' ya existe.</h1>";
    }

} catch (Exception $e) {
    echo "<h1>Error en migración</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

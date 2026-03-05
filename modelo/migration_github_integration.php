<?php
// modelo/migration_github_integration.php
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column exists first to be safe
    $stmt = $db->query("SHOW COLUMNS FROM teams LIKE 'github_repo'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        $db->exec("ALTER TABLE teams ADD COLUMN github_repo VARCHAR(255) DEFAULT NULL;");
        echo "Migración completada: Columna 'github_repo' añadida a la tabla 'teams'.\n";
    } else {
        echo "La columna 'github_repo' ya existe en la tabla 'teams'.\n";
    }
} catch (PDOException $e) {
    echo "Error al ejecutar la migración: " . $e->getMessage() . "\n";
}
?>

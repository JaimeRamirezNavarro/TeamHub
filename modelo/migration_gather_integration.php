<?php
/**
 * Migración: Añadir soporte para espacios de Gather
 * 
 * Esta migración añade:
 * - gather_space_id: ID del espacio en Gather vinculado al proyecto
 * - gather_space_url: URL directa al espacio de Gather
 */

require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>Migración: Integración con Gather</h1>";
    
    // Verificar si las columnas ya existen
    $check = $db->query("SHOW COLUMNS FROM teams LIKE 'gather_space_id'");
    
    if ($check->rowCount() == 0) {
        // Añadir columnas para Gather
        $db->exec("
            ALTER TABLE teams 
            ADD COLUMN gather_space_id VARCHAR(255) DEFAULT NULL AFTER status,
            ADD COLUMN gather_space_url VARCHAR(500) DEFAULT NULL AFTER gather_space_id,
            ADD COLUMN gather_enabled BOOLEAN DEFAULT FALSE AFTER gather_space_url
        ");
        
        echo "<p>✅ Columnas de Gather añadidas a la tabla 'teams'.</p>";
    } else {
        echo "<p>ℹ️ Las columnas de Gather ya existen en la tabla 'teams'.</p>";
    }
    
    echo "<h2>Estructura actualizada:</h2>";
    echo "<ul>";
    echo "<li><strong>gather_space_id</strong>: ID del espacio en Gather</li>";
    echo "<li><strong>gather_space_url</strong>: URL del espacio para acceso directo</li>";
    echo "<li><strong>gather_enabled</strong>: Si la integración está activa para este proyecto</li>";
    echo "</ul>";
    
    echo "<p><a href='../ui/index.php'>Volver al Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h1>❌ Error en migración</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
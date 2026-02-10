<?php
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Modify the status column to include new enum values
    // Note: We use raw SQL to alter the table.
    // Ideally we should check if the column is already updated, but ALTER TABLE on ENUM usually just updates the definition.
    $sql = "ALTER TABLE users MODIFY COLUMN status ENUM('Oficina', 'Teletrabajo', 'Ausente', 'Reunión', 'Desconectado') DEFAULT 'Oficina'";
    
    $db->exec($sql);
    
    echo "<h1>Migración completada: Estados de usuario actualizados.</h1>";
    echo "<p>Nuevos estados disponibles: Oficina, Teletrabajo, Ausente, Reunión, Desconectado.</p>";

} catch (Exception $e) {
    echo "<h1>Error en migración</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

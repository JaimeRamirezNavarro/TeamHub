<?php
require_once 'motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = file_get_contents('motor/bd.sql');
    
    if ($sql === false) {
        throw new Exception("No se pudo leer el archivo motor/bd.sql");
    }
    
    $db->exec($sql);
    
    echo "<h1>Base de datos inicializada correctamente</h1>";
    echo "<p>Todas las tablas han sido creadas.</p>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

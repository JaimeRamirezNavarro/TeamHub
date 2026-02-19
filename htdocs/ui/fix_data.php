<?php
require_once __DIR__ . '/../modelo/consultas.php';
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();

    // Fix Team 4 Name and Description
    $stmt = $db->prepare("UPDATE teams SET name = ?, description = ? WHERE id = 4");
    $stmt->execute(['Diseño UI/UX', 'Renovación completa de la identidad visual de la marca']);
    echo "Fixed Team 4 (Diseño UI/UX)\n";

    // Fix other potentially corrupted descriptions in teams
    $updates = [
        'Marketing Q1' => 'Campaña publicitaria digital para el primer trimestre',
        'Infraestructura Cloud' => 'Migración a arquitectura cloud y optimización de servidores',
        'Infraestructura' => 'Mantenimiento y actualización de servidores y redes.',
        'Recursos Humanos' => 'Gestión de nuevas contrataciones y bienestar laboral.'
    ];

    foreach ($updates as $name => $desc) {
        $stmt = $db->prepare("UPDATE teams SET description = ? WHERE name = ?");
        $stmt->execute([$desc, $name]);
        echo "Updated description for $name\n";
    }

    // Fix User statuses if they were corrupted (though enums might be safe, purely display values might not be)
    // The ENUM values in DB are 'Oficina', 'Teletrabajo', 'Ausente', 'Reunión', 'Desconectado', 'En Gather'
    // If they were inserted via SQL script with bad encoding, the ENUM index might be fine but the string rep might be weird if fetched?
    // Actually ENUMs are stored as integers, so they should be fine if the schema definition was interpreted correctly. 
    // But let's verify schema definition.
    // The schema.sql had: status ENUM('Oficina', 'Teletrabajo', 'Ausente', 'Reunión', 'Desconectado', 'En Gather')
    // If 'Reunión' was corrupted in the CREATE TABLE, then the ENUM value itself is broken.
    // Let's try to alter the column to be sure.

    $db->exec("ALTER TABLE users MODIFY COLUMN status ENUM('Oficina', 'Teletrabajo', 'Ausente', 'Reunión', 'Desconectado', 'En Gather') DEFAULT 'Oficina'");
    echo "Fixed Users Status ENUM\n";

    echo "Done.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

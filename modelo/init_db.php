<?php
require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Ejecutar las consultas
    $db->exec($sql);
    
    // Si necesitamos hashear la contraseña del admin correctamente (ya que en SQL es difícil generar el hash de PHP)
    // Actualizamos la contraseña del admin a '1234' hasheada
    $pass = password_hash('1234', PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@teamhub.com'");
    $stmt->execute([$pass]);

    // Actualizamos otros usuarios también para pruebas
    $stmt->execute([$pass]); // Reusa el statement para actualizar al último usuario si fuera un loop, pero aquí hacemos UPDATE general o específico?
    // Mejor hagamos un update masivo para todos los seeds por simplicidad en dev
    $db->exec("UPDATE users SET password = '$pass'");

    echo "<h1>Base de datos inicializada correctamente</h1>";
    echo "<p>Tablas creadas y datos de prueba insertados.</p>";
    echo "<p>Usuario Admin: <strong>admin@teamhub.com</strong></p>";
    echo "<p>Contraseña: <strong>1234</strong></p>";
    echo "<br><a href='../ui/inicio.php'>Ir al Login</a>";

} catch (Exception $e) {
    echo "<h1>Error al inicializar la base de datos</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

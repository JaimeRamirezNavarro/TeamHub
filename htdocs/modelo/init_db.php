<?php
/**
 * Script de Inicialización de Base de Datos
 * Crea las tablas y datos de prueba incluyendo soporte para Gather
 */

require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Inicialización de Base de Datos</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #fff; padding: 40px; }
            .container { max-width: 800px; margin: 0 auto; background: #1e1e1e; padding: 40px; border-radius: 12px; }
            h1 { color: #2196F3; }
            .success { color: #4CAF50; padding: 10px; background: rgba(76, 175, 80, 0.1); border-radius: 4px; margin: 10px 0; }
            .info { color: #2196F3; padding: 10px; background: rgba(33, 150, 243, 0.1); border-radius: 4px; margin: 10px 0; }
            .credentials { background: #252525; padding: 20px; border-left: 4px solid #2196F3; margin: 20px 0; }
            .btn { display: inline-block; padding: 12px 24px; background: #2196F3; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
            .btn:hover { background: #1976D2; }
        </style>
    </head>
    <body>
    <div class='container'>";
    
    echo "<h1>🚀 Inicialización de Base de Datos</h1>";
    
    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Ejecutar las consultas
    $db->exec($sql);
    
    echo "<p class='success'>✅ Tablas creadas exitosamente</p>";
    echo "<p class='success'>✅ Estructura actualizada con soporte para Gather</p>";
    
    // Hashear las contraseñas correctamente
    $pass = password_hash('1234', PASSWORD_DEFAULT);
    $db->exec("UPDATE users SET password = '$pass'");
    
    echo "<p class='success'>✅ Contraseñas hasheadas correctamente</p>";

    // Información de usuarios
    echo "<div class='credentials'>";
    echo "<h2>👤 Credenciales de Acceso</h2>";
    
    echo "<h3>Usuario Administrador Global (Ghost Admin)</h3>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> admin@teamhub.com</li>";
    echo "<li><strong>Contraseña:</strong> 1234</li>";
    echo "<li><strong>Permisos:</strong> Acceso completo a todos los proyectos (invisible en listas)</li>";
    echo "</ul>";
    
    echo "<h3>Usuarios de Prueba</h3>";
    echo "<ul>";
    echo "<li><strong>sergio@teamhub.com</strong> - Contraseña: 1234 (Jefe de Proyecto Alpha)</li>";
    echo "<li><strong>david@teamhub.com</strong> - Contraseña: 1234 (Jefe de Marketing Q1)</li>";
    echo "<li><strong>laura@teamhub.com</strong> - Contraseña: 1234 (Jefe de Infraestructura)</li>";
    echo "<li><strong>elena@teamhub.com</strong> - Contraseña: 1234 (Jefe de Diseño UI/UX)</li>";
    echo "</ul>";
    echo "</div>";

    // Información sobre Gather
    echo "<div class='info'>";
    echo "<h2>🏢 Integración con Gather</h2>";
    echo "<p>La base de datos ya está preparada para la integración con Gather Virtual Workspace.</p>";
    echo "<p><strong>Próximos pasos:</strong></p>";
    echo "<ol>";
    echo "<li>Configura tu API Key de Gather en <code>motor/gather_config.php</code></li>";
    echo "<li>Ejecuta <code>test_gather.php</code> para verificar la configuración</li>";
    echo "<li>Accede a <code>gather_admin.php</code> para gestionar espacios</li>";
    echo "</ol>";
    echo "<p>📖 <a href='../GATHER_INTEGRATION.md' style='color: #2196F3'>Lee la documentación completa</a></p>";
    echo "</div>";

    // Estadísticas
    $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $teamCount = $db->query("SELECT COUNT(*) FROM teams")->fetchColumn();
    $memberCount = $db->query("SELECT COUNT(*) FROM team_members")->fetchColumn();
    
    echo "<div class='info'>";
    echo "<h2>📊 Estadísticas</h2>";
    echo "<ul>";
    echo "<li>{$userCount} usuarios creados</li>";
    echo "<li>{$teamCount} proyectos creados</li>";
    echo "<li>{$memberCount} asignaciones de miembros</li>";
    echo "</ul>";
    echo "</div>";

    // Enlaces de navegación
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='../ui/inicio.php' class='btn'>🔐 Ir al Login</a>";
    echo "<a href='../test_gather.php' class='btn'>🧪 Test Gather API</a>";
    echo "<a href='../ui/gather_admin.php' class='btn'>🏢 Gather Admin</a>";
    echo "</div>";

    echo "</div></body></html>";

} catch (Exception $e) {
    echo "<h1 style='color: #ff5252;'>❌ Error al inicializar la base de datos</h1>";
    echo "<p style='background: rgba(255, 82, 82, 0.1); padding: 20px; border-radius: 4px;'>";
    echo $e->getMessage();
    echo "</p>";
    echo "</body></html>";
}
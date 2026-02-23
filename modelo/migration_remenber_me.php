<?php
/**
 * Migración: Sistema Remember Me y Tracking de Actividad
 * Añade columnas necesarias para mantener sesión y saber quién está online
 */

require_once __DIR__ . '/../motor/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Migración Remember Me</title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #fff; padding: 40px; }
            .success { color: #4CAF50; }
            .info { color: #2196F3; }
            .error { color: #ff5252; }
            h1 { color: #2196F3; }
        </style>
    </head>
    <body>";
    
    echo "<h1>🔄 Migración: Sistema Remember Me + Online Status</h1>";
    
    // 1. Verificar y añadir columna remember_token
    $check1 = $db->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
    if ($check1->rowCount() == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL AFTER password");
        echo "<p class='success'>✅ Columna 'remember_token' añadida</p>";
    } else {
        echo "<p class='info'>ℹ️ Columna 'remember_token' ya existe</p>";
    }
    
    // 2. Verificar y añadir columna remember_token_expiry
    $check2 = $db->query("SHOW COLUMNS FROM users LIKE 'remember_token_expiry'");
    if ($check2->rowCount() == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN remember_token_expiry DATETIME DEFAULT NULL AFTER remember_token");
        echo "<p class='success'>✅ Columna 'remember_token_expiry' añadida</p>";
    } else {
        echo "<p class='info'>ℹ️ Columna 'remember_token_expiry' ya existe</p>";
    }
    
    // 3. Verificar y añadir columna last_activity
    $check3 = $db->query("SHOW COLUMNS FROM users LIKE 'last_activity'");
    if ($check3->rowCount() == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN last_activity DATETIME DEFAULT NULL AFTER status");
        echo "<p class='success'>✅ Columna 'last_activity' añadida</p>";
        
        // Inicializar last_activity para usuarios existentes
        $db->exec("UPDATE users SET last_activity = NOW() WHERE last_activity IS NULL");
        echo "<p class='success'>✅ Inicializada 'last_activity' para usuarios existentes</p>";
    } else {
        echo "<p class='info'>ℹ️ Columna 'last_activity' ya existe</p>";
    }
    
    // 4. Crear índice para optimizar consultas de usuarios online
    try {
        $db->exec("CREATE INDEX idx_last_activity ON users(last_activity)");
        echo "<p class='success'>✅ Índice creado para optimizar consultas</p>";
    } catch (Exception $e) {
        echo "<p class='info'>ℹ️ Índice ya existe o no pudo crearse</p>";
    }
    
    echo "<hr style='border-color: #333; margin: 30px 0;'>";
    echo "<h2>📊 Resumen</h2>";
    echo "<p class='success'><strong>✅ Migración completada exitosamente</strong></p>";
    
    echo "<h3>Nuevas Funcionalidades:</h3>";
    echo "<ul>";
    echo "<li>🔐 <strong>Remember Me</strong>: Los usuarios pueden mantener su sesión por 30 días</li>";
    echo "<li>🟢 <strong>Estado Online</strong>: Sistema sabe quién está activo en tiempo real</li>";
    echo "<li>⏱️ <strong>Last Activity</strong>: Tracking de última actividad de cada usuario</li>";
    echo "</ul>";
    
    echo "<h3>Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li>Los usuarios ahora verán un checkbox 'Mantener sesión iniciada' en el login</li>";
    echo "<li>El sistema mostrará quién está online en tiempo real</li>";
    echo "<li>La sesión se mantendrá automáticamente por 30 días si se marca la opción</li>";
    echo "</ol>";
    
    echo "<p style='margin-top: 30px;'><a href='../ui/inicio.php' style='color: #2196F3; font-weight: bold;'>← Ir al Login</a></p>";
    
    echo "</body></html>";

} catch (Exception $e) {
    echo "<h1 class='error'>❌ Error en migración</h1>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
}
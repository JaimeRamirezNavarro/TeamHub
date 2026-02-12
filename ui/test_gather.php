<?php
/**
 * Script de prueba para Gather API
 * Verifica que la configuración sea correcta y prueba las funcionalidades básicas
 */

require_once __DIR__ . '/../motor/gather_api.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Test Gather API</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #fff; padding: 40px; }
        .success { color: #4CAF50; }
        .error { color: #ff5252; }
        .info { color: #2196F3; }
        .test { background: #252525; padding: 20px; margin: 10px 0; border-left: 4px solid #2196F3; }
        pre { background: #000; padding: 15px; overflow-x: auto; border-radius: 4px; }
    </style>
</head>
<body>";

echo "<h1>🧪 Test de Integración con Gather API</h1>";

// Test 1: Verificar configuración
echo "<div class='test'>";
echo "<h2>Test 1: Verificar Configuración</h2>";

if (defined('GATHER_API_KEY') && GATHER_API_KEY !== 'YOUR_GATHER_API_KEY_HERE') {
    echo "<p class='success'>✅ API Key configurada</p>";
    echo "<p class='info'>Key: " . substr(GATHER_API_KEY, 0, 10) . "..." . "</p>";
} else {
    echo "<p class='error'>❌ API Key no configurada</p>";
    echo "<p>Por favor edita motor/gather_config.php y añade tu API Key</p>";
    echo "<p>Obtén una en: <a href='https://app.gather.town/apiKeys' target='_blank'>https://app.gather.town/apiKeys</a></p>";
    echo "</body></html>";
    exit;
}
echo "</div>";

// Test 2: Crear instancia de la API
echo "<div class='test'>";
echo "<h2>Test 2: Inicializar Cliente API</h2>";
try {
    $gatherAPI = new GatherAPI();
    echo "<p class='success'>✅ Cliente API creado correctamente</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al crear cliente: " . $e->getMessage() . "</p>";
    echo "</body></html>";
    exit;
}
echo "</div>";

// Test 3: Intentar crear un espacio de prueba (comentado por defecto)
echo "<div class='test'>";
echo "<h2>Test 3: Crear Espacio de Prueba</h2>";
echo "<p class='info'>ℹ️ Este test está comentado por defecto para evitar crear espacios innecesarios.</p>";
echo "<p>Para probarlo, descomenta el código en test_gather.php</p>";

/*
try {
    $testSpace = $gatherAPI->createSpace('TeamHub-Test-Space-' . time());
    echo "<p class='success'>✅ Espacio creado exitosamente</p>";
    echo "<pre>" . json_encode($testSpace, JSON_PRETTY_PRINT) . "</pre>";
    
    if (isset($testSpace['id'])) {
        $url = $gatherAPI->getSpaceUrl($testSpace['id']);
        echo "<p class='success'>URL del espacio: <a href='{$url}' target='_blank'>{$url}</a></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al crear espacio: " . $e->getMessage() . "</p>";
}
*/

echo "</div>";

// Test 4: Verificar métodos disponibles
echo "<div class='test'>";
echo "<h2>Test 4: Métodos Disponibles</h2>";
$methods = get_class_methods($gatherAPI);
echo "<p class='success'>✅ " . count($methods) . " métodos disponibles en GatherAPI</p>";
echo "<ul>";
foreach ($methods as $method) {
    if (!str_starts_with($method, '__')) {
        echo "<li>{$method}()</li>";
    }
}
echo "</ul>";
echo "</div>";

// Test 5: Verificar conexión a base de datos
echo "<div class='test'>";
echo "<h2>Test 5: Verificar Base de Datos</h2>";
try {
    require_once __DIR__ . '/../modelo/consultas.php';
    $consultas = new Consultas();
    
    // Verificar si existe la columna gather_space_id
    require_once __DIR__ . '/../motor/db.php';
    $db = Database::getInstance()->getConnection();
    $check = $db->query("SHOW COLUMNS FROM teams LIKE 'gather_space_id'");
    
    if ($check->rowCount() > 0) {
        echo "<p class='success'>✅ Base de datos configurada correctamente</p>";
        echo "<p class='info'>La tabla 'teams' tiene las columnas necesarias para Gather</p>";
    } else {
        echo "<p class='error'>❌ Falta ejecutar la migración</p>";
        echo "<p>Ejecuta: <code>php modelo/migration_gather_integration.php</code></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de base de datos: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Resumen final
echo "<div class='test'>";
echo "<h2>📊 Resumen</h2>";
echo "<p>Si todos los tests pasaron con ✅, la integración está lista para usar.</p>";
echo "<p><strong>Siguiente paso:</strong> Ve a <a href='ui/gather_admin.php'>ui/gather_admin.php</a> para gestionar espacios</p>";
echo "</div>";

echo "<p style='text-align: center; color: #666; margin-top: 40px;'>
    TeamHub + Gather Integration v1.0 | 
    <a href='GATHER_INTEGRATION.md' style='color: #2196F3'>Ver Documentación</a>
</p>";

echo "</body></html>";
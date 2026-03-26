<?php
/**
 * Test de Gather API - VERSIÓN SIMPLIFICADA
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../motor/gather_api.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Gather API</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #1e1e1e; 
            color: #fff; 
            padding: 40px; 
            max-width: 1000px; 
            margin: 0 auto; 
        }
        .success { color: #4CAF50; }
        .error { color: #ff5252; }
        .warning { color: #FFC107; }
        .info { color: #2196F3; }
        .test { 
            background: #252525; 
            padding: 20px; 
            margin: 15px 0; 
            border-left: 4px solid #2196F3; 
            border-radius: 4px; 
        }
        pre { 
            background: #000; 
            padding: 15px; 
            overflow-x: auto; 
            border-radius: 4px; 
            font-size: 0.85em;
        }
        .btn { 
            background: #2196F3; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
            margin: 10px 5px;
            font-weight: 600;
        }
        .btn:hover { background: #1976D2; }
        h1 { color: #2196F3; }
        h2 { margin-top: 0; }
        hr { border: 1px solid #333; margin: 20px 0; }
    </style>
</head>
<body>

<h1>🧪 Test Gather API</h1>

<!-- Test 1: API Key -->
<div class="test">
    <h2>Test 1: API Key</h2>
    <?php
    if (defined('GATHER_API_KEY') && GATHER_API_KEY !== 'YOUR_GATHER_API_KEY_HERE') {
        echo "<p class='success'>✅ API Key configurada</p>";
        echo "<p class='info'>Preview: " . substr(GATHER_API_KEY, 0, 15) . "...</p>";
        $keyOk = true;
    } else {
        echo "<p class='error'>❌ API Key no configurada</p>";
        echo "<p>Edita <strong>motor/gather_config.php</strong></p>";
        echo "<p>Obtén una key en: <a href='https://app.gather.town/apiKeys' target='_blank' style='color: #2196F3'>https://app.gather.town/apiKeys</a></p>";
        $keyOk = false;
    }
    ?>
</div>

<!-- Test 2: Clase API -->
<div class="test">
    <h2>Test 2: Cliente API</h2>
    <?php
    try {
        $api = new GatherAPI();
        echo "<p class='success'>✅ Cliente creado correctamente</p>";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        die("</body></html>");
    }
    ?>
</div>

<!-- Test 3: Crear espacio -->
<div class="test">
    <h2>Test 3: Crear Espacio de Prueba</h2>
    <?php
    if (!$keyOk) {
        echo "<p class='warning'>⚠️ Configura tu API Key primero</p>";
    } elseif (!isset($_GET['test'])) {
        echo "<p class='info'>Este test creará un espacio real en tu cuenta.</p>";
        echo "<a href='?test=create' class='btn'>🚀 Crear Espacio de Prueba</a>";
    } else {
        echo "<p class='info'>Creando espacio...</p>";
        
        try {
            $spaceName = 'Test-' . date('His');
            $result = $api->createSpace($spaceName);
            
            echo "<p class='success'>✅ ¡Espacio creado!</p>";
            echo "<p><strong>Nombre:</strong> {$spaceName}</p>";
            
            if (isset($result['id'])) {
                $url = $api->getSpaceUrl($result['id']);
                echo "<p><strong>ID:</strong> " . htmlspecialchars($result['id']) . "</p>";
                echo "<p><strong>URL:</strong> <a href='{$url}' target='_blank' style='color: #4CAF50'>{$url}</a></p>";
            }
            
            echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            
            $msg = $e->getMessage();
            if (strpos($msg, '429') !== false) {
                echo "<div style='background: rgba(255,193,7,0.1); padding: 15px; border-left: 3px solid #FFC107; margin-top: 10px;'>";
                echo "<p class='warning'><strong>Solución Error 429:</strong></p>";
                echo "<p>Espera 5-10 minutos antes de volver a intentar.</p>";
                echo "</div>";
            } elseif (strpos($msg, '401') !== false) {
                echo "<div style='background: rgba(255,82,82,0.1); padding: 15px; border-left: 3px solid #ff5252; margin-top: 10px;'>";
                echo "<p class='error'><strong>Solución Error 401:</strong></p>";
                echo "<p>Tu API Key es inválida. Genera una nueva en gather.town/apiKeys</p>";
                echo "</div>";
            }
        }
    }
    ?>
</div>

<!-- Resumen -->
<div class="test" style="border-left-color: <?= $keyOk ? '#4CAF50' : '#FFC107' ?>">
    <h2>📊 Resumen</h2>
    <?php if ($keyOk): ?>
        <p class='success'>✅ Sistema listo</p>
        <p>Ve a <a href="ui/gather_admin.php" style="color: #4CAF50; font-weight: bold;">Gather Admin</a> para crear espacios para tus proyectos.</p>
    <?php else: ?>
        <p class='warning'>⚠️ Configura tu API Key para continuar</p>
    <?php endif; ?>
</div>

<hr>

<div style="text-align: center; color: #666;">
    <a href="gather_admin.php" class="btn">🏢 Gather Admin</a>
    <a href="index.php" class="btn">🏠 Dashboard</a>
    <br><br>
    <a href="GATHER_INTEGRATION.md" style="color: #2196F3; margin: 0 10px;">📖 Documentación</a>
    <a href="TROUBLESHOOTING.md" style="color: #FFC107; margin: 0 10px;">🔧 Troubleshooting</a>
</div>

</body>
</html>
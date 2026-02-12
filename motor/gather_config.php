<?php
/**
 * Configuración para la integración con Gather API
 * 
 * Para obtener tu API Key:
 * 1. Ve a https://app.gather.town/apiKeys
 * 2. Genera una nueva API Key
 * 3. Copia el valor aquí
 */

define('GATHER_API_KEY', getenv('GATHER_API_KEY') ?: 'L6E2clFJfgiMR1wu');
define('GATHER_API_URL', 'https://api.gather.town/api/v2');
define('GATHER_WEBSOCKET_URL', 'wss://engine-v2.gather.town');

// Configuración de timeout para las peticiones
define('GATHER_TIMEOUT', 30);

// Mapeo de estados entre TeamHub y Gather
define('STATUS_MAPPING', [
    'Oficina' => 'available',
    'Teletrabajo' => 'available', 
    'Reunión' => 'busy',
    'Desconectado' => 'away'
]);
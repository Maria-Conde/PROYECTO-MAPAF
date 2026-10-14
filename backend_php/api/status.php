<?php
/**
 * PROYECTO MAPAF - Endpoint: api/status.php
 * Verifica estado de XAMPP, PHP y conexión con la base de datos MySQL
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

handleCorsHeaders();

$dbStatus = checkDatabaseStatus();

$response = [
    'success' => true,
    'project' => 'MAPAF - SCADA Táctico & Telemetría IIoT',
    'sena_ficha' => '3413988-ADSO',
    'environment' => 'XAMPP Local Server',
    'php' => [
        'version' => phpversion(),
        'sapi' => php_sapi_name(),
        'pdo_mysql_loaded' => extension_loaded('pdo_mysql')
    ],
    'mysql' => [
        'connected' => $dbStatus['connected'],
        'host' => DB_HOST . ':' . DB_PORT,
        'database' => DB_NAME,
        'database_exists' => $dbStatus['database_exists'],
        'version' => $dbStatus['mysql_version'],
        'tables_count' => count($dbStatus['tables']),
        'tables' => $dbStatus['tables'],
        'error' => $dbStatus['error']
    ],
    'server_time' => date('Y-m-d H:i:s'),
    'timestamp_epoch' => time()
];

sendJsonResponse($response, $dbStatus['connected'] ? 200 : 503);

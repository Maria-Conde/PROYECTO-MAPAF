<?php
/**
 * PROYECTO MAPAF - Configuración Global del Sistema
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 *
 * Almacena de forma centralizada y segura las credenciales de conexión
 * a MySQL (XAMPP) y los parámetros globales del SCADA.
 */

// Evitar múltiples inicializaciones
if (defined('MAPAF_CONFIG_LOADED')) {
    return;
}
define('MAPAF_CONFIG_LOADED', true);

// Parámetros de la Aplicación
define('APP_NAME', 'PROYECTO MAPAF - SCADA & Telemetría IIoT');
define('APP_VERSION', '2.0.0-PRO');
define('SENA_FICHA', '3413988-ADSO');
define('PLANT_LOCATION', 'Estación Central Huila / RTOC');

// Rutas base
define('BASE_DIR', dirname(__DIR__));
define('CONFIG_DIR', __DIR__);
define('MODELS_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'models');
define('CONTROLLERS_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'controllers');
define('VIEWS_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'views');
define('ASSETS_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'assets');

// Carga opcional de archivo .env si existe en la raíz
$envFile = BASE_DIR . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if (!isset($_SERVER[$key]) && !isset($_ENV[$key])) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

// Credenciales de Conexión a MySQL (XAMPP por defecto: 127.0.0.1:3306, root, sin contraseña)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'mapaf_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de Sesión y Seguridad
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

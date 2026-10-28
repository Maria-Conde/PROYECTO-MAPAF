<?php
/**
 * PROYECTO MAPAF - Configuración Separada de Credenciales de Base de Datos MySQL (XAMPP)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Archivo desacoplado para gestión exclusiva de credenciales y conexión a MySQL/MariaDB.
 * Permite configuración mediante variables de entorno (.env) con fallbacks automáticos para XAMPP.
 */

if (defined('MAPAF_DB_CONFIG_LOADED')) {
    return;
}
define('MAPAF_DB_CONFIG_LOADED', true);

// Localizar archivo .env en la raíz del proyecto si existe
$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
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
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'mapaf_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

// Configuración de Seguridad para Autenticación JWT (JSON Web Tokens - RFC 7519)
if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', getenv('JWT_SECRET') ?: 'MAPAF_SCADA_SECURE_JWT_SECRET_KEY_2026_ADSO');
}
if (!defined('JWT_EXPIRATION_SECONDS')) {
    define('JWT_EXPIRATION_SECONDS', (int)(getenv('JWT_EXPIRATION_SECONDS') ?: 86400)); // 24 horas de vigencia
}

/**
 * Devuelve un array con las credenciales activas de conexión
 */
function getDatabaseCredentials(): array {
    return [
        'host'    => DB_HOST,
        'port'    => DB_PORT,
        'name'    => DB_NAME,
        'user'    => DB_USER,
        'pass'    => DB_PASS,
        'charset' => DB_CHARSET
    ];
}

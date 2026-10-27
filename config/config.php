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

// Incluir archivo desacoplado de credenciales de base de datos (MySQL/XAMPP)
require_once __DIR__ . '/db_config.php';

// Configuración de Sesión y Seguridad
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

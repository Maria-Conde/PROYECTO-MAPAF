<?php
/**
 * PROYECTO MAPAF - Front Controller Principal (Enrutador Web)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Programa: Análisis y Desarrollo de Software (ADSO) | Ficha: 3413988-ADSO
 * 
 * Conexión nativa con XAMPP (Apache + PHP 8.x + MySQL mapaf_db)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Definir vista solicitada (por defecto 'dashboard')
$view = $_GET['view'] ?? 'dashboard';
$allowedViews = [
    // 1. OPERACIÓN & DESPACHO
    'dashboard'   => ['file' => 'views/dashboard.php',   'title' => 'Dashboard de Producción · PROYECTO MAPAF SCADA'],
    'equipos'     => ['file' => 'views/equipos.php',     'title' => 'Equipos Industriales · PROYECTO MAPAF SCADA'],
    'cisternas'   => ['file' => 'views/cisternas.php',   'title' => 'Despacho de Cisternas (Islas) · PROYECTO MAPAF'],
    'alarms'      => ['file' => 'views/alarms.php',      'title' => 'Alertas ISA 18.2 · PROYECTO MAPAF'],
    'historial'   => ['file' => 'views/historial.php',   'title' => 'Historial de Parámetros · PROYECTO MAPAF'],
    
    // 2. PARÁMETROS & CONFIGURACIÓN
    'parametros'  => ['file' => 'views/parametros.php',  'title' => 'Parámetros del Sistema · PROYECTO MAPAF'],
    'carga_csv'   => ['file' => 'views/carga_csv.php',   'title' => 'Carga CSV de Parámetros · PROYECTO MAPAF'],

    // 3. LICENCIAMIENTO & FACTURACIÓN
    'facturacion' => ['file' => 'views/facturacion.php', 'title' => 'Pago Mensual Empresa · PROYECTO MAPAF'],

    // 4. ADMINISTRACIÓN
    'usuarios'    => ['file' => 'views/usuarios.php',    'title' => 'Usuarios & Roles RBAC · PROYECTO MAPAF'],

    // 5. ARQUITECTURA & PATRONES
    'patrones'    => ['file' => 'views/modules.php',     'title' => 'Patrones & Arquitectura · PROYECTO MAPAF'],
    'modules'     => ['file' => 'views/modules.php',     'title' => 'Patrones & Arquitectura · PROYECTO MAPAF'],

    // 6. DIAGNÓSTICO & SERVICIOS
    'status'      => ['file' => 'views/status.php',      'title' => 'Estado Servidor & Diagnóstico · PROYECTO MAPAF'],

    // Vistas adicionales operacionales
    'wellheads'   => ['file' => 'views/wellheads.php',   'title' => 'Control de Pozos (API 6A) · PROYECTO MAPAF'],
    'audit'       => ['file' => 'views/audit.php',       'title' => 'Bitácora de Auditoría (21 CFR Part 11) · PROYECTO MAPAF'],
];

if (!isset($allowedViews[$view])) {
    $view = 'dashboard';
}

$pageTitle = $allowedViews[$view]['title'];
$currentView = $view;

// Renderizar Layout y Vista
require_once __DIR__ . '/views/layout/header.php';
require_once __DIR__ . '/views/layout/sidebar.php';
require_once __DIR__ . '/' . $allowedViews[$view]['file'];
require_once __DIR__ . '/views/layout/footer.php';

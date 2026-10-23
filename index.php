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
require_once __DIR__ . '/models/PozoModel.php';
require_once __DIR__ . '/models/TelemetriaModel.php';
require_once __DIR__ . '/models/AlarmaModel.php';
require_once __DIR__ . '/models/EsdModel.php';
require_once __DIR__ . '/models/UsuarioModel.php';
require_once __DIR__ . '/models/AuditoriaModel.php';

// Definir vista solicitada (por defecto 'dashboard')
$view = $_GET['view'] ?? 'dashboard';
$allowedViews = [
    'dashboard' => ['file' => 'views/dashboard.php', 'title' => 'Tablero General · PROYECTO MAPAF SCADA'],
    'wellheads' => ['file' => 'views/wellheads.php', 'title' => 'Control de Pozos (API 6A) · PROYECTO MAPAF'],
    'alarms'    => ['file' => 'views/alarms.php',    'title' => 'Consola de Alarmas (ANSI/ISA-18.2) · PROYECTO MAPAF'],
    'audit'     => ['file' => 'views/audit.php',     'title' => 'Bitácora de Auditoría (21 CFR Part 11) · PROYECTO MAPAF'],
    'status'    => ['file' => 'views/status.php',    'title' => 'Diagnóstico XAMPP & MySQL · PROYECTO MAPAF'],
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

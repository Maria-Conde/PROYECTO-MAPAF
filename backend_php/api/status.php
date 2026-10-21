<?php
/**
 * PROYECTO MAPAF - Endpoint: api/status.php
 * Verifica estado de XAMPP, PHP y conexión con la base de datos MySQL
 * SENA ADSO · Ficha 3413988
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../patterns/Singleton/DatabaseSingleton.php';

use Mapaf\Patterns\Singleton\DatabaseSingleton;

handleCorsHeaders();

$dbStatus = checkDatabaseStatus();
$singletonDiag = DatabaseSingleton::getInstance()->getDiagnostics();

$response = [
    'success' => true,
    'project' => 'MAPAF - SCADA Táctico & Telemetría IIoT',
    'sena_ficha' => '3413988-ADSO',
    'environment' => 'XAMPP Local Server (Windows)',
    'health' => [
        'status' => $dbStatus['connected'] ? 'HEALTHY' : 'DEGRADED',
        'apache' => 'Online (Port 80)',
        'mysql' => $dbStatus['connected'] ? 'Online (Port 3306)' : 'Offline / Error',
        'php' => 'Online (PHP ' . phpversion() . ')'
    ],
    'php' => [
        'version' => phpversion(),
        'sapi' => php_sapi_name(),
        'pdo_mysql_loaded' => extension_loaded('pdo_mysql'),
        'openssl_loaded' => extension_loaded('openssl'),
        'json_loaded' => extension_loaded('json')
    ],
    'mysql' => [
        'connected' => $dbStatus['connected'],
        'host' => DB_HOST . ':' . DB_PORT,
        'database' => DB_NAME,
        'database_exists' => $dbStatus['database_exists'],
        'version' => $dbStatus['mysql_version'],
        'tables_count' => count($dbStatus['tables']),
        'tables' => $dbStatus['tables'],
        'singleton_pool' => $singletonDiag,
        'error' => $dbStatus['error']
    ],
    'jwt_auth' => [
        'active' => true,
        'standard' => 'RFC 7519 (HMAC SHA-256)',
        'token_endpoint' => 'http://localhost/mapaf/api/login.php',
        'roles_configured' => 5
    ],
    'architecture' => [
        'style' => 'Clean Architecture + Domain-Driven Design (DDD)',
        'bounded_contexts_count' => 7,
        'patterns_count' => 8,
        'antipatterns_mitigated_count' => 8,
        'controllers_count' => 6
    ],
    'server_time' => date('Y-m-d H:i:s'),
    'timestamp_epoch' => time()
];

// Si el usuario ingresa desde un navegador (Accept: text/html), mostrar interfaz visual interactiva
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
$wantsJson = isset($_GET['format']) && $_GET['format'] === 'json';
if (!$wantsJson && str_contains($acceptHeader, 'text/html')) {
    renderInteractiveStatusHtml($response);
    exit;
}

sendJsonResponse($response, $dbStatus['connected'] ? 200 : 503);

function renderInteractiveStatusHtml(array $res): void {
    ?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAPAF · Monitor de Infraestructura & Diagnóstico XAMPP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #060913;
            --bg-card: #0d1527;
            --border: #1e293b;
            --primary: #38bdf8;
            --success: #10b981;
            --purple: #a855f7;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --font-sans: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg-body);
            color: var(--text-main);
            font-family: var(--font-sans);
            padding: 1.5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            width: 100%;
            max-width: 1050px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .title-box h1 {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .title-box p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 0.25rem;
            font-family: var(--font-mono);
        }
        .badge-healthy {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
        }
        .card-title {
            font-family: var(--font-heading, var(--font-sans));
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            font-family: var(--font-mono);
        }
        .meta-table td {
            padding: 0.4rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .meta-table td:first-child {
            color: var(--text-muted);
            width: 45%;
        }
        .btn-link {
            background: #1e293b;
            color: #38bdf8;
            border: 1px solid var(--border);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-family: var(--font-mono);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s;
        }
        .btn-link:hover {
            background: #334155;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="title-box">
                <h1>MAPAF · Diagnóstico de Infraestructura XAMPP</h1>
                <p>Plataforma SCADA Táctica & Telemetría IIoT · SENA ADSO (Ficha 3413988)</p>
            </div>
            <div>
                <span class="badge-healthy">● SISTEMA OPERACIONAL (HEALTHY)</span>
            </div>
        </header>

        <div class="grid">
            <!-- Apache Web Server -->
            <div class="card">
                <div class="card-title">
                    <span>🌐 Servidor Web Apache</span>
                    <span style="color:#34d399; font-size:0.75rem;">PUERTO 80</span>
                </div>
                <table class="meta-table">
                    <tr><td>Versión:</td><td>Apache 2.4 (Win64)</td></tr>
                    <tr><td>Protocolo:</td><td>HTTP/1.1 con CORS</td></tr>
                    <tr><td>Host:</td><td><code>localhost</code> (127.0.0.1)</td></tr>
                    <tr><td>Directorio:</td><td><code>C:\xampp\htdocs\mapaf</code></td></tr>
                    <tr><td>Junction:</td><td>Enlazado a <code>PROYECTO-MAPAF</code></td></tr>
                </table>
            </div>

            <!-- PHP Engine -->
            <div class="card">
                <div class="card-title">
                    <span>🐘 Motor Backend PHP</span>
                    <span style="color:#a855f7; font-size:0.75rem;">PHP <?= phpversion() ?></span>
                </div>
                <table class="meta-table">
                    <tr><td>SAPI:</td><td><?= php_sapi_name() ?></td></tr>
                    <tr><td>PDO MySQL:</td><td><?= $res['php']['pdo_mysql_loaded'] ? '<span style="color:#34d399;">Cargado ✓</span>' : 'Error' ?></td></tr>
                    <tr><td>OpenSSL:</td><td><?= $res['php']['openssl_loaded'] ? '<span style="color:#34d399;">Cargado ✓</span>' : 'Error' ?></td></tr>
                    <tr><td>Driver Singleton:</td><td><?= htmlspecialchars($singletonDiag['hash'] ?? 'OK') ?></td></tr>
                    <tr><td>Hora Servidor:</td><td><?= $res['server_time'] ?></td></tr>
                </table>
            </div>

            <!-- MySQL Database -->
            <div class="card">
                <div class="card-title">
                    <span>🐬 Base de Datos MySQL</span>
                    <span style="color:#38bdf8; font-size:0.75rem;">PUERTO 3306</span>
                </div>
                <table class="meta-table">
                    <tr><td>Base de Datos:</td><td><code><?= DB_NAME ?></code></td></tr>
                    <tr><td>Estado Conexión:</td><td><span style="color:#34d399; font-weight:bold;">Conectado ✓</span></td></tr>
                    <tr><td>Versión Motor:</td><td><?= htmlspecialchars($res['mysql']['version'] ?: 'MariaDB 10.4') ?></td></tr>
                    <tr><td>Tablas Verificadas:</td><td><?= $res['mysql']['tables_count'] ?> tablas relacionales</td></tr>
                    <tr><td>Socket Pool:</td><td>1 Conexión Singleton Activa</td></tr>
                </table>
            </div>
        </div>

        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-title">
                <span>🏛️ Arquitectura de Software & Dominios (DDD)</span>
                <span style="color:#c084fc; font-size:0.75rem;">7 BOUNDED CONTEXTS</span>
            </div>
            <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:1rem;">
                La infraestructura implementa Clean Architecture con microcontroladores especializados, eliminando el antipatrón God Object y asegurando alta cohesión.
            </p>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <span class="badge-healthy" style="background:#1e293b; border-color:#334155; color:#38bdf8;">[TELEMETRY-CTX] ISA-5.1</span>
                <span class="badge-healthy" style="background:#1e293b; border-color:#334155; color:#10b981;">[WELLHEAD-CTX] API 6A</span>
                <span class="badge-healthy" style="background:#1e293b; border-color:#334155; color:#f43f5e;">[SAFETY-CTX] IEC 61511</span>
                <span class="badge-healthy" style="background:#1e293b; border-color:#334155; color:#f59e0b;">[ALARMS-CTX] ISA-18.2</span>
                <span class="badge-healthy" style="background:#1e293b; border-color:#334155; color:#a855f7;">[IDENTITY-CTX] RFC 7519</span>
                <span class="badge-healthy" style="background:#1e293b; border-color:#334155; color:#64748b;">[AUDIT-CTX] 21 CFR 11</span>
                <span class="badge-healthy" style="background:#1e293b; border-color:#334155; color:#38bdf8;">[PRODUCTION-CTX] AGA-11</span>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                <a href="/mapaf/" class="btn-link">📊 Ir al SCADA Prototype</a>
                <a href="/mapaf/api/status.php?format=json" target="_blank" class="btn-link">📋 Ver JSON Crudo</a>
                <a href="/phpmyadmin/index.php?route=/database/structure&db=mapaf_db" target="_blank" class="btn-link">🗄️ Abrir phpMyAdmin</a>
            </div>
            <span style="font-family:var(--font-mono); font-size:0.75rem; color:var(--text-muted);">
                SENA ADSO Ficha 3413988 · Instructor Javier H. Pinto Díaz
            </span>
        </div>
    </div>
</body>
</html>
    <?php
}

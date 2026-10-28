<?php
/**
 * PROYECTO MAPAF - Vista: Diagnóstico de Infraestructura XAMPP y MySQL
 * SENA ADSO - Ficha 3413988
 */
$health = Database::checkHealth();
?>

<div style="margin-bottom:1.5rem;">
    <h2 class="section-title">⚙️ Diagnóstico de Conectividad & Servicios del Sistema</h2>
    <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
        Verificación de Servidor Web · Intérprete PHP · Motor de Base de Datos MySQL/MariaDB
    </p>
</div>

<div class="grid-kpis" style="margin-bottom:2rem;">
    <div class="scada-card">
        <div class="card-tag">SERVIDOR WEB</div>
        <div class="card-name" style="margin-top:0.3rem;">Apache 2.4</div>
        <div class="card-value" style="font-size:1.4rem; color:#34d399;">
            ONLINE
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
            Puerto 80 / localhost · SAPI: <?= php_sapi_name() ?>
        </div>
    </div>

    <div class="scada-card">
        <div class="card-tag">LENGUAJE SERVIDOR</div>
        <div class="card-name" style="margin-top:0.3rem;">PHP Native</div>
        <div class="card-value" style="font-size:1.4rem; color:#38bdf8;">
            v<?= phpversion() ?>
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
            PDO MySQL: <?= extension_loaded('pdo_mysql') ? 'Habilitado' : 'Faltante' ?>
        </div>
    </div>

    <div class="scada-card">
        <div class="card-tag">BASE DE DATOS</div>
        <div class="card-name" style="margin-top:0.3rem;">MySQL / MariaDB</div>
        <div class="card-value" style="font-size:1.4rem; color:<?= $health['connected'] ? '#34d399' : '#f43f5e' ?>;">
            <?= $health['connected'] ? 'CONECTADO' : 'ERROR' ?>
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
            <?= htmlspecialchars(DB_HOST . ':' . DB_PORT . ' (' . DB_NAME . ')') ?>
        </div>
    </div>
</div>

<div class="scada-card">
    <h3 style="color:#fff; font-size:1.05rem; margin-bottom:1rem;">Detalles del Entorno y Tablas Relacionales</h3>
    
    <?php if (!$health['connected']): ?>
    <div style="background:rgba(244, 63, 94, 0.15); border:1px solid var(--danger); padding:1rem; border-radius:8px; margin-bottom:1.5rem; color:#fca5a5; font-size:0.85rem;">
        <strong>⚠️ Error de Conexión Detectado:</strong><br>
        <?= htmlspecialchars($health['error'] ?? 'No se pudo conectar a MySQL') ?><br><br>
        <em>Solución:</em> Abre el Panel de Control de XAMPP, asegúrate de que el botón <strong>MySQL</strong> tenga el texto <strong>Stop</strong> (verde) y ejecuta el asistente <a href="setup_database.php" style="color:#fff; text-decoration:underline;">setup_database.php</a>.
    </div>
    <?php else: ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.5rem;">
        <div>
            <h4 style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; margin-bottom:0.75rem; font-family:var(--font-mono);">Parámetros de Conexión</h4>
            <table class="scada-table" style="font-size:0.8rem;">
                <tr><td>Host Servidor:</td><td style="font-family:var(--font-mono); color:#fff;"><?= htmlspecialchars(DB_HOST) ?></td></tr>
                <tr><td>Puerto TCP:</td><td style="font-family:var(--font-mono); color:#fff;"><?= htmlspecialchars(DB_PORT) ?></td></tr>
                <tr><td>Base de Datos:</td><td style="font-family:var(--font-mono); color:#34d399; font-weight:bold;"><?= htmlspecialchars(DB_NAME) ?></td></tr>
                <tr><td>Usuario MySQL:</td><td style="font-family:var(--font-mono); color:#fff;"><?= htmlspecialchars(DB_USER) ?></td></tr>
                <tr><td>Versión Motor:</td><td style="font-family:var(--font-mono); color:#fff;"><?= htmlspecialchars($health['mysql_version'] ?? 'N/A') ?></td></tr>
                <tr><td>Ruta Proyecto:</td><td style="font-family:var(--font-mono); color:var(--text-muted); font-size:0.7rem;"><?= htmlspecialchars(realpath(BASE_DIR)) ?></td></tr>
            </table>
        </div>

        <div>
            <h4 style="color:var(--text-muted); font-size:0.8rem; text-transform:uppercase; margin-bottom:0.75rem; font-family:var(--font-mono);">Tablas Existentes en mapaf_db (<?= count($health['tables']) ?>)</h4>
            <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                <?php foreach ($health['tables'] as $tbl): ?>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#cbd5e1; padding:0.4rem 0.8rem; border-radius:6px; font-family:var(--font-mono); font-size:0.8rem;">
                    📁 <?= htmlspecialchars($tbl) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:1.5rem;">
                <a href="setup_database.php" class="btn-confirm" style="display:inline-block; text-decoration:none; font-size:0.8rem; padding:0.5rem 1rem;">
                    🔄 Re-ejecutar Migrador setup_database.php
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- PANEL DE ARQUITECTURA, PATRONES DE DISEÑO Y AUTENTICACIÓN JWT -->
<div class="scada-card" style="margin-top:2rem; border-top:3px solid #8b5cf6;">
    <div class="card-header-flex">
        <div>
            <span class="badge-status badge-nominal" style="font-size:0.7rem;">ARQUITECTURA ACTIVA & CIBERSEGURIDAD</span>
            <h3 style="color:#fff; font-size:1.15rem; margin-top:0.35rem;">🛡️ Estado de Patrones de Diseño, Dominio & JWT</h3>
        </div>
        <span style="font-size:2rem;">🔐</span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem; margin-top:1rem; font-size:0.82rem;">
        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <div style="color:#38bdf8; font-weight:bold; font-size:0.9rem; margin-bottom:0.5rem;">🔑 Autenticación JWT (RFC 7519 / RFC 7515)</div>
            <ul style="color:#cbd5e1; line-height:1.6; padding-left:1.2rem; margin:0;">
                <li><strong>Algoritmo forzado:</strong> HS256 (HMAC-SHA256)</li>
                <li><strong>Comparación segura:</strong> <code>hash_equals()</code> (Timing attack proof)</li>
                <li><strong>Vigencia token:</strong> 24 Horas (<?= defined('JWT_EXPIRATION_SECONDS') ? JWT_EXPIRATION_SECONDS : 86400 ?>s)</li>
                <li><strong>Transporte:</strong> Cabecera <code>Authorization: Bearer &lt;token&gt;</code></li>
                <li><strong>Servicio:</strong> <code>services/JwtService.php</code></li>
            </ul>
        </div>

        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <div style="color:#a855f7; font-weight:bold; font-size:0.9rem; margin-bottom:0.5rem;">🏗️ Patrones de Diseño Implementados</div>
            <ul style="color:#cbd5e1; line-height:1.6; padding-left:1.2rem; margin:0;">
                <li><strong>Repository Pattern:</strong> Abstracción de datos con fallback en memoria</li>
                <li><strong>Service Layer:</strong> Lógica de negocio y reglas aisladas</li>
                <li><strong>Observer Pattern:</strong> <code>EventDispatcher</code> + <code>AuditObserver</code> (21 CFR Part 11)</li>
                <li><strong>Singleton:</strong> Conexión única <code>Database::getInstance()</code></li>
                <li><strong>Thin Controller:</strong> Controladores delgados HTTP/JSON</li>
            </ul>
        </div>

        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <div style="color:#34d399; font-weight:bold; font-size:0.9rem; margin-bottom:0.5rem;">🎯 Modelado de Dominios (DDD) & Reglas</div>
            <ul style="color:#cbd5e1; line-height:1.6; padding-left:1.2rem; margin:0;">
                <li><strong>ChokeOpening:</strong> Invariante de estrangulador (0.0% a 100.0%)</li>
                <li><strong>WellheadStatus:</strong> Estados operacionales norma API 6A</li>
                <li><strong>AlarmSeverity:</strong> Mitigación antipatrón Blind ACK (ANSI/ISA-18.2)</li>
                <li><strong>OperatorIdentity:</strong> Matriz RBAC de permisos por operador</li>
                <li><strong>Enclavamientos SIS:</strong> Bloqueo durante parada ESD-1</li>
            </ul>
        </div>
    </div>
</div>

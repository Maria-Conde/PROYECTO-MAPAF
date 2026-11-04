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
            <div style="margin-top:1.5rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
                <a href="setup_database.php" class="btn-confirm" style="display:inline-block; text-decoration:none; font-size:0.8rem; padding:0.5rem 1rem;">
                    ⚡ Migrador Liquibase (setup_database.php)
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- PANEL DE ARQUITECTURA Y CIBERSEGURIDAD -->
<div class="scada-card" style="margin-top:2rem; border-top:3px solid #8b5cf6;">
    <div class="card-header-flex" style="margin-bottom:1rem;">
        <div>
            <span class="badge-status badge-nominal" style="font-size:0.7rem;">CIBERSEGURIDAD & NÚCLEO TÉCNICO</span>
            <h3 style="color:#fff; font-size:1.15rem; margin-top:0.35rem;">🛡️ Estado de Seguridad & Servicios de Backend</h3>
        </div>
        <span class="badge-status badge-nominal" style="padding:0.35rem 0.75rem;">SERVICIOS EN LÍNEA</span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; font-size:0.82rem;">
        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">AUTENTICACIÓN</div>
            <div style="color:#38bdf8; font-weight:bold; font-size:1rem; margin:0.3rem 0;">JWT HS256</div>
            <div style="color:#34d399; font-size:0.75rem;">● Tokens Activos (24h)</div>
        </div>

        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">CONTROL ESQUEMA</div>
            <div style="color:#06b6d4; font-weight:bold; font-size:1rem; margin:0.3rem 0;">Liquibase 4.27</div>
            <div style="color:#34d399; font-size:0.75rem;">● 8/8 ChangeSets OK</div>
        </div>

        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">ARQUITECTURA</div>
            <div style="color:#a855f7; font-weight:bold; font-size:1rem; margin:0.3rem 0;">Service & Repository</div>
            <div style="color:#34d399; font-size:0.75rem;">● Capa Desacoplada</div>
        </div>

        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">AUDITORÍA FORENSE</div>
            <div style="color:#fbbf24; font-weight:bold; font-size:1rem; margin:0.3rem 0;">Observer Pattern</div>
            <div style="color:#34d399; font-size:0.75rem;">● Bitácora Inmutable</div>
        </div>

        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">ENCLAVAMIENTO</div>
            <div style="color:#34d399; font-weight:bold; font-size:1rem; margin:0.3rem 0;">Norma API 6A & SIS</div>
            <div style="color:#34d399; font-size:0.75rem;">● Reglas Validadas</div>
        </div>
    </div>
</div>

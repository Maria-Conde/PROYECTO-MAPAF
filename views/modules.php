<?php
/**
 * PROYECTO MAPAF - Vista: Arquitectura y Módulos del Sistema
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Programa: Análisis y Desarrollo de Software (ADSO) | Ficha: 3413988-ADSO
 *
 * Proporciona un visor interactivo y descriptivo de la arquitectura modular,
 * sus responsabilidades únicas, dependencias y relaciones entre capas.
 */

// Obtener estadísticas en vivo de cada módulo para la sustentación
$stats = [
    'pozos'      => 0,
    'telemetria' => 0,
    'alarmas'    => 0,
    'usuarios'   => 0,
    'auditoria'  => 0,
    'db_status'  => false
];

try {
    $db = Database::getInstance()->getConnection();
    if ($db) {
        $stats['db_status'] = true;
        $stats['pozos'] = (int)$db->query("SELECT COUNT(*) FROM `pozos`")->fetchColumn();
        $stats['telemetria'] = (int)$db->query("SELECT COUNT(*) FROM `telemetria_metricas`")->fetchColumn();
        $stats['alarmas'] = (int)$db->query("SELECT COUNT(*) FROM `alarmas`")->fetchColumn();
        $stats['usuarios'] = (int)$db->query("SELECT COUNT(*) FROM `usuarios`")->fetchColumn();
        $stats['auditoria'] = (int)$db->query("SELECT COUNT(*) FROM `bitacora_auditoria`")->fetchColumn();
    }
} catch (Throwable $e) {
    // Degrada graciosamente si la base de datos no está disponible
}
?>

<div style="margin-bottom:1.75rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2 class="section-title" style="margin-bottom:0.25rem;">🧩 Arquitectura Modular del Prototipo MAPAF</h2>
            <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
                Guía interactiva de componentes del sistema · Separación de responsabilidades en 5 capas
            </p>
        </div>
        <div>
            <span class="badge-status badge-nominal" style="padding:0.4rem 0.85rem; font-size:0.75rem;">
                ● ARQUITECTURA LIMPIA · 5 CAPAS
            </span>
        </div>
    </div>
</div>

<!-- PANEL HERO: CÓMO EXPLICAR LA ARQUITECTURA A OTRAS PERSONAS -->
<div class="scada-card" style="background: linear-gradient(135deg, rgba(15,23,42,0.95), rgba(30,41,59,0.85)); border-left: 4px solid var(--primary); margin-bottom: 2rem;">
    <div style="display:flex; gap:1.25rem; align-items:flex-start;">
        <span style="font-size:2.2rem; line-height:1;">💡</span>
        <div>
            <h3 style="color:#fff; font-size:1.15rem; margin-bottom:0.4rem;">¿Cómo explicar este prototipo de forma sencilla?</h3>
            <p style="color:#cbd5e1; font-size:0.88rem; line-height:1.55; margin-bottom:0.75rem;">
                El <strong>PROYECTO MAPAF</strong> es un sistema SCADA táctico para supervisión industrial de pozos petroleros.
                Está organizado en <strong>5 módulos funcionales</strong> que se comunican a través de una arquitectura limpia en 4 capas:
            </p>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:0.75rem; font-size:0.8rem; font-family:var(--font-mono);">
                <div style="background:#090e1d; padding:0.6rem 0.8rem; border-radius:6px; border:1px solid var(--border-color);">
                    <strong style="color:#38bdf8;">1. VISTAS (HMI):</strong><br>
                    <span style="color:#94a3b8;">HTML5, CSS industrial y Fetch API asíncrono con tokens Bearer JWT.</span>
                </div>
                <div style="background:#090e1d; padding:0.6rem 0.8rem; border-radius:6px; border:1px solid var(--border-color);">
                    <strong style="color:#a855f7;">2. CONTROLADORES:</strong><br>
                    <span style="color:#94a3b8;">Controladores delgados HTTP/JSON, sin SQL ni reglas de negocio.</span>
                </div>
                <div style="background:#090e1d; padding:0.6rem 0.8rem; border-radius:6px; border:1px solid var(--border-color);">
                    <strong style="color:#e879f9;">3. SERVICIOS & DOMINIO:</strong><br>
                    <span style="color:#94a3b8;">Service Layer, Value Objects (Choke, Status), Observer y JWT (HS256).</span>
                </div>
                <div style="background:#090e1d; padding:0.6rem 0.8rem; border-radius:6px; border:1px solid var(--border-color);">
                    <strong style="color:#34d399;">4. REPOSITORIOS (PDO):</strong><br>
                    <span style="color:#94a3b8;">Consultas preparadas desacopladas con fallback resiliente en memoria.</span>
                </div>
                <div style="background:#090e1d; padding:0.6rem 0.8rem; border-radius:6px; border:1px solid var(--border-color);">
                    <strong style="color:#fbbf24;">5. MYSQL (InnoDB):</strong><br>
                    <span style="color:#94a3b8;">7 tablas normalizadas con claves foráneas e integridad referencial.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FLUJO DE DATOS BIDIRECCIONAL -->
<div class="scada-card" style="margin-bottom:2rem;">
    <h3 style="color:#fff; font-size:1.05rem; margin-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem;">
        <span>🔄</span> Flujo de una Petición Operativa con Arquitectura Limpia y JWT
    </h3>
    <div style="display:flex; flex-wrap:wrap; gap:0.6rem; align-items:center; justify-content:space-between; font-family:var(--font-mono); font-size:0.76rem;">
        <div style="background:#090e1d; border:1px solid #38bdf8; padding:0.65rem; border-radius:6px; flex:1; min-width:140px; text-align:center;">
            <div style="color:#38bdf8; font-weight:bold;">1. Operador (HMI)</div>
            <div style="color:var(--text-muted); font-size:0.68rem; margin-top:0.2rem;">Consigna Choke 45% + Token JWT</div>
        </div>
        <span style="color:var(--primary); font-size:1.1rem;">➔</span>
        <div style="background:#090e1d; border:1px solid #38bdf8; padding:0.65rem; border-radius:6px; flex:1; min-width:140px; text-align:center;">
            <div style="color:#38bdf8; font-weight:bold;">2. authFetch ➔ api/wellheads.php</div>
            <div style="color:var(--text-muted); font-size:0.68rem; margin-top:0.2rem;">Header Bearer Authorization</div>
        </div>
        <span style="color:var(--primary); font-size:1.1rem;">➔</span>
        <div style="background:#090e1d; border:1px solid #a855f7; padding:0.65rem; border-radius:6px; flex:1; min-width:140px; text-align:center;">
            <div style="color:#a855f7; font-weight:bold;">3. WellheadController</div>
            <div style="color:var(--text-muted); font-size:0.68rem; margin-top:0.2rem;">Controlador delgado HTTP/JSON</div>
        </div>
        <span style="color:var(--primary); font-size:1.1rem;">➔</span>
        <div style="background:#090e1d; border:1px solid #e879f9; padding:0.65rem; border-radius:6px; flex:1; min-width:140px; text-align:center;">
            <div style="color:#e879f9; font-weight:bold;">4. WellheadService & Dominio</div>
            <div style="color:var(--text-muted); font-size:0.68rem; margin-top:0.2rem;">Value Object Choke + Observer</div>
        </div>
        <span style="color:var(--primary); font-size:1.1rem;">➔</span>
        <div style="background:#090e1d; border:1px solid #34d399; padding:0.65rem; border-radius:6px; flex:1; min-width:140px; text-align:center;">
            <div style="color:#34d399; font-weight:bold;">5. WellheadRepository</div>
            <div style="color:var(--text-muted); font-size:0.68rem; margin-top:0.2rem;">Acceso a datos + Fallback</div>
        </div>
        <span style="color:var(--primary); font-size:1.1rem;">➔</span>
        <div style="background:#090e1d; border:1px solid #fbbf24; padding:0.65rem; border-radius:6px; flex:1; min-width:140px; text-align:center;">
            <div style="color:#fbbf24; font-weight:bold;">6. MySQL `mapaf_db`</div>
            <div style="color:var(--text-muted); font-size:0.68rem; margin-top:0.2rem;">InnoDB & Bitácora 21 CFR Part 11</div>
        </div>
    </div>
</div>

<!-- DETALLE DE LOS 5 MÓDULOS NÚCLEO -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:1.5rem;">

    <!-- MÓDULO 1: PRODUCCIÓN Y POZOS -->
    <div class="scada-card" style="border-top: 3px solid #38bdf8;">
        <div class="card-header-flex">
            <div>
                <span class="badge-status badge-nominal" style="font-size:0.68rem;">MÓDULO 1 · UPSTREAM</span>
                <h3 style="color:#fff; font-size:1.1rem; margin-top:0.35rem;">Control de Cabezales de Pozo (API 6A)</h3>
            </div>
            <span style="font-size:1.8rem;">🛢️</span>
        </div>
        <p style="font-size:0.83rem; color:#94a3b8; line-height:1.45; margin:0.75rem 0;">
            <strong>Responsabilidad Única:</strong> Gestionar el estado operativo y los actuadores mecánicos de los cabezales de pozo (árboles de navidad), permitiendo regular el flujo de hidrocarburos y aislar líneas.
        </p>
        <div style="font-size:0.78rem; font-family:var(--font-mono); background:#090e1d; padding:0.75rem; border-radius:6px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:0.4rem;">
            <div><span style="color:var(--text-muted);">Tabla MySQL:</span> <code style="color:#34d399;">pozos</code> (<?= $stats['pozos'] ?> registrados)</div>
            <div><span style="color:var(--text-muted);">Servicio Dominio:</span> <code style="color:#e879f9;">services/WellheadService.php</code></div>
            <div><span style="color:var(--text-muted);">Repositorio:</span> <code style="color:#38bdf8;">repositories/WellheadRepository.php</code></div>
            <div><span style="color:var(--text-muted);">Controlador:</span> <code style="color:#a855f7;">controllers/WellheadController.php</code></div>
            <div><span style="color:var(--text-muted);">Endpoint REST:</span> <code style="color:#fbbf24;">api/wellheads.php</code></div>
            <div><span style="color:var(--text-muted);">Vista HMI:</span> <code style="color:#cbd5e1;">views/wellheads.php</code></div>
        </div>
        <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted);">
            ✓ Funciones: Invariante ChokeOpening (0-100%), enclavamiento SIS ESD-1, conmutación SSV y Observer de auditoría.
        </div>
    </div>

    <!-- MÓDULO 2: TELEMETRÍA IIOT -->
    <div class="scada-card" style="border-top: 3px solid #10b981;">
        <div class="card-header-flex">
            <div>
                <span class="badge-status badge-nominal" style="font-size:0.68rem;">MÓDULO 2 · INSTRUMENTACIÓN</span>
                <h3 style="color:#fff; font-size:1.1rem; margin-top:0.35rem;">Telemetría de Proceso en Tiempo Real</h3>
            </div>
            <span style="font-size:1.8rem;">📡</span>
        </div>
        <p style="font-size:0.83rem; color:#94a3b8; line-height:1.45; margin:0.75rem 0;">
            <strong>Responsabilidad Única:</strong> Adquirir, procesar y suministrar lecturas continuas de sensores físicos de campo con cálculo de KPIs consolidados de producción y renderizado de gráficos analíticos.
        </p>
        <div style="font-size:0.78rem; font-family:var(--font-mono); background:#090e1d; padding:0.75rem; border-radius:6px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:0.4rem;">
            <div><span style="color:var(--text-muted);">Tabla MySQL:</span> <code style="color:#34d399;">telemetria_metricas</code> (<?= $stats['telemetria'] ?> sensores)</div>
            <div><span style="color:var(--text-muted);">Servicio Dominio:</span> <code style="color:#e879f9;">services/TelemetryService.php</code></div>
            <div><span style="color:var(--text-muted);">Repositorio:</span> <code style="color:#38bdf8;">repositories/TelemetryRepository.php</code></div>
            <div><span style="color:var(--text-muted);">Controlador:</span> <code style="color:#a855f7;">controllers/TelemetryController.php</code></div>
            <div><span style="color:var(--text-muted);">Endpoint REST:</span> <code style="color:#fbbf24;">api/telemetry.php</code></div>
            <div><span style="color:var(--text-muted);">Vista HMI:</span> <code style="color:#cbd5e1;">views/dashboard.php</code></div>
        </div>
        <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted);">
            ✓ Sensores: Presión de Tubing (WHTP), Presión de Casing, Temperatura de Manifold, Caudal Másico (BPD), Variador ESP y GOR.
        </div>
    </div>

    <!-- MÓDULO 3: ALARMAS ANSI/ISA-18.2 -->
    <div class="scada-card" style="border-top: 3px solid #f59e0b;">
        <div class="card-header-flex">
            <div>
                <span class="badge-status badge-advisory" style="font-size:0.68rem;">MÓDULO 3 · SEGURIDAD FUNCIONAL</span>
                <h3 style="color:#fff; font-size:1.1rem; margin-top:0.35rem;">Gestión de Alarmas (ANSI/ISA-18.2)</h3>
            </div>
            <span style="font-size:1.8rem;">🔔</span>
        </div>
        <p style="font-size:0.83rem; color:#94a3b8; line-height:1.45; margin:0.75rem 0;">
            <strong>Responsabilidad Única:</strong> Gestionar el ciclo de vida de las alarmas de proceso por severidad y forzar el reconocimiento trazable exigiendo la identificación del operador y justificación técnica.
        </p>
        <div style="font-size:0.78rem; font-family:var(--font-mono); background:#090e1d; padding:0.75rem; border-radius:6px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:0.4rem;">
            <div><span style="color:var(--text-muted);">Tabla MySQL:</span> <code style="color:#34d399;">alarmas</code> (<?= $stats['alarmas'] ?> registradas)</div>
            <div><span style="color:var(--text-muted);">Servicio Dominio:</span> <code style="color:#e879f9;">services/AlarmService.php</code></div>
            <div><span style="color:var(--text-muted);">Repositorio:</span> <code style="color:#38bdf8;">repositories/AlarmRepository.php</code></div>
            <div><span style="color:var(--text-muted);">Controlador:</span> <code style="color:#a855f7;">controllers/AlarmController.php</code></div>
            <div><span style="color:var(--text-muted);">Endpoint REST:</span> <code style="color:#fbbf24;">api/alarms.php</code></div>
            <div><span style="color:var(--text-muted);">Vista HMI:</span> <code style="color:#cbd5e1;">views/alarms.php</code></div>
        </div>
        <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted);">
            ✓ Mitigación Antipatrón: Bloquea el "Blind Alarm ACK" mediante AlarmSeverity, exigiendo mín. 8 caracteres y firma Bearer JWT.
        </div>
    </div>

    <!-- MÓDULO 4: SISTEMA INSTRUMENTADO SIS / ESD-1 -->
    <div class="scada-card" style="border-top: 3px solid #f43f5e;">
        <div class="card-header-flex">
            <div>
                <span class="badge-status badge-critical" style="font-size:0.68rem;">MÓDULO 4 · SIS IEC 61511</span>
                <h3 style="color:#fff; font-size:1.1rem; margin-top:0.35rem;">Parada de Emergencia (ESD-1)</h3>
            </div>
            <span style="font-size:1.8rem;">🚨</span>
        </div>
        <p style="font-size:0.83rem; color:#94a3b8; line-height:1.45; margin:0.75rem 0;">
            <strong>Responsabilidad Única:</strong> Implementar la capa instrumentada de seguridad crítica para aislar atómicamente la estación completa, cerrando válvulas SSV, estranguladores al 0% y bloqueando maniobras.
        </p>
        <div style="font-size:0.78rem; font-family:var(--font-mono); background:#090e1d; padding:0.75rem; border-radius:6px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:0.4rem;">
            <div><span style="color:var(--text-muted);">Tabla MySQL:</span> <code style="color:#34d399;">configuracion_sistema</code> (clave: <code>esd_triggered</code>)</div>
            <div><span style="color:var(--text-muted);">Servicio Dominio:</span> <code style="color:#e879f9;">services/EsdService.php</code></div>
            <div><span style="color:var(--text-muted);">Repositorio:</span> <code style="color:#38bdf8;">repositories/EsdRepository.php</code></div>
            <div><span style="color:var(--text-muted);">Controlador:</span> <code style="color:#a855f7;">controllers/EsdController.php</code></div>
            <div><span style="color:var(--text-muted);">Endpoint REST:</span> <code style="color:#fbbf24;">api/esd.php</code></div>
            <div><span style="color:var(--text-muted);">Componentes UI:</span> <code style="color:#cbd5e1;">Banner ESD & Modal en footer.php</code></div>
        </div>
        <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted);">
            ✓ Enclavamiento Seguro: Transacción atómica PDO que aísla los 4 pozos y requiere rearme manual controlado.
        </div>
    </div>

    <!-- MÓDULO 5: AUDITORÍA 21 CFR PART 11 Y CONTROL RBAC -->
    <div class="scada-card" style="border-top: 3px solid #8b5cf6;">
        <div class="card-header-flex">
            <div>
                <span class="badge-status badge-nominal" style="font-size:0.68rem;">MÓDULO 5 · GOBERNANZA & AUDITORÍA</span>
                <h3 style="color:#fff; font-size:1.1rem; margin-top:0.35rem;">Control de Acceso (RBAC) & Bitácora Forense</h3>
            </div>
            <span style="font-size:1.8rem;">📜</span>
        </div>
        <p style="font-size:0.83rem; color:#94a3b8; line-height:1.45; margin:0.75rem 0;">
            <strong>Responsabilidad Única:</strong> Gestionar la matriz de operadores y roles de turno mediante JWT (HS256), registrando cada consigna, acuse o cambio de estado en una bitácora inmutable conforme a 21 CFR Part 11.
        </p>
        <div style="font-size:0.78rem; font-family:var(--font-mono); background:#090e1d; padding:0.75rem; border-radius:6px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:0.4rem;">
            <div><span style="color:var(--text-muted);">Tablas MySQL:</span> <code style="color:#34d399;">roles</code>, <code style="color:#34d399;">usuarios</code>, <code style="color:#34d399;">bitacora_auditoria</code> (<?= $stats['auditoria'] ?> registros)</div>
            <div><span style="color:var(--text-muted);">Servicios:</span> <code style="color:#e879f9;">AuthService.php</code>, <code style="color:#e879f9;">JwtService.php</code>, <code style="color:#e879f9;">EventDispatcher.php</code></div>
            <div><span style="color:var(--text-muted);">Repositorios:</span> <code style="color:#38bdf8;">UserRepository.php</code>, <code style="color:#38bdf8;">AuditRepository.php</code></div>
            <div><span style="color:var(--text-muted);">Controladores:</span> <code style="color:#a855f7;">AuthController.php</code>, <code style="color:#a855f7;">AuditController.php</code></div>
            <div><span style="color:var(--text-muted);">Endpoints REST:</span> <code style="color:#fbbf24;">api/auth.php</code>, <code style="color:#fbbf24;">api/audit.php</code></div>
            <div><span style="color:var(--text-muted);">Vista HMI:</span> <code style="color:#cbd5e1;">views/audit.php</code></div>
        </div>
        <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted);">
            ✓ Integridad Inmutable: Observer desacoplado que registra eventos de dominio sin opción de alteración ni borrado.
        </div>
    </div>

    <!-- MÓDULO 6: INFRAESTRUCTURA Y BASE DE DATOS -->
    <div class="scada-card" style="border-top: 3px solid #06b6d4;">
        <div class="card-header-flex">
            <div>
                <span class="badge-status badge-nominal" style="font-size:0.68rem;">MÓDULO 6 · INFRAESTRUCTURA</span>
                <h3 style="color:#fff; font-size:1.1rem; margin-top:0.35rem;">Conexión Desacoplada & Diagnóstico XAMPP</h3>
            </div>
            <span style="font-size:1.8rem;">⚙️</span>
        </div>
        <p style="font-size:0.83rem; color:#94a3b8; line-height:1.45; margin:0.75rem 0;">
            <strong>Responsabilidad Única:</strong> Centralizar de forma aislada las credenciales en un archivo separado, gestionar el pool de conexiones mediante Singleton PDO y monitorear la salud de Apache, PHP y MariaDB.
        </p>
        <div style="font-size:0.78rem; font-family:var(--font-mono); background:#090e1d; padding:0.75rem; border-radius:6px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:0.4rem;">
            <div><span style="color:var(--text-muted);">Credenciales:</span> <code style="color:#34d399;">config/db_config.php</code> (desacopladas)</div>
            <div><span style="color:var(--text-muted);">Motor Singleton:</span> <code style="color:#38bdf8;">config/database.php</code> (reconexión automática)</div>
            <div><span style="color:var(--text-muted);">Autoloader PSR:</span> <code style="color:#a855f7;">config/config.php</code></div>
            <div><span style="color:var(--text-muted);">Migrador / DDL:</span> <code style="color:#fbbf24;">setup_database.php</code> & <code style="color:#fbbf24;">sql/database.sql</code></div>
            <div><span style="color:var(--text-muted);">Diagnóstico HMI:</span> <code style="color:#cbd5e1;">views/status.php</code></div>
        </div>
        <div style="margin-top:0.75rem; font-size:0.75rem; color:var(--text-muted);">
            ✓ Cero Dependencias: No requiere Composer, NPM, Node.js ni herramientas externas para su ejecución en XAMPP.
        </div>
    </div>

</div>

<!-- GUÍA PARA PREGUNTAS EN SUSTENTACIÓN -->
<div class="scada-card" style="margin-top:2rem;">
    <h3 style="color:#fff; font-size:1.05rem; margin-bottom:0.75rem;">📋 Respuestas Rápidas para Evaluadores</h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem; font-size:0.85rem; line-height:1.5;">
        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <strong style="color:#38bdf8;">¿Por qué cada módulo es independiente?</strong>
            <p style="color:#94a3b8; font-size:0.8rem; margin-top:0.35rem;">
                Porque aplican el <strong>Principio de Responsabilidad Única (SRP)</strong>: cada modelo y controlador sólo gestiona su propia tabla y dominio. Si se requiere modificar el cálculo de caudal, sólo se edita <code>TelemetriaModel</code> sin alterar alarmas ni pozos.
            </p>
        </div>
        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <strong style="color:#34d399;">¿Cómo se garantiza la seguridad en la base de datos?</strong>
            <p style="color:#94a3b8; font-size:0.8rem; margin-top:0.35rem;">
                Todas las consultas utilizan <strong>sentencias preparadas nativas PDO</strong> (con emulación desactivada). Las credenciales están en un archivo separado <code>db_config.php</code> y la integridad referencial se custodia con llaves foráneas <code>InnoDB</code>.
            </p>
        </div>
        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:1rem;">
            <strong style="color:#fbbf24;">¿Cómo se comunican el Frontend y el Backend?</strong>
            <p style="color:#94a3b8; font-size:0.8rem; margin-top:0.35rem;">
                Mediante la <strong>Fetch API nativa de JavaScript</strong> con sondeo cada 3 segundos hacia los endpoints REST en <code>api/*.php</code>. El backend procesa las solicitudes, valida las reglas y devuelve JSON estándar.
            </p>
        </div>
    </div>
</div>

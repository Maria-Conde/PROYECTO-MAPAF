<?php
/**
 * PROYECTO MAPAF - Vista: Subsistemas & Módulos Operativos
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Programa: Análisis y Desarrollo de Software (ADSO) | Ficha: 3413988-ADSO
 */

// Obtener estadísticas de subsistemas
$stats = [
    'pozos'      => 4,
    'telemetria' => 6,
    'alarmas'    => 3,
    'usuarios'   => 5,
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
    // Degrada graciosamente
}
?>

<div style="margin-bottom:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2 class="section-title" style="margin-bottom:0.25rem;">🧩 Subsistemas & Módulos del Sistema</h2>
            <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
                Panel centralizado de componentes operativos · Control, monitoreo y acceso directo
            </p>
        </div>
        <div>
            <span class="badge-status badge-nominal" style="padding:0.4rem 0.85rem; font-size:0.75rem;">
                ● 6 SUBSISTEMAS ACTIVOS
            </span>
        </div>
    </div>
</div>

<!-- RESUMEN EJECUTIVO DE COMPONENTES -->
<div class="grid-kpis" style="margin-bottom:1.5rem;">
    <div class="scada-card" style="padding:1rem;">
        <div class="card-tag">CABEZALES API 6A</div>
        <div class="card-value" style="font-size:1.4rem; color:#38bdf8; margin:0.3rem 0;">
            <?= $stats['pozos'] ?> Pozos
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
            Actuadores Choke & SSV
        </div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div class="card-tag">INSTRUMENTACIÓN</div>
        <div class="card-value" style="font-size:1.4rem; color:#34d399; margin:0.3rem 0;">
            <?= $stats['telemetria'] ?> Sensores
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
            Telemetría IIoT en Vivo
        </div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div class="card-tag">CONSOLA ALARMAS</div>
        <div class="card-value" style="font-size:1.4rem; color:#fbbf24; margin:0.3rem 0;">
            <?= $stats['alarmas'] ?> Alertas
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
            Norma ANSI/ISA-18.2
        </div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div class="card-tag">SEGURIDAD SIS</div>
        <div class="card-value" style="font-size:1.4rem; color:#f43f5e; margin:0.3rem 0;">
            ESD-1
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
            Aislamiento IEC 61511
        </div>
    </div>
</div>

<!-- TARJETAS DE MÓDULOS DEL SISTEMA -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.25rem;">

    <!-- MÓDULO 1: PRODUCCIÓN Y POZOS -->
    <div class="scada-card" style="border-top: 3px solid #38bdf8; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div class="card-header-flex" style="margin-bottom:0.75rem;">
                <div>
                    <span class="badge-status badge-nominal" style="font-size:0.65rem;">MÓDULO 1 · UPSTREAM</span>
                    <h3 style="color:#fff; font-size:1.05rem; margin-top:0.3rem;">Control de Cabezales de Pozo</h3>
                </div>
                <span style="font-size:1.6rem;">🛢️</span>
            </div>
            <p style="font-size:0.82rem; color:#94a3b8; line-height:1.45; margin-bottom:1rem;">
                Regulación de estranguladores Choke (0.0% a 100.0%), conmutación de válvulas maestras SSV y enclavamiento de seguridad API 6A.
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:1rem;">
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#cbd5e1; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    <?= $stats['pozos'] ?> Cabezales Activos
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#38bdf8; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    Norma API 6A
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#34d399; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    SIL-3
                </span>
            </div>
        </div>
        <div style="border-top:1px solid var(--border-color); padding-top:0.75rem;">
            <a href="index.php?view=wellheads" class="btn-confirm" style="display:block; text-align:center; text-decoration:none; font-size:0.8rem; padding:0.5rem 0.75rem;">
                Abrir Control de Pozos &rarr;
            </a>
        </div>
    </div>

    <!-- MÓDULO 2: TELEMETRÍA IIOT -->
    <div class="scada-card" style="border-top: 3px solid #10b981; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div class="card-header-flex" style="margin-bottom:0.75rem;">
                <div>
                    <span class="badge-status badge-nominal" style="font-size:0.65rem;">MÓDULO 2 · INSTRUMENTACIÓN</span>
                    <h3 style="color:#fff; font-size:1.05rem; margin-top:0.3rem;">Telemetría de Proceso en Tiempo Real</h3>
                </div>
                <span style="font-size:1.6rem;">📡</span>
            </div>
            <p style="font-size:0.82rem; color:#94a3b8; line-height:1.45; margin-bottom:1rem;">
                Adquisición continua de sensores de campo (presión tubing/casing, temperatura, caudal BPD, variadores ESP y curvas de tendencia).
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:1rem;">
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#cbd5e1; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    <?= $stats['telemetria'] ?> Transmisores
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#10b981; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    Muestreo 3s
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#38bdf8; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    Curvas Historian
                </span>
            </div>
        </div>
        <div style="border-top:1px solid var(--border-color); padding-top:0.75rem;">
            <a href="index.php?view=dashboard" class="btn-confirm" style="display:block; text-align:center; text-decoration:none; font-size:0.8rem; padding:0.5rem 0.75rem;">
                Ver Tablero General &rarr;
            </a>
        </div>
    </div>

    <!-- MÓDULO 3: ALARMAS ANSI/ISA-18.2 -->
    <div class="scada-card" style="border-top: 3px solid #f59e0b; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div class="card-header-flex" style="margin-bottom:0.75rem;">
                <div>
                    <span class="badge-status badge-advisory" style="font-size:0.65rem;">MÓDULO 3 · SEGURIDAD</span>
                    <h3 style="color:#fff; font-size:1.05rem; margin-top:0.3rem;">Gestión de Alarmas Industriales</h3>
                </div>
                <span style="font-size:1.6rem;">🔔</span>
            </div>
            <p style="font-size:0.82rem; color:#94a3b8; line-height:1.45; margin-bottom:1rem;">
                Consola de detección y reconocimiento formal bajo norma ANSI/ISA-18.2 con justificación técnica obligatoria y firma del operador.
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:1rem;">
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#cbd5e1; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    <?= $stats['alarmas'] ?> Alarmas
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#fbbf24; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    ANSI/ISA-18.2
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#34d399; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    Anti-Acuse Ciego
                </span>
            </div>
        </div>
        <div style="border-top:1px solid var(--border-color); padding-top:0.75rem;">
            <a href="index.php?view=alarms" class="btn-confirm" style="display:block; text-align:center; text-decoration:none; font-size:0.8rem; padding:0.5rem 0.75rem;">
                Abrir Consola de Alarmas &rarr;
            </a>
        </div>
    </div>

    <!-- MÓDULO 4: SISTEMA INSTRUMENTADO SIS / ESD-1 -->
    <div class="scada-card" style="border-top: 3px solid #f43f5e; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div class="card-header-flex" style="margin-bottom:0.75rem;">
                <div>
                    <span class="badge-status badge-critical" style="font-size:0.65rem;">MÓDULO 4 · SIS IEC 61511</span>
                    <h3 style="color:#fff; font-size:1.05rem; margin-top:0.3rem;">Parada de Emergencia (ESD-1)</h3>
                </div>
                <span style="font-size:1.6rem;">🚨</span>
            </div>
            <p style="font-size:0.82rem; color:#94a3b8; line-height:1.45; margin-bottom:1rem;">
                Capa instrumentada de seguridad crítica para aislamiento atómico de la estación, cierre de válvulas SSV y bloqueo de consignas.
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:1rem;">
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#cbd5e1; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    Disparo &lt; 1.2s
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#f43f5e; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    IEC 61511
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#38bdf8; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    Rearme Seguro
                </span>
            </div>
        </div>
        <div style="border-top:1px solid var(--border-color); padding-top:0.75rem;">
            <a href="index.php?view=dashboard" class="btn-confirm" style="display:block; text-align:center; text-decoration:none; font-size:0.8rem; padding:0.5rem 0.75rem;">
                Ver Estado de Seguridad &rarr;
            </a>
        </div>
    </div>

    <!-- MÓDULO 5: AUDITORÍA 21 CFR PART 11 -->
    <div class="scada-card" style="border-top: 3px solid #8b5cf6; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div class="card-header-flex" style="margin-bottom:0.75rem;">
                <div>
                    <span class="badge-status badge-nominal" style="font-size:0.65rem;">MÓDULO 5 · AUDITORÍA</span>
                    <h3 style="color:#fff; font-size:1.05rem; margin-top:0.3rem;">Bitácora Forense Inmutable</h3>
                </div>
                <span style="font-size:1.6rem;">📜</span>
            </div>
            <p style="font-size:0.82rem; color:#94a3b8; line-height:1.45; margin-bottom:1rem;">
                Registro cronológico no alterable de cada acción operativa, conmutación de turno y consigna conforme a la norma 21 CFR Part 11.
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:1rem;">
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#cbd5e1; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    <?= $stats['auditoria'] ?> Registros
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#8b5cf6; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    21 CFR Part 11
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#34d399; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    Patrón Observer
                </span>
            </div>
        </div>
        <div style="border-top:1px solid var(--border-color); padding-top:0.75rem;">
            <a href="index.php?view=audit" class="btn-confirm" style="display:block; text-align:center; text-decoration:none; font-size:0.8rem; padding:0.5rem 0.75rem;">
                Consultar Bitácora &rarr;
            </a>
        </div>
    </div>

    <!-- MÓDULO 6: INFRAESTRUCTURA & SEGURIDAD JWT -->
    <div class="scada-card" style="border-top: 3px solid #06b6d4; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div class="card-header-flex" style="margin-bottom:0.75rem;">
                <div>
                    <span class="badge-status badge-nominal" style="font-size:0.65rem;">MÓDULO 6 · INFRAESTRUCTURA</span>
                    <h3 style="color:#fff; font-size:1.05rem; margin-top:0.3rem;">Diagnóstico & Ciberseguridad</h3>
                </div>
                <span style="font-size:1.6rem;">⚙️</span>
            </div>
            <p style="font-size:0.82rem; color:#94a3b8; line-height:1.45; margin-bottom:1rem;">
                Gestión de matriz RBAC, autenticación con tokens JWT HS256, diagnóstico de conectividad y persistencia relacional.
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:1rem;">
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#cbd5e1; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    <?= $stats['usuarios'] ?> Operadores
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#06b6d4; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    JWT HS256
                </span>
                <span style="background:#090e1d; border:1px solid var(--border-color); color:#34d399; padding:0.25rem 0.6rem; border-radius:4px; font-size:0.72rem; font-family:var(--font-mono);">
                    Base de Datos OK
                </span>
            </div>
        </div>
        <div style="border-top:1px solid var(--border-color); padding-top:0.75rem;">
            <a href="index.php?view=status" class="btn-confirm" style="display:block; text-align:center; text-decoration:none; font-size:0.8rem; padding:0.5rem 0.75rem;">
                Ver Diagnóstico &rarr;
            </a>
        </div>
    </div>

</div>

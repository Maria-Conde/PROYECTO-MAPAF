<?php
/**
 * PROYECTO MAPAF - Vista: Parámetros del Sistema SCADA
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO | SCADA Petrolero · SPS-01
 */

$parametrosService = new ParametrosService();
$saveSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_params') {
    if (isset($_POST['params']) && is_array($_POST['params'])) {
        $parametrosService->saveParametros($_POST['params']);
        $saveSuccess = true;
    }
}

$params = $parametrosService->getParametros();
?>

<!-- Encabezado del Módulo -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
            <h2 class="section-title" style="margin-bottom:0;">📋 Parámetros del Sistema SCADA</h2>
            <span class="badge-sidebar-pill badge-pill-admin" style="font-size:0.75rem;">ADMINISTRACIÓN DE CONSIGNA</span>
        </div>
        <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
            Configuración de Consignas Operativas, Enclavamientos de Seguridad SIS y Parámetros de Red IIoT
        </p>
    </div>

    <div style="display:flex; gap:0.75rem; align-items:center;">
        <a href="index.php?view=carga_csv" class="btn-well btn-ssv-open">
            📥 Importar CSV
        </a>
    </div>
</div>

<?php if ($saveSuccess): ?>
<div style="background:rgba(16, 185, 129, 0.15); border:1px solid #10b981; border-radius:8px; padding:0.85rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
    <span style="font-size:1.2rem;">✅</span>
    <span style="font-size:0.85rem; color:#34d399; font-family:var(--font-mono);">
        Los parámetros operativos fueron actualizados y sincronizados con los controladores PLC/RTU exitosamente.
    </span>
</div>
<?php endif; ?>

<form method="POST" action="index.php?view=parametros">
    <input type="hidden" name="action" value="save_params">

    <!-- Sección 1: Alivio de Presión y Seguridad SIS (IEC 61511 / API 6A) -->
    <div class="scada-card" style="padding:1.25rem; margin-bottom:1.5rem; border-left:4px solid #ef4444;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <div>
                <h3 style="font-size:1.05rem; color:#f8fafc; font-weight:700;">
                    🛡️ Presión Crítica & Enclavamientos de Seguridad SIS
                </h3>
                <p style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-mono);">
                    Estándares ANSI/ISA-84 / IEC 61511 (SIL-2 / SIL-3)
                </p>
            </div>
            <span class="badge-status badge-alarm">CRÍTICO SIL-2</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem;">
            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    PRESIÓN MÁXIMA DE ALIVIO SIS (PSI):
                </label>
                <input type="number" step="0.1" name="params[SIS_PRESSURE_MAX_PSI]" 
                       value="<?= htmlspecialchars($params['SIS_PRESSURE_MAX_PSI']['valor'] ?? '650.0') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Disparo automático de válvula ESDV al superar este umbral.
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    RETARDO DE DISPARO ESD-1 (SEGUNDOS):
                </label>
                <input type="number" step="0.5" name="params[ESD_TRIP_DELAY_SEC]" 
                       value="<?= htmlspecialchars($params['ESD_TRIP_DELAY_SEC']['valor'] ?? '2.5') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Ventana de amortiguación para evitar falsos disparos por transitorios.
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    TIEMPO MÁXIMO CIERRE SSV (SEGUNDOS):
                </label>
                <input type="number" step="0.5" name="params[SSV_CLOSE_TIME_SEC]" 
                       value="<?= htmlspecialchars($params['SSV_CLOSE_TIME_SEC']['valor'] ?? '5.0') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Norma API 6A: La válvula de seguridad de subsuelo debe cerrar en &le; 5.0 seg.
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 2: Producción & Estranguladores de Pozo (API 6A) -->
    <div class="scada-card" style="padding:1.25rem; margin-bottom:1.5rem; border-left:4px solid #38bdf8;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <div>
                <h3 style="font-size:1.05rem; color:#f8fafc; font-weight:700;">
                    🛢️ Producción & Estranguladores de Flujo (Choke API 6A)
                </h3>
                <p style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-mono);">
                    Consignas de entrega en cabezales de pozo y manifold general
                </p>
            </div>
            <span class="badge-status badge-nominal">PRODUCCIÓN</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem;">
            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    APERTURA NOMINAL DE CHOKE (%):
                </label>
                <input type="number" step="1" name="params[CHOKE_DEFAULT_OPENING]" 
                       value="<?= htmlspecialchars($params['CHOKE_DEFAULT_OPENING']['valor'] ?? '35') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Posición inicial de estrangulador al energizar pozo.
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    CAUDAL MÁXIMO SEGURO POR POZO (BPD):
                </label>
                <input type="number" step="10" name="params[MAX_FLOW_RATE_BPD]" 
                       value="<?= htmlspecialchars($params['MAX_FLOW_RATE_BPD']['valor'] ?? '2500') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Límite para evitar erosión o arrastre de arenas de formación.
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    RELACIÓN GAS-PETRÓLEO MÁXIMA (PC/BL):
                </label>
                <input type="number" step="5" name="params[GAS_OIL_RATIO_MAX]" 
                       value="<?= htmlspecialchars($params['GAS_OIL_RATIO_MAX']['valor'] ?? '450') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Monitoreo de desgasificación en cabeza de pozo.
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 3: Comunicaciones de Red IIoT & Telemetría -->
    <div class="scada-card" style="padding:1.25rem; margin-bottom:1.5rem; border-left:4px solid #10b981;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <div>
                <h3 style="font-size:1.05rem; color:#f8fafc; font-weight:700;">
                    📡 Protocolos de Red IIoT & Polling Modbus TCP
                </h3>
                <p style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-mono);">
                    Temporización de adquisición de datos en campo RTU/PLC
                </p>
            </div>
            <span class="badge-status badge-standby">RED INDUSTRIAL</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.25rem;">
            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    FRECUENCIA POLLING RTU (MS):
                </label>
                <input type="number" step="100" name="params[RTU_POLLING_INTERVAL_MS]" 
                       value="<?= htmlspecialchars($params['RTU_POLLING_INTERVAL_MS']['valor'] ?? '1000') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Ciclo de muestreo en milisegundos para RTU de cabeza de pozo.
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    TIMEOUT DE ENLACE DE RED (SEGUNDOS):
                </label>
                <input type="number" step="1" name="params[COMM_TIMEOUT_SEC]" 
                       value="<?= htmlspecialchars($params['COMM_TIMEOUT_SEC']['valor'] ?? '5') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Tiempo máximo sin respuesta antes de disparar alarma COMM_LOST.
                </div>
            </div>

            <div>
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">
                    FRECUENCIA HISTORIAN (SEGUNDOS):
                </label>
                <input type="number" step="1" name="params[HISTORIAN_LOG_FREQ_SEC]" 
                       value="<?= htmlspecialchars($params['HISTORIAN_LOG_FREQ_SEC']['valor'] ?? '60') ?>" 
                       class="modal-input" style="width:100%; box-sizing:border-box;">
                <div style="font-size:0.7rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.25rem;">
                    Frecuencia de persistencia en base de datos para análisis histórico.
                </div>
            </div>
        </div>
    </div>

    <!-- Botonera de Guardado -->
    <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
        <a href="index.php?view=parametros" class="btn-well btn-choke">Descartar Cambios</a>
        <button type="submit" class="btn-well btn-ssv-open" style="font-weight:700;">
            💾 Guardar Todos los Parámetros
        </button>
    </div>
</form>

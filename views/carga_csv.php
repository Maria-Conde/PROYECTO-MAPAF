<?php
/**
 * PROYECTO MAPAF - Vista: Carga Masiva de Parámetros vía CSV
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO | SCADA Petrolero · SPS-01
 */

$parametrosService = new ParametrosService();
$importResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_csv') {
    $csvData = '';
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $csvData = file_get_contents($_FILES['csv_file']['tmp_name']);
    } elseif (!empty($_POST['csv_text'])) {
        $csvData = $_POST['csv_text'];
    }

    if (!empty($csvData)) {
        $importResult = $parametrosService->parseAndImportCsv($csvData);
    }
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
            <h2 class="section-title" style="margin-bottom:0;">📥 Carga CSV de Parámetros</h2>
            <span class="badge-sidebar-pill badge-pill-admin" style="font-size:0.75rem;">SOLO ADMINISTRADOR</span>
        </div>
        <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
            Importación Masiva de Consignas de Proceso, Alarmas ISA 18.2 y Enclavamientos SIS
        </p>
    </div>

    <div style="display:flex; gap:0.75rem; align-items:center;">
        <a href="index.php?view=parametros" class="btn-well btn-choke">
            ⬅️ Volver a Parámetros
        </a>
    </div>
</div>

<?php if ($importResult && !empty($importResult['success'])): ?>
<div style="background:rgba(16, 185, 129, 0.15); border:1px solid #10b981; border-radius:8px; padding:1rem 1.25rem; margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.35rem;">
        <span style="font-size:1.2rem;">✅</span>
        <strong style="color:#34d399; font-size:0.95rem;">
            ¡Importación Exitosa! Se aplicaron <?= $importResult['imported'] ?> parámetros al sistema SCADA.
        </strong>
    </div>
    <div style="font-size:0.78rem; color:#cbd5e1; font-family:var(--font-mono);">
        Los valores fueron validados contra la matriz técnica y registrados en la bitácora de auditoría 21 CFR Part 11.
    </div>
</div>
<?php endif; ?>

<!-- Formulario de Carga y Previsualizador -->
<div class="scada-card" style="padding:1.5rem; margin-bottom:1.5rem;">
    <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.5rem;">
        1. Selección o Pegado del Archivo CSV
    </h3>
    <p style="font-size:0.8rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:1.25rem;">
        Formato requerido: <code style="color:#38bdf8;">clave,valor,descripcion</code> (con encabezado en la primera línea).
    </p>

    <form method="POST" action="index.php?view=carga_csv" enctype="multipart/form-data" id="csvForm">
        <input type="hidden" name="action" value="import_csv">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
            <!-- Opción A: Subir Archivo -->
            <div style="border:2px dashed rgba(255,255,255,0.15); border-radius:8px; padding:1.5rem; text-align:center; background:#090e1d;">
                <div style="font-size:2rem; margin-bottom:0.5rem;">📁</div>
                <div style="font-size:0.85rem; color:#f8fafc; font-weight:700; margin-bottom:0.25rem;">Arrastra o selecciona un archivo .csv</div>
                <div style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:1rem;">Máximo 2MB · UTF-8</div>
                <input type="file" name="csv_file" id="csvFileInput" accept=".csv,text/csv" class="modal-input" style="max-width:240px; margin:0 auto; display:block;">
            </div>

            <!-- Opción B: Pegar Texto CSV -->
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                    <label style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">O PEGA EL TEXTO CSV DIRECTAMENTE:</label>
                    <button type="button" class="btn-well btn-choke" style="padding:0.2rem 0.5rem; font-size:0.7rem;" onclick="loadDemoCsv()">
                        📄 Cargar Ejemplo Demo
                    </button>
                </div>
                <textarea name="csv_text" id="csvTextInput" class="modal-input" rows="7" style="width:100%; box-sizing:border-box; font-family:var(--font-mono); font-size:0.78rem; resize:vertical;" placeholder="clave,valor,descripcion&#10;SIS_PRESSURE_MAX_PSI,650.0,Presión Máxima de Alivio SIS&#10;ESD_TRIP_DELAY_SEC,2.5,Retardo de Disparo ESD-1&#10;CHOKE_DEFAULT_OPENING,35,Apertura Nominal de Estrangulador"></textarea>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem;">
            <button type="button" class="btn-well btn-choke" onclick="downloadTemplate()">
                📥 Descargar Plantilla .CSV
            </button>
            <button type="submit" class="btn-well btn-ssv-open" style="font-weight:700;">
                🚀 Validar & Aplicar Parámetros en SCADA
            </button>
        </div>
    </form>
</div>

<!-- Estructura de Ejemplo y Especificaciones -->
<div class="scada-card" style="padding:1.25rem;">
    <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.5rem;">
        2. Especificación Técnica de Parámetros Importables
    </h3>
    <div style="overflow-x:auto;">
        <table class="telemetry-table" style="width:100%; border-collapse:collapse; font-size:0.8rem;">
            <thead>
                <tr>
                    <th style="padding:0.6rem; text-align:left;">CLAVE SCADA</th>
                    <th style="padding:0.6rem; text-align:left;">RANGO VÁLIDO</th>
                    <th style="padding:0.6rem; text-align:left;">UNIDAD</th>
                    <th style="padding:0.6rem; text-align:left;">NORMA ASOCIADA</th>
                    <th style="padding:0.6rem; text-align:left;">DESCRIPCIÓN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:0.6rem; font-family:var(--font-mono); color:#38bdf8;">SIS_PRESSURE_MAX_PSI</td>
                    <td style="padding:0.6rem; font-family:var(--font-mono);">400.0 - 900.0</td>
                    <td style="padding:0.6rem; font-family:var(--font-mono); color:var(--text-muted);">PSI</td>
                    <td style="padding:0.6rem; color:#f59e0b;">IEC 61511 / SIL-2</td>
                    <td style="padding:0.6rem; color:#cbd5e1;">Presión de disparo de alivio SIS</td>
                </tr>
                <tr>
                    <td style="padding:0.6rem; font-family:var(--font-mono); color:#38bdf8;">ESD_TRIP_DELAY_SEC</td>
                    <td style="padding:0.6rem; font-family:var(--font-mono);">0.5 - 10.0</td>
                    <td style="padding:0.6rem; font-family:var(--font-mono); color:var(--text-muted);">seg</td>
                    <td style="padding:0.6rem; color:#f59e0b;">IEC 61511</td>
                    <td style="padding:0.6rem; color:#cbd5e1;">Ventana de amortiguación para ESD-1</td>
                </tr>
                <tr>
                    <td style="padding:0.6rem; font-family:var(--font-mono); color:#38bdf8;">CHOKE_DEFAULT_OPENING</td>
                    <td style="padding:0.6rem; font-family:var(--font-mono);">5 - 80</td>
                    <td style="padding:0.6rem; font-family:var(--font-mono); color:var(--text-muted);">%</td>
                    <td style="padding:0.6rem; color:#34d399;">API 6A</td>
                    <td style="padding:0.6rem; color:#cbd5e1;">Apertura nominal de estrangulador</td>
                </tr>
                <tr>
                    <td style="padding:0.6rem; font-family:var(--font-mono); color:#38bdf8;">RTU_POLLING_INTERVAL_MS</td>
                    <td style="padding:0.6rem; font-family:var(--font-mono);">250 - 5000</td>
                    <td style="padding:0.6rem; font-family:var(--font-mono); color:var(--text-muted);">ms</td>
                    <td style="padding:0.6rem; color:#cbd5e1;">Modbus TCP</td>
                    <td style="padding:0.6rem; color:#cbd5e1;">Tiempo de ciclo de telemetría de campo</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadDemoCsv() {
    const demo = `clave,valor,descripcion
SIS_PRESSURE_MAX_PSI,650.0,Presión Máxima de Alivio SIS
ESD_TRIP_DELAY_SEC,2.5,Retardo de Disparo ESD-1
SSV_CLOSE_TIME_SEC,5.0,Tiempo Máximo Cierre SSV
CHOKE_DEFAULT_OPENING,35,Apertura Nominal de Choke
MAX_FLOW_RATE_BPD,2500,Caudal Máximo Seguro
GAS_OIL_RATIO_MAX,450,Relación Gas-Petróleo Máxima
RTU_POLLING_INTERVAL_MS,1000,Frecuencia Polling RTU
COMM_TIMEOUT_SEC,5,Timeout de Enlace de Red
HISTORIAN_LOG_FREQ_SEC,60,Frecuencia Persistencia Historian`;
    document.getElementById('csvTextInput').value = demo;
}

function downloadTemplate() {
    const content = `clave,valor,descripcion
SIS_PRESSURE_MAX_PSI,650.0,Presión Máxima de Alivio SIS
ESD_TRIP_DELAY_SEC,2.5,Retardo de Disparo ESD-1
CHOKE_DEFAULT_OPENING,35,Apertura Nominal Choke API 6A
RTU_POLLING_INTERVAL_MS,1000,Frecuencia Polling RTU Modbus`;
    const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", "plantilla_parametros_mapaf.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

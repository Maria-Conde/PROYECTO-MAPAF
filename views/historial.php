<?php
/**
 * PROYECTO MAPAF - Vista: Historial de Parámetros & Historian Industrial
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO | SCADA Petrolero · SPS-01
 */

$selectedAsset = $_GET['asset'] ?? 'PZ-LIZ-104';
$selectedParam = $_GET['param'] ?? 'presion';
$selectedRange = $_GET['range'] ?? '24h';

// Generar serie sintética pero realista basada en el parámetro seleccionado
$points = [];
$now = time();
$baseVal = 540.0;
$unit = 'PSI';
$paramName = 'Presión en Cabeza de Pozo (Tubing)';

if ($selectedParam === 'caudal') {
    $baseVal = 1250.0;
    $unit = 'BPD';
    $paramName = 'Caudal de Producción de Crudo';
} elseif ($selectedParam === 'temperatura') {
    $baseVal = 68.5;
    $unit = '°C';
    $paramName = 'Temperatura de Fluido de Línea';
} elseif ($selectedParam === 'choke') {
    $baseVal = 34.0;
    $unit = '%';
    $paramName = 'Apertura de Estrangulador API 6A';
}

$numPoints = 24;
$vals = [];
for ($i = $numPoints - 1; $i >= 0; $i--) {
    $t = $now - ($i * 3600);
    $jitter = (sin($i * 0.5) * 12) + (cos($i * 0.8) * 8) + ((rand(-15, 15) / 10));
    $v = round($baseVal + $jitter, 1);
    $vals[] = $v;
    $points[] = [
        'timestamp' => date('H:i', $t),
        'full_time' => date('Y-m-d H:i:s', $t),
        'valor'     => $v
    ];
}

$avg = round(array_sum($vals) / count($vals), 1);
$max = max($vals);
$min = min($vals);
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
            <h2 class="section-title" style="margin-bottom:0;">📈 Historial de Parámetros & Tendencias</h2>
            <span class="badge-sidebar-pill badge-pill-count" style="font-size:0.75rem;">3 SEÑALES CRÍTICAS</span>
        </div>
        <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
            Historian Redundante IEC 62443 · Trazabilidad Forense de Variables de Campo SPS-01
        </p>
    </div>
    
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <button type="button" class="btn-well btn-choke" onclick="exportarHistorial('csv')">
            📥 Descargar CSV
        </button>
        <button type="button" class="btn-well btn-choke" onclick="exportarHistorial('json')">
            💾 Exportar JSON
        </button>
    </div>
</div>

<!-- Filtros de Consulta -->
<div class="scada-card" style="padding:1rem 1.25rem; margin-bottom:1.5rem;">
    <form method="GET" action="index.php" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; align-items:flex-end;">
        <input type="hidden" name="view" value="historial">
        
        <div>
            <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">EQUIPO / ACTIVO INDUSTRIAL:</label>
            <select name="asset" class="modal-input" style="width:100%; box-sizing:border-box;" onchange="this.form.submit()">
                <option value="PZ-LIZ-104" <?= $selectedAsset === 'PZ-LIZ-104' ? 'selected' : '' ?>>PZ-LIZ-104 (Pozo Productor)</option>
                <option value="TK-ALM-01" <?= $selectedAsset === 'TK-ALM-01' ? 'selected' : '' ?>>TK-ALM-01 (Tanque Fiscal 10k)</option>
                <option value="PZ-ALF-12" <?= $selectedAsset === 'PZ-ALF-12' ? 'selected' : '' ?>>PZ-ALF-12 (Pozo Inyector)</option>
                <option value="SEP-V201" <?= $selectedAsset === 'SEP-V201' ? 'selected' : '' ?>>SEP-V201 (Separador Bifásico)</option>
                <option value="BMB-ESP-01" <?= $selectedAsset === 'BMB-ESP-01' ? 'selected' : '' ?>>BMB-ESP-01 (Bomba ESP)</option>
                <option value="TK-DES-02" <?= $selectedAsset === 'TK-DES-02' ? 'selected' : '' ?>>TK-DES-02 (Tanque Despacho)</option>
            </select>
        </div>

        <div>
            <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">VARIABLE OPERATIVA:</label>
            <select name="param" class="modal-input" style="width:100%; box-sizing:border-box;" onchange="this.form.submit()">
                <option value="presion" <?= $selectedParam === 'presion' ? 'selected' : '' ?>>Presión en Cabeza (PSI)</option>
                <option value="caudal" <?= $selectedParam === 'caudal' ? 'selected' : '' ?>>Caudal de Flujo (BPD)</option>
                <option value="temperatura" <?= $selectedParam === 'temperatura' ? 'selected' : '' ?>>Temperatura de Línea (°C)</option>
                <option value="choke" <?= $selectedParam === 'choke' ? 'selected' : '' ?>>Apertura de Choke API 6A (%)</option>
            </select>
        </div>

        <div>
            <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">VENTANA TEMPORAL:</label>
            <select name="range" class="modal-input" style="width:100%; box-sizing:border-box;" onchange="this.form.submit()">
                <option value="24h" <?= $selectedRange === '24h' ? 'selected' : '' ?>>Últimas 24 Horas (Horario)</option>
                <option value="7d" <?= $selectedRange === '7d' ? 'selected' : '' ?>>Últimos 7 Días (Tendencia)</option>
                <option value="30d" <?= $selectedRange === '30d' ? 'selected' : '' ?>>Mes Actual (Consolidado)</option>
            </select>
        </div>
    </form>
</div>

<!-- KPI Cards Resumen -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">PROMEDIO REGISTRADO</div>
        <div style="font-size:1.5rem; font-weight:800; color:#38bdf8; font-family:var(--font-mono); margin-top:0.25rem;">
            <?= number_format($avg, 1) ?> <span style="font-size:0.8rem; color:#94a3b8;"><?= $unit ?></span>
        </div>
        <div style="font-size:0.72rem; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">● Dentro de consigna nominal</div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">PICO MÁXIMO (PEAK)</div>
        <div style="font-size:1.5rem; font-weight:800; color:#f59e0b; font-family:var(--font-mono); margin-top:0.25rem;">
            <?= number_format($max, 1) ?> <span style="font-size:0.8rem; color:#94a3b8;"><?= $unit ?></span>
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:0.25rem;">Límite Hi-Hi: <?= $baseVal * 1.2 ?> <?= $unit ?></div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">VALOR MÍNIMO (TROUGH)</div>
        <div style="font-size:1.5rem; font-weight:800; color:#cbd5e1; font-family:var(--font-mono); margin-top:0.25rem;">
            <?= number_format($min, 1) ?> <span style="font-size:0.8rem; color:#94a3b8;"><?= $unit ?></span>
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:0.25rem;">Límite Lo-Lo: <?= $baseVal * 0.75 ?> <?= $unit ?></div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">INTEGRIDAD DE MUESTREO</div>
        <div style="font-size:1.5rem; font-weight:800; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">
            100.0 %
        </div>
        <div style="font-size:0.72rem; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">24/24 muestras validadas</div>
    </div>
</div>

<!-- Gráfico de Tendencia SCADA en Canvas -->
<div class="scada-card" style="padding:1.25rem; margin-bottom:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.2rem;">
                Curva de Tendencia: <?= htmlspecialchars($paramName) ?> (<?= htmlspecialchars($selectedAsset) ?>)
            </h3>
            <span style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">Eje Y: <?= $unit ?> | Eje X: Tiempo (Horas)</span>
        </div>
        <div style="display:flex; gap:1rem; font-size:0.75rem; font-family:var(--font-mono);">
            <span style="color:#38bdf8;">■ Señal Realtime</span>
            <span style="color:#ef4444; border-bottom:1px dashed #ef4444;">-- Alarma Hi-Hi (ISA 18.2)</span>
        </div>
    </div>

    <div style="position:relative; width:100%; height:260px;">
        <canvas id="trendChartCanvas" style="width:100%; height:100%;"></canvas>
    </div>
</div>

<!-- Tabla de Muestreo del Historian -->
<div class="scada-card" style="padding:1.25rem;">
    <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.85rem;">
        📋 Muestras Registradas en Historian (Últimas Muestras)
    </h3>
    <div style="overflow-x:auto;">
        <table class="telemetry-table" style="width:100%; border-collapse:collapse; font-size:0.83rem;">
            <thead>
                <tr>
                    <th style="padding:0.75rem; text-align:left;">TIMESTAMP (UTC-5)</th>
                    <th style="padding:0.75rem; text-align:left;">ACTIVO</th>
                    <th style="padding:0.75rem; text-align:left;">PARÁMETRO</th>
                    <th style="padding:0.75rem; text-align:right;">VALOR REGISTRADO</th>
                    <th style="padding:0.75rem; text-align:center;">UNIDAD</th>
                    <th style="padding:0.75rem; text-align:center;">ESTADO CALIDAD</th>
                    <th style="padding:0.75rem; text-align:left;">OPERADOR TURNO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse(array_slice($points, -8)) as $idx => $pt): ?>
                <tr>
                    <td style="padding:0.75rem; font-family:var(--font-mono); color:#cbd5e1;"><?= $pt['full_time'] ?></td>
                    <td style="padding:0.75rem; font-family:var(--font-mono); font-weight:700; color:#38bdf8;"><?= htmlspecialchars($selectedAsset) ?></td>
                    <td style="padding:0.75rem; color:#f8fafc;"><?= htmlspecialchars($paramName) ?></td>
                    <td style="padding:0.75rem; text-align:right; font-family:var(--font-mono); font-weight:700; color:#34d399; font-size:0.95rem;">
                        <?= number_format($pt['valor'], 1) ?>
                    </td>
                    <td style="padding:0.75rem; text-align:center; font-family:var(--font-mono); color:var(--text-muted);"><?= $unit ?></td>
                    <td style="padding:0.75rem; text-align:center;">
                        <span class="badge-status badge-nominal">● GOOD (0xC0)</span>
                    </td>
                    <td style="padding:0.75rem; font-family:var(--font-mono); font-size:0.75rem; color:#94a3b8;">
                        Ing. Rodrigo Valenzuela (EMP-001)
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Renderizado interactivo en Canvas del Gráfico de Tendencias SCADA
(function() {
    const canvas = document.getElementById('trendChartCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    const rawData = <?= json_encode($points) ?>;
    if (!rawData || rawData.length === 0) return;

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * window.devicePixelRatio;
        canvas.height = rect.height * window.devicePixelRatio;
        ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        draw(rect.width, rect.height);
    }

    function draw(w, h) {
        ctx.clearRect(0, 0, w, h);
        const paddingLeft = 55;
        const paddingRight = 20;
        const paddingTop = 25;
        const paddingBottom = 35;
        
        const chartW = w - paddingLeft - paddingRight;
        const chartH = h - paddingTop - paddingBottom;

        const values = rawData.map(d => d.valor);
        const minVal = Math.min(...values) * 0.95;
        const maxVal = Math.max(...values) * 1.05;

        // Grid horizontal
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.07)';
        ctx.lineWidth = 1;
        const steps = 4;
        for (let i = 0; i <= steps; i++) {
            const y = paddingTop + (chartH / steps) * i;
            ctx.beginPath();
            ctx.moveTo(paddingLeft, y);
            ctx.lineTo(w - paddingRight, y);
            ctx.stroke();

            // Labels
            const valLabel = (maxVal - ((maxVal - minVal) / steps) * i).toFixed(1);
            ctx.fillStyle = '#64748b';
            ctx.font = '10px monospace';
            ctx.textAlign = 'right';
            ctx.fillText(valLabel, paddingLeft - 8, y + 3);
        }

        // Puntos de la serie
        const stepX = chartW / (rawData.length - 1);
        const coords = rawData.map((d, i) => {
            const x = paddingLeft + (i * stepX);
            const normY = (d.valor - minVal) / (maxVal - minVal);
            const y = paddingTop + chartH - (normY * chartH);
            return { x, y, label: d.timestamp, val: d.valor };
        });

        // Área bajo la curva con gradiente
        const grad = ctx.createLinearGradient(0, paddingTop, 0, paddingTop + chartH);
        grad.addColorStop(0, 'rgba(56, 189, 248, 0.35)');
        grad.addColorStop(1, 'rgba(56, 189, 248, 0.0)');

        ctx.beginPath();
        ctx.moveTo(coords[0].x, paddingTop + chartH);
        coords.forEach(pt => ctx.lineTo(pt.x, pt.y));
        ctx.lineTo(coords[coords.length - 1].x, paddingTop + chartH);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();

        // Línea principal
        ctx.beginPath();
        ctx.strokeStyle = '#38bdf8';
        ctx.lineWidth = 2.5;
        coords.forEach((pt, i) => {
            if (i === 0) ctx.moveTo(pt.x, pt.y);
            else ctx.lineTo(pt.x, pt.y);
        });
        ctx.stroke();

        // Puntos
        coords.forEach(pt => {
            ctx.beginPath();
            ctx.arc(pt.x, pt.y, 3, 0, Math.PI * 2);
            ctx.fillStyle = '#0ea5e9';
            ctx.fill();
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 1.5;
            ctx.stroke();
        });

        // Labels X
        ctx.fillStyle = '#94a3b8';
        ctx.font = '10px monospace';
        ctx.textAlign = 'center';
        coords.forEach((pt, i) => {
            if (i % 3 === 0 || i === coords.length - 1) {
                ctx.fillText(pt.label, pt.x, h - 10);
            }
        });
    }

    window.addEventListener('resize', resizeCanvas);
    setTimeout(resizeCanvas, 50);
})();

function exportarHistorial(formato) {
    alert("Exportando registros del Historian SCADA en formato " + formato.toUpperCase() + "...");
}
</script>

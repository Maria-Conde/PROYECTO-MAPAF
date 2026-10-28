<?php
/**
 * PROYECTO MAPAF - Layout: Encabezado Superior (Header)
 * SENA ADSO - Ficha 3413988
 */
try {
    $telemetryService = new TelemetryService();
    $kpis = $telemetryService->getConsolidatedKpis();
    $esdService = new EsdService();
    $esdState = $esdService->getSystemState();
} catch (Throwable $e) {
    $kpis = [
        'total_production'    => 0,
        'avg_tubing_pressure' => 0.0,
        'avg_temperature'     => 0.0,
        'active_wells_count'  => 0,
        'total_wells_count'   => 0
    ];
    $esdState = [
        'esd_triggered'    => false,
        'esd_trigger_time' => '',
        'flow_state'       => 'degraded'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/scada.css">
</head>
<body>
    <header class="app-header">
        <div class="header-brand">
            <div class="brand-dot"></div>
            <div>
                <div class="brand-title">
                    <span>PROYECTO MAPAF</span>
                    <span style="font-size:0.65rem; background:#1e293b; color:#38bdf8; padding:0.15rem 0.5rem; border-radius:4px; font-family:var(--font-mono); font-weight:normal;">XAMPP · PHP 8 · MySQL</span>
                </div>
                <div class="brand-subtitle">SCADA UPSTREAM OIL & GAS · ESTACIÓN TERMINAL NEIVA</div>
            </div>
        </div>

        <div class="header-kpis">
            <div>
                <span class="kpi-unit">PRODUCCIÓN:</span>
                <span class="kpi-val" id="topKpiFlow"><?= number_format($kpis['total_production']) ?> BPD</span>
            </div>
            <div>
                <span class="kpi-unit">POZOS ACTIVOS:</span>
                <span class="kpi-val" id="topKpiWells"><?= $kpis['active_wells_count'] ?> / <?= $kpis['total_wells_count'] ?></span>
            </div>
            <div>
                <span class="kpi-unit">PRESIÓN PROM:</span>
                <span class="kpi-val" id="topKpiPressure"><?= $kpis['avg_tubing_pressure'] ?> PSI</span>
            </div>
        </div>

        <div class="header-actions">
            <select id="operatorSelect" class="operator-select" title="Seleccionar Operador de Turno">
                <option value="EMP-8812">EMP-8812 · M. Conde Dussan (Administrador)</option>
            </select>

            <button type="button" id="btnEsdTop" class="btn-esd-top" title="Parada de Emergencia ESD-1">
                ⚠️ PARADA ESD-1
            </button>
        </div>
    </header>

    <div class="app-container">

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
    <div class="app-container">

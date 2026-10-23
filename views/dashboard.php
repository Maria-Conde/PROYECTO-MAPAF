<?php
/**
 * PROYECTO MAPAF - Vista: Tablero General SCADA (Dashboard)
 * SENA ADSO - Ficha 3413988
 */
$telemetriaModel = new TelemetriaModel();
$pozoModel = new PozoModel();
$alarmaModel = new AlarmaModel();

$metrics = $telemetriaModel->getAll();
$wells = $pozoModel->getAll();
$alarms = $alarmaModel->getAll(true); // Solo pendientes
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
    <div>
        <h2 class="section-title" style="margin-bottom:0.25rem;">📡 Telemetría de Proceso en Tiempo Real</h2>
        <p style="font-size:0.8rem; color:var(--text-muted); font-family:var(--font-mono);">
            Sondeo adaptativo cada 3.0s · Conexión PDO UTF-8 a MySQL (mapaf_db)
        </p>
    </div>
    <span class="badge-status badge-nominal" style="padding:0.4rem 0.8rem;">● TRANSMISORES ONLINE</span>
</div>

<!-- Tarjetas de Telemetría (Sensores) -->
<div class="grid-kpis">
    <?php foreach ($metrics as $m): ?>
    <div class="scada-card">
        <div class="card-header-flex">
            <div>
                <div class="card-tag"><?= htmlspecialchars($m['tag']) ?></div>
                <div class="card-name"><?= htmlspecialchars($m['etiqueta']) ?></div>
            </div>
            <span id="badge-<?= $m['id'] ?>" class="badge-status badge-<?= $m['severidad'] ?>">
                <?= strtoupper($m['severidad']) ?>
            </span>
        </div>
        <div class="card-value">
            <span id="val-<?= $m['id'] ?>"><?= number_format((float)$m['valor'], 1) ?></span>
            <span class="card-unit"><?= htmlspecialchars($m['unidad']) ?></span>
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono); display:flex; justify-content:space-between;">
            <span>Obj: <?= number_format((float)$m['valor_objetivo'], 1) ?></span>
            <span>Trip Alto: <?= number_format((float)$m['trip_alto'], 1) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Cabezales de Pozo en Vivo -->
<div style="margin-top:2rem;">
    <h2 class="section-title">🛢️ Estado Operativo de Cabezales (API 6A)</h2>
    <div class="grid-wells">
        <?php foreach ($wells as $w): 
            $badgeClass = 'badge-online';
            if ($w['estado'] === 'DISPARADO') $badgeClass = 'badge-tripped';
            elseif ($w['estado'] === 'ESTRANGULADO') $badgeClass = 'badge-throttled';
            elseif ($w['estado'] === 'MANTENIMIENTO') $badgeClass = 'badge-maintenance';
        ?>
        <div class="scada-card">
            <div class="card-header-flex">
                <div>
                    <div class="card-tag"><?= htmlspecialchars($w['sector']) ?></div>
                    <div class="card-name"><?= htmlspecialchars($w['nombre']) ?></div>
                </div>
                <span id="wellStatus-<?= $w['id'] ?>" class="badge-status <?= $badgeClass ?>">
                    <?= htmlspecialchars($w['estado']) ?>
                </span>
            </div>

            <div class="well-metrics">
                <div>
                    <div class="well-metric-lbl">PRESIÓN WHTP:</div>
                    <div class="well-metric-val"><?= number_format((float)$w['presion_tubing'], 1) ?> PSI</div>
                </div>
                <div>
                    <div class="well-metric-lbl">CAUDAL FLOW:</div>
                    <div class="well-metric-val"><?= number_format((float)$w['caudal_produccion'], 1) ?> BPD</div>
                </div>
                <div>
                    <div class="well-metric-lbl">CASING ANULAR:</div>
                    <div class="well-metric-val"><?= number_format((float)$w['presion_casing'], 1) ?> PSI</div>
                </div>
                <div>
                    <div class="well-metric-lbl">TEMP FLUIDO:</div>
                    <div class="well-metric-val"><?= number_format((float)$w['temperatura'], 1) ?> °C</div>
                </div>
            </div>

            <div class="choke-control-row">
                <div class="choke-header">
                    <span style="color:var(--text-muted);">Apertura Estrangulador (Choke):</span>
                    <strong id="chokeVal-<?= $w['id'] ?>" style="color:var(--primary);"><?= number_format((float)$w['apertura_choke'], 1) ?>%</strong>
                </div>
                <input type="range" min="0" max="100" step="0.5" 
                       value="<?= (float)$w['apertura_choke'] ?>" 
                       id="chokeSlider-<?= $w['id'] ?>" 
                       class="choke-slider"
                       onchange="SCADA.setChoke('<?= $w['id'] ?>', this.value)"
                       oninput="document.getElementById('chokeVal-<?= $w['id'] ?>').textContent = parseFloat(this.value).toFixed(1) + '%'">
            </div>

            <div class="well-actions">
                <button type="button" id="ssvBtn-<?= $w['id'] ?>" 
                        class="btn-well <?= ($w['valvula_ssv_abierta'] == 1) ? 'btn-ssv-open' : 'btn-ssv-closed' ?>"
                        onclick="SCADA.toggleSsv('<?= $w['id'] ?>', <?= ($w['valvula_ssv_abierta'] == 1) ? 'true' : 'false' ?>)">
                    <?= ($w['valvula_ssv_abierta'] == 1) ? 'SSV: ABIERTA' : 'SSV: CERRADA' ?>
                </button>

                <?php if ($w['estado'] === 'DISPARADO'): ?>
                <button type="button" class="btn-well" style="border-color:#38bdf8; color:#38bdf8;" 
                        onclick="SCADA.resetWell('<?= $w['id'] ?>')">
                    🔄 REARMAR POZO
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Alarmas Pendientes -->
<div style="margin-top:2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 class="section-title" style="margin-bottom:0;">🔔 Alarmas Activas Pendientes (ANSI/ISA-18.2)</h2>
        <a href="index.php?view=alarms" style="color:#38bdf8; font-size:0.8rem; text-decoration:none; font-family:var(--font-mono);">
            Ver consola completa &rarr;
        </a>
    </div>

    <div class="scada-table-container">
        <table class="scada-table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Tag</th>
                    <th>Subsistema</th>
                    <th>Descripción</th>
                    <th>Valor Leído</th>
                    <th>Severidad</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alarms)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:1.5rem; color:var(--primary);">
                        ✓ No hay alarmas pendientes de reconocimiento en este momento.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($alarms as $a): ?>
                    <tr>
                        <td style="font-family:var(--font-mono);"><?= htmlspecialchars($a['timestamp_evento']) ?></td>
                        <td><strong style="color:#fff; font-family:var(--font-mono);"><?= htmlspecialchars($a['tag']) ?></strong></td>
                        <td><?= htmlspecialchars($a['subsistema']) ?></td>
                        <td><?= htmlspecialchars($a['descripcion']) ?></td>
                        <td style="font-family:var(--font-mono); color:#fbbf24;"><?= htmlspecialchars($a['valor']) ?></td>
                        <td>
                            <span class="badge-status badge-<?= $a['severidad'] ?>">
                                <?= strtoupper($a['severidad']) ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn-confirm" style="padding:0.35rem 0.75rem; font-size:0.75rem;"
                                    onclick="SCADA.openAckModal('<?= $a['id'] ?>', '<?= htmlspecialchars($a['tag']) ?>')">
                                Acusar ISA-18.2
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

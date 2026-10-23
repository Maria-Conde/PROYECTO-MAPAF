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
$kpis = $telemetriaModel->getPlantKpis();
?>

<!-- Banner Hero con Imagen de Sala de Control RTOC -->
<div class="hero-scada-banner">
    <img src="assets/images/scada_control_room.jpg" alt="Sala de Control SCADA RTOC" class="hero-scada-bg">
    <div class="hero-scada-gradient"></div>
    <div class="hero-scada-content">
        <div>
            <span class="badge-status badge-nominal" style="margin-bottom:0.4rem; padding:0.25rem 0.65rem;">
                ● SALA DE CONTROL RTOC HUILA · OPERATIVA
            </span>
            <h1 style="font-family:var(--font-heading); font-size:1.6rem; color:#fff; font-weight:700; text-shadow: 0 2px 10px rgba(0,0,0,0.8);">
                Supervisión Táctica de Producción & Telemetría IIoT
            </h1>
            <p style="color:#cbd5e1; font-size:0.85rem; font-family:var(--font-mono); margin-top:0.2rem;">
                Monitoreo Instrumentado de Pozos · Enclavamientos API 6A · Alarmas ANSI/ISA-18.2
            </p>
        </div>
        <div style="display:flex; gap:0.75rem; align-items:center;">
            <div style="background:rgba(15, 23, 42, 0.85); backdrop-filter:blur(6px); border:1px solid var(--border-color); padding:0.5rem 1rem; border-radius:8px; text-align:right;">
                <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">CAPACIDAD DE PLANTA</div>
                <div style="font-size:1.15rem; font-weight:700; color:#38bdf8; font-family:var(--font-mono);">
                    <?= number_format($kpis['total_production']) ?> <span style="font-size:0.75rem; color:#94a3b8;">BPD</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header de Telemetría -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
    <div>
        <h2 class="section-title" style="margin-bottom:0.25rem;">📡 Sensores y Transmisores en Tiempo Real</h2>
        <p style="font-size:0.8rem; color:var(--text-muted); font-family:var(--font-mono);">
            Transmisión continua de telemetría · Persistencia relacional MySQL (mapaf_db)
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

<!-- SECCIÓN: GRÁFICOS Y CURVAS DE TENDENCIA EN VIVO -->
<div style="margin-top:2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <div>
            <h2 class="section-title" style="margin-bottom:0.25rem;">📈 Curvas de Tendencia y Comportamiento de Planta</h2>
            <p style="font-size:0.8rem; color:var(--text-muted); font-family:var(--font-mono);">
                Gráficos analíticos dinámicos de presión, caudal y frecuencia con muestreo cada 3 segundos
            </p>
        </div>
        <span class="badge-status badge-nominal">● HISTORIAN ACTIVO</span>
    </div>

    <div class="chart-grid">
        <!-- Gráfico 1: Curva de Presiones WHTP vs Casing -->
        <div class="scada-chart-card">
            <div class="scada-chart-header">
                <div class="scada-chart-title">
                    <span>⚡ Presión Cabezal (WHTP) vs Anular (Casing)</span>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background:#38bdf8;"></div>
                        <span style="color:#cbd5e1;">WHTP (PSI)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background:#fbbf24;"></div>
                        <span style="color:#cbd5e1;">Casing (PSI)</span>
                    </div>
                </div>
            </div>
            <div class="canvas-container">
                <canvas id="chartPressure" class="scada-canvas"></canvas>
            </div>
        </div>

        <!-- Gráfico 2: Curva de Producción Neta (BPD) -->
        <div class="scada-chart-card">
            <div class="scada-chart-header">
                <div class="scada-chart-title">
                    <span>🛢️ Tasa de Producción Bruta vs Objetivo</span>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background:#10b981;"></div>
                        <span style="color:#cbd5e1;">Actual (BPD)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background:#a855f7;"></div>
                        <span style="color:#cbd5e1;">Target 15K BPD</span>
                    </div>
                </div>
            </div>
            <div class="canvas-container">
                <canvas id="chartProduction" class="scada-canvas"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN: DIAGRAMA DE PROCESO E INSTRUMENTACIÓN P&ID ILUSTRADO -->
<div class="pid-diagram-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h3 style="color:#fff; font-size:1rem; font-weight:700; display:flex; align-items:center; gap:0.5rem;">
                <span>🔬 Diagrama de Flujo de Proceso P&ID Dinámico</span>
                <span class="badge-status badge-nominal" style="font-size:0.65rem;">ANIMACIÓN DE FLUJO ACTIVA</span>
            </h3>
            <p style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:0.2rem;">
                Topología: Cabezales de Pozo ➔ Choke FCV-101 ➔ Manifold Recolección ➔ Separador V-201 ➔ Oleoducto
            </p>
        </div>
        <div style="font-family:var(--font-mono); font-size:0.75rem; color:#38bdf8;">
            ENCLAVAMIENTO SIS: <strong style="color:#34d399;">LIBERADO (SIL-3)</strong>
        </div>
    </div>

    <svg class="pid-svg" viewBox="0 0 920 200" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="pipeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#10b981"/>
                <stop offset="50%" stop-color="#38bdf8"/>
                <stop offset="100%" stop-color="#10b981"/>
            </linearGradient>
            <filter id="glow">
                <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                <feMerge>
                    <feMergeNode in="coloredBlur"/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>
        </defs>

        <!-- Tuberías Base -->
        <path d="M 60 100 L 220 100 L 220 70 L 380 70 L 380 100 L 520 100 L 680 100 L 860 100" class="flow-pipe" />
        
        <!-- Línea animada de flujo de hidrocarburo -->
        <path d="M 60 100 L 220 100 L 220 70 L 380 70 L 380 100 L 520 100 L 680 100 L 860 100" class="flow-active" />

        <!-- Nodo 1: Cabezal Alfa-12 -->
        <g transform="translate(60, 100)">
            <rect x="-40" y="-35" width="80" height="70" rx="8" fill="#10182c" stroke="#38bdf8" stroke-width="2"/>
            <text x="0" y="-12" fill="#38bdf8" font-size="10" font-family="JetBrains Mono" font-weight="bold" text-anchor="middle">WHTP-101A</text>
            <text x="0" y="6" fill="#fff" font-size="11" font-family="Inter" font-weight="bold" text-anchor="middle">ALFA-12</text>
            <text x="0" y="24" fill="#34d399" font-size="10" font-family="JetBrains Mono" text-anchor="middle">1,842 PSI</text>
        </g>

        <!-- Nodo 2: Estrangulador Choke -->
        <g transform="translate(220, 70)">
            <circle cx="0" cy="0" r="24" fill="#10182c" stroke="#10b981" stroke-width="2"/>
            <polygon points="-12,-12 12,12 -12,12 12,-12" fill="none" stroke="#10b981" stroke-width="2"/>
            <text x="0" y="-30" fill="#cbd5e1" font-size="9" font-family="JetBrains Mono" text-anchor="middle">CHOKE FCV-101</text>
            <text x="0" y="38" fill="#10b981" font-size="10" font-family="JetBrains Mono" font-weight="bold" text-anchor="middle">42.8%</text>
        </g>

        <!-- Nodo 3: Manifold Recolección -->
        <g transform="translate(380, 85)">
            <rect x="-35" y="-30" width="70" height="60" rx="6" fill="#10182c" stroke="#a855f7" stroke-width="2"/>
            <text x="0" y="-10" fill="#a855f7" font-size="9" font-family="JetBrains Mono" font-weight="bold" text-anchor="middle">MAN-400</text>
            <text x="0" y="8" fill="#fff" font-size="10" font-family="Inter" font-weight="bold" text-anchor="middle">MANIFOLD</text>
            <text x="0" y="22" fill="#fbbf24" font-size="9" font-family="JetBrains Mono" text-anchor="middle">78.4 °C</text>
        </g>

        <!-- Nodo 4: Separador Trifásico V-201 -->
        <g transform="translate(580, 100)">
            <ellipse cx="0" cy="0" rx="55" ry="32" fill="#10182c" stroke="#38bdf8" stroke-width="2"/>
            <text x="0" y="-8" fill="#38bdf8" font-size="10" font-family="JetBrains Mono" font-weight="bold" text-anchor="middle">SEP V-201</text>
            <text x="0" y="8" fill="#fff" font-size="10" font-family="Inter" font-weight="bold" text-anchor="middle">TRIFÁSICO</text>
            <text x="0" y="24" fill="#34d399" font-size="9" font-family="JetBrains Mono" text-anchor="middle">450 PSI</text>
        </g>

        <!-- Nodo 5: Fiscalización y Oleoducto -->
        <g transform="translate(780, 100)">
            <rect x="-45" y="-30" width="90" height="60" rx="8" fill="#10182c" stroke="#10b981" stroke-width="2"/>
            <text x="0" y="-10" fill="#34d399" font-size="9" font-family="JetBrains Mono" font-weight="bold" text-anchor="middle">FISCALIZACIÓN</text>
            <text x="0" y="6" fill="#fff" font-size="10" font-family="Inter" font-weight="bold" text-anchor="middle">OLEODUCTO</text>
            <text x="0" y="22" fill="#34d399" font-size="10" font-family="JetBrains Mono" font-weight="bold" text-anchor="middle">14,820 BPD</text>
        </g>
    </svg>
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

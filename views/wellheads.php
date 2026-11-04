<?php
/**
 * PROYECTO MAPAF - Vista: Control de Cabezales de Pozo (API 6A)
 * SENA ADSO - Ficha 3413988
 */
$wells = [];
try {
    $wellheadService = new WellheadService();
    $wells = $wellheadService->getAllWells();
} catch (Throwable $e) {
    // Degrada graciosamente
}
?>

<div style="margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 class="section-title" style="margin-bottom:0.25rem;">🛢️ Consola de Cabezales de Producción (API 6A)</h2>
        <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
            Control de Estranguladores de Flujo (Choke) · Válvulas de Seguridad SSV · Telemetría en Boca de Pozo
        </p>
    </div>
    <div style="display:flex; gap:0.5rem; align-items:center;">
        <span class="badge-status badge-nominal">● NORMA API 6A</span>
        <span class="badge-status badge-advisory">ENCLAVAMIENTO SIL-3</span>
    </div>
</div>

<!-- Tarjetas de Resumen Operativo de Cabezales -->
<div class="grid-kpis" style="margin-bottom:1.5rem;">
    <div class="scada-card" style="padding:1rem;">
        <div class="card-tag">TASA AGREGADA</div>
        <div class="card-value" style="font-size:1.4rem; color:#34d399; margin:0.3rem 0;">
            14,820 <span style="font-size:0.75rem; color:#94a3b8;">BPD</span>
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
            PAD 04 & 09 Operativos
        </div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div class="card-tag">POZOS MONITOREADOS</div>
        <div class="card-value" style="font-size:1.4rem; color:#38bdf8; margin:0.3rem 0;">
            <?= count($wells) ?> Cabezales
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
            Presión WHTP Redundante
        </div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div class="card-tag">LÍMITE CASING</div>
        <div class="card-value" style="font-size:1.4rem; color:#fbbf24; margin:0.3rem 0;">
            950 <span style="font-size:0.75rem; color:#94a3b8;">PSI</span>
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
            Umbral Trip de Seguridad
        </div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div class="card-tag">SISTEMA SIS</div>
        <div class="card-value" style="font-size:1.4rem; color:#34d399; margin:0.3rem 0;">
            NORMAL
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
            Respuesta ESD-1 &lt; 1.2s
        </div>
    </div>
</div>

<!-- Tabla de Control de Pozos -->
<div class="scada-table-container">
    <table class="scada-table">
        <thead>
            <tr>
                <th>Pozo</th>
                <th>Sector</th>
                <th>Estado API 6A</th>
                <th>Presión Tubing</th>
                <th>Presión Casing</th>
                <th>Caudal Flow</th>
                <th>Apertura Choke</th>
                <th>Válvula SSV</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($wells as $w): 
                $badgeClass = 'badge-online';
                if ($w['estado'] === 'DISPARADO') $badgeClass = 'badge-tripped';
                elseif ($w['estado'] === 'ESTRANGULADO') $badgeClass = 'badge-throttled';
                elseif ($w['estado'] === 'MANTENIMIENTO') $badgeClass = 'badge-maintenance';
            ?>
            <tr>
                <td>
                    <strong style="color:#fff;"><?= htmlspecialchars($w['nombre']) ?></strong>
                    <div style="font-size:0.7rem; color:var(--text-muted); font-family:var(--font-mono);"><?= htmlspecialchars($w['id']) ?></div>
                </td>
                <td><?= htmlspecialchars($w['sector']) ?></td>
                <td>
                    <span id="wellStatus-<?= $w['id'] ?>" class="badge-status <?= $badgeClass ?>">
                        <?= htmlspecialchars($w['estado']) ?>
                    </span>
                </td>
                <td style="font-family:var(--font-mono); font-weight:600;"><?= number_format((float)$w['presion_tubing'], 1) ?> PSI</td>
                <td style="font-family:var(--font-mono); color:#fbbf24;"><?= number_format((float)$w['presion_casing'], 1) ?> PSI</td>
                <td style="font-family:var(--font-mono); color:#34d399; font-weight:600;"><?= number_format((float)$w['caudal_produccion'], 1) ?> BPD</td>
                <td style="width:220px;">
                    <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.25rem;">
                        <span>Consigna:</span>
                        <strong id="chokeVal-<?= $w['id'] ?>" style="color:var(--primary);"><?= number_format((float)$w['apertura_choke'], 1) ?>%</strong>
                    </div>
                    <input type="range" min="0" max="100" step="0.5" 
                           value="<?= (float)$w['apertura_choke'] ?>" 
                           id="chokeSlider-<?= $w['id'] ?>" 
                           class="choke-slider"
                           onchange="SCADA.setChoke('<?= $w['id'] ?>', this.value)"
                           oninput="document.getElementById('chokeVal-<?= $w['id'] ?>').textContent = parseFloat(this.value).toFixed(1) + '%'">
                </td>
                <td>
                    <button type="button" id="ssvBtn-<?= $w['id'] ?>" 
                            class="btn-well <?= ($w['valvula_ssv_abierta'] == 1) ? 'btn-ssv-open' : 'btn-ssv-closed' ?>"
                            onclick="SCADA.toggleSsv('<?= $w['id'] ?>', <?= ($w['valvula_ssv_abierta'] == 1) ? 'true' : 'false' ?>)"
                            style="padding:0.4rem 0.8rem;">
                        <?= ($w['valvula_ssv_abierta'] == 1) ? 'ABIERTA' : 'CERRADA' ?>
                    </button>
                </td>
                <td>
                    <?php if ($w['estado'] === 'DISPARADO'): ?>
                    <button type="button" class="btn-confirm" style="font-size:0.75rem; padding:0.4rem 0.75rem;" 
                            onclick="SCADA.resetWell('<?= $w['id'] ?>')">
                        Rearmar
                    </button>
                    <?php else: ?>
                    <span style="color:var(--text-dim); font-size:0.75rem;">Operación Normal</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
/**
 * PROYECTO MAPAF - Vista: Control de Cabezales de Pozo (API 6A)
 * SENA ADSO - Ficha 3413988
 */
$wells = [];
try {
    $pozoModel = new PozoModel();
    $wells = $pozoModel->getAll();
} catch (Throwable $e) {
    // Degrada graciosamente
}
?>

<div style="margin-bottom:1.5rem;">
    <h2 class="section-title">🛢️ Consola de Cabezales de Producción (API 6A)</h2>
    <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
        Control de Estranguladores de Flujo Choke · Válvulas de Seguridad de Superficie SSV · Monitoreo Anular
    </p>
</div>

<!-- Tarjeta Técnica con Imagen Isométrica de Cabezal API 6A -->
<div class="wellhead-technical-card">
    <div style="position:relative; overflow:hidden;">
        <img src="assets/images/scada_wellhead_isometric.jpg" alt="Esquema Isométrico Cabezal API 6A" class="wellhead-technical-img">
        <div style="position:absolute; bottom:0; left:0; width:100%; background:linear-gradient(180deg, transparent, rgba(15,23,42,0.95)); padding:1rem;">
            <span class="badge-status badge-advisory" style="font-size:0.7rem;">ESQUEMA INSTRUMENTADO API 6A</span>
            <div style="font-size:0.85rem; color:#fff; font-weight:600; margin-top:0.25rem;">
                Árbol de Navidad (Christmas Tree) & Válvula Choke FCV
            </div>
        </div>
    </div>
    
    <div style="padding:1.5rem; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                <h3 style="color:#38bdf8; font-size:1.05rem; font-weight:700;">Anatomía Operativa del Pozo</h3>
                <span class="badge-status badge-nominal">NORMA API 6A / 10,000 PSI</span>
            </div>
            <p style="font-size:0.8rem; color:#94a3b8; line-height:1.5; margin-bottom:1rem;">
                Cada cabezal de pozo cuenta con instrumentación redundante SIL-3 para monitorizar la presión de tubing (WHTP) y anular casing, gobernado por un estrangulador angular de aguja con accionamiento electrohidráulico.
            </p>

            <div style="display:flex; flex-direction:column; gap:0.55rem; font-size:0.78rem; font-family:var(--font-mono);">
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-color); padding-bottom:0.35rem;">
                    <span style="color:var(--text-muted);">Válvula Maestra Inferior:</span>
                    <span style="color:#34d399; font-weight:bold;">Hidráulica Actuada (SSV)</span>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-color); padding-bottom:0.35rem;">
                    <span style="color:var(--text-muted);">Actuador de Choke:</span>
                    <span style="color:#38bdf8; font-weight:bold;">Posicionador Digital 4-20mA</span>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid var(--border-color); padding-bottom:0.35rem;">
                    <span style="color:var(--text-muted);">Límite de Presión Casing:</span>
                    <span style="color:#fbbf24; font-weight:bold;">Trip a 950 PSI</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:var(--text-muted);">Enclavamiento SIS:</span>
                    <span style="color:#f43f5e; font-weight:bold;">Cierre por ESD-1 &lt; 1.2s</span>
                </div>
            </div>
        </div>

        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:0.75rem 1rem; margin-top:1rem; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
                Tasa Agregada PAD 04 & 09:
            </div>
            <div style="font-size:1.1rem; color:#34d399; font-family:var(--font-mono); font-weight:700;">
                14,820 BPD
            </div>
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

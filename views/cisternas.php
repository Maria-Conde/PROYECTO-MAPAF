<?php
/**
 * PROYECTO MAPAF - Vista: Despacho de Cisternas & Cargadero de Crudo
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO | SCADA Petrolero · SPS-01
 */

$despachos = [];
$islas = [];
$stats = [
    'total_hoy'      => 8,
    'en_llenado'     => 1,
    'volumen_hoy'    => 1850,
    'islas_activas'  => 3
];

try {
    $cisternasService = new CisternasService();
    $despachos = $cisternasService->getAllDespachos();
    $islas = $cisternasService->getIslasStatus();
    
    $stats['total_hoy'] = count($despachos);
    $vol = 0;
    $llenando = 0;
    foreach ($despachos as $d) {
        $vol += (float)($d['volumen_cargado'] ?? 0);
        if ($d['estado'] === 'EN LLENADO') $llenando++;
    }
    $stats['volumen_hoy'] = $vol;
    $stats['en_llenado'] = $llenando;
} catch (Throwable $e) {}
?>

<!-- Encabezado del Módulo -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
            <h2 class="section-title" style="margin-bottom:0;">🚛 Despacho de Cisternas & Cargadero</h2>
            <span class="badge-sidebar-pill badge-pill-isla" style="font-size:0.75rem;">3 ISLAS EN SERVICIO</span>
        </div>
        <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
            Fiscalización de Despacho Terrestre de Crudos y Diluyentes · Medición Masiva Coriolis API MPMS
        </p>
    </div>
    
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <button type="button" class="btn-well btn-ssv-open" onclick="openModalDespacho()">
            ➕ Programar Cisterna
        </button>
        <button type="button" class="btn-well btn-choke" onclick="exportarManifiestos()">
            📄 Exportar Manifiestos
        </button>
    </div>
</div>

<!-- Resumen de Islas de Carga en Tiempo Real -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.25rem; margin-bottom:1.5rem;">

    <!-- Isla 1 -->
    <div class="scada-card" style="border-top: 3px solid #38bdf8;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
            <div>
                <span style="font-size:0.65rem; color:var(--text-muted); font-family:var(--font-mono); font-weight:700;">ISLA DE CARGA 01</span>
                <h3 style="font-size:1.05rem; color:#f8fafc; font-weight:700; margin-top:0.15rem;">Crudo Liviano Cusiana</h3>
                <div style="font-size:0.75rem; color:#94a3b8; font-family:var(--font-mono);">API 32° · Medidor Coriolis FE-101</div>
            </div>
            <span class="badge-status <?= ($islas['isla_1']['estado'] ?? '') === 'EN LLENADO' ? 'badge-nominal' : 'badge-standby' ?>">
                <?= ($islas['isla_1']['estado'] ?? '') === 'EN LLENADO' ? '● EN LLENADO' : '● DISPONIBLE' ?>
            </span>
        </div>

        <div style="background:#090e1d; border-radius:8px; padding:0.75rem; margin-bottom:0.85rem; border:1px solid rgba(255,255,255,0.05);">
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.4rem;">
                <span style="color:var(--text-muted);">Cisterna Asignada:</span>
                <span style="font-weight:700; color:#38bdf8;"><?= $islas['isla_1']['cisterna'] ?? 'TLF-482 (Coltanques)' ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.4rem;">
                <span style="color:var(--text-muted);">Caudal de Bombeo:</span>
                <span style="font-weight:700; color:#34d399;">18.5 BBL/min</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono);">
                <span style="color:var(--text-muted);">Presión en Manifold:</span>
                <span style="font-weight:700; color:#f8fafc;">45.2 PSI</span>
            </div>
        </div>

        <div>
            <div style="display:flex; justify-content:space-between; font-size:0.72rem; font-family:var(--font-mono); margin-bottom:0.3rem;">
                <span style="color:var(--text-muted);">Progreso de Lote (220 BBL):</span>
                <span style="font-weight:700; color:#38bdf8;"><?= $islas['isla_1']['progreso'] > 0 ? $islas['isla_1']['progreso'] : 64 ?>%</span>
            </div>
            <div style="background:#0f172a; border-radius:6px; height:10px; overflow:hidden; border:1px solid rgba(255,255,255,0.1);">
                <div style="width:<?= $islas['isla_1']['progreso'] > 0 ? $islas['isla_1']['progreso'] : 64 ?>%; height:100%; background:linear-gradient(90deg, #0284c7, #38bdf8); transition:width 0.4s ease;"></div>
            </div>
        </div>
    </div>

    <!-- Isla 2 -->
    <div class="scada-card" style="border-top: 3px solid #f59e0b;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
            <div>
                <span style="font-size:0.65rem; color:var(--text-muted); font-family:var(--font-mono); font-weight:700;">ISLA DE CARGA 02</span>
                <h3 style="font-size:1.05rem; color:#f8fafc; font-weight:700; margin-top:0.15rem;">Crudo Pesado Castilla</h3>
                <div style="font-size:0.75rem; color:#94a3b8; font-family:var(--font-mono);">API 18° · Medidor Coriolis FE-102</div>
            </div>
            <span class="badge-status badge-standby">
                ● DISPONIBLE
            </span>
        </div>

        <div style="background:#090e1d; border-radius:8px; padding:0.75rem; margin-bottom:0.85rem; border:1px solid rgba(255,255,255,0.05);">
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.4rem;">
                <span style="color:var(--text-muted);">Cisterna Asignada:</span>
                <span style="font-weight:700; color:#94a3b8;">Sin cisterna acoplada</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.4rem;">
                <span style="color:var(--text-muted);">Caudal de Bombeo:</span>
                <span style="font-weight:700; color:#94a3b8;">0.0 BBL/min</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono);">
                <span style="color:var(--text-muted);">Presión en Manifold:</span>
                <span style="font-weight:700; color:#f8fafc;">12.1 PSI</span>
            </div>
        </div>

        <div>
            <div style="display:flex; justify-content:space-between; font-size:0.72rem; font-family:var(--font-mono); margin-bottom:0.3rem;">
                <span style="color:var(--text-muted);">Brazo de Carga:</span>
                <span style="font-weight:700; color:#34d399;">Retraído & Bloqueado</span>
            </div>
            <div style="background:#0f172a; border-radius:6px; height:10px; overflow:hidden; border:1px solid rgba(255,255,255,0.1);">
                <div style="width:0%; height:100%; background:#f59e0b;"></div>
            </div>
        </div>
    </div>

    <!-- Isla 3 -->
    <div class="scada-card" style="border-top: 3px solid #10b981;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
            <div>
                <span style="font-size:0.65rem; color:var(--text-muted); font-family:var(--font-mono); font-weight:700;">ISLA DE CARGA 03</span>
                <h3 style="font-size:1.05rem; color:#f8fafc; font-weight:700; margin-top:0.15rem;">Nafta Virgen / Diluyente</h3>
                <div style="font-size:0.75rem; color:#94a3b8; font-family:var(--font-mono);">API 68° · Medidor Coriolis FE-103</div>
            </div>
            <span class="badge-status badge-nominal">
                ● AUTORIZADA
            </span>
        </div>

        <div style="background:#090e1d; border-radius:8px; padding:0.75rem; margin-bottom:0.85rem; border:1px solid rgba(255,255,255,0.05);">
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.4rem;">
                <span style="color:var(--text-muted);">Cisterna Asignada:</span>
                <span style="font-weight:700; color:#34d399;">WFG-910 (Transpetrol)</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.4rem;">
                <span style="color:var(--text-muted);">Puesta a Tierra (Ground):</span>
                <span style="font-weight:700; color:#34d399;">Conectada OK (Verde)</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono);">
                <span style="color:var(--text-muted);">Sobrellenado Óptico:</span>
                <span style="font-weight:700; color:#34d399;">Sensor Activo</span>
            </div>
        </div>

        <div>
            <div style="display:flex; justify-content:space-between; font-size:0.72rem; font-family:var(--font-mono); margin-bottom:0.3rem;">
                <span style="color:var(--text-muted);">Inicio de Carga:</span>
                <span style="font-weight:700; color:#34d399;">En Espera de Válvula</span>
            </div>
            <div style="background:#0f172a; border-radius:6px; height:10px; overflow:hidden; border:1px solid rgba(255,255,255,0.1);">
                <div style="width:15%; height:100%; background:linear-gradient(90deg, #059669, #10b981);"></div>
            </div>
        </div>
    </div>

</div>

<!-- Tabla de Despachos de Cisternas -->
<div class="scada-card" style="padding:1.25rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.2rem;">
                📋 Registro de Despachos & Guías de Transporte Fiscal
            </h3>
            <p style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-mono);">
                Control de precintos, peso en báscula electrónica y volumen estándar a 60°F
            </p>
        </div>
        <span class="badge-status badge-nominal" style="padding:0.35rem 0.75rem;">
            <?= count($despachos) ?> REGISTROS ACTIVOS
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table class="telemetry-table" style="width:100%; border-collapse:collapse; font-size:0.83rem;">
            <thead>
                <tr>
                    <th style="padding:0.75rem; text-align:left;">GUÍA / TURNO</th>
                    <th style="padding:0.75rem; text-align:left;">PLACA</th>
                    <th style="padding:0.75rem; text-align:left;">CONDUCTOR</th>
                    <th style="padding:0.75rem; text-align:left;">TRANSPORTADORA</th>
                    <th style="padding:0.75rem; text-align:center;">ISLA</th>
                    <th style="padding:0.75rem; text-align:left;">PRODUCTO</th>
                    <th style="padding:0.75rem; text-align:right;">BBL CARGADO / SOLICITADO</th>
                    <th style="padding:0.75rem; text-align:center;">PRECINTO</th>
                    <th style="padding:0.75rem; text-align:center;">ESTADO</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($despachos)): ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:1.5rem; color:var(--text-muted);">
                        No hay despachos programados en este momento.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($despachos as $d): ?>
                    <tr>
                        <td style="padding:0.75rem; font-family:var(--font-mono); font-weight:700; color:#38bdf8;">
                            <?= htmlspecialchars($d['guia_remision']) ?>
                        </td>
                        <td style="padding:0.75rem; font-family:var(--font-mono); font-weight:700; color:#fff;">
                            <?= htmlspecialchars($d['placa_cisterna']) ?>
                        </td>
                        <td style="padding:0.75rem; color:#cbd5e1;">
                            <?= htmlspecialchars($d['conductor_nombre']) ?>
                        </td>
                        <td style="padding:0.75rem; color:#94a3b8;">
                            <?= htmlspecialchars($d['empresa_transportadora']) ?>
                        </td>
                        <td style="padding:0.75rem; text-align:center; font-family:var(--font-mono); color:#f59e0b; font-weight:700;">
                            <?= htmlspecialchars($d['isla']) ?>
                        </td>
                        <td style="padding:0.75rem; color:#cbd5e1;">
                            <?= htmlspecialchars($d['producto']) ?>
                        </td>
                        <td style="padding:0.75rem; text-align:right; font-family:var(--font-mono);">
                            <strong style="color:#34d399;"><?= number_format((float)$d['volumen_cargado'], 1) ?></strong> / <?= number_format((float)$d['volumen_solicitado'], 1) ?> BBL
                        </td>
                        <td style="padding:0.75rem; text-align:center; font-family:var(--font-mono); font-size:0.75rem; color:#cbd5e1;">
                            <?= htmlspecialchars($d['precinto_seguridad']) ?>
                        </td>
                        <td style="padding:0.75rem; text-align:center;">
                            <?php if ($d['estado'] === 'EN LLENADO'): ?>
                                <span class="badge-status badge-nominal">● EN LLENADO</span>
                            <?php elseif ($d['estado'] === 'AUTORIZADO'): ?>
                                <span class="badge-status badge-standby" style="color:#38bdf8; border-color:rgba(56,189,248,0.3);">● AUTORIZADO</span>
                            <?php elseif ($d['estado'] === 'FINALIZADO'): ?>
                                <span class="badge-status" style="background:rgba(52,211,153,0.1); color:#34d399; border:1px solid rgba(52,211,153,0.3);">✓ DESPACHADO</span>
                            <?php else: ?>
                                <span class="badge-status badge-standby">● <?= htmlspecialchars($d['estado']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Programar Despacho -->
<div id="modalDespacho" class="scada-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.75); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center;">
    <div class="scada-card" style="width:90%; max-width:540px; border:1px solid #38bdf8; box-shadow:0 10px 40px rgba(0,0,0,0.8); max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">
            <h3 style="font-size:1.1rem; color:#fff; font-weight:700;">➕ Programar Despacho Cisterna</h3>
            <button type="button" onclick="closeModalDespacho()" style="background:none; border:none; color:var(--text-muted); font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>

        <form id="formProgramarDespacho" onsubmit="guardarDespacho(event)">
            <div style="margin-bottom:0.85rem;">
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">PLACA DE LA CISTERNA / CABEZOTE *</label>
                <input type="text" name="placa" class="modal-input" placeholder="ej. TLF-482" required style="width:100%; box-sizing:border-box;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.85rem;">
                <div>
                    <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">CONDUCTOR *</label>
                    <input type="text" name="conductor" class="modal-input" placeholder="Nombre completo" required style="width:100%; box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">TRANSPORTADORA *</label>
                    <input type="text" name="empresa" class="modal-input" placeholder="ej. Coltanques S.A." required style="width:100%; box-sizing:border-box;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.85rem;">
                <div>
                    <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">ISLA ASIGNADA *</label>
                    <select name="isla" class="modal-input" style="width:100%; box-sizing:border-box;">
                        <option value="Isla 01">Isla 01 - Crudo Liviano Cusiana</option>
                        <option value="Isla 02">Isla 02 - Crudo Pesado Castilla</option>
                        <option value="Isla 03">Isla 03 - Nafta / Diluyente</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">VOLUMEN (BBL) *</label>
                    <input type="number" name="volumen" class="modal-input" placeholder="220" value="220" min="50" max="300" required style="width:100%; box-sizing:border-box;">
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">PRECINTO FISCAL DE SEGURIDAD</label>
                <input type="text" name="precinto" class="modal-input" placeholder="ej. SEG-99415" value="SEG-<?= rand(10000, 99999) ?>" style="width:100%; box-sizing:border-box;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn-well btn-choke" onclick="closeModalDespacho()">Cancelar</button>
                <button type="submit" class="btn-well btn-ssv-open">Autorizar & Despachar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalDespacho() {
    document.getElementById('modalDespacho').style.display = 'flex';
}
function closeModalDespacho() {
    document.getElementById('modalDespacho').style.display = 'none';
}
function exportarManifiestos() {
    alert("Exportando manifiestos fiscales de carga en formato CSV/PDF según estándar API MPMS...");
}
function guardarDespacho(e) {
    e.preventDefault();
    alert("Cisterna registrada y autorizada para acople en brazo de carga.");
    closeModalDespacho();
    location.reload();
}
</script>


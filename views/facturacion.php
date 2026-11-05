<?php
/**
 * PROYECTO MAPAF - Vista: Licenciamiento & Facturación Industrial
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO | SCADA Petrolero · SPS-01
 */

$facturacionService = new FacturacionService();
$sub = $facturacionService->getSubscriptionInfo();
$invoices = $facturacionService->getInvoicesHistory();
$paymentSuccess = false;
$receipt = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simular_pago') {
    $metodo = $_POST['metodo'] ?? 'Transferencia Bancaria ACH';
    $referencia = $_POST['referencia'] ?? ('REF-ACH-' . rand(10000000, 99999999));
    $receipt = $facturacionService->simularPago($metodo, $referencia);
    $paymentSuccess = true;
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
            <h2 class="section-title" style="margin-bottom:0;">💳 Licenciamiento & Facturación</h2>
            <span class="badge-sidebar-pill badge-pill-admin" style="font-size:0.75rem;">CONTRATO ENTERPRISE</span>
        </div>
        <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
            Suscripción de Planta SCADA Petrolero SPS-01 · Modalidad Administrador Activa ($2,450 USD/mes)
        </p>
    </div>

    <div style="display:flex; gap:0.75rem; align-items:center;">
        <button type="button" class="btn-well btn-ssv-open" onclick="openPaymentModal()">
            💳 Realizar Pago Mensual
        </button>
        <button type="button" class="btn-well btn-choke" onclick="alert('Descargando Certificado de Cumplimiento IEC 62443 y Licencia Corporativa...')">
            📜 Certificado de Licencia
        </button>
    </div>
</div>

<?php if ($paymentSuccess && $receipt): ?>
<div style="background:rgba(16, 185, 129, 0.15); border:1px solid #10b981; border-radius:8px; padding:1.25rem; margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.4rem;">
        <span style="font-size:1.3rem;">✅</span>
        <strong style="color:#34d399; font-size:1.05rem;">
            Pago Mensual Procesado con Éxito - Recibo <?= htmlspecialchars($receipt['recibo_id']) ?>
        </strong>
    </div>
    <div style="font-size:0.82rem; color:#cbd5e1; font-family:var(--font-mono);">
        Monto: $<?= number_format($receipt['monto_usd'], 2) ?> USD | Método: <?= htmlspecialchars($receipt['metodo']) ?> | Referencia: <?= htmlspecialchars($receipt['referencia']) ?>
    </div>
    <div style="font-size:0.75rem; color:#94a3b8; font-family:var(--font-mono); margin-top:0.3rem;">
        La licencia Enterprise se mantiene vigente sin interrupción para los activos de la Estación Terminal Neiva.
    </div>
</div>
<?php endif; ?>

<!-- Tarjeta de Suscripción Principal y Medidores -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.25rem; margin-bottom:1.5rem;">

    <!-- Plan Actual -->
    <div class="scada-card" style="border-top:3px solid #f59e0b; padding:1.25rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem;">
            <div>
                <span style="font-size:0.65rem; color:var(--text-muted); font-family:var(--font-mono); font-weight:700;">CONTRATO CORPORATIVO</span>
                <h3 style="font-size:1.2rem; color:#fff; font-weight:800; margin-top:0.2rem;"><?= htmlspecialchars($sub['plan_nombre']) ?></h3>
                <div style="font-size:0.8rem; color:#94a3b8; font-family:var(--font-mono);"><?= htmlspecialchars($sub['empresa']) ?> (NIT: <?= htmlspecialchars($sub['nit']) ?>)</div>
            </div>
            <span class="badge-status badge-nominal">● <?= $sub['estado'] ?></span>
        </div>

        <div style="background:#090e1d; border-radius:8px; padding:1rem; margin-bottom:1rem; border:1px solid rgba(255,255,255,0.05);">
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:0.5rem;">
                <span style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">Canon Mensual:</span>
                <span style="font-size:1.4rem; font-weight:800; color:#38bdf8; font-family:var(--font-mono);">$<?= number_format($sub['costo_mensual_usd'], 2) ?> <span style="font-size:0.75rem; color:#94a3b8;">USD/mes</span></span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.3rem;">
                <span style="color:var(--text-muted);">Período en Curso:</span>
                <span style="color:#cbd5e1; font-weight:700;"><?= $sub['periodo_vigente'] ?></span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono);">
                <span style="color:var(--text-muted);">Próximo Vencimiento:</span>
                <span style="color:#f59e0b; font-weight:700;"><?= $sub['fecha_proximo_corte'] ?></span>
            </div>
        </div>

        <div style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono);">
            SLA de Disponibilidad Garantizado: <strong style="color:#34d399;">99.9% Uptime</strong>
        </div>
    </div>

    <!-- Recursos en Uso -->
    <div class="scada-card" style="padding:1.25rem;">
        <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:1rem;">
            📊 Recursos Operativos Contratados vs En Uso
        </h3>

        <div style="margin-bottom:0.85rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.25rem;">
                <span style="color:var(--text-muted);">Pozos & Activos Conectados:</span>
                <span style="color:#38bdf8; font-weight:700;">6 / 10 Pozos (60%)</span>
            </div>
            <div style="background:#0f172a; border-radius:6px; height:8px; overflow:hidden;">
                <div style="width:60%; height:100%; background:#38bdf8;"></div>
            </div>
        </div>

        <div style="margin-bottom:0.85rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.25rem;">
                <span style="color:var(--text-muted);">Islas de Despacho Terrestre:</span>
                <span style="color:#34d399; font-weight:700;">3 / 3 Habilitadas (100%)</span>
            </div>
            <div style="background:#0f172a; border-radius:6px; height:8px; overflow:hidden;">
                <div style="width:100%; height:100%; background:#34d399;"></div>
            </div>
        </div>

        <div style="margin-bottom:0.85rem;">
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.25rem;">
                <span style="color:var(--text-muted);">Frecuencia de Polling RTU:</span>
                <span style="color:#f59e0b; font-weight:700;">1.0 Segundo (Tiempo Real)</span>
            </div>
            <div style="background:#0f172a; border-radius:6px; height:8px; overflow:hidden;">
                <div style="width:100%; height:100%; background:#f59e0b;"></div>
            </div>
        </div>

        <div>
            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-family:var(--font-mono); margin-bottom:0.25rem;">
                <span style="color:var(--text-muted);">Almacenamiento Historian Redundante:</span>
                <span style="color:#a855f7; font-weight:700;">1 Año Completo</span>
            </div>
            <div style="background:#0f172a; border-radius:6px; height:8px; overflow:hidden;">
                <div style="width:100%; height:100%; background:#a855f7;"></div>
            </div>
        </div>
    </div>

</div>

<!-- Tabla de Historial de Facturación -->
<div class="scada-card" style="padding:1.25rem;">
    <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.85rem;">
        📑 Historial de Facturas y Comprobantes Fiscales
    </h3>

    <div style="overflow-x:auto;">
        <table class="telemetry-table" style="width:100%; border-collapse:collapse; font-size:0.83rem;">
            <thead>
                <tr>
                    <th style="padding:0.75rem; text-align:left;">N° FACTURA</th>
                    <th style="padding:0.75rem; text-align:left;">PERÍODO</th>
                    <th style="padding:0.75rem; text-align:left;">FECHA DE PAGO</th>
                    <th style="padding:0.75rem; text-align:right;">MONTO USD</th>
                    <th style="padding:0.75rem; text-align:left;">MÉTODO DE PAGO</th>
                    <th style="padding:0.75rem; text-align:center;">REFERENCIA</th>
                    <th style="padding:0.75rem; text-align:center;">ESTADO</th>
                    <th style="padding:0.75rem; text-align:center;">RECIBO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td style="padding:0.75rem; font-family:var(--font-mono); font-weight:700; color:#38bdf8;">
                        <?= htmlspecialchars($inv['id']) ?>
                    </td>
                    <td style="padding:0.75rem; color:#f8fafc;">
                        <?= htmlspecialchars($inv['periodo']) ?>
                    </td>
                    <td style="padding:0.75rem; font-family:var(--font-mono); color:#cbd5e1;">
                        <?= htmlspecialchars($inv['fecha_pago']) ?>
                    </td>
                    <td style="padding:0.75rem; text-align:right; font-family:var(--font-mono); font-weight:700; color:#34d399;">
                        $<?= number_format($inv['monto_usd'], 2) ?>
                    </td>
                    <td style="padding:0.75rem; color:#94a3b8;">
                        <?= htmlspecialchars($inv['metodo']) ?>
                    </td>
                    <td style="padding:0.75rem; text-align:center; font-family:var(--font-mono); font-size:0.75rem; color:#cbd5e1;">
                        <?= htmlspecialchars($inv['referencia']) ?>
                    </td>
                    <td style="padding:0.75rem; text-align:center;">
                        <span class="badge-status" style="background:rgba(52,211,153,0.1); color:#34d399; border:1px solid rgba(52,211,153,0.3);">
                            ✓ <?= htmlspecialchars($inv['estado']) ?>
                        </span>
                    </td>
                    <td style="padding:0.75rem; text-align:center;">
                        <button type="button" class="btn-well btn-choke" style="padding:0.25rem 0.5rem; font-size:0.72rem;" onclick="alert('Descargando comprobante fiscal <?= $inv['id'] ?> (PDF)...')">
                            PDF
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Simular Pago -->
<div id="modalPago" class="scada-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.75); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center;">
    <div class="scada-card" style="width:90%; max-width:480px; border:1px solid #38bdf8; box-shadow:0 10px 40px rgba(0,0,0,0.8);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">
            <h3 style="font-size:1.1rem; color:#fff; font-weight:700;">💳 Pasarela de Pago Mensual SCADA</h3>
            <button type="button" onclick="closePaymentModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" action="index.php?view=facturacion">
            <input type="hidden" name="action" value="simular_pago">

            <div style="background:#090e1d; border-radius:8px; padding:0.85rem; margin-bottom:1rem; border:1px solid rgba(255,255,255,0.05);">
                <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">MONTO A FACTURAR:</div>
                <div style="font-size:1.5rem; font-weight:800; color:#38bdf8; font-family:var(--font-mono);">$2,450.00 USD</div>
                <div style="font-size:0.75rem; color:#94a3b8; font-family:var(--font-mono);">Período: Noviembre 2026 · SPS-01</div>
            </div>

            <div style="margin-bottom:0.85rem;">
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">MÉTODO DE PAGO:</label>
                <select name="metodo" class="modal-input" style="width:100%; box-sizing:border-box;">
                    <option value="Transferencia Bancaria ACH / Bancolombia">Transferencia Bancaria ACH / Bancolombia</option>
                    <option value="Orden de Compra Corporativa (PO-9912)">Orden de Compra Corporativa (PO-9912)</option>
                    <option value="Tarjeta de Crédito Corporativa Visa">Tarjeta de Crédito Corporativa Visa</option>
                    <option value="Carta de Crédito Standby">Carta de Crédito Standby</option>
                </select>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">REFERENCIA DE TRANSACCIÓN O APROBACIÓN:</label>
                <input type="text" name="referencia" class="modal-input" value="REF-ACH-<?= rand(10000000, 99999999) ?>" required style="width:100%; box-sizing:border-box;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn-well btn-choke" onclick="closePaymentModal()">Cancelar</button>
                <button type="submit" class="btn-well btn-ssv-open">Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal() {
    document.getElementById('modalPago').style.display = 'flex';
}
function closePaymentModal() {
    document.getElementById('modalPago').style.display = 'none';
}
</script>


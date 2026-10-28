<?php
/**
 * PROYECTO MAPAF - Vista: Bitácora Forense de Auditoría (21 CFR Part 11)
 * SENA ADSO - Ficha 3413988
 */
$logs = [];
try {
    $auditRepo = new AuditRepository();
    $logs = $auditRepo->getRecent(100);
} catch (Throwable $e) {
    // Degrada graciosamente
}
?>

<div style="margin-bottom:1.5rem;">
    <h2 class="section-title">📜 Bitácora de Auditoría Forense Inmutable</h2>
    <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
        Cumplimiento normativo 21 CFR Part 11 · Registro estricto de cada consigna de choke, acuse y maniobra SIS
    </p>
</div>

<div class="scada-table-container">
    <table class="scada-table">
        <thead>
            <tr>
                <th>ID Registro</th>
                <th>Hora Evento</th>
                <th>Operador / Empleado</th>
                <th>Rol Asignado</th>
                <th>Categoría</th>
                <th>Acción Operativa</th>
                <th>Estado</th>
                <th>Detalles Técnicos</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
            <tr>
                <td colspan="8" style="text-align:center; padding:2rem; color:var(--text-muted);">
                    No hay registros de auditoría almacenados.
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($logs as $l): 
                    $badgeState = 'badge-nominal';
                    if ($l['estado'] === 'RECHAZADO') $badgeState = 'badge-critical';
                    elseif ($l['estado'] === 'ADVERTENCIA') $badgeState = 'badge-warning';
                ?>
                <tr>
                    <td style="font-family:var(--font-mono); color:var(--text-muted);"><?= htmlspecialchars($l['id']) ?></td>
                    <td style="font-family:var(--font-mono);"><?= htmlspecialchars($l['timestamp_log']) ?></td>
                    <td>
                        <strong style="color:#fff;"><?= htmlspecialchars($l['nombre_usuario']) ?></strong>
                        <div style="font-size:0.7rem; color:var(--text-muted); font-family:var(--font-mono);"><?= htmlspecialchars($l['codigo_usuario']) ?></div>
                    </td>
                    <td><span style="color:#a855f7; font-size:0.75rem;"><?= htmlspecialchars($l['nombre_rol']) ?></span></td>
                    <td><span style="font-size:0.75rem; font-weight:600; color:#38bdf8;"><?= htmlspecialchars($l['categoria']) ?></span></td>
                    <td><strong style="color:#fff;"><?= htmlspecialchars($l['accion']) ?></strong></td>
                    <td>
                        <span class="badge-status <?= $badgeState ?>">
                            <?= htmlspecialchars($l['estado']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.78rem; color:#94a3b8; max-width:320px; line-height:1.4;">
                        <?= htmlspecialchars($l['detalles']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

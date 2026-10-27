<?php
/**
 * PROYECTO MAPAF - Vista: Consola de Alarmas Industriales (ANSI/ISA-18.2)
 * SENA ADSO - Ficha 3413988
 */
$alarms = [];
$counts = ['critical' => 0, 'warning' => 0, 'advisory' => 0, 'total' => 0];
$pendingOnly = isset($_GET['filter']) && $_GET['filter'] === 'pending';

try {
    $alarmaModel = new AlarmaModel();
    $alarms = $alarmaModel->getAll($pendingOnly);
    $counts = $alarmaModel->getPendingCounts();
} catch (Throwable $e) {
    // Degrada graciosamente
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 class="section-title" style="margin-bottom:0.25rem;">🔔 Consola de Gestión de Alarmas (ANSI/ISA-18.2)</h2>
        <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
            Trazabilidad obligatoria de acuse · Mitigación de Antipatrón Blind Alarm ACK
        </p>
    </div>

    <div style="display:flex; gap:0.5rem;">
        <a href="index.php?view=alarms" class="btn-well <?= !$pendingOnly ? 'btn-ssv-open' : '' ?>" style="text-decoration:none; padding:0.5rem 1rem;">
            Todas (<?= count($alarmaModel->getAll(false)) ?>)
        </a>
        <a href="index.php?view=alarms&filter=pending" class="btn-well <?= $pendingOnly ? 'btn-ssv-open' : '' ?>" style="text-decoration:none; padding:0.5rem 1rem;">
            Pendientes (<?= $counts['total'] ?>)
        </a>
    </div>
</div>

<div class="scada-table-container">
    <table class="scada-table">
        <thead>
            <tr>
                <th>Hora Evento</th>
                <th>Tag Instrumento</th>
                <th>Pozo / Área</th>
                <th>Subsistema</th>
                <th>Descripción de Condición</th>
                <th>Valor / Umbral</th>
                <th>Severidad</th>
                <th>Estado Acuse</th>
                <th>Operador Responsable</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alarms)): ?>
            <tr>
                <td colspan="10" style="text-align:center; padding:2rem; color:var(--primary);">
                    ✓ No hay alarmas bajo el filtro seleccionado.
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($alarms as $a): ?>
                <tr>
                    <td style="font-family:var(--font-mono);"><?= htmlspecialchars($a['timestamp_evento']) ?></td>
                    <td><strong style="color:#fff; font-family:var(--font-mono);"><?= htmlspecialchars($a['tag']) ?></strong></td>
                    <td><?= htmlspecialchars($a['nombre_pozo'] ?? 'GENERAL') ?></td>
                    <td><?= htmlspecialchars($a['subsistema']) ?></td>
                    <td><?= htmlspecialchars($a['descripcion']) ?></td>
                    <td style="font-family:var(--font-mono);">
                        <span style="color:#fbbf24; font-weight:bold;"><?= htmlspecialchars($a['valor']) ?></span>
                        <div style="font-size:0.7rem; color:var(--text-muted);"><?= htmlspecialchars($a['umbral']) ?></div>
                    </td>
                    <td>
                        <span class="badge-status badge-<?= $a['severidad'] ?>">
                            <?= strtoupper($a['severidad']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($a['reconocida'] == 1): ?>
                            <span class="badge-status badge-nominal">✓ RECONOCIDA</span>
                            <div style="font-size:0.7rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:0.2rem;">
                                <?= htmlspecialchars($a['ack_timestamp'] ?? '') ?>
                            </div>
                        <?php else: ?>
                            <span class="badge-status badge-critical">● PENDIENTE</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($a['reconocida'] == 1): ?>
                            <span style="color:#fff; font-weight:500;"><?= htmlspecialchars($a['reconocida_por']) ?></span>
                            <?php if (!empty($a['causa_disparo'])): ?>
                                <div style="font-size:0.7rem; color:var(--text-muted); max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($a['causa_disparo']) ?>">
                                    <?= htmlspecialchars($a['causa_disparo']) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:var(--text-dim);">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($a['reconocida'] == 0): ?>
                        <button type="button" class="btn-confirm" style="padding:0.4rem 0.8rem; font-size:0.75rem;"
                                onclick="SCADA.openAckModal('<?= $a['id'] ?>', '<?= htmlspecialchars($a['tag']) ?>')">
                            Acusar Alarma
                        </button>
                        <?php else: ?>
                        <span style="color:var(--primary); font-size:0.75rem; font-weight:bold;">Conforme ISA</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

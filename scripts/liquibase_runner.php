<?php
/**
 * PROYECTO MAPAF - CLI Runner de Migraciones Liquibase
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Programa: Análisis y Desarrollo de Software (ADSO) | Ficha: 3413988-ADSO
 * 
 * Uso:
 *   php scripts/liquibase_runner.php update
 *   php scripts/liquibase_runner.php status
 *   php scripts/liquibase_runner.php rollback [count]
 *   php scripts/liquibase_runner.php reset
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/LiquibaseService.php';

$action = $argv[1] ?? 'status';
$param = $argv[2] ?? 1;

echo "\n======================================================================\n";
echo "       PROYECTO MAPAF - MOTOR DE MIGRACIONES LIQUIBASE 4.27\n";
echo "     SENA ADSO · Ficha 3413988 | SCADA Upstream Oil & Gas\n";
echo "======================================================================\n\n";

try {
    $liquibase = new LiquibaseService();

    switch (strtolower($action)) {
        case 'update':
            echo "[*] Ejecutando migraciones pendientes (liquibase update)...\n";
            $res = $liquibase->update();
            echo "[+] ÉXITO: " . $res['message'] . "\n";
            echo "    - Deployment ID:       " . $res['deploymentId'] . "\n";
            echo "    - ChangeSets Aplicados: " . $res['appliedCount'] . " de " . $res['totalChangesets'] . "\n\n";
            if (!empty($res['appliedChanges'])) {
                foreach ($res['appliedChanges'] as $ac) {
                    printf("    • [%02d] %-36s (%s)\n", $ac['order'], $ac['id'], $ac['author']);
                }
            } else {
                echo "    ✓ El esquema ya se encontraba 100% al día.\n";
            }
            break;

        case 'status':
            echo "[*] Consultando estado de ChangeSets (liquibase status)...\n\n";
            $status = $liquibase->getStatus();
            echo "  Base de Datos:     " . $status['database'] . "\n";
            echo "  Changelog Master:  " . $status['changelogFile'] . "\n";
            echo "  Versión Liquibase: " . $status['liquibaseVersion'] . "\n";
            echo "  Estado Bloqueo:    " . ($status['locked'] ? "BLOQUEADO por " . $status['lockedBy'] : "LIBRE") . "\n";
            echo "  Total ChangeSets:  " . $status['totalChangesets'] . "\n";
            echo "  Aplicados:         " . $status['appliedCount'] . "\n";
            echo "  Pendientes:        " . $status['pendingCount'] . "\n";
            echo "----------------------------------------------------------------------\n";
            printf("  %-3s | %-34s | %-10s | %-11s | %s\n", "N°", "ID ChangeSet", "Autor", "Estado", "Fecha Ejecución");
            echo "----------------------------------------------------------------------\n";
            $idx = 1;
            foreach ($status['changesets'] as $cs) {
                $stColor = ($cs['status'] === 'APLICADO') ? "APLICADO" : "PENDIENTE";
                printf("  %02d  | %-34s | %-10s | %-11s | %s\n",
                    $idx++,
                    $cs['id'],
                    $cs['author'],
                    $stColor,
                    $cs['dateExecuted'] ?: '---'
                );
            }
            echo "----------------------------------------------------------------------\n";
            break;

        case 'rollback':
            $count = max(1, (int)$param);
            echo "[*] Revertiendo los últimos {$count} ChangeSets (liquibase rollback)...\n";
            $res = $liquibase->rollback($count);
            echo "[+] ÉXITO: " . $res['message'] . "\n";
            echo "    - Revertidos: " . implode(', ', $res['rolledBackIds']) . "\n";
            break;

        case 'reset':
            echo "[!] Reinicializando y montando base de datos limpia desde Liquibase...\n";
            $res = $liquibase->resetAndMount();
            echo "[+] ÉXITO: Base de datos montada completamente con Liquibase.\n";
            echo "    - ChangeSets Aplicados: " . $res['appliedCount'] . " de " . $res['totalChangesets'] . "\n";
            break;

        default:
            echo "[ERROR] Comando no reconocido: {$action}\n";
            echo "Uso: php scripts/liquibase_runner.php [update|status|rollback|reset]\n";
            exit(1);
    }

    echo "\n======================================================================\n\n";

} catch (Throwable $e) {
    echo "\n[ERROR CRÍTICO LIQUIBASE] " . $e->getMessage() . "\n\n";
    exit(1);
}


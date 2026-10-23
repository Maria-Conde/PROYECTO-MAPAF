<?php
/**
 * PROYECTO MAPAF - Modelo de Dominio: Parada de Emergencia ESD-1 (IEC 61511 / ISA-84)
 * SENA ADSO - Ficha 3413988
 * 
 * Gestiona el aislamiento total de la estación (cierre de SSVs y chokes a 0%)
 * y el rearme seguro bajo protocolo de confirmación.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/AuditoriaModel.php';

class EsdModel {
    private ?PDO $db;
    private AuditoriaModel $auditoria;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
        $this->auditoria = new AuditoriaModel($this->db);
    }

    /**
     * Comprueba si la Parada de Emergencia ESD-1 está activa
     */
    public function isTriggered(): bool {
        if (!$this->db) {
            return false;
        }
        $stmt = $this->db->prepare("SELECT `valor` FROM `configuracion_sistema` WHERE `clave` = 'esd_triggered' LIMIT 1");
        $stmt->execute();
        $val = $stmt->fetchColumn();
        return ($val === '1' || $val === 1);
    }

    /**
     * Obtiene el estado general del sistema y parada de emergencia
     */
    public function getSystemState(): array {
        if (!$this->db) {
            return [
                'esd_triggered' => false,
                'esd_trigger_time' => '',
                'flow_state' => 'unknown',
                'plant_name' => APP_NAME
            ];
        }

        $stmt = $this->db->query("SELECT `clave`, `valor` FROM `configuracion_sistema`");
        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row['clave']] = $row['valor'];
        }

        return [
            'esd_triggered'    => (($config['esd_triggered'] ?? '0') === '1'),
            'esd_trigger_time' => $config['esd_trigger_time'] ?? '',
            'flow_state'       => $config['flow_state'] ?? 'nominal',
            'plant_name'       => $config['plant_name'] ?? APP_NAME,
            'production_rate'  => (float)($config['production_rate'] ?? 0)
        ];
    }

    /**
     * Dispara la Parada de Emergencia ESD-1: Transacción atómica
     */
    public function triggerEsd(string $operatorCode, string $operatorName, string $reason): bool {
        if (!$this->db) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $timeStr = date('H:i:s');

        try {
            $this->db->beginTransaction();

            // 1. Activar bandera de ESD-1 en configuracion_sistema
            $stmt1 = $this->db->prepare("
                UPDATE `configuracion_sistema` 
                SET `valor` = '1' 
                WHERE `clave` = 'esd_triggered'
            ");
            $stmt1->execute();

            $stmt2 = $this->db->prepare("
                UPDATE `configuracion_sistema` 
                SET `valor` = :time 
                WHERE `clave` = 'esd_trigger_time'
            ");
            $stmt2->execute([':time' => $timeStr]);

            $stmtFlow = $this->db->prepare("
                UPDATE `configuracion_sistema` 
                SET `valor` = 'tripped' 
                WHERE `clave` = 'flow_state'
            ");
            $stmtFlow->execute();

            // 2. Aislar pozos: Cerrar válvulas SSV, choke a 0% y conmutar a estado DISPARADO
            $stmtPozos = $this->db->prepare("
                UPDATE `pozos` 
                SET `valvula_ssv_abierta` = 0,
                    `apertura_choke` = 0.00,
                    `objetivo_choke` = 0.00,
                    `estado` = 'DISPARADO'
            ");
            $stmtPozos->execute();

            // 3. Registrar en bitácora de auditoría inmutable
            $this->auditoria->log(
                $operatorCode,
                $operatorName,
                'Operador / SIS',
                'DISPARO DE EMERGENCIA ESD-1',
                'SEGURIDAD',
                'ADVERTENCIA',
                "Parada de emergencia ejecutada. Razón: '{$reason}'. Cabezales aislados bajo norma IEC 61511."
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Rearma de forma segura el sistema ESD-1 tras inspección
     */
    public function resetEsd(string $operatorCode, string $operatorName): bool {
        if (!$this->db) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmt1 = $this->db->prepare("UPDATE `configuracion_sistema` SET `valor` = '0' WHERE `clave` = 'esd_triggered'");
            $stmt1->execute();

            $stmt2 = $this->db->prepare("UPDATE `configuracion_sistema` SET `valor` = '' WHERE `clave` = 'esd_trigger_time'");
            $stmt2->execute();

            $stmtFlow = $this->db->prepare("UPDATE `configuracion_sistema` SET `valor` = 'nominal' WHERE `clave` = 'flow_state'");
            $stmtFlow->execute();

            // Rearmar pozos a EN LÍNEA con choke al 15% seguro
            $stmtPozos = $this->db->prepare("
                UPDATE `pozos` 
                SET `valvula_ssv_abierta` = 1,
                    `apertura_choke` = 15.00,
                    `objetivo_choke` = 15.00,
                    `estado` = 'EN LÍNEA'
                WHERE `estado` = 'DISPARADO'
            ");
            $stmtPozos->execute();

            $this->auditoria->log(
                $operatorCode,
                $operatorName,
                'Supervisor / Ingeniero',
                'REARME DE SEGURIDAD ESD-1',
                'SEGURIDAD',
                'EXITOSO',
                "Rearme completado con éxito. Estado de enclavamiento liberado y consignas en nivel seguro (15%)."
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}

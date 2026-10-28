<?php
/**
 * PROYECTO MAPAF - Patrón de Diseño REPOSITORY: EsdRepository
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona el estado de parada de emergencia SIS en `configuracion_sistema`.
 */

require_once __DIR__ . '/../config/database.php';

class EsdRepository {
    private ?PDO $db;
    private static array $fallbackState = [
        'esd_triggered'    => false,
        'esd_trigger_time' => '',
        'flow_state'       => 'nominal',
        'plant_name'       => APP_NAME,
        'production_rate'  => 10460.0
    ];

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    public function isTriggered(): bool {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT `valor` FROM `configuracion_sistema` WHERE `clave` = 'esd_triggered' LIMIT 1");
                $stmt->execute();
                $val = $stmt->fetchColumn();
                return ($val === '1' || $val === 1);
            } catch (Throwable $e) {
                error_log('[EsdRepository] Error isTriggered: ' . $e->getMessage());
            }
        }
        return self::$fallbackState['esd_triggered'];
    }

    public function getSystemState(): array {
        if ($this->db) {
            try {
                $stmt = $this->db->query("SELECT `clave`, `valor` FROM `configuracion_sistema`");
                $config = [];
                while ($row = $stmt->fetch()) {
                    $config[$row['clave']] = $row['valor'];
                }
                if (!empty($config)) {
                    return [
                        'esd_triggered'    => (($config['esd_triggered'] ?? '0') === '1'),
                        'esd_trigger_time' => $config['esd_trigger_time'] ?? '',
                        'flow_state'       => $config['flow_state'] ?? 'nominal',
                        'plant_name'       => $config['plant_name'] ?? APP_NAME,
                        'production_rate'  => (float)($config['production_rate'] ?? 0)
                    ];
                }
            } catch (Throwable $e) {
                error_log('[EsdRepository] Error getSystemState: ' . $e->getMessage());
            }
        }
        return self::$fallbackState;
    }

    public function setTriggered(string $timeStr): bool {
        self::$fallbackState['esd_triggered'] = true;
        self::$fallbackState['esd_trigger_time'] = $timeStr;
        self::$fallbackState['flow_state'] = 'tripped';

        if ($this->db) {
            try {
                $this->db->exec("UPDATE `configuracion_sistema` SET `valor` = '1' WHERE `clave` = 'esd_triggered'");
                $stmt = $this->db->prepare("UPDATE `configuracion_sistema` SET `valor` = :time WHERE `clave` = 'esd_trigger_time'");
                $stmt->execute([':time' => $timeStr]);
                $this->db->exec("UPDATE `configuracion_sistema` SET `valor` = 'tripped' WHERE `clave` = 'flow_state'");
                return true;
            } catch (Throwable $e) {
                error_log('[EsdRepository] Error setTriggered: ' . $e->getMessage());
            }
        }
        return true;
    }

    public function setReset(): bool {
        self::$fallbackState['esd_triggered'] = false;
        self::$fallbackState['esd_trigger_time'] = '';
        self::$fallbackState['flow_state'] = 'nominal';

        if ($this->db) {
            try {
                $this->db->exec("UPDATE `configuracion_sistema` SET `valor` = '0' WHERE `clave` = 'esd_triggered'");
                $this->db->exec("UPDATE `configuracion_sistema` SET `valor` = '' WHERE `clave` = 'esd_trigger_time'");
                $this->db->exec("UPDATE `configuracion_sistema` SET `valor` = 'nominal' WHERE `clave` = 'flow_state'");
                return true;
            } catch (Throwable $e) {
                error_log('[EsdRepository] Error setReset: ' . $e->getMessage());
            }
        }
        return true;
    }
}

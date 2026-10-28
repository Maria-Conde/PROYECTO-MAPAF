<?php
/**
 * PROYECTO MAPAF - Patrón de Diseño REPOSITORY: AlarmRepository
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona el ciclo de vida de alarmas industriales (ANSI/ISA-18.2).
 */

require_once __DIR__ . '/RepositoryInterface.php';
require_once __DIR__ . '/../config/database.php';

class AlarmRepository implements RepositoryInterface {
    private ?PDO $db;
    private static array $fallbackAlarms = [
        'alm-01' => [
            'id' => 'alm-01', 'timestamp_evento' => '14:28:12', 'tag' => 'PAL-101A', 'pozo_id' => 'well-01',
            'nombre_pozo' => 'POZO HUILA-NORTE-01', 'subsistema' => 'PAD 04-A',
            'descripcion' => 'Presión de Tubing por debajo del límite operacional (LOW)',
            'valor' => '1845.5 PSI', 'umbral' => '< 1900.0 PSI',
            'variable' => 'presion_tubing', 'tipo_limite' => 'LOW', 'valor_limite' => 1900.00,
            'valor_actual' => 1845.50, 'severidad' => 'warning', 'estado' => 'ACTIVA',
            'reconocida' => 0, 'reconocida_por' => null, 'ack_timestamp' => null,
            'causa_disparo' => null, 'fecha_registro' => '2026-10-27 10:14:22'
        ],
        'alm-02' => [
            'id' => 'alm-02', 'timestamp_evento' => '13:52:04', 'tag' => 'TAH-301C', 'pozo_id' => 'well-03',
            'nombre_pozo' => 'POZO DINA-CRETÁCEO-09', 'subsistema' => 'MANIFOLD TRONCAL',
            'descripcion' => 'Temperatura de fondo y línea en nivel crítico alto (HIGH)',
            'valor' => '84.0 °C', 'umbral' => '> 80.0 °C',
            'variable' => 'temperatura', 'tipo_limite' => 'HIGH', 'valor_limite' => 80.00,
            'valor_actual' => 84.00, 'severidad' => 'critical', 'estado' => 'ACTIVA',
            'reconocida' => 0, 'reconocida_por' => null, 'ack_timestamp' => null,
            'causa_disparo' => null, 'fecha_registro' => '2026-10-27 10:28:05'
        ],
        'alm-03' => [
            'id' => 'alm-03', 'timestamp_evento' => '10:45:10', 'tag' => 'PAH-102B', 'pozo_id' => 'well-02',
            'nombre_pozo' => 'POZO HUILA-SUR-04', 'subsistema' => 'PAD 04-B',
            'descripcion' => 'Acumulación de gas en espacio anular casing monitoreada',
            'valor' => '380.0 PSI', 'umbral' => '> 350.0 PSI',
            'variable' => 'presion_casing', 'tipo_limite' => 'HIGH', 'valor_limite' => 350.00,
            'valor_actual' => 380.00, 'severidad' => 'advisory', 'estado' => 'ACTIVA',
            'reconocida' => 1, 'reconocida_por' => 'Hernann Thomas Manchola', 'ack_timestamp' => '10:45:10',
            'causa_disparo' => 'Acumulación de gas en anular monitoreada.', 'fecha_registro' => '2026-10-27 09:55:12'
        ]
    ];

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    public function getAll(bool $unacknowledgedOnly = false): array {
        if ($this->db) {
            try {
                $sql = "SELECT a.*, p.nombre as nombre_pozo 
                        FROM `alarmas` a
                        LEFT JOIN `pozos` p ON a.pozo_id = p.id ";
                if ($unacknowledgedOnly) {
                    $sql .= "WHERE a.`reconocida` = 0 ";
                }
                $sql .= "ORDER BY (CASE a.severidad WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 WHEN 'advisory' THEN 3 ELSE 4 END) ASC, a.fecha_registro DESC";

                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (Throwable $e) {
                error_log('[AlarmRepository] Error consultando alarmas en BD: ' . $e->getMessage());
            }
        }

        $res = array_values(self::$fallbackAlarms);
        if ($unacknowledgedOnly) {
            $res = array_filter($res, fn($a) => $a['reconocida'] === 0);
        }
        return array_values($res);
    }

    public function getById(string $id): ?array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM `alarmas` WHERE `id` = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                if ($row) return $row;
            } catch (Throwable $e) {
                error_log('[AlarmRepository] Error getById: ' . $e->getMessage());
            }
        }
        return self::$fallbackAlarms[$id] ?? null;
    }

    public function acknowledge(string $id, string $operatorName, string $reason): array {
        $now = date('H:i:s');
        if (isset(self::$fallbackAlarms[$id])) {
            self::$fallbackAlarms[$id]['reconocida'] = 1;
            self::$fallbackAlarms[$id]['reconocida_por'] = $operatorName;
            self::$fallbackAlarms[$id]['ack_timestamp'] = $now;
            self::$fallbackAlarms[$id]['causa_disparo'] = $reason;
        }

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE `alarmas`
                    SET `reconocida` = 1,
                        `reconocida_por` = :operator,
                        `ack_timestamp` = :ack_time,
                        `causa_disparo` = :reason
                    WHERE `id` = :id
                ");
                $executed = $stmt->execute([
                    ':operator' => $operatorName,
                    ':ack_time' => $now,
                    ':reason'   => $reason,
                    ':id'       => $id
                ]);
                return [
                    'success'       => $executed,
                    'ack_timestamp' => $now,
                    'operator'      => $operatorName
                ];
            } catch (Throwable $e) {
                error_log('[AlarmRepository] Error actualizando acuse en BD: ' . $e->getMessage());
            }
        }

        return [
            'success'       => isset(self::$fallbackAlarms[$id]),
            'ack_timestamp' => $now,
            'operator'      => $operatorName
        ];
    }

    public function getPendingCounts(): array {
        if ($this->db) {
            try {
                $stmt = $this->db->query("
                    SELECT 
                        SUM(CASE WHEN `severidad` = 'critical' AND `reconocida` = 0 THEN 1 ELSE 0 END) as critical_count,
                        SUM(CASE WHEN `severidad` = 'warning' AND `reconocida` = 0 THEN 1 ELSE 0 END) as warning_count,
                        SUM(CASE WHEN `severidad` = 'advisory' AND `reconocida` = 0 THEN 1 ELSE 0 END) as advisory_count,
                        SUM(CASE WHEN `reconocida` = 0 THEN 1 ELSE 0 END) as total_pending
                    FROM `alarmas`
                ");
                $data = $stmt->fetch();
                if ($data && isset($data['total_pending'])) {
                    return [
                        'critical' => (int)($data['critical_count'] ?? 0),
                        'warning'  => (int)($data['warning_count'] ?? 0),
                        'advisory' => (int)($data['advisory_count'] ?? 0),
                        'total'    => (int)($data['total_pending'] ?? 0)
                    ];
                }
            } catch (Throwable $e) {
                error_log('[AlarmRepository] Error calculando conteos: ' . $e->getMessage());
            }
        }

        $crit = 0; $warn = 0; $adv = 0;
        foreach (self::$fallbackAlarms as $a) {
            if ($a['reconocida'] === 0) {
                if ($a['severidad'] === 'critical') $crit++;
                elseif ($a['severidad'] === 'warning') $warn++;
                elseif ($a['severidad'] === 'advisory') $adv++;
            }
        }
        return [
            'critical' => $crit,
            'warning'  => $warn,
            'advisory' => $adv,
            'total'    => $crit + $warn + $adv
        ];
    }
}

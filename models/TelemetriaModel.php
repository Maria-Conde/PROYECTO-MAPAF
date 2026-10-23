<?php
/**
 * PROYECTO MAPAF - Modelo de Dominio: Telemetría de Sensores Físicos IIoT
 * SENA ADSO - Ficha 3413988
 * 
 * Gestiona lecturas de sensores de campo (Presión, Temperatura, Caudal, VFD, GOR).
 */

require_once __DIR__ . '/../config/database.php';

class TelemetriaModel {
    private ?PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    /**
     * Obtiene la lista completa de métricas de telemetría
     */
    public function getAll(): array {
        if (!$this->db) {
            return [];
        }
        $stmt = $this->db->prepare("SELECT * FROM `telemetria_metricas` ORDER BY `id` ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            if (isset($r['historial']) && is_string($r['historial'])) {
                $r['historial'] = json_decode($r['historial'], true) ?: [];
            }
        }
        return $rows;
    }

    /**
     * Obtiene una métrica por su tag industrial
     */
    public function getByTag(string $tag): ?array {
        if (!$this->db) {
            return null;
        }
        $stmt = $this->db->prepare("SELECT * FROM `telemetria_metricas` WHERE `tag` = :tag LIMIT 1");
        $stmt->execute([':tag' => $tag]);
        $row = $stmt->fetch();
        if ($row && isset($row['historial']) && is_string($row['historial'])) {
            $row['historial'] = json_decode($row['historial'], true) ?: [];
        }
        return $row ?: null;
    }

    /**
     * Actualiza el valor leído de un sensor y recalcula severidad
     */
    public function updateMetric(string $id, float $value, string $severity = 'nominal', string $trend = 'stable'): bool {
        if (!$this->db) {
            return false;
        }
        $stmt = $this->db->prepare("
            UPDATE `telemetria_metricas` 
            SET `valor` = :val, `severidad` = :sev, `tendencia` = :trend 
            WHERE `id` = :id
        ");
        return $stmt->execute([
            ':val'   => $value,
            ':sev'   => $severity,
            ':trend' => $trend,
            ':id'    => $id
        ]);
    }

    /**
     * Devuelve los KPIs principales consolidados de la planta
     */
    public function getPlantKpis(): array {
        if (!$this->db) {
            return [
                'total_production' => 0.0,
                'avg_tubing_pressure' => 0.0,
                'avg_temperature' => 0.0,
                'active_wells_count' => 0,
                'total_wells_count' => 0
            ];
        }

        $wellStmt = $this->db->query("
            SELECT 
                COUNT(*) as total_wells,
                SUM(CASE WHEN `estado` = 'EN LÍNEA' THEN 1 ELSE 0 END) as active_wells,
                COALESCE(SUM(`caudal_produccion`), 0) as total_flow,
                COALESCE(AVG(`presion_tubing`), 0) as avg_presion,
                COALESCE(AVG(`temperatura`), 0) as avg_temp
            FROM `pozos`
        ");
        $data = $wellStmt->fetch();

        return [
            'total_production'    => (float)($data['total_flow'] ?? 0),
            'avg_tubing_pressure' => round((float)($data['avg_presion'] ?? 0), 1),
            'avg_temperature'     => round((float)($data['avg_temp'] ?? 0), 1),
            'active_wells_count'  => (int)($data['active_wells'] ?? 0),
            'total_wells_count'   => (int)($data['total_wells'] ?? 0)
        ];
    }
}

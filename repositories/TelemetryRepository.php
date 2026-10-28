<?php
/**
 * PROYECTO MAPAF - Patrón de Diseño REPOSITORY: TelemetryRepository
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona mediciones de transmisores IIoT y consolidación de KPIs de planta.
 */

require_once __DIR__ . '/RepositoryInterface.php';
require_once __DIR__ . '/../config/database.php';

class TelemetryRepository implements RepositoryInterface {
    private ?PDO $db;
    private static array $fallbackMetrics = [
        'pt-101' => [
            'id' => 'pt-101', 'tag' => 'WHTP-101A', 'pozo_id' => 'well-a12', 'tipo_sensor' => 'TRANSMISOR_PRESION',
            'etiqueta' => 'PRESIÓN DE CABEZAL (TUBING)', 'descripcion' => 'Presión de Cabezal Tubing Pozo 01',
            'unidad' => 'PSI', 'valor' => 1842.50, 'valor_objetivo' => 1800.00, 'trip_bajo' => 850.00,
            'alarma_alta' => 2100.00, 'trip_alto' => 2350.00, 'limite_min' => 850.00, 'limite_max' => 2350.00,
            'severidad' => 'nominal', 'tendencia' => 'stable',
            'historial' => [1810, 1822, 1835, 1830, 1845, 1840, 1838, 1842.5]
        ],
        'pt-102' => [
            'id' => 'pt-102', 'tag' => 'CASING-101B', 'pozo_id' => 'well-a12', 'tipo_sensor' => 'TRANSMISOR_PRESION',
            'etiqueta' => 'PRESIÓN ANULAR (CASING)', 'descripcion' => 'Presión Espacio Anular Casing Pozo 01',
            'unidad' => 'PSI', 'valor' => 642.00, 'valor_objetivo' => 600.00, 'trip_bajo' => 100.00,
            'alarma_alta' => 800.00, 'trip_alto' => 950.00, 'limite_min' => 100.00, 'limite_max' => 950.00,
            'severidad' => 'advisory', 'tendencia' => 'rising',
            'historial' => [610, 618, 622, 630, 635, 638, 640, 642]
        ],
        'tt-301' => [
            'id' => 'tt-301', 'tag' => 'TEMP-FLOW-301', 'pozo_id' => 'well-a12', 'tipo_sensor' => 'TRANSMISOR_TEMPERATURA',
            'etiqueta' => 'TEMPERATURA DE MANIFOLD', 'descripcion' => 'Temperatura de Fondo y Línea Pozo 03',
            'unidad' => '°C', 'valor' => 78.40, 'valor_objetivo' => 75.00, 'trip_bajo' => 20.00,
            'alarma_alta' => 92.00, 'trip_alto' => 105.00, 'limite_min' => 20.00, 'limite_max' => 105.00,
            'severidad' => 'nominal', 'tendencia' => 'rising',
            'historial' => [75.2, 75.8, 76.5, 77.0, 77.5, 78.0, 78.2, 78.4]
        ],
        'ft-201' => [
            'id' => 'ft-201', 'tag' => 'MFM-201', 'pozo_id' => 'well-b04', 'tipo_sensor' => 'MEDIDOR_FLUJO',
            'etiqueta' => 'CAUDAL BRUTO DE PRODUCCIÓN', 'descripcion' => 'Caudalímetro Másico Multifásico Terminal Neiva',
            'unidad' => 'BPD', 'valor' => 14820.00, 'valor_objetivo' => 15000.00, 'trip_bajo' => 4000.00,
            'alarma_alta' => 16500.00, 'trip_alto' => 18000.00, 'limite_min' => 4000.00, 'limite_max' => 18000.00,
            'severidad' => 'nominal', 'tendencia' => 'stable',
            'historial' => [14750, 14780, 14810, 14800, 14830, 14815, 14820, 14820]
        ],
        'esp-01' => [
            'id' => 'esp-01', 'tag' => 'ESP-FREQ-01', 'pozo_id' => 'well-a12', 'tipo_sensor' => 'VARIADOR_FRECUENCIA',
            'etiqueta' => 'FRECUENCIA BOMBA ESP', 'descripcion' => 'Frecuencia Bombeo Electrosumergible ESP-01',
            'unidad' => 'Hz', 'valor' => 58.20, 'valor_objetivo' => 60.00, 'trip_bajo' => 35.00,
            'alarma_alta' => 63.50, 'trip_alto' => 66.00, 'limite_min' => 35.00, 'limite_max' => 66.00,
            'severidad' => 'nominal', 'tendencia' => 'stable',
            'historial' => [57.8, 58.0, 58.1, 58.2, 58.0, 58.2, 58.1, 58.2]
        ],
        'gor-402' => [
            'id' => 'gor-402', 'tag' => 'GOR-ANALYZER', 'pozo_id' => 'well-c09', 'tipo_sensor' => 'ANALIZADOR_GOR',
            'etiqueta' => 'RELACIÓN GAS/PETRÓLEO (GOR)', 'descripcion' => 'Relación Gas-Petróleo en Línea Pad 09',
            'unidad' => 'SCF/BBL', 'valor' => 462.80, 'valor_objetivo' => 450.00, 'trip_bajo' => 200.00,
            'alarma_alta' => 550.00, 'trip_alto' => 680.00, 'limite_min' => 200.00, 'limite_max' => 680.00,
            'severidad' => 'nominal', 'tendencia' => 'falling',
            'historial' => [475, 471, 468, 465, 464, 463, 463.5, 462.8]
        ]
    ];

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    public function getAll(): array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM `telemetria_metricas` ORDER BY `id` ASC");
                $stmt->execute();
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (Throwable $e) {
                error_log('[TelemetryRepository] Error consultando métricas: ' . $e->getMessage());
            }
        }
        return array_values(self::$fallbackMetrics);
    }

    public function getById(string $id): ?array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM `telemetria_metricas` WHERE `id` = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $res = $stmt->fetch();
                if ($res) return $res;
            } catch (Throwable $e) {
                error_log('[TelemetryRepository] Error getById: ' . $e->getMessage());
            }
        }
        return self::$fallbackMetrics[$id] ?? null;
    }

    public function getByTag(string $tag): ?array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM `telemetria_metricas` WHERE `tag` = :tag LIMIT 1");
                $stmt->execute([':tag' => $tag]);
                $res = $stmt->fetch();
                if ($res) return $res;
            } catch (Throwable $e) {
                error_log('[TelemetryRepository] Error getByTag: ' . $e->getMessage());
            }
        }
        foreach (self::$fallbackMetrics as $m) {
            if ($m['tag'] === $tag) return $m;
        }
        return null;
    }

    public function getPlantKpis(): array {
        if ($this->db) {
            try {
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
                if ($data && isset($data['total_wells'])) {
                    return [
                        'total_production'    => (float)($data['total_flow'] ?? 0),
                        'avg_tubing_pressure' => round((float)($data['avg_presion'] ?? 0), 1),
                        'avg_temperature'     => round((float)($data['avg_temp'] ?? 0), 1),
                        'active_wells_count'  => (int)($data['active_wells'] ?? 0),
                        'total_wells_count'   => (int)($data['total_wells'] ?? 0)
                    ];
                }
            } catch (Throwable $e) {
                error_log('[TelemetryRepository] Error calculando KPIs en BD: ' . $e->getMessage());
            }
        }

        return [
            'total_production'    => 10460.0,
            'avg_tubing_pressure' => 1671.8,
            'avg_temperature'     => 69.1,
            'active_wells_count'  => 2,
            'total_wells_count'   => 4
        ];
    }
}

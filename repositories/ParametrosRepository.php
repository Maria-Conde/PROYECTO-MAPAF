<?php
/**
 * PROYECTO MAPAF - Repositorio: Parámetros del Sistema & Configuración
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

require_once __DIR__ . '/../config/database.php';

class ParametrosRepository {
    private ?PDO $pdo;

    private static array $mockParametros = [
        'plant_name'             => ['valor' => 'MAPAF RTOC - Estación Central de Telemetría', 'descripcion' => 'Nombre de la instalación'],
        'sena_ficha'             => ['valor' => '3413988-ADSO', 'descripcion' => 'Ficha del programa SENA ADSO'],
        'production_rate'        => ['valor' => '14820', 'descripcion' => 'Tasa bruta de producción en BPD'],
        'flow_state'             => ['valor' => 'nominal', 'descripcion' => 'Estado del flujo general: nominal, throttled, tripped'],
        'esd_triggered'          => ['valor' => '0', 'descripcion' => 'Estado de Parada de Emergencia ESD-1 (0=Normal, 1=Disparado)'],
        'esd_trigger_time'       => ['valor' => '', 'descripcion' => 'Hora del último disparo ESD'],
        'choke_max_limit'        => ['valor' => '85.0', 'descripcion' => 'Límite máximo de apertura de estrangulador Choke (%)'],
        'trip_high_pressure'     => ['valor' => '2350.0', 'descripcion' => 'Presión de disparo alto de seguridad SIS (PSI)'],
        'trip_low_pressure'      => ['valor' => '650.0', 'descripcion' => 'Presión de disparo bajo de seguridad SIS (PSI)'],
        'telemetry_interval_sec' => ['valor' => '3', 'descripcion' => 'Intervalo de ciclo de telemetría RTU (segundos)']
    ];

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM `configuracion_sistema`");
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    $res = [];
                    foreach ($rows as $r) {
                        $res[$r['clave']] = [
                            'valor'       => $r['valor'],
                            'descripcion' => $r['descripcion'] ?? ''
                        ];
                    }
                    return array_merge(self::$mockParametros, $res);
                }
            } catch (Throwable $e) {}
        }
        return self::$mockParametros;
    }

    public function update(string $clave, string $valor, ?string $descripcion = null): bool {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`), `descripcion` = COALESCE(VALUES(`descripcion`), `descripcion`)
                ");
                return $stmt->execute([$clave, $valor, $descripcion]);
            } catch (Throwable $e) {}
        }
        self::$mockParametros[$clave] = [
            'valor'       => $valor,
            'descripcion' => $descripcion ?? (self::$mockParametros[$clave]['descripcion'] ?? '')
        ];
        return true;
    }

    public function batchImportCsvRows(array $rows): int {
        $count = 0;
        foreach ($rows as $r) {
            if (!empty($r['clave']) && isset($r['valor'])) {
                if ($this->update($r['clave'], (string)$r['valor'], $r['descripcion'] ?? null)) {
                    $count++;
                }
            }
        }
        return $count;
    }
}

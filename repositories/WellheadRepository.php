<?php
/**
 * PROYECTO MAPAF - Patrón de Diseño REPOSITORY: WellheadRepository
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona el acceso y persistencia de cabezales de pozo (API 6A / 5000-10000 PSI).
 * Incluye resiliencia con estado en memoria ante contingencias de base de datos.
 */

require_once __DIR__ . '/RepositoryInterface.php';
require_once __DIR__ . '/../config/database.php';

class WellheadRepository implements RepositoryInterface {
    private ?PDO $db;
    private static array $fallbackWells = [
        'well-01' => [
            'id' => 'well-01', 'nombre' => 'POZO HUILA-NORTE-01', 'sector' => 'PAD 04-A', 'campo' => 'Campo Huila Norte',
            'clase_api' => 'API 6A 5000 PSI', 'estado' => 'EN LÍNEA', 'apertura_choke' => 25.00,
            'objetivo_choke' => 25.00, 'valvula_ssv_abierta' => 1, 'presion_tubing' => 1845.50,
            'presion_casing' => 420.00, 'temperatura' => 68.50, 'caudal_produccion' => 3450.00,
            'frecuencia_esp' => 58.20, 'vibracion_esp' => 1.840, 'purga_anular_abierta' => 0,
            'inyeccion_quimica' => 1, 'arena_acustica_ppm' => 4.20,
            'bsw_porcentaje' => 3.20, 'gor_scfbbl' => 410.00
        ],
        'well-02' => [
            'id' => 'well-02', 'nombre' => 'POZO HUILA-SUR-04', 'sector' => 'PAD 04-B', 'campo' => 'Campo Huila Sur',
            'clase_api' => 'API 6A 5000 PSI', 'estado' => 'EN LÍNEA', 'apertura_choke' => 30.00,
            'objetivo_choke' => 30.00, 'valvula_ssv_abierta' => 1, 'presion_tubing' => 1720.00,
            'presion_casing' => 380.00, 'temperatura' => 71.20, 'caudal_produccion' => 4120.00,
            'frecuencia_esp' => 60.00, 'vibracion_esp' => 2.150, 'purga_anular_abierta' => 0,
            'inyeccion_quimica' => 1, 'arena_acustica_ppm' => 6.80,
            'bsw_porcentaje' => 4.50, 'gor_scfbbl' => 395.00
        ],
        'well-03' => [
            'id' => 'well-03', 'nombre' => 'POZO DINA-CRETÁCEO-09', 'sector' => 'PAD 09-NORTH', 'campo' => 'Campo Dina Cretáceo',
            'clase_api' => 'API 6A 10000 PSI', 'estado' => 'ESTRANGULADO', 'apertura_choke' => 15.00,
            'objetivo_choke' => 15.00, 'valvula_ssv_abierta' => 1, 'presion_tubing' => 2450.00,
            'presion_casing' => 510.00, 'temperatura' => 84.00, 'caudal_produccion' => 2890.00,
            'frecuencia_esp' => 52.00, 'vibracion_esp' => 1.450, 'purga_anular_abierta' => 0,
            'inyeccion_quimica' => 1, 'arena_acustica_ppm' => 2.10,
            'bsw_porcentaje' => 6.80, 'gor_scfbbl' => 520.00
        ],
        'well-04' => [
            'id' => 'well-04', 'nombre' => 'POZO TELLO-PROFUNDO-12', 'sector' => 'PAD 09-SOUTH', 'campo' => 'Campo Tello',
            'clase_api' => 'API 6A 5000 PSI', 'estado' => 'DISPARADO', 'apertura_choke' => 0.00,
            'objetivo_choke' => 0.00, 'valvula_ssv_abierta' => 0, 'presion_tubing' => 650.00,
            'presion_casing' => 120.00, 'temperatura' => 52.00, 'caudal_produccion' => 0.00,
            'frecuencia_esp' => 0.00, 'vibracion_esp' => 0.020, 'purga_anular_abierta' => 1,
            'inyeccion_quimica' => 0, 'arena_acustica_ppm' => 0.00,
            'bsw_porcentaje' => 12.00, 'gor_scfbbl' => 150.00
        ]
    ];

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    public function getAll(): array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM `pozos` ORDER BY `id` ASC");
                $stmt->execute();
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (Throwable $e) {
                error_log('[WellheadRepository] Fallo en BD, usando fallback: ' . $e->getMessage());
            }
        }
        return array_values(self::$fallbackWells);
    }

    public function getById(string $id): ?array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM `pozos` WHERE `id` = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $res = $stmt->fetch();
                if ($res) {
                    return $res;
                }
            } catch (Throwable $e) {
                error_log('[WellheadRepository] Fallo getById en BD: ' . $e->getMessage());
            }
        }
        return self::$fallbackWells[$id] ?? null;
    }

    public function setChoke(string $id, float $chokeOpening): bool {
        $clamped = max(0.0, min(100.0, $chokeOpening));
        if (isset(self::$fallbackWells[$id])) {
            self::$fallbackWells[$id]['apertura_choke'] = $clamped;
            self::$fallbackWells[$id]['objetivo_choke'] = $clamped;
            if ($clamped <= 0.01) {
                self::$fallbackWells[$id]['estado'] = 'DISPARADO';
            } elseif ($clamped < 20.0) {
                self::$fallbackWells[$id]['estado'] = 'ESTRANGULADO';
            } else {
                self::$fallbackWells[$id]['estado'] = 'EN LÍNEA';
            }
        }

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE `pozos` 
                    SET `apertura_choke` = :choke, `objetivo_choke` = :target 
                    WHERE `id` = :id
                ");
                return $stmt->execute([
                    ':choke'  => $clamped,
                    ':target' => $clamped,
                    ':id'     => $id
                ]);
            } catch (Throwable $e) {
                error_log('[WellheadRepository] Error actualizando choke en BD: ' . $e->getMessage());
            }
        }
        return isset(self::$fallbackWells[$id]);
    }

    public function setSsvValve(string $id, bool $isOpen): bool {
        if (isset(self::$fallbackWells[$id])) {
            self::$fallbackWells[$id]['valvula_ssv_abierta'] = $isOpen ? 1 : 0;
            if (!$isOpen) {
                self::$fallbackWells[$id]['estado'] = 'DISPARADO';
                self::$fallbackWells[$id]['apertura_choke'] = 0.0;
            }
        }

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE `pozos` 
                    SET `valvula_ssv_abierta` = :ssv 
                    WHERE `id` = :id
                ");
                return $stmt->execute([
                    ':ssv' => $isOpen ? 1 : 0,
                    ':id'  => $id
                ]);
            } catch (Throwable $e) {
                error_log('[WellheadRepository] Error actualizando SSV en BD: ' . $e->getMessage());
            }
        }
        return isset(self::$fallbackWells[$id]);
    }

    public function setStatus(string $id, string $status): bool {
        if (isset(self::$fallbackWells[$id])) {
            self::$fallbackWells[$id]['estado'] = $status;
        }

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("UPDATE `pozos` SET `estado` = :status WHERE `id` = :id");
                return $stmt->execute([':status' => $status, ':id' => $id]);
            } catch (Throwable $e) {
                error_log('[WellheadRepository] Error actualizando estado en BD: ' . $e->getMessage());
            }
        }
        return isset(self::$fallbackWells[$id]);
    }

    public function resetWellhead(string $id): bool {
        if (isset(self::$fallbackWells[$id])) {
            self::$fallbackWells[$id]['estado'] = 'EN LÍNEA';
            self::$fallbackWells[$id]['valvula_ssv_abierta'] = 1;
            self::$fallbackWells[$id]['apertura_choke'] = 15.00;
            self::$fallbackWells[$id]['objetivo_choke'] = 15.00;
        }

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE `pozos` 
                    SET `estado` = 'EN LÍNEA',
                        `valvula_ssv_abierta` = 1,
                        `apertura_choke` = 15.00,
                        `objetivo_choke` = 15.00
                    WHERE `id` = :id
                ");
                return $stmt->execute([':id' => $id]);
            } catch (Throwable $e) {
                error_log('[WellheadRepository] Error rearmando pozo en BD: ' . $e->getMessage());
            }
        }
        return isset(self::$fallbackWells[$id]);
    }

    public function isolateAllWells(): bool {
        foreach (self::$fallbackWells as &$w) {
            $w['valvula_ssv_abierta'] = 0;
            $w['apertura_choke'] = 0.00;
            $w['objetivo_choke'] = 0.00;
            $w['estado'] = 'DISPARADO';
        }

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE `pozos` 
                    SET `valvula_ssv_abierta` = 0,
                        `apertura_choke` = 0.00,
                        `objetivo_choke` = 0.00,
                        `estado` = 'DISPARADO'
                ");
                return $stmt->execute();
            } catch (Throwable $e) {
                error_log('[WellheadRepository] Error aislando pozos: ' . $e->getMessage());
            }
        }
        return true;
    }

    public function resetAllWellsNominal(): bool {
        foreach (self::$fallbackWells as &$w) {
            if ($w['estado'] === 'DISPARADO') {
                $w['valvula_ssv_abierta'] = 1;
                $w['apertura_choke'] = 15.00;
                $w['objetivo_choke'] = 15.00;
                $w['estado'] = 'EN LÍNEA';
            }
        }

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE `pozos` 
                    SET `valvula_ssv_abierta` = 1,
                        `apertura_choke` = 15.00,
                        `objetivo_choke` = 15.00,
                        `estado` = 'EN LÍNEA'
                    WHERE `estado` = 'DISPARADO'
                ");
                return $stmt->execute();
            } catch (Throwable $e) {
                error_log('[WellheadRepository] Error rearmando todos los pozos: ' . $e->getMessage());
            }
        }
        return true;
    }
}

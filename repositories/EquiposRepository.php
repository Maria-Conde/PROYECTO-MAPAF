<?php
/**
 * PROYECTO MAPAF - Repositorio: Equipos Industriales
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

require_once __DIR__ . '/../config/database.php';

class EquiposRepository {
    private ?PDO $pdo;

    private static array $mockEquipos = [
        [
            'id'                  => 'eq-001',
            'tag'                 => 'PZ-LIZ-104',
            'nombre'              => 'Cabeza de Pozo',
            'tipo'                => 'Pozo',
            'campo'               => 'Campo Lizama',
            'presion_critica'     => 538.20,
            'caudal_produccion'   => 1253.00,
            'estado'              => 'OPERATIVO',
            'tiempo_conexion_seg' => 2
        ],
        [
            'id'                  => 'eq-002',
            'tag'                 => 'TK-ALM-01',
            'nombre'              => 'Tanque Fiscal Cusiana',
            'tipo'                => 'Tanque',
            'campo'               => 'Campo Cusiana',
            'presion_critica'     => 14.60,
            'caudal_produccion'   => 3171.00,
            'estado'              => 'OPERATIVO',
            'tiempo_conexion_seg' => 5
        ],
        [
            'id'                  => 'eq-003',
            'tag'                 => 'PZ-ALF-12',
            'nombre'              => 'Cabeza de Pozo API 6A',
            'tipo'                => 'Pozo',
            'campo'               => 'Campo Neiva Huila',
            'presion_critica'     => 1842.50,
            'caudal_produccion'   => 4210.00,
            'estado'              => 'OPERATIVO',
            'tiempo_conexion_seg' => 1
        ],
        [
            'id'                  => 'eq-004',
            'tag'                 => 'SEP-V201',
            'nombre'              => 'Separador Trifásico V-201',
            'tipo'                => 'Separador',
            'campo'               => 'Campo Lizama',
            'presion_critica'     => 450.00,
            'caudal_produccion'   => 14820.00,
            'estado'              => 'OPERATIVO',
            'tiempo_conexion_seg' => 3
        ],
        [
            'id'                  => 'eq-005',
            'tag'                 => 'BMB-ESP-01',
            'nombre'              => 'Bomba Electrosumergible ESP',
            'tipo'                => 'Bomba',
            'campo'               => 'Campo Cusiana',
            'presion_critica'     => 820.00,
            'caudal_produccion'   => 4890.00,
            'estado'              => 'OPERATIVO',
            'tiempo_conexion_seg' => 2
        ],
        [
            'id'                  => 'eq-006',
            'tag'                 => 'TK-DES-02',
            'nombre'              => 'Tanque de Decantación 02',
            'tipo'                => 'Tanque',
            'campo'               => 'Campo Neiva Huila',
            'presion_critica'     => 8.50,
            'caudal_produccion'   => 2980.00,
            'estado'              => 'OPERATIVO',
            'tiempo_conexion_seg' => 4
        ]
    ];

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM `equipos_industriales` ORDER BY `tag` ASC");
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (Throwable $e) {}
        }
        return self::$mockEquipos;
    }

    public function getById(string $id): ?array {
        $all = $this->getAll();
        foreach ($all as $eq) {
            if ($eq['id'] === $id || $eq['tag'] === $id) {
                return $eq;
            }
        }
        return null;
    }

    public function updateStatus(string $id, string $estado): bool {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("UPDATE `equipos_industriales` SET `estado` = ? WHERE `id` = ? OR `tag` = ?");
                return $stmt->execute([$estado, $id, $id]);
            } catch (Throwable $e) {}
        }
        foreach (self::$mockEquipos as &$eq) {
            if ($eq['id'] === $id || $eq['tag'] === $id) {
                $eq['estado'] = $estado;
                return true;
            }
        }
        return false;
    }
}


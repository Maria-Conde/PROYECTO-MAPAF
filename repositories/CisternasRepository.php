<?php
/**
 * PROYECTO MAPAF - Repositorio: Despacho de Cisternas en Islas
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

require_once __DIR__ . '/../config/database.php';

class CisternasRepository {
    private ?PDO $pdo;

    private static array $mockCisternas = [
        [
            'id'                 => 'dsp-001',
            'numero_guia'        => 'DSP-2026-0842',
            'placa_cisterna'     => 'SQP-418',
            'transportadora'     => 'TransCaribe Hidrocarburos SAS',
            'conductor'          => 'Jairo Ortiz Morales',
            'isla'               => 'Isla 1 (Crudo Liviano API 32)',
            'volumen_solicitado' => 300.00,
            'volumen_cargado'    => 220.00,
            'precinto_fiscal'    => 'SEC-984210',
            'estado'             => 'EN LLENADO',
            'fecha_despacho'     => '2026-11-04 18:40:00'
        ],
        [
            'id'                 => 'dsp-002',
            'numero_guia'        => 'DSP-2026-0843',
            'placa_cisterna'     => 'WMC-902',
            'transportadora'     => 'Transportes Petroleros del Huila',
            'conductor'          => 'Andrés Castro Rivera',
            'isla'               => 'Isla 3 (Nafta / Diluyente)',
            'volumen_solicitado' => 250.00,
            'volumen_cargado'    => 250.00,
            'precinto_fiscal'    => 'SEC-984211',
            'estado'             => 'COMPLETADO',
            'fecha_despacho'     => '2026-11-04 17:15:00'
        ],
        [
            'id'                 => 'dsp-003',
            'numero_guia'        => 'DSP-2026-0844',
            'placa_cisterna'     => 'TGA-551',
            'transportadora'     => 'Logística Cusiana Oriente',
            'conductor'          => 'Fabián Gómez Soler',
            'isla'               => 'Isla 2 (Crudo Pesado Castilla)',
            'volumen_solicitado' => 280.00,
            'volumen_cargado'    => 0.00,
            'precinto_fiscal'    => 'SEC-984212',
            'estado'             => 'AUTORIZADO',
            'fecha_despacho'     => '2026-11-04 19:10:00'
        ]
    ];

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query("SELECT * FROM `despachos_cisternas` ORDER BY `fecha_despacho` DESC");
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (Throwable $e) {}
        }
        return self::$mockCisternas;
    }

    public function create(array $data): bool {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO `despachos_cisternas` 
                    (`id`, `numero_guia`, `placa_cisterna`, `transportadora`, `conductor`, `isla`, `volumen_solicitado`, `volumen_cargado`, `precinto_fiscal`, `estado`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                return $stmt->execute([
                    $data['id'] ?? ('dsp-' . uniqid()),
                    $data['numero_guia'],
                    $data['placa_cisterna'],
                    $data['transportadora'],
                    $data['conductor'],
                    $data['isla'],
                    $data['volumen_solicitado'],
                    $data['volumen_cargado'] ?? 0.0,
                    $data['precinto_fiscal'] ?? ('SEC-' . mt_rand(100000, 999999)),
                    $data['estado'] ?? 'AUTORIZADO'
                ]);
            } catch (Throwable $e) {}
        }

        $data['id'] = $data['id'] ?? ('dsp-' . uniqid());
        $data['fecha_despacho'] = date('Y-m-d H:i:s');
        array_unshift(self::$mockCisternas, $data);
        return true;
    }
}


<?php
/**
 * PROYECTO MAPAF - Servicio: Despacho de Cisternas
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

require_once __DIR__ . '/../repositories/CisternasRepository.php';

class CisternasService {
    private CisternasRepository $repository;

    public function __construct(?CisternasRepository $repo = null) {
        $this->repository = $repo ?? new CisternasRepository();
    }

    public function getAllDespachos(): array {
        return $this->repository->getAll();
    }

    public function getIslasStatus(): array {
        $despachos = $this->repository->getAll();
        
        $islas = [
            'isla_1' => [
                'nombre'    => 'Isla 01 - Crudo Liviano (API 32)',
                'producto'  => 'Crudo Liviano Cusiana',
                'estado'    => 'DISPONIBLE',
                'cisterna'  => null,
                'progreso'  => 0
            ],
            'isla_2' => [
                'nombre'    => 'Isla 02 - Crudo Pesado Castilla (API 18)',
                'producto'  => 'Crudo Pesado Rubiales',
                'estado'    => 'DISPONIBLE',
                'cisterna'  => null,
                'progreso'  => 0
            ],
            'isla_3' => [
                'nombre'    => 'Isla 03 - Nafta / Diluyente',
                'producto'  => 'Nafta Virgen de Mezcla',
                'estado'    => 'DISPONIBLE',
                'cisterna'  => null,
                'progreso'  => 0
            ]
        ];

        foreach ($despachos as $d) {
            if ($d['estado'] === 'EN LLENADO') {
                if (stripos($d['isla'], '1') !== false) {
                    $islas['isla_1']['estado'] = 'EN LLENADO';
                    $islas['isla_1']['cisterna'] = $d['placa_cisterna'];
                    $islas['isla_1']['progreso'] = $d['volumen_solicitado'] > 0 ? round(($d['volumen_cargado'] / $d['volumen_solicitado']) * 100) : 0;
                } elseif (stripos($d['isla'], '2') !== false) {
                    $islas['isla_2']['estado'] = 'EN LLENADO';
                    $islas['isla_2']['cisterna'] = $d['placa_cisterna'];
                    $islas['isla_2']['progreso'] = $d['volumen_solicitado'] > 0 ? round(($d['volumen_cargado'] / $d['volumen_solicitado']) * 100) : 0;
                } elseif (stripos($d['isla'], '3') !== false) {
                    $islas['isla_3']['estado'] = 'EN LLENADO';
                    $islas['isla_3']['cisterna'] = $d['placa_cisterna'];
                    $islas['isla_3']['progreso'] = $d['volumen_solicitado'] > 0 ? round(($d['volumen_cargado'] / $d['volumen_solicitado']) * 100) : 0;
                }
            }
        }

        return $islas;
    }

    public function programarDespacho(array $data): bool {
        return $this->repository->create($data);
    }
}


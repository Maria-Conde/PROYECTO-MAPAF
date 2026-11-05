<?php
/**
 * PROYECTO MAPAF - Servicio: Equipos Industriales
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

require_once __DIR__ . '/../repositories/EquiposRepository.php';

class EquiposService {
    private EquiposRepository $repository;

    public function __construct(?EquiposRepository $repo = null) {
        $this->repository = $repo ?? new EquiposRepository();
    }

    public function getAllEquipos(?string $filter = null): array {
        $all = $this->repository->getAll();
        if (!$filter || $filter === 'ALL') {
            return $all;
        }
        return array_values(array_filter($all, function ($e) use ($filter) {
            return strcasecmp($e['tipo'], $filter) === 0;
        }));
    }

    public function getSummaryKpis(): array {
        $all = $this->repository->getAll();
        $total = count($all);
        $operativos = 0;
        $totalProduccion = 0.0;

        foreach ($all as $e) {
            if ($e['estado'] === 'OPERATIVO') {
                $operativos++;
            }
            $totalProduccion += (float)$e['caudal_produccion'];
        }

        $disponibilidad = $total > 0 ? round(($operativos / $total) * 100, 1) : 100.0;

        return [
            'total_equipos'    => $total,
            'operativos'       => $operativos,
            'disponibilidad'   => $disponibilidad,
            'total_produccion' => $totalProduccion
        ];
    }

    public function toggleEstado(string $id, string $nuevoEstado): bool {
        return $this->repository->updateStatus($id, $nuevoEstado);
    }
}


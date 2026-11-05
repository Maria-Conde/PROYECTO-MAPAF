<?php
/**
 * PROYECTO MAPAF - Servicio: Parámetros del Sistema
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

require_once __DIR__ . '/../repositories/ParametrosRepository.php';

class ParametrosService {
    private ParametrosRepository $repository;

    public function __construct(?ParametrosRepository $repo = null) {
        $this->repository = $repo ?? new ParametrosRepository();
    }

    public function getParametros(): array {
        return $this->repository->getAll();
    }

    public function saveParametros(array $data): bool {
        foreach ($data as $k => $v) {
            $this->repository->update($k, (string)$v);
        }
        return true;
    }

    public function parseAndImportCsv(string $csvContent): array {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent));
        if (empty($lines)) {
            return ['success' => false, 'error' => 'El archivo CSV está vacío'];
        }

        $header = str_getcsv(array_shift($lines));
        $header = array_map('trim', array_map('strtolower', $header));

        $imported = 0;
        $rows = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $data = str_getcsv($line);
            if (count($data) < 2) continue;

            $clave = trim($data[0]);
            $valor = trim($data[1]);
            $desc = isset($data[2]) ? trim($data[2]) : 'Importado vía CSV';

            $rows[] = [
                'clave'       => $clave,
                'valor'       => $valor,
                'descripcion' => $desc,
                'valido'      => true
            ];

            if ($this->repository->update($clave, $valor, $desc)) {
                $imported++;
            }
        }

        return [
            'success'   => true,
            'imported'  => $imported,
            'rows'      => $rows
        ];
    }
}

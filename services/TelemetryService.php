<?php
/**
 * PROYECTO MAPAF - Capa de Servicio: TelemetryService
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona lecturas de sensores IIoT, evaluación de severidad y KPIs consolidados.
 */

require_once __DIR__ . '/../repositories/TelemetryRepository.php';

class TelemetryService {
    private TelemetryRepository $telemetryRepo;

    public function __construct(?TelemetryRepository $telemetryRepo = null) {
        $this->telemetryRepo = $telemetryRepo ?: new TelemetryRepository();
    }

    public function getAllMetrics(): array {
        return $this->telemetryRepo->getAll();
    }

    public function getMetricByTag(string $tag): ?array {
        return $this->telemetryRepo->getByTag($tag);
    }

    public function getConsolidatedKpis(): array {
        return $this->telemetryRepo->getPlantKpis();
    }
}

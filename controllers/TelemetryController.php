<?php
/**
 * PROYECTO MAPAF - Controlador: TelemetryController
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Expone las lecturas en tiempo real de sensores IIoT y los KPIs consolidados.
 */

require_once __DIR__ . '/../services/TelemetryService.php';
require_once __DIR__ . '/../config/cors.php';

class TelemetryController {
    private TelemetryService $telemetryService;

    public function __construct(?TelemetryService $telemetryService = null) {
        $this->telemetryService = $telemetryService ?: new TelemetryService();
    }

    /**
     * Devuelve las métricas de telemetría y los KPIs de planta
     */
    public function index(): void {
        $metrics = $this->telemetryService->getAllMetrics();
        $kpis = $this->telemetryService->getConsolidatedKpis();

        sendJsonResponse([
            'success'   => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'kpis'      => $kpis,
            'metrics'   => $metrics
        ]);
    }
}

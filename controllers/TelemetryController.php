<?php
/**
 * PROYECTO MAPAF - Controlador: TelemetryController
 * SENA ADSO - Ficha 3413988
 * 
 * Expone las lecturas en tiempo real de sensores IIoT y los KPIs consolidados.
 */

require_once __DIR__ . '/../models/TelemetriaModel.php';
require_once __DIR__ . '/../config/cors.php';

class TelemetryController {
    private TelemetriaModel $telemetriaModel;

    public function __construct() {
        $this->telemetriaModel = new TelemetriaModel();
    }

    /**
     * Devuelve las métricas de telemetría y los KPIs de planta
     */
    public function index(): void {
        $metrics = $this->telemetriaModel->getAll();
        $kpis = $this->telemetriaModel->getPlantKpis();

        sendJsonResponse([
            'success'   => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'kpis'      => $kpis,
            'metrics'   => $metrics
        ]);
    }
}

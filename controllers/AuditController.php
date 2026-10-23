<?php
/**
 * PROYECTO MAPAF - Controlador: AuditController
 * SENA ADSO - Ficha 3413988
 * 
 * Expone la bitácora inmutable de eventos y maniobras (21 CFR Part 11).
 */

require_once __DIR__ . '/../models/AuditoriaModel.php';
require_once __DIR__ . '/../config/cors.php';

class AuditController {
    private AuditoriaModel $auditoriaModel;

    public function __construct() {
        $this->auditoriaModel = new AuditoriaModel();
    }

    /**
     * Lista los registros más recientes de la bitácora
     */
    public function index(): void {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $logs = $this->auditoriaModel->getRecent($limit);

        sendJsonResponse([
            'success' => true,
            'count'   => count($logs),
            'logs'    => $logs
        ]);
    }
}

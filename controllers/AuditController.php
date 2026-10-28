<?php
/**
 * PROYECTO MAPAF - Controlador: AuditController
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Expone la consulta de la bitácora de auditoría inmutable (21 CFR Part 11).
 */

require_once __DIR__ . '/../repositories/AuditRepository.php';
require_once __DIR__ . '/../config/cors.php';

class AuditController {
    private AuditRepository $auditRepo;

    public function __construct(?AuditRepository $auditRepo = null) {
        $this->auditRepo = $auditRepo ?: new AuditRepository();
    }

    /**
     * Consulta los registros más recientes de auditoría
     */
    public function index(): void {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $logs = $this->auditRepo->getRecent($limit);

        sendJsonResponse([
            'success' => true,
            'count'   => count($logs),
            'logs'    => $logs
        ]);
    }
}

<?php
/**
 * PROYECTO MAPAF - Controlador: AlarmController
 * SENA ADSO - Ficha 3413988
 * 
 * Gestiona el listado y acuse de alarmas operacionales (ANSI/ISA-18.2).
 */

require_once __DIR__ . '/../models/AlarmaModel.php';
require_once __DIR__ . '/../models/AuditoriaModel.php';
require_once __DIR__ . '/../config/cors.php';

class AlarmController {
    private AlarmaModel $alarmaModel;
    private AuditoriaModel $auditoriaModel;

    public function __construct() {
        $this->alarmaModel = new AlarmaModel();
        $this->auditoriaModel = new AuditoriaModel();
    }

    /**
     * Lista alarmas y conteos de pendientes
     */
    public function index(): void {
        $unackOnly = isset($_GET['pending']) && $_GET['pending'] === '1';
        $alarms = $this->alarmaModel->getAll($unackOnly);
        $counts = $this->alarmaModel->getPendingCounts();

        sendJsonResponse([
            'success' => true,
            'counts'  => $counts,
            'alarms'  => $alarms
        ]);
    }

    /**
     * Reconoce una alarma con validación de operador y justificación
     */
    public function acknowledge(): void {
        $input = getJsonInput();
        $alarmId = $input['id'] ?? null;
        $operatorCode = $input['operatorCode'] ?? ($_SESSION['user']['codigo_empleado'] ?? 'EMP-8812');
        $operatorName = $input['operatorName'] ?? ($_SESSION['user']['nombre_completo'] ?? '');
        $reason = $input['reason'] ?? '';

        if (!$alarmId) {
            sendJsonResponse(['success' => false, 'error' => 'Identificador "id" de alarma requerido'], 400);
        }

        $alarm = $this->alarmaModel->getById($alarmId);
        if (!$alarm) {
            sendJsonResponse(['success' => false, 'error' => "Alarma '{$alarmId}' no encontrada"], 404);
        }

        $res = $this->alarmaModel->acknowledge($alarmId, $operatorName, $reason);
        if (!$res['success']) {
            // Registrar intento fallido / bloqueo de Blind ACK
            $this->auditoriaModel->log(
                $operatorCode,
                $operatorName ?: 'Desconocido',
                'Operador',
                "Intento de Acuse Ciego en Alarma {$alarm['tag']}",
                'SEGURIDAD',
                'RECHAZADO',
                "ESCUDO ISA-18.2: {$res['error']}"
            );
            sendJsonResponse(['success' => false, 'error' => $res['error']], 422);
        }

        // Registrar acuse formal exitoso
        $this->auditoriaModel->log(
            $operatorCode,
            $operatorName,
            'Operador / Sala de Control',
            "Acuse de Alarma {$alarm['tag']} ({$alarm['severidad']})",
            'ALARMA',
            'EXITOSO',
            "Alarma formalmente reconocida. Justificación técnica: '{$reason}'"
        );

        sendJsonResponse([
            'success'       => true,
            'message'       => "Alarma {$alarm['tag']} reconocida formalmente",
            'ack_timestamp' => $res['ack_timestamp'],
            'operator'      => $operatorName
        ]);
    }
}

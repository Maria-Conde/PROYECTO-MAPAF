<?php
/**
 * PROYECTO MAPAF - Controlador: AlarmController
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Controlador delgado para gestión de alarmas (ANSI/ISA-18.2).
 * Delega reglas y mitigación de acuse ciego a AlarmService.
 */

require_once __DIR__ . '/../services/AlarmService.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../config/cors.php';

class AlarmController {
    private AlarmService $alarmService;
    private AuthService $authService;

    public function __construct(?AlarmService $alarmService = null, ?AuthService $authService = null) {
        $this->alarmService = $alarmService ?: new AlarmService();
        $this->authService = $authService ?: new AuthService();
    }

    /**
     * Lista alarmas y conteos de pendientes
     */
    public function index(): void {
        $unackOnly = isset($_GET['pending']) && $_GET['pending'] === '1';
        $alarms = $this->alarmService->getAlarms($unackOnly);
        $counts = $this->alarmService->getAlarmCounts();

        sendJsonResponse([
            'success' => true,
            'counts'  => $counts,
            'alarms'  => $alarms
        ]);
    }

    /**
     * Reconoce una alarma con validación de operador y justificación técnica
     */
    public function acknowledge(): void {
        $input = getJsonInput();
        $alarmId = $input['id'] ?? null;
        $operatorName = $input['operatorName'] ?? '';
        $reason = $input['reason'] ?? '';

        if (!$alarmId) {
            sendJsonResponse(['success' => false, 'error' => 'Identificador "id" de alarma requerido'], 400);
        }

        $operator = $this->authService->getCurrentOperator();

        try {
            $result = $this->alarmService->acknowledgeAlarm($alarmId, $operatorName, $reason, $operator);
            sendJsonResponse($result);
        } catch (ValidationException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (DomainException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        } catch (Throwable $e) {
            sendJsonResponse(['success' => false, 'error' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }
}

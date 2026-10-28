<?php
/**
 * PROYECTO MAPAF - Controlador: EsdController
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Controlador delgado para Sistema Instrumentado de Seguridad (SIS / ESD-1).
 * Delega reglas y aislamiento atómico a EsdService.
 */

require_once __DIR__ . '/../services/EsdService.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../config/cors.php';

class EsdController {
    private EsdService $esdService;
    private AuthService $authService;

    public function __construct(?EsdService $esdService = null, ?AuthService $authService = null) {
        $this->esdService = $esdService ?: new EsdService();
        $this->authService = $authService ?: new AuthService();
    }

    /**
     * Consulta el estado del sistema y parada de emergencia
     */
    public function index(): void {
        $state = $this->esdService->getSystemState();
        sendJsonResponse([
            'success' => true,
            'state'   => $state
        ]);
    }

    /**
     * Dispara la Parada de Emergencia ESD-1
     */
    public function trigger(): void {
        $input = getJsonInput();
        $reason = $input['reason'] ?? 'Disparo manual preventivo desde consola SCADA';

        $operator = $this->authService->getCurrentOperator();

        try {
            $result = $this->esdService->triggerEsd($reason, $operator);
            sendJsonResponse($result);
        } catch (ValidationException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            sendJsonResponse(['success' => false, 'error' => 'Fallo al procesar ESD-1: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Rearma el sistema de seguridad
     */
    public function reset(): void {
        $operator = $this->authService->getCurrentOperator();

        try {
            $result = $this->esdService->resetEsd($operator);
            sendJsonResponse($result);
        } catch (ValidationException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            sendJsonResponse(['success' => false, 'error' => 'Fallo al rearmar ESD-1: ' . $e->getMessage()], 500);
        }
    }
}

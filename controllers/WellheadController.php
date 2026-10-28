<?php
/**
 * PROYECTO MAPAF - Controlador: WellheadController
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Controlador delgado (Thin Controller):
 * Delega toda la lógica de negocio, validaciones y enclavamientos a WellheadService.
 */

require_once __DIR__ . '/../services/WellheadService.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../config/cors.php';

class WellheadController {
    private WellheadService $wellheadService;
    private AuthService $authService;

    public function __construct(?WellheadService $wellheadService = null, ?AuthService $authService = null) {
        $this->wellheadService = $wellheadService ?: new WellheadService();
        $this->authService = $authService ?: new AuthService();
    }

    /**
     * Lista todos los pozos
     */
    public function index(): void {
        $wells = $this->wellheadService->getAllWells();
        sendJsonResponse([
            'success' => true,
            'data'    => $wells
        ]);
    }

    /**
     * Ejecuta una maniobra sobre el pozo (Choke, SSV o Rearme)
     */
    public function update(): void {
        $input = getJsonInput();
        $wellId = $input['id'] ?? null;

        if (!$wellId) {
            sendJsonResponse(['success' => false, 'error' => 'Se requiere el parámetro "id" del cabezal de pozo'], 400);
        }

        // Resolver operador desde token JWT (o sesión de respaldo)
        $operator = $this->authService->getCurrentOperator();

        try {
            // Caso 1: Actualizar apertura de Choke
            if (isset($input['chokeOpening'])) {
                $rawChoke = (float)$input['chokeOpening'];
                $result = $this->wellheadService->adjustChoke($wellId, $rawChoke, $operator);
                sendJsonResponse($result);
            }

            // Caso 2: Conmutar Válvula de Seguridad SSV
            if (isset($input['ssvOpen'])) {
                $ssvOpen = (bool)$input['ssvOpen'];
                $result = $this->wellheadService->toggleSsvValve($wellId, $ssvOpen, $operator);
                sendJsonResponse($result);
            }

            // Caso 3: Rearme de pozo disparado
            if (isset($input['action']) && $input['action'] === 'reset') {
                $result = $this->wellheadService->resetWell($wellId, $operator);
                sendJsonResponse($result);
            }

            sendJsonResponse(['success' => false, 'error' => 'No se especificó ninguna acción válida'], 400);
        } catch (ValidationException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (DomainException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        } catch (Throwable $e) {
            sendJsonResponse(['success' => false, 'error' => 'Error inesperado: ' . $e->getMessage()], 500);
        }
    }
}

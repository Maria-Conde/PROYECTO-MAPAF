<?php
/**
 * PROYECTO MAPAF - Controlador: EsdController
 * SENA ADSO - Ficha 3413988
 * 
 * Gestiona el sistema instrumentado de seguridad (SIS) y Parada de Emergencia ESD-1.
 */

require_once __DIR__ . '/../models/EsdModel.php';
require_once __DIR__ . '/../config/cors.php';

class EsdController {
    private EsdModel $esdModel;

    public function __construct() {
        $this->esdModel = new EsdModel();
    }

    /**
     * Consulta el estado del sistema y parada de emergencia
     */
    public function index(): void {
        $state = $this->esdModel->getSystemState();
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
        $operatorCode = $input['operatorCode'] ?? ($_SESSION['user']['codigo_empleado'] ?? 'EMP-8812');
        $operatorName = $input['operatorName'] ?? ($_SESSION['user']['nombre_completo'] ?? 'Operador de Emergencia');
        $reason = $input['reason'] ?? 'Disparo manual preventivo desde consola SCADA';

        $ok = $this->esdModel->triggerEsd($operatorCode, $operatorName, $reason);

        if ($ok) {
            sendJsonResponse([
                'success' => true,
                'message' => '¡PARADA DE EMERGENCIA ESD-1 ACTIVADA! Todos los pozos han sido aislados.',
                'state'   => $this->esdModel->getSystemState()
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'error'   => 'Error interno al procesar el disparo de seguridad ESD-1'
            ], 500);
        }
    }

    /**
     * Rearma el sistema de seguridad
     */
    public function reset(): void {
        $input = getJsonInput();
        $operatorCode = $input['operatorCode'] ?? ($_SESSION['user']['codigo_empleado'] ?? 'EMP-8812');
        $operatorName = $input['operatorName'] ?? ($_SESSION['user']['nombre_completo'] ?? 'Supervisor Autorizado');

        $ok = $this->esdModel->resetEsd($operatorCode, $operatorName);

        if ($ok) {
            sendJsonResponse([
                'success' => true,
                'message' => 'Sistema ESD-1 rearmado exitosamente. Planta en estado nominal seguro.',
                'state'   => $this->esdModel->getSystemState()
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'error'   => 'Error interno al procesar el rearme de seguridad'
            ], 500);
        }
    }
}

<?php
/**
 * PROYECTO MAPAF - Controlador: WellheadController
 * SENA ADSO - Ficha 3413988
 * 
 * Orquesta las maniobras operativas sobre cabezales de pozo (API 6A).
 */

require_once __DIR__ . '/../models/PozoModel.php';
require_once __DIR__ . '/../models/EsdModel.php';
require_once __DIR__ . '/../models/AuditoriaModel.php';
require_once __DIR__ . '/../config/cors.php';

class WellheadController {
    private PozoModel $pozoModel;
    private EsdModel $esdModel;
    private AuditoriaModel $auditoriaModel;

    public function __construct() {
        $this->pozoModel = new PozoModel();
        $this->esdModel = new EsdModel();
        $this->auditoriaModel = new AuditoriaModel();
    }

    /**
     * Lista todos los pozos
     */
    public function index(): void {
        $wells = $this->pozoModel->getAll();
        sendJsonResponse([
            'success' => true,
            'data'    => $wells
        ]);
    }

    /**
     * Ejecuta una maniobra sobre el pozo (Choke o SSV)
     */
    public function update(): void {
        $input = getJsonInput();
        $wellId = $input['id'] ?? null;
        $operatorCode = $input['operatorCode'] ?? ($_SESSION['user']['codigo_empleado'] ?? 'EMP-8812');
        $operatorName = $input['operatorName'] ?? ($_SESSION['user']['nombre_completo'] ?? 'Operador de Turno');

        if (!$wellId) {
            sendJsonResponse(['success' => false, 'error' => 'Se requiere el parámetro "id" del pozo'], 400);
        }

        $well = $this->pozoModel->getById($wellId);
        if (!$well) {
            sendJsonResponse(['success' => false, 'error' => "Pozo '{$wellId}' no encontrado"], 404);
        }

        // Si la planta está en parada de emergencia ESD-1, no permitir maniobras de apertura
        if ($this->esdModel->isTriggered()) {
            $this->auditoriaModel->log(
                $operatorCode,
                $operatorName,
                'Operador',
                "Intento de maniobra en pozo {$well['nombre']}",
                'SEGURIDAD',
                'RECHAZADO',
                "Maniobra bloqueada: La planta se encuentra en estado de Parada de Emergencia ESD-1 activa."
            );
            sendJsonResponse([
                'success' => false,
                'error'   => 'Enclavamiento activo: La estación está en Parada de Emergencia ESD-1. Maniobra denegada.'
            ], 403);
        }

        // Caso 1: Actualizar apertura de Choke
        if (isset($input['chokeOpening'])) {
            $newChoke = (float)$input['chokeOpening'];
            $oldChoke = (float)$well['apertura_choke'];

            $ok = $this->pozoModel->setChoke($wellId, $newChoke);
            if ($ok) {
                $this->auditoriaModel->log(
                    $operatorCode,
                    $operatorName,
                    'Operador / Ingeniero',
                    "Ajuste Estrangulador Choke en {$well['nombre']}",
                    'MANIOBRA',
                    'EXITOSO',
                    sprintf("Apertura ajustada de %.1f%% a %.1f%%", $oldChoke, $newChoke)
                );
                sendJsonResponse([
                    'success' => true,
                    'message' => "Choke de {$well['nombre']} ajustado a {$newChoke}%",
                    'well'    => $this->pozoModel->getById($wellId)
                ]);
            }
        }

        // Caso 2: Conmutar Válvula de Seguridad SSV
        if (isset($input['ssvOpen'])) {
            $ssvOpen = (bool)$input['ssvOpen'];
            $ok = $this->pozoModel->setSsvValve($wellId, $ssvOpen);
            if ($ok) {
                $actionTxt = $ssvOpen ? 'APERTURA de Válvula SSV' : 'CIERRE de Válvula SSV';
                $this->auditoriaModel->log(
                    $operatorCode,
                    $operatorName,
                    'Supervisor / Operador',
                    "{$actionTxt} en {$well['nombre']}",
                    'MANIOBRA',
                    'EXITOSO',
                    "Estado de válvula SSV conmutado a: " . ($ssvOpen ? 'ABIERTA' : 'CERRADA')
                );
                sendJsonResponse([
                    'success' => true,
                    'message' => "Válvula SSV de {$well['nombre']} conmutada a " . ($ssvOpen ? 'ABIERTA' : 'CERRADA'),
                    'well'    => $this->pozoModel->getById($wellId)
                ]);
            }
        }

        // Caso 3: Rearme de pozo disparado
        if (isset($input['action']) && $input['action'] === 'reset') {
            $ok = $this->pozoModel->resetWellhead($wellId);
            if ($ok) {
                $this->auditoriaModel->log(
                    $operatorCode,
                    $operatorName,
                    'Supervisor de Turno',
                    "Rearme de Cabezal {$well['nombre']}",
                    'SEGURIDAD',
                    'EXITOSO',
                    "Pozo rearmado a estado 'EN LÍNEA' con estrangulador en 15% seguro."
                );
                sendJsonResponse([
                    'success' => true,
                    'message' => "Pozo {$well['nombre']} rearmado exitosamente a estado EN LÍNEA",
                    'well'    => $this->pozoModel->getById($wellId)
                ]);
            }
        }

        sendJsonResponse(['success' => false, 'error' => 'No se especificó ninguna acción válida'], 400);
    }
}

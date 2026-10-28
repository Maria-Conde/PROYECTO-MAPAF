<?php
/**
 * PROYECTO MAPAF - Capa de Servicio: EsdService
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Reglas de negocio del Sistema Instrumentado de Seguridad (SIS / ESD-1 / IEC 61511):
 * - Aislamiento atómico de cabezales en parada de emergencia.
 * - Secuencia de rearme seguro liberando enclavamientos.
 * - Trazabilidad forense inmutable mediante Patrón Observer.
 */

require_once __DIR__ . '/EventDispatcher.php';
require_once __DIR__ . '/AuditObserver.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/../domain/ValueObjects.php';
require_once __DIR__ . '/../repositories/EsdRepository.php';
require_once __DIR__ . '/../repositories/WellheadRepository.php';

class EsdService {
    private EsdRepository $esdRepo;
    private WellheadRepository $wellheadRepo;
    private AuthService $authService;

    public function __construct(
        ?EsdRepository $esdRepo = null,
        ?WellheadRepository $wellheadRepo = null,
        ?AuthService $authService = null
    ) {
        $this->esdRepo = $esdRepo ?: new EsdRepository();
        $this->wellheadRepo = $wellheadRepo ?: new WellheadRepository();
        $this->authService = $authService ?: new AuthService();
    }

    public function getSystemState(): array {
        return $this->esdRepo->getSystemState();
    }

    public function isTriggered(): bool {
        return $this->esdRepo->isTriggered();
    }

    /**
     * Dispara la Parada de Emergencia ESD-1 y aísla la estación completa
     */
    public function triggerEsd(string $reason, ?OperatorIdentity $operator = null): array {
        $op = $this->authService->requirePermission('execute_esd', $operator);

        $timeStr = date('H:i:s');
        $reasonClean = trim($reason) ?: 'Disparo manual preventivo desde consola SCADA';

        // 1. Activar bandera de ESD en el repositorio
        $this->esdRepo->setTriggered($timeStr);

        // 2. Aislar inmediatamente todos los pozos (cierre SSV y chokes a 0%)
        $this->wellheadRepo->isolateAllWells();

        // 3. Emitir evento de emergencia
        EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
            'DISPARO DE EMERGENCIA ESD-1',
            'SEGURIDAD',
            $op->getCode(),
            $op->getName(),
            $op->getRoleCode(),
            'ADVERTENCIA',
            "Parada de emergencia ejecutada. Razón: '{$reasonClean}'. Cabezales aislados bajo norma IEC 61511."
        ));

        return [
            'success' => true,
            'message' => '¡PARADA DE EMERGENCIA ESD-1 ACTIVADA! Todos los pozos han sido aislados.',
            'state'   => $this->esdRepo->getSystemState()
        ];
    }

    /**
     * Rearma el sistema ESD-1 tras verificar condiciones de seguridad
     */
    public function resetEsd(?OperatorIdentity $operator = null): array {
        $op = $this->authService->requirePermission('reset_esd', $operator);

        // 1. Liberar bandera de parada en el sistema
        $this->esdRepo->setReset();

        // 2. Restablecer pozos disparados a estado EN LÍNEA con choke seguro al 15%
        $this->wellheadRepo->resetAllWellsNominal();

        // 3. Emitir evento de rearme
        EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
            'REARME DE SEGURIDAD ESD-1',
            'SEGURIDAD',
            $op->getCode(),
            $op->getName(),
            $op->getRoleCode(),
            'EXITOSO',
            "Rearme completado con éxito. Estado de enclavamiento liberado y consignas en nivel seguro (15%)."
        ));

        return [
            'success' => true,
            'message' => 'Sistema ESD-1 rearmado exitosamente. Planta en estado nominal seguro.',
            'state'   => $this->esdRepo->getSystemState()
        ];
    }
}

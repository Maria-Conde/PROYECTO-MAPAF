<?php
/**
 * PROYECTO MAPAF - Capa de Servicio: WellheadService
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Reglas de negocio para maniobras en cabezales de pozo (API 6A):
 * - Enclavamiento de seguridad SIS: Bloqueo de apertura si ESD-1 está activo.
 * - Validación estricta con Objeto de Valor ChokeOpening (0.0% a 100.0%).
 * - Despacho de eventos de auditoría inmutable vía Patrón Observer.
 */

require_once __DIR__ . '/EventDispatcher.php';
require_once __DIR__ . '/AuditObserver.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/../domain/ValueObjects.php';
require_once __DIR__ . '/../repositories/WellheadRepository.php';
require_once __DIR__ . '/../repositories/EsdRepository.php';

class WellheadService {
    private WellheadRepository $wellheadRepo;
    private EsdRepository $esdRepo;
    private AuthService $authService;

    public function __construct(
        ?WellheadRepository $wellheadRepo = null,
        ?EsdRepository $esdRepo = null,
        ?AuthService $authService = null
    ) {
        $this->wellheadRepo = $wellheadRepo ?: new WellheadRepository();
        $this->esdRepo = $esdRepo ?: new EsdRepository();
        $this->authService = $authService ?: new AuthService();
    }

    public function getAllWells(): array {
        return $this->wellheadRepo->getAll();
    }

    public function getWellById(string $id): ?array {
        return $this->wellheadRepo->getById($id);
    }

    /**
     * Ajusta la apertura del estrangulador (Choke)
     */
    public function adjustChoke(string $wellId, float $rawChoke, ?OperatorIdentity $operator = null): array {
        $op = $this->authService->requirePermission('control_choke', $operator);

        // 1. Validar invariante mediante Value Object ChokeOpening
        $choke = new ChokeOpening($rawChoke);

        // 2. Comprobar enclavamiento de seguridad con ESD-1
        if ($this->esdRepo->isTriggered() && !$choke->isClosed()) {
            EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
                "Apertura de Choke Bloqueada en Pozo {$wellId}",
                'SEGURIDAD',
                $op->getCode(),
                $op->getName(),
                $op->getRoleCode(),
                'RECHAZADO',
                "Enclavamiento activo: La planta se encuentra en Parada de Emergencia ESD-1."
            ));
            throw new ValidationException('Enclavamiento SIS activo: La planta está en Parada de Emergencia ESD-1. Maniobra denegada.');
        }

        $well = $this->wellheadRepo->getById($wellId);
        if (!$well) {
            throw new ValidationException("Cabezal de pozo '{$wellId}' no encontrado.");
        }

        $oldChoke = (float)$well['apertura_choke'];
        $ok = $this->wellheadRepo->setChoke($wellId, $choke->getValue());

        if (!$ok) {
            throw new DomainException("Error al actualizar la consigna de estrangulador en el pozo {$wellId}.");
        }

        // 3. Emitir evento de dominio para trazabilidad
        EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
            "Ajuste Estrangulador Choke en {$well['nombre']}",
            'MANIOBRA',
            $op->getCode(),
            $op->getName(),
            $op->getRoleCode(),
            'EXITOSO',
            sprintf("Apertura ajustada de %.1f%% a %.1f%%", $oldChoke, $choke->getValue())
        ));

        return [
            'success' => true,
            'message' => "Choke de {$well['nombre']} ajustado a {$choke->getValue()}%",
            'well'    => $this->wellheadRepo->getById($wellId)
        ];
    }

    /**
     * Conmuta la Válvula de Seguridad de Superficie (SSV)
     */
    public function toggleSsvValve(string $wellId, bool $isOpen, ?OperatorIdentity $operator = null): array {
        $op = $this->authService->requirePermission('control_valves', $operator);

        // Si la planta está en ESD-1, no permitir abrir válvulas SSV
        if ($this->esdRepo->isTriggered() && $isOpen) {
            EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
                "Apertura de Válvula SSV Bloqueada en Pozo {$wellId}",
                'SEGURIDAD',
                $op->getCode(),
                $op->getName(),
                $op->getRoleCode(),
                'RECHAZADO',
                "Enclavamiento activo: La planta se encuentra en Parada de Emergencia ESD-1."
            ));
            throw new ValidationException('Enclavamiento SIS activo: No se permite abrir válvulas SSV durante Parada de Emergencia ESD-1.');
        }

        $well = $this->wellheadRepo->getById($wellId);
        if (!$well) {
            throw new ValidationException("Cabezal de pozo '{$wellId}' no encontrado.");
        }

        $ok = $this->wellheadRepo->setSsvValve($wellId, $isOpen);
        if (!$ok) {
            throw new DomainException("Error al conmutar válvula SSV de {$well['nombre']}.");
        }

        $actionTxt = $isOpen ? 'APERTURA de Válvula SSV' : 'CIERRE de Válvula SSV';
        EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
            "{$actionTxt} en {$well['nombre']}",
            'MANIOBRA',
            $op->getCode(),
            $op->getName(),
            $op->getRoleCode(),
            'EXITOSO',
            "Estado de válvula SSV conmutado a: " . ($isOpen ? 'ABIERTA' : 'CERRADA')
        ));

        return [
            'success' => true,
            'message' => "Válvula SSV de {$well['nombre']} conmutada a " . ($isOpen ? 'ABIERTA' : 'CERRADA'),
            'well'    => $this->wellheadRepo->getById($wellId)
        ];
    }

    /**
     * Rearma de forma segura un pozo disparado
     */
    public function resetWell(string $wellId, ?OperatorIdentity $operator = null): array {
        $op = $this->authService->requirePermission('control_valves', $operator);

        if ($this->esdRepo->isTriggered()) {
            throw new ValidationException('No se puede rearmar un pozo mientras la Parada de Emergencia ESD-1 global esté activa.');
        }

        $well = $this->wellheadRepo->getById($wellId);
        if (!$well) {
            throw new ValidationException("Cabezal de pozo '{$wellId}' no encontrado.");
        }

        $ok = $this->wellheadRepo->resetWellhead($wellId);
        if (!$ok) {
            throw new DomainException("Error al rearmar pozo {$well['nombre']}.");
        }

        EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
            "Rearme de Cabezal {$well['nombre']}",
            'SEGURIDAD',
            $op->getCode(),
            $op->getName(),
            $op->getRoleCode(),
            'EXITOSO',
            "Pozo rearmado a estado 'EN LÍNEA' con estrangulador en 15.0% seguro."
        ));

        return [
            'success' => true,
            'message' => "Pozo {$well['nombre']} rearmado exitosamente a estado EN LÍNEA",
            'well'    => $this->wellheadRepo->getById($wellId)
        ];
    }
}

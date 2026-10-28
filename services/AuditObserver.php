<?php
/**
 * PROYECTO MAPAF - Patrón OBSERVER: Observador de Auditoría Forense
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Escucha los eventos de dominio (maniobras, alarmas, disparos SIS, turnos)
 * y delega su registro inmutable en el AuditRepository.
 */

require_once __DIR__ . '/EventDispatcher.php';
require_once __DIR__ . '/../repositories/AuditRepository.php';

class AuditObserver implements EventObserverInterface {
    private AuditRepository $auditRepo;

    public function __construct(?AuditRepository $auditRepo = null) {
        $this->auditRepo = $auditRepo ?: new AuditRepository();
    }

    public function onDomainEvent(DomainEventInterface $event): void {
        $this->auditRepo->log(
            $event->getUserCode(),
            $event->getUserName(),
            $event->getRoleName(),
            $event->getName(),
            $event->getCategory(),
            $event->getStatus(),
            $event->getDetails()
        );
    }
}

// Registrar automáticamente el observador global de auditoría
EventDispatcher::getInstance()->subscribe(new AuditObserver());

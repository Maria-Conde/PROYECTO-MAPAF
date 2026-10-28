<?php
/**
 * PROYECTO MAPAF - Patrón de Diseño OBSERVER (Publicador / Suscriptor de Eventos)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Desacopla la lógica de negocio de cabezales, alarmas y SIS respecto a
 * la persistencia de auditoría, trazabilidad y futuras notificaciones.
 */

interface DomainEventInterface {
    public function getName(): string;
    public function getCategory(): string;
    public function getUserCode(): string;
    public function getUserName(): string;
    public function getRoleName(): string;
    public function getStatus(): string;
    public function getDetails(): string;
    public function getTimestamp(): string;
}

interface EventObserverInterface {
    public function onDomainEvent(DomainEventInterface $event): void;
}

class GenericDomainEvent implements DomainEventInterface {
    private string $name;
    private string $category;
    private string $userCode;
    private string $userName;
    private string $roleName;
    private string $status;
    private string $details;
    private string $timestamp;

    public function __construct(
        string $name,
        string $category,
        string $userCode,
        string $userName,
        string $roleName,
        string $status,
        string $details
    ) {
        $this->name = $name;
        $this->category = $category;
        $this->userCode = $userCode;
        $this->userName = $userName;
        $this->roleName = $roleName;
        $this->status = $status;
        $this->details = $details;
        $this->timestamp = date('H:i:s');
    }

    public function getName(): string { return $this->name; }
    public function getCategory(): string { return $this->category; }
    public function getUserCode(): string { return $this->userCode; }
    public function getUserName(): string { return $this->userName; }
    public function getRoleName(): string { return $this->roleName; }
    public function getStatus(): string { return $this->status; }
    public function getDetails(): string { return $this->details; }
    public function getTimestamp(): string { return $this->timestamp; }
}

class EventDispatcher {
    private static ?EventDispatcher $instance = null;
    /** @var EventObserverInterface[] */
    private array $observers = [];

    private function __construct() {}

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function subscribe(EventObserverInterface $observer): void {
        $this->observers[] = $observer;
    }

    public function dispatch(DomainEventInterface $event): void {
        foreach ($this->observers as $observer) {
            try {
                $observer->onDomainEvent($event);
            } catch (Throwable $e) {
                error_log('[EventDispatcher] Error en observador: ' . $e->getMessage());
            }
        }
    }
}

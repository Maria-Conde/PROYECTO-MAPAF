<?php
/**
 * PROYECTO MAPAF - Patrón de Comportamiento: OBSERVER (Observador)
 * SENA ADSO · Ficha 3413988
 * 
 * Implementa el patrón observador para telemetría industrial reactiva.
 * Cuando un sensor (Subject) emite una nueva lectura o cruza un umbral crítico,
 * todos los observadores suscritos (Alarma ISA-18.2, Logger de Auditoría, Enclavamiento ESD, Notificador)
 * reaccionan automáticamente de manera desacoplada sin que el sensor conozca a sus consumidores.
 */

namespace Mapaf\Patterns\Observer;

class TelemetryEvent {
    public string $tag;
    public string $sensorType;
    public float $value;
    public float $previousValue;
    public string $unit;
    public string $severity;
    public string $timestamp;
    public array $metadata;

    public function __construct(string $tag, string $sensorType, float $value, float $previousValue, string $unit, string $severity, array $metadata = []) {
        $this->tag = $tag;
        $this->sensorType = $sensorType;
        $this->value = $value;
        $this->previousValue = $previousValue;
        $this->unit = $unit;
        $this->severity = $severity;
        $this->timestamp = date('H:i:s');
        $this->metadata = $metadata;
    }
}

interface TelemetryObserverInterface {
    public function getName(): string;
    public function update(TelemetryEvent $event): array;
}

class TelemetrySubject {
    /** @var TelemetryObserverInterface[] */
    private array $observers = [];
    private array $eventHistory = [];

    public function attach(TelemetryObserverInterface $observer): void {
        $key = spl_object_hash($observer);
        $this->observers[$key] = $observer;
    }

    public function detach(TelemetryObserverInterface $observer): void {
        $key = spl_object_hash($observer);
        unset($this->observers[$key]);
    }

    public function notify(TelemetryEvent $event): array {
        $results = [];
        $this->eventHistory[] = [
            'tag' => $event->tag,
            'value' => $event->value,
            'severity' => $event->severity,
            'time' => $event->timestamp
        ];

        foreach ($this->observers as $observer) {
            $results[$observer->getName()] = $observer->update($event);
        }

        return $results;
    }

    public function getObserversCount(): int {
        return count($this->observers);
    }
}

/**
 * Observador 1: Disparador de Alarmas ISA-18.2
 */
class AlarmTriggerObserver implements TelemetryObserverInterface {
    public function getName(): string { return 'AlarmTriggerObserver'; }

    public function update(TelemetryEvent $event): array {
        if ($event->severity === 'critical' || $event->severity === 'warning') {
            return [
                'action' => 'ALARM_GENERATED',
                'alarmId' => 'alm-obs-' . rand(100, 999),
                'tag' => $event->tag,
                'severity' => $event->severity,
                'message' => "Alarma disparada por lectura de {$event->value} {$event->unit} en {$event->tag}",
                'isaStandard' => 'ISA-18.2'
            ];
        }
        return ['action' => 'NO_ACTION', 'status' => 'NOMINAL'];
    }
}

/**
 * Observador 2: Bitácora de Auditoría y Trazabilidad SCADA
 */
class AuditLoggerObserver implements TelemetryObserverInterface {
    public function getName(): string { return 'AuditLoggerObserver'; }

    public function update(TelemetryEvent $event): array {
        if ($event->severity === 'critical' || abs($event->value - $event->previousValue) > 100) {
            return [
                'action' => 'AUDIT_LOG_WRITTEN',
                'entry' => [
                    'category' => 'TELEMETRÍA',
                    'tag' => $event->tag,
                    'delta' => round($event->value - $event->previousValue, 2),
                    'timestamp' => $event->timestamp,
                    'message' => "Transgresión registrada en bitácora para el tag {$event->tag} ({$event->value} {$event->unit})"
                ]
            ];
        }
        return ['action' => 'NO_ACTION', 'status' => 'FILTERED'];
    }
}

/**
 * Observador 3: Enclavamiento de Seguridad y Parada de Emergencia (ESD Safety Interlock)
 */
class SafetyInterlockObserver implements TelemetryObserverInterface {
    public function getName(): string { return 'SafetyInterlockObserver'; }

    public function update(TelemetryEvent $event): array {
        // En cabezal de pozo WHTP, si la presión excede 2400 PSI High-High, activa enclavamiento
        if ($event->severity === 'critical' && ($event->tag === 'WHTP-101A' || $event->tag === 'CASING-101B')) {
            return [
                'action' => 'SAFETY_INTERLOCK_TRIP',
                'target' => 'ESD-1',
                'cause' => "Sobrepresión Crítica High-High ({$event->value} {$event->unit}) en {$event->tag}",
                'valvesToClose' => ['SSV-101', 'WING-101', 'CHOKE-101'],
                'safeState' => 'ISOLATED'
            ];
        }
        return ['action' => 'NO_ACTION', 'status' => 'INTERLOCK_HEALTHY'];
    }
}

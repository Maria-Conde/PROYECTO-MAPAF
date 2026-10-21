<?php
/**
 * PROYECTO MAPAF - Patrón de Comportamiento: STATE (Estado)
 * SENA ADSO · Ficha 3413988
 * 
 * Modela el ciclo de vida operacional de un cabezal de pozo petrolero (Wellhead Unit)
 * de acuerdo con la norma de seguridad API 6A y las normas de enclavamiento SCADA:
 * Estados:
 * 1. Cerrado / Shut-In (Válvulas SSV cerradas, pozo estático)
 * 2. En Producción Nominal (SSV abierta, estrangulador regulado)
 * 3. Estrangulado / Throttled (Choke restringido por contrapresión)
 * 4. Disparado / Tripped (Parada de seguridad por sobrepresión o ESD)
 * 5. Mantenimiento (Bloqueo y etiquetado LOTO)
 */

namespace Mapaf\Patterns\State;

interface WellheadStateInterface {
    public function getStateName(): string;
    public function getColor(): string;
    public function canProduce(): bool;
    public function openSsv(WellheadContext $context): array;
    public function closeSsv(WellheadContext $context): array;
    public function adjustChoke(WellheadContext $context, float $percent): array;
    public function trip(WellheadContext $context, string $cause): array;
    public function reset(WellheadContext $context): array;
}

class WellheadContext {
    private string $wellId;
    private WellheadStateInterface $state;
    private float $chokeOpening = 42.8;
    private bool $ssvOpen = true;
    private array $transitionHistory = [];

    public function __construct(string $wellId, $initialState = null) {
        $this->wellId = $wellId;
        if (is_string($initialState)) {
            $upper = strtoupper(trim($initialState));
            if (str_contains($upper, 'DISPAR') || str_contains($upper, 'TRIP')) {
                $this->state = new TrippedState();
                $this->ssvOpen = false;
            } elseif (str_contains($upper, 'CERR') || str_contains($upper, 'SHUT')) {
                $this->state = new ShutInState();
                $this->ssvOpen = false;
            } elseif (str_contains($upper, 'ESTRANG')) {
                $this->state = new ChokedState();
            } else {
                $this->state = new ProducingState();
            }
        } elseif ($initialState instanceof WellheadStateInterface) {
            $this->state = $initialState;
        } else {
            $this->state = new ProducingState();
        }
        $this->recordTransition('INIT', $this->state->getStateName(), 'Inicialización de pozo');
    }

    public function setState(WellheadStateInterface $newState, string $reason = ''): void {
        $old = $this->state->getStateName();
        $this->state = $newState;
        $this->recordTransition($old, $newState->getStateName(), $reason);
    }

    public function getState(): WellheadStateInterface { return $this->state; }
    public function getWellId(): string { return $this->wellId; }
    public function getChoke(): float { return $this->chokeOpening; }
    public function setChoke(float $c): void { $this->chokeOpening = $c; }
    public function isSsvOpen(): bool { return $this->ssvOpen; }
    public function setSsvOpen(bool $open): void { $this->ssvOpen = $open; }

    private function recordTransition(string $from, string $to, string $reason): void {
        $this->transitionHistory[] = [
            'timestamp' => date('H:i:s'),
            'from' => $from,
            'to' => $to,
            'reason' => $reason
        ];
    }

    public function openSsv(): array { return $this->state->openSsv($this); }
    public function closeSsv(): array { return $this->state->closeSsv($this); }
    public function adjustChoke(float $percent): array { return $this->state->adjustChoke($this, $percent); }
    public function trip(string $cause): array { return $this->state->trip($this, $cause); }
    public function reset(): array { return $this->state->reset($this); }

    public function toArray(): array {
        return [
            'wellId' => $this->wellId,
            'state' => $this->state->getStateName(),
            'color' => $this->state->getColor(),
            'canProduce' => $this->state->canProduce(),
            'choke' => $this->chokeOpening,
            'ssvOpen' => $this->ssvOpen,
            'history' => array_reverse($this->transitionHistory)
        ];
    }
}

class ProducingState implements WellheadStateInterface {
    public function getStateName(): string { return 'EN LÍNEA / PRODUCIENDO'; }
    public function getColor(): string { return '#10b981'; }
    public function canProduce(): bool { return true; }

    public function openSsv(WellheadContext $context): array {
        return ['success' => false, 'message' => 'La válvula SSV ya se encuentra abierta y en flujo.'];
    }

    public function closeSsv(WellheadContext $context): array {
        $context->setSsvOpen(false);
        $context->setState(new ShutInState(), 'Cierre preventivo de válvula maestra SSV');
        return ['success' => true, 'message' => 'Válvula SSV cerrada. El pozo pasa a estado CERRADO / SHUT-IN.'];
    }

    public function adjustChoke(WellheadContext $context, float $percent): array {
        $context->setChoke($percent);
        if ($percent < 25.0) {
            $context->setState(new ChokedState(), "Apertura reducida a {$percent}%");
            return ['success' => true, 'message' => "Choke estrangulado a {$percent}%. Estado actualizado a ESTRANGULADO."];
        }
        return ['success' => true, 'message' => "Choke regulado a {$percent}% en estado de producción nominal."];
    }

    public function trip(WellheadContext $context, string $cause): array {
        $context->setSsvOpen(false);
        $context->setState(new TrippedState(), $cause);
        return ['success' => true, 'action' => 'TRIPPED', 'message' => "¡DISPARO DE SEGURIDAD! Pozo aislado por: {$cause}"];
    }

    public function reset(WellheadContext $context): array {
        return ['success' => false, 'message' => 'El pozo ya se encuentra en producción normal.'];
    }
}

class ChokedState implements WellheadStateInterface {
    public function getStateName(): string { return 'ESTRANGULADO'; }
    public function getColor(): string { return '#f59e0b'; }
    public function canProduce(): bool { return true; }

    public function openSsv(WellheadContext $context): array {
        return ['success' => false, 'message' => 'SSV ya está abierta.'];
    }

    public function closeSsv(WellheadContext $context): array {
        $context->setSsvOpen(false);
        $context->setState(new ShutInState(), 'Cierre manual desde modo estrangulado');
        return ['success' => true, 'message' => 'Pozo cerrado.'];
    }

    public function adjustChoke(WellheadContext $context, float $percent): array {
        $context->setChoke($percent);
        if ($percent >= 25.0) {
            $context->setState(new ProducingState(), "Apertura restablecida a {$percent}%");
            return ['success' => true, 'message' => 'Flujo normal restablecido. Estado: EN LÍNEA.'];
        }
        return ['success' => true, 'message' => "Choke ajustado a {$percent}% manteniendo estrangulamiento."];
    }

    public function trip(WellheadContext $context, string $cause): array {
        $context->setSsvOpen(false);
        $context->setState(new TrippedState(), $cause);
        return ['success' => true, 'message' => "Disparo de seguridad: {$cause}"];
    }

    public function reset(WellheadContext $context): array {
        return ['success' => false, 'message' => 'Ajuste el estrangulador a >= 25% para salir de estrangulado.'];
    }
}

class TrippedState implements WellheadStateInterface {
    public function getStateName(): string { return 'DISPARADO / TRIPPED'; }
    public function getColor(): string { return '#f43f5e'; }
    public function canProduce(): bool { return false; }

    public function openSsv(WellheadContext $context): array {
        return ['success' => false, 'error' => 'ENCLAVAMIENTO ACTIVO: No se puede abrir SSV en estado DISPARADO sin rearme previo.'];
    }

    public function closeSsv(WellheadContext $context): array {
        return ['success' => false, 'message' => 'Las válvulas ya se encuentran cerradas por seguridad.'];
    }

    public function adjustChoke(WellheadContext $context, float $percent): array {
        return ['success' => false, 'error' => 'Ajuste de choke bloqueado mientras el pozo esté disparado.'];
    }

    public function trip(WellheadContext $context, string $cause): array {
        return ['success' => false, 'message' => 'El pozo ya está en condición de disparo.'];
    }

    public function reset(WellheadContext $context): array {
        $context->setState(new ShutInState(), 'Protocolo de rearme de seguridad completado');
        return ['success' => true, 'message' => 'Enclavamiento liberado. El pozo pasa a CERRADO / SHUT-IN listo para arranque controlado.'];
    }
}

class ShutInState implements WellheadStateInterface {
    public function getStateName(): string { return 'CERRADO / SHUT-IN'; }
    public function getColor(): string { return '#64748b'; }
    public function canProduce(): bool { return false; }

    public function openSsv(WellheadContext $context): array {
        $context->setSsvOpen(true);
        $context->setState(new ProducingState(), 'Arranque de pozo con apertura de válvula maestra SSV');
        return ['success' => true, 'message' => 'Válvula SSV abierta. El pozo entra en producción.'];
    }

    public function closeSsv(WellheadContext $context): array {
        return ['success' => false, 'message' => 'La válvula ya está cerrada.'];
    }

    public function adjustChoke(WellheadContext $context, float $percent): array {
        $context->setChoke($percent);
        return ['success' => true, 'message' => "Posición objetivo de Choke pre-configurada a {$percent}% para próximo arranque."];
    }

    public function trip(WellheadContext $context, string $cause): array {
        $context->setState(new TrippedState(), $cause);
        return ['success' => true, 'message' => "Pozo cerrado bloqueado por alarma: {$cause}"];
    }

    public function reset(WellheadContext $context): array {
        return ['success' => false, 'message' => 'El pozo no está disparado. Presione abrir SSV para arrancar.'];
    }
}

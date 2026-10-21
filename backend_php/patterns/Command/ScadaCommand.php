<?php
/**
 * PROYECTO MAPAF - Patrón de Comportamiento: COMMAND (Comando)
 * SENA ADSO · Ficha 3413988
 * 
 * Encapsula cada maniobra operativa SCADA (regulación de estrangulador Choke, apertura/cierre
 * de válvula de seguridad SSV, reconocimiento de alarma y parada ESD) como un objeto autónomo.
 * Proporciona:
 * 1. Ejecución controlada con verificación de enclavamiento.
 * 2. Capacidad de Reversión / Deshacer (UNDO) a un estado seguro anterior.
 * 3. Trazabilidad completa en la bitácora de auditoría.
 */

namespace Mapaf\Patterns\Command;

interface ScadaCommandInterface {
    public function execute(): array;
    public function undo(): array;
    public function getId(): string;
    public function getDescription(): string;
    public function getOperator(): string;
    public function getTimestamp(): string;
    public function getAuditLog(): array;
}

abstract class BaseScadaCommand implements ScadaCommandInterface {
    protected string $id;
    protected string $operator;
    protected string $timestamp;
    protected bool $executed = false;
    protected bool $undone = false;

    public function __construct(string $operator = 'M. Conde Dussan') {
        $this->id = 'cmd-' . substr(strval(time()), -5) . rand(10, 99);
        $this->operator = $operator;
        $this->timestamp = date('H:i:s');
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return (new \ReflectionClass($this))->getShortName(); }
    public function getOperator(): string { return $this->operator; }
    public function getTimestamp(): string { return $this->timestamp; }
}

/**
 * Comando 1: Regulación de Estrangulador Choke (API 6A) con Undo
 */
class SetChokeCommand extends BaseScadaCommand {
    private string $wellId;
    private float $previousOpening;
    private float $targetOpening;
    private ?object $wellContext;

    public function __construct(string $wellId, float $previousOpening, float $targetOpening, string $operator = 'M. Conde Dussan', ?object $wellContext = null) {
        parent::__construct($operator);
        $this->wellId = $wellId;
        $this->previousOpening = $previousOpening;
        $this->targetOpening = $targetOpening;
        $this->wellContext = $wellContext;
    }

    public function execute(): array {
        $this->executed = true;
        $this->undone = false;
        return [
            'success' => true,
            'commandId' => $this->id,
            'action' => 'CHOKE_ADJUSTED',
            'wellId' => $this->wellId,
            'from' => $this->previousOpening,
            'to' => $this->targetOpening,
            'message' => "Estrangulador de {$this->wellId} ajustado a {$this->targetOpening}% (anterior: {$this->previousOpening}%)"
        ];
    }

    public function undo(): array {
        if (!$this->executed || $this->undone) {
            return ['success' => false, 'error' => 'El comando no puede ser deshecho en este estado.'];
        }
        $this->undone = true;
        return [
            'success' => true,
            'commandId' => $this->id,
            'action' => 'CHOKE_RESTORED_UNDO',
            'wellId' => $this->wellId,
            'restoredValue' => $this->previousOpening,
            'message' => "Deshacer maniobra: Estrangulador de {$this->wellId} restaurado a {$this->previousOpening}%"
        ];
    }

    public function getDescription(): string {
        return "Ajuste de Choke {$this->wellId}: {$this->previousOpening}% ➔ {$this->targetOpening}%";
    }

    public function getAuditLog(): array {
        return [
            'user' => $this->operator,
            'category' => 'MANIOBRA',
            'action' => 'Ajuste de Estrangulador',
            'details' => "Pozo {$this->wellId} regulado a {$this->targetOpening}% por {$this->operator}"
        ];
    }
}

/**
 * Comando 2: Conmutación de Válvula de Seguridad SSV (Superficie)
 */
class ToggleSsvValveCommand extends BaseScadaCommand {
    private string $wellId;
    private bool $previousState;
    private bool $targetState;

    public function __construct(string $wellId, bool $previousState, bool $targetState, string $operator = 'M. Conde Dussan') {
        parent::__construct($operator);
        $this->wellId = $wellId;
        $this->previousState = $previousState;
        $this->targetState = $targetState;
    }

    public function execute(): array {
        $this->executed = true;
        $stateText = $this->targetState ? 'ABIERTA' : 'CERRADA';
        return [
            'success' => true,
            'commandId' => $this->id,
            'action' => 'SSV_VALVE_TOGGLED',
            'wellId' => $this->wellId,
            'state' => $this->targetState,
            'message' => "Válvula hidráulica SSV de {$this->wellId} conmutada a {$stateText}"
        ];
    }

    public function undo(): array {
        $this->undone = true;
        $stateText = $this->previousState ? 'ABIERTA' : 'CERRADA';
        return [
            'success' => true,
            'commandId' => $this->id,
            'action' => 'SSV_VALVE_UNDO',
            'wellId' => $this->wellId,
            'state' => $this->previousState,
            'message' => "Reversión de seguridad: Válvula SSV de {$this->wellId} retornada a {$stateText}"
        ];
    }

    public function getDescription(): string {
        $t1 = $this->previousState ? 'ABIERTA' : 'CERRADA';
        $t2 = $this->targetState ? 'ABIERTA' : 'CERRADA';
        return "Válvula SSV {$this->wellId}: {$t1} ➔ {$t2}";
    }

    public function getAuditLog(): array {
        return [
            'user' => $this->operator,
            'category' => 'SEGURIDAD',
            'action' => 'Conmutación de Válvula SSV',
            'details' => "Pozo {$this->wellId} SSV cambiado a " . ($this->targetState ? 'Abierto' : 'Cerrado')
        ];
    }
}

/**
 * Comando 3: Parada de Emergencia ESD-1 y Rearme
 */
class EmergencyShutdownCommand extends BaseScadaCommand {
    private string $esdLevel;

    public function __construct(string $esdLevel = 'ESD-1 (Parada Total de Planta)', string $operator = 'M. Conde Dussan') {
        parent::__construct($operator);
        $this->esdLevel = $esdLevel;
    }

    public function execute(): array {
        $this->executed = true;
        return [
            'success' => true,
            'commandId' => $this->id,
            'action' => 'ESD_TRIGGERED',
            'level' => $this->esdLevel,
            'message' => "PARADA DE EMERGENCIA {$this->esdLevel} EJECUTADA. Todos los pozos cerrados y enclavados."
        ];
    }

    public function undo(): array {
        $this->undone = true;
        return [
            'success' => true,
            'commandId' => $this->id,
            'action' => 'ESD_RESET_UNDO',
            'message' => "Rearme de Seguridad: Enclavamiento ESD-1 liberado bajo protocolo operacional."
        ];
    }

    public function getDescription(): string {
        return "Disparo de Parada de Emergencia: {$this->esdLevel}";
    }

    public function getAuditLog(): array {
        return [
            'user' => $this->operator,
            'category' => 'SEGURIDAD CRÍTICA',
            'action' => 'Disparo ESD-1',
            'details' => "Parada general de planta activada por {$this->operator}"
        ];
    }
}

/**
 * Invoker / Gestor de Comandos (Command Manager con Historial y Deshacer)
 */
class CommandManager {
    /** @var ScadaCommandInterface[] */
    private array $history = [];
    /** @var ScadaCommandInterface[] */
    private array $undoneHistory = [];

    public function executeCommand(ScadaCommandInterface $command): array {
        $result = $command->execute();
        $this->history[] = $command;
        return $result;
    }

    public function undoLastCommand(): array {
        if (empty($this->history)) {
            return [
                'success' => false,
                'error' => 'No hay maniobras previas en el historial para deshacer.'
            ];
        }

        $command = array_pop($this->history);
        $result = $command->undo();
        $this->undoneHistory[] = $command;
        return $result;
    }

    public function getHistorySummary(): array {
        return array_map(function(ScadaCommandInterface $cmd) {
            return [
                'id' => $cmd->getId(),
                'description' => $cmd->getDescription(),
                'operator' => $cmd->getOperator(),
                'timestamp' => $cmd->getTimestamp(),
                'audit' => $cmd->getAuditLog()
            ];
        }, array_reverse($this->history));
    }

    public function getCount(): int {
        return count($this->history);
    }
}

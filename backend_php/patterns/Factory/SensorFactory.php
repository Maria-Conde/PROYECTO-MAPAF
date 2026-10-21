<?php
/**
 * PROYECTO MAPAF - Patrón Creacional: FACTORY METHOD
 * SENA ADSO · Ficha 3413988
 * 
 * Desacopla la creación concreta de instrumentos y sensores de telemetría industrial
 * (Transmisores de Presión PT, Termoresistencias TT, Flujómetros Coriolis FT, VFD de Bomba ESP)
 * aplicando validaciones específicas de ingeniería, rangos de calibración y umbrales ISA-18.2.
 */

namespace Mapaf\Patterns\Factory;

interface SensorInterface {
    public function getTag(): string;
    public function getLabel(): string;
    public function getUnit(): string;
    public function getTargetValue(): float;
    public function getLowTrip(): float;
    public function getHighWarning(): float;
    public function getHighTrip(): float;
    public function validate(float $value): bool;
    public function evaluateSeverity(float $value): string;
    public function toArray(): array;
}

abstract class BaseSensor implements SensorInterface {
    protected string $tag;
    protected string $label;
    protected string $unit;
    protected float $target;
    protected float $lowTrip;
    protected float $highWarning;
    protected float $highTrip;
    protected float $currentValue;

    public function __construct(string $tag, string $label, string $unit, float $target, float $lowTrip, float $highWarning, float $highTrip, float $initialValue = 0) {
        $this->tag = $tag;
        $this->label = $label;
        $this->unit = $unit;
        $this->target = $target;
        $this->lowTrip = $lowTrip;
        $this->highWarning = $highWarning;
        $this->highTrip = $highTrip;
        $this->currentValue = $initialValue;
    }

    public function getTag(): string { return $this->tag; }
    public function getLabel(): string { return $this->label; }
    public function getUnit(): string { return $this->unit; }
    public function getTargetValue(): float { return $this->target; }
    public function getLowTrip(): float { return $this->lowTrip; }
    public function getHighWarning(): float { return $this->highWarning; }
    public function getHighTrip(): float { return $this->highTrip; }

    public function evaluateSeverity(float $value): string {
        if ($value >= $this->highTrip || $value <= $this->lowTrip) {
            return 'critical';
        }
        if ($value >= $this->highWarning) {
            return 'warning';
        }
        if (abs($value - $this->target) > ($this->highWarning - $this->target) * 0.5) {
            return 'advisory';
        }
        return 'nominal';
    }

    public function toArray(): array {
        return [
            'tag' => $this->tag,
            'label' => $this->label,
            'unit' => $this->unit,
            'target' => $this->target,
            'lowTrip' => $this->lowTrip,
            'highWarning' => $this->highWarning,
            'highTrip' => $this->highTrip,
            'currentValue' => $this->currentValue,
            'severity' => $this->evaluateSeverity($this->currentValue),
            'sensorType' => (new \ReflectionClass($this))->getShortName()
        ];
    }
}

class PressureSensor extends BaseSensor {
    public function validate(float $value): bool {
        // Presión en PSI nunca puede ser negativa en cabezal de pozo
        return $value >= 0 && $value <= 5000;
    }
}

class TemperatureSensor extends BaseSensor {
    public function validate(float $value): bool {
        // Rango térmico de proceso de -20°C a 200°C
        return $value >= -20 && $value <= 200;
    }
}

class FlowSensor extends BaseSensor {
    public function validate(float $value): bool {
        // Caudal en BPD (Barriles Por Día) no negativo
        return $value >= 0 && $value <= 30000;
    }
}

class EspFrequencySensor extends BaseSensor {
    public function validate(float $value): bool {
        // Rango de variador de frecuencia ESP en Hz (30 a 70 Hz)
        return $value >= 30.0 && $value <= 70.0;
    }
}

class GorAnalyzerSensor extends BaseSensor {
    public function validate(float $value): bool {
        // Gas-Oil Ratio en SCF/BBL
        return $value >= 0 && $value <= 2000;
    }
}

/**
 * Fábrica principal de Instrumentos (Factory Method)
 */
class SensorFactory {
    public static function create(string $type, string $tag, string $label, ?array $customLimits = null): SensorInterface {
        $type = strtolower(trim($type));

        switch ($type) {
            case 'pressure':
            case 'presion':
            case 'pt':
                $limits = $customLimits ?: ['unit' => 'PSI', 'target' => 1850, 'low' => 600, 'warning' => 2000, 'trip' => 2400];
                return new PressureSensor($tag, $label, $limits['unit'], $limits['target'], $limits['low'], $limits['warning'], $limits['trip'], $limits['target']);

            case 'temperature':
            case 'temperatura':
            case 'tt':
                $limits = $customLimits ?: ['unit' => '°C', 'target' => 64.2, 'low' => 15, 'warning' => 85, 'trip' => 95];
                return new TemperatureSensor($tag, $label, $limits['unit'], $limits['target'], $limits['low'], $limits['warning'], $limits['trip'], $limits['target']);

            case 'flow':
            case 'caudal':
            case 'ft':
                $limits = $customLimits ?: ['unit' => 'BPD', 'target' => 14820, 'low' => 4000, 'warning' => 16500, 'trip' => 18500];
                return new FlowSensor($tag, $label, $limits['unit'], $limits['target'], $limits['low'], $limits['warning'], $limits['trip'], $limits['target']);

            case 'frequency':
            case 'esp':
            case 'vfd':
                $limits = $customLimits ?: ['unit' => 'Hz', 'target' => 58.2, 'low' => 40.0, 'warning' => 63.5, 'trip' => 66.0];
                return new EspFrequencySensor($tag, $label, $limits['unit'], $limits['target'], $limits['low'], $limits['warning'], $limits['trip'], $limits['target']);

            case 'gor':
            case 'gas_oil':
                $limits = $customLimits ?: ['unit' => 'SCF/BBL', 'target' => 380, 'low' => 100, 'warning' => 520, 'trip' => 650];
                return new GorAnalyzerSensor($tag, $label, $limits['unit'], $limits['target'], $limits['low'], $limits['warning'], $limits['trip'], $limits['target']);

            default:
                throw new \InvalidArgumentException("Tipo de sensor desconocido para la fábrica: '{$type}'");
        }
    }
}

/**
 * Fábrica de Alarmas bajo norma ANSI/ISA-18.2
 */
class AlarmFactory {
    public static function createAlarm(
        string $tag,
        string $subsystem,
        string $description,
        float $currentValue,
        float $threshold,
        string $severity,
        string $cause = 'Desviación de Proceso Upstream'
    ): array {
        return [
            'id' => 'alm-' . substr(strval(time()), -5) . rand(10, 99),
            'timestamp' => date('H:i:s'),
            'tag' => $tag,
            'subsystem' => $subsystem,
            'description' => $description,
            'value' => number_format($currentValue, 1),
            'threshold' => number_format($threshold, 1),
            'severity' => $severity,
            'acknowledged' => false,
            'acknowledgedBy' => null,
            'ackTimestamp' => null,
            'tripCause' => $cause
        ];
    }
}

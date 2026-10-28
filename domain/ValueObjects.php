<?php
/**
 * PROYECTO MAPAF - Objetos de Valor y Entidades de Dominio (Domain-Driven Design - DDD)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Modela los conceptos nucleares del proceso industrial upstream:
 * - ChokeOpening: Apertura de estrangulador con invariante estricta (0.0% a 100.0%).
 * - WellheadStatus: Estados operacionales de cabezales bajo norma API 6A.
 * - AlarmSeverity: Severidades de alarma normalizadas ANSI/ISA-18.2 con reglas de acuse.
 * - OperatorIdentity: Identidad validada y matriz de permisos RBAC del operador de turno.
 */

// DomainException es nativa de PHP (SPL). Definimos ValidationException heredando de ella:
class ValidationException extends DomainException {}

/**
 * Value Object: Apertura de Estrangulador de Flujo (Choke Valve - API 6A)
 */
class ChokeOpening {
    private float $value;

    public function __construct(float $value) {
        if ($value < 0.0 || $value > 100.0) {
            throw new ValidationException(sprintf(
                'Consigna de estrangulador inválida: %.2f%%. El rango físico permitido es de 0.0%% a 100.0%%.',
                $value
            ));
        }
        $this->value = round($value, 2);
    }

    public function getValue(): float {
        return $this->value;
    }

    public function isClosed(): bool {
        return $this->value <= 0.01;
    }

    public function isFullyOpen(): bool {
        return $this->value >= 99.99;
    }

    public function __toString(): string {
        return sprintf('%.1f%%', $this->value);
    }
}

/**
 * Value Object: Estado Operativo de Cabezal de Pozo
 */
class WellheadStatus {
    public const ONLINE      = 'EN LÍNEA';
    public const THROTTLED   = 'ESTRANGULADO';
    public const TRIPPED     = 'DISPARADO';
    public const MAINTENANCE = 'MANTENIMIENTO';

    private string $status;

    public function __construct(string $status) {
        $normalized = mb_strtoupper(trim($status), 'UTF-8');
        $allowed = [self::ONLINE, self::THROTTLED, self::TRIPPED, self::MAINTENANCE];
        if (!in_array($normalized, $allowed, true)) {
            throw new ValidationException("Estado operacional de pozo no válido: '{$status}'.");
        }
        $this->status = $normalized;
    }

    public function getValue(): string {
        return $this->status;
    }

    public function isOnline(): bool {
        return $this->status === self::ONLINE;
    }

    public function isTripped(): bool {
        return $this->status === self::TRIPPED;
    }
}

/**
 * Value Object: Severidad de Alarma ISA-18.2 y Reglas de Acuse
 */
class AlarmSeverity {
    public const CRITICAL = 'critical';
    public const WARNING  = 'warning';
    public const ADVISORY = 'advisory';
    public const NOMINAL  = 'nominal';

    private string $severity;

    public function __construct(string $severity) {
        $low = strtolower(trim($severity));
        $allowed = [self::CRITICAL, self::WARNING, self::ADVISORY, self::NOMINAL];
        if (!in_array($low, $allowed, true)) {
            $low = self::NOMINAL;
        }
        $this->severity = $low;
    }

    public function getValue(): string {
        return $this->severity;
    }

    public function getPriority(): int {
        return match ($this->severity) {
            self::CRITICAL => 1,
            self::WARNING  => 2,
            self::ADVISORY => 3,
            default        => 4
        };
    }

    /**
     * Valida si un intento de acuse cumple las directrices anti-Blind ACK de la ISA-18.2
     */
    public static function validateAcknowledgment(string $operatorName, string $reason): void {
        if (empty(trim($operatorName))) {
            throw new ValidationException('Regla ISA-18.2: Debe identificarse al operador que reconoce la alarma.');
        }
        if (mb_strlen(trim($reason), 'UTF-8') < 8) {
            throw new ValidationException('Regla ISA-18.2: La justificación técnica debe contener al menos 8 caracteres explicativos.');
        }
    }
}

/**
 * Value Object: Identidad y Permisos RBAC del Operador
 */
class OperatorIdentity {
    private string $id;
    private string $code;
    private string $name;
    private string $roleCode;
    private array $permissions;

    public function __construct(string $id, string $code, string $name, string $roleCode, array $permissions = []) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->roleCode = $roleCode;
        $this->permissions = $permissions;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['id'] ?? $data['user_id'] ?? 'usr-anon',
            $data['codigo_empleado'] ?? 'EMP-ANON',
            $data['nombre_completo'] ?? $data['nombre'] ?? 'Operador Anónimo',
            $data['rol_codigo'] ?? 'AUDIT-VIEW',
            $data['permisos'] ?? []
        );
    }

    public function hasPermission(string $permission): bool {
        // Administrador tiene control total
        if ($this->roleCode === 'SYS-ADMIN') {
            return true;
        }
        return in_array($permission, $this->permissions, true);
    }

    public function getId(): string { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getRoleCode(): string { return $this->roleCode; }
    public function getPermissions(): array { return $this->permissions; }
}

<?php
/**
 * PROYECTO MAPAF - Arquitectura de Dominio (Domain-Driven Design - DDD)
 * SENA ADSO · Ficha 3413988
 * 
 * Modela los 7 Dominios de Negocio (Bounded Contexts) que rigen el sistema
 * SCADA Táctico y Telemetría Industrial para Upstream / Midstream de Hidrocarburos.
 */

namespace Mapaf\Domain;

class DomainDefinition {
    public string $id;
    public string $name;
    public string $code;
    public string $description;
    public string $industrialStandard;
    public array $entities;
    public array $valueObjects;
    public array $domainEvents;
    public array $invariants;
    public string $color;

    public function __construct(
        string $id,
        string $name,
        string $code,
        string $description,
        string $industrialStandard,
        array $entities,
        array $valueObjects,
        array $domainEvents,
        array $invariants,
        string $color
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->description = $description;
        $this->industrialStandard = $industrialStandard;
        $this->entities = $entities;
        $this->valueObjects = $valueObjects;
        $this->domainEvents = $domainEvents;
        $this->invariants = $invariants;
        $this->color = $color;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'standard' => $this->industrialStandard,
            'entities' => $this->entities,
            'valueObjects' => $this->valueObjects,
            'domainEvents' => $this->domainEvents,
            'invariants' => $this->invariants,
            'color' => $this->color
        ];
    }
}

class DomainCatalog {
    /**
     * Retorna los 7 Bounded Contexts formales del sistema MAPAF
     * @return DomainDefinition[]
     */
    public static function getAllDomains(): array {
        return [
            new DomainDefinition(
                'dom-telemetry',
                'Dominio de Telemetría e Instrumentación IIoT',
                'TELEMETRY-CTX',
                'Supervisa en tiempo real las señales físicas provenientes de sensores de presión, temperatura, flujo másico y variadores de frecuencia.',
                'ANSI/ISA-5.1 (Simbología de Instrumentación)',
                ['TelemetryMetric', 'InstrumentNode', 'SensorCalibration'],
                ['EngineeringUnit (PSI, °C, BPD, Hz)', 'SignalQuality (Good, Bad, Uncertain)', 'SampleFrequency'],
                ['MetricUpdatedEvent', 'HighTripExceededEvent', 'SensorOfflineEvent'],
                [
                    'La presión en cabezal no puede ser un valor negativo.',
                    'Las lecturas fuera de rango físico deben marcarse con SignalQuality::Bad.',
                    'Todo cambio de valor debe preservar su historial de tendencias para análisis forense.'
                ],
                'emerald'
            ),

            new DomainDefinition(
                'dom-wellhead',
                'Dominio de Control de Pozos y Actuadores de Superficie',
                'WELLHEAD-CTX',
                'Controla la apertura micrométrica de los estranguladores Choke, las válvulas de seguridad hidráulica (SSV) y bombas electrosumergibles.',
                'API Spec 6A (Wellhead and Christmas Tree Equipment)',
                ['WellheadUnit', 'ChokeActuator', 'SurfaceSafetyValve (SSV)', 'EspPump'],
                ['ChokePercentage (0.0% - 100.0%)', 'OperatingMode (Normal, Choked, Tripped, ShutIn)', 'WellSector'],
                ['ChokePositionChangedEvent', 'ValveStateToggledEvent', 'WellheadTrippedEvent'],
                [
                    'No se puede abrir una válvula SSV si el pozo se encuentra en estado DISPARADO sin rearme previo.',
                    'El estrangulador Choke debe tener un rango continuo estrictamente acotado entre 0% y 100%.',
                    'Todo cambio de choke superior a ±15% requiere registro de maniobra en bitácora.'
                ],
                'sky'
            ),

            new DomainDefinition(
                'dom-safety',
                'Dominio de Seguridad Funcional y Paradas de Emergencia (SIS / ESD)',
                'SAFETY-CTX',
                'Garantiza la integridad de las personas, el medio ambiente y la infraestructura ante sobrepresiones extremas o fallas catastróficas.',
                'IEC 61511 / ISA-84 (Sistemas Instrumentados de Seguridad)',
                ['EsdInterlockMatrix', 'SafetyNode', 'BypassKey'],
                ['SafetyIntegrityLevel (SIL-2 / SIL-3)', 'EsdLevel (ESD-1 Total, ESD-2 Sector)', 'TripCause'],
                ['EmergencyShutdownTriggeredEvent', 'InterlockActivatedEvent', 'SafetyResetEvent'],
                [
                    'Una parada de emergencia ESD-1 tiene máxima prioridad y anula cualquier comando manual de apertura de válvulas.',
                    'El rearme (Reset) de una parada de emergencia requiere un rol con permiso "reset_esd".',
                    'Los enclavamientos de seguridad no pueden ser ignorados salvo autorización expresa de Bypass con firma digital.'
                ],
                'rose'
            ),

            new DomainDefinition(
                'dom-alarms',
                'Dominio de Gestión y Ciclo de Vida de Alarmas',
                'ALARMS-CTX',
                'Gestiona la detección, priorización, visualización y reconocimiento de condiciones anormales de proceso conforme a normas internacionales.',
                'ANSI/ISA-18.2 (Management of Alarm Systems for Process Industries)',
                ['AlarmEvent', 'AlarmDefinition', 'SuppressionRule'],
                ['AlarmSeverity (Nominal, Advisory, Warning, Critical)', 'AlarmState (Normal, Unacked, Acked, Shelved)', 'AckSignature'],
                ['AlarmTriggeredEvent', 'AlarmAcknowledgedEvent', 'AlarmClearedEvent', 'AlarmFloodDetectedEvent'],
                [
                    'Una alarma crítica no puede ser silenciada sin registrar la identidad del operador y la hora exacta.',
                    'El reconocimiento masivo (ACK ALL) solo es válido si el operador tiene nivel Supervisor o superior.',
                    'Las alarmas que permanezcan más de 2 horas sin acuse deben escalar automáticamente a nivel gerencial.'
                ],
                'amber'
            ),

            new DomainDefinition(
                'dom-identity',
                'Dominio de Identidad, Control de Acceso y Sesión JWT',
                'IDENTITY-CTX',
                'Autentica a los operadores de sala de control, emite tokens criptográficos JWT y hace cumplir la matriz de permisos RBAC.',
                'RFC 7519 (JSON Web Tokens) / NIST SP 800-63',
                ['SecurityUser', 'UserRole', 'OperatorSession', 'JwtToken'],
                ['EmployeeCode', 'PermissionKey', 'HmacSignature', 'TokenExpiration'],
                ['UserAuthenticatedEvent', 'JwtTokenIssuedEvent', 'PermissionDeniedEvent', 'SessionExpiredEvent'],
                [
                    'Todo token JWT debe estar firmado con HMAC SHA-256 usando la clave secreta del servidor.',
                    'Las maniobras críticas (ESD, Apertura de válvulas) exigen verificación de token y permiso específico en cada petición.',
                    'Un usuario con estado "SUSPENDIDO" no puede emitir ni renovar tokens JWT.'
                ],
                'purple'
            ),

            new DomainDefinition(
                'dom-audit',
                'Dominio de Auditoría Forense y Trazabilidad SCADA',
                'AUDIT-CTX',
                'Almacena de forma inmutable el registro cronológico de todas las intervenciones humanas y eventos automáticos del sistema.',
                '21 CFR Part 11 / ISA-95 (Trazabilidad y Registros Electrónicos)',
                ['AuditLogEntry', 'HistorianArchive', 'OperationReceipt'],
                ['LogCategory (Seguridad, Maniobra, Alarma, Configuración)', 'OperationStatus (Exitoso, Rechazado, Advertencia)', 'AuditDetails'],
                ['AuditRecordCreatedEvent', 'TamperAttemptDetectedEvent'],
                [
                    'Los registros de auditoría son de sólo anexión (Append-Only); queda terminantemente prohibida su modificación o eliminación.',
                    'Cada registro debe incluir código de empleado, nombre, rol, marca de tiempo y detalle explícito del delta.',
                    'La persistencia de auditoría se sincroniza en MySQL (mapaf_db) para garantizar durabilidad transaccional ACID.'
                ],
                'teal'
            ),

            new DomainDefinition(
                'dom-production',
                'Dominio de Producción y Balance de Flujo (Fiscalización)',
                'PRODUCTION-CTX',
                'Calcula el volumen neto fiscal de petróleo producido (BPD), el gas asociado (MSCFD) y la relación Gas-Oil (GOR).',
                'API MPMS Ch. 5.6 (Coriolis) / AGA Report No. 3 (Orifice)',
                ['ProductionReport', 'WellAllocation', 'FiscalMeter'],
                ['NetOilRate (BPD)', 'GasOilRatio (SCF/BBL)', 'MeasurementUncertainty (%)', 'CalculationMethod'],
                ['ProductionReconciledEvent', 'FiscalThresholdExceededEvent'],
                [
                    'El cálculo de producción debe compensar el volumen neto a condiciones estándar de temperatura (60°F / 15.56°C).',
                    'Si el flujómetro Coriolis entra en alarma, el sistema debe degradar automáticamente a cálculo por Placa de Orificio AGA-3.',
                    'La incertidumbre de medición para venta de custodia no puede superar el ±0.25%.'
                ],
                'indigo'
            )
        ];
    }
}

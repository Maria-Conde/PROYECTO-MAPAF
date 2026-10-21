/**
 * PROYECTO MAPAF - Dominios del Sistema (Domain-Driven Design - DDD)
 * SENA ADSO · Ficha 3413988
 * 
 * Define los 7 Bounded Contexts que componen la arquitectura de software
 * para la supervisión y control de telemetría de pozos petroleros.
 */

export interface SystemDomain {
  id: string;
  name: string;
  code: string;
  description: string;
  standard: string;
  entities: string[];
  valueObjects: string[];
  domainEvents: string[];
  invariants: string[];
  color: 'emerald' | 'sky' | 'rose' | 'amber' | 'purple' | 'teal' | 'indigo';
  icon: string;
}

export const SYSTEM_DOMAINS: SystemDomain[] = [
  {
    id: 'dom-telemetry',
    name: 'Dominio de Telemetría e Instrumentación IIoT',
    code: 'TELEMETRY-CTX',
    description: 'Adquisición y acondicionamiento en tiempo real de variables de proceso físico: presión de tubería (WHTP), presión de casing, temperatura de flujo, caudal volumétrico y frecuencia de variador ESP.',
    standard: 'ANSI/ISA-5.1 (Simbología e Identificación de Instrumentación)',
    entities: ['TelemetryMetric', 'InstrumentNode', 'SensorCalibration'],
    valueObjects: ['EngineeringUnit (PSI, °C, BPD, Hz)', 'SignalQuality (Good, Bad, Uncertain)', 'SampleFrequency (1000ms)'],
    domainEvents: ['MetricUpdatedEvent', 'HighTripExceededEvent', 'SensorOfflineEvent'],
    invariants: [
      'La presión en cabezal no puede ser un valor negativo.',
      'Las lecturas fuera del rango físico de calibración deben marcarse con SignalQuality::Bad.',
      'Todo cambio de valor debe preservar su historial de tendencias para análisis forense.'
    ],
    color: 'emerald',
    icon: 'Activity'
  },
  {
    id: 'dom-wellhead',
    name: 'Dominio de Control de Pozos y Actuadores de Superficie',
    code: 'WELLHEAD-CTX',
    description: 'Control de estranguladores micrométricos Choke, válvulas de seguridad hidráulica de superficie (SSV), inyección de desemulsificante químico y control de bombeo electrosumergible ESP.',
    standard: 'API Spec 6A (Wellhead and Christmas Tree Equipment)',
    entities: ['WellheadUnit', 'ChokeActuator', 'SurfaceSafetyValve (SSV)', 'EspPump'],
    valueObjects: ['ChokePercentage (0.0% - 100.0%)', 'OperatingMode (Normal, Choked, Tripped, ShutIn)', 'WellSector'],
    domainEvents: ['ChokePositionChangedEvent', 'ValveStateToggledEvent', 'WellheadTrippedEvent'],
    invariants: [
      'No se puede abrir una válvula SSV si el pozo se encuentra en estado DISPARADO sin rearme previo.',
      'El estrangulador Choke debe tener un rango continuo estrictamente acotado entre 0% y 100%.',
      'Todo cambio de choke superior a ±15% requiere registro de maniobra en bitácora.'
    ],
    color: 'sky',
    icon: 'Sliders'
  },
  {
    id: 'dom-safety',
    name: 'Dominio de Seguridad Funcional y Paradas de Emergencia (SIS / ESD)',
    code: 'SAFETY-CTX',
    description: 'Garantiza la integridad de vidas humanas, protección medioambiental e infraestructura mediante la activación de la matriz causa-efecto ESD-1 (Parada Total) y despresurización a antorcha.',
    standard: 'IEC 61511 / ISA-84 (Sistemas Instrumentados de Seguridad)',
    entities: ['EsdInterlockMatrix', 'SafetyNode', 'BypassKey'],
    valueObjects: ['SafetyIntegrityLevel (SIL-2 / SIL-3)', 'EsdLevel (ESD-1 Total, ESD-2 Sector)', 'TripCause'],
    domainEvents: ['EmergencyShutdownTriggeredEvent', 'InterlockActivatedEvent', 'SafetyResetEvent'],
    invariants: [
      'Una parada de emergencia ESD-1 tiene máxima prioridad y anula cualquier comando manual de apertura de válvulas.',
      'El rearme (Reset) de una parada de emergencia requiere un rol con permiso "reset_esd".',
      'Los enclavamientos de seguridad no pueden ser ignorados salvo autorización expresa de Bypass con firma digital.'
    ],
    color: 'rose',
    icon: 'ShieldAlert'
  },
  {
    id: 'dom-alarms',
    name: 'Dominio de Gestión y Ciclo de Vida de Alarmas',
    code: 'ALARMS-CTX',
    description: 'Gestión estandarizada de alarmas de proceso, priorización según severidad, filtrado de avalanchas y acuse trazable por operador conforme a normas de ingeniería química y de petróleo.',
    standard: 'ANSI/ISA-18.2 (Management of Alarm Systems for Process Industries)',
    entities: ['AlarmEvent', 'AlarmDefinition', 'SuppressionRule'],
    valueObjects: ['AlarmSeverity (Nominal, Advisory, Warning, Critical)', 'AlarmState (Normal, Unacked, Acked, Shelved)', 'AckSignature'],
    domainEvents: ['AlarmTriggeredEvent', 'AlarmAcknowledgedEvent', 'AlarmClearedEvent', 'AlarmFloodDetectedEvent'],
    invariants: [
      'Una alarma crítica no puede ser silenciada sin registrar la identidad del operador y la hora exacta.',
      'El reconocimiento masivo (ACK ALL) solo es válido si el operador tiene nivel Supervisor o superior.',
      'Las alarmas que permanezcan más de 2 horas sin acuse deben escalar automáticamente a nivel gerencial.'
    ],
    color: 'amber',
    icon: 'AlertTriangle'
  },
  {
    id: 'dom-identity',
    name: 'Dominio de Identidad, Control de Acceso y Sesión JWT',
    code: 'IDENTITY-CTX',
    description: 'Autenticación criptográfica de operadores con tokens JWT (HMAC SHA-256), cumplimiento de la matriz de roles y permisos RBAC y validación de turnos de guardia.',
    standard: 'RFC 7519 (JSON Web Tokens) / NIST SP 800-63',
    entities: ['SecurityUser', 'UserRole', 'OperatorSession', 'JwtToken'],
    valueObjects: ['EmployeeCode', 'PermissionKey', 'HmacSignature', 'TokenExpiration'],
    domainEvents: ['UserAuthenticatedEvent', 'JwtTokenIssuedEvent', 'PermissionDeniedEvent', 'SessionExpiredEvent'],
    invariants: [
      'Todo token JWT debe estar firmado con HMAC SHA-256 usando la clave secreta del servidor.',
      'Las maniobras críticas (ESD, Apertura de válvulas) exigen verificación de token y permiso específico en cada petición.',
      'Un usuario con estado "SUSPENDIDO" no puede emitir ni renovar tokens JWT.'
    ],
    color: 'purple',
    icon: 'Key'
  },
  {
    id: 'dom-audit',
    name: 'Dominio de Auditoría Forense y Trazabilidad SCADA',
    code: 'AUDIT-CTX',
    description: 'Almacenamiento inmutable y persistente en MySQL de cada intervención humana, cambio de consigna, reconocimiento de alarma y activación de seguridad.',
    standard: '21 CFR Part 11 / ISA-95 (Trazabilidad y Registros Electrónicos)',
    entities: ['AuditLogEntry', 'HistorianArchive', 'OperationReceipt'],
    valueObjects: ['LogCategory (Seguridad, Maniobra, Alarma, Configuración)', 'OperationStatus (Exitoso, Rechazado, Advertencia)', 'AuditDetails'],
    domainEvents: ['AuditRecordCreatedEvent', 'TamperAttemptDetectedEvent'],
    invariants: [
      'Los registros de auditoría son de sólo anexión (Append-Only); queda terminantemente prohibida su modificación o eliminación.',
      'Cada registro debe incluir código de empleado, nombre, rol, marca de tiempo y detalle explícito del delta.',
      'La persistencia de auditoría se sincroniza en MySQL (mapaf_db) para garantizar durabilidad transaccional ACID.'
    ],
    color: 'teal',
    icon: 'FileText'
  },
  {
    id: 'dom-production',
    name: 'Dominio de Producción y Balance de Flujo (Fiscalización)',
    code: 'PRODUCTION-CTX',
    description: 'Cálculo del caudal fiscal neto de crudo (BPD), gas asociado (MSCFD) y relación Gas-Oil (GOR) con compensación térmica a condiciones estándar 60°F.',
    standard: 'API MPMS Ch. 5.6 (Coriolis) / AGA Report No. 3 (Orifice)',
    entities: ['ProductionReport', 'WellAllocation', 'FiscalMeter'],
    valueObjects: ['NetOilRate (BPD)', 'GasOilRatio (SCF/BBL)', 'MeasurementUncertainty (%)', 'CalculationMethod'],
    domainEvents: ['ProductionReconciledEvent', 'FiscalThresholdExceededEvent'],
    invariants: [
      'El cálculo de producción debe compensar el volumen neto a condiciones estándar de temperatura (60°F / 15.56°C).',
      'Si el flujómetro Coriolis entra en alarma, el sistema debe degradar automáticamente a cálculo por Placa de Orificio AGA-3.',
      'La incertidumbre de medición para venta de custodia no puede superar el ±0.25%.'
    ],
    color: 'indigo',
    icon: 'Cpu'
  }
];

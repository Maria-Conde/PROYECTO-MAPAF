export type OperationalSeverity = 'nominal' | 'advisory' | 'warning' | 'critical';

export interface TelemetryMetric {
  id: string;
  tag: string;
  label: string;
  value: number;
  unit: string;
  target: number;
  lowTrip: number;
  highWarning: number;
  highTrip: number;
  trend: 'rising' | 'falling' | 'stable';
  history: number[];
  severity: OperationalSeverity;
}

export interface WellheadUnit {
  id: string;
  name: string;
  sector: string;
  status: 'EN LÍNEA' | 'ESTRANGULADO' | 'DISPARADO' | 'MANTENIMIENTO';
  tubingPressure: number; // PSI
  casingPressure: number; // PSI
  flowRate: number; // BPD
  temperature: number; // °C
  chokeOpening: number; // % 0-100
  chokeTarget: number;
  espFrequency: number; // Hz 30-65
  espVibration: number; // mm/s
  ssvOpen: boolean; // Válvula de Seguridad de Superficie
  annulusBleedOpen: boolean;
  chemicalInjection: boolean;
  sandAcousticPpm: number;
}

export interface AlarmEvent {
  id: string;
  timestamp: string;
  tag: string;
  subsystem: string;
  description: string;
  value: string;
  threshold: string;
  severity: OperationalSeverity;
  acknowledged: boolean;
  acknowledgedBy?: string;
  ackTimestamp?: string;
  tripCause?: string;
}

export interface ProcessNode {
  id: string;
  name: string;
  code: string;
  type: 'wellhead' | 'cyclone' | 'separator' | 'choke' | 'compressor' | 'pipeline';
  x: number;
  y: number;
  pressure: number;
  temperature: number;
  flow: number;
  valveOpen: boolean;
  status: OperationalSeverity;
}

export interface SpectralPoint {
  frequency: number; // Hz
  amplitude: number; // mm/s
  baseline: number;
}

export type PermissionKey =
  | 'view_telemetry'
  | 'control_valves'
  | 'control_choke'
  | 'execute_esd'
  | 'reset_esd'
  | 'ack_alarms'
  | 'ack_all_alarms'
  | 'export_historian'
  | 'control_cctv'
  | 'generate_reports'
  | 'bypass_interlocks'
  | 'manage_roles_users';

export interface PermissionDefinition {
  key: PermissionKey;
  label: string;
  category: 'Monitoreo' | 'Control y Maniobras' | 'Seguridad Crítica' | 'Administración';
  description: string;
}

export interface UserRole {
  id: string;
  name: string;
  code: string;
  description: string;
  color: 'emerald' | 'amber' | 'purple' | 'sky' | 'rose';
  isSystem: boolean;
  permissions: PermissionKey[];
}

export interface SecurityUser {
  id: string;
  employeeCode: string;
  fullName: string;
  email: string;
  roleId: string;
  assignedSector: string;
  status: 'ACTIVO' | 'EN TURNO' | 'SUSPENDIDO';
  lastAccess: string;
}

export interface AuditLogEntry {
  id: string;
  timestamp: string;
  userCode: string;
  userName: string;
  roleName: string;
  action: string;
  category: 'SEGURIDAD' | 'MANIOBRA' | 'ALARMA' | 'CONFIGURACIÓN';
  status: 'EXITOSO' | 'RECHAZADO' | 'ADVERTENCIA';
  details: string;
}


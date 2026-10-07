import { PermissionDefinition, UserRole, SecurityUser, AuditLogEntry } from '../types/scada';

export const ALL_PERMISSIONS: PermissionDefinition[] = [
  // Monitoreo
  {
    key: 'view_telemetry',
    label: 'Visualización de Telemetría en Vivo',
    category: 'Monitoreo',
    description: 'Acceso a lecturas de presión, caudal, corte de agua y diagramas P&ID.',
  },
  {
    key: 'export_historian',
    label: 'Historiador y Exportación CSV',
    category: 'Monitoreo',
    description: 'Descarga de series temporales y visualización de análisis espectral FFT.',
  },
  {
    key: 'control_cctv',
    label: 'Videovigilancia Óptica y CCTV',
    category: 'Monitoreo',
    description: 'Conmutación de canales de cámaras de proceso y visualización en tiempo real.',
  },

  // Control y Maniobras
  {
    key: 'control_valves',
    label: 'Accionamiento de Válvulas y Bloques',
    category: 'Control y Maniobras',
    description: 'Apertura y cierre de válvulas maestras, SSV y derivaciones de proceso.',
  },
  {
    key: 'control_choke',
    label: 'Ajuste de Estranguladores (Chokes)',
    category: 'Control y Maniobras',
    description: 'Modificación del porcentaje de apertura y consignas de orificio.',
  },
  {
    key: 'ack_alarms',
    label: 'Reconocimiento de Alarmas Individuales',
    category: 'Control y Maniobras',
    description: 'Confirmación individual de eventos de alarma ISA-18.2.',
  },
  {
    key: 'ack_all_alarms',
    label: 'Reconocimiento Masivo (ACK ALL)',
    category: 'Control y Maniobras',
    description: 'Reconocimiento global simultáneo de todas las alertas activas.',
  },

  // Seguridad Crítica
  {
    key: 'execute_esd',
    label: 'Disparo de Parada de Emergencia (ESD-1)',
    category: 'Seguridad Crítica',
    description: 'Autorización para activar el aislamiento total de estación y venteo.',
  },
  {
    key: 'reset_esd',
    label: 'Restablecimiento de Sistema tras ESD',
    category: 'Seguridad Crítica',
    description: 'Re-energización y retorno a condiciones nominales de planta.',
  },
  {
    key: 'bypass_interlocks',
    label: 'Bypass de Enclavamientos Nivel 2',
    category: 'Seguridad Crítica',
    description: 'Anulación de la guarda de retención de 1.5s para maniobras urgentes.',
  },

  // Administración
  {
    key: 'generate_reports',
    label: 'Emisión de Informes de Integridad',
    category: 'Administración',
    description: 'Generación de certificados técnicos y reportes de calibración de activos.',
  },
  {
    key: 'manage_roles_users',
    label: 'Gestión de Roles y Usuarios',
    category: 'Administración',
    description: 'Creación, edición y revocación de roles, permisos y credenciales.',
  },
];

export const INITIAL_ROLES: UserRole[] = [
  {
    id: 'role-operator',
    name: 'Operador de Consola',
    code: 'OPERATOR',
    description: 'Monitoreo de telemetría de campo, ajuste de consignas rutinarias y reconocimiento de alarmas individuales.',
    color: 'emerald',
    isSystem: true,
    permissions: [
      'view_telemetry',
      'control_valves',
      'control_choke',
      'ack_alarms',
      'export_historian',
      'control_cctv',
    ],
  },
  {
    id: 'role-supervisor',
    name: 'Supervisor de Turno RTOC',
    code: 'SUPERVISOR',
    description: 'Autoridad operativa total sobre la batería de pozos, autorización de parada de emergencia y reconocimiento masivo de alarmas.',
    color: 'amber',
    isSystem: true,
    permissions: [
      'view_telemetry',
      'control_valves',
      'control_choke',
      'execute_esd',
      'reset_esd',
      'ack_alarms',
      'ack_all_alarms',
      'export_historian',
      'control_cctv',
      'generate_reports',
    ],
  },
  {
    id: 'role-admin',
    name: 'Administrador de Sistema SCADA',
    code: 'ADMIN',
    description: 'Control de seguridad integral, gestión de matriz de permisos SIL-3, auditoría y aprovisionamiento de cuentas.',
    color: 'purple',
    isSystem: true,
    permissions: [
      'view_telemetry',
      'control_valves',
      'control_choke',
      'execute_esd',
      'reset_esd',
      'ack_alarms',
      'ack_all_alarms',
      'export_historian',
      'control_cctv',
      'generate_reports',
      'bypass_interlocks',
      'manage_roles_users',
    ],
  },
];

export const INITIAL_USERS: SecurityUser[] = [
  {
    id: 'usr-1',
    employeeCode: 'OP-449',
    fullName: 'M. Conde Dussan',
    email: 'mcondedussan@gmail.com',
    roleId: 'role-admin',
    assignedSector: 'Sector 04 · Compresión & Pozo Pad-04',
    status: 'EN TURNO',
    lastAccess: 'Hace 2 min (Consola Principal)',
  },
  {
    id: 'usr-2',
    employeeCode: 'OP-302',
    fullName: 'Carlos Mendoza',
    email: 'cmendoza@rtoc-operations.com',
    roleId: 'role-supervisor',
    assignedSector: 'Sector 04 · Batería de Separadores',
    status: 'ACTIVO',
    lastAccess: 'Hace 14 min',
  },
  {
    id: 'usr-3',
    employeeCode: 'OP-118',
    fullName: 'Elena Rojas',
    email: 'erojas@rtoc-operations.com',
    roleId: 'role-operator',
    assignedSector: 'Superficie Pad-04A / 04D',
    status: 'EN TURNO',
    lastAccess: 'Hace 5 min',
  },
  {
    id: 'usr-4',
    employeeCode: 'OP-520',
    fullName: 'Andrés Villalba',
    email: 'avillalba@rtoc-operations.com',
    roleId: 'role-operator',
    assignedSector: 'Trenes de Inyección Química',
    status: 'ACTIVO',
    lastAccess: 'Hace 1 hora',
  },
];

export const INITIAL_AUDIT_LOGS: AuditLogEntry[] = [
  {
    id: 'aud-01',
    timestamp: '14:22:10',
    userCode: 'OP-449',
    userName: 'M. Conde Dussan',
    roleName: 'Administrador',
    action: 'Auditoría de Matriz de Permisos',
    category: 'CONFIGURACIÓN',
    status: 'EXITOSO',
    details: 'Verificación de políticas SIL-3 y privilegios de operador completada.',
  },
  {
    id: 'aud-02',
    timestamp: '14:15:40',
    userCode: 'OP-302',
    userName: 'Carlos Mendoza',
    roleName: 'Supervisor',
    action: 'Aprobación de Setpoint de Estrangulador',
    category: 'MANIOBRA',
    status: 'EXITOSO',
    details: 'Ajuste de estrangulador PWV-103 autorizado al 42.8%.',
  },
  {
    id: 'aud-03',
    timestamp: '14:02:18',
    userCode: 'OP-118',
    userName: 'Elena Rojas',
    roleName: 'Operador',
    action: 'Reconocimiento de Alarma',
    category: 'ALARMA',
    status: 'EXITOSO',
    details: 'Alarma reconocida: Transitorio de presión WHTP-101A.',
  },
  {
    id: 'aud-04',
    timestamp: '13:48:52',
    userCode: 'OP-520',
    userName: 'Andrés Villalba',
    roleName: 'Operador',
    action: 'Intento de Disparo ESD Bloqueado',
    category: 'SEGURIDAD',
    status: 'RECHAZADO',
    details: 'Permiso denegado: El rol Operador no posee autorización para disparar ESD-1.',
  },
];

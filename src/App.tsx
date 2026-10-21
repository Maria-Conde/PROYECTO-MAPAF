/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState, useEffect } from 'react';
import { Header, ScreenId } from './components/Header';
import { OverviewScreen } from './screens/OverviewScreen';
import { WellheadControlScreen } from './screens/WellheadControlScreen';
import { AlarmsScreen } from './screens/AlarmsScreen';
import { HistorianScreen } from './screens/HistorianScreen';
import { FacilityVisualScreen } from './screens/FacilityVisualScreen';
import { SecurityRolesScreen } from './screens/SecurityRolesScreen';
import { ArchitectureComparisonsScreen } from './screens/ArchitectureComparisonsScreen';
import { PatternsScreen } from './screens/PatternsScreen';
import { HoldInterlockModal } from './components/HoldInterlockModal';
import { EsdModal } from './components/EsdModal';
import { XamppConnectionModal } from './components/XamppConnectionModal';
import { mapafApi, XamppStatus } from './services/mapafApi';
import { ShieldAlert, X, ArrowRight } from 'lucide-react';
import {
  INITIAL_METRICS,
  INITIAL_WELLHEADS,
  INITIAL_ALARMS,
  PROCESS_NODES,
  INITIAL_SPECTRAL,
} from './mock/scadaData';
import {
  INITIAL_ROLES,
  INITIAL_USERS,
  INITIAL_AUDIT_LOGS,
} from './mock/securityData';
import {
  TelemetryMetric,
  WellheadUnit,
  AlarmEvent,
  ProcessNode,
  UserRole,
  SecurityUser,
  AuditLogEntry,
  PermissionKey,
} from './types/scada';

export default function App() {
  const [currentScreen, setCurrentScreen] = useState<ScreenId>('overview');
  const [metrics, setMetrics] = useState<TelemetryMetric[]>(INITIAL_METRICS);
  const [wellheads, setWellheads] = useState<WellheadUnit[]>(INITIAL_WELLHEADS);
  const [alarms, setAlarms] = useState<AlarmEvent[]>(INITIAL_ALARMS);
  const [processNodes, setProcessNodes] = useState<ProcessNode[]>(PROCESS_NODES);
  const [audioMuted, setAudioMuted] = useState<boolean>(true);
  const [isEsdOpen, setIsEsdOpen] = useState<boolean>(false);
  const [flowState, setFlowState] = useState<'nominal' | 'throttled' | 'tripped'>('nominal');
  const [productionRate, setProductionRate] = useState<number>(14820);

  // Security & Roles State (with localStorage persistence)
  const [roles, setRoles] = useState<UserRole[]>(() => {
    try {
      const saved = localStorage.getItem('scada_roles');
      return saved ? JSON.parse(saved) : INITIAL_ROLES;
    } catch {
      return INITIAL_ROLES;
    }
  });

  const [users, setUsers] = useState<SecurityUser[]>(() => {
    try {
      const saved = localStorage.getItem('scada_users');
      return saved ? JSON.parse(saved) : INITIAL_USERS;
    } catch {
      return INITIAL_USERS;
    }
  });

  const [auditLogs, setAuditLogs] = useState<AuditLogEntry[]>(INITIAL_AUDIT_LOGS);
  const [currentRoleId, setCurrentRoleId] = useState<string>('role-admin');

  // RBAC Permission Denied Notification State
  const [deniedNotice, setDeniedNotice] = useState<{
    isOpen: boolean;
    title: string;
    requiredPerm: string;
    message: string;
  } | null>(null);

  // XAMPP (PHP + MySQL) Integration State
  const [xamppStatus, setXamppStatus] = useState<XamppStatus>({ connected: false });
  const [isXamppModalOpen, setIsXamppModalOpen] = useState<boolean>(false);

  // Sync data with XAMPP MySQL backend
  const syncWithXampp = async () => {
    try {
      const status = await mapafApi.checkStatus();
      setXamppStatus(status);
      if (status.connected) {
        const remote = await mapafApi.fetchSyncData();
        if (remote) {
          if (remote.metrics && remote.metrics.length > 0) setMetrics(remote.metrics);
          if (remote.wellheads && remote.wellheads.length > 0) setWellheads(remote.wellheads);
          if (remote.alarms && remote.alarms.length > 0) setAlarms(remote.alarms);
          if (remote.roles && remote.roles.length > 0) setRoles(remote.roles);
          if (remote.users && remote.users.length > 0) setUsers(remote.users);
          if (remote.auditLogs && remote.auditLogs.length > 0) setAuditLogs(remote.auditLogs);
          if (remote.systemConfig) {
            setFlowState(remote.systemConfig.flowState || 'nominal');
            setProductionRate(remote.systemConfig.productionRate || 14820);
          }
        }
      }
    } catch {
      // offline fallback
    }
  };

  useEffect(() => {
    syncWithXampp();
    const timer = setInterval(syncWithXampp, 8000);
    return () => clearInterval(timer);
  }, []);

  // Sync roles & users to localStorage as local cache
  useEffect(() => {
    try {
      localStorage.setItem('scada_roles', JSON.stringify(roles));
    } catch {
      // storage unavailable
    }
  }, [roles]);

  useEffect(() => {
    try {
      localStorage.setItem('scada_users', JSON.stringify(users));
    } catch {
      // storage unavailable
    }
  }, [users]);

  const currentRole = roles.find((r) => r.id === currentRoleId) || roles[0];

  // RBAC Check Helper
  const hasPermission = (key: PermissionKey): boolean => {
    if (!currentRole) return false;
    return currentRole.permissions.includes(key);
  };

  // Add an audit log entry
  const logAudit = (
    action: string,
    category: 'SEGURIDAD' | 'MANIOBRA' | 'ALARMA' | 'CONFIGURACIÓN',
    status: 'EXITOSO' | 'RECHAZADO' | 'ADVERTENCIA',
    details: string
  ) => {
    const now = new Date();
    const timeStr = now.toTimeString().split(' ')[0];
    const newLog: AuditLogEntry = {
      id: `aud-${Date.now().toString().slice(-5)}`,
      timestamp: timeStr,
      userCode: 'OP-449',
      userName: 'M. Conde Dussan',
      roleName: currentRole.name,
      action,
      category,
      status,
      details,
    };
    setAuditLogs((prev) => [newLog, ...prev]);
    // Persistir en MySQL en XAMPP
    mapafApi.addAuditLog(newLog).catch(() => {});
  };

  // Estado del modal de enclavamiento de seguridad
  const [interlockModal, setInterlockModal] = useState<{
    isOpen: boolean;
    title: string;
    targetTag: string;
    actionDescription: string;
    warningNote?: string;
    onConfirm: () => void;
  }>({
    isOpen: false,
    title: '',
    targetTag: '',
    actionDescription: '',
    onConfirm: () => {},
  });

  // Sintetizador de tonos audibles mediante Web Audio API
  const playBeep = (freq: number, type: OscillatorType = 'sine', duration = 0.2) => {
    if (audioMuted) return;
    try {
      const AudioCtx =
        window.AudioContext ||
        (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = type;
      osc.frequency.setValueAtTime(freq, ctx.currentTime);
      gain.gain.setValueAtTime(0.08, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + duration);
    } catch {
      // Audio seguro
    }
  };

  // Telemetría 1000ms
  useEffect(() => {
    const interval = setInterval(() => {
      if (flowState === 'tripped') return;

      setMetrics((prevMetrics) =>
        prevMetrics.map((m) => {
          const delta = (Math.random() - 0.49) * (m.target * 0.004);
          let newVal = m.value + delta;

          if (m.id === 'ft-201' && flowState === 'throttled') {
            newVal = 11850 + (Math.random() - 0.5) * 80;
          }

          const roundedVal = Number(newVal.toFixed(m.id === 'ft-201' ? 0 : 1));
          const newHistory = [...m.history.slice(1), roundedVal];

          let trend: 'rising' | 'falling' | 'stable' = 'stable';
          if (roundedVal > m.value + 0.1) trend = 'rising';
          else if (roundedVal < m.value - 0.1) trend = 'falling';

          return {
            ...m,
            value: roundedVal,
            history: newHistory,
            trend,
          };
        })
      );

      setWellheads((prevWells) =>
        prevWells.map((w) => {
          if (w.status === 'DISPARADO') return w;
          const pDelta = (Math.random() - 0.5) * 3;
          return {
            ...w,
            tubingPressure: Number((w.tubingPressure + pDelta).toFixed(1)),
          };
        })
      );
    }, 1000);

    return () => clearInterval(interval);
  }, [flowState]);

  // Request interlock with RBAC check
  const handleRequestInterlock = (
    tag: string,
    action: string,
    onConfirm: () => void,
    warning?: string
  ) => {
    if (!hasPermission('control_valves')) {
      logAudit(
        `Maniobra de válvula en ${tag}`,
        'SEGURIDAD',
        'RECHAZADO',
        `El rol '${currentRole.name}' no tiene el permiso 'control_valves'.`
      );
      setDeniedNotice({
        isOpen: true,
        title: 'Maniobra Bloqueada por Seguridad RBAC',
        requiredPerm: 'Accionamiento de Válvulas y Bloques',
        message: `El rol actual '${currentRole.name}' no cuenta con autorización para abrir o cerrar válvulas. Requiere credenciales de Supervisor o Administrador.`,
      });
      playBeep(260, 'sawtooth', 0.25);
      return;
    }

    setInterlockModal({
      isOpen: true,
      title: 'ENCLAVAMIENTO DE SEGURIDAD OPERATIVA',
      targetTag: tag,
      actionDescription: action,
      warningNote: warning,
      onConfirm: () => {
        setInterlockModal((prev) => ({ ...prev, isOpen: false }));
        onConfirm();
        playBeep(880, 'sine', 0.2);
        logAudit(
          `Ejecución de enclavamiento en ${tag}`,
          'MANIOBRA',
          'EXITOSO',
          `Acción confirmada tras guarda de 1.5s: ${action}`
        );
      },
    });
  };

  // Node interlock from process diagram
  const handleNodeInterlock = (node: ProcessNode) => {
    handleRequestInterlock(
      `${node.name} [${node.code}]`,
      node.valveOpen
        ? 'ORDEN: AISLAR VÁLVULA DE BLOQUEO DE EMERGENCIA (CIERRE)'
        : 'ORDEN: RESTABLECER Y APERTURAR VÁLVULA DE PASO DE PROCESO',
      () => {
        setProcessNodes((prev) =>
          prev.map((n) =>
            n.id === node.id ? { ...n, valveOpen: !n.valveOpen } : n
          )
        );
      },
      'Aislar este equipo redirigirá el caudal automáticamente hacia el múltiple de bypass.'
    );
  };

  // Update wellhead
  const handleUpdateWellhead = (updated: WellheadUnit) => {
    if (!hasPermission('control_choke')) {
      logAudit(
        `Modificación de pozo ${updated.name}`,
        'MANIOBRA',
        'RECHAZADO',
        `El rol '${currentRole.name}' no tiene permiso de ajuste de estranguladores.`
      );
      setDeniedNotice({
        isOpen: true,
        title: 'Consigna de Estrangulador Bloqueada',
        requiredPerm: 'Ajuste de Estranguladores (Chokes)',
        message: `El rol actual '${currentRole.name}' no tiene autorización para reposicionar estranguladores. Requiere rol de Operador o superior con permiso 'control_choke'.`,
      });
      return;
    }

    setWellheads((prev) =>
      prev.map((w) => (w.id === updated.id ? updated : w))
    );
    const totalFlow = wellheads.reduce(
      (sum, w) => sum + (w.id === updated.id ? updated.flowRate : w.flowRate),
      0
    );
    setProductionRate(totalFlow);

    // Persistir en MySQL en XAMPP
    mapafApi.updateWellhead({
      id: updated.id,
      chokeOpening: updated.chokeOpening,
      status: updated.status,
      ssvOpen: updated.ssvOpen,
      annulusBleedOpen: updated.annulusBleedOpen,
      chemicalInjection: updated.chemicalInjection,
      espFrequency: updated.espFrequency,
      actionDescription: `Ajuste en pozo ${updated.name}`,
      userName: 'M. Conde Dussan',
      roleName: currentRole.name,
    }).catch(() => {});
  };

  // Quick throttle from overview
  const handleQuickThrottle = () => {
    if (!hasPermission('control_choke')) {
      setDeniedNotice({
        isOpen: true,
        title: 'Estrangulamiento Bloqueado',
        requiredPerm: 'Ajuste de Estranguladores (Chokes)',
        message: 'No posee privilegios para estrangular la tasa general de producción.',
      });
      return;
    }

    if (flowState === 'throttled') {
      setFlowState('nominal');
      setProductionRate(14820);
      logAudit('Restablecimiento de Caudal', 'MANIOBRA', 'EXITOSO', 'Caudal general a 14,820 BPD.');
    } else {
      setFlowState('throttled');
      setProductionRate(11850);
      logAudit('Estrangulamiento Rápido', 'MANIOBRA', 'EXITOSO', 'Caudal restringido a 11,850 BPD.');
    }
  };

  // Open ESD with RBAC check
  const handleOpenEsd = () => {
    if (!hasPermission('execute_esd')) {
      logAudit(
        'Intento de Acceso a Parada de Emergencia ESD',
        'SEGURIDAD',
        'RECHAZADO',
        `El rol '${currentRole.name}' carece de autoridad para disparar ESD-1.`
      );
      setDeniedNotice({
        isOpen: true,
        title: 'Autorización Denegada: Parada de Emergencia ESD-1',
        requiredPerm: 'Disparo de Parada de Emergencia (ESD-1)',
        message: `El rol actual '${currentRole.name}' no tiene la autorización SIL-3 requerida para el disparo de aislamiento general de planta. Esta maniobra está reservada exclusivamente a Supervisores de Turno y Administradores.`,
      });
      playBeep(260, 'sawtooth', 0.3);
      return;
    }

    setIsEsdOpen(true);
  };

  // Execute ESD
  const handleExecuteEsd = () => {
    setIsEsdOpen(false);
    setFlowState('tripped');
    setProductionRate(0);

    setWellheads((prev) =>
      prev.map((w) => ({
        ...w,
        status: 'DISPARADO',
        flowRate: 0,
        ssvOpen: false,
        chokeOpening: 0,
        espFrequency: 0,
      }))
    );

    const now = new Date();
    const timeStr = now.toTimeString().split(' ')[0];
    const newAlarm: AlarmEvent = {
      id: `ALM-ESD-${Date.now().toString().slice(-4)}`,
      timestamp: timeStr,
      tag: 'ESD-ETAPA-1',
      subsystem: 'SISTEMA ESD DE ESTACIÓN',
      description: 'DISPARO MANUAL DE OPERADOR: AISLAMIENTO ESD-1 DE ESTACIÓN EJECUTADO',
      value: 'DISPARADO',
      threshold: 'ARMADO',
      severity: 'critical',
      acknowledged: false,
      tripCause: `Aislamiento general ejecutado por rol ${currentRole.name}`,
    };

    setAlarms((prev) => [newAlarm, ...prev]);
    playBeep(440, 'sawtooth', 0.5);

    logAudit(
      'Parada de Emergencia (ESD-1) Ejecutada',
      'SEGURIDAD',
      'EXITOSO',
      'Aislamiento total de estación y cierre de pozos activado exitosamente.'
    );
    // Persistir en MySQL en XAMPP
    mapafApi.setEsdState('trip', `M. Conde Dussan (${currentRole.name})`).catch(() => {});
  };

  // Reset ESD
  const handleResetSystem = () => {
    if (!hasPermission('reset_esd')) {
      setDeniedNotice({
        isOpen: true,
        title: 'Restablecimiento Denegado',
        requiredPerm: 'Restablecimiento de Sistema tras ESD',
        message: `El rol actual '${currentRole.name}' no tiene privilegios para desarmar el viaje ESD.`,
      });
      return;
    }

    setFlowState('nominal');
    setProductionRate(14820);
    setWellheads(INITIAL_WELLHEADS);
    setMetrics(INITIAL_METRICS);
    playBeep(660, 'sine', 0.2);

    logAudit(
      'Re-energización de Estación tras ESD',
      'SEGURIDAD',
      'EXITOSO',
      'Sistema restablecido a parámetros nominales de operación.'
    );
    // Persistir en MySQL en XAMPP
    mapafApi.setEsdState('reset', `M. Conde Dussan (${currentRole.name})`).catch(() => {});
  };

  // Acknowledge Alarm
  const handleAcknowledgeAlarm = (id: string) => {
    if (!hasPermission('ack_alarms')) {
      setDeniedNotice({
        isOpen: true,
        title: 'Permiso Insuficiente',
        requiredPerm: 'Reconocimiento de Alarmas Individuales',
        message: `Su rol '${currentRole.name}' no tiene autorización para reconocer alarmas.`,
      });
      return;
    }

    const now = new Date();
    const timeStr = now.toTimeString().split(' ')[0];
    setAlarms((prev) =>
      prev.map((a) =>
        a.id === id
          ? {
              ...a,
              acknowledged: true,
              acknowledgedBy: `OP-449 (${currentRole.name})`,
              ackTimestamp: timeStr,
            }
          : a
      )
    );
    playBeep(523.25, 'sine', 0.15);
    logAudit('Reconocimiento de Alarma', 'ALARMA', 'EXITOSO', `Evento ${id} reconocido.`);

    // Persistir en MySQL en XAMPP
    mapafApi.acknowledgeAlarm(id, `M. Conde Dussan (${currentRole.name})`).catch(() => {});
  };

  // Acknowledge All Alarms
  const handleAcknowledgeAll = () => {
    if (!hasPermission('ack_all_alarms')) {
      setDeniedNotice({
        isOpen: true,
        title: 'Reconocimiento Masivo Bloqueado',
        requiredPerm: 'Reconocimiento Masivo (ACK ALL)',
        message: `El reconocimiento global simultáneo requiere rol de Supervisor o Administrador.`,
      });
      return;
    }

    const now = new Date();
    const timeStr = now.toTimeString().split(' ')[0];
    setAlarms((prev) =>
      prev.map((a) => ({
        ...a,
        acknowledged: true,
        acknowledgedBy: `OP-449 (${currentRole.name})`,
        ackTimestamp: timeStr,
      }))
    );
    playBeep(523.25, 'sine', 0.2);
    logAudit('Reconocimiento Masivo (ACK ALL)', 'ALARMA', 'EXITOSO', 'Todas las alertas activas reconocidas.');

    // Persistir en MySQL en XAMPP
    mapafApi.acknowledgeAllAlarms(`M. Conde Dussan (${currentRole.name})`).catch(() => {});
  };

  // Trigger simulated alarm
  const handleTriggerSimulatedAlarm = () => {
    const now = new Date();
    const timeStr = now.toTimeString().split(' ')[0];
    const tags = ['PT-ALTA-104', 'VIB-BES-B04', 'GOR-LÍMITE-402', 'TEMP-CHOKE-A12'];
    const randomTag = tags[Math.floor(Math.random() * tags.length)];

    const newAlarm: AlarmEvent = {
      id: `ALM-${Math.floor(1000 + Math.random() * 9000)}`,
      timestamp: timeStr,
      tag: randomTag,
      subsystem: 'MÚLTIPLE DE CABEZALES',
      description: 'Transitorio de sobrepresión detectado sobrepasando límite secundario',
      value: `${(1800 + Math.random() * 450).toFixed(1)} PSI`,
      threshold: '2,100 PSI',
      severity: 'warning',
      acknowledged: false,
      tripCause: 'Ajuste rápido de orificio de estrangulador indujo bacheo transitorio',
    };

    setAlarms((prev) => [newAlarm, ...prev]);
    playBeep(880, 'square', 0.3);
    logAudit('Inyección de Alarma Simulada', 'ALARMA', 'ADVERTENCIA', `Prueba de sensor ${randomTag}`);
  };

  // Export CSV
  const handleExportCsv = () => {
    if (!hasPermission('export_historian')) {
      setDeniedNotice({
        isOpen: true,
        title: 'Exportación de Datos Denegada',
        requiredPerm: 'Historiador y Exportación CSV',
        message: 'No posee permiso de descarga de series temporales de proceso.',
      });
      return;
    }

    const rows = [
      ['MARCA_TEMPORAL', 'TAG_VARIABLE', 'VALOR', 'UNIDAD', 'SEVERIDAD'],
      ...metrics.map((m) => [
        new Date().toISOString(),
        m.tag,
        m.value.toString(),
        m.unit,
        m.severity,
      ]),
    ];
    const csvContent =
      'data:text/csv;charset=utf-8,' + rows.map((e) => e.join(',')).join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `scada_telemetria_export_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    logAudit('Exportación Histórica CSV', 'CONFIGURACIÓN', 'EXITOSO', 'Descarga de registro de planta.');
  };

  // Role Management Handlers
  const handleSelectActiveRole = (roleId: string) => {
    setCurrentRoleId(roleId);
    const target = roles.find((r) => r.id === roleId);
    if (target) {
      logAudit(
        'Cambio de Rol Activo en Sesión',
        'SEGURIDAD',
        'EXITOSO',
        `Sesión conmutada al rol: ${target.name} (${target.code})`
      );
    }
  };

  const handleUpdateRolePermissions = (roleId: string, updatedPerms: PermissionKey[]) => {
    setRoles((prev) =>
      prev.map((r) => (r.id === roleId ? { ...r, permissions: updatedPerms } : r))
    );
    const target = roles.find((r) => r.id === roleId);
    logAudit(
      `Actualización de Permisos del Rol`,
      'CONFIGURACIÓN',
      'EXITOSO',
      `Rol ${target?.name}: ${updatedPerms.length} permisos asignados.`
    );
  };

  const handleCreateRole = (newRoleData: Omit<UserRole, 'id' | 'isSystem'>) => {
    const newId = `role-custom-${Date.now().toString().slice(-4)}`;
    const newRole: UserRole = {
      ...newRoleData,
      id: newId,
      isSystem: false,
    };
    setRoles((prev) => [...prev, newRole]);
    logAudit(
      'Creación de Rol Personalizado',
      'CONFIGURACIÓN',
      'EXITOSO',
      `Nuevo rol creado: ${newRole.name} [${newRole.code}] con ${newRole.permissions.length} permisos.`
    );
  };

  const handleDeleteRole = (roleId: string) => {
    const target = roles.find((r) => r.id === roleId);
    if (!target || target.isSystem) return;

    setRoles((prev) => prev.filter((r) => r.id !== roleId));
    if (currentRoleId === roleId) {
      setCurrentRoleId('role-operator');
    }
    logAudit(
      'Eliminación de Rol Personalizado',
      'CONFIGURACIÓN',
      'ADVERTENCIA',
      `Rol eliminado: ${target.name} (${target.code})`
    );
  };

  const handleUpdateUserRole = (userId: string, newRoleId: string) => {
    setUsers((prev) =>
      prev.map((u) => (u.id === userId ? { ...u, roleId: newRoleId } : u))
    );
    const userObj = users.find((u) => u.id === userId);
    const roleObj = roles.find((r) => r.id === newRoleId);
    logAudit(
      'Reasignación de Rol a Operador',
      'CONFIGURACIÓN',
      'EXITOSO',
      `Usuario ${userObj?.fullName} (${userObj?.employeeCode}) reasignado a rol ${roleObj?.name}.`
    );
  };

  const handleCreateUser = (newUserData: Omit<SecurityUser, 'id' | 'lastAccess'>) => {
    const newUser: SecurityUser = {
      ...newUserData,
      id: `usr-${Date.now().toString().slice(-4)}`,
      lastAccess: 'Registrado recientemente',
    };
    setUsers((prev) => [...prev, newUser]);
    logAudit(
      'Aprovisionamiento de Nuevo Operador',
      'CONFIGURACIÓN',
      'EXITOSO',
      `Operador registrado: ${newUser.fullName} [${newUser.employeeCode}].`
    );
  };

  const handleToggleUserStatus = (userId: string) => {
    setUsers((prev) =>
      prev.map((u) => {
        if (u.id === userId) {
          const nextStatus: SecurityUser['status'] =
            u.status === 'SUSPENDIDO' ? 'EN TURNO' : 'SUSPENDIDO';
          logAudit(
            'Modificación de Estado de Usuario',
            'CONFIGURACIÓN',
            'ADVERTENCIA',
            `Usuario ${u.employeeCode} cambiado a ${nextStatus}`
          );
          return { ...u, status: nextStatus };
        }
        return u;
      })
    );
  };

  const unacknowledgedCount = alarms.filter((a) => !a.acknowledged).length;

  return (
    <div className="flex flex-col h-screen w-screen bg-slate-950 text-slate-100 overflow-hidden select-none font-sans">
      {/* Barra superior con navegación y roles */}
      <Header
        currentScreen={currentScreen}
        onSelectScreen={setCurrentScreen}
        onOpenEsd={handleOpenEsd}
        unacknowledgedCount={unacknowledgedCount}
        audioMuted={audioMuted}
        onToggleAudio={() => setAudioMuted(!audioMuted)}
        activeRoleName={currentRole.name}
        activeRoleColor={currentRole.color}
        xamppConnected={xamppStatus.connected}
        onOpenXamppModal={() => setIsXamppModalOpen(true)}
      />

      {/* Escenario de Operaciones Principal */}
      <main className="flex-1 flex flex-col overflow-hidden bg-tech-grid">
        {currentScreen === 'overview' && (
          <OverviewScreen
            metrics={metrics}
            processNodes={processNodes}
            onOpenInterlock={handleNodeInterlock}
            productionRate={productionRate}
            flowState={flowState}
            onQuickThrottle={handleQuickThrottle}
            onResetSystem={handleResetSystem}
          />
        )}

        {currentScreen === 'wellheads' && (
          <WellheadControlScreen
            wellheads={wellheads}
            onUpdateWellhead={handleUpdateWellhead}
            onRequestInterlock={handleRequestInterlock}
          />
        )}

        {currentScreen === 'alarms' && (
          <AlarmsScreen
            alarms={alarms}
            onAcknowledgeAlarm={handleAcknowledgeAlarm}
            onAcknowledgeAll={handleAcknowledgeAll}
            onTriggerSimulatedAlarm={handleTriggerSimulatedAlarm}
          />
        )}

        {currentScreen === 'historian' && (
          <HistorianScreen
            metrics={metrics}
            spectralData={INITIAL_SPECTRAL}
            onExportCsv={handleExportCsv}
          />
        )}

        {currentScreen === 'facility' && <FacilityVisualScreen />}

        {currentScreen === 'patterns' && (
          <PatternsScreen
            xamppConnected={xamppStatus.connected}
            onOpenXamppModal={() => setIsXamppModalOpen(true)}
          />
        )}

        {currentScreen === 'comparisons' && <ArchitectureComparisonsScreen />}

        {currentScreen === 'roles' && (
          <SecurityRolesScreen
            roles={roles}
            users={users}
            auditLogs={auditLogs}
            currentRoleId={currentRoleId}
            onSelectActiveRole={handleSelectActiveRole}
            onUpdateRolePermissions={handleUpdateRolePermissions}
            onCreateRole={handleCreateRole}
            onDeleteRole={handleDeleteRole}
            onUpdateUserRole={handleUpdateUserRole}
            onCreateUser={handleCreateUser}
            onToggleUserStatus={handleToggleUserStatus}
          />
        )}
      </main>

      {/* Modal de Enclavamiento de Seguridad */}
      <HoldInterlockModal
        isOpen={interlockModal.isOpen}
        title={interlockModal.title}
        targetTag={interlockModal.targetTag}
        actionDescription={interlockModal.actionDescription}
        warningNote={interlockModal.warningNote}
        onConfirm={interlockModal.onConfirm}
        onCancel={() =>
          setInterlockModal((prev) => ({ ...prev, isOpen: false }))
        }
      />

      {/* Modal de Parada de Emergencia (ESD) */}
      <EsdModal
        isOpen={isEsdOpen}
        onConfirm={handleExecuteEsd}
        onCancel={() => setIsEsdOpen(false)}
      />

      {/* Modal / Toast de Notificación de Permiso Denegado por RBAC */}
      {deniedNotice && deniedNotice.isOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
          <div className="relative w-full max-w-md bg-slate-900 rounded-2xl border border-rose-500/60 shadow-2xl p-6 text-slate-100">
            <div className="flex items-start justify-between border-b border-slate-800 pb-3 mb-4">
              <div className="flex items-center gap-3">
                <div className="p-2.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400">
                  <ShieldAlert className="w-6 h-6 animate-pulse" />
                </div>
                <div>
                  <h3 className="font-display text-sm font-bold uppercase tracking-wider text-rose-200">
                    {deniedNotice.title}
                  </h3>
                  <p className="text-[11px] font-mono-data text-slate-400">
                    SEGURIDAD DE PROCESO ISA-84 / IEC-61511
                  </p>
                </div>
              </div>
              <button
                onClick={() => setDeniedNotice(null)}
                className="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800 mb-4">
              <div className="text-[10px] font-mono-data uppercase text-slate-400 font-semibold mb-1">
                PERMISO REQUERIDO
              </div>
              <div className="font-mono-data text-xs font-bold text-rose-400">
                {deniedNotice.requiredPerm}
              </div>
              <div className="text-xs text-slate-300 mt-2 leading-relaxed">
                {deniedNotice.message}
              </div>
            </div>

            <div className="flex items-center justify-between gap-3">
              <button
                onClick={() => setDeniedNotice(null)}
                className="px-4 py-2 text-xs font-mono-data uppercase bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors font-semibold"
              >
                Cerrar
              </button>
              <button
                onClick={() => {
                  setDeniedNotice(null);
                  setCurrentScreen('roles');
                }}
                className="flex-1 px-4 py-2 text-xs font-mono-data font-bold uppercase bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition-all flex items-center justify-center gap-2 shadow-sm"
              >
                <span>Gestionar Roles</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal de Conexión y Diagnóstico XAMPP (PHP + MySQL) */}
      <XamppConnectionModal
        isOpen={isXamppModalOpen}
        onClose={() => setIsXamppModalOpen(false)}
        status={xamppStatus}
        onRefresh={syncWithXampp}
      />
    </div>
  );
}

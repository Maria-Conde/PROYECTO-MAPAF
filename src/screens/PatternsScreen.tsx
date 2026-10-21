import React, { useState, useEffect } from 'react';
import {
  Layers,
  Shield,
  Sliders,
  Database,
  Key,
  Server,
  Activity,
  CheckCircle2,
  AlertTriangle,
  RotateCcw,
  Play,
  Copy,
  Check,
  Cpu,
  RefreshCw,
  ExternalLink,
  Zap,
  Radio,
  FileText,
  Lock,
  ArrowRight,
  Gauge,
  Code2,
} from 'lucide-react';
import {
  ScadaTelemetryStore,
  SensorMetricFactory,
  IndustrialSensor,
  TelemetryPublisher,
  TelemetryNotification,
  ScadaCommandManager,
  IScadaCommand,
  CoriolisStrategy,
  Aga3Strategy,
  BeggsBrillStrategy,
  FlowCalculationResult,
  WellheadStateMachine,
  WellStateId,
  IndustrialProtocolAdapter,
} from '../patterns/scadaPatterns';
import { ANTIPATTERNS_DATA, AntiPatternItem } from '../antipatterns/antiPatternsData';
import { SYSTEM_DOMAINS, SystemDomain } from '../domains/domainModels';
import { jwtAuth, DecodedJwt } from '../services/jwtAuth';

interface PatternsScreenProps {
  xamppConnected?: boolean;
  onOpenXamppModal?: () => void;
}

export const PatternsScreen: React.FC<PatternsScreenProps> = ({
  xamppConnected = false,
  onOpenXamppModal,
}) => {
  const [activeMainTab, setActiveMainTab] = useState<'patterns' | 'antipatterns' | 'domains' | 'jwt' | 'xampp'>('patterns');
  const [activePatternSubTab, setActivePatternSubTab] = useState<'command' | 'observer' | 'strategy' | 'factory' | 'state' | 'adapter' | 'singleton' | 'matrix'>('command');

  // ==========================================================================
  // STATE: 1. COMMAND PATTERN (con UNDO)
  // ==========================================================================
  const [commandManager] = useState(() => new ScadaCommandManager());
  const [chokeValue, setChokeValue] = useState<number>(42.8);
  const [ssvValveOpen, setSsvValveOpen] = useState<boolean>(true);
  const [commandLogs, setCommandLogs] = useState<IScadaCommand[]>([]);
  const [commandFeedback, setCommandFeedback] = useState<string>('Listo para ejecutar maniobras.');

  const handleAdjustChoke = (newTarget: number) => {
    const previous = chokeValue;
    const cmd: IScadaCommand = {
      id: 'cmd-' + Math.random().toString(36).substring(2, 7),
      description: `Regulación Choke Alfa-12: ${previous}% ➔ ${newTarget}%`,
      operator: 'M. Conde Dussan (Admin)',
      timestamp: new Date().toLocaleTimeString(),
      execute: () => {
        setChokeValue(newTarget);
        return { success: true, message: `Estrangulador regulado a ${newTarget}%` };
      },
      undo: () => {
        setChokeValue(previous);
        return { success: true, message: `Reversión: Choke restaurado a ${previous}%` };
      },
    };

    const res = commandManager.execute(cmd);
    setCommandLogs(commandManager.getHistory());
    setCommandFeedback(res.message);
  };

  const handleToggleValve = () => {
    const previous = ssvValveOpen;
    const target = !previous;
    const cmd: IScadaCommand = {
      id: 'cmd-' + Math.random().toString(36).substring(2, 7),
      description: `Conmutación Válvula SSV: ${previous ? 'ABIERTA' : 'CERRADA'} ➔ ${target ? 'ABIERTA' : 'CERRADA'}`,
      operator: 'M. Conde Dussan (Admin)',
      timestamp: new Date().toLocaleTimeString(),
      execute: () => {
        setSsvValveOpen(target);
        return { success: true, message: `Válvula hidráulica SSV ${target ? 'abierta' : 'cerrada'}.` };
      },
      undo: () => {
        setSsvValveOpen(previous);
        return { success: true, message: `Reversión: Válvula SSV retornada a ${previous ? 'abierta' : 'cerrada'}.` };
      },
    };

    const res = commandManager.execute(cmd);
    setCommandLogs(commandManager.getHistory());
    setCommandFeedback(res.message);
  };

  const handleUndo = () => {
    const res = commandManager.undoLast();
    setCommandLogs(commandManager.getHistory());
    setCommandFeedback(res.message);
  };

  // ==========================================================================
  // STATE: 2. OBSERVER PATTERN
  // ==========================================================================
  const [testPressure, setTestPressure] = useState<number>(1850);
  const [publisher] = useState(() => new TelemetryPublisher());
  const [observerEvents, setObserverEvents] = useState<Array<{ observer: string; time: string; text: string; severity: string }>>([]);

  useEffect(() => {
    // Configurar observadores reactivos
    const unsubAlarm = publisher.subscribe('AlarmObserver (ISA-18.2)', (ev) => {
      if (ev.value >= 2400) {
        setObserverEvents((prev) => [
          { observer: 'AlarmObserver', time: ev.timestamp, text: `🚨 ALARMA CRÍTICA DISPARADA: ${ev.tag} = ${ev.value} PSI (Excede High-Trip 2400 PSI)`, severity: 'critical' },
          ...prev.slice(0, 15),
        ]);
      } else if (ev.value >= 2000) {
        setObserverEvents((prev) => [
          { observer: 'AlarmObserver', time: ev.timestamp, text: `⚠️ ADVERTENCIA: ${ev.tag} = ${ev.value} PSI (Excede High-Warning 2000 PSI)`, severity: 'warning' },
          ...prev.slice(0, 15),
        ]);
      }
    });

    const unsubAudit = publisher.subscribe('AuditLoggerObserver', (ev) => {
      if (ev.value >= 2000) {
        setObserverEvents((prev) => [
          { observer: 'AuditLoggerObserver', time: ev.timestamp, text: `📝 Log registrado en MySQL: Transgresión de presión en ${ev.tag} (${ev.value} PSI)`, severity: 'audit' },
          ...prev.slice(0, 15),
        ]);
      }
    });

    const unsubInterlock = publisher.subscribe('SafetyInterlockObserver (ESD-1)', (ev) => {
      if (ev.value >= 2400) {
        setObserverEvents((prev) => [
          { observer: 'SafetyInterlockObserver', time: ev.timestamp, text: `🛑 ENCLAVAMIENTO SIS ACTIVADO: Disparo de Parada de Emergencia ESD-1 por sobrepresión.`, severity: 'critical' },
          ...prev.slice(0, 15),
        ]);
      }
    });

    return () => {
      unsubAlarm();
      unsubAudit();
      unsubInterlock();
    };
  }, [publisher]);

  const handlePressureChange = (val: number) => {
    setTestPressure(val);
    const notification: TelemetryNotification = {
      tag: 'WHTP-101A',
      value: val,
      unit: 'PSI',
      severity: val >= 2400 ? 'critical' : val >= 2000 ? 'warning' : 'nominal',
      timestamp: new Date().toLocaleTimeString(),
    };
    publisher.notify(notification);
  };

  // ==========================================================================
  // STATE: 3. STRATEGY PATTERN
  // ==========================================================================
  const [stratWhtp, setStratWhtp] = useState<number>(1842.5);
  const [stratTemp, setStratTemp] = useState<number>(64.2);
  const [stratChoke, setStratChoke] = useState<number>(42.8);
  const [stratEsp, setStratEsp] = useState<number>(58.2);

  const coriolisRes = new CoriolisStrategy().calculate(stratWhtp, stratTemp, stratChoke, stratEsp);
  const aga3Res = new Aga3Strategy().calculate(stratWhtp, stratTemp, stratChoke, stratEsp);
  const beggsRes = new BeggsBrillStrategy().calculate(stratWhtp, stratTemp, stratChoke, stratEsp);

  // ==========================================================================
  // STATE: 4. FACTORY METHOD PATTERN
  // ==========================================================================
  const [factoryType, setFactoryType] = useState<'pressure' | 'temperature' | 'flow' | 'frequency' | 'gor'>('pressure');
  const [factoryTag, setFactoryTag] = useState<string>('PT-101A');
  const [factoryLabel, setFactoryLabel] = useState<string>('Presión Cabezal Tubing');
  const [spawnedSensors, setSpawnedSensors] = useState<IndustrialSensor[]>([
    SensorMetricFactory.createSensor('pressure', 'WHTP-101A', 'Presión Tubing Pozo Alfa-12', 1842.5),
    SensorMetricFactory.createSensor('temperature', 'TEMP-301', 'Temperatura Termoresistencia PT-100', 64.2),
    SensorMetricFactory.createSensor('flow', 'MFM-201', 'Flujómetro Másico Coriolis', 14820),
  ]);

  const handleSpawnSensor = () => {
    const newSensor = SensorMetricFactory.createSensor(factoryType, factoryTag, factoryLabel);
    setSpawnedSensors([newSensor, ...spawnedSensors]);
  };

  // ==========================================================================
  // STATE: 5. STATE PATTERN
  // ==========================================================================
  const [stateMachine] = useState(() => new WellheadStateMachine('PRODUCIENDO'));
  const [wellCurrentState, setWellCurrentState] = useState<WellStateId>('PRODUCIENDO');
  const [stateMessage, setStateMessage] = useState<string>('Pozo en operación nominal.');

  const handleStateTransition = (target: WellStateId, reason: string) => {
    const res = stateMachine.transition(target, reason);
    setWellCurrentState(stateMachine.getState());
    setStateMessage(res.message);
  };

  // ==========================================================================
  // STATE: 6. ADAPTER PATTERN
  // ==========================================================================
  const [adaptedModbus, setAdaptedModbus] = useState<any>(null);
  const [adaptedOpcUa, setAdaptedOpcUa] = useState<any>(null);

  const runAdapterDemo = () => {
    const rawModbus = { slaveId: 1, registerAddress: 40001, hexBytes: [0x44, 0xE7] };
    const mRes = IndustrialProtocolAdapter.adaptModbus(rawModbus, 'WHTP-101A', 'PSI');
    setAdaptedModbus(mRes);

    const rawOpc = {
      nodeId: 'ns=2;s=Separator.V-101.Pressure',
      sourceTimestamp: new Date().toISOString(),
      variantValue: 642.0,
      statusCode: '0x00000000 (Good)',
    };
    const oRes = IndustrialProtocolAdapter.adaptOpcUa(rawOpc, 'CASING-101B', 'PSI');
    setAdaptedOpcUa(oRes);
  };

  // ==========================================================================
  // STATE: 7. SINGLETON PATTERN DIAGNOSTIC
  // ==========================================================================
  const [singletonDiag, setSingletonDiag] = useState<any>(null);
  const runSingletonCheck = () => {
    const store = ScadaTelemetryStore.getInstance();
    setSingletonDiag(store.getDiagnostics());
  };

  // ==========================================================================
  // STATE: JWT AUTHENTICATION
  // ==========================================================================
  const [selectedOperatorCode, setSelectedOperatorCode] = useState<string>('OP-449');
  const [jwtTokenData, setJwtTokenData] = useState<DecodedJwt | null>(null);
  const [rawJwtString, setRawJwtString] = useState<string>('');
  const [jwtFeedback, setJwtFeedback] = useState<string>('');
  const [copiedJwt, setCopiedJwt] = useState<boolean>(false);

  useEffect(() => {
    // Generar o cargar token inicial
    const existing = jwtAuth.getToken();
    if (existing) {
      setRawJwtString(existing);
      setJwtTokenData(jwtAuth.decodeToken(existing));
    } else {
      const mock = jwtAuth.createMockJwt('OP-449');
      setRawJwtString(mock.token);
      setJwtTokenData(mock.decoded);
    }
  }, []);

  const handleGenerateJwt = async () => {
    setJwtFeedback('Generando token criptográfico con PHP backend...');
    const result = await jwtAuth.loginWithPhpBackend(selectedOperatorCode, 28800);
    if (result.success && result.token) {
      setRawJwtString(result.token);
      setJwtTokenData(jwtAuth.decodeToken(result.token));
      setJwtFeedback('✓ Token emitido exitosamente con firma HMAC SHA-256 (RFC 7519).');
    } else {
      setJwtFeedback('Error al emitir token: ' + (result.error || 'Servidor no responde'));
    }
  };

  const copyTokenToClipboard = () => {
    if (rawJwtString) {
      navigator.clipboard.writeText(rawJwtString);
      setCopiedJwt(true);
      setTimeout(() => setCopiedJwt(false), 2000);
    }
  };

  // ==========================================================================
  // STATE: ANTIPATTERNS AUDIT SCANNER
  // ==========================================================================
  const [antiFilter, setAntiFilter] = useState<'all' | 'CRÍTICO' | 'ALTO' | 'MEDIO'>('all');
  const [selectedAntiPattern, setSelectedAntiPattern] = useState<AntiPatternItem>(ANTIPATTERNS_DATA[0]);

  const filteredAntiPatterns = antiFilter === 'all'
    ? ANTIPATTERNS_DATA
    : ANTIPATTERNS_DATA.filter((a) => a.severity === antiFilter);

  return (
    <div className="flex-1 flex flex-col gap-5 p-4 lg:p-6 max-w-[1780px] mx-auto w-full overflow-y-auto font-sans">
      
      {/* ================================================================== */}
      {/* 1. ENCABEZADO PRINCIPAL DIDÁCTICO Y ACADÉMICO                      */}
      {/* ================================================================== */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-slate-900/90 rounded-xl border border-slate-800 p-5 shadow-lg backdrop-blur-md">
        <div>
          <div className="flex items-center gap-2 mb-1.5 flex-wrap">
            <span className="text-xs font-mono-data bg-emerald-500/15 text-emerald-400 px-2.5 py-0.5 rounded border border-emerald-500/30 font-bold uppercase tracking-wider">
              SENA ADSO · Ficha 3413988
            </span>
            <span className="text-xs font-mono-data bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded border border-sky-500/20">
              Regional Huila · Proyecto MAPAF
            </span>
            <span className="text-xs font-mono-data text-slate-400">
              Instructor: Javier Humberto Pinto Díaz
            </span>
          </div>
          <h1 className="font-display text-2xl font-bold tracking-tight text-white flex items-center gap-2.5">
            <Layers className="w-6 h-6 text-emerald-400" />
            Patrones de Diseño (GoF), Antipatrones, Dominios DDD y JWT
          </h1>
          <p className="text-xs text-slate-300 mt-1 max-w-4xl">
            Sustentación técnica formal: Aplicación práctica de patrones creacionales, estructurales y de comportamiento, 
            identificación de antipatrones industriales, delimitación de los 7 Bounded Contexts y seguridad mediante JSON Web Tokens.
          </p>
          <div className="text-[11px] font-mono-data text-slate-400 mt-1.5 flex items-center gap-2">
            <span className="text-emerald-400 font-semibold">Integrantes:</span> Hernann Manchola · María José Conde · Emilin Vargas
          </div>
        </div>

        {/* Indicador de Conexión XAMPP / PHP / MySQL */}
        <div className="flex items-center gap-3">
          <div className={`flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-mono-data ${
            xamppConnected
              ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300'
              : 'bg-amber-500/10 border-amber-500/30 text-amber-300'
          }`}>
            <Database className="w-4 h-4 text-emerald-400" />
            <div>
              <div className="font-bold">{xamppConnected ? 'XAMPP (PHP + MySQL) ONLINE' : 'Modo Simulado / XAMPP Offline'}</div>
              <div className="text-[10px] text-slate-400">{xamppConnected ? 'mapaf_db · PDO Singleton' : 'Haga clic para conectar XAMPP'}</div>
            </div>
          </div>

          {onOpenXamppModal && (
            <button
              onClick={onOpenXamppModal}
              className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg text-xs font-mono-data cursor-pointer transition-colors"
            >
              Diagnóstico XAMPP
            </button>
          )}
        </div>
      </div>

      {/* ================================================================== */}
      {/* 2. SELECTOR DE PESTAÑAS PRINCIPALES                                */}
      {/* ================================================================== */}
      <div className="flex flex-wrap gap-2 bg-slate-950 p-1.5 rounded-xl border border-slate-800 text-xs font-mono-data">
        <button
          onClick={() => setActiveMainTab('patterns')}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg font-bold transition-all cursor-pointer ${
            activeMainTab === 'patterns'
              ? 'bg-emerald-600 text-white shadow-[0_0_12px_rgba(16,185,129,0.35)]'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
          }`}
        >
          <Layers className="w-4 h-4" />
          <span>1. Patrones de Diseño (GoF)</span>
        </button>

        <button
          onClick={() => setActiveMainTab('antipatterns')}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg font-bold transition-all cursor-pointer ${
            activeMainTab === 'antipatterns'
              ? 'bg-rose-600 text-white shadow-[0_0_12px_rgba(244,63,94,0.35)]'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
          }`}
        >
          <AlertTriangle className="w-4 h-4" />
          <span>2. Antipatrones de Software (8)</span>
        </button>

        <button
          onClick={() => setActiveMainTab('domains')}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg font-bold transition-all cursor-pointer ${
            activeMainTab === 'domains'
              ? 'bg-sky-600 text-white shadow-[0_0_12px_rgba(14,165,233,0.35)]'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
          }`}
        >
          <Cpu className="w-4 h-4" />
          <span>3. Dominios del Sistema (DDD - 7)</span>
        </button>

        <button
          onClick={() => setActiveMainTab('jwt')}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg font-bold transition-all cursor-pointer ${
            activeMainTab === 'jwt'
              ? 'bg-purple-600 text-white shadow-[0_0_12px_rgba(168,85,247,0.35)]'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
          }`}
        >
          <Key className="w-4 h-4" />
          <span>4. Seguridad & JWT (RFC 7519)</span>
        </button>

        <button
          onClick={() => setActiveMainTab('xampp')}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg font-bold transition-all cursor-pointer ${
            activeMainTab === 'xampp'
              ? 'bg-amber-600 text-white shadow-[0_0_12px_rgba(245,158,11,0.35)]'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
          }`}
        >
          <Server className="w-4 h-4" />
          <span>5. Conexión XAMPP (PHP + MySQL)</span>
        </button>
      </div>

      {/* ================================================================== */}
      {/* SECCIÓN 1: PATRONES DE DISEÑO GOF & ARQUITECTURA                   */}
      {/* ================================================================== */}
      {activeMainTab === 'patterns' && (
        <div className="space-y-5 animate-fadeIn">
          {/* Sub-selector de Patrones para el Laboratorio Interactivo */}
          <div className="bg-slate-900 border border-slate-800 rounded-xl p-3 flex flex-wrap items-center justify-between gap-2 text-xs font-mono-data">
            <span className="text-slate-400 uppercase tracking-wider font-semibold text-[11px] ml-1">
              Laboratorio en Vivo:
            </span>
            <div className="flex flex-wrap gap-1.5">
              <button
                onClick={() => setActivePatternSubTab('command')}
                className={`px-3 py-1.5 rounded transition-all cursor-pointer ${
                  activePatternSubTab === 'command' ? 'bg-emerald-500 text-black font-bold' : 'text-slate-400 hover:text-white bg-slate-800/60'
                }`}
              >
                Command (con UNDO)
              </button>
              <button
                onClick={() => setActivePatternSubTab('observer')}
                className={`px-3 py-1.5 rounded transition-all cursor-pointer ${
                  activePatternSubTab === 'observer' ? 'bg-sky-500 text-black font-bold' : 'text-slate-400 hover:text-white bg-slate-800/60'
                }`}
              >
                Observer (Reactivo)
              </button>
              <button
                onClick={() => setActivePatternSubTab('strategy')}
                className={`px-3 py-1.5 rounded transition-all cursor-pointer ${
                  activePatternSubTab === 'strategy' ? 'bg-purple-500 text-white font-bold' : 'text-slate-400 hover:text-white bg-slate-800/60'
                }`}
              >
                Strategy (Caudal)
              </button>
              <button
                onClick={() => setActivePatternSubTab('factory')}
                className={`px-3 py-1.5 rounded transition-all cursor-pointer ${
                  activePatternSubTab === 'factory' ? 'bg-amber-500 text-black font-bold' : 'text-slate-400 hover:text-white bg-slate-800/60'
                }`}
              >
                Factory Method
              </button>
              <button
                onClick={() => setActivePatternSubTab('state')}
                className={`px-3 py-1.5 rounded transition-all cursor-pointer ${
                  activePatternSubTab === 'state' ? 'bg-rose-500 text-white font-bold' : 'text-slate-400 hover:text-white bg-slate-800/60'
                }`}
              >
                State (Pozo)
              </button>
              <button
                onClick={() => setActivePatternSubTab('adapter')}
                className={`px-3 py-1.5 rounded transition-all cursor-pointer ${
                  activePatternSubTab === 'adapter' ? 'bg-teal-500 text-black font-bold' : 'text-slate-400 hover:text-white bg-slate-800/60'
                }`}
              >
                Adapter (Modbus/OPC)
              </button>
              <button
                onClick={() => setActivePatternSubTab('singleton')}
                className={`px-3 py-1.5 rounded transition-all cursor-pointer ${
                  activePatternSubTab === 'singleton' ? 'bg-indigo-500 text-white font-bold' : 'text-slate-400 hover:text-white bg-slate-800/60'
                }`}
              >
                Singleton (MySQL PDO)
              </button>
              <button
                onClick={() => setActivePatternSubTab('matrix')}
                className={`px-3 py-1.5 rounded transition-all cursor-pointer ${
                  activePatternSubTab === 'matrix' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-white bg-slate-800/60'
                }`}
              >
                Matriz Académica SENA
              </button>
            </div>
          </div>

          {/* SUB-LAB 1: COMMAND PATTERN CON UNDO */}
          {activePatternSubTab === 'command' && (
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-5">
              <div className="lg:col-span-6 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <div className="flex items-center gap-2">
                    <Sliders className="w-5 h-5 text-emerald-400" />
                    <h3 className="font-display text-base font-bold text-white">Patrón Command: Maniobras de Pozo con UNDO</h3>
                  </div>
                  <span className="text-[10px] font-mono-data bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/30">
                    GoF Comportamiento
                  </span>
                </div>

                <p className="text-xs text-slate-300">
                  Encapsula cada maniobra de sala de control como un objeto autónomo. Proporciona validación de seguridad, 
                  trazabilidad inmutable en la bitácora y la capacidad matemática de <strong>revertir (UNDO)</strong> a un estado anterior seguro.
                </p>

                {/* Controles de Maniobra */}
                <div className="p-4 bg-slate-950 rounded-lg border border-slate-800 space-y-4">
                  <div>
                    <div className="flex justify-between text-xs font-mono-data mb-1.5">
                      <span className="text-slate-400">Apertura Estrangulador Choke (Alfa-12):</span>
                      <span className="text-emerald-400 font-bold">{chokeValue.toFixed(1)}%</span>
                    </div>
                    <div className="flex gap-2">
                      <button
                        onClick={() => handleAdjustChoke(25.0)}
                        className="flex-1 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-mono-data text-white rounded cursor-pointer transition-colors"
                      >
                        Choke 25% (Estrangular)
                      </button>
                      <button
                        onClick={() => handleAdjustChoke(50.0)}
                        className="flex-1 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-mono-data text-white rounded cursor-pointer transition-colors"
                      >
                        Choke 50% (Medio)
                      </button>
                      <button
                        onClick={() => handleAdjustChoke(75.0)}
                        className="flex-1 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-mono-data text-white rounded cursor-pointer transition-colors"
                      >
                        Choke 75% (Flujo Alto)
                      </button>
                    </div>
                  </div>

                  <div className="flex items-center justify-between pt-2 border-t border-slate-800">
                    <div>
                      <div className="text-xs font-bold text-slate-200">Válvula de Seguridad SSV (Bravo-04)</div>
                      <div className="text-[11px] font-mono-data text-slate-400">
                        Estado actual: <span className={ssvValveOpen ? 'text-emerald-400 font-bold' : 'text-rose-400 font-bold'}>{ssvValveOpen ? 'ABIERTA' : 'CERRADA'}</span>
                      </div>
                    </div>
                    <button
                      onClick={handleToggleValve}
                      className={`px-3 py-1.5 rounded text-xs font-mono-data font-bold cursor-pointer transition-colors ${
                        ssvValveOpen ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 hover:bg-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30'
                      }`}
                    >
                      {ssvValveOpen ? 'Cerrar Válvula SSV' : 'Abrir Válvula SSV'}
                    </button>
                  </div>

                  {/* Botón UNDO Maestro */}
                  <div className="pt-2 border-t border-slate-800 flex items-center justify-between">
                    <span className="text-xs text-slate-400 font-mono-data">Pila de maniobras: {commandLogs.length} ejecutadas</span>
                    <button
                      onClick={handleUndo}
                      disabled={commandLogs.length === 0}
                      className="flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-30 disabled:pointer-events-none text-slate-950 rounded text-xs font-mono-data font-bold cursor-pointer transition-colors shadow"
                    >
                      <RotateCcw className="w-3.5 h-3.5" />
                      <span>Deshacer Maniobra (UNDO)</span>
                    </button>
                  </div>
                </div>

                <div className="p-3 bg-slate-950/70 border-l-3 border-emerald-500 rounded text-xs text-slate-300 font-mono-data">
                  <strong>Estado:</strong> {commandFeedback}
                </div>
              </div>

              {/* Historial en Vivo del Invoker */}
              <div className="lg:col-span-6 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <h3 className="font-display text-sm font-bold text-white flex items-center gap-2">
                    <RotateCcw className="w-4 h-4 text-amber-400" />
                    Pila de Historial del Command Invoker
                  </h3>
                  <span className="text-[11px] font-mono-data text-slate-400">Total: {commandLogs.length}</span>
                </div>

                {commandLogs.length === 0 ? (
                  <div className="text-center py-10 text-xs text-slate-400 font-mono-data">
                    Ejecute un ajuste de Choke o conmute la válvula SSV para ver la pila de comandos en tiempo real.
                  </div>
                ) : (
                  <div className="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                    {commandLogs.map((cmd, idx) => (
                      <div key={cmd.id + idx} className="p-2.5 bg-slate-950 rounded border border-slate-800 flex items-center justify-between text-xs font-mono-data">
                        <div>
                          <div className="text-white font-semibold flex items-center gap-2">
                            <span className="text-emerald-400">#{commandLogs.length - idx}</span>
                            <span>{cmd.description}</span>
                          </div>
                          <div className="text-[10px] text-slate-400 mt-0.5">
                            Operador: {cmd.operator} · ID: {cmd.id}
                          </div>
                        </div>
                        <span className="text-[10px] text-slate-400 bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                          {cmd.timestamp}
                        </span>
                      </div>
                    ))}
                  </div>
                )}

                <div className="text-[11px] text-slate-400 p-3 bg-slate-950/50 rounded border border-slate-800/60">
                  <strong>Justificación SENA:</strong> El patrón Command desacopla al emisor de la orden (Frontend / Operador) del receptor (Actuador Choke / MySQL). 
                  Al mantener un histórico interno de objetos con método <code className="text-emerald-400">undo()</code>, el sistema revierte cambios erróneos sin reiniciar la planta.
                </div>
              </div>
            </div>
          )}

          {/* SUB-LAB 2: OBSERVER PATTERN */}
          {activePatternSubTab === 'observer' && (
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-5">
              <div className="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <div className="flex items-center gap-2">
                    <Radio className="w-5 h-5 text-sky-400" />
                    <h3 className="font-display text-base font-bold text-white">Patrón Observer: Telemetría Reactiva</h3>
                  </div>
                  <span className="text-[10px] font-mono-data bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded border border-sky-500/30">
                    GoF Comportamiento
                  </span>
                </div>

                <p className="text-xs text-slate-300">
                  El sensor físico <code>WHTP-101A</code> actúa como <strong>Subject</strong>. Al fluctuar la presión, notifica 
                  automáticamente a los 3 observadores suscritos sin conocer sus detalles de implementación.
                </p>

                <div className="p-4 bg-slate-950 rounded-lg border border-slate-800 space-y-3">
                  <div className="flex justify-between text-xs font-mono-data">
                    <span className="text-slate-400">Presión Simulada (WHTP-101A):</span>
                    <span className={`font-bold text-sm ${testPressure >= 2400 ? 'text-rose-400 animate-pulse' : testPressure >= 2000 ? 'text-amber-400' : 'text-emerald-400'}`}>
                      {testPressure} PSI
                    </span>
                  </div>

                  <input
                    type="range"
                    min="1200"
                    max="2800"
                    step="25"
                    value={testPressure}
                    onChange={(e) => handlePressureChange(Number(e.target.value))}
                    className="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-sky-500"
                  />

                  <div className="flex justify-between text-[10px] font-mono-data text-slate-400">
                    <span>1,200 PSI (Baja)</span>
                    <span className="text-amber-400">2,000 PSI (Warning)</span>
                    <span className="text-rose-400">2,400 PSI (Trip ESD)</span>
                    <span>2,800 PSI</span>
                  </div>
                </div>

                <div className="space-y-2">
                  <div className="text-xs font-bold text-slate-300">Observadores Conectados al Subject:</div>
                  <div className="p-2.5 bg-slate-950 rounded border border-slate-800 text-xs font-mono-data space-y-1">
                    <div className="text-emerald-400 flex items-center gap-1.5">
                      <span className="w-2 h-2 rounded-full bg-emerald-500" />
                      1. AlarmObserver (Dispara alarma ISA-18.2 si &gt; 2000 PSI)
                    </div>
                    <div className="text-sky-400 flex items-center gap-1.5">
                      <span className="w-2 h-2 rounded-full bg-sky-500" />
                      2. AuditLoggerObserver (Graba en bitacora_auditoria en MySQL)
                    </div>
                    <div className="text-rose-400 flex items-center gap-1.5">
                      <span className="w-2 h-2 rounded-full bg-rose-500" />
                      3. SafetyInterlockObserver (Dispara ESD-1 si &gt; 2400 PSI)
                    </div>
                  </div>
                </div>
              </div>

              {/* Registro de Eventos Emitidos */}
              <div className="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <h3 className="font-display text-sm font-bold text-white flex items-center gap-2">
                    <Zap className="w-4 h-4 text-emerald-400" />
                    Flujo de Eventos Reactivos Recibidos
                  </h3>
                  <span className="text-[11px] font-mono-data text-slate-400">Eventos: {observerEvents.length}</span>
                </div>

                {observerEvents.length === 0 ? (
                  <div className="text-center py-12 text-xs text-slate-400 font-mono-data">
                    Mueva el control deslizante de presión hacia arriba de 2,000 PSI o 2,400 PSI para ver reaccionar a los observadores en tiempo real.
                  </div>
                ) : (
                  <div className="space-y-2 max-h-[320px] overflow-y-auto pr-1">
                    {observerEvents.map((ev, i) => (
                      <div
                        key={i}
                        className={`p-2.5 rounded border text-xs font-mono-data ${
                          ev.severity === 'critical'
                            ? 'bg-rose-950/30 border-rose-500/40 text-rose-200'
                            : ev.severity === 'warning'
                            ? 'bg-amber-950/30 border-amber-500/40 text-amber-200'
                            : 'bg-sky-950/30 border-sky-500/40 text-sky-200'
                        }`}
                      >
                        <div className="flex justify-between items-center text-[10px] opacity-75 mb-1">
                          <span className="font-bold uppercase tracking-wider">{ev.observer}</span>
                          <span>{ev.time}</span>
                        </div>
                        <div>{ev.text}</div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          )}

          {/* SUB-LAB 3: STRATEGY PATTERN */}
          {activePatternSubTab === 'strategy' && (
            <div className="space-y-4">
              <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <div className="flex items-center gap-2">
                    <Cpu className="w-5 h-5 text-purple-400" />
                    <div>
                      <h3 className="font-display text-base font-bold text-white">Patrón Strategy: Cálculo Fiscal de Caudal de Crudo</h3>
                      <p className="text-xs text-slate-400">Alterna dinámicamente entre familias de algoritmos termodinámicos sin modificar el código de la estación SCADA.</p>
                    </div>
                  </div>
                  <span className="text-[10px] font-mono-data bg-purple-500/10 text-purple-400 px-2 py-0.5 rounded border border-purple-500/30">
                    GoF Comportamiento
                  </span>
                </div>

                {/* Parámetros de Entrada de Proceso */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-3 bg-slate-950 p-3.5 rounded-lg border border-slate-800 text-xs font-mono-data">
                  <div>
                    <label className="text-slate-400 block mb-1">Presión Tubing (WHTP):</label>
                    <input
                      type="number"
                      value={stratWhtp}
                      onChange={(e) => setStratWhtp(Number(e.target.value))}
                      className="w-full bg-slate-900 border border-slate-700 text-white p-1.5 rounded"
                    />
                    <span className="text-[10px] text-slate-400">PSI</span>
                  </div>
                  <div>
                    <label className="text-slate-400 block mb-1">Temperatura de Línea:</label>
                    <input
                      type="number"
                      value={stratTemp}
                      onChange={(e) => setStratTemp(Number(e.target.value))}
                      className="w-full bg-slate-900 border border-slate-700 text-white p-1.5 rounded"
                    />
                    <span className="text-[10px] text-slate-400">°C</span>
                  </div>
                  <div>
                    <label className="text-slate-400 block mb-1">Apertura Choke:</label>
                    <input
                      type="number"
                      value={stratChoke}
                      onChange={(e) => setStratChoke(Number(e.target.value))}
                      className="w-full bg-slate-900 border border-slate-700 text-white p-1.5 rounded"
                    />
                    <span className="text-[10px] text-slate-400">%</span>
                  </div>
                  <div>
                    <label className="text-slate-400 block mb-1">Frecuencia Bomba ESP:</label>
                    <input
                      type="number"
                      value={stratEsp}
                      onChange={(e) => setStratEsp(Number(e.target.value))}
                      className="w-full bg-slate-900 border border-slate-700 text-white p-1.5 rounded"
                    />
                    <span className="text-[10px] text-slate-400">Hz</span>
                  </div>
                </div>

                {/* Comparación de las 3 Estrategias Lado a Lado */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  {/* Estrategia 1: Coriolis */}
                  <div className="p-4 bg-slate-950 rounded-xl border border-emerald-500/40 space-y-3 relative overflow-hidden">
                    <div className="text-[10px] font-mono-data bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/30 inline-block font-bold">
                      RECOMENDADO · FISCAL
                    </div>
                    <h4 className="font-display font-bold text-sm text-white">{coriolisRes.algorithmName}</h4>
                    <div className="text-[11px] font-mono-data text-slate-400">{coriolisRes.standard}</div>

                    <div className="p-3 bg-slate-900 rounded border border-slate-800 space-y-1 text-xs font-mono-data">
                      <div className="flex justify-between">
                        <span className="text-slate-400">Crudo Neto:</span>
                        <span className="text-emerald-400 font-bold text-sm">{coriolisRes.netOilBpd.toLocaleString()} BPD</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-slate-400">Gas Asociado:</span>
                        <span className="text-slate-200">{coriolisRes.gasMscfd.toLocaleString()} MSCFD</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-slate-400">Incertidumbre:</span>
                        <span className="text-emerald-400 font-bold">±{coriolisRes.uncertaintyPercent}%</span>
                      </div>
                    </div>
                    <p className="text-[11px] text-slate-400">{coriolisRes.description}</p>
                  </div>

                  {/* Estrategia 2: AGA-3 */}
                  <div className="p-4 bg-slate-950 rounded-xl border border-sky-500/40 space-y-3">
                    <div className="text-[10px] font-mono-data bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded border border-sky-500/30 inline-block font-bold">
                      NORMA DE ORIFICIO
                    </div>
                    <h4 className="font-display font-bold text-sm text-white">{aga3Res.algorithmName}</h4>
                    <div className="text-[11px] font-mono-data text-slate-400">{aga3Res.standard}</div>

                    <div className="p-3 bg-slate-900 rounded border border-slate-800 space-y-1 text-xs font-mono-data">
                      <div className="flex justify-between">
                        <span className="text-slate-400">Crudo Calculado:</span>
                        <span className="text-sky-400 font-bold text-sm">{aga3Res.netOilBpd.toLocaleString()} BPD</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-slate-400">Gas Asociado:</span>
                        <span className="text-slate-200">{aga3Res.gasMscfd.toLocaleString()} MSCFD</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-slate-400">Incertidumbre:</span>
                        <span className="text-sky-400 font-bold">±{aga3Res.uncertaintyPercent}%</span>
                      </div>
                    </div>
                    <p className="text-[11px] text-slate-400">{aga3Res.description}</p>
                  </div>

                  {/* Estrategia 3: Beggs & Brill */}
                  <div className="p-4 bg-slate-950 rounded-xl border border-purple-500/40 space-y-3">
                    <div className="text-[10px] font-mono-data bg-purple-500/10 text-purple-400 px-2 py-0.5 rounded border border-purple-500/30 inline-block font-bold">
                      MULTIFÁSICO
                    </div>
                    <h4 className="font-display font-bold text-sm text-white">{beggsRes.algorithmName}</h4>
                    <div className="text-[11px] font-mono-data text-slate-400">{beggsRes.standard}</div>

                    <div className="p-3 bg-slate-900 rounded border border-slate-800 space-y-1 text-xs font-mono-data">
                      <div className="flex justify-between">
                        <span className="text-slate-400">Crudo Estimado:</span>
                        <span className="text-purple-400 font-bold text-sm">{beggsRes.netOilBpd.toLocaleString()} BPD</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-slate-400">Gas Asociado:</span>
                        <span className="text-slate-200">{beggsRes.gasMscfd.toLocaleString()} MSCFD</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-slate-400">Incertidumbre:</span>
                        <span className="text-amber-400 font-bold">±{beggsRes.uncertaintyPercent}%</span>
                      </div>
                    </div>
                    <p className="text-[11px] text-slate-400">{beggsRes.description}</p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* SUB-LAB 4: FACTORY METHOD */}
          {activePatternSubTab === 'factory' && (
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-5">
              <div className="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <div className="flex items-center gap-2">
                    <Layers className="w-5 h-5 text-amber-400" />
                    <h3 className="font-display text-base font-bold text-white">Factory Method: Instanciador de Sensores</h3>
                  </div>
                  <span className="text-[10px] font-mono-data bg-amber-500/10 text-amber-400 px-2 py-0.5 rounded border border-amber-500/30">
                    GoF Creacional
                  </span>
                </div>

                <p className="text-xs text-slate-300">
                  Desacopla la lógica de instanciación. La clase <code>SensorMetricFactory</code> construye el objeto exacto 
                  con sus rangos de seguridad calibrados bajo la norma ANSI/ISA-18.2 sin acoplar la interfaz gráfica.
                </p>

                <div className="space-y-3 bg-slate-950 p-4 rounded-lg border border-slate-800 text-xs font-mono-data">
                  <div>
                    <label className="text-slate-400 block mb-1">Tipo de Instrumento:</label>
                    <select
                      value={factoryType}
                      onChange={(e: any) => setFactoryType(e.target.value)}
                      className="w-full bg-slate-900 border border-slate-700 text-white p-2 rounded"
                    >
                      <option value="pressure">Transmisor de Presión (PT - PSI)</option>
                      <option value="temperature">Termoresistencia (TT - °C)</option>
                      <option value="flow">Flujómetro Másico (FT - BPD)</option>
                      <option value="frequency">Variador de Frecuencia ESP (VFD - Hz)</option>
                      <option value="gor">Analizador Gas-Oil Ratio (GOR - SCF/BBL)</option>
                    </select>
                  </div>

                  <div>
                    <label className="text-slate-400 block mb-1">Tag del Instrumento:</label>
                    <input
                      type="text"
                      value={factoryTag}
                      onChange={(e) => setFactoryTag(e.target.value)}
                      className="w-full bg-slate-900 border border-slate-700 text-white p-2 rounded"
                    />
                  </div>

                  <div>
                    <label className="text-slate-400 block mb-1">Etiqueta Descriptiva:</label>
                    <input
                      type="text"
                      value={factoryLabel}
                      onChange={(e) => setFactoryLabel(e.target.value)}
                      className="w-full bg-slate-900 border border-slate-700 text-white p-2 rounded"
                    />
                  </div>

                  <button
                    onClick={handleSpawnSensor}
                    className="w-full py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded cursor-pointer transition-colors shadow flex items-center justify-center gap-2"
                  >
                    <Play className="w-3.5 h-3.5" />
                    <span>Instanciar con SensorMetricFactory</span>
                  </button>
                </div>
              </div>

              {/* Lista de Instrumentos Creados por la Fábrica */}
              <div className="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <h3 className="font-display text-sm font-bold text-white">Objetos Creados por el Factory ({spawnedSensors.length})</h3>
                  <span className="text-[11px] font-mono-data text-emerald-400">Calibrados bajo ISA-18.2</span>
                </div>

                <div className="space-y-2.5 max-h-[340px] overflow-y-auto pr-1">
                  {spawnedSensors.map((s, idx) => (
                    <div key={idx} className="p-3 bg-slate-950 rounded-lg border border-slate-800 text-xs font-mono-data space-y-1.5">
                      <div className="flex justify-between items-center">
                        <span className="text-amber-400 font-bold text-sm">{s.tag}</span>
                        <span className="text-slate-400 text-[10px]">{s.unit}</span>
                      </div>
                      <div className="text-slate-200">{s.label}</div>
                      <div className="grid grid-cols-4 gap-2 pt-2 border-t border-slate-800/80 text-[10px] text-slate-400">
                        <div>Objetivo: <span className="text-white font-bold">{s.target}</span></div>
                        <div>Trip Bajo: <span className="text-sky-400">{s.lowTrip}</span></div>
                        <div>Alarma Alta: <span className="text-amber-400">{s.highWarning}</span></div>
                        <div>Trip Alto: <span className="text-rose-400">{s.highTrip}</span></div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* SUB-LAB 5: STATE PATTERN */}
          {activePatternSubTab === 'state' && (
            <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
              <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                <div className="flex items-center gap-2">
                  <Shield className="w-5 h-5 text-rose-400" />
                  <div>
                    <h3 className="font-display text-base font-bold text-white">Patrón State: Ciclo de Vida y Seguridad del Cabezal de Pozo</h3>
                    <p className="text-xs text-slate-400">Modela los estados operacionales (Norma API 6A) e impide transiciones ilegales por enclavamiento.</p>
                  </div>
                </div>
                <span className="text-[10px] font-mono-data bg-rose-500/10 text-rose-400 px-2 py-0.5 rounded border border-rose-500/30">
                  GoF Comportamiento
                </span>
              </div>

              {/* Diagrama Visual de Estados */}
              <div className="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs font-mono-data">
                <div className={`p-4 rounded-xl border text-center transition-all ${
                  wellCurrentState === 'PRODUCIENDO'
                    ? 'bg-emerald-500/20 border-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.3)]'
                    : 'bg-slate-950 border-slate-800 opacity-60'
                }`}>
                  <div className="font-bold text-emerald-400 text-sm mb-1">1. PRODUCIENDO</div>
                  <div className="text-[11px] text-slate-300">Flujo nominal activo</div>
                  <div className="text-[10px] text-emerald-500 mt-2 font-bold">{wellCurrentState === 'PRODUCIENDO' ? '● ESTADO ACTIVO' : ''}</div>
                </div>

                <div className={`p-4 rounded-xl border text-center transition-all ${
                  wellCurrentState === 'ESTRANGULADO'
                    ? 'bg-amber-500/20 border-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.3)]'
                    : 'bg-slate-950 border-slate-800 opacity-60'
                }`}>
                  <div className="font-bold text-amber-400 text-sm mb-1">2. ESTRANGULADO</div>
                  <div className="text-[11px] text-slate-300">Choke restringido &lt; 25%</div>
                  <div className="text-[10px] text-amber-500 mt-2 font-bold">{wellCurrentState === 'ESTRANGULADO' ? '● ESTADO ACTIVO' : ''}</div>
                </div>

                <div className={`p-4 rounded-xl border text-center transition-all ${
                  wellCurrentState === 'DISPARADO'
                    ? 'bg-rose-500/20 border-rose-500 shadow-[0_0_15px_rgba(244,63,94,0.3)]'
                    : 'bg-slate-950 border-slate-800 opacity-60'
                }`}>
                  <div className="font-bold text-rose-400 text-sm mb-1">3. DISPARADO (TRIP)</div>
                  <div className="text-[11px] text-slate-300">Válvulas SSV cerradas por ESD</div>
                  <div className="text-[10px] text-rose-500 mt-2 font-bold">{wellCurrentState === 'DISPARADO' ? '● ESTADO ACTIVO' : ''}</div>
                </div>

                <div className={`p-4 rounded-xl border text-center transition-all ${
                  wellCurrentState === 'CERRADO'
                    ? 'bg-slate-700/40 border-slate-500 shadow-[0_0_15px_rgba(148,163,184,0.3)]'
                    : 'bg-slate-950 border-slate-800 opacity-60'
                }`}>
                  <div className="font-bold text-slate-300 text-sm mb-1">4. CERRADO (SHUT-IN)</div>
                  <div className="text-[11px] text-slate-400">Estático listo para arranque</div>
                  <div className="text-[10px] text-slate-400 mt-2 font-bold">{wellCurrentState === 'CERRADO' ? '● ESTADO ACTIVO' : ''}</div>
                </div>
              </div>

              {/* Botones de Transición para Probar Invariantes de Seguridad */}
              <div className="p-4 bg-slate-950 rounded-lg border border-slate-800 space-y-3">
                <div className="text-xs font-bold text-slate-300">Probar Transiciones de Estado en el Pozo Alfa-12:</div>
                <div className="flex flex-wrap gap-2 text-xs font-mono-data">
                  <button
                    onClick={() => handleStateTransition('ESTRANGULADO', 'Ajuste manual de Choke a 18%')}
                    className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-500/30 rounded cursor-pointer transition-colors"
                  >
                    Estrangular Pozo (&lt; 25%)
                  </button>
                  <button
                    onClick={() => handleStateTransition('DISPARADO', 'Sobrepresión WHTP > 2400 PSI')}
                    className="px-3 py-1.5 bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 rounded cursor-pointer transition-colors"
                  >
                    Simular Disparo de Alarma (TRIP)
                  </button>
                  <button
                    onClick={() => handleStateTransition('CERRADO', 'Rearme de seguridad y parada de pozo')}
                    className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-600 rounded cursor-pointer transition-colors"
                  >
                    Rearmar a Cerrado (Shut-In)
                  </button>
                  <button
                    onClick={() => handleStateTransition('PRODUCIENDO', 'Apertura de válvula maestra SSV')}
                    className="px-3 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 rounded cursor-pointer transition-colors"
                  >
                    Abrir Válvula SSV y Arrancar
                  </button>
                </div>

                <div className="p-3 bg-slate-900 rounded border border-slate-800 text-xs font-mono-data text-slate-300">
                  <strong>Resultado de la transición:</strong> {stateMessage}
                </div>
              </div>
            </div>
          )}

          {/* SUB-LAB 6: ADAPTER & 7: SINGLETON */}
          {(activePatternSubTab === 'adapter' || activePatternSubTab === 'singleton') && (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              {/* Adapter Pattern */}
              <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <div className="flex items-center gap-2">
                    <RefreshCw className="w-5 h-5 text-teal-400" />
                    <h3 className="font-display text-sm font-bold text-white">Patrón Adapter: Protocolos Industriales</h3>
                  </div>
                  <span className="text-[10px] font-mono-data bg-teal-500/10 text-teal-400 px-2 py-0.5 rounded border border-teal-500/30">
                    GoF Estructural
                  </span>
                </div>

                <p className="text-xs text-slate-300">
                  Convierte tramas binarias de protocolos industriales heterogéneos (Modbus RTU registros 16-bit hex y paquetes OPC-UA)
                  al formato unificado JSON de telemetría de MAPAF SCADA.
                </p>

                <button
                  onClick={runAdapterDemo}
                  className="w-full py-2 bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs font-mono-data rounded cursor-pointer transition-colors shadow"
                >
                  Ejecutar Adaptación de Tramas Modbus y OPC-UA
                </button>

                {adaptedModbus && (
                  <div className="p-3 bg-slate-950 rounded border border-slate-800 text-xs font-mono-data space-y-1.5">
                    <div className="text-teal-400 font-bold">✓ Trama Modbus RTU Convertida:</div>
                    <pre className="text-[11px] text-slate-300 overflow-x-auto bg-slate-900 p-2 rounded">
                      {JSON.stringify(adaptedModbus, null, 2)}
                    </pre>
                  </div>
                )}
              </div>

              {/* Singleton Pattern */}
              <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                  <div className="flex items-center gap-2">
                    <Database className="w-5 h-5 text-indigo-400" />
                    <h3 className="font-display text-sm font-bold text-white">Patrón Singleton: Conexión Única MySQL PDO</h3>
                  </div>
                  <span className="text-[10px] font-mono-data bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded border border-indigo-500/30">
                    GoF Creacional
                  </span>
                </div>

                <p className="text-xs text-slate-300">
                  Tanto en PHP (<code>DatabaseSingleton.php</code>) como en el frontend de TypeScript, el Singleton restringe la instanciación 
                  a una única conexión compartida para todo el ciclo de vida, previniendo saturación de conexiones en el servidor MySQL.
                </p>

                <button
                  onClick={runSingletonCheck}
                  className="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs font-mono-data rounded cursor-pointer transition-colors shadow"
                >
                  Verificar Estado e Invariante del Singleton
                </button>

                {singletonDiag && (
                  <div className="p-3 bg-slate-950 rounded border border-slate-800 text-xs font-mono-data space-y-1.5">
                    <div className="text-indigo-400 font-bold">✓ Diagnóstico del Singleton Activo:</div>
                    <div className="text-slate-300">ID de Conexión: <span className="text-white font-bold">{singletonDiag.connectionId}</span></div>
                    <div className="text-slate-300">Llamadas Acumuladas: <span className="text-emerald-400 font-bold">{singletonDiag.accessCounter}</span></div>
                    <div className="text-slate-300">Instancias Reales en Memoria: <span className="text-white font-bold">1 (Única)</span></div>
                    <div className="text-[10px] text-slate-400 mt-1">Garantiza que múltiples controladores reutilicen el mismo recurso sin reconectar.</div>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* SUB-LAB 8: MATRIZ COMPARATIVA ACADÉMICA SENA ADSO */}
          {activePatternSubTab === 'matrix' && (
            <div className="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg">
              <div className="p-4 bg-slate-950/80 border-b border-slate-800 flex justify-between items-center">
                <h3 className="font-display text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                  <Layers className="w-4 h-4 text-emerald-400" />
                  Matriz Académica de Patrones de Software SENA ADSO (Ficha 3413988)
                </h3>
                <span className="text-[11px] font-mono-data text-emerald-400">8 PATRONES IMPLEMENTADOS</span>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs text-slate-300 border-collapse">
                  <thead>
                    <tr className="bg-slate-950/40 border-b border-slate-800 text-[11px] font-mono-data uppercase text-slate-400">
                      <th className="p-3.5">Patrón GoF</th>
                      <th className="p-3.5 text-sky-400">Categoría</th>
                      <th className="p-3.5 text-purple-400">Clase en PHP (Backend)</th>
                      <th className="p-3.5 text-amber-400">Clase en TypeScript (Frontend)</th>
                      <th className="p-3.5 text-emerald-400">Problema Técnico que Resuelve en SCADA</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/60 font-sans">
                    <tr className="hover:bg-slate-800/30">
                      <td className="p-3.5 font-bold text-white">Singleton</td>
                      <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono-data text-[10px]">Creacional</span></td>
                      <td className="p-3.5 font-mono-data text-purple-300">DatabaseSingleton.php</td>
                      <td className="p-3.5 font-mono-data text-amber-300">ScadaTelemetryStore.ts</td>
                      <td className="p-3.5 text-slate-300">Evita saturación del pool de conexiones MySQL mapaf_db creando una única instancia PDO.</td>
                    </tr>
                    <tr className="hover:bg-slate-800/30">
                      <td className="p-3.5 font-bold text-white">Factory Method</td>
                      <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono-data text-[10px]">Creacional</span></td>
                      <td className="p-3.5 font-mono-data text-purple-300">SensorFactory.php</td>
                      <td className="p-3.5 font-mono-data text-amber-300">SensorMetricFactory.ts</td>
                      <td className="p-3.5 text-slate-300">Instancia instrumentos (PT, TT, FT, VFD) con sus rangos de calibración y umbrales ISA-18.2 encapsulados.</td>
                    </tr>
                    <tr className="hover:bg-slate-800/30">
                      <td className="p-3.5 font-bold text-white">Observer</td>
                      <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-sky-500/10 text-sky-400 border border-sky-500/20 font-mono-data text-[10px]">Comportamiento</span></td>
                      <td className="p-3.5 font-mono-data text-purple-300">TelemetrySubject.php</td>
                      <td className="p-3.5 font-mono-data text-amber-300">TelemetryPublisher.ts</td>
                      <td className="p-3.5 text-slate-300">Desacopla la adquisición de telemetría: notifica reactivamente a alarmas, bitácora y enclavamientos SIS.</td>
                    </tr>
                    <tr className="hover:bg-slate-800/30">
                      <td className="p-3.5 font-bold text-white">Command</td>
                      <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-sky-500/10 text-sky-400 border border-sky-500/20 font-mono-data text-[10px]">Comportamiento</span></td>
                      <td className="p-3.5 font-mono-data text-purple-300">CommandManager.php</td>
                      <td className="p-3.5 font-mono-data text-amber-300">ScadaCommandManager.ts</td>
                      <td className="p-3.5 text-slate-300">Encapsula maniobras críticas de estrangulador y válvulas con capacidad de reversión (UNDO) y auditoría.</td>
                    </tr>
                    <tr className="hover:bg-slate-800/30">
                      <td className="p-3.5 font-bold text-white">Strategy</td>
                      <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-sky-500/10 text-sky-400 border border-sky-500/20 font-mono-data text-[10px]">Comportamiento</span></td>
                      <td className="p-3.5 font-mono-data text-purple-300">FlowCalculationStrategy.php</td>
                      <td className="p-3.5 font-mono-data text-amber-300">CoriolisStrategy.ts</td>
                      <td className="p-3.5 text-slate-300">Alterna algoritmos de fiscalización (Coriolis AGA-11, Placa AGA-3, Beggs & Brill) según disponibilidad de sensores.</td>
                    </tr>
                    <tr className="hover:bg-slate-800/30">
                      <td className="p-3.5 font-bold text-white">State</td>
                      <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-sky-500/10 text-sky-400 border border-sky-500/20 font-mono-data text-[10px]">Comportamiento</span></td>
                      <td className="p-3.5 font-mono-data text-purple-300">WellheadState.php</td>
                      <td className="p-3.5 font-mono-data text-amber-300">WellheadStateMachine.ts</td>
                      <td className="p-3.5 text-slate-300">Gestiona transiciones de seguridad de pozo (API 6A) e impide arrancar un pozo disparado sin rearme previo.</td>
                    </tr>
                    <tr className="hover:bg-slate-800/30">
                      <td className="p-3.5 font-bold text-white">Adapter</td>
                      <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-teal-500/10 text-teal-400 border border-teal-500/20 font-mono-data text-[10px]">Estructural</span></td>
                      <td className="p-3.5 font-mono-data text-purple-300">IndustrialProtocolAdapter.php</td>
                      <td className="p-3.5 font-mono-data text-amber-300">IndustrialProtocolAdapter.ts</td>
                      <td className="p-3.5 text-slate-300">Convierte registros binarios Modbus RTU de 16 bits y nodos OPC-UA al formato unificado JSON de MAPAF.</td>
                    </tr>
                    <tr className="hover:bg-slate-800/30">
                      <td className="p-3.5 font-bold text-white">Repository</td>
                      <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 border border-purple-500/20 font-mono-data text-[10px]">Arquitectónico</span></td>
                      <td className="p-3.5 font-mono-data text-purple-300">WellheadRepository.php</td>
                      <td className="p-3.5 font-mono-data text-amber-300">mapafApi.ts</td>
                      <td className="p-3.5 text-slate-300">Desacopla la lógica de negocio de las consultas directas SQL PDO a MySQL mapaf_db en XAMPP.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}

      {/* ================================================================== */}
      {/* SECCIÓN 2: ANTIPATRONES DE SOFTWARE                                */}
      {/* ================================================================== */}
      {activeMainTab === 'antipatterns' && (
        <div className="space-y-5 animate-fadeIn">
          {/* Banner Didáctico de Antipatrones */}
          <div className="p-4 bg-rose-950/20 border-l-4 border-rose-500 rounded-r-xl text-xs text-slate-300 space-y-1">
            <strong className="text-rose-400 font-bold">Criterio de Evaluación SENA:</strong>
            <p>
              Un antipatrón es un patrón de diseño recurrente que genera consecuencias negativas (deuda técnica, vulnerabilidades de ciberseguridad industrial, caídas de planta). 
              A continuación se documentan los 8 antipatrones identificados y cómo la arquitectura de MAPAF los previene con soluciones formales.
            </p>
          </div>

          {/* Filtros de Severidad */}
          <div className="flex items-center gap-2 text-xs font-mono-data bg-slate-900 p-2.5 rounded-lg border border-slate-800">
            <span className="text-slate-400 mr-2">Filtrar Severidad:</span>
            <button
              onClick={() => setAntiFilter('all')}
              className={`px-3 py-1 rounded cursor-pointer transition-colors ${antiFilter === 'all' ? 'bg-slate-700 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
            >
              Todos (8)
            </button>
            <button
              onClick={() => setAntiFilter('CRÍTICO')}
              className={`px-3 py-1 rounded cursor-pointer transition-colors ${antiFilter === 'CRÍTICO' ? 'bg-rose-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
            >
              🔴 Críticos (4)
            </button>
            <button
              onClick={() => setAntiFilter('ALTO')}
              className={`px-3 py-1 rounded cursor-pointer transition-colors ${antiFilter === 'ALTO' ? 'bg-amber-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
            >
              🟡 Altos (2)
            </button>
            <button
              onClick={() => setAntiFilter('MEDIO')}
              className={`px-3 py-1 rounded cursor-pointer transition-colors ${antiFilter === 'MEDIO' ? 'bg-sky-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
            >
              🔵 Medios (2)
            </button>
          </div>

          {/* Grid de Antipatrones */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {filteredAntiPatterns.map((item) => (
              <div
                key={item.id}
                className="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3.5 shadow-md hover:border-slate-700 transition-colors"
              >
                <div className="flex justify-between items-start gap-2">
                  <div>
                    <span className="text-[10px] font-mono-data text-slate-400 uppercase tracking-wider block mb-1">
                      {item.category}
                    </span>
                    <h3 className="font-display font-bold text-sm text-white">{item.name}</h3>
                  </div>
                  <span className={`text-[10px] font-mono-data px-2 py-0.5 rounded font-bold border ${
                    item.severity === 'CRÍTICO'
                      ? 'bg-rose-500/20 text-rose-300 border-rose-500/40'
                      : item.severity === 'ALTO'
                      ? 'bg-amber-500/20 text-amber-300 border-amber-500/40'
                      : 'bg-sky-500/20 text-sky-300 border-sky-500/40'
                  }`}>
                    {item.severity}
                  </span>
                </div>

                <div className="text-xs text-slate-300 space-y-1">
                  <div><strong>Síntoma:</strong> {item.symptom}</div>
                  <div className="text-rose-300"><strong>Riesgo en Pozos SCADA:</strong> {item.scadaRisk}</div>
                </div>

                {/* Comparación Bad Code vs Good Code */}
                <div className="space-y-2 pt-2 border-t border-slate-800 text-[11px] font-mono-data">
                  <div className="bg-rose-950/20 border border-rose-500/30 rounded p-2.5">
                    <span className="text-rose-400 font-bold block mb-1">❌ Antipatrón / Código Defectuoso (Bad Practice):</span>
                    <pre className="text-slate-300 whitespace-pre-wrap text-[10px] overflow-x-auto">{item.badCode}</pre>
                  </div>

                  <div className="bg-emerald-950/20 border border-emerald-500/30 rounded p-2.5">
                    <span className="text-emerald-400 font-bold block mb-1">✓ Patrón Solución Aplicado en MAPAF:</span>
                    <div className="text-slate-200 font-semibold mb-1">{item.solutionPattern}</div>
                    <pre className="text-slate-300 whitespace-pre-wrap text-[10px] overflow-x-auto">{item.goodCode}</pre>
                  </div>
                </div>

                <div className="text-[10px] font-mono-data text-slate-400 bg-slate-950 p-2 rounded border border-slate-800">
                  <span className="text-emerald-400 font-bold">Implementación MAPAF:</span> {item.appliedInMapaf}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* ================================================================== */}
      {/* SECCIÓN 3: DOMINIOS DEL SISTEMA (DDD - DOMAIN-DRIVEN DESIGN)       */}
      {/* ================================================================== */}
      {activeMainTab === 'domains' && (
        <div className="space-y-5 animate-fadeIn">
          {/* Banner de Dominios */}
          <div className="p-4 bg-sky-950/20 border-l-4 border-sky-500 rounded-r-xl text-xs text-slate-300 space-y-1">
            <strong className="text-sky-400 font-bold">Diseño Dirigido por el Dominio (Domain-Driven Design - DDD):</strong>
            <p>
              El sistema MAPAF está estructurado en <strong>7 Bounded Contexts (Contextos Delimitados)</strong> independientes. 
              Cada dominio tiene sus propias Entidades, Objetos de Valor (Value Objects), Eventos de Dominio e Invariantes de seguridad industrial.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {SYSTEM_DOMAINS.map((dom) => (
              <div
                key={dom.id}
                className="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3.5 shadow-md flex flex-col justify-between"
              >
                <div>
                  <div className="flex justify-between items-start gap-2 mb-2">
                    <span className="text-[10px] font-mono-data bg-slate-800 text-sky-400 px-2 py-0.5 rounded border border-slate-700">
                      {dom.code}
                    </span>
                    <span className="text-[10px] font-mono-data bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/30">
                      {dom.standard.split(' ')[0]}
                    </span>
                  </div>

                  <h3 className="font-display font-bold text-base text-white">{dom.name}</h3>
                  <p className="text-xs text-slate-300 mt-1">{dom.description}</p>
                  <div className="text-[11px] font-mono-data text-emerald-400 mt-1">Norma: {dom.standard}</div>
                </div>

                <div className="space-y-2 pt-3 border-t border-slate-800 text-xs font-mono-data">
                  <div>
                    <span className="text-slate-400 block text-[10px] uppercase font-bold">Entidades del Dominio:</span>
                    <div className="flex flex-wrap gap-1 mt-0.5">
                      {dom.entities.map((e, idx) => (
                        <span key={idx} className="bg-slate-950 px-2 py-0.5 rounded text-[11px] text-slate-200 border border-slate-800">
                          {e}
                        </span>
                      ))}
                    </div>
                  </div>

                  <div>
                    <span className="text-slate-400 block text-[10px] uppercase font-bold">Objetos de Valor (Value Objects):</span>
                    <div className="text-[11px] text-slate-300 mt-0.5">{dom.valueObjects.join(' · ')}</div>
                  </div>

                  <div>
                    <span className="text-rose-400 block text-[10px] uppercase font-bold">Invariantes de Seguridad:</span>
                    <ul className="list-disc list-inside text-[10px] text-slate-300 space-y-0.5 mt-0.5">
                      {dom.invariants.map((inv, idx) => (
                        <li key={idx}>{inv}</li>
                      ))}
                    </ul>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* ================================================================== */}
      {/* SECCIÓN 4: SEGURIDAD & AUTENTICACIÓN JWT (RFC 7519)                */}
      {/* ================================================================== */}
      {activeMainTab === 'jwt' && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-5 animate-fadeIn">
          {/* Panel Izquierdo: Emisor de Tokens */}
          <div className="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <div className="flex items-center gap-2">
                <Key className="w-5 h-5 text-purple-400" />
                <h3 className="font-display text-base font-bold text-white">Servicio de Emisión JWT (RFC 7519)</h3>
              </div>
              <span className="text-[10px] font-mono-data bg-purple-500/10 text-purple-400 px-2 py-0.5 rounded border border-purple-500/30">
                HMAC SHA-256
              </span>
            </div>

            <p className="text-xs text-slate-300">
              Autentica criptográficamente a los operadores de sala de control en el backend PHP. 
              El token emitido contiene el código de empleado, rol, sector y la matriz de permisos RBAC para autorizar maniobras en pozos.
            </p>

            <div className="p-4 bg-slate-950 rounded-lg border border-slate-800 space-y-3 text-xs font-mono-data">
              <div>
                <label className="text-slate-400 block mb-1">Seleccionar Operador para Emitir JWT:</label>
                <select
                  value={selectedOperatorCode}
                  onChange={(e) => setSelectedOperatorCode(e.target.value)}
                  className="w-full bg-slate-900 border border-slate-700 text-white p-2 rounded"
                >
                  <option value="OP-449">OP-449 · María José Conde Dussan (Admin - Control Total)</option>
                  <option value="OP-312">OP-312 · Hernann Manchola (Ingeniero - Choke & Curvas)</option>
                  <option value="OP-205">OP-205 · Emilin Vargas (Supervisor - Válvulas & Turno)</option>
                  <option value="OP-118">OP-118 · Javier Pinto (Instructor / Auditor Senior SENA)</option>
                </select>
              </div>

              <div className="text-[11px] text-slate-400">
                Tiempo de vida del token: <strong className="text-emerald-400">8 Horas (28,800 segundos)</strong> · Turno Operativo Estándar
              </div>

              <button
                onClick={handleGenerateJwt}
                className="w-full py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded cursor-pointer transition-colors shadow flex items-center justify-center gap-2"
              >
                <Key className="w-4 h-4" />
                <span>Generar y Firmar Token JWT en PHP</span>
              </button>

              {jwtFeedback && (
                <div className="p-2.5 bg-slate-900 rounded border border-slate-800 text-[11px] text-emerald-400">
                  {jwtFeedback}
                </div>
              )}
            </div>

            {/* Cabecera HTTP Bearer */}
            <div className="p-3 bg-slate-950 rounded border border-slate-800 text-xs font-mono-data space-y-1">
              <span className="text-slate-400 text-[10px] block uppercase font-bold">Cabecera HTTP de Autorización:</span>
              <div className="text-emerald-400 text-[11px] break-all select-all bg-slate-900 p-2 rounded border border-slate-800">
                Authorization: Bearer {rawJwtString.substring(0, 32)}...
              </div>
            </div>
          </div>

          {/* Panel Derecho: Desglose Criptográfico del Token */}
          <div className="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <h3 className="font-display text-sm font-bold text-white flex items-center gap-2">
                <Lock className="w-4 h-4 text-purple-400" />
                Anatomía Criptográfica del Token JWT Emitido
              </h3>
              <button
                onClick={copyTokenToClipboard}
                className="flex items-center gap-1 px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-mono-data rounded cursor-pointer transition-colors"
              >
                {copiedJwt ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                <span>{copiedJwt ? 'Copiado!' : 'Copiar Token'}</span>
              </button>
            </div>

            {jwtTokenData ? (
              <div className="space-y-3 font-mono-data text-xs">
                {/* 1. Header */}
                <div className="p-3 bg-rose-950/20 border border-rose-500/40 rounded-lg space-y-1">
                  <div className="flex justify-between items-center text-rose-400 font-bold text-[11px]">
                    <span>1. HEADER (Algoritmo & Tipo):</span>
                    <span>HS256 · JWT</span>
                  </div>
                  <pre className="text-slate-200 text-[11px] overflow-x-auto bg-slate-950/60 p-2 rounded">
                    {JSON.stringify(jwtTokenData.header, null, 2)}
                  </pre>
                </div>

                {/* 2. Payload */}
                <div className="p-3 bg-purple-950/20 border border-purple-500/40 rounded-lg space-y-1">
                  <div className="flex justify-between items-center text-purple-400 font-bold text-[11px]">
                    <span>2. PAYLOAD (Claims del Operador SCADA):</span>
                    <span>Rol: {jwtTokenData.payload.roleName}</span>
                  </div>
                  <pre className="text-slate-200 text-[11px] overflow-x-auto bg-slate-950/60 p-2 rounded max-h-[160px]">
                    {JSON.stringify(jwtTokenData.payload, null, 2)}
                  </pre>
                </div>

                {/* 3. Signature */}
                <div className="p-3 bg-sky-950/20 border border-sky-500/40 rounded-lg space-y-1">
                  <div className="flex justify-between items-center text-sky-400 font-bold text-[11px]">
                    <span>3. VERIFY SIGNATURE (Firma Criptográfica):</span>
                    <span className="text-emerald-400 font-bold">✓ VÁLIDA</span>
                  </div>
                  <div className="text-[11px] text-slate-300 break-all bg-slate-950/60 p-2 rounded">
                    HMACSHA256(base64UrlEncode(header) + "." + base64UrlEncode(payload), secret_key)
                  </div>
                </div>

                {/* Permisos Activos del Operador */}
                <div className="p-3 bg-slate-950 rounded-lg border border-slate-800 space-y-1.5">
                  <span className="text-slate-400 text-[10px] block uppercase font-bold">
                    Permisos Autorizados en el Token ({jwtTokenData.payload.permissions?.length || 0}):
                  </span>
                  <div className="flex flex-wrap gap-1">
                    {jwtTokenData.payload.permissions?.map((p: string, idx: number) => (
                      <span key={idx} className="px-2 py-0.5 rounded bg-slate-800 text-emerald-400 border border-slate-700 text-[10px]">
                        ✓ {p}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            ) : (
              <div className="text-center py-10 text-xs text-slate-400 font-mono-data">
                Presione el botón "Generar y Firmar Token JWT" para inspeccionar la estructura criptográfica.
              </div>
            )}
          </div>
        </div>
      )}

      {/* ================================================================== */}
      {/* SECCIÓN 5: CONEXIÓN XAMPP (PHP + MYSQL)                            */}
      {/* ================================================================== */}
      {activeMainTab === 'xampp' && (
        <div className="space-y-5 animate-fadeIn">
          <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3">
              <div className="flex items-center gap-2">
                <Server className="w-5 h-5 text-amber-400" />
                <h3 className="font-display text-base font-bold text-white">Integración con XAMPP (Apache, PHP 8 y MySQL mapaf_db)</h3>
              </div>
              <span className={`text-xs font-mono-data px-2.5 py-0.5 rounded border font-bold ${
                xamppConnected
                  ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                  : 'bg-amber-500/10 text-amber-400 border-amber-500/30'
              }`}>
                {xamppConnected ? 'CONECTADO AL SERVIDOR XAMPP' : 'XAMPP NO DETECTADO'}
              </span>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-mono-data">
              <div className="p-4 bg-slate-950 rounded-xl border border-slate-800 space-y-2">
                <div className="text-slate-400 uppercase text-[10px] font-bold">1. Servidor Web Apache</div>
                <div className="text-white font-bold text-sm">http://localhost/mapaf/</div>
                <div className="text-slate-400 text-[11px]">Procesa peticiones HTTP REST y sirve el backend en PHP con CORS habilitado.</div>
              </div>

              <div className="p-4 bg-slate-950 rounded-xl border border-slate-800 space-y-2">
                <div className="text-slate-400 uppercase text-[10px] font-bold">2. Lenguaje PHP 8.x</div>
                <div className="text-emerald-400 font-bold text-sm">Extensiones: pdo_mysql</div>
                <div className="text-slate-400 text-[11px]">Ejecuta los patrones de diseño (Singleton, Factory, Observer, Command, Strategy, State).</div>
              </div>

              <div className="p-4 bg-slate-950 rounded-xl border border-slate-800 space-y-2">
                <div className="text-slate-400 uppercase text-[10px] font-bold">3. Base de Datos MySQL</div>
                <div className="text-sky-400 font-bold text-sm">mapaf_db (Puerto 3306)</div>
                <div className="text-slate-400 text-[11px]">8 tablas relacionales con soporte ACID, motor InnoDB y bitácora de auditoría inmutable.</div>
              </div>
            </div>

            {/* Guía Rápida para Iniciar XAMPP */}
            <div className="p-4 bg-slate-950 rounded-xl border border-slate-800 space-y-3 text-xs font-mono-data">
              <div className="text-amber-400 font-bold text-sm">Cómo iniciar y probar la conexión con XAMPP:</div>
              <ol className="list-decimal list-inside space-y-1.5 text-slate-300">
                <li>Abra el <strong>Panel de Control de XAMPP</strong> y presione <strong>Start</strong> en los módulos <strong>Apache</strong> y <strong>MySQL</strong>.</li>
                <li>O ejecute el archivo automático ubicado en la raíz del proyecto: <code className="text-emerald-400 bg-slate-900 px-1.5 py-0.5 rounded">iniciar_xampp_mapaf.bat</code>.</li>
                <li>Verifique el instalador de la base de datos en: <a href="http://localhost/mapaf/setup_database.php" target="_blank" rel="noreferrer" className="text-sky-400 underline">http://localhost/mapaf/setup_database.php</a>.</li>
                <li>Consulte el estado de la API en: <a href="http://localhost/mapaf/api/status.php" target="_blank" rel="noreferrer" className="text-sky-400 underline">http://localhost/mapaf/api/status.php</a>.</li>
                <li>Pruebe la API de Patrones en: <a href="http://localhost/mapaf/api/patterns.php" target="_blank" rel="noreferrer" className="text-sky-400 underline">http://localhost/mapaf/api/patterns.php</a>.</li>
              </ol>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};

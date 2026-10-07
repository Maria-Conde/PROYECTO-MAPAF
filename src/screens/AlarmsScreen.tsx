import React, { useState } from 'react';
import { AlarmEvent } from '../types/scada';
import { SplitBadge } from '../components/SplitBadge';
import {
  AlertTriangle,
  AlertOctagon,
  CheckCircle,
  Search,
  Check,
  ShieldAlert,
  FileText,
  Clock,
  Filter,
} from 'lucide-react';

interface AlarmsScreenProps {
  alarms: AlarmEvent[];
  onAcknowledgeAlarm: (id: string) => void;
  onAcknowledgeAll: () => void;
  onTriggerSimulatedAlarm: () => void;
}

export const AlarmsScreen: React.FC<AlarmsScreenProps> = ({
  alarms,
  onAcknowledgeAlarm,
  onAcknowledgeAll,
  onTriggerSimulatedAlarm,
}) => {
  const [filterSeverity, setFilterSeverity] = useState<string>('TODAS');
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [selectedAlarm, setSelectedAlarm] = useState<AlarmEvent | null>(alarms[0] || null);

  const filteredAlarms = alarms.filter((alm) => {
    let matchesSev = true;
    if (filterSeverity === 'CRÍTICA') matchesSev = alm.severity === 'critical';
    else if (filterSeverity === 'ALERTA') matchesSev = alm.severity === 'warning';
    else if (filterSeverity === 'NOMINAL') matchesSev = alm.severity === 'nominal';

    const matchesSearch =
      alm.tag.toLowerCase().includes(searchTerm.toLowerCase()) ||
      alm.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
      alm.subsystem.toLowerCase().includes(searchTerm.toLowerCase());
    return matchesSev && matchesSearch;
  });

  const unacknowledgedCount = alarms.filter((a) => !a.acknowledged).length;
  const criticalCount = alarms.filter((a) => a.severity === 'critical').length;
  const warningCount = alarms.filter((a) => a.severity === 'warning').length;

  return (
    <div className="flex-1 flex flex-col gap-4 p-4 lg:p-6 max-w-[1780px] mx-auto w-full overflow-y-auto">
      {/* Cabecera Principal y Estadísticas de Alarma */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 shadow-sm backdrop-blur-md">
        <div className="flex items-center gap-3">
          <div className="p-2.5 bg-rose-500/10 border border-rose-500/30 rounded-lg text-rose-400">
            <ShieldAlert className="w-5 h-5 animate-pulse" />
          </div>
          <div>
            <h1 className="font-display text-base font-bold uppercase tracking-wider text-white">
              Anunciador de Alarmas y Registro de Eventos (ISA-18.2)
            </h1>
            <div className="text-xs font-mono-data text-slate-400 flex items-center gap-2 mt-0.5">
              <span>SISTEMA DE SEGURIDAD FUNCIONAL SIL-3</span>
              <span aria-hidden="true">·</span>
              <span className="text-amber-400 font-semibold">
                {unacknowledgedCount} EVENTOS PENDIENTES DE RECONOCER
              </span>
            </div>
          </div>
        </div>

        {/* Acciones de Operador */}
        <div className="flex items-center gap-3 flex-wrap">
          <button
            onClick={onTriggerSimulatedAlarm}
            className="px-3.5 py-2 text-xs font-mono-data uppercase bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-md transition-all shadow-sm"
          >
            + SIMULAR ALARMA DE PRUEBA
          </button>

          {unacknowledgedCount > 0 && (
            <button
              onClick={onAcknowledgeAll}
              className="px-4 py-2 text-xs font-mono-data font-bold uppercase bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-md transition-all flex items-center gap-2 shadow-[0_0_12px_rgba(16,185,129,0.3)]"
            >
              <Check className="w-4 h-4" />
              <span>RECONOCER TODAS (ACK ALL)</span>
            </button>
          )}
        </div>
      </div>

      {/* Barra de Filtros y Búsqueda */}
      <div className="flex flex-wrap items-center justify-between gap-3 bg-slate-950 p-3 rounded-xl border border-slate-800/90">
        <div className="flex items-center gap-2">
          <span className="text-xs font-mono-data uppercase text-slate-400 mr-1 flex items-center gap-1.5 font-semibold">
            <Filter className="w-3.5 h-3.5" /> FILTRAR:
          </span>
          {['TODAS', 'CRÍTICA', 'ALERTA', 'NOMINAL'].map((sev) => {
            const isActive = filterSeverity === sev;
            return (
              <button
                key={sev}
                onClick={() => setFilterSeverity(sev)}
                className={`px-3 py-1.5 text-xs font-mono-data uppercase rounded-md transition-all ${
                  isActive
                    ? 'bg-slate-800 text-white font-bold border border-sky-500/60 shadow-sm'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900 border border-transparent'
                }`}
              >
                {sev}
              </button>
            );
          })}
        </div>

        {/* Buscador de Etiquetas o Eventos */}
        <div className="relative min-w-[280px]">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
          <input
            type="text"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            placeholder="BUSCAR ETIQUETA, EQUIPO O CAUSA..."
            className="w-full bg-slate-900 border border-slate-800 focus:border-sky-500 text-xs font-mono-data pl-9 pr-3 py-2 text-white rounded-lg outline-none placeholder:text-slate-500"
          />
        </div>
      </div>

      {/* Grid Principal: Tabla de Eventos y Panel de Causa Raíz */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-4 flex-1">
        {/* Tabla de Eventos (8 cols) */}
        <div className="lg:col-span-8 bg-slate-900/90 rounded-xl border border-slate-800/90 flex flex-col overflow-hidden shadow-sm backdrop-blur-md">
          <div className="p-4 border-b border-slate-800/80 flex items-center justify-between">
            <span className="font-display text-xs font-bold uppercase tracking-wider text-white">
              Secuencia Cronológica de Eventos (SOE)
            </span>
            <span className="text-xs font-mono-data text-slate-400">
              {filteredAlarms.length} eventos registrados
            </span>
          </div>

          <div className="flex-1 overflow-x-auto overflow-y-auto min-h-[380px]">
            <table className="w-full text-left border-collapse text-xs font-mono-data">
              <thead>
                <tr className="bg-slate-950 border-b border-slate-800/90 text-slate-400 text-[10px] uppercase">
                  <th className="py-3 px-3.5">HORA</th>
                  <th className="py-3 px-3.5">SEVERIDAD</th>
                  <th className="py-3 px-3.5">ETIQUETA (TAG)</th>
                  <th className="py-3 px-3.5">SUBSISTEMA</th>
                  <th className="py-3 px-3.5">DESCRIPCIÓN DE ALARMA</th>
                  <th className="py-3 px-3.5">VALOR / LÍMITE</th>
                  <th className="py-3 px-3.5 text-right">ACCIÓN</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/70">
                {filteredAlarms.map((alarm) => {
                  const isSelected = selectedAlarm?.id === alarm.id;
                  const isCritical = alarm.severity === 'critical';
                  const isWarning = alarm.severity === 'warning';

                  return (
                    <tr
                      key={alarm.id}
                      onClick={() => setSelectedAlarm(alarm)}
                      className={`cursor-pointer transition-all ${
                        isSelected
                          ? 'bg-slate-800/80 border-l-4 border-l-sky-400'
                          : 'hover:bg-slate-800/40'
                      } ${!alarm.acknowledged && isCritical ? 'bg-rose-950/20' : ''}`}
                    >
                      <td className="py-3 px-3.5 text-slate-300 whitespace-nowrap font-medium">
                        {alarm.timestamp}
                      </td>
                      <td className="py-3 px-3.5 whitespace-nowrap">
                        <span
                          className={`inline-flex items-center gap-1.5 font-bold ${
                            isCritical
                              ? 'text-rose-400 animate-pulse'
                              : isWarning
                              ? 'text-amber-400'
                              : 'text-emerald-400'
                          }`}
                        >
                          {isCritical && '✖ DISPARO'}
                          {isWarning && '▲ ALTA'}
                          {!isCritical && !isWarning && '● OK'}
                        </span>
                      </td>
                      <td className="py-3 px-3.5 font-bold text-sky-400 whitespace-nowrap">
                        {alarm.tag}
                      </td>
                      <td className="py-3 px-3.5 text-slate-400 whitespace-nowrap">
                        {alarm.subsystem}
                      </td>
                      <td className="py-3 px-3.5 text-slate-200 max-w-[260px] truncate">
                        {alarm.description}
                      </td>
                      <td className="py-3 px-3.5 whitespace-nowrap text-slate-300">
                        <span className="font-bold text-white">{alarm.value}</span>
                        <span className="text-slate-500 text-[10px] ml-1.5">
                          [{alarm.threshold}]
                        </span>
                      </td>
                      <td className="py-3 px-3.5 text-right whitespace-nowrap">
                        {alarm.acknowledged ? (
                          <span className="text-xs text-emerald-400 flex items-center justify-end gap-1 font-semibold">
                            <CheckCircle className="w-3.5 h-3.5" />
                            <span>ACK</span>
                          </span>
                        ) : (
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              onAcknowledgeAlarm(alarm.id);
                            }}
                            className="px-2.5 py-1 text-[10px] uppercase font-bold bg-rose-600 hover:bg-rose-500 text-white rounded transition-all shadow-sm"
                          >
                            RECONOCER
                          </button>
                        )}
                      </td>
                    </tr>
                  );
                })}

                {filteredAlarms.length === 0 && (
                  <tr>
                    <td colSpan={7} className="py-12 text-center text-slate-500">
                      NO SE ENCONTRARON ALARMAS ACTIVAS CON EL FILTRO SELECCIONADO
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* Panel Lateral: Diagnóstico de Causa Raíz (4 cols) */}
        <div className="lg:col-span-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 flex flex-col justify-between shadow-sm backdrop-blur-md">
          {selectedAlarm ? (
            <div>
              <div className="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-4">
                <div>
                  <span className="text-[10px] font-mono-data uppercase text-slate-400 font-semibold tracking-wider">
                    ANÁLISIS DE CAUSA RAÍZ (RCA)
                  </span>
                  <h3 className="font-display text-base font-bold text-white mt-0.5">
                    {selectedAlarm.tag}
                  </h3>
                </div>
                <SplitBadge
                  prefix="SEV"
                  value={
                    selectedAlarm.severity === 'critical'
                      ? 'CRÍTICA'
                      : selectedAlarm.severity === 'warning'
                      ? 'ALERTA'
                      : 'NOMINAL'
                  }
                  severity={selectedAlarm.severity}
                />
              </div>

              {/* Contenido de Diagnóstico */}
              <div className="space-y-3 mb-4">
                <div className="bg-slate-950 p-3.5 rounded-lg border border-slate-800/90">
                  <span className="text-[10px] font-mono-data text-slate-400 uppercase block mb-1 font-semibold">
                    DESCRIPCIÓN DEL EVENTO
                  </span>
                  <p className="text-xs font-mono-data text-white leading-relaxed">
                    {selectedAlarm.description}
                  </p>
                </div>

                <div className="grid grid-cols-2 gap-2 text-xs font-mono-data">
                  <div className="bg-slate-950 p-3 rounded-lg border border-slate-800/90">
                    <span className="text-[10px] text-slate-400 block">VALOR REGISTRADO</span>
                    <span className="font-bold text-rose-400 text-sm mt-0.5 block">{selectedAlarm.value}</span>
                  </div>
                  <div className="bg-slate-950 p-3 rounded-lg border border-slate-800/90">
                    <span className="text-[10px] text-slate-400 block">SETPOINT DE DISPARO</span>
                    <span className="font-bold text-slate-200 text-sm mt-0.5 block">{selectedAlarm.threshold}</span>
                  </div>
                </div>

                <div className="bg-slate-950 p-3.5 rounded-lg border border-slate-800/90">
                  <span className="text-[10px] font-mono-data text-slate-400 uppercase block mb-1 font-semibold">
                    DIAGNÓSTICO AUTOMÁTICO DE FALLA
                  </span>
                  <p className="text-xs font-mono-data text-amber-300 leading-relaxed">
                    {selectedAlarm.tripCause ||
                      'Disparo preventivo automático por gradiente diferencial de presión en el cabezal.'}
                  </p>
                </div>

                <div className="bg-slate-950 p-3.5 rounded-lg border border-slate-800/90">
                  <span className="text-[10px] font-mono-data text-slate-400 uppercase block mb-1 font-semibold">
                    REGISTRO DE AUDITORÍA
                  </span>
                  <div className="text-xs font-mono-data text-slate-300 space-y-1">
                    <div>REGISTRADO: {selectedAlarm.timestamp} (Hora Local)</div>
                    <div>
                      ESTADO:{' '}
                      {selectedAlarm.acknowledged ? (
                        <span className="text-emerald-400 font-semibold">
                          RECONOCIDO POR {selectedAlarm.acknowledgedBy || 'OPERADOR'} A LAS{' '}
                          {selectedAlarm.ackTimestamp || selectedAlarm.timestamp}
                        </span>
                      ) : (
                        <span className="text-rose-400 font-semibold">PENDIENTE DE RECONOCIMIENTO</span>
                      )}
                    </div>
                  </div>
                </div>
              </div>

              {!selectedAlarm.acknowledged && (
                <button
                  onClick={() => onAcknowledgeAlarm(selectedAlarm.id)}
                  className="w-full py-3 px-4 bg-rose-600 hover:bg-rose-500 text-white font-mono-data font-bold uppercase tracking-wider text-xs rounded-lg transition-all shadow-md"
                >
                  RECONOCER ESTA ALARMA AHORA
                </button>
              )}
            </div>
          ) : (
            <div className="py-12 text-center text-xs font-mono-data text-slate-500">
              SELECCIONA UNA FILA DE ALARMA PARA INSPECCIONAR
            </div>
          )}

          <div className="text-[11px] font-mono-data text-slate-500 border-t border-slate-800/80 pt-3 mt-4">
            NORMA TÉCNICA: ISA-18.2 / IEC 62682 GESTIÓN DE ALARMAS
          </div>
        </div>
      </div>
    </div>
  );
};


import React, { useState } from 'react';
import { WellheadUnit } from '../types/scada';
import { SplitBadge } from '../components/SplitBadge';
import {
  Sliders,
  Power,
  ShieldAlert,
  Droplets,
  Gauge,
  Flame,
  Activity,
  CheckCircle2,
  AlertTriangle,
} from 'lucide-react';

interface WellheadControlScreenProps {
  wellheads: WellheadUnit[];
  onUpdateWellhead: (wellhead: WellheadUnit) => void;
  onRequestInterlock: (
    tag: string,
    action: string,
    onConfirm: () => void,
    warning?: string
  ) => void;
}

export const WellheadControlScreen: React.FC<WellheadControlScreenProps> = ({
  wellheads,
  onUpdateWellhead,
  onRequestInterlock,
}) => {
  const [selectedWellId, setSelectedWellId] = useState<string>(wellheads[0].id);

  const selectedWell =
    wellheads.find((w) => w.id === selectedWellId) || wellheads[0];

  const handleChokeChange = (newVal: number) => {
    onUpdateWellhead({
      ...selectedWell,
      chokeOpening: newVal,
      flowRate: Math.round((newVal / 50) * 4500),
      tubingPressure: Number((2200 - newVal * 8.5).toFixed(1)),
    });
  };

  const handleEspFreqChange = (newFreq: number) => {
    onUpdateWellhead({
      ...selectedWell,
      espFrequency: newFreq,
      flowRate: Math.round(newFreq * 72),
    });
  };

  const toggleSsvWithInterlock = () => {
    const nextState = !selectedWell.ssvOpen;
    onRequestInterlock(
      `${selectedWell.name} - VÁLVULA SSV HIDRÁULICA`,
      nextState
        ? 'ORDEN DE APERTURA REMOTA DE VÁLVULA DE SEGURIDAD (SSV)'
        : 'ORDEN DE DISPARO DE CIERRE DE VÁLVULA DE SEGURIDAD (SSV)',
      () => {
        onUpdateWellhead({
          ...selectedWell,
          ssvOpen: nextState,
          status: nextState ? 'EN LÍNEA' : 'DISPARADO',
          flowRate: nextState ? 3800 : 0,
        });
      },
      nextState
        ? 'PRECAUCIÓN: La apertura rápida de la SSV presuriza el cabezal e impacta el manifold.'
        : 'CRÍTICO: El cierre de la SSV aísla inmediatamente el flujo de hidrocarburos del pozo.'
    );
  };

  const toggleChemicalInjection = () => {
    onUpdateWellhead({
      ...selectedWell,
      chemicalInjection: !selectedWell.chemicalInjection,
    });
  };

  const toggleAnnulusBleed = () => {
    const nextState = !selectedWell.annulusBleedOpen;
    onRequestInterlock(
      `${selectedWell.name} - VENTEO DE ANULAR (SOLENOIDE)`,
      nextState
        ? 'APERTURA DE VÁLVULA DE ALIVIO DE ANULAR HACIA LA ANTORCHA'
        : 'CIERRE Y AISLAMIENTO DE LÍNEA DE VENTEO DE ANULAR',
      () => {
        onUpdateWellhead({
          ...selectedWell,
          annulusBleedOpen: nextState,
          casingPressure: nextState
            ? Math.max(200, selectedWell.casingPressure - 180)
            : selectedWell.casingPressure,
        });
      },
      'El gas despresurizado del anular se dirigirá al tambor separador de antorcha de baja presión.'
    );
  };

  return (
    <div className="flex-1 flex flex-col gap-4 p-4 lg:p-6 max-w-[1780px] mx-auto w-full overflow-y-auto">
      {/* Selector de Pozos */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 shadow-sm backdrop-blur-md">
        <div className="flex items-center gap-3">
          <div className="p-2 bg-emerald-500/10 border border-emerald-500/30 rounded-lg text-emerald-400">
            <Gauge className="w-5 h-5" />
          </div>
          <div>
            <h1 className="font-display text-base font-bold uppercase tracking-wider text-white">
              Bahía de Cabezales y Control de Pozos
            </h1>
            <div className="text-xs font-mono-data text-slate-400 flex items-center gap-2 mt-0.5">
              <span>BATERÍA PAD-04</span>
              <span aria-hidden="true">·</span>
              <span>4 POZOS PRODUCTORES ASIGNADOS</span>
              <span aria-hidden="true">·</span>
              <span className="text-emerald-400">CONTROL PID REMOTO EN LÍNEA</span>
            </div>
          </div>
        </div>

        {/* Pestañas de Selección de Pozo */}
        <div className="flex items-center gap-2 bg-slate-950 p-1.5 rounded-lg border border-slate-800/90 overflow-x-auto">
          {wellheads.map((well) => {
            const isSelected = well.id === selectedWellId;
            return (
              <button
                key={well.id}
                onClick={() => setSelectedWellId(well.id)}
                className={`px-3.5 py-2 text-xs font-mono-data uppercase tracking-wider rounded-md transition-all whitespace-nowrap flex items-center gap-2.5 ${
                  isSelected
                    ? 'bg-slate-800 text-white font-bold border border-sky-500/60 shadow-[0_0_12px_rgba(14,165,233,0.2)]'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/80 border border-transparent'
                }`}
              >
                <span
                  className={`w-2 h-2 rounded-full ${
                    well.status === 'EN LÍNEA'
                      ? 'bg-emerald-500 shadow-[0_0_6px_#10b981]'
                      : well.status === 'ESTRANGULADO'
                      ? 'bg-amber-500'
                      : 'bg-rose-500 animate-pulse'
                  }`}
                />
                <span>{well.name}</span>
                <span className="text-[10px] text-slate-400">
                  [{well.flowRate.toLocaleString()} BPD]
                </span>
              </button>
            );
          })}
        </div>
      </div>

      {/* Cuadrícula de 3 Columnas Organizada */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 flex-1">
        {/* Columna 1: Telemetría y Estado Físico del Pozo */}
        <div className="flex flex-col gap-4">
          <div className="bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md flex flex-col justify-between">
            <div className="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-4">
              <div>
                <span className="text-[10px] font-mono-data text-slate-400 uppercase font-semibold">
                  ÁRBOL DE NAVIDAD ACTIVO
                </span>
                <h2 className="font-display text-lg font-bold text-white mt-0.5">
                  {selectedWell.name}
                </h2>
              </div>
              <div className="flex items-center gap-2">
                <SplitBadge
                  prefix="ESTADO"
                  value={selectedWell.status}
                  severity={
                    selectedWell.status === 'EN LÍNEA'
                      ? 'nominal'
                      : selectedWell.status === 'ESTRANGULADO'
                      ? 'warning'
                      : 'critical'
                  }
                />
                <SplitBadge prefix="SECTOR" value={selectedWell.sector} severity="info" />
              </div>
            </div>

            {/* 4 Bloques de Parámetros de Fondo y Superficie */}
            <div className="grid grid-cols-2 gap-3 mb-4">
              <div className="bg-slate-950 rounded-lg border border-slate-800/90 p-3.5">
                <span className="text-[10px] font-mono-data text-slate-400 uppercase block">
                  PRESIÓN DE CABEZAL (WHTP)
                </span>
                <div className="font-mono-data text-2xl font-bold text-white mt-1">
                  {selectedWell.tubingPressure.toFixed(1)}{' '}
                  <span className="text-xs text-slate-400 font-normal">PSI</span>
                </div>
                <div className="text-[10px] font-mono-data text-emerald-400 mt-1">
                  Límite Seguro: &lt; 2,350 PSI
                </div>
              </div>

              <div className="bg-slate-950 rounded-lg border border-slate-800/90 p-3.5">
                <span className="text-[10px] font-mono-data text-slate-400 uppercase block">
                  PRESIÓN ANULAR (CASING)
                </span>
                <div className="font-mono-data text-2xl font-bold text-amber-400 mt-1">
                  {selectedWell.casingPressure.toFixed(1)}{' '}
                  <span className="text-xs text-slate-400 font-normal">PSI</span>
                </div>
                <div className="text-[10px] font-mono-data text-slate-400 mt-1">
                  Venteo Umbral: 800 PSI
                </div>
              </div>

              <div className="bg-slate-950 rounded-lg border border-slate-800/90 p-3.5">
                <span className="text-[10px] font-mono-data text-slate-400 uppercase block">
                  CAUDAL DE PRODUCCIÓN
                </span>
                <div className="font-mono-data text-2xl font-bold text-emerald-400 mt-1">
                  {selectedWell.flowRate.toLocaleString()}{' '}
                  <span className="text-xs text-slate-400 font-normal">BPD</span>
                </div>
                <div className="text-[10px] font-mono-data text-slate-400 mt-1">
                  Aporte: {((selectedWell.flowRate / 14820) * 100).toFixed(1)}% total
                </div>
              </div>

              <div className="bg-slate-950 rounded-lg border border-slate-800/90 p-3.5">
                <span className="text-[10px] font-mono-data text-slate-400 uppercase block">
                  VIBRACIÓN BOMBA ESP
                </span>
                <div
                  className={`font-mono-data text-2xl font-bold mt-1 ${
                    selectedWell.espVibration > 4.5
                      ? 'text-rose-400'
                      : selectedWell.espVibration > 3.0
                      ? 'text-amber-400'
                      : 'text-white'
                  }`}
                >
                  {selectedWell.espVibration.toFixed(2)}{' '}
                  <span className="text-xs text-slate-400 font-normal">mm/s</span>
                </div>
                <div className="text-[10px] font-mono-data text-slate-400 mt-1">
                  Disparo ISO: 5.0 mm/s
                </div>
              </div>
            </div>

            {/* Sonda de Detección de Arena Acústica */}
            <div className="bg-slate-950 rounded-lg border border-slate-800/90 p-3 flex items-center justify-between text-xs font-mono-data">
              <span className="text-slate-400">SONDA ACÚSTICA DE ARENA:</span>
              <span className="font-bold text-slate-200">
                {selectedWell.sandAcousticPpm.toFixed(1)} PPM SÓLIDOS
              </span>
              <span className="text-emerald-400 font-medium">[PERMITIDO &lt; 50 PPM]</span>
            </div>
          </div>

          {/* Válvula de Seguridad de Superficie (SSV) */}
          <div className="bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center gap-2">
                <Power className="w-4 h-4 text-rose-400" />
                <h3 className="font-display text-sm font-bold uppercase tracking-wider text-white">
                  Válvula de Seguridad de Superficie (SSV)
                </h3>
              </div>
              <SplitBadge
                prefix="ACTUADOR"
                value={selectedWell.ssvOpen ? 'ABIERTA / EN LÍNEA' : 'CERRADA / DISPARADA'}
                severity={selectedWell.ssvOpen ? 'nominal' : 'critical'}
              />
            </div>

            <p className="text-xs font-mono-data text-slate-400 mb-4 leading-relaxed">
              Válvula de compuerta a prueba de fallos de retención hidráulica. Ante pérdida de
              presión hidráulica, se cierra por resorte mecánico en 0.8 segundos.
            </p>

            <button
              onClick={toggleSsvWithInterlock}
              className={`w-full py-3 px-4 rounded-lg text-xs font-mono-data uppercase font-bold tracking-wider transition-all flex items-center justify-center gap-2 border shadow-sm ${
                selectedWell.ssvOpen
                  ? 'bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border-rose-500/50 hover:border-rose-400'
                  : 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 border-emerald-400 shadow-[0_0_12px_rgba(16,185,129,0.3)]'
              }`}
            >
              <ShieldAlert className="w-4 h-4" />
              <span>
                {selectedWell.ssvOpen
                  ? 'DISPARAR CIERRE DE EMERGENCIA SSV'
                  : 'ORDENAR REAPERTURA HIDRÁULICA SSV'}
              </span>
            </button>
          </div>
        </div>

        {/* Columna 2: Control de Válvula de Estrangulamiento (Choke) */}
        <div className="bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md flex flex-col justify-between">
          <div>
            <div className="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-4">
              <div>
                <span className="text-[10px] font-mono-data text-slate-400 uppercase font-semibold">
                  REGULACIÓN DE CAUDAL Y PRESIÓN
                </span>
                <h3 className="font-display text-base font-bold text-white mt-0.5">
                  Válvula Choke de Producción
                </h3>
              </div>
              <div className="flex items-center gap-2">
                <span className="text-xs font-mono-data text-slate-400">APERTURA:</span>
                <span className="font-mono-data text-xl font-bold text-amber-400">
                  {selectedWell.chokeOpening.toFixed(1)}%
                </span>
              </div>
            </div>

            {/* Slider de Alta Precisión */}
            <div className="mb-6">
              <div className="flex justify-between text-[10px] font-mono-data text-slate-400 mb-2 font-medium">
                <span className="text-rose-400">0% (CERRADA)</span>
                <span>25%</span>
                <span className="text-emerald-400">50% (NOMINAL)</span>
                <span>75%</span>
                <span className="text-rose-400">100% (MÁX)</span>
              </div>

              <div className="relative py-2">
                <input
                  type="range"
                  min="0"
                  max="100"
                  step="0.5"
                  disabled={!selectedWell.ssvOpen}
                  value={selectedWell.chokeOpening}
                  onChange={(e) => handleChokeChange(parseFloat(e.target.value))}
                  className="w-full h-3 bg-slate-950 rounded-lg appearance-none cursor-pointer border border-slate-800 accent-sky-400 disabled:opacity-40"
                />

                {/* Marcas de graduación visuales */}
                <div className="flex justify-between px-1 mt-1">
                  {[0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100].map((tick) => (
                    <div
                      key={tick}
                      className={`h-1.5 w-0.5 ${
                        tick === 0 || tick === 100
                          ? 'bg-rose-500'
                          : tick === 50
                          ? 'bg-emerald-400'
                          : 'bg-slate-700'
                      }`}
                    />
                  ))}
                </div>
              </div>

              <div className="grid grid-cols-2 gap-2 mt-4 text-xs font-mono-data">
                <div className="bg-slate-950 p-3 rounded-lg border border-slate-800/90">
                  <span className="text-slate-400 text-[10px] block">DIÁMETRO DE ORIFICIO</span>
                  <span className="font-bold text-white text-base">
                    {(selectedWell.chokeOpening * 0.64).toFixed(2)} mm
                  </span>
                </div>
                <div className="bg-slate-950 p-3 rounded-lg border border-slate-800/90">
                  <span className="text-slate-400 text-[10px] block">CAÍDA DE PRESIÓN (dP)</span>
                  <span className="font-bold text-sky-400 text-base">
                    {(2200 - selectedWell.chokeOpening * 12).toFixed(0)} PSI
                  </span>
                </div>
              </div>
            </div>

            {/* Botones de Ajuste Rápido */}
            <div className="pt-4 border-t border-slate-800/80">
              <span className="text-[10px] font-mono-data text-slate-400 uppercase block mb-2 font-semibold">
                AJUSTES PREDETERMINADOS (SETPOINTS RÁPIDOS):
              </span>
              <div className="grid grid-cols-5 gap-2">
                {[15, 25, 42.8, 65, 80].map((preset) => (
                  <button
                    key={preset}
                    disabled={!selectedWell.ssvOpen}
                    onClick={() => handleChokeChange(preset)}
                    className="py-2 text-xs font-mono-data bg-slate-950 hover:bg-slate-800 text-slate-200 border border-slate-800 rounded-md transition-all font-semibold disabled:opacity-40"
                  >
                    {preset}%
                  </button>
                ))}
              </div>
            </div>
          </div>

          <div className="mt-4 p-3 bg-slate-950 rounded-lg border border-slate-800/90 text-xs font-mono-data text-slate-400">
            CONTROL PID EN MODO SUPERVISORIO CON RESTRICCIÓN DE VELOCIDAD DE VÁLVULA A 2%/SEG.
          </div>
        </div>

        {/* Columna 3: Bomba ESP y Actuadores Auxiliares */}
        <div className="flex flex-col gap-4">
          {/* Bomba ESP */}
          <div className="bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md">
            <div className="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-4">
              <div>
                <span className="text-[10px] font-mono-data text-slate-400 uppercase font-semibold">
                  LEVANTAMIENTO ARTIFICIAL
                </span>
                <h3 className="font-display text-base font-bold text-white mt-0.5">
                  Variador de Frecuencia (VFD) ESP
                </h3>
              </div>
              <div className="flex items-center gap-2">
                <span className="text-xs font-mono-data text-slate-400">SALIDA:</span>
                <span className="font-mono-data text-xl font-bold text-emerald-400">
                  {selectedWell.espFrequency.toFixed(1)} Hz
                </span>
              </div>
            </div>

            <div className="mb-4">
              <div className="flex justify-between text-[10px] font-mono-data text-slate-400 mb-2">
                <span>30.0 Hz (MÍN ENFRIAMIENTO)</span>
                <span>60.0 Hz (RED NOMINAL)</span>
                <span className="text-amber-400">65.0 Hz (PICO)</span>
              </div>

              <input
                type="range"
                min="30"
                max="65"
                step="0.5"
                disabled={!selectedWell.ssvOpen}
                value={selectedWell.espFrequency}
                onChange={(e) => handleEspFreqChange(parseFloat(e.target.value))}
                className="w-full h-3 bg-slate-950 rounded-lg appearance-none cursor-pointer border border-slate-800 accent-emerald-500 disabled:opacity-40"
              />

              <div className="grid grid-cols-3 gap-2 mt-4 text-xs font-mono-data">
                <div className="bg-slate-950 p-2.5 rounded-lg border border-slate-800/90 text-center">
                  <span className="text-slate-400 text-[10px] block">RPM MOTOR</span>
                  <span className="font-bold text-white text-sm">
                    {Math.round(selectedWell.espFrequency * 58)}
                  </span>
                </div>
                <div className="bg-slate-950 p-2.5 rounded-lg border border-slate-800/90 text-center">
                  <span className="text-slate-400 text-[10px] block">CONSUMO</span>
                  <span className="font-bold text-sky-400 text-sm">
                    {(selectedWell.espFrequency * 1.62).toFixed(1)} A
                  </span>
                </div>
                <div className="bg-slate-950 p-2.5 rounded-lg border border-slate-800/90 text-center">
                  <span className="text-slate-400 text-[10px] block">DEVÁNADO</span>
                  <span className="font-bold text-amber-400 text-sm">
                    {(115 + selectedWell.espFrequency * 0.4).toFixed(1)} °C
                  </span>
                </div>
              </div>
            </div>
          </div>

          {/* Actuadores Discretos y Dosificación de Químico */}
          <div className="bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md flex-1">
            <span className="text-[10px] font-mono-data text-slate-400 uppercase block mb-3 font-semibold">
              ACTUADORES AUXILIARES Y PROTECCIÓN QUÍMICA
            </span>

            <div className="space-y-3">
              {/* Interruptor 1: Inyección de Químico */}
              <div className="flex items-center justify-between p-3.5 bg-slate-950 rounded-lg border border-slate-800/90">
                <div>
                  <div className="flex items-center gap-2 text-xs font-mono-data font-bold text-white">
                    <Droplets className="w-4 h-4 text-sky-400" />
                    <span>INHIBIDOR DE CORROSIÓN</span>
                  </div>
                  <span className="text-[10px] font-mono-data text-slate-400">
                    Dosificación continua: 15.2 L/día
                  </span>
                </div>

                <button
                  type="button"
                  onClick={toggleChemicalInjection}
                  className={`px-3 py-1.5 text-xs font-mono-data font-bold uppercase rounded-md transition-all border ${
                    selectedWell.chemicalInjection
                      ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50'
                      : 'bg-slate-900 text-slate-400 border-slate-800'
                  }`}
                >
                  {selectedWell.chemicalInjection ? 'ACTIVO (ON)' : 'APAGADO (OFF)'}
                </button>
              </div>

              {/* Interruptor 2: Válvula de Venteo de Anular */}
              <div className="flex items-center justify-between p-3.5 bg-slate-950 rounded-lg border border-slate-800/90">
                <div>
                  <div className="flex items-center gap-2 text-xs font-mono-data font-bold text-white">
                    <Flame className="w-4 h-4 text-amber-400" />
                    <span>ALIVIO DE ANULAR A ANTORCHA</span>
                  </div>
                  <span className="text-[10px] font-mono-data text-slate-400">
                    Despresurización de sobrepresión en casing
                  </span>
                </div>

                <button
                  type="button"
                  onClick={toggleAnnulusBleed}
                  className={`px-3 py-1.5 text-xs font-mono-data font-bold uppercase rounded-md transition-all border ${
                    selectedWell.annulusBleedOpen
                      ? 'bg-amber-500/20 text-amber-400 border-amber-500/50'
                      : 'bg-slate-900 text-slate-400 border-slate-800'
                  }`}
                >
                  {selectedWell.annulusBleedOpen ? 'VENTILANDO' : 'SEGURO (CERRADO)'}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};


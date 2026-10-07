import React, { useState } from 'react';
import { Camera, MapPin, Layers, Crosshair, FileCheck, CheckCircle2, ShieldCheck } from 'lucide-react';
import { SplitBadge } from '../components/SplitBadge';

interface SensorPin {
  id: string;
  label: string;
  tag: string;
  x: number; // percentage
  y: number;
  value: string;
  unit: string;
  status: 'nominal' | 'warning' | 'critical';
}

const FACILITY_PINS: SensorPin[] = [
  {
    id: 'pin-1',
    label: 'TREN DE SEPARACIÓN PRIMARIA A',
    tag: 'V-201',
    x: 48,
    y: 38,
    value: '820.0',
    unit: 'PSI',
    status: 'nominal',
  },
  {
    id: 'pin-2',
    label: 'COMPRESOR BOOSTER DE GAS',
    tag: 'K-301',
    x: 68,
    y: 30,
    value: '1,120.0',
    unit: 'PSI',
    status: 'nominal',
  },
  {
    id: 'pin-3',
    label: 'CLÚSTER DE CABEZALES PAD-04',
    tag: 'PAD-04A',
    x: 24,
    y: 62,
    value: '1,842.5',
    unit: 'PSI',
    status: 'nominal',
  },
  {
    id: 'pin-4',
    label: 'MÚLTIPLE DE BOMBEO DE EXPORTACIÓN',
    tag: 'P-104',
    x: 78,
    y: 72,
    value: '14,820',
    unit: 'BPD',
    status: 'nominal',
  },
];

const WELLHEAD_PINS: SensorPin[] = [
  {
    id: 'wh-1',
    label: 'VÁLVULA DE SEGURIDAD EN SUPERFICIE (SSV)',
    tag: 'SSV-101',
    x: 48,
    y: 52,
    value: 'ABIERTA',
    unit: 'RETENIDA',
    status: 'nominal',
  },
  {
    id: 'wh-2',
    label: 'VÁLVULA MAESTRA SUPERIOR DE FLUJO',
    tag: 'UMV-102',
    x: 48,
    y: 35,
    value: '1,842.5',
    unit: 'PSI',
    status: 'nominal',
  },
  {
    id: 'wh-3',
    label: 'ESTRANGULADOR DE LÍNEA LATERAL (CHOKE)',
    tag: 'PWV-103',
    x: 74,
    y: 44,
    value: '42.8%',
    unit: 'ORIFICIO',
    status: 'nominal',
  },
  {
    id: 'wh-4',
    label: 'TRANSMISOR DE PRESIÓN ANULAR (CASING)',
    tag: 'CP-104',
    x: 26,
    y: 68,
    value: '642.0',
    unit: 'PSI',
    status: 'nominal',
  },
];

export const FacilityVisualScreen: React.FC = () => {
  const [activeView, setActiveView] = useState<'facility' | 'wellhead' | 'cctv'>('facility');
  const [selectedPin, setSelectedPin] = useState<SensorPin>(FACILITY_PINS[0]);
  const [cameraFeed, setCameraFeed] = useState<'CAM-01 MÚLTIPLE' | 'CAM-02 SEPARADOR' | 'CAM-03 ANTORCHA'>('CAM-01 MÚLTIPLE');
  const [reportGenerated, setReportGenerated] = useState<string | null>(null);

  const currentPins = activeView === 'facility' ? FACILITY_PINS : activeView === 'wellhead' ? WELLHEAD_PINS : [];

  const handleGenerateReport = (tag: string) => {
    setReportGenerated(`Informe de integridad y calibración técnica emitido con éxito para ${tag}`);
    setTimeout(() => {
      setReportGenerated(null);
    }, 4000);
  };

  return (
    <div className="flex-1 flex flex-col gap-4 p-4 lg:p-6 max-w-[1780px] mx-auto w-full overflow-y-auto">
      {/* Barra Superior de Control y Perspectivas */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 shadow-sm backdrop-blur-md">
        <div className="flex items-center gap-3">
          <div className="p-2.5 bg-emerald-500/10 border border-emerald-500/30 rounded-lg text-emerald-400">
            <Layers className="w-5 h-5" />
          </div>
          <div>
            <h1 className="font-display text-base font-bold uppercase tracking-wider text-white">
              Esquemas Visuales de Instalación y Telemetría Óptica
            </h1>
            <div className="text-xs font-mono-data text-slate-400 flex items-center gap-2 mt-0.5">
              <span>CAPA DE GEMELO DIGITAL 3D</span>
              <span aria-hidden="true">·</span>
              <span>GEO-REFERENCIACIÓN WGS84</span>
              <span aria-hidden="true">·</span>
              <span className="text-emerald-400 font-semibold">RESOLUCIÓN HD EN VIVO</span>
            </div>
          </div>
        </div>

        {/* Selector de Perspectiva Visual */}
        <div className="flex items-center gap-2 bg-slate-950 p-1.5 rounded-lg border border-slate-800/90 overflow-x-auto">
          <button
            onClick={() => {
              setActiveView('facility');
              setSelectedPin(FACILITY_PINS[0]);
            }}
            className={`px-3.5 py-1.5 text-xs font-mono-data uppercase rounded-md transition-all flex items-center gap-2 font-semibold ${
              activeView === 'facility'
                ? 'bg-slate-800 text-sky-400 border border-sky-500/40 shadow-sm'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            <MapPin className="w-3.5 h-3.5 text-sky-400" />
            <span>PLANTA ISOMÉTRICA 3D</span>
          </button>

          <button
            onClick={() => {
              setActiveView('wellhead');
              setSelectedPin(WELLHEAD_PINS[0]);
            }}
            className={`px-3.5 py-1.5 text-xs font-mono-data uppercase rounded-md transition-all flex items-center gap-2 font-semibold ${
              activeView === 'wellhead'
                ? 'bg-slate-800 text-emerald-400 border border-emerald-500/40 shadow-sm'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            <Crosshair className="w-3.5 h-3.5 text-emerald-400" />
            <span>ÁRBOL DE NAVIDAD (CABEZAL)</span>
          </button>

          <button
            onClick={() => setActiveView('cctv')}
            className={`px-3.5 py-1.5 text-xs font-mono-data uppercase rounded-md transition-all flex items-center gap-2 font-semibold ${
              activeView === 'cctv'
                ? 'bg-slate-800 text-amber-400 border border-amber-500/40 shadow-sm'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            <Camera className="w-3.5 h-3.5 text-amber-400" />
            <span>VÍDEOVIGILANCIA CCTV</span>
          </button>
        </div>
      </div>

      {/* Notificación de informe generado */}
      {reportGenerated && (
        <div className="flex items-center gap-3 bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl text-xs font-mono-data shadow-sm animate-fadeIn">
          <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
          <span>{reportGenerated}</span>
        </div>
      )}

      {/* Escenario Visual Principal y Panel Lateral */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-4 flex-1">
        {/* Visor Visual (9 columnas en pantallas grandes) */}
        <div className="lg:col-span-8 xl:col-span-9 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 flex flex-col justify-between shadow-sm backdrop-blur-md">
          {/* Cabecera del Visor */}
          <div className="flex items-center justify-between border-b border-slate-800 pb-3 mb-3">
            <div className="flex items-center gap-2.5">
              <span className="font-display text-xs font-bold uppercase tracking-wider text-white">
                {activeView === 'facility' && 'MODELO ISOMÉTRICO: REFINERÍA Y MÚLTIPLES DE PROCESO'}
                {activeView === 'wellhead' && 'ESQUEMA DE ALTA PRESIÓN: ÁRBOL DE NAVIDAD Y VÁLVULAS SSV'}
                {activeView === 'cctv' && `CÁMARA ÓPTICA INDUSTRIAL: ${cameraFeed}`}
              </span>
              <span className="text-[10px] font-mono-data text-slate-400">
                [CAMPO VISUAL: 94° / FLIR ÓPTICO]
              </span>
            </div>

            {activeView === 'cctv' && (
              <div className="flex items-center gap-1.5">
                {(['CAM-01 MÚLTIPLE', 'CAM-02 SEPARADOR', 'CAM-03 ANTORCHA'] as const).map((cam) => (
                  <button
                    key={cam}
                    onClick={() => setCameraFeed(cam)}
                    className={`px-2.5 py-1 text-[10px] font-mono-data uppercase rounded-md border font-semibold transition-all ${
                      cameraFeed === cam
                        ? 'bg-amber-500/20 border-amber-500/50 text-amber-300'
                        : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-slate-200'
                    }`}
                  >
                    {cam.split(' ')[0]}
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Contenedor de Imagen y Pines Interactivos */}
          <div className="relative w-full flex-1 bg-slate-950 rounded-lg border border-slate-800 overflow-hidden flex items-center justify-center min-h-[460px]">
            {/* Vista 1: Planta Isométrica */}
            {activeView === 'facility' && (
              <div className="relative w-full h-full flex items-center justify-center">
                <img
                  src="/src/assets/images/scada_facility_isometric_1791404941231.jpg"
                  alt="Esquema Isométrico de Complejo Industrial Petrolero"
                  referrerPolicy="no-referrer"
                  className="w-full h-full object-cover max-h-[580px] filter brightness-95 contrast-105"
                />

                {/* Pines de Sensores Superpuestos */}
                {currentPins.map((pin) => {
                  const isSelected = selectedPin.id === pin.id;
                  return (
                    <button
                      key={pin.id}
                      onClick={() => setSelectedPin(pin)}
                      style={{ left: `${pin.x}%`, top: `${pin.y}%` }}
                      className={`absolute -translate-x-1/2 -translate-y-1/2 z-20 group transition-transform ${
                        isSelected ? 'scale-110' : 'hover:scale-105'
                      }`}
                    >
                      <div className="relative flex items-center justify-center">
                        <span className="animate-ping absolute inline-flex h-6 w-6 rounded-full bg-emerald-500 opacity-40"></span>
                        <div
                          className={`px-2.5 py-1 rounded-md text-[10px] font-mono-data font-bold tracking-wider flex items-center gap-2 border shadow-lg transition-all ${
                            isSelected
                              ? 'bg-sky-500 text-slate-950 border-white ring-2 ring-sky-400/50'
                              : 'bg-slate-900/90 text-emerald-400 border-emerald-500/40 hover:border-emerald-400'
                          }`}
                        >
                          <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                          <span>{pin.tag}</span>
                          <span className="text-white/90">
                            {pin.value} {pin.unit}
                          </span>
                        </div>
                      </div>
                    </button>
                  );
                })}
              </div>
            )}

            {/* Vista 2: Árbol de Navidad */}
            {activeView === 'wellhead' && (
              <div className="relative w-full h-full flex items-center justify-center bg-slate-950 p-2">
                <img
                  src="/src/assets/images/scada_wellhead_schematic_1791404952073.jpg"
                  alt="Plano Técnico de Árbol de Navidad de Cabezal"
                  referrerPolicy="no-referrer"
                  className="w-full h-full object-contain max-h-[580px] filter brightness-95"
                />

                {/* Pines de Cabezal Superpuestos */}
                {currentPins.map((pin) => {
                  const isSelected = selectedPin.id === pin.id;
                  return (
                    <button
                      key={pin.id}
                      onClick={() => setSelectedPin(pin)}
                      style={{ left: `${pin.x}%`, top: `${pin.y}%` }}
                      className={`absolute -translate-x-1/2 -translate-y-1/2 z-20 transition-transform ${
                        isSelected ? 'scale-110' : 'hover:scale-105'
                      }`}
                    >
                      <div
                        className={`px-2.5 py-1 rounded-md text-[10px] font-mono-data font-bold tracking-wider flex items-center gap-2 border shadow-lg transition-all ${
                          isSelected
                            ? 'bg-emerald-500 text-slate-950 border-white ring-2 ring-emerald-400/50'
                            : 'bg-slate-900/95 text-sky-400 border-sky-500/40 hover:border-sky-400'
                        }`}
                      >
                        <Crosshair className="w-3.5 h-3.5 text-amber-400" />
                        <span>{pin.tag}</span>
                        <span className="text-slate-200">
                          {pin.value} {pin.unit}
                        </span>
                      </div>
                    </button>
                  );
                })}
              </div>
            )}

            {/* Vista 3: Transmisión Óptica CCTV */}
            {activeView === 'cctv' && (
              <div className="relative w-full h-full flex items-center justify-center overflow-hidden">
                <img
                  src="/src/assets/images/scada_control_room_feed_1791404961503.jpg"
                  alt="Cámara de Seguridad Industrial Múltiple de Bombeo"
                  referrerPolicy="no-referrer"
                  className="w-full h-full object-cover max-h-[580px] filter contrast-115 saturate-80"
                />

                {/* HUD Táctico CCTV Superpuesto */}
                <div className="absolute inset-0 pointer-events-none p-4 flex flex-col justify-between">
                  {/* Información Superior */}
                  <div className="flex items-center justify-between text-xs font-mono-data text-emerald-400 bg-slate-950/80 px-3.5 py-2 rounded-lg border border-emerald-500/30 backdrop-blur-xs">
                    <div className="flex items-center gap-2.5">
                      <span className="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping" />
                      <span className="font-bold">REC · TRANSMISIÓN EN DIRECTO 25 FPS</span>
                      <span>·</span>
                      <span className="text-white">{cameraFeed}</span>
                    </div>
                    <div className="flex items-center gap-3">
                      <span>FPS: 24.9</span>
                      <span>ILUMINADOR IR: 850nm ACTIVO</span>
                    </div>
                  </div>

                  {/* Retícula Central */}
                  <div className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 flex items-center justify-center opacity-60">
                    <div className="w-20 h-20 border border-emerald-400/60 rounded-full flex items-center justify-center">
                      <div className="w-2 h-2 bg-emerald-400 rounded-full" />
                    </div>
                  </div>

                  {/* Telemetría Inferior */}
                  <div className="flex items-center justify-between text-[11px] font-mono-data text-sky-300 bg-slate-950/80 px-3.5 py-2 rounded-lg border border-sky-500/30 backdrop-blur-xs">
                    <span>PANEO: 142.4° / INCLINACIÓN: -12.1° / ZOOM ÓPTICO: 1.8X</span>
                    <span className="text-emerald-400 font-semibold">DETECCIÓN ÓPTICA DE MOVIMIENTO: ÁREA SEGURA</span>
                  </div>
                </div>
              </div>
            )}
          </div>

          {/* Pie del Visor */}
          <div className="flex flex-wrap items-center justify-between text-xs font-mono-data text-slate-400 pt-3 border-t border-slate-800 mt-2 gap-2">
            <span>SISTEMA DE COORDENADAS: WGS84 EPSG:4326 · ELEVACIÓN 742 m</span>
            <span>HOMOLOGACIÓN DE SEGURIDAD: CLASIFICACIÓN ATEX ZONA 1 / SIL-3</span>
          </div>
        </div>

        {/* Panel Lateral de Inspección del Activo (3 o 4 columnas) */}
        <div className="lg:col-span-4 xl:col-span-3 bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 flex flex-col justify-between shadow-sm backdrop-blur-md">
          <div>
            <div className="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
              <div>
                <span className="text-[10px] font-mono-data uppercase text-slate-400 block font-semibold">
                  ELEMENTO EN INSPECCIÓN
                </span>
                <h2 className="font-display text-sm font-bold text-white mt-0.5">
                  {selectedPin.label}
                </h2>
              </div>
              <SplitBadge prefix="TAG" value={selectedPin.tag} severity="info" />
            </div>

            {/* Lectura de Telemetría Instantánea */}
            <div className="space-y-3.5 mb-4">
              <div className="bg-slate-950 p-4 rounded-xl border border-slate-800">
                <span className="text-[10px] font-mono-data text-slate-400 uppercase block mb-1 font-semibold">
                  LECTURA TELEMÉTRICA EN VIVO
                </span>
                <div className="font-mono-data text-2xl font-bold text-emerald-400 flex items-baseline gap-2">
                  <span>{selectedPin.value}</span>
                  <span className="text-xs text-slate-400 font-normal">{selectedPin.unit}</span>
                </div>
              </div>

              {/* Especificaciones de Ingeniería */}
              <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800 text-xs font-mono-data space-y-2.5">
                <div className="flex justify-between">
                  <span className="text-slate-400">TIPO DE EQUIPO:</span>
                  <span className="text-slate-200 font-semibold">CONTENCIÓN DE PRESIÓN</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-400">NORMATIVA DE DISEÑO:</span>
                  <span className="text-slate-200 font-semibold">API-6A / ISO-10423</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-400">PRÓXIMO MANTENIMIENTO:</span>
                  <span className="text-emerald-400 font-semibold">2,400 HORAS OPERATIVAS</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-400">ESTADO SIL:</span>
                  <span className="text-emerald-400 font-semibold flex items-center gap-1">
                    <ShieldCheck className="w-3.5 h-3.5 text-emerald-400" /> ACTIVO / SEGURO
                  </span>
                </div>
              </div>

              {/* Bitácora de Inspección Óptica */}
              <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800 text-xs font-mono-data text-slate-400 space-y-1.5">
                <div className="text-[10px] uppercase font-bold text-slate-300">
                  REGISTRO TERMOGRÁFICO Y ÓPTICO
                </div>
                <p className="leading-relaxed text-[11px] text-slate-300">
                  El barrido por termografía infrarroja indica un gradiente térmico nominal entre bridas.
                  Cero fugas de metano o hidrocarburos volátiles detectadas por el sensor óptico OGI.
                </p>
              </div>
            </div>
          </div>

          <div className="border-t border-slate-800 pt-4">
            <button
              onClick={() => handleGenerateReport(selectedPin.tag)}
              className="w-full py-2.5 px-4 text-xs font-mono-data uppercase bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 hover:border-slate-600 rounded-lg transition-all font-semibold flex items-center justify-center gap-2 shadow-sm"
            >
              <FileCheck className="w-4 h-4 text-emerald-400" />
              <span>GENERAR INFORME TÉCNICO DE SALUD</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

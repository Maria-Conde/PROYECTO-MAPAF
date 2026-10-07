import React, { useState, useMemo } from 'react';
import {
  ResponsiveContainer,
  ComposedChart,
  Line,
  Area,
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ReferenceLine,
  Brush,
  Cell,
} from 'recharts';
import { SpectralPoint, TelemetryMetric } from '../types/scada';
import {
  Download,
  BarChart2,
  TrendingUp,
  Activity,
  Layers,
  Calendar,
  Filter,
  CheckCircle2,
  Gauge,
  Sliders,
  Maximize2,
} from 'lucide-react';
import { SplitBadge } from '../components/SplitBadge';

interface HistorianScreenProps {
  metrics: TelemetryMetric[];
  spectralData: SpectralPoint[];
  onExportCsv: () => void;
}

type TimeRangeOption = '15m' | '1h' | '6h' | '24h' | '7d';

interface HistoricalDataPoint {
  time: string;
  timestamp: number;
  tubingPressure: number;
  casingPressure: number;
  flowRate: number;
  espFrequency: number;
  vibration: number;
  waterCut: number;
}

export const HistorianScreen: React.FC<HistorianScreenProps> = ({
  metrics,
  spectralData,
  onExportCsv,
}) => {
  const [timeRange, setTimeRange] = useState<TimeRangeOption>('1h');
  const [selectedMetrics, setSelectedMetrics] = useState<{
    tubingPressure: boolean;
    casingPressure: boolean;
    flowRate: boolean;
    vibration: boolean;
    espFrequency: boolean;
  }>({
    tubingPressure: true,
    casingPressure: true,
    flowRate: false,
    vibration: false,
    espFrequency: false,
  });

  const [chartType, setChartType] = useState<'area' | 'line'>('area');
  const [exportedNotice, setExportedNotice] = useState(false);
  const [showLimits, setShowLimits] = useState(true);

  // Generación de serie temporal sintética de alta resolución según el rango seleccionado
  const historicalData: HistoricalDataPoint[] = useMemo(() => {
    let pointsCount = 40;
    let stepMinutes = 1.5;

    if (timeRange === '15m') {
      pointsCount = 30;
      stepMinutes = 0.5;
    } else if (timeRange === '1h') {
      pointsCount = 45;
      stepMinutes = 1.33;
    } else if (timeRange === '6h') {
      pointsCount = 60;
      stepMinutes = 6;
    } else if (timeRange === '24h') {
      pointsCount = 72;
      stepMinutes = 20;
    } else if (timeRange === '7d') {
      pointsCount = 84;
      stepMinutes = 120;
    }

    const now = Date.now();
    const data: HistoricalDataPoint[] = [];

    for (let i = pointsCount - 1; i >= 0; i--) {
      const timeOffset = i * stepMinutes * 60 * 1000;
      const pointTime = new Date(now - timeOffset);
      const timeStr =
        timeRange === '7d' || timeRange === '24h'
          ? pointTime.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' }) +
            ' ' +
            pointTime.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
          : pointTime.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

      // Ondas senoidales industriales con armónicos y ruido realista
      const t = (pointsCount - i) * 0.18;
      const tubing = Number((1840 + Math.sin(t) * 45 + Math.cos(t * 2.3) * 18 + ((i % 5) - 2) * 4).toFixed(1));
      const casing = Number((640 + Math.sin(t * 0.7) * 22 + Math.sin(t * 1.5) * 8).toFixed(1));
      const flow = Number((14800 + Math.sin(t * 0.9) * 320 + Math.cos(t * 1.2) * 150).toFixed(0));
      const espFreq = Number((58.5 + Math.sin(t * 0.4) * 1.2).toFixed(1));
      const vib = Number((2.1 + Math.sin(t * 3.1) * 0.6 + ((i % 3) === 0 ? 0.3 : 0)).toFixed(2));
      const bsw = Number((12.4 + Math.cos(t * 0.5) * 1.1).toFixed(1));

      data.push({
        time: timeStr,
        timestamp: pointTime.getTime(),
        tubingPressure: tubing,
        casingPressure: casing,
        flowRate: flow,
        espFrequency: espFreq,
        vibration: vib,
        waterCut: bsw,
      });
    }

    return data;
  }, [timeRange]);

  // Métricas estadísticas para el rango seleccionado
  const stats = useMemo(() => {
    if (!historicalData.length) return null;
    const tubings = historicalData.map((d) => d.tubingPressure);
    const flows = historicalData.map((d) => d.flowRate);
    const minTubing = Math.min(...tubings);
    const maxTubing = Math.max(...tubings);
    const avgTubing = Number((tubings.reduce((a, b) => a + b, 0) / tubings.length).toFixed(1));
    const avgFlow = Number((flows.reduce((a, b) => a + b, 0) / flows.length).toFixed(0));

    return {
      minTubing,
      maxTubing,
      avgTubing,
      avgFlow,
    };
  }, [historicalData]);

  const handleExport = () => {
    onExportCsv();
    setExportedNotice(true);
    setTimeout(() => setExportedNotice(false), 3000);
  };

  const toggleMetric = (key: keyof typeof selectedMetrics) => {
    setSelectedMetrics((prev) => {
      const next = { ...prev, [key]: !prev[key] };
      // Evitar desmarcar todas
      const hasAny = Object.values(next).some(Boolean);
      return hasAny ? next : prev;
    });
  };

  // Tooltip personalizado de Recharts con diseño SCADA de alta densidad
  const CustomSCADATooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-slate-900/95 border border-slate-700/80 p-3.5 rounded-xl shadow-2xl backdrop-blur-md text-xs font-mono-data min-w-[240px]">
          <div className="border-b border-slate-800 pb-2 mb-2 flex items-center justify-between text-slate-400">
            <span className="font-semibold text-slate-300">{label}</span>
            <span className="text-[10px] text-emerald-400">REGISTRO SIL-3</span>
          </div>

          <div className="space-y-1.5">
            {payload.map((entry: any, index: number) => {
              let unit = 'PSI';
              if (entry.dataKey === 'flowRate') unit = 'BPD';
              if (entry.dataKey === 'vibration') unit = 'mm/s';
              if (entry.dataKey === 'espFrequency') unit = 'Hz';

              return (
                <div key={index} className="flex items-center justify-between gap-4">
                  <div className="flex items-center gap-2">
                    <span
                      className="w-2.5 h-2.5 rounded-sm"
                      style={{ backgroundColor: entry.color }}
                    />
                    <span className="text-slate-300">{entry.name}:</span>
                  </div>
                  <span className="font-bold text-white">
                    {entry.value} <span className="text-slate-500 font-normal">{unit}</span>
                  </span>
                </div>
              );
            })}
          </div>
        </div>
      );
    }
    return null;
  };

  return (
    <div className="flex-1 flex flex-col gap-4 p-4 lg:p-6 max-w-[1780px] mx-auto w-full overflow-y-auto">
      {/* Cabecera del Historiador */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 shadow-sm backdrop-blur-md">
        <div className="flex items-center gap-3">
          <div className="p-2.5 bg-sky-500/10 border border-sky-500/30 rounded-lg text-sky-400">
            <TrendingUp className="w-5 h-5" />
          </div>
          <div>
            <h1 className="font-display text-base font-bold uppercase tracking-wider text-white">
              Historiador de Planta y Diagnóstico de Tendencias (D3 / Recharts)
            </h1>
            <div className="text-xs font-mono-data text-slate-400 flex items-center gap-2 mt-0.5">
              <span>SERIES TEMPORALES ALTA RESOLUCIÓN</span>
              <span aria-hidden="true">·</span>
              <span className="text-emerald-400 font-semibold">TASA DE REGISTRO 100 Hz</span>
              <span aria-hidden="true">·</span>
              <span>BUFFER DE {historicalData.length} PUNTOS</span>
            </div>
          </div>
        </div>

        {/* Selector de Rango de Tiempo y Botón de Exportación */}
        <div className="flex items-center gap-3 flex-wrap">
          {/* Opciones de Rango Temporal */}
          <div className="flex items-center bg-slate-950 p-1.5 rounded-lg border border-slate-800/90">
            <Calendar className="w-3.5 h-3.5 text-slate-500 ml-1.5 mr-1" />
            {(
              [
                { id: '15m', label: '15 MIN' },
                { id: '1h', label: '1 HORA' },
                { id: '6h', label: '6 HORAS' },
                { id: '24h', label: '24 HORAS' },
                { id: '7d', label: '7 DÍAS' },
              ] as const
            ).map((tr) => (
              <button
                key={tr.id}
                onClick={() => setTimeRange(tr.id)}
                className={`px-3 py-1.5 text-xs font-mono-data uppercase rounded-md transition-all font-semibold ${
                  timeRange === tr.id
                    ? 'bg-slate-800 text-sky-400 border border-sky-500/40 shadow-sm'
                    : 'text-slate-400 hover:text-slate-200'
                }`}
              >
                {tr.label}
              </button>
            ))}
          </div>

          <button
            onClick={handleExport}
            className="px-4 py-2 text-xs font-mono-data uppercase bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-lg transition-all flex items-center gap-2 shadow-[0_0_12px_rgba(16,185,129,0.3)]"
          >
            {exportedNotice ? <CheckCircle2 className="w-4 h-4" /> : <Download className="w-4 h-4" />}
            <span>{exportedNotice ? 'DESCARGADO CON ÉXITO' : 'EXPORTAR REPORTE CSV'}</span>
          </button>
        </div>
      </div>

      {/* Cinta de Estadísticas de la Ventana Temporal Seleccionada */}
      {stats && (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div className="bg-slate-900/90 rounded-xl border border-slate-800 p-3.5 flex flex-col justify-between">
            <span className="text-[10px] font-mono-data uppercase text-slate-400 font-semibold">
              PRESIÓN MÍNIMA TUBING ({timeRange.toUpperCase()})
            </span>
            <div className="font-mono-data text-xl font-bold text-sky-400 mt-1">
              {stats.minTubing} <span className="text-xs text-slate-500 font-normal">PSI</span>
            </div>
          </div>

          <div className="bg-slate-900/90 rounded-xl border border-slate-800 p-3.5 flex flex-col justify-between">
            <span className="text-[10px] font-mono-data uppercase text-slate-400 font-semibold">
              PRESIÓN MÁXIMA PICO ({timeRange.toUpperCase()})
            </span>
            <div className="font-mono-data text-xl font-bold text-amber-400 mt-1">
              {stats.maxTubing} <span className="text-xs text-slate-500 font-normal">PSI</span>
            </div>
          </div>

          <div className="bg-slate-900/90 rounded-xl border border-slate-800 p-3.5 flex flex-col justify-between">
            <span className="text-[10px] font-mono-data uppercase text-slate-400 font-semibold">
              PRESIÓN PROMEDIO PONDERADA
            </span>
            <div className="font-mono-data text-xl font-bold text-emerald-400 mt-1">
              {stats.avgTubing} <span className="text-xs text-slate-500 font-normal">PSI</span>
            </div>
          </div>

          <div className="bg-slate-900/90 rounded-xl border border-slate-800 p-3.5 flex flex-col justify-between">
            <span className="text-[10px] font-mono-data uppercase text-slate-400 font-semibold">
              CAUDAL MEDIO DE PRODUCCIÓN
            </span>
            <div className="font-mono-data text-xl font-bold text-purple-400 mt-1">
              {stats.avgFlow} <span className="text-xs text-slate-500 font-normal">BPD</span>
            </div>
          </div>
        </div>
      )}

      {/* Contenedor Principal del Gráfico Interactivo de Tendencias Recharts */}
      <div className="bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 flex flex-col shadow-sm backdrop-blur-md">
        {/* Barra de Controles de Serie y Filtros de Canales */}
        <div className="flex flex-wrap items-center justify-between border-b border-slate-800/80 pb-3.5 mb-4 gap-3">
          <div className="flex items-center gap-2 flex-wrap">
            <span className="text-xs font-mono-data uppercase text-slate-400 font-semibold flex items-center gap-1.5 mr-1">
              <Filter className="w-3.5 h-3.5" /> CANALES ACTIVOS:
            </span>

            {/* Conmutadores de Variables */}
            <button
              onClick={() => toggleMetric('tubingPressure')}
              className={`px-3 py-1.5 text-xs font-mono-data uppercase rounded-lg border transition-all flex items-center gap-1.5 font-semibold ${
                selectedMetrics.tubingPressure
                  ? 'bg-emerald-950/70 border-emerald-500/70 text-emerald-300 shadow-xs'
                  : 'bg-slate-950 border-slate-800 text-slate-500'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-emerald-400" />
              <span>TUBING (WHTP)</span>
            </button>

            <button
              onClick={() => toggleMetric('casingPressure')}
              className={`px-3 py-1.5 text-xs font-mono-data uppercase rounded-lg border transition-all flex items-center gap-1.5 font-semibold ${
                selectedMetrics.casingPressure
                  ? 'bg-sky-950/70 border-sky-500/70 text-sky-300 shadow-xs'
                  : 'bg-slate-950 border-slate-800 text-slate-500'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-sky-400" />
              <span>CASING (ANULAR)</span>
            </button>

            <button
              onClick={() => toggleMetric('flowRate')}
              className={`px-3 py-1.5 text-xs font-mono-data uppercase rounded-lg border transition-all flex items-center gap-1.5 font-semibold ${
                selectedMetrics.flowRate
                  ? 'bg-purple-950/70 border-purple-500/70 text-purple-300 shadow-xs'
                  : 'bg-slate-950 border-slate-800 text-slate-500'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-purple-400" />
              <span>CAUDAL (BPD)</span>
            </button>

            <button
              onClick={() => toggleMetric('vibration')}
              className={`px-3 py-1.5 text-xs font-mono-data uppercase rounded-lg border transition-all flex items-center gap-1.5 font-semibold ${
                selectedMetrics.vibration
                  ? 'bg-amber-950/70 border-amber-500/70 text-amber-300 shadow-xs'
                  : 'bg-slate-950 border-slate-800 text-slate-500'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-amber-400" />
              <span>VIBRACIÓN BES</span>
            </button>

            <button
              onClick={() => toggleMetric('espFrequency')}
              className={`px-3 py-1.5 text-xs font-mono-data uppercase rounded-lg border transition-all flex items-center gap-1.5 font-semibold ${
                selectedMetrics.espFrequency
                  ? 'bg-rose-950/70 border-rose-500/70 text-rose-300 shadow-xs'
                  : 'bg-slate-950 border-slate-800 text-slate-500'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-rose-400" />
              <span>FRECUENCIA BES</span>
            </button>
          </div>

          <div className="flex items-center gap-2">
            <button
              onClick={() => setShowLimits(!showLimits)}
              className={`px-2.5 py-1 text-xs font-mono-data uppercase rounded border transition-colors ${
                showLimits
                  ? 'bg-rose-500/10 border-rose-500/40 text-rose-300 font-semibold'
                  : 'bg-slate-950 border-slate-800 text-slate-500'
              }`}
            >
              Límites SIL-3 {showLimits ? 'Visibles' : 'Ocultos'}
            </button>

            <button
              onClick={() => setChartType(chartType === 'area' ? 'line' : 'area')}
              className="px-2.5 py-1 text-xs font-mono-data uppercase rounded border border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700 transition-colors"
            >
              {chartType === 'area' ? 'Modo Área Gradiente' : 'Modo Línea Pura'}
            </button>
          </div>
        </div>

        {/* Lienzo del Gráfico Interactivo de Recharts */}
        <div className="w-full h-80 sm:h-96 min-w-[320px] select-none">
          <ResponsiveContainer width="100%" height="100%">
            <ComposedChart
              data={historicalData}
              margin={{ top: 15, right: 25, left: 0, bottom: 5 }}
            >
              <defs>
                <linearGradient id="colorTubing" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#10b981" stopOpacity={0.4} />
                  <stop offset="95%" stopColor="#10b981" stopOpacity={0.0} />
                </linearGradient>
                <linearGradient id="colorCasing" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#0ea5e9" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="#0ea5e9" stopOpacity={0.0} />
                </linearGradient>
                <linearGradient id="colorFlow" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#a855f7" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="#a855f7" stopOpacity={0.0} />
                </linearGradient>
              </defs>

              <CartesianGrid strokeDasharray="3 3" stroke="#1e293b" vertical={false} />

              <XAxis
                dataKey="time"
                stroke="#64748b"
                fontSize={11}
                tickLine={false}
                fontFamily="JetBrains Mono"
                dy={6}
              />

              <YAxis
                yAxisId="pressure"
                stroke="#64748b"
                fontSize={11}
                tickLine={false}
                fontFamily="JetBrains Mono"
                domain={['auto', 'auto']}
                unit=" psi"
              />

              {selectedMetrics.flowRate && (
                <YAxis
                  yAxisId="flow"
                  orientation="right"
                  stroke="#a855f7"
                  fontSize={11}
                  tickLine={false}
                  fontFamily="JetBrains Mono"
                  domain={['auto', 'auto']}
                  unit=" bpd"
                />
              )}

              <Tooltip content={<CustomSCADATooltip />} />
              <Legend
                verticalAlign="top"
                height={32}
                wrapperStyle={{
                  paddingBottom: '8px',
                  fontFamily: 'JetBrains Mono',
                  fontSize: '11px',
                }}
              />

              {/* Líneas de Referencia de Disparo SIL-3 */}
              {showLimits && selectedMetrics.tubingPressure && (
                <>
                  <ReferenceLine
                    yAxisId="pressure"
                    y={1950}
                    label={{
                      value: 'HH TRIP (1,950 PSI)',
                      fill: '#f43f5e',
                      fontSize: 10,
                      fontFamily: 'JetBrains Mono',
                      position: 'top',
                    }}
                    stroke="#f43f5e"
                    strokeDasharray="5 5"
                    strokeWidth={1.5}
                  />
                  <ReferenceLine
                    yAxisId="pressure"
                    y={1900}
                    label={{
                      value: 'H ALERTA (1,900 PSI)',
                      fill: '#f59e0b',
                      fontSize: 10,
                      fontFamily: 'JetBrains Mono',
                      position: 'top',
                    }}
                    stroke="#f59e0b"
                    strokeDasharray="4 4"
                    strokeWidth={1}
                  />
                </>
              )}

              {/* Serie: Presión Tubing */}
              {selectedMetrics.tubingPressure &&
                (chartType === 'area' ? (
                  <Area
                    yAxisId="pressure"
                    type="monotone"
                    dataKey="tubingPressure"
                    name="Presión Tubing (WHTP)"
                    stroke="#10b981"
                    strokeWidth={2.5}
                    fillOpacity={1}
                    fill="url(#colorTubing)"
                    dot={false}
                    activeDot={{ r: 6, fill: '#10b981', stroke: '#ffffff', strokeWidth: 2 }}
                  />
                ) : (
                  <Line
                    yAxisId="pressure"
                    type="monotone"
                    dataKey="tubingPressure"
                    name="Presión Tubing (WHTP)"
                    stroke="#10b981"
                    strokeWidth={2.5}
                    dot={false}
                    activeDot={{ r: 6, fill: '#10b981', stroke: '#ffffff', strokeWidth: 2 }}
                  />
                ))}

              {/* Serie: Presión Casing */}
              {selectedMetrics.casingPressure &&
                (chartType === 'area' ? (
                  <Area
                    yAxisId="pressure"
                    type="monotone"
                    dataKey="casingPressure"
                    name="Presión Casing (Anular)"
                    stroke="#0ea5e9"
                    strokeWidth={2}
                    fillOpacity={1}
                    fill="url(#colorCasing)"
                    dot={false}
                    activeDot={{ r: 5, fill: '#0ea5e9', stroke: '#ffffff', strokeWidth: 2 }}
                  />
                ) : (
                  <Line
                    yAxisId="pressure"
                    type="monotone"
                    dataKey="casingPressure"
                    name="Presión Casing (Anular)"
                    stroke="#0ea5e9"
                    strokeWidth={2}
                    dot={false}
                    activeDot={{ r: 5, fill: '#0ea5e9', stroke: '#ffffff', strokeWidth: 2 }}
                  />
                ))}

              {/* Serie: Caudal BPD (Eje secundario) */}
              {selectedMetrics.flowRate && (
                <Line
                  yAxisId="flow"
                  type="monotone"
                  dataKey="flowRate"
                  name="Caudal de Producción"
                  stroke="#a855f7"
                  strokeWidth={2}
                  dot={false}
                  activeDot={{ r: 5, fill: '#a855f7', stroke: '#ffffff', strokeWidth: 2 }}
                />
              )}

              {/* Serie: Vibración BES */}
              {selectedMetrics.vibration && (
                <Line
                  yAxisId="pressure"
                  type="monotone"
                  dataKey="vibration"
                  name="Vibración ESP (mm/s)"
                  stroke="#f59e0b"
                  strokeWidth={2}
                  dot={false}
                  activeDot={{ r: 5, fill: '#f59e0b', stroke: '#ffffff', strokeWidth: 2 }}
                />
              )}

              {/* Serie: Frecuencia BES */}
              {selectedMetrics.espFrequency && (
                <Line
                  yAxisId="pressure"
                  type="monotone"
                  dataKey="espFrequency"
                  name="Frecuencia Variador (Hz)"
                  stroke="#f43f5e"
                  strokeWidth={2}
                  dot={false}
                  activeDot={{ r: 5, fill: '#f43f5e', stroke: '#ffffff', strokeWidth: 2 }}
                />
              )}

              {/* Selector de Rango de Zoom y Desplazamiento (Brush) */}
              <Brush
                dataKey="time"
                height={26}
                stroke="#475569"
                fill="#020617"
                travellerWidth={10}
              />
            </ComposedChart>
          </ResponsiveContainer>
        </div>

        <div className="flex flex-wrap items-center justify-between text-xs font-mono-data text-slate-500 pt-3 border-t border-slate-800/80 mt-2">
          <span>DESLIZADOR TEMPORAL: ARRASTRE LOS BORDES DEL CONTROL INFERIOR PARA HACER ZOOM</span>
          <span className="text-emerald-400 font-semibold">MOTOR DE RENDERIZADO: RECHARTS V2 SVG RESPONSIVE</span>
        </div>
      </div>

      {/* Analizador de Espectro FFT de Vibración */}
      <div className="bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 flex flex-col shadow-sm backdrop-blur-md">
        <div className="flex flex-wrap items-center justify-between border-b border-slate-800/80 pb-3 mb-4 gap-2">
          <div className="flex items-center gap-2.5">
            <div className="p-2 bg-amber-500/10 border border-amber-500/30 rounded-lg text-amber-400">
              <BarChart2 className="w-4 h-4" />
            </div>
            <div>
              <h2 className="font-display text-sm font-bold uppercase tracking-wider text-white">
                Analizador de Frecuencias FFT · Diagnóstico Armónico de Bomba BES
              </h2>
              <span className="text-xs font-mono-data text-slate-400">
                ESPECTRO DE VIBRACIÓN (5 Hz A 240 Hz) CON MONITOREO DE CAVITACIÓN
              </span>
            </div>
          </div>
          <div className="text-xs font-mono-data text-slate-400 flex items-center gap-4">
            <span>ARMÓNICOS 1X (60 Hz) & 2X (120 Hz)</span>
            <span className="text-emerald-400 font-semibold">ESTADO MECÁNICO: NOMINAL SIL-3</span>
          </div>
        </div>

        {/* Gráfico de Barras Espectrales FFT con Recharts */}
        <div className="w-full h-56 select-none bg-slate-950 rounded-lg border border-slate-800/90 p-2">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart
              data={spectralData.map((d) => ({
                frecuencia: `${d.frequency} Hz`,
                freqNum: d.frequency,
                amplitud: d.amplitude,
                base: d.baseline,
              }))}
              margin={{ top: 10, right: 15, left: -10, bottom: 5 }}
            >
              <CartesianGrid strokeDasharray="3 3" stroke="#1e293b" vertical={false} />
              <XAxis dataKey="frecuencia" stroke="#64748b" fontSize={10} fontFamily="JetBrains Mono" />
              <YAxis stroke="#64748b" fontSize={10} fontFamily="JetBrains Mono" unit=" mm/s" />
              <Tooltip
                content={({ active, payload }: any) => {
                  if (active && payload && payload.length) {
                    const data = payload[0].payload;
                    return (
                      <div className="bg-slate-900 border border-slate-700 p-2.5 rounded-lg text-xs font-mono-data text-white shadow-xl">
                        <div>Frecuencia: <span className="font-bold text-sky-400">{data.frecuencia}</span></div>
                        <div>Amplitud Pico: <span className="font-bold text-emerald-400">{data.amplitud} mm/s</span></div>
                        <div>Línea Base: <span className="text-slate-400">{data.base} mm/s</span></div>
                      </div>
                    );
                  }
                  return null;
                }}
              />
              <ReferenceLine
                y={3.0}
                label={{
                  value: 'UMBRAL DE ALERTA ISO-10816 (3.0 mm/s)',
                  fill: '#f59e0b',
                  fontSize: 10,
                  fontFamily: 'JetBrains Mono',
                  position: 'top',
                }}
                stroke="#f59e0b"
                strokeDasharray="4 4"
              />
              <Bar dataKey="amplitud" name="Amplitud Vibración" radius={[3, 3, 0, 0]}>
                {spectralData.map((entry, index) => {
                  const isPeak = entry.frequency === 60 || entry.frequency === 120;
                  const isHigh = entry.amplitude > 3.0;
                  return (
                    <Cell
                      key={`cell-${index}`}
                      fill={isHigh ? '#f43f5e' : isPeak ? '#f59e0b' : '#0ea5e9'}
                    />
                  );
                })}
              </Bar>
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="flex flex-wrap items-center justify-between text-xs font-mono-data text-slate-400 pt-3 border-t border-slate-800/80 mt-3">
          <div>VENTANA DE INTEGRACIÓN: FFT HANNING 2048 MUESTRAS · SENSOR PIEZOELÉCTRICO ACC-402</div>
          <div className="text-emerald-400 font-semibold">
            VIBRACIÓN RMS GLOBAL DENTRO DE LÍMITES PERMITIDOS
          </div>
        </div>
      </div>
    </div>
  );
};

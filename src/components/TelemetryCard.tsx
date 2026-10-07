import React from 'react';
import { TelemetryMetric } from '../types/scada';
import { ArrowUpRight, ArrowDownRight, Minus } from 'lucide-react';

interface TelemetryCardProps {
  metric: TelemetryMetric;
  onClick?: () => void;
  selected?: boolean;
}

export const TelemetryCard: React.FC<TelemetryCardProps> = ({
  metric,
  onClick,
  selected = false,
}) => {
  const history = metric.history;
  const minVal = Math.min(...history) * 0.98;
  const maxVal = Math.max(...history) * 1.02;
  const range = maxVal - minVal || 1;
  const svgWidth = 220;
  const svgHeight = 40;

  const points = history
    .map((val, idx) => {
      const x = (idx / (history.length - 1)) * svgWidth;
      const y = svgHeight - ((val - minVal) / range) * (svgHeight - 10) - 5;
      return `${x},${y}`;
    })
    .join(' ');

  const isCritical = metric.severity === 'critical';
  const isWarning = metric.severity === 'warning';
  const isNominal = metric.severity === 'nominal';

  const strokeColor = isCritical ? '#f43f5e' : isWarning ? '#f59e0b' : '#10b981';
  const gradientId = `grad-${metric.id}`;

  const severityText = isCritical
    ? 'CRÍTICO'
    : isWarning
    ? 'ALERTA'
    : 'NOMINAL';

  return (
    <div
      onClick={onClick}
      role="button"
      tabIndex={0}
      className={`relative flex flex-col justify-between rounded-lg border transition-all text-left cursor-pointer p-3.5 select-none bg-gradient-to-b from-slate-900/95 via-slate-900/90 to-slate-950/95 backdrop-blur-sm shadow-sm ${
        selected
          ? 'border-sky-500 shadow-[0_0_16px_rgba(14,165,233,0.25)] ring-1 ring-sky-500/50'
          : isCritical
          ? 'border-rose-500/80 shadow-[0_0_14px_rgba(244,63,94,0.2)]'
          : isWarning
          ? 'border-amber-500/80 shadow-[0_0_12px_rgba(245,158,11,0.15)]'
          : 'border-slate-800 hover:border-slate-700 hover:shadow-md'
      }`}
    >
      {/* Header: Etiqueta y Baliza de Estado */}
      <div className="flex items-center justify-between gap-2 border-b border-slate-800/80 pb-2 mb-2.5">
        <div className="min-w-0">
          <div className="flex items-center gap-1.5">
            <span className="font-display text-xs font-bold tracking-wider text-slate-200 uppercase">
              {metric.tag}
            </span>
          </div>
          <p className="text-[10px] font-mono-data text-slate-400 truncate mt-0.5">
            {metric.label}
          </p>
        </div>

        {/* Baliza de Estado */}
        <div className="flex items-center gap-1.5 shrink-0 bg-slate-950/60 px-2 py-0.5 rounded border border-slate-800/80">
          <span
            className={`w-1.5 h-1.5 rounded-full ${
              isCritical
                ? 'bg-rose-500 animate-ping'
                : isWarning
                ? 'bg-amber-500'
                : 'bg-emerald-500'
            }`}
          />
          <span
            className={`text-[9px] font-mono-data font-semibold tracking-wider ${
              isCritical
                ? 'text-rose-400'
                : isWarning
                ? 'text-amber-400'
                : 'text-emerald-400'
            }`}
          >
            {severityText}
          </span>
        </div>
      </div>

      {/* Lectura Principal del Sensor */}
      <div className="flex items-baseline justify-between mb-2">
        <div className="flex items-baseline gap-1.5">
          <span className="font-mono-data text-2xl font-bold tracking-tight text-white leading-none">
            {metric.value.toLocaleString(undefined, {
              minimumFractionDigits: metric.value % 1 === 0 ? 0 : 1,
              maximumFractionDigits: 1,
            })}
          </span>
          <span className="font-mono-data text-xs font-semibold uppercase text-slate-400">
            {metric.unit}
          </span>
        </div>

        {/* Indicador de Tendencia */}
        <div className="text-[11px] font-mono-data">
          {metric.trend === 'rising' && (
            <span className="flex items-center text-amber-400 font-medium">
              <ArrowUpRight className="w-3.5 h-3.5 mr-0.5" /> +Subiendo
            </span>
          )}
          {metric.trend === 'falling' && (
            <span className="flex items-center text-sky-400 font-medium">
              <ArrowDownRight className="w-3.5 h-3.5 mr-0.5" /> -Bajando
            </span>
          )}
          {metric.trend === 'stable' && (
            <span className="flex items-center text-slate-400">
              <Minus className="w-3.5 h-3.5 mr-0.5" /> Estable
            </span>
          )}
        </div>
      </div>

      {/* Barra de Rango Operativo */}
      <div className="mb-2.5">
        <div className="flex justify-between text-[9px] font-mono-data text-slate-400 mb-1">
          <span>MÍN: {metric.lowTrip}</span>
          <span className="text-slate-300">OBJ: {metric.target}</span>
          <span>MÁX: {metric.highTrip}</span>
        </div>
        <div className="relative h-1.5 w-full bg-slate-950 rounded overflow-hidden border border-slate-800">
          <div
            className="absolute top-0 bottom-0 left-0 bg-rose-500/30"
            style={{ width: `${(metric.lowTrip / metric.highTrip) * 100}%` }}
          />
          <div
            className="absolute top-0 bottom-0 right-0 bg-rose-500/30"
            style={{ width: '15%' }}
          />
          <div
            className={`absolute top-0 bottom-0 w-1.5 rounded-full ${
              isCritical ? 'bg-rose-500' : isWarning ? 'bg-amber-500' : 'bg-emerald-400'
            }`}
            style={{
              left: `${Math.min(98, Math.max(0, (metric.value / metric.highTrip) * 100))}%`,
            }}
          />
        </div>
      </div>

      {/* Gráfico Sparkline de Tendencia */}
      <div className="relative h-10 w-full bg-slate-950/80 rounded border border-slate-800/80 overflow-hidden flex items-center justify-center p-0.5">
        <svg
          viewBox={`0 0 ${svgWidth} ${svgHeight}`}
          className="w-full h-full overflow-visible"
          preserveAspectRatio="none"
        >
          <defs>
            <linearGradient id={gradientId} x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stopColor={strokeColor} stopOpacity="0.35" />
              <stop offset="100%" stopColor={strokeColor} stopOpacity="0.0" />
            </linearGradient>
          </defs>
          <polyline
            fill="none"
            stroke={strokeColor}
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            points={points}
          />
          <polygon
            fill={`url(#${gradientId})`}
            points={`0,${svgHeight} ${points} ${svgWidth},${svgHeight}`}
          />
        </svg>
      </div>
    </div>
  );
};


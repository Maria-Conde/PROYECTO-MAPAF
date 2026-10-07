import React, { useState } from 'react';
import { TelemetryMetric, ProcessNode } from '../types/scada';
import { TelemetryCard } from '../components/TelemetryCard';
import { SplitBadge } from '../components/SplitBadge';
import {
  Activity,
  Sliders,
  RotateCcw,
  Gauge,
  Droplet,
  Flame,
  ShieldCheck,
  CheckCircle2,
  Info,
} from 'lucide-react';

interface OverviewScreenProps {
  metrics: TelemetryMetric[];
  processNodes: ProcessNode[];
  onOpenInterlock: (node: ProcessNode) => void;
  productionRate: number;
  flowState: 'nominal' | 'throttled' | 'tripped';
  onQuickThrottle: () => void;
  onResetSystem: () => void;
}

export const OverviewScreen: React.FC<OverviewScreenProps> = ({
  metrics,
  processNodes,
  onOpenInterlock,
  productionRate,
  flowState,
  onQuickThrottle,
  onResetSystem,
}) => {
  const [selectedNode, setSelectedNode] = useState<ProcessNode | null>(processNodes[2]); // Separador
  const [selectedMetricId, setSelectedMetricId] = useState<string>('pt-101');
  const [showFlowAnimation, setShowFlowAnimation] = useState<boolean>(true);

  return (
    <div className="flex-1 flex flex-col gap-4 p-4 lg:p-6 max-w-[1780px] mx-auto w-full overflow-y-auto">
      {/* Barra Superior de Estado Operacional */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 shadow-sm backdrop-blur-md">
        <div className="flex flex-wrap items-center gap-3 sm:gap-6">
          <div className="flex items-center gap-3">
            <div className="relative flex h-3 w-3">
              <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </div>
            <div>
              <h1 className="font-display text-base font-bold tracking-tight text-white uppercase">
                Sector 04 · Cuenca Occidental RTOC
              </h1>
              <div className="flex items-center gap-2 text-xs font-mono-data text-slate-400 mt-0.5">
                <span>ESTACIÓN CENTRAL PS-04 ALFA</span>
                <span aria-hidden="true">·</span>
                <span className="text-emerald-400">TELEMETRÍA EN VIVO (1000ms)</span>
              </div>
            </div>
          </div>
        </div>

        {/* Insignias de Estado y Acciones de Operación */}
        <div className="flex items-center gap-2.5 flex-wrap">
          <SplitBadge
            prefix="PRODUCCIÓN"
            value={`${productionRate.toLocaleString()} BPD`}
            severity={flowState === 'tripped' ? 'critical' : flowState === 'throttled' ? 'warning' : 'nominal'}
          />
          <SplitBadge prefix="CONTROL" value="LAZO PID CASCADA" severity="info" />
          <SplitBadge prefix="ESTADO" value={flowState === 'tripped' ? 'PLANTA DISPARADA' : flowState === 'throttled' ? 'ESTRANGULADO' : 'NOMINAL 100%'} severity={flowState === 'tripped' ? 'critical' : flowState === 'throttled' ? 'warning' : 'nominal'} />

          <button
            onClick={onQuickThrottle}
            className="px-3 py-1.5 text-xs font-mono-data uppercase bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-md transition-all flex items-center gap-2 shadow-sm"
          >
            <Sliders className="w-3.5 h-3.5 text-sky-400" />
            <span>{flowState === 'throttled' ? 'REANUDAR 100%' : 'ESTRANGULAR 20%'}</span>
          </button>

          {flowState === 'tripped' && (
            <button
              onClick={onResetSystem}
              className="px-3.5 py-1.5 text-xs font-mono-data uppercase bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-md transition-all flex items-center gap-2 shadow-[0_0_12px_rgba(16,185,129,0.4)]"
            >
              <RotateCcw className="w-3.5 h-3.5" />
              <span>REINICIAR SISTEMA (ESD)</span>
            </button>
          )}
        </div>
      </div>

      {/* 6 Tarjetas de Telemetría Principales */}
      <div>
        <div className="flex items-center justify-between mb-2 px-1">
          <h2 className="text-xs font-mono-data uppercase tracking-wider text-slate-400 font-semibold flex items-center gap-2">
            <Gauge className="w-3.5 h-3.5 text-emerald-400" />
            <span>Variables Críticas de Proceso en Tiempo Real</span>
          </h2>
          <span className="text-[11px] font-mono-data text-slate-500">
            Haz clic en una variable para filtrar o analizar
          </span>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
          {metrics.map((metric) => (
            <TelemetryCard
              key={metric.id}
              metric={metric}
              selected={selectedMetricId === metric.id}
              onClick={() => setSelectedMetricId(metric.id)}
            />
          ))}
        </div>
      </div>

      {/* Diagrama de Proceso P&ID y Panel de Inspección de Equipos */}
      <div className="flex-1 flex flex-col xl:flex-row gap-4 min-h-[460px]">
        {/* Bahía del Diagrama P&ID */}
        <div className="flex-1 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 flex flex-col justify-between shadow-sm backdrop-blur-md">
          {/* Cabecera del Diagrama */}
          <div className="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-3">
            <div className="flex items-center gap-2.5">
              <div className="p-1.5 bg-sky-500/10 rounded border border-sky-500/30 text-sky-400">
                <Activity className="w-4 h-4" />
              </div>
              <div>
                <h3 className="font-display text-sm font-bold text-white uppercase tracking-wider">
                  Diagrama Esquemático de Tuberías e Instrumentación (P&ID)
                </h3>
                <span className="text-[11px] font-mono-data text-slate-400">
                  Tren de Separación y Transporte de Hidrocarburos · Norma API-14C
                </span>
              </div>
            </div>

            <div className="flex items-center gap-3">
              <button
                onClick={() => setShowFlowAnimation(!showFlowAnimation)}
                className={`px-2.5 py-1 text-xs font-mono-data uppercase rounded-md border transition-all ${
                  showFlowAnimation
                    ? 'bg-emerald-950/60 border-emerald-500/50 text-emerald-400'
                    : 'bg-slate-950 border-slate-800 text-slate-500'
                }`}
              >
                Líneas de Flujo: {showFlowAnimation ? 'ANIMADAS' : 'ESTÁTICAS'}
              </button>
            </div>
          </div>

          {/* Lienzo SVG Industrial con Tuberías y Nodos */}
          <div className="relative flex-1 w-full bg-slate-950 rounded-lg border border-slate-800/90 p-3 flex items-center justify-center overflow-x-auto min-h-[320px]">
            <svg
              viewBox="0 0 1080 320"
              className="w-full h-full min-w-[820px] max-h-[380px] select-none"
            >
              {/* Fondo sutil de cuadrícula */}
              <defs>
                <pattern id="grid" width="24" height="24" patternUnits="userSpaceOnUse">
                  <path d="M 24 0 L 0 0 0 24" fill="none" stroke="#1e293b" strokeWidth="0.5" opacity="0.4" />
                </pattern>
                <linearGradient id="pipeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                  <stop offset="0%" stopColor="#10b981" />
                  <stop offset="50%" stopColor="#0ea5e9" />
                  <stop offset="100%" stopColor="#10b981" />
                </linearGradient>
              </defs>
              <rect width="1080" height="320" fill="url(#grid)" />

              {/* Tuberías de Conexión */}
              {/* 1. Batería a Ciclón */}
              <line x1="140" y1="160" x2="270" y2="160" stroke="#334155" strokeWidth="8" strokeLinecap="round" />
              <line
                x1="140"
                y1="160"
                x2="270"
                y2="160"
                stroke={flowState === 'tripped' ? '#f43f5e' : '#10b981'}
                strokeWidth="3.5"
                strokeDasharray={showFlowAnimation ? '8 4' : undefined}
              />

              {/* 2. Ciclón a Separador */}
              <line x1="330" y1="160" x2="480" y2="160" stroke="#334155" strokeWidth="8" strokeLinecap="round" />
              <line
                x1="330"
                y1="160"
                x2="480"
                y2="160"
                stroke={flowState === 'tripped' ? '#f43f5e' : '#10b981'}
                strokeWidth="3.5"
                strokeDasharray={showFlowAnimation ? '8 4' : undefined}
              />

              {/* 3. Separador a Manifold de Choque (Líquido) */}
              <line x1="580" y1="160" x2="710" y2="160" stroke="#334155" strokeWidth="8" strokeLinecap="round" />
              <line
                x1="580"
                y1="160"
                x2="710"
                y2="160"
                stroke={flowState === 'tripped' ? '#f43f5e' : '#10b981'}
                strokeWidth="3.5"
                strokeDasharray={showFlowAnimation ? '8 4' : undefined}
              />

              {/* 4. Separador a Compresor de Gas */}
              <path
                d="M 530 110 L 530 60 L 700 60"
                fill="none"
                stroke="#334155"
                strokeWidth="7"
                strokeLinecap="round"
              />
              <path
                d="M 530 110 L 530 60 L 700 60"
                fill="none"
                stroke={flowState === 'tripped' ? '#f43f5e' : '#0ea5e9'}
                strokeWidth="3"
                strokeDasharray={showFlowAnimation ? '6 4' : undefined}
              />

              {/* 5. Compresor a Venta de Gas */}
              <line x1="775" y1="60" x2="940" y2="60" stroke="#334155" strokeWidth="7" strokeLinecap="round" />
              <line
                x1="775"
                y1="60"
                x2="940"
                y2="60"
                stroke="#0ea5e9"
                strokeWidth="3"
                strokeDasharray={showFlowAnimation ? '6 4' : undefined}
              />
              <text x="948" y="64" fill="#89ceff" fontSize="11" fontFamily="JetBrains Mono" fontWeight="bold">
                VENTA DE GAS [6.8 MMSCFD]
              </text>

              {/* 6. Manifold a Oleoducto */}
              <line x1="770" y1="160" x2="920" y2="160" stroke="#334155" strokeWidth="8" strokeLinecap="round" />
              <line
                x1="770"
                y1="160"
                x2="920"
                y2="160"
                stroke={flowState === 'tripped' ? '#f43f5e' : '#10b981'}
                strokeWidth="3.5"
                strokeDasharray={showFlowAnimation ? '8 4' : undefined}
              />

              {/* Flechas de flujo */}
              <polygon points="205,155 218,160 205,165" fill={flowState === 'tripped' ? '#f43f5e' : '#10b981'} />
              <polygon points="405,155 418,160 405,165" fill={flowState === 'tripped' ? '#f43f5e' : '#10b981'} />
              <polygon points="645,155 658,160 645,165" fill={flowState === 'tripped' ? '#f43f5e' : '#10b981'} />
              <polygon points="845,155 858,160 845,165" fill={flowState === 'tripped' ? '#f43f5e' : '#10b981'} />

              {/* NODO 1: Batería de Pozos */}
              <g
                className="cursor-pointer group"
                onClick={() => setSelectedNode(processNodes[0])}
              >
                <rect
                  x="50"
                  y="105"
                  width="95"
                  height="110"
                  rx="8"
                  fill="#0f172a"
                  stroke={selectedNode?.id === 'node-pad' ? '#38bdf8' : '#334155'}
                  strokeWidth={selectedNode?.id === 'node-pad' ? 2.5 : 1.5}
                  className="transition-all group-hover:stroke-sky-400"
                />
                <polygon points="75,145 120,165 120,145 75,165" fill="#10b981" />
                <circle cx="97" cy="155" r="4" fill="#020617" />
                <text x="97" y="125" fill="#ffffff" fontSize="11" fontWeight="bold" fontFamily="Space Grotesk" textAnchor="middle">
                  PAD-04
                </text>
                <text x="97" y="185" fill="#4edea3" fontSize="11" fontFamily="JetBrains Mono" fontWeight="bold" textAnchor="middle">
                  {metrics[0].value.toFixed(0)} PSI
                </text>
                <text x="97" y="202" fill="#94a3b8" fontSize="9" fontFamily="JetBrains Mono" textAnchor="middle">
                  BATERÍA POZOS
                </text>
              </g>

              {/* NODO 2: Desarenador Ciclónico */}
              <g
                className="cursor-pointer group"
                onClick={() => setSelectedNode(processNodes[1])}
              >
                <rect
                  x="270"
                  y="115"
                  width="65"
                  height="90"
                  rx="8"
                  fill="#0f172a"
                  stroke={selectedNode?.id === 'node-cyclone' ? '#38bdf8' : '#334155'}
                  strokeWidth={selectedNode?.id === 'node-cyclone' ? 2.5 : 1.5}
                  className="transition-all group-hover:stroke-sky-400"
                />
                <polygon points="280,140 324,140 302,175" fill="none" stroke="#38bdf8" strokeWidth="2" />
                <text x="302" y="132" fill="#ffffff" fontSize="10" fontWeight="bold" fontFamily="Space Grotesk" textAnchor="middle">
                  V-102
                </text>
                <text x="302" y="196" fill="#94a3b8" fontSize="9" fontFamily="JetBrains Mono" textAnchor="middle">
                  DESARENADOR
                </text>
              </g>

              {/* NODO 3: Separador Trifásico */}
              <g
                className="cursor-pointer group"
                onClick={() => setSelectedNode(processNodes[2])}
              >
                <rect
                  x="480"
                  y="105"
                  width="105"
                  height="110"
                  rx="10"
                  fill="#0f172a"
                  stroke={selectedNode?.id === 'node-separator' ? '#38bdf8' : '#334155'}
                  strokeWidth={selectedNode?.id === 'node-separator' ? 3 : 1.5}
                  className="transition-all group-hover:stroke-sky-400"
                />
                {/* Nivel de fluido en el separador */}
                <rect x="484" y="155" width="97" height="56" rx="6" fill="#10b981" fillOpacity="0.2" />
                <line x1="484" y1="155" x2="581" y2="155" stroke="#10b981" strokeWidth="2" strokeDasharray="4 2" />
                <text x="532" y="126" fill="#ffffff" fontSize="12" fontWeight="bold" fontFamily="Space Grotesk" textAnchor="middle">
                  V-201
                </text>
                <text x="532" y="142" fill="#cbd5e1" fontSize="9" fontFamily="JetBrains Mono" textAnchor="middle">
                  SEP. TRIFÁSICO
                </text>
                <text x="532" y="176" fill="#4edea3" fontSize="11" fontFamily="JetBrains Mono" fontWeight="bold" textAnchor="middle">
                  NIVEL: 64.2%
                </text>
                <text x="532" y="198" fill="#38bdf8" fontSize="10" fontFamily="JetBrains Mono" textAnchor="middle">
                  820 PSI
                </text>
              </g>

              {/* NODO 4: Manifold de Choque */}
              <g
                className="cursor-pointer group"
                onClick={() => setSelectedNode(processNodes[3])}
              >
                <rect
                  x="710"
                  y="120"
                  width="65"
                  height="80"
                  rx="8"
                  fill="#0f172a"
                  stroke={selectedNode?.id === 'node-choke' ? '#38bdf8' : '#334155'}
                  strokeWidth={selectedNode?.id === 'node-choke' ? 2.5 : 1.5}
                  className="transition-all group-hover:stroke-sky-400"
                />
                <polygon points="720,150 742,160 742,140 765,150 765,170 742,160 742,180 720,170" fill="#f59e0b" />
                <text x="742" y="136" fill="#ffffff" fontSize="10" fontWeight="bold" fontFamily="Space Grotesk" textAnchor="middle">
                  CV-104
                </text>
                <text x="742" y="192" fill="#fbbf24" fontSize="10" fontFamily="JetBrains Mono" fontWeight="bold" textAnchor="middle">
                  42.8%
                </text>
              </g>

              {/* NODO 5: Compresor Booster */}
              <g
                className="cursor-pointer group"
                onClick={() => setSelectedNode(processNodes[4])}
              >
                <circle
                  cx="738"
                  cy="60"
                  r="36"
                  fill="#0f172a"
                  stroke={selectedNode?.id === 'node-compressor' ? '#38bdf8' : '#334155'}
                  strokeWidth={selectedNode?.id === 'node-compressor' ? 2.5 : 1.5}
                  className="transition-all group-hover:stroke-sky-400"
                />
                <polygon points="720,74 758,60 720,46" fill="none" stroke="#0ea5e9" strokeWidth="2.5" />
                <text x="738" y="55" fill="#ffffff" fontSize="10" fontWeight="bold" fontFamily="Space Grotesk" textAnchor="middle">
                  K-301
                </text>
                <text x="738" y="70" fill="#89ceff" fontSize="9" fontFamily="JetBrains Mono" textAnchor="middle">
                  COMPRESOR
                </text>
              </g>

              {/* NODO 6: Oleoducto de Exportación */}
              <g
                className="cursor-pointer group"
                onClick={() => setSelectedNode(processNodes[5])}
              >
                <rect
                  x="920"
                  y="115"
                  width="110"
                  height="90"
                  rx="8"
                  fill="#0f172a"
                  stroke={selectedNode?.id === 'node-pipeline' ? '#38bdf8' : '#334155'}
                  strokeWidth={selectedNode?.id === 'node-pipeline' ? 2.5 : 1.5}
                  className="transition-all group-hover:stroke-sky-400"
                />
                <circle cx="975" cy="150" r="15" fill="#020617" stroke="#10b981" strokeWidth="2.5" />
                <polyline points="970,150 975,145 980,155" fill="none" stroke="#10b981" strokeWidth="2" />
                <text x="975" y="132" fill="#ffffff" fontSize="11" fontWeight="bold" fontFamily="Space Grotesk" textAnchor="middle">
                  PL-808
                </text>
                <text x="975" y="180" fill="#4edea3" fontSize="10" fontFamily="JetBrains Mono" fontWeight="bold" textAnchor="middle">
                  VENTA CRUDO
                </text>
                <text x="975" y="196" fill="#94a3b8" fontSize="9" fontFamily="JetBrains Mono" textAnchor="middle">
                  14,700 BPD
                </text>
              </g>
            </svg>
          </div>

          {/* Leyenda de fluidos */}
          <div className="flex flex-wrap items-center justify-between text-xs font-mono-data text-slate-400 pt-3 border-t border-slate-800/80 mt-2">
            <div className="flex items-center gap-4">
              <span className="flex items-center gap-1.5">
                <span className="w-3 h-1 bg-emerald-500 rounded" /> PETRÓLEO CRUDO
              </span>
              <span className="flex items-center gap-1.5">
                <span className="w-3 h-1 bg-sky-500 rounded" /> GAS NATURAL
              </span>
              <span className="flex items-center gap-1.5">
                <span className="w-3 h-1 bg-amber-500 rounded" /> CONDENSADOS
              </span>
            </div>
            <div className="text-[11px] text-slate-400">
              PUNTO DE AJUSTE HIDROSTÁTICO: 1,800.0 PSI (DISPARO A 2,350 PSI)
            </div>
          </div>
        </div>

        {/* Panel Lateral: Inspector de Nodo Seleccionado */}
        {selectedNode && (
          <div className="w-full xl:w-96 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 flex flex-col justify-between shadow-sm backdrop-blur-md">
            <div>
              <div className="flex items-center justify-between border-b border-slate-800/80 pb-3 mb-3">
                <div>
                  <span className="text-[10px] font-mono-data uppercase text-slate-400 font-semibold tracking-wider">
                    INSPECCIÓN DE EQUIPO
                  </span>
                  <h3 className="font-display text-base font-bold text-white mt-0.5">
                    {selectedNode.name}
                  </h3>
                </div>
                <SplitBadge prefix="TAG" value={selectedNode.code} severity="info" />
              </div>

              {/* Tarjetas de Medición del Equipo */}
              <div className="space-y-2.5 mb-4">
                <div className="flex items-center justify-between p-2.5 bg-slate-950 rounded-lg border border-slate-800/90">
                  <span className="text-xs font-mono-data text-slate-400">PRESIÓN OPERATIVA</span>
                  <span className="text-base font-mono-data font-bold text-white">
                    {selectedNode.pressure.toFixed(1)} PSI
                  </span>
                </div>

                <div className="flex items-center justify-between p-2.5 bg-slate-950 rounded-lg border border-slate-800/90">
                  <span className="text-xs font-mono-data text-slate-400">TEMPERATURA INTERNA</span>
                  <span className="text-base font-mono-data font-bold text-amber-400">
                    {selectedNode.temperature.toFixed(1)} °C
                  </span>
                </div>

                <div className="flex items-center justify-between p-2.5 bg-slate-950 rounded-lg border border-slate-800/90">
                  <span className="text-xs font-mono-data text-slate-400">CAUDAL DE PROCESAMIENTO</span>
                  <span className="text-base font-mono-data font-bold text-emerald-400">
                    {selectedNode.flow.toLocaleString()} BPD
                  </span>
                </div>

                <div className="flex items-center justify-between p-2.5 bg-slate-950 rounded-lg border border-slate-800/90">
                  <span className="text-xs font-mono-data text-slate-400">ESTADO DE VÁLVULA</span>
                  <span className="text-xs font-mono-data font-bold text-emerald-400 flex items-center gap-1.5">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    {selectedNode.valveOpen ? 'ENERGIZADA / ABIERTA' : 'ENCLAVADA / CERRADA'}
                  </span>
                </div>
              </div>

              {/* Especificaciones de Ingeniería */}
              <div className="text-xs font-mono-data text-slate-400 p-3 bg-slate-950 rounded-lg border border-slate-800/90 mb-4 space-y-1.5">
                <div className="text-[10px] uppercase font-bold text-slate-300">
                  ESPECIFICACIÓN MECÁNICA
                </div>
                <div>CLASE DE SEGURIDAD: ASME SECCIÓN VIII DIV 1</div>
                <div>RATING DE BRIDA: 2,500# ANSI RF</div>
                <div>PRESIÓN MÁX. PERMITIDA (MAWP): 2,850 PSIG @ 120°C</div>
              </div>
            </div>

            {/* Acciones de Enclavamiento */}
            <div className="space-y-2 pt-3 border-t border-slate-800/80">
              <button
                onClick={() => onOpenInterlock(selectedNode)}
                className="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-sky-400 border border-slate-700 hover:border-sky-500 text-xs font-mono-data uppercase tracking-wider font-bold rounded-lg transition-all flex items-center justify-center gap-2 shadow-sm"
              >
                <Sliders className="w-4 h-4 text-sky-400" />
                <span>ACCIONAR VÁLVULA CON ENCLAVAMIENTO</span>
              </button>
              <p className="text-[10px] font-mono-data text-slate-500 text-center">
                Requiere mantener presionado 1.5s por protocolo de seguridad
              </p>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};


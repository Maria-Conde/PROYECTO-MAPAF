import React, { useState, useEffect, useRef } from 'react';
import { ShieldAlert, CheckCircle2, X } from 'lucide-react';

interface HoldInterlockModalProps {
  isOpen: boolean;
  title: string;
  targetTag: string;
  actionDescription: string;
  warningNote?: string;
  onConfirm: () => void;
  onCancel: () => void;
  holdDurationMs?: number;
}

export const HoldInterlockModal: React.FC<HoldInterlockModalProps> = ({
  isOpen,
  title,
  targetTag,
  actionDescription,
  warningNote = 'Guarda de Enclavamiento Nivel 2 activa. La maniobra afecta el equilibrio hidrostático del sistema.',
  onConfirm,
  onCancel,
  holdDurationMs = 1500,
}) => {
  const [holding, setHolding] = useState(false);
  const [progress, setProgress] = useState(0);
  const [completed, setCompleted] = useState(false);
  const timerRef = useRef<number | null>(null);
  const startTimeRef = useRef<number>(0);

  useEffect(() => {
    if (!isOpen) {
      setHolding(false);
      setProgress(0);
      setCompleted(false);
      if (timerRef.current) cancelAnimationFrame(timerRef.current);
    }
  }, [isOpen]);

  const handleMouseDown = () => {
    if (completed) return;
    setHolding(true);
    startTimeRef.current = Date.now();

    const updateLoop = () => {
      const elapsed = Date.now() - startTimeRef.current;
      const currentProgress = Math.min(100, (elapsed / holdDurationMs) * 100);
      setProgress(currentProgress);

      if (elapsed >= holdDurationMs) {
        setCompleted(true);
        setHolding(false);
        setTimeout(() => {
          onConfirm();
        }, 300);
      } else {
        timerRef.current = requestAnimationFrame(updateLoop);
      }
    };

    timerRef.current = requestAnimationFrame(updateLoop);
  };

  const handleMouseUp = () => {
    if (completed) return;
    setHolding(false);
    setProgress(0);
    if (timerRef.current) {
      cancelAnimationFrame(timerRef.current);
    }
  };

  if (!isOpen) return null;

  const radius = 38;
  const circumference = 2 * Math.PI * radius;
  const strokeDashoffset = circumference - (progress / 100) * circumference;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 backdrop-blur-sm p-4">
      <div className="relative w-full max-w-md bg-slate-900 rounded-2xl border border-slate-700/80 shadow-2xl p-6 text-slate-100">
        {/* Cabecera */}
        <div className="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
          <div className="flex items-center gap-2.5">
            <div className="p-2 bg-amber-500/10 border border-amber-500/30 rounded-lg text-amber-400">
              <ShieldAlert className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-display text-sm font-bold tracking-wider uppercase text-white">
                {title}
              </h3>
              <p className="text-[11px] font-mono-data text-slate-400">
                PROTOCOLO DE ENCLAVAMIENTO CRÍTICO
              </p>
            </div>
          </div>
          <button
            onClick={onCancel}
            aria-label="Cerrar modal"
            className="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Caja de Activo Destino */}
        <div className="bg-slate-950 rounded-xl border border-slate-800 p-4 mb-4">
          <div className="text-[10px] font-mono-data text-slate-400 uppercase tracking-wider mb-1">
            SUB-SISTEMA / ELEMENTO DESTINO
          </div>
          <div className="font-mono-data text-base font-bold text-sky-400">
            {targetTag}
          </div>
          <div className="text-xs font-mono-data text-slate-200 mt-1">
            {actionDescription}
          </div>
        </div>

        {/* Nota de Advertencia Operativa */}
        <div className="bg-amber-500/10 border-l-2 border-amber-500 p-3 mb-5 text-xs text-amber-200/90 font-mono-data rounded-r-lg">
          {warningNote}
        </div>

        {/* Pulsador Físico de Retención (Hold-to-Confirm) */}
        <div className="flex flex-col items-center justify-center p-5 bg-slate-950 rounded-xl border border-slate-800">
          <div className="relative w-28 h-28 flex items-center justify-center select-none cursor-pointer">
            <svg className="w-28 h-28 -rotate-90">
              <circle
                cx="56"
                cy="56"
                r={radius}
                stroke="#1e293b"
                strokeWidth="6"
                fill="transparent"
              />
              <circle
                cx="56"
                cy="56"
                r={radius}
                stroke={completed ? '#10b981' : holding ? '#0ea5e9' : '#475569'}
                strokeWidth="6"
                strokeDasharray={circumference}
                strokeDashoffset={strokeDashoffset}
                strokeLinecap="round"
                fill="transparent"
                className="transition-all duration-75"
              />
            </svg>

            <button
              onMouseDown={handleMouseDown}
              onMouseUp={handleMouseUp}
              onMouseLeave={handleMouseUp}
              onTouchStart={handleMouseDown}
              onTouchEnd={handleMouseUp}
              className={`absolute inset-3 rounded-full flex flex-col items-center justify-center transition-all ${
                completed
                  ? 'bg-emerald-500 text-slate-950 shadow-[0_0_20px_rgba(16,185,129,0.5)]'
                  : holding
                  ? 'bg-sky-500 text-slate-950 scale-95 shadow-[0_0_20px_rgba(14,165,233,0.6)]'
                  : 'bg-slate-800 text-white hover:bg-slate-700 border border-slate-700'
              }`}
            >
              {completed ? (
                <CheckCircle2 className="w-7 h-7 animate-bounce" />
              ) : (
                <>
                  <span className="font-display text-[11px] font-bold uppercase tracking-wider text-center leading-tight">
                    {holding ? 'MANTENIENDO' : 'PULSAR Y SOSTENER'}
                  </span>
                  <span className="font-mono-data text-[10px] opacity-80 mt-0.5">
                    {(holdDurationMs / 1000).toFixed(1)}s GUARDA
                  </span>
                </>
              )}
            </button>
          </div>

          <p className="mt-3.5 text-xs font-mono-data text-slate-400 text-center">
            {completed
              ? 'ENCLAVAMIENTO LIBERADO — ORDEN TRANSMITIDA'
              : holding
              ? `Liberando en ${Math.max(0, ((holdDurationMs - (progress / 100) * holdDurationMs) / 1000)).toFixed(1)}s...`
              : 'Mantenga presionado el botón 1.5s continuos para accionar'}
          </p>
        </div>

        {/* Acciones */}
        <div className="flex justify-end gap-3 mt-5">
          <button
            onClick={onCancel}
            className="w-full py-2.5 text-xs font-mono-data uppercase tracking-wider bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg transition-colors font-semibold"
          >
            ABORTAR / CANCELAR ACCIÓN
          </button>
        </div>
      </div>
    </div>
  );
};

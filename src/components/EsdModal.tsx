import React, { useState } from 'react';
import { AlertOctagon, Key, X, Check, ShieldAlert } from 'lucide-react';

interface EsdModalProps {
  isOpen: boolean;
  onConfirm: () => void;
  onCancel: () => void;
}

export const EsdModal: React.FC<EsdModalProps> = ({ isOpen, onConfirm, onCancel }) => {
  const [safetyGuardFlipped, setSafetyGuardFlipped] = useState(false);
  const [keyRotated, setKeyRotated] = useState(false);
  const [confirmText, setConfirmText] = useState('');

  if (!isOpen) return null;

  const canExecute =
    safetyGuardFlipped &&
    keyRotated &&
    (confirmText.trim().toUpperCase() === 'PARADA' ||
      confirmText.trim().toUpperCase() === 'ESD-CONFIRM');

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-4">
      <div className="relative w-full max-w-lg bg-slate-900 rounded-2xl border-2 border-rose-500/80 shadow-[0_0_50px_rgba(244,63,94,0.3)] overflow-hidden text-slate-100">
        {/* Franja diagonal superior de peligro */}
        <div className="h-3 w-full bg-hatched-danger border-b border-rose-500/80" />

        <div className="p-6">
          {/* Cabecera */}
          <div className="flex items-start justify-between border-b border-slate-800 pb-4 mb-4">
            <div className="flex items-center gap-3">
              <div className="p-2.5 bg-rose-500/20 border border-rose-500/50 rounded-xl text-rose-400">
                <AlertOctagon className="w-8 h-8 animate-pulse" />
              </div>
              <div>
                <h2 className="font-display text-lg font-bold uppercase tracking-wider text-rose-200">
                  PARADA DE EMERGENCIA GENERAL (ESD-1)
                </h2>
                <p className="text-xs font-mono-data text-rose-300/80">
                  AISLAMIENTO TOTAL DE ESTACIÓN Y DESPRESURIZACIÓN SIL-3
                </p>
              </div>
            </div>
            <button
              onClick={onCancel}
              aria-label="Cerrar modal"
              className="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          {/* Advertencia Crítica */}
          <div className="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3.5 mb-5 text-xs font-mono-data text-rose-200 space-y-1.5">
            <div className="font-bold flex items-center gap-2 text-rose-300">
              <ShieldAlert className="w-4 h-4" />
              <span>PROTOCOLO DE SEGURIDAD CRÍTICA — NORMATIVA ISA-84 / IEC-61511</span>
            </div>
            <p className="leading-relaxed text-slate-300 text-[11px]">
              El disparo de la parada de emergencia cerrará de inmediato las válvulas maestras SSV de
              todos los cabezales, desenergizará los variadores de bombas electrosumergibles (BES/ESP),
              aislará trenes de compresión y desviará el inventario a la tea de antorcha.
            </p>
          </div>

          {/* Pasos de Seguridad Triple Enclavamiento */}
          <div className="space-y-3 mb-6 bg-slate-950 p-4 rounded-xl border border-slate-800">
            <div className="text-[10px] font-mono-data uppercase tracking-wider text-slate-400 font-semibold">
              TRIPLE ENCLAVAMIENTO DE CONFIRMACIÓN OBLIGATORIA
            </div>

            {/* Enclavamiento 1: Guarda física */}
            <div className="flex items-center justify-between p-3 bg-slate-900 rounded-lg border border-slate-800">
              <div className="flex items-center gap-2.5 text-xs font-mono-data">
                <span className="text-slate-500 font-bold">01.</span>
                <span>TAPA FÍSICA DE SEGURIDAD ROJA</span>
              </div>
              <button
                type="button"
                onClick={() => setSafetyGuardFlipped(!safetyGuardFlipped)}
                className={`px-3.5 py-1.5 text-xs font-mono-data uppercase font-bold rounded-md transition-all ${
                  safetyGuardFlipped
                    ? 'bg-emerald-500 text-slate-950 shadow-sm'
                    : 'bg-slate-800 text-slate-400 border border-slate-700 hover:border-slate-500'
                }`}
              >
                {safetyGuardFlipped ? 'ARMADA / ABIERTA' : 'GUARDA CERRADA'}
              </button>
            </div>

            {/* Enclavamiento 2: Llave de supervisor */}
            <div className="flex items-center justify-between p-3 bg-slate-900 rounded-lg border border-slate-800">
              <div className="flex items-center gap-2.5 text-xs font-mono-data">
                <span className="text-slate-500 font-bold">02.</span>
                <Key className="w-4 h-4 text-amber-400" />
                <span>LLAVE ROTATORIA DE SUPERVISOR</span>
              </div>
              <button
                type="button"
                disabled={!safetyGuardFlipped}
                onClick={() => setKeyRotated(!keyRotated)}
                className={`px-3.5 py-1.5 text-xs font-mono-data uppercase font-bold rounded-md transition-all disabled:opacity-30 disabled:cursor-not-allowed ${
                  keyRotated
                    ? 'bg-emerald-500 text-slate-950 shadow-sm'
                    : 'bg-slate-800 text-slate-400 border border-slate-700 hover:border-slate-500'
                }`}
              >
                {keyRotated ? 'LLAVE GIRADA 90°' : 'POSICIÓN NEUTRA'}
              </button>
            </div>

            {/* Enclavamiento 3: Confirmación textual */}
            <div className="p-3 bg-slate-900 rounded-lg border border-slate-800">
              <div className="flex items-center justify-between text-xs font-mono-data mb-2">
                <div className="flex items-center gap-2.5">
                  <span className="text-slate-500 font-bold">03.</span>
                  <span>CONFIRMACIÓN ESCRITA DE OPERADOR</span>
                </div>
                <span className="text-[11px] text-amber-400 font-semibold">ESCRIBA: PARADA</span>
              </div>
              <div className="relative">
                <input
                  type="text"
                  disabled={!keyRotated}
                  value={confirmText}
                  onChange={(e) => setConfirmText(e.target.value)}
                  placeholder="Escriba 'PARADA' o 'ESD-CONFIRM'"
                  className="w-full bg-slate-950 border border-slate-800 focus:border-rose-500 text-xs font-mono-data px-3.5 py-2.5 text-white uppercase tracking-wider rounded-lg outline-none disabled:opacity-30 placeholder:text-slate-600 transition-colors"
                />
                {(confirmText.trim().toUpperCase() === 'PARADA' ||
                  confirmText.trim().toUpperCase() === 'ESD-CONFIRM') && (
                  <Check className="w-4 h-4 text-emerald-400 absolute right-3.5 top-3" />
                )}
              </div>
            </div>
          </div>

          {/* Botones de Acción */}
          <div className="flex items-center justify-between gap-4">
            <button
              type="button"
              onClick={onCancel}
              className="px-4 py-2.5 text-xs font-mono-data uppercase tracking-wider bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-lg transition-colors font-semibold"
            >
              CANCELAR Y DESARMAR
            </button>

            <button
              type="button"
              disabled={!canExecute}
              onClick={onConfirm}
              className={`flex-1 py-2.5 px-4 text-xs font-mono-data font-bold uppercase tracking-wider rounded-lg transition-all flex items-center justify-center gap-2 ${
                canExecute
                  ? 'bg-rose-500 hover:bg-rose-600 text-white shadow-[0_0_20px_rgba(244,63,94,0.6)] animate-pulse'
                  : 'bg-rose-950/40 text-rose-300/30 cursor-not-allowed border border-rose-900/40'
              }`}
            >
              <AlertOctagon className="w-4 h-4" />
              <span>ACTIVAR PARADA DE EMERGENCIA</span>
            </button>
          </div>
        </div>

        {/* Franja diagonal inferior */}
        <div className="h-2 w-full bg-hatched-danger" />
      </div>
    </div>
  );
};

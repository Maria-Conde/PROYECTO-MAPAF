import React, { useState } from 'react';
import { Database, Server, RefreshCw, CheckCircle, AlertTriangle, ExternalLink, X, ShieldCheck } from 'lucide-react';
import { XamppStatus, mapafApi } from '../services/mapafApi';

interface XamppConnectionModalProps {
  isOpen: boolean;
  onClose: () => void;
  status: XamppStatus;
  onRefresh: () => Promise<void>;
}

export const XamppConnectionModal: React.FC<XamppConnectionModalProps> = ({
  isOpen,
  onClose,
  status,
  onRefresh,
}) => {
  const [testing, setTesting] = useState(false);
  const [message, setMessage] = useState<string | null>(null);

  if (!isOpen) return null;

  const handleTestConnection = async () => {
    setTesting(true);
    setMessage(null);
    try {
      await onRefresh();
      setMessage('Prueba de conexión completada.');
    } catch {
      setMessage('Error al probar la conexión.');
    } finally {
      setTesting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm animate-fadeIn">
      <div className="relative w-full max-w-xl bg-slate-900 border border-slate-700/80 rounded-xl shadow-2xl p-6 text-slate-100 font-sans">
        
        {/* Header */}
        <div className="flex items-center justify-between pb-4 border-b border-slate-800">
          <div className="flex items-center gap-3">
            <div className={`p-2 rounded-lg ${status.connected ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400'}`}>
              <Database className="w-5 h-5" />
            </div>
            <div>
              <h2 className="text-base font-bold text-white tracking-wide font-display">
                Conexión XAMPP · PHP & MySQL
              </h2>
              <p className="text-xs text-slate-400 font-mono-data">
                PROYECTO MAPAF · SENA ADSO 3413988
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-1 rounded-md text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Status Card */}
        <div className="my-5">
          {status.connected ? (
            <div className="flex items-center gap-3 p-3.5 bg-emerald-950/40 border border-emerald-500/30 rounded-lg text-emerald-300">
              <CheckCircle className="w-5 h-5 shrink-0 text-emerald-400" />
              <div className="text-xs">
                <p className="font-semibold text-emerald-200">Conexión Exitosa con XAMPP y MySQL</p>
                <p className="text-emerald-400/80 mt-0.5">
                  La aplicación está leyendo y guardando telemetría, eventos y roles en la base de datos <strong className="text-white">mapaf_db</strong>.
                </p>
              </div>
            </div>
          ) : (
            <div className="flex items-center gap-3 p-3.5 bg-rose-950/40 border border-rose-500/30 rounded-lg text-rose-300">
              <AlertTriangle className="w-5 h-5 shrink-0 text-rose-400" />
              <div className="text-xs">
                <p className="font-semibold text-rose-200">Sin Conexión con el Servidor XAMPP</p>
                <p className="text-rose-400/80 mt-0.5">
                  {status.error || 'Asegúrate de que Apache y MySQL estén iniciados en el Panel de Control de XAMPP.'}
                </p>
              </div>
            </div>
          )}
        </div>

        {/* Technical Grid */}
        <div className="bg-slate-950/60 rounded-lg border border-slate-800 p-4 space-y-2.5 text-xs font-mono-data mb-5">
          <div className="flex justify-between items-center py-1 border-b border-slate-800/60">
            <span className="text-slate-400 flex items-center gap-1.5">
              <Server className="w-3.5 h-3.5 text-sky-400" /> Servidor Web:
            </span>
            <span className="text-slate-200">Apache 2.4 (XAMPP Localhost)</span>
          </div>

          <div className="flex justify-between items-center py-1 border-b border-slate-800/60">
            <span className="text-slate-400">Lenguaje Backend:</span>
            <span className="text-emerald-400 font-bold">PHP {status.phpVersion || '8.0+'}</span>
          </div>

          <div className="flex justify-between items-center py-1 border-b border-slate-800/60">
            <span className="text-slate-400">Motor de Base de Datos:</span>
            <span className="text-purple-400 font-bold">MySQL {status.mysqlVersion || 'MariaDB'}</span>
          </div>

          <div className="flex justify-between items-center py-1 border-b border-slate-800/60">
            <span className="text-slate-400">Nombre de Base de Datos:</span>
            <span className="text-amber-300 font-bold">mapaf_db</span>
          </div>

          <div className="flex justify-between items-center py-1">
            <span className="text-slate-400">Tablas Verificadas:</span>
            <span className="text-emerald-300 font-bold">
              {status.tablesCount !== undefined ? `${status.tablesCount} tablas sincronizadas` : 'Sin datos'}
            </span>
          </div>
        </div>

        {/* Instructions for SENA */}
        <div className="text-xs text-slate-400 bg-slate-800/40 border border-slate-700/50 rounded-lg p-3 mb-5">
          <p className="font-semibold text-slate-300 mb-1 flex items-center gap-1.5">
            <ShieldCheck className="w-4 h-4 text-emerald-400" /> Instrucciones para XAMPP:
          </p>
          <ol className="list-decimal list-inside space-y-1 text-slate-400">
            <li>Abre <strong className="text-slate-300">XAMPP Control Panel</strong>.</li>
            <li>Inicia los módulos <strong className="text-emerald-400">Apache</strong> y <strong className="text-emerald-400">MySQL</strong>.</li>
            <li>Puedes abrir phpMyAdmin en el navegador para inspeccionar las tablas de <code>mapaf_db</code>.</li>
          </ol>
        </div>

        {message && (
          <div className="text-xs text-center text-emerald-400 mb-3 font-mono-data">
            {message}
          </div>
        )}

        {/* Actions */}
        <div className="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-800">
          <div className="flex items-center gap-2">
            <button
              onClick={handleTestConnection}
              disabled={testing}
              className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono-data rounded bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`w-3.5 h-3.5 ${testing ? 'animate-spin' : ''}`} />
              Probar Conexión
            </button>

            <a
              href="http://localhost/mapaf/"
              target="_blank"
              rel="noreferrer"
              className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono-data rounded bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors"
            >
              <ExternalLink className="w-3.5 h-3.5" />
              Abrir Web PHP
            </a>
          </div>

          <div className="flex items-center gap-2">
            <a
              href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=mapaf_db"
              target="_blank"
              rel="noreferrer"
              className="flex items-center gap-1 px-3 py-1.5 text-xs font-mono-data rounded bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border border-purple-500/40 transition-colors"
            >
              <ExternalLink className="w-3.5 h-3.5" />
              phpMyAdmin
            </a>

            <button
              onClick={onClose}
              className="px-4 py-1.5 text-xs font-mono-data rounded bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-colors"
            >
              Cerrar
            </button>
          </div>
        </div>

      </div>
    </div>
  );
};

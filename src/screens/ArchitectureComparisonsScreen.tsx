import React, { useState } from 'react';
import {
  Layers,
  Database,
  ShieldAlert,
  Sliders,
  Server,
  FileText,
  Activity,
  CheckCircle2,
  AlertTriangle,
  Flame,
  ArrowRight,
  ExternalLink,
  BookOpen,
  Cpu,
} from 'lucide-react';

export const ArchitectureComparisonsScreen: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'microservices' | 'database' | 'modes' | 'alarms' | 'schematics' | 'patterns'>('microservices');

  return (
    <div className="flex-1 flex flex-col gap-5 p-4 lg:p-6 max-w-[1780px] mx-auto w-full overflow-y-auto font-sans">
      
      {/* Encabezado Principal */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-slate-900/90 rounded-xl border border-slate-800 p-5 shadow-sm backdrop-blur-md">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="text-xs font-mono-data bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/30 uppercase font-bold">
              SENA ADSO · Ficha 3413988
            </span>
            <span className="text-xs font-mono-data text-slate-400">
              Regional Huila · Proyecto MAPAF
            </span>
          </div>
          <h1 className="font-display text-xl font-bold tracking-tight text-white">
            Guía Didáctica, Comparativas y Arquitectura Técnica
          </h1>
          <p className="text-xs text-slate-400 mt-0.5">
            Documentación interactiva y tablas comparativas para comprender la ingeniería de telemetría y el stack XAMPP (PHP + MySQL).
          </p>
        </div>

        {/* Selector de Pestañas Didácticas */}
        <div className="flex flex-wrap gap-1.5 bg-slate-950 p-1 rounded-lg border border-slate-800 text-xs font-mono-data">
          <button
            onClick={() => setActiveTab('microservices')}
            className={`px-3 py-1.5 rounded transition-all ${
              activeTab === 'microservices'
                ? 'bg-emerald-600 text-white font-bold shadow'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            Monolito vs Microservicios
          </button>
          <button
            onClick={() => setActiveTab('database')}
            className={`px-3 py-1.5 rounded transition-all ${
              activeTab === 'database'
                ? 'bg-sky-600 text-white font-bold shadow'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            LocalStorage vs MySQL
          </button>
          <button
            onClick={() => setActiveTab('modes')}
            className={`px-3 py-1.5 rounded transition-all ${
              activeTab === 'modes'
                ? 'bg-amber-600 text-white font-bold shadow'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            Modos de Operación
          </button>
          <button
            onClick={() => setActiveTab('alarms')}
            className={`px-3 py-1.5 rounded transition-all ${
              activeTab === 'alarms'
                ? 'bg-rose-600 text-white font-bold shadow'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            Alarmas ISA-18.2
          </button>
          <button
            onClick={() => setActiveTab('schematics')}
            className={`px-3 py-1.5 rounded transition-all ${
              activeTab === 'schematics'
                ? 'bg-purple-600 text-white font-bold shadow'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            Diagramas Técnicos P&ID
          </button>
          <button
            onClick={() => setActiveTab('patterns')}
            className={`px-3 py-1.5 rounded transition-all ${
              activeTab === 'patterns'
                ? 'bg-indigo-600 text-white font-bold shadow'
                : 'text-slate-400 hover:text-slate-200'
            }`}
          >
            Patrones & Antipatrones (GoF)
          </button>
        </div>
      </div>

      {/* 1. MONOLITO VS MICROSERVICIOS */}
      {activeTab === 'microservices' && (
        <div className="space-y-5 animate-fadeIn">
          <div className="p-4 bg-emerald-950/20 border-l-4 border-emerald-500 rounded-r-xl text-xs text-slate-300">
            <strong>Criterio de Evaluación SENA:</strong> Los microservicios permiten que la plataforma MAPAF escale y aísle sus módulos críticos (Telemetría IIoT, Gestión de Alertas, Control de Despachos y Reportes) evitando caídas globales de la planta ante picos de datos.
          </div>

          <div className="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg">
            <div className="p-4 bg-slate-950/80 border-b border-slate-800 flex justify-between items-center">
              <h2 className="font-display text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <Layers className="w-4 h-4 text-emerald-400" />
                Matriz Comparativa: Monolito Tradicional vs Microservicios MAPAF
              </h2>
              <span className="text-[11px] font-mono-data text-emerald-400">ACTIVIDAD 1 & 2 ADSO</span>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs text-slate-300 border-collapse">
                <thead>
                  <tr className="bg-slate-950/40 border-b border-slate-800 text-[11px] font-mono-data uppercase text-slate-400">
                    <th className="p-3.5">Criterio Técnico</th>
                    <th className="p-3.5 text-amber-400">Arquitectura Monolítica</th>
                    <th className="p-3.5 text-emerald-400">Microservicios (Proyecto MAPAF)</th>
                    <th className="p-3.5 text-sky-400">Justificación en la Operación</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800/60 font-sans">
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-slate-200">Estructura del Código</td>
                    <td className="p-3.5">Un solo paquete monolítico donde todo está acoplado en un único repositorio.</td>
                    <td className="p-3.5 font-semibold text-emerald-300">Servicios autónomos desacoplados (Auth, Telemetría, Alertas, Despachos).</td>
                    <td className="p-3.5 text-slate-400">Hernann, María José y Emilin pueden avanzar en backend y frontend sin bloquearse.</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-slate-200">Base de Datos</td>
                    <td className="p-3.5">Una sola base de datos centralizada compartida por todos los módulos.</td>
                    <td className="p-3.5 font-semibold text-emerald-300">Esquema modular relacional en MySQL (<code>mapaf_db</code>).</td>
                    <td className="p-3.5 text-slate-400">Garantiza persistencia ACID e integridad de datos de telemetría y usuarios.</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-slate-200">Despliegue y Publicación</td>
                    <td className="p-3.5">Cualquier cambio mínimo exige re-compilar y re-desplegar todo el sistema.</td>
                    <td className="p-3.5 font-semibold text-emerald-300">Despliegue independiente de endpoints vía Apache y PHP.</td>
                    <td className="p-3.5 text-slate-400">La sala de control nunca interrumpe el monitoreo de pozos por mantenimiento de reportes.</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-slate-200">Tolerancia a Fallos</td>
                    <td className="p-3.5 text-rose-400">Efecto dominó: un error en reportes puede tumbar la sala de control.</td>
                    <td className="p-3.5 font-semibold text-emerald-300">Aislamiento: si falla un servicio, el resto opera normalmente.</td>
                    <td className="p-3.5 text-slate-400">Seguridad SIL-3: el bucle de Parada de Emergencia ESD se mantiene alerta.</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-slate-200">Escalabilidad</td>
                    <td className="p-3.5">Monolítica: requiere clonar servidores enteros consumiendo memoria innecesaria.</td>
                    <td className="p-3.5 font-semibold text-emerald-300">Granular/Selectiva: se escala solo el servicio de alta demanda.</td>
                    <td className="p-3.5 text-slate-400">Permite absorber ráfagas masivas de lecturas de sensores IIoT cada segundo.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* 2. LOCALSTORAGE VS MYSQL */}
      {activeTab === 'database' && (
        <div className="space-y-5 animate-fadeIn">
          <div className="p-4 bg-sky-950/20 border-l-4 border-sky-500 rounded-r-xl text-xs text-slate-300">
            <strong>¿Por qué conectamos MAPAF a MySQL en XAMPP?</strong>
            Una aplicación industrial no puede depender de la memoria del navegador. MySQL en XAMPP proporciona persistencia real en disco, concurrencia de múltiples operadores y trazabilidad forense mediante la tabla de auditoría.
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
            {/* Card LocalStorage */}
            <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 relative overflow-hidden">
              <div className="absolute top-0 left-0 right-0 h-1 bg-slate-600" />
              <div className="flex justify-between items-center mb-4">
                <h3 className="font-display text-base font-bold text-slate-300">Almacenamiento Local (Mock / LocalStorage)</h3>
                <span className="text-[10px] font-mono-data bg-slate-800 text-slate-400 px-2 py-0.5 rounded">LIMITADO</span>
              </div>
              <ul className="space-y-3 text-xs text-slate-400 font-mono-data">
                <li className="flex justify-between border-b border-slate-800/60 pb-2">
                  <span>Alcance:</span>
                  <span className="text-rose-400">Aislado en un solo navegador</span>
                </li>
                <li className="flex justify-between border-b border-slate-800/60 pb-2">
                  <span>Capacidad Máxima:</span>
                  <span className="text-slate-300">~ 5 Megabytes</span>
                </li>
                <li className="flex justify-between border-b border-slate-800/60 pb-2">
                  <span>Concurrencia:</span>
                  <span className="text-rose-400">Incompatible con múltiples usuarios</span>
                </li>
                <li className="flex justify-between border-b border-slate-800/60 pb-2">
                  <span>Integridad Relacional:</span>
                  <span className="text-rose-400">Sin Foreign Keys ni validación SQL</span>
                </li>
                <li className="flex justify-between pb-1">
                  <span>Seguridad:</span>
                  <span className="text-rose-400">Manipulable desde la consola del navegador</span>
                </li>
              </ul>
            </div>

            {/* Card MySQL */}
            <div className="bg-slate-900 border border-emerald-500/40 rounded-xl p-5 relative overflow-hidden shadow-lg shadow-emerald-950/20">
              <div className="absolute top-0 left-0 right-0 h-1 bg-emerald-500" />
              <div className="flex justify-between items-center mb-4">
                <h3 className="font-display text-base font-bold text-white flex items-center gap-2">
                  <Database className="w-4 h-4 text-emerald-400" />
                  Base de Datos MySQL (`mapaf_db` en XAMPP)
                </h3>
                <span className="text-[10px] font-mono-data bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded border border-emerald-500/40">
                  IMPLEMENTADO ✓
                </span>
              </div>
              <ul className="space-y-3 text-xs text-slate-300 font-mono-data">
                <li className="flex justify-between border-b border-slate-800/60 pb-2">
                  <span>Alcance:</span>
                  <span className="text-emerald-400 font-bold">Centralizado y accesible por red local</span>
                </li>
                <li className="flex justify-between border-b border-slate-800/60 pb-2">
                  <span>Capacidad Máxima:</span>
                  <span className="text-emerald-400 font-bold">Terabytes (Motor InnoDB)</span>
                </li>
                <li className="flex justify-between border-b border-slate-800/60 pb-2">
                  <span>Concurrencia:</span>
                  <span className="text-emerald-400 font-bold">Múltiples operadores en tiempo real</span>
                </li>
                <li className="flex justify-between border-b border-slate-800/60 pb-2">
                  <span>Integridad Relacional:</span>
                  <span className="text-emerald-400 font-bold">Claves foráneas y tipos de datos estrictos</span>
                </li>
                <li className="flex justify-between pb-1">
                  <span>Trazabilidad:</span>
                  <span className="text-emerald-400 font-bold">Bitácora inmutable de auditoría con fecha y hora</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      )}

      {/* 3. MODOS DE OPERACIÓN */}
      {activeTab === 'modes' && (
        <div className="space-y-5 animate-fadeIn">
          <div className="p-4 bg-amber-950/20 border-l-4 border-amber-500 rounded-r-xl text-xs text-slate-300">
            <strong>Control de Proceso Operacional:</strong>
            La plataforma MAPAF permite simular los 3 regímenes operativos principales de un campo petrolero real: producción al 100%, estrangulamiento preventivo y parada crítica de emergencia ESD-1.
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
            {/* Nominal */}
            <div className="bg-slate-900 border border-emerald-500/40 rounded-xl p-5 relative overflow-hidden">
              <div className="absolute top-0 left-0 right-0 h-1 bg-emerald-500" />
              <div className="flex items-center justify-between mb-3">
                <h3 className="font-display text-sm font-bold text-emerald-400">1. Operación Nominal</h3>
                <span className="text-[10px] font-mono-data bg-emerald-500/10 text-emerald-300 px-2 py-0.5 rounded">100% CAPACIDAD</span>
              </div>
              <div className="text-2xl font-mono-data font-bold text-white mb-2">14,820 BPD</div>
              <p className="text-xs text-slate-400 mb-4">Condición de diseño óptima. Pozos en línea, estrangulador regulado y bombeo electrosumergible estable.</p>
              <div className="space-y-1.5 text-xs font-mono-data text-slate-300 border-t border-slate-800 pt-3">
                <div className="flex justify-between"><span>Válvulas SSV:</span><span className="text-emerald-400 font-bold">ABIERTA</span></div>
                <div className="flex justify-between"><span>Apertura Choke:</span><span className="text-slate-200">42.8%</span></div>
                <div className="flex justify-between"><span>Bomba ESP:</span><span className="text-slate-200">58.2 Hz</span></div>
              </div>
            </div>

            {/* Throttled */}
            <div className="bg-slate-900 border border-amber-500/40 rounded-xl p-5 relative overflow-hidden">
              <div className="absolute top-0 left-0 right-0 h-1 bg-amber-500" />
              <div className="flex items-center justify-between mb-3">
                <h3 className="font-display text-sm font-bold text-amber-400">2. Modo Estrangulado</h3>
                <span className="text-[10px] font-mono-data bg-amber-500/10 text-amber-300 px-2 py-0.5 rounded">80% PROTECCIÓN</span>
              </div>
              <div className="text-2xl font-mono-data font-bold text-amber-300 mb-2">11,850 BPD</div>
              <p className="text-xs text-slate-400 mb-4">Restricción controlada de caudal ante alta presión en manifold o mantenimiento preventivo en separadores.</p>
              <div className="space-y-1.5 text-xs font-mono-data text-slate-300 border-t border-slate-800 pt-3">
                <div className="flex justify-between"><span>Válvulas SSV:</span><span className="text-emerald-400 font-bold">ABIERTA</span></div>
                <div className="flex justify-between"><span>Apertura Choke:</span><span className="text-amber-400 font-bold">22.0%</span></div>
                <div className="flex justify-between"><span>Bomba ESP:</span><span className="text-amber-400 font-bold">52.0 Hz</span></div>
              </div>
            </div>

            {/* ESD Tripped */}
            <div className="bg-slate-900 border border-rose-500/40 rounded-xl p-5 relative overflow-hidden">
              <div className="absolute top-0 left-0 right-0 h-1 bg-rose-500" />
              <div className="flex items-center justify-between mb-3">
                <h3 className="font-display text-sm font-bold text-rose-400">3. Parada de Emergencia</h3>
                <span className="text-[10px] font-mono-data bg-rose-500/20 text-rose-300 px-2 py-0.5 rounded">SIL-3 CRÍTICO</span>
              </div>
              <div className="text-2xl font-mono-data font-bold text-rose-400 mb-2">0 BPD</div>
              <p className="text-xs text-slate-400 mb-4">Aislamiento total activado por el operador o disparo por sobrepresión. Cierre de pozos en menos de 2 segundos.</p>
              <div className="space-y-1.5 text-xs font-mono-data text-slate-300 border-t border-slate-800 pt-3">
                <div className="flex justify-between"><span>Válvulas SSV:</span><span className="text-rose-400 font-bold">CERRADA (FAIL-SAFE)</span></div>
                <div className="flex justify-between"><span>Apertura Choke:</span><span className="text-rose-400 font-bold">0.0%</span></div>
                <div className="flex justify-between"><span>Bomba ESP:</span><span className="text-rose-400 font-bold">0.0 Hz (TRIPPED)</span></div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* 4. ALARMAS ISA-18.2 */}
      {activeTab === 'alarms' && (
        <div className="space-y-5 animate-fadeIn">
          <div className="p-4 bg-rose-950/20 border-l-4 border-rose-500 rounded-r-xl text-xs text-slate-300">
            <strong>Norma ANSI/ISA-18.2 (Gestión de Sistemas de Alarmas):</strong>
            Establece los niveles de severidad para evitar la sobrecarga de alarmas en el operador, definiendo tiempos máximos de respuesta y acciones de contingencia requeridas.
          </div>

          <div className="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg">
            <table className="w-full text-left text-xs text-slate-300 border-collapse">
              <thead>
                <tr className="bg-slate-950/40 border-b border-slate-800 text-[11px] font-mono-data uppercase text-slate-400">
                  <th className="p-3.5">Severidad</th>
                  <th className="p-3.5">Código Visual</th>
                  <th className="p-3.5">Alerta Acústica</th>
                  <th className="p-3.5">Tiempo de Respuesta</th>
                  <th className="p-3.5">Acción Requerida del Operador</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60 font-sans">
                <tr className="hover:bg-slate-800/30">
                  <td className="p-3.5 font-bold text-rose-400">CRÍTICA (Emergency Trip)</td>
                  <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 border border-rose-500/40 font-mono-data">ROJO PULSANTE</span></td>
                  <td className="p-3.5 font-mono-data">Sirena continua 880 Hz</td>
                  <td className="p-3.5 font-bold text-white font-mono-data">&lt; 2 minutos</td>
                  <td className="p-3.5 text-slate-300">Verificar cierre inmediato de válvulas SSV y despresurización de líneas.</td>
                </tr>
                <tr className="hover:bg-slate-800/30">
                  <td className="p-3.5 font-bold text-amber-400">ADVERTENCIA (Warning)</td>
                  <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/40 font-mono-data">ÁMBAR</span></td>
                  <td className="p-3.5 font-mono-data">Doble pitido intermitente</td>
                  <td className="p-3.5 font-bold text-slate-200 font-mono-data">&lt; 15 minutos</td>
                  <td className="p-3.5 text-slate-300">Ajustar estrangulador (Choke) o regular la frecuencia del variador ESP.</td>
                </tr>
                <tr className="hover:bg-slate-800/30">
                  <td className="p-3.5 font-bold text-sky-400">AVISO (Advisory)</td>
                  <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-sky-500/20 text-sky-300 border border-sky-500/40 font-mono-data">AZUL CELESTE</span></td>
                  <td className="p-3.5 font-mono-data">Tono simple suave</td>
                  <td className="p-3.5 font-bold text-slate-200 font-mono-data">&lt; 60 minutos</td>
                  <td className="p-3.5 text-slate-300">Monitorear tendencia en el historiador y registrar observación en la bitácora.</td>
                </tr>
                <tr className="hover:bg-slate-800/30">
                  <td className="p-3.5 font-bold text-emerald-400">NOMINAL (Normal)</td>
                  <td className="p-3.5"><span className="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-mono-data">VERDE ESMERALDA</span></td>
                  <td className="p-3.5 font-mono-data">Silencioso</td>
                  <td className="p-3.5 font-bold text-slate-200 font-mono-data">Continuo</td>
                  <td className="p-3.5 text-slate-300">Supervisión rutinaria de telemetría dentro de umbrales seguros.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* 5. DIAGRAMAS TÉCNICOS & P&ID */}
      {activeTab === 'schematics' && (
        <div className="space-y-5 animate-fadeIn">
          {/* Diagrama Cabezal */}
          <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg">
            <div className="flex justify-between items-center mb-3">
              <div>
                <h3 className="font-display text-base font-bold text-white">Esquema Técnico de Cabezal de Pozo (Christmas Tree)</h3>
                <p className="text-xs text-slate-400 font-mono-data">Ubicación física de los sensores que transmiten datos a MySQL</p>
              </div>
              <span className="text-[11px] font-mono-data bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/30">
                NORMA API 6A
              </span>
            </div>

            <div className="bg-slate-950 p-4 rounded-lg border border-slate-800">
              <svg className="w-full h-auto max-h-[380px]" viewBox="0 0 920 400" xmlns="http://www.w3.org/2000/svg">
                <!-- Suelo -->
                <rect x="40" y="360" width="840" height="15" fill="#1e293b" rx="3" />
                <line x1="40" y1="360" x2="880" y2="360" stroke="#475569" strokeDasharray="6 6" />
                <text x="50" y="372" fontFamily="'JetBrains Mono', monospace" fontSize="9" fill="#94a3b8">BODEGA DE POZO / SUPERFICIE (CELLAR)</text>

                <!-- Casing & Tubing Spool -->
                <rect x="200" y="300" width="100" height="60" fill="#334155" stroke="#475569" strokeWidth="2" />
                <text x="250" y="335" fontFamily="'JetBrains Mono', monospace" fontSize="10" fill="#fff" textAnchor="middle">CASING HEAD</text>

                <rect x="215" y="240" width="70" height="60" fill="#334155" stroke="#475569" strokeWidth="2" />
                <text x="250" y="275" fontFamily="'JetBrains Mono', monospace" fontSize="9" fill="#fff" textAnchor="middle">TUBING SPOOL</text>

                <!-- Válvula Maestra Inferior -->
                <polygon points="220,220 280,220 250,200 220,180 280,180" fill="#0f172a" stroke="#38bdf8" strokeWidth="2" />
                <text x="310" y="205" fontFamily="'Inter', sans-serif" fontSize="11" fill="#cbd5e1">Válvula Maestra Inferior (LMV)</text>

                <!-- Válvula SSV (Válvula Maestra Superior) -->
                <polygon points="220,160 280,160 250,140 220,120 280,120" fill="#0f172a" stroke="#10b981" strokeWidth="2" />
                <rect x="185" y="125" width="25" height="30" fill="#064e3b" stroke="#10b981" strokeWidth="1.5" rx="3" />
                <text x="310" y="145" fontFamily="'Inter', sans-serif" fontSize="11" fill="#34d399" fontWeight="bold">Válvula SSV (Superficie / Hidráulica)</text>

                <!-- Manómetro Tubing WHTP-101A -->
                <circle cx="250" cy="45" r="18" fill="#0f172a" stroke="#38bdf8" strokeWidth="2" />
                <text x="250" y="48" fontFamily="'JetBrains Mono', monospace" fontSize="8" fill="#38bdf8" textAnchor="middle" fontWeight="bold">PT-101</text>
                <line x1="250" y1="63" x2="250" y2="120" stroke="#38bdf8" strokeWidth="2" />
                <rect x="280" y="30" width="180" height="28" fill="#1e293b" stroke="#38bdf8" strokeWidth="1" rx="4" />
                <text x="290" y="44" fontFamily="'JetBrains Mono', monospace" fontSize="8" fill="#94a3b8">WHTP-101A (Tubing)</text>
                <text x="290" y="54" fontFamily="'JetBrains Mono', monospace" fontSize="9" fill="#38bdf8" fontWeight="bold">1,842.5 PSI en MySQL</text>

                <!-- Wing Valve y Choke hacia la derecha -->
                <rect x="280" y="130" width="100" height="20" fill="#334155" stroke="#475569" strokeWidth="1.5" />
                <polygon points="380,125 410,125 395,140 380,155 410,155" fill="#0f172a" stroke="#10b981" strokeWidth="2" />

                <!-- Choke Valve FCV-101 -->
                <rect x="410" y="130" width="80" height="20" fill="#334155" stroke="#475569" strokeWidth="1.5" />
                <polygon points="490,120 530,120 510,140 490,160 530,160" fill="#1e293b" stroke="#f59e0b" strokeWidth="2" />
                <rect x="500" y="95" width="20" height="25" fill="#78350f" stroke="#f59e0b" strokeWidth="1.5" rx="3" />
                <rect x="540" y="100" width="180" height="32" fill="#1e293b" stroke="#f59e0b" strokeWidth="1" rx="4" />
                <text x="550" y="114" fontFamily="'JetBrains Mono', monospace" fontSize="8" fill="#94a3b8">FCV-101 (Estrangulador Choke)</text>
                <text x="550" y="125" fontFamily="'JetBrains Mono', monospace" fontSize="9" fill="#fbbf24" fontWeight="bold">Apertura: 42.8% regulable</text>

                <!-- Tubería hacia Manifold -->
                <path d="M 530,140 L 800,140 L 800,240 L 870,240" fill="none" stroke="#10b981" strokeWidth="5" strokeDasharray="6 6" />
                <polygon points="870,235 885,240 870,245" fill="#10b981" />
                <text x="680" y="270" fontFamily="'JetBrains Mono', monospace" fontSize="10" fill="#34d399" fontWeight="bold">FLUIDO HACIA MANIFOLD MAN-400</text>

                <!-- Sensor Casing CASING-101B -->
                <rect x="120" y="280" width="80" height="15" fill="#334155" stroke="#475569" strokeWidth="1.5" />
                <circle cx="110" cy="287" r="16" fill="#0f172a" stroke="#fbbf24" strokeWidth="2" />
                <text x="110" y="290" fontFamily="'JetBrains Mono', monospace" fontSize="7" fill="#fbbf24" textAnchor="middle" fontWeight="bold">PT-102</text>
                <rect x="20" y="220" width="160" height="30" fill="#1e293b" stroke="#fbbf24" strokeWidth="1" rx="4" />
                <text x="30" y="234" fontFamily="'JetBrains Mono', monospace" fontSize="8" fill="#94a3b8">CASING-101B (Anular)</text>
                <text x="30" y="244" fontFamily="'JetBrains Mono', monospace" fontSize="9" fill="#fbbf24" fontWeight="bold">642.0 PSI · Aviso</text>
              </svg>
            </div>
          </div>
        </div>
      )}

      {/* 6. PATRONES & ANTIPATRONES (GoF) */}
      {activeTab === 'patterns' && (
        <div className="space-y-5 animate-fadeIn">
          <div className="p-4 bg-indigo-950/20 border-l-4 border-indigo-500 rounded-r-xl text-xs text-slate-300">
            <strong>Criterio de Evaluación SENA (Ficha 3413988):</strong> La implementación de patrones de diseño GoF (Creacionales, Estructurales, Comportamiento) y la mitigación de los 8 antipatrones críticos garantiza alta cohesión, bajo acoplamiento, seguridad operacional en pozos y cumplimiento de normas ISA-18.2, API 6A y RFC 7519 (JWT).
          </div>

          <div className="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg">
            <div className="p-4 bg-slate-950/80 border-b border-slate-800 flex justify-between items-center">
              <h2 className="font-display text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <Cpu className="w-4 h-4 text-indigo-400" />
                Matriz Resumen de Patrones GoF y Antipatrones en MAPAF SCADA
              </h2>
              <span className="text-[11px] font-mono-data text-emerald-400">ACTIVIDAD 3 & 4 ADSO</span>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs text-slate-300 border-collapse">
                <thead>
                  <tr className="bg-slate-950/40 border-b border-slate-800 text-[11px] font-mono-data uppercase text-slate-400">
                    <th className="p-3.5">Patrón GoF</th>
                    <th className="p-3.5 text-sky-400">Tipo</th>
                    <th className="p-3.5 text-rose-400">Antipatrón que Evita</th>
                    <th className="p-3.5 text-emerald-400">Implementación en MAPAF</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800/60 font-sans">
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-white">Singleton</td>
                    <td className="p-3.5"><span className="text-[10px] font-mono-data bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/20">Creacional</span></td>
                    <td className="p-3.5 text-rose-300">Polling Flood & Fuga de Conexiones</td>
                    <td className="p-3.5 font-mono-data text-slate-300"><code>DatabaseSingleton.php</code> (Conexión PDO única compartida a MySQL mapaf_db)</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-white">Factory Method</td>
                    <td className="p-3.5"><span className="text-[10px] font-mono-data bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/20">Creacional</span></td>
                    <td className="p-3.5 text-rose-300">Hardcoded Types & Código Espagueti</td>
                    <td className="p-3.5 font-mono-data text-slate-300"><code>SensorFactory.php</code> (Instancia PT, TT, FT, VFD con calibración ISA-18.2)</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-white">Observer</td>
                    <td className="p-3.5"><span className="text-[10px] font-mono-data bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded border border-sky-500/20">Comportamiento</span></td>
                    <td className="p-3.5 text-rose-300">Acoplamiento Fuerte entre Telemetría y Alarmas</td>
                    <td className="p-3.5 font-mono-data text-slate-300"><code>TelemetrySubject.php</code> (Emite lecturas reactivas a alarmas, bitácora y SIS)</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-white">Command</td>
                    <td className="p-3.5"><span className="text-[10px] font-mono-data bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded border border-sky-500/20">Comportamiento</span></td>
                    <td className="p-3.5 text-rose-300">Maniobras Irreversibles sin Auditoría</td>
                    <td className="p-3.5 font-mono-data text-slate-300"><code>ScadaCommand.php</code> (Ajuste de Choke y Válvulas con función UNDO y firma JWT)</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-white">Strategy</td>
                    <td className="p-3.5"><span className="text-[10px] font-mono-data bg-purple-500/10 text-purple-400 px-2 py-0.5 rounded border border-purple-500/20">Comportamiento</span></td>
                    <td className="p-3.5 text-rose-300">Golden Hammer en Cálculo de Producción</td>
                    <td className="p-3.5 font-mono-data text-slate-300"><code>FlowCalculationStrategy.php</code> (Coriolis AGA-11, Placa AGA-3, Beggs & Brill)</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-white">State</td>
                    <td className="p-3.5"><span className="text-[10px] font-mono-data bg-sky-500/10 text-sky-400 px-2 py-0.5 rounded border border-sky-500/20">Comportamiento</span></td>
                    <td className="p-3.5 text-rose-300">Bypass sin Enclavamiento (IEC 61511)</td>
                    <td className="p-3.5 font-mono-data text-slate-300"><code>WellheadState.php</code> (Ciclo de pozo API 6A: Cerrado, Produciendo, Disparado)</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-white">Adapter</td>
                    <td className="p-3.5"><span className="text-[10px] font-mono-data bg-teal-500/10 text-teal-400 px-2 py-0.5 rounded border border-teal-500/20">Estructural</span></td>
                    <td className="p-3.5 text-rose-300">Incompatibilidad de Protocolos Legacy</td>
                    <td className="p-3.5 font-mono-data text-slate-300"><code>IndustrialProtocolAdapter.php</code> (Conversor Modbus RTU binario y OPC-UA a JSON)</td>
                  </tr>
                  <tr className="hover:bg-slate-800/30">
                    <td className="p-3.5 font-bold text-white">Repository</td>
                    <td className="p-3.5"><span className="text-[10px] font-mono-data bg-amber-500/10 text-amber-400 px-2 py-0.5 rounded border border-amber-500/20">Arquitectónico</span></td>
                    <td className="p-3.5 text-rose-300">The God Object & Inyección SQL</td>
                    <td className="p-3.5 font-mono-data text-slate-300"><code>ScadaRepositories.php</code> (Desacopla SQL PDO de controladores REST)</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};

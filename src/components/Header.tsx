import React from 'react';
import { AlertOctagon, Volume2, VolumeX, Shield, ShieldCheck } from 'lucide-react';

export type ScreenId = 'overview' | 'wellheads' | 'alarms' | 'historian' | 'facility' | 'roles';

interface HeaderProps {
  currentScreen: ScreenId;
  onSelectScreen: (screen: ScreenId) => void;
  onOpenEsd: () => void;
  unacknowledgedCount: number;
  audioMuted: boolean;
  onToggleAudio: () => void;
  activeRoleName?: string;
  activeRoleColor?: string;
}

export const Header: React.FC<HeaderProps> = ({
  currentScreen,
  onSelectScreen,
  onOpenEsd,
  unacknowledgedCount,
  audioMuted,
  onToggleAudio,
  activeRoleName = 'Admin',
  activeRoleColor = 'purple',
}) => {
  const navItems: { id: ScreenId; label: string; badge?: number }[] = [
    { id: 'overview', label: 'Visión General' },
    { id: 'wellheads', label: 'Pozos y Válvulas' },
    { id: 'alarms', label: 'Alarmas', badge: unacknowledgedCount },
    { id: 'historian', label: 'Historiador' },
    { id: 'facility', label: 'Vistas de Planta' },
    { id: 'roles', label: 'Seguridad y Roles' },
  ];

  return (
    <header className="flex items-center justify-between px-4 sm:px-6 py-2.5 bg-[#090d1a] border-b border-slate-800/80 shadow-md select-none shrink-0 z-40">
      {/* Zone 1: Single Text Element Wordmark in Display Face */}
      <a
        href="#"
        onClick={(e) => {
          e.preventDefault();
          onSelectScreen('overview');
        }}
        className="flex items-center gap-2.5 group cursor-pointer"
      >
        <div className="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981]" />
        <span className="font-display text-sm sm:text-base font-bold tracking-tight text-white group-hover:text-emerald-400 transition-colors whitespace-nowrap">
          SCADA Táctico · RTOC
        </span>
      </a>

      {/* Zone 2: 4-6 Nav Links, 1-2 Word Labels, Single-Line */}
      <nav className="flex items-center gap-1 sm:gap-2 lg:gap-3 overflow-x-auto py-1">
        {navItems.map((item) => {
          const isActive = currentScreen === item.id;
          return (
            <button
              key={item.id}
              onClick={() => onSelectScreen(item.id)}
              className={`relative px-3 py-1.5 text-xs font-mono-data uppercase tracking-wider whitespace-nowrap rounded-[4px] transition-all flex items-center gap-1.5 ${
                isActive
                  ? 'bg-slate-800/90 text-emerald-400 font-semibold shadow-inner border border-slate-700/60'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 border border-transparent'
              }`}
            >
              <span>{item.label}</span>
              {item.badge !== undefined && item.badge > 0 && (
                <span className="inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold bg-rose-500 text-white rounded-full leading-tight animate-pulse shadow-[0_0_8px_rgba(244,63,94,0.6)]">
                  {item.badge}
                </span>
              )}
            </button>
          );
        })}
      </nav>

      {/* Zone 3: Actions & Active Role Indicator */}
      <div className="flex items-center gap-2.5 shrink-0">
        <button
          onClick={() => onSelectScreen('roles')}
          title="Ver permisos del rol actual"
          className="hidden sm:flex items-center gap-1.5 px-2.5 py-1 text-xs font-mono-data rounded-[4px] border border-purple-500/30 bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 transition-colors"
        >
          <Shield className="w-3.5 h-3.5 text-purple-400" />
          <span className="font-semibold">{activeRoleName}</span>
        </button>

        <button
          onClick={onToggleAudio}
          title={audioMuted ? 'Activar sirena de alarmas' : 'Silenciar sirena de alarmas'}
          className={`p-2 rounded-[4px] border transition-colors ${
            audioMuted
              ? 'bg-slate-900 border-slate-800 text-slate-500 hover:text-slate-300'
              : 'bg-sky-950/60 border-sky-500/50 text-sky-400 hover:bg-sky-900/60 shadow-[0_0_8px_rgba(14,165,233,0.3)]'
          }`}
        >
          {audioMuted ? <VolumeX className="w-4 h-4" /> : <Volume2 className="w-4 h-4" />}
        </button>

        <button
          onClick={onOpenEsd}
          className="flex items-center gap-2 px-3 py-1.5 text-xs font-mono-data font-bold uppercase tracking-wider bg-rose-600 hover:bg-rose-500 text-white rounded-[4px] border border-rose-500 shadow-[0_0_12px_rgba(244,63,94,0.4)] whitespace-nowrap transition-all active:scale-95"
        >
          <AlertOctagon className="w-4 h-4 animate-pulse" />
          <span className="hidden md:inline">PARADA EMERGENCIA</span>
          <span>(ESD)</span>
        </button>
      </div>
    </header>
  );
};


import React from 'react';
import { OperationalSeverity } from '../types/scada';

interface SplitBadgeProps {
  prefix: string;
  value: string | number;
  severity?: OperationalSeverity | 'info' | 'muted';
  className?: string;
}

export const SplitBadge: React.FC<SplitBadgeProps> = ({
  prefix,
  value,
  severity = 'nominal',
  className = '',
}) => {
  let borderColor = 'border-emerald-500/30';
  let prefixText = 'text-emerald-400';
  let bgTint = 'bg-emerald-500/10';

  if (severity === 'warning') {
    borderColor = 'border-amber-500/40';
    prefixText = 'text-amber-400';
    bgTint = 'bg-amber-500/10';
  } else if (severity === 'critical') {
    borderColor = 'border-rose-500/40';
    prefixText = 'text-rose-400';
    bgTint = 'bg-rose-500/10';
  } else if (severity === 'advisory' || severity === 'info') {
    borderColor = 'border-sky-500/40';
    prefixText = 'text-sky-400';
    bgTint = 'bg-sky-500/10';
  } else if (severity === 'muted') {
    borderColor = 'border-slate-800';
    prefixText = 'text-slate-400';
    bgTint = 'bg-slate-900';
  }

  return (
    <span
      className={`inline-flex items-center text-[10px] uppercase font-mono-data rounded-md border ${borderColor} ${bgTint} overflow-hidden shrink-0 shadow-xs ${className}`}
    >
      <span className={`px-2 py-0.5 font-bold tracking-wider ${prefixText} border-r ${borderColor} bg-slate-950/60`}>
        {prefix}
      </span>
      <span className="px-2 py-0.5 font-medium tracking-normal text-slate-200">
        {value}
      </span>
    </span>
  );
};

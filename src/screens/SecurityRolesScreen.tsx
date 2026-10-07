import React, { useState } from 'react';
import {
  Shield,
  ShieldAlert,
  ShieldCheck,
  UserCheck,
  Users,
  Plus,
  Trash2,
  Check,
  X,
  Search,
  Lock,
  Unlock,
  Key,
  Layers,
  FileText,
  UserPlus,
  Sliders,
  AlertCircle,
  Eye,
} from 'lucide-react';
import {
  UserRole,
  PermissionKey,
  PermissionDefinition,
  SecurityUser,
  AuditLogEntry,
} from '../types/scada';
import { ALL_PERMISSIONS } from '../mock/securityData';
import { SplitBadge } from '../components/SplitBadge';

interface SecurityRolesScreenProps {
  roles: UserRole[];
  users: SecurityUser[];
  auditLogs: AuditLogEntry[];
  currentRoleId: string;
  onSelectActiveRole: (roleId: string) => void;
  onUpdateRolePermissions: (roleId: string, permissions: PermissionKey[]) => void;
  onCreateRole: (newRole: Omit<UserRole, 'id' | 'isSystem'>) => void;
  onDeleteRole: (roleId: string) => void;
  onUpdateUserRole: (userId: string, newRoleId: string) => void;
  onCreateUser: (newUser: Omit<SecurityUser, 'id' | 'lastAccess'>) => void;
  onToggleUserStatus: (userId: string) => void;
}

type SecurityTab = 'roles' | 'users' | 'preview' | 'audit';

export const SecurityRolesScreen: React.FC<SecurityRolesScreenProps> = ({
  roles,
  users,
  auditLogs,
  currentRoleId,
  onSelectActiveRole,
  onUpdateRolePermissions,
  onCreateRole,
  onDeleteRole,
  onUpdateUserRole,
  onCreateUser,
  onToggleUserStatus,
}) => {
  const [activeTab, setActiveTab] = useState<SecurityTab>('roles');
  const [selectedRoleId, setSelectedRoleId] = useState<string>(roles[0]?.id || 'role-operator');

  // Modals state
  const [isCreateRoleOpen, setIsCreateRoleOpen] = useState(false);
  const [isCreateUserOpen, setIsCreateUserOpen] = useState(false);

  // New Role Form State
  const [newRoleName, setNewRoleName] = useState('');
  const [newRoleCode, setNewRoleCode] = useState('');
  const [newRoleDesc, setNewRoleDesc] = useState('');
  const [newRoleColor, setNewRoleColor] = useState<'emerald' | 'amber' | 'purple' | 'sky' | 'rose'>('sky');
  const [newRolePerms, setNewRolePerms] = useState<PermissionKey[]>([
    'view_telemetry',
    'export_historian',
  ]);

  // New User Form State
  const [newUserEmpCode, setNewUserEmpCode] = useState('');
  const [newUserFullName, setNewUserFullName] = useState('');
  const [newUserEmail, setNewUserEmail] = useState('');
  const [newUserRoleId, setNewUserRoleId] = useState(roles[0]?.id || 'role-operator');
  const [newUserSector, setNewUserSector] = useState('Sector 04 · Compresión');

  // Search & Filters
  const [userSearchTerm, setUserSearchTerm] = useState('');
  const [auditFilterCategory, setAuditFilterCategory] = useState<string>('TODAS');

  const selectedRole = roles.find((r) => r.id === selectedRoleId) || roles[0];
  const activeRole = roles.find((r) => r.id === currentRoleId) || roles[0];

  // Group permissions by category
  const categories = ['Monitoreo', 'Control y Maniobras', 'Seguridad Crítica', 'Administración'] as const;

  const handleTogglePermission = (key: PermissionKey) => {
    if (!selectedRole) return;
    const hasPerm = selectedRole.permissions.includes(key);
    const updated = hasPerm
      ? selectedRole.permissions.filter((p) => p !== key)
      : [...selectedRole.permissions, key];
    onUpdateRolePermissions(selectedRole.id, updated);
  };

  const handleCreateRoleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newRoleName.trim() || !newRoleCode.trim()) return;

    onCreateRole({
      name: newRoleName.trim(),
      code: newRoleCode.trim().toUpperCase(),
      description: newRoleDesc.trim() || 'Rol personalizado para gestión de operaciones.',
      color: newRoleColor,
      permissions: newRolePerms,
    });

    setIsCreateRoleOpen(false);
    setNewRoleName('');
    setNewRoleCode('');
    setNewRoleDesc('');
    setNewRolePerms(['view_telemetry', 'export_historian']);
  };

  const handleCreateUserSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newUserFullName.trim() || !newUserEmpCode.trim()) return;

    onCreateUser({
      employeeCode: newUserEmpCode.trim().toUpperCase(),
      fullName: newUserFullName.trim(),
      email: newUserEmail.trim() || `${newUserEmpCode.toLowerCase()}@rtoc-operations.com`,
      roleId: newUserRoleId,
      assignedSector: newUserSector,
      status: 'EN TURNO',
    });

    setIsCreateUserOpen(false);
    setNewUserEmpCode('');
    setNewUserFullName('');
    setNewUserEmail('');
  };

  const filteredUsers = users.filter((u) => {
    const term = userSearchTerm.toLowerCase();
    const roleObj = roles.find((r) => r.id === u.roleId);
    return (
      u.fullName.toLowerCase().includes(term) ||
      u.employeeCode.toLowerCase().includes(term) ||
      u.assignedSector.toLowerCase().includes(term) ||
      (roleObj && roleObj.name.toLowerCase().includes(term))
    );
  });

  const filteredAuditLogs = auditLogs.filter((log) => {
    if (auditFilterCategory === 'TODAS') return true;
    return log.category === auditFilterCategory;
  });

  const getRoleBadgeClasses = (color: string) => {
    switch (color) {
      case 'purple':
        return 'border-purple-500/40 bg-purple-500/10 text-purple-300';
      case 'amber':
        return 'border-amber-500/40 bg-amber-500/10 text-amber-300';
      case 'emerald':
        return 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300';
      case 'rose':
        return 'border-rose-500/40 bg-rose-500/10 text-rose-300';
      default:
        return 'border-sky-500/40 bg-sky-500/10 text-sky-300';
    }
  };

  return (
    <div className="flex-1 flex flex-col gap-4 p-4 lg:p-6 max-w-[1780px] mx-auto w-full overflow-y-auto">
      {/* Cabecera Principal del Módulo de Seguridad y Roles */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 shadow-sm backdrop-blur-md">
        <div className="flex items-center gap-3">
          <div className="p-2.5 bg-purple-500/10 border border-purple-500/30 rounded-lg text-purple-400">
            <Shield className="w-5 h-5" />
          </div>
          <div>
            <h1 className="font-display text-base font-bold uppercase tracking-wider text-white">
              Gestión de Seguridad, Roles y Permisos (RBAC SIL-3)
            </h1>
            <div className="text-xs font-mono-data text-slate-400 flex items-center gap-2 mt-0.5">
              <span>CONTROL DE ACCESO BASADO EN ROLES</span>
              <span aria-hidden="true">·</span>
              <span className="text-purple-400 font-semibold">{roles.length} ROLES CONFIGURADOS</span>
              <span aria-hidden="true">·</span>
              <span className="text-emerald-400 font-semibold">{users.length} USUARIOS ACTIVOS</span>
            </div>
          </div>
        </div>

        {/* Indicador de Rol Activo y Selector Rápido */}
        <div className="flex items-center gap-3 flex-wrap">
          <div className="flex items-center gap-2 bg-slate-950 px-3 py-1.5 rounded-lg border border-slate-800">
            <span className="text-xs font-mono-data text-slate-400">ROL EN SESIÓN:</span>
            <span
              className={`px-2 py-0.5 rounded text-xs font-mono-data font-bold uppercase border ${getRoleBadgeClasses(
                activeRole.color
              )}`}
            >
              {activeRole.name}
            </span>
          </div>

          <button
            onClick={() => setIsCreateRoleOpen(true)}
            className="px-3.5 py-2 text-xs font-mono-data font-bold uppercase bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition-all flex items-center gap-2 shadow-[0_0_12px_rgba(168,85,247,0.3)]"
          >
            <Plus className="w-4 h-4" />
            <span>NUEVO ROL</span>
          </button>

          <button
            onClick={() => setIsCreateUserOpen(true)}
            className="px-3.5 py-2 text-xs font-mono-data uppercase bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg transition-all flex items-center gap-2 shadow-sm"
          >
            <UserPlus className="w-4 h-4 text-emerald-400" />
            <span>AÑADIR USUARIO</span>
          </button>
        </div>
      </div>

      {/* Navegación por Pestañas (Tabbed View) */}
      <div className="flex items-center gap-2 border-b border-slate-800 pb-2 overflow-x-auto">
        <button
          onClick={() => setActiveTab('roles')}
          className={`px-4 py-2 text-xs font-mono-data uppercase tracking-wider rounded-lg transition-all flex items-center gap-2 font-semibold ${
            activeTab === 'roles'
              ? 'bg-slate-800 text-purple-400 border border-purple-500/40 shadow-sm'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
          }`}
        >
          <Layers className="w-4 h-4" />
          <span>Matriz de Roles y Permisos</span>
          <span className="ml-1 px-1.5 py-0.2 bg-purple-500/20 text-purple-300 rounded text-[10px]">
            {roles.length}
          </span>
        </button>

        <button
          onClick={() => setActiveTab('users')}
          className={`px-4 py-2 text-xs font-mono-data uppercase tracking-wider rounded-lg transition-all flex items-center gap-2 font-semibold ${
            activeTab === 'users'
              ? 'bg-slate-800 text-emerald-400 border border-emerald-500/40 shadow-sm'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
          }`}
        >
          <Users className="w-4 h-4" />
          <span>Personal y Asignación de Roles</span>
          <span className="ml-1 px-1.5 py-0.2 bg-emerald-500/20 text-emerald-300 rounded text-[10px]">
            {users.length}
          </span>
        </button>

        <button
          onClick={() => setActiveTab('preview')}
          className={`px-4 py-2 text-xs font-mono-data uppercase tracking-wider rounded-lg transition-all flex items-center gap-2 font-semibold ${
            activeTab === 'preview'
              ? 'bg-slate-800 text-sky-400 border border-sky-500/40 shadow-sm'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
          }`}
        >
          <Sliders className="w-4 h-4" />
          <span>Simulador de Rol en Vivo</span>
        </button>

        <button
          onClick={() => setActiveTab('audit')}
          className={`px-4 py-2 text-xs font-mono-data uppercase tracking-wider rounded-lg transition-all flex items-center gap-2 font-semibold ${
            activeTab === 'audit'
              ? 'bg-slate-800 text-amber-400 border border-amber-500/40 shadow-sm'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'
          }`}
        >
          <FileText className="w-4 h-4" />
          <span>Bitácora de Auditoría SIL-3</span>
          <span className="ml-1 px-1.5 py-0.2 bg-amber-500/20 text-amber-300 rounded text-[10px]">
            {auditLogs.length}
          </span>
        </button>
      </div>

      {/* CONTENIDO DE PESTAÑAS */}

      {/* PESTAÑA 1: MATRIZ DE ROLES Y PERMISOS */}
      {activeTab === 'roles' && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-4 flex-1">
          {/* Columna Izquierda: Lista de Roles (4 columnas) */}
          <div className="lg:col-span-4 flex flex-col gap-3 bg-slate-900/90 rounded-xl border border-slate-800/90 p-4 shadow-sm backdrop-blur-md">
            <div className="flex items-center justify-between border-b border-slate-800 pb-2.5">
              <span className="text-xs font-mono-data uppercase text-slate-400 font-semibold">
                ROLES EN EL SISTEMA
              </span>
              <span className="text-xs font-mono-data text-slate-500">
                Seleccione para configurar
              </span>
            </div>

            <div className="space-y-2.5 flex-1 overflow-y-auto pr-1">
              {roles.map((role) => {
                const isSelected = selectedRoleId === role.id;
                const isSystem = role.isSystem;
                const usersInRole = users.filter((u) => u.roleId === role.id).length;

                return (
                  <div
                    key={role.id}
                    onClick={() => setSelectedRoleId(role.id)}
                    className={`p-3.5 rounded-xl border transition-all cursor-pointer ${
                      isSelected
                        ? 'bg-slate-800/90 border-purple-500/60 shadow-md ring-1 ring-purple-500/30'
                        : 'bg-slate-950/70 border-slate-800 hover:border-slate-700 hover:bg-slate-900/60'
                    }`}
                  >
                    <div className="flex items-center justify-between mb-1.5">
                      <div className="flex items-center gap-2">
                        <span
                          className={`px-2 py-0.5 rounded text-[10px] font-mono-data font-bold uppercase border ${getRoleBadgeClasses(
                            role.color
                          )}`}
                        >
                          {role.code}
                        </span>
                        <h2 className="font-display text-sm font-bold text-white">
                          {role.name}
                        </h2>
                      </div>

                      {isSystem ? (
                        <span className="text-[10px] font-mono-data uppercase text-slate-500 bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                          SISTEMA
                        </span>
                      ) : (
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            onDeleteRole(role.id);
                          }}
                          title="Eliminar rol personalizado"
                          className="p-1 text-slate-500 hover:text-rose-400 rounded hover:bg-rose-500/10 transition-colors"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>

                    <p className="text-xs text-slate-400 line-clamp-2 leading-relaxed mb-3">
                      {role.description}
                    </p>

                    <div className="flex items-center justify-between text-xs font-mono-data text-slate-400 pt-2 border-t border-slate-800/60">
                      <span className="flex items-center gap-1.5">
                        <Users className="w-3.5 h-3.5 text-slate-400" />
                        {usersInRole} {usersInRole === 1 ? 'operador' : 'operadores'}
                      </span>
                      <span className="text-purple-400 font-semibold">
                        {role.permissions.length} de {ALL_PERMISSIONS.length} permisos
                      </span>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* Columna Derecha: Matriz Granular de Permisos del Rol Seleccionado (8 columnas) */}
          <div className="lg:col-span-8 flex flex-col gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md">
            {selectedRole && (
              <>
                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3">
                  <div>
                    <div className="flex items-center gap-2.5">
                      <h2 className="font-display text-base font-bold text-white">
                        Permisos para: {selectedRole.name}
                      </h2>
                      <span
                        className={`px-2 py-0.5 rounded text-[10px] font-mono-data font-bold uppercase border ${getRoleBadgeClasses(
                          selectedRole.color
                        )}`}
                      >
                        {selectedRole.code}
                      </span>
                    </div>
                    <p className="text-xs text-slate-400 mt-0.5">
                      {selectedRole.description}
                    </p>
                  </div>

                  <div className="flex items-center gap-2">
                    <button
                      onClick={() =>
                        onUpdateRolePermissions(
                          selectedRole.id,
                          ALL_PERMISSIONS.map((p) => p.key)
                        )
                      }
                      className="px-3 py-1.5 text-xs font-mono-data uppercase bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-700 transition-colors"
                    >
                      Marcar Todos
                    </button>
                    <button
                      onClick={() => onUpdateRolePermissions(selectedRole.id, [])}
                      className="px-3 py-1.5 text-xs font-mono-data uppercase bg-slate-800 hover:bg-slate-700 text-slate-300 rounded border border-slate-700 transition-colors"
                    >
                      Desmarcar Todos
                    </button>
                  </div>
                </div>

                {/* Categorías de Permisos */}
                <div className="space-y-5 flex-1 overflow-y-auto pr-1">
                  {categories.map((cat) => {
                    const permsInCat = ALL_PERMISSIONS.filter((p) => p.category === cat);
                    return (
                      <div key={cat} className="space-y-2.5">
                        <div className="flex items-center gap-2 text-xs font-mono-data uppercase font-bold text-slate-300">
                          {cat === 'Seguridad Crítica' && (
                            <ShieldAlert className="w-3.5 h-3.5 text-rose-400" />
                          )}
                          {cat === 'Control y Maniobras' && (
                            <Sliders className="w-3.5 h-3.5 text-amber-400" />
                          )}
                          {cat === 'Monitoreo' && (
                            <Eye className="w-3.5 h-3.5 text-sky-400" />
                          )}
                          {cat === 'Administración' && (
                            <Key className="w-3.5 h-3.5 text-purple-400" />
                          )}
                          <span>{cat}</span>
                          <span className="text-[10px] text-slate-500 font-normal">
                            ({permsInCat.filter((p) => selectedRole.permissions.includes(p.key)).length}/
                            {permsInCat.length} activos)
                          </span>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                          {permsInCat.map((perm) => {
                            const isAllowed = selectedRole.permissions.includes(perm.key);
                            return (
                              <div
                                key={perm.key}
                                onClick={() => handleTogglePermission(perm.key)}
                                className={`p-3 rounded-lg border transition-all cursor-pointer flex items-start gap-3 select-none ${
                                  isAllowed
                                    ? 'bg-slate-950/80 border-slate-700 hover:border-emerald-500/50'
                                    : 'bg-slate-950/40 border-slate-900 opacity-60 hover:opacity-100 hover:border-slate-800'
                                }`}
                              >
                                <div
                                  className={`w-5 h-5 rounded flex items-center justify-center shrink-0 mt-0.5 transition-colors ${
                                    isAllowed
                                      ? 'bg-emerald-500 text-slate-950 shadow-xs'
                                      : 'bg-slate-800 border border-slate-700 text-transparent'
                                  }`}
                                >
                                  <Check className="w-3.5 h-3.5 stroke-[3]" />
                                </div>

                                <div className="flex-1">
                                  <div className="text-xs font-semibold text-slate-200">
                                    {perm.label}
                                  </div>
                                  <div className="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                                    {perm.description}
                                  </div>
                                </div>
                              </div>
                            );
                          })}
                        </div>
                      </div>
                    );
                  })}
                </div>
              </>
            )}
          </div>
        </div>
      )}

      {/* PESTAÑA 2: USUARIOS Y ASIGNACIÓN DE ROLES */}
      {activeTab === 'users' && (
        <div className="flex flex-col gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md flex-1">
          {/* Barra de Filtro y Búsqueda */}
          <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div className="relative min-w-[280px]">
              <Search className="w-4 h-4 absolute left-3 top-2.5 text-slate-400" />
              <input
                type="text"
                value={userSearchTerm}
                onChange={(e) => setUserSearchTerm(e.target.value)}
                placeholder="Buscar por operador, código o sector..."
                className="w-full bg-slate-950 border border-slate-800 focus:border-purple-500 pl-9 pr-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none transition-colors placeholder:text-slate-500"
              />
            </div>

            <div className="text-xs font-mono-data text-slate-400">
              Mostrando <span className="text-white font-bold">{filteredUsers.length}</span> de{' '}
              {users.length} operadores
            </div>
          </div>

          {/* Tabla de Usuarios */}
          <div className="overflow-x-auto flex-1">
            <table className="w-full text-left text-xs font-mono-data border-collapse">
              <thead>
                <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px]">
                  <th className="py-3 px-3">CÓDIGO</th>
                  <th className="py-3 px-3">OPERADOR / NOMBRE</th>
                  <th className="py-3 px-3">CORREO RTOC</th>
                  <th className="py-3 px-3">ROL ASIGNADO</th>
                  <th className="py-3 px-3">SECTOR OPERATIVO</th>
                  <th className="py-3 px-3">ESTADO</th>
                  <th className="py-3 px-3 text-right">ACCIONES</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60">
                {filteredUsers.map((user) => {
                  const userRole = roles.find((r) => r.id === user.roleId) || roles[0];
                  return (
                    <tr
                      key={user.id}
                      className="hover:bg-slate-800/40 transition-colors group"
                    >
                      <td className="py-3.5 px-3 font-bold text-sky-400">{user.employeeCode}</td>
                      <td className="py-3.5 px-3">
                        <div className="font-sans font-semibold text-white">{user.fullName}</div>
                        <div className="text-[10px] text-slate-500">{user.lastAccess}</div>
                      </td>
                      <td className="py-3.5 px-3 text-slate-400">{user.email}</td>
                      <td className="py-3.5 px-3">
                        <select
                          value={user.roleId}
                          onChange={(e) => onUpdateUserRole(user.id, e.target.value)}
                          className="bg-slate-950 border border-slate-800 text-white rounded px-2.5 py-1 text-xs outline-none focus:border-purple-500 transition-colors cursor-pointer"
                        >
                          {roles.map((r) => (
                            <option key={r.id} value={r.id}>
                              {r.name} ({r.code})
                            </option>
                          ))}
                        </select>
                      </td>
                      <td className="py-3.5 px-3 text-slate-300">{user.assignedSector}</td>
                      <td className="py-3.5 px-3">
                        <span
                          className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase inline-flex items-center gap-1 ${
                            user.status === 'EN TURNO'
                              ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                              : user.status === 'ACTIVO'
                              ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30'
                              : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                          }`}
                        >
                          <span
                            className={`w-1.5 h-1.5 rounded-full ${
                              user.status === 'EN TURNO'
                                ? 'bg-emerald-400 animate-pulse'
                                : user.status === 'ACTIVO'
                                ? 'bg-sky-400'
                                : 'bg-rose-400'
                            }`}
                          />
                          {user.status}
                        </span>
                      </td>
                      <td className="py-3.5 px-3 text-right">
                        <button
                          onClick={() => onToggleUserStatus(user.id)}
                          className="px-2.5 py-1 text-[11px] rounded border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors"
                        >
                          {user.status === 'SUSPENDIDO' ? 'Reactivar' : 'Alternar Estado'}
                        </button>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* PESTAÑA 3: SIMULADOR DE ROL EN VIVO */}
      {activeTab === 'preview' && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-4 flex-1">
          {/* Panel Izquierdo: Conmutador de Rol (5 columnas) */}
          <div className="lg:col-span-5 flex flex-col gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md">
            <div>
              <h2 className="font-display text-base font-bold text-white uppercase tracking-wider">
                Simulador de Rol Activo para la Sesión
              </h2>
              <p className="text-xs text-slate-400 mt-1 leading-relaxed">
                Seleccione un rol para experimentar cómo el sistema restringe o habilita maniobras,
                disparos de parada de emergencia y aprobaciones en tiempo real en la consola SCADA.
              </p>
            </div>

            <div className="space-y-2.5 flex-1">
              {roles.map((r) => {
                const isActive = currentRoleId === r.id;
                return (
                  <button
                    key={r.id}
                    onClick={() => onSelectActiveRole(r.id)}
                    className={`w-full p-4 rounded-xl border text-left transition-all flex items-center justify-between ${
                      isActive
                        ? 'bg-purple-950/40 border-purple-500/70 shadow-[0_0_20px_rgba(168,85,247,0.2)] ring-1 ring-purple-500'
                        : 'bg-slate-950/70 border-slate-800 hover:border-slate-700 hover:bg-slate-900/50'
                    }`}
                  >
                    <div>
                      <div className="flex items-center gap-2 mb-1">
                        <span
                          className={`px-2 py-0.5 rounded text-[10px] font-mono-data font-bold uppercase border ${getRoleBadgeClasses(
                            r.color
                          )}`}
                        >
                          {r.code}
                        </span>
                        <span className="font-display text-sm font-bold text-white">
                          {r.name}
                        </span>
                      </div>
                      <p className="text-xs text-slate-400 line-clamp-1">{r.description}</p>
                    </div>

                    <div className="shrink-0 ml-3">
                      {isActive ? (
                        <div className="p-1.5 bg-purple-500 text-slate-950 rounded-full">
                          <Check className="w-4 h-4 stroke-[3]" />
                        </div>
                      ) : (
                        <div className="px-2.5 py-1 text-xs font-mono-data uppercase bg-slate-800 text-slate-400 rounded border border-slate-700">
                          Activar
                        </div>
                      )}
                    </div>
                  </button>
                );
              })}
            </div>

            <div className="p-3.5 bg-slate-950 rounded-xl border border-slate-800 text-xs font-mono-data text-slate-400 space-y-1">
              <div className="font-bold text-slate-300 flex items-center gap-1.5">
                <AlertCircle className="w-4 h-4 text-sky-400" />
                <span>Impacto en la Interfaz</span>
              </div>
              <p>
                Al cambiar el rol activo, la barra superior y los botones protegidos reflejarán las
                autorizaciones asignadas inmediatamente en las pantallas de Visión General, Pozos y Alarmas.
              </p>
            </div>
          </div>

          {/* Panel Derecho: Matriz de Acceso del Rol Seleccionado (7 columnas) */}
          <div className="lg:col-span-7 flex flex-col gap-3 bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md">
            <div className="border-b border-slate-800 pb-3 flex items-center justify-between">
              <div>
                <span className="text-xs font-mono-data uppercase text-slate-400 font-semibold block">
                  CAPACIDADES DE MANIOBRA EN TIEMPO REAL
                </span>
                <h3 className="font-display text-base font-bold text-white mt-0.5">
                  Privilegios para {activeRole.name} ({activeRole.code})
                </h3>
              </div>
              <SplitBadge prefix="PERMISOS" value={`${activeRole.permissions.length} / ${ALL_PERMISSIONS.length}`} severity="info" />
            </div>

            <div className="space-y-2 flex-1 overflow-y-auto pr-1">
              {ALL_PERMISSIONS.map((perm) => {
                const isGranted = activeRole.permissions.includes(perm.key);
                return (
                  <div
                    key={perm.key}
                    className={`p-3 rounded-lg border flex items-center justify-between text-xs font-mono-data ${
                      isGranted
                        ? 'bg-slate-950/80 border-emerald-500/30 text-slate-200'
                        : 'bg-slate-950/40 border-slate-800/80 text-slate-500 opacity-60'
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <div
                        className={`w-5 h-5 rounded flex items-center justify-center shrink-0 ${
                          isGranted
                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                            : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                        }`}
                      >
                        {isGranted ? <Unlock className="w-3 h-3" /> : <Lock className="w-3 h-3" />}
                      </div>
                      <div>
                        <div className="font-semibold">{perm.label}</div>
                        <div className="text-[10px] text-slate-500">{perm.category}</div>
                      </div>
                    </div>

                    <span
                      className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${
                        isGranted
                          ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                          : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                      }`}
                    >
                      {isGranted ? 'AUTORIZADO' : 'DENEGADO'}
                    </span>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      )}

      {/* PESTAÑA 4: BITÁCORA DE AUDITORÍA SIL-3 */}
      {activeTab === 'audit' && (
        <div className="flex flex-col gap-4 bg-slate-900/90 rounded-xl border border-slate-800/90 p-5 shadow-sm backdrop-blur-md flex-1">
          {/* Filtros de la Bitácora */}
          <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div className="flex items-center gap-2">
              <span className="text-xs font-mono-data uppercase text-slate-400">FILTRAR POR CATEGORÍA:</span>
              {['TODAS', 'SEGURIDAD', 'MANIOBRA', 'ALARMA', 'CONFIGURACIÓN'].map((cat) => (
                <button
                  key={cat}
                  onClick={() => setAuditFilterCategory(cat)}
                  className={`px-3 py-1 text-xs font-mono-data uppercase rounded transition-colors font-semibold ${
                    auditFilterCategory === cat
                      ? 'bg-slate-800 text-amber-400 border border-amber-500/40'
                      : 'text-slate-400 hover:text-white'
                  }`}
                >
                  {cat}
                </button>
              ))}
            </div>

            <div className="text-xs font-mono-data text-slate-400">
              CUMPLIMIENTO DE AUDITORÍA: <span className="text-emerald-400 font-bold">INMUTABLE SIL-3</span>
            </div>
          </div>

          {/* Lista de Registros */}
          <div className="overflow-x-auto flex-1">
            <table className="w-full text-left text-xs font-mono-data border-collapse">
              <thead>
                <tr className="border-b border-slate-800 text-slate-400 uppercase text-[10px]">
                  <th className="py-3 px-3">HORA</th>
                  <th className="py-3 px-3">OPERADOR / ID</th>
                  <th className="py-3 px-3">ROL</th>
                  <th className="py-3 px-3">ACCIÓN EJECUTADA</th>
                  <th className="py-3 px-3">CATEGORÍA</th>
                  <th className="py-3 px-3">RESULTADO</th>
                  <th className="py-3 px-3">DETALLES TÉCNICOS</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60">
                {filteredAuditLogs.map((log) => (
                  <tr key={log.id} className="hover:bg-slate-800/40 transition-colors">
                    <td className="py-3.5 px-3 font-semibold text-slate-400">{log.timestamp}</td>
                    <td className="py-3.5 px-3">
                      <span className="font-bold text-sky-400">{log.userCode}</span>{' '}
                      <span className="text-slate-300">({log.userName})</span>
                    </td>
                    <td className="py-3.5 px-3 text-slate-300">{log.roleName}</td>
                    <td className="py-3.5 px-3 font-semibold text-white">{log.action}</td>
                    <td className="py-3.5 px-3 text-slate-400">{log.category}</td>
                    <td className="py-3.5 px-3">
                      <span
                        className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase inline-flex items-center gap-1 ${
                          log.status === 'EXITOSO'
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                            : 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
                        }`}
                      >
                        {log.status}
                      </span>
                    </td>
                    <td className="py-3.5 px-3 text-slate-400 max-w-xs truncate">{log.details}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* MODAL: CREAR NUEVO ROL PERSONALIZADO */}
      {isCreateRoleOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 backdrop-blur-sm p-4">
          <div className="relative w-full max-w-xl bg-slate-900 rounded-2xl border border-slate-700/80 shadow-2xl p-6 text-slate-100 max-h-[90vh] flex flex-col">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
              <div className="flex items-center gap-2.5">
                <div className="p-2 bg-purple-500/10 border border-purple-500/30 rounded-lg text-purple-400">
                  <Shield className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-display text-sm font-bold uppercase tracking-wider text-white">
                    Crear Nuevo Rol Personalizado
                  </h3>
                  <p className="text-xs text-slate-400">
                    Defina nombre, código y asignación de permisos
                  </p>
                </div>
              </div>
              <button
                onClick={() => setIsCreateRoleOpen(false)}
                className="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreateRoleSubmit} className="flex-1 overflow-y-auto space-y-4 pr-1">
              <div>
                <label className="text-xs font-mono-data text-slate-400 block mb-1">
                  NOMBRE DEL ROL *
                </label>
                <input
                  type="text"
                  required
                  value={newRoleName}
                  onChange={(e) => setNewRoleName(e.target.value)}
                  placeholder="ej. Ingeniero de Procesos"
                  className="w-full bg-slate-950 border border-slate-800 focus:border-purple-500 px-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-xs font-mono-data text-slate-400 block mb-1">
                    CÓDIGO / TAG *
                  </label>
                  <input
                    type="text"
                    required
                    value={newRoleCode}
                    onChange={(e) => setNewRoleCode(e.target.value.toUpperCase())}
                    placeholder="ej. PROCESS_ENG"
                    className="w-full bg-slate-950 border border-slate-800 focus:border-purple-500 px-3 py-2 text-xs font-mono-data text-white uppercase rounded-lg outline-none"
                  />
                </div>

                <div>
                  <label className="text-xs font-mono-data text-slate-400 block mb-1">
                    COLOR DE IDENTIFICACIÓN
                  </label>
                  <select
                    value={newRoleColor}
                    onChange={(e) =>
                      setNewRoleColor(e.target.value as 'emerald' | 'amber' | 'purple' | 'sky' | 'rose')
                    }
                    className="w-full bg-slate-950 border border-slate-800 focus:border-purple-500 px-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none cursor-pointer"
                  >
                    <option value="sky">Azul Cielo (Sky)</option>
                    <option value="emerald">Verde Esmeralda (Emerald)</option>
                    <option value="amber">Ámbar / Naranja (Amber)</option>
                    <option value="purple">Púrpura (Purple)</option>
                    <option value="rose">Rosa / Carmesí (Rose)</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="text-xs font-mono-data text-slate-400 block mb-1">
                  DESCRIPCIÓN DEL ALCANCE OPERATIVO
                </label>
                <textarea
                  rows={2}
                  value={newRoleDesc}
                  onChange={(e) => setNewRoleDesc(e.target.value)}
                  placeholder="Descripción de responsabilidades y nivel de autorización..."
                  className="w-full bg-slate-950 border border-slate-800 focus:border-purple-500 px-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none resize-none"
                />
              </div>

              {/* Selector de Permisos Iniciales */}
              <div>
                <label className="text-xs font-mono-data text-slate-400 block mb-2 font-semibold">
                  SELECCIONAR PERMISOS INICIALES ({newRolePerms.length} seleccionados)
                </label>
                <div className="space-y-2 max-h-48 overflow-y-auto p-2 bg-slate-950 rounded-xl border border-slate-800">
                  {ALL_PERMISSIONS.map((perm) => {
                    const isChecked = newRolePerms.includes(perm.key);
                    return (
                      <label
                        key={perm.key}
                        className="flex items-center gap-2.5 p-1.5 rounded hover:bg-slate-900 cursor-pointer text-xs font-mono-data select-none"
                      >
                        <input
                          type="checkbox"
                          checked={isChecked}
                          onChange={() => {
                            if (isChecked) {
                              setNewRolePerms(newRolePerms.filter((k) => k !== perm.key));
                            } else {
                              setNewRolePerms([...newRolePerms, perm.key]);
                            }
                          }}
                          className="rounded border-slate-700 text-purple-500 focus:ring-0"
                        />
                        <span className={isChecked ? 'text-white' : 'text-slate-400'}>
                          {perm.label}
                        </span>
                      </label>
                    );
                  })}
                </div>
              </div>

              <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setIsCreateRoleOpen(false)}
                  className="px-4 py-2 text-xs font-mono-data uppercase bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 transition-colors"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 text-xs font-mono-data font-bold uppercase bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition-colors shadow-sm"
                >
                  Crear Rol
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: AÑADIR NUEVO USUARIO */}
      {isCreateUserOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/85 backdrop-blur-sm p-4">
          <div className="relative w-full max-w-md bg-slate-900 rounded-2xl border border-slate-700/80 shadow-2xl p-6 text-slate-100">
            <div className="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
              <div className="flex items-center gap-2.5">
                <div className="p-2 bg-emerald-500/10 border border-emerald-500/30 rounded-lg text-emerald-400">
                  <UserPlus className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-display text-sm font-bold uppercase tracking-wider text-white">
                    Registrar Nuevo Operador
                  </h3>
                  <p className="text-xs text-slate-400">
                    Aprovisionamiento de cuenta y asignación de rol
                  </p>
                </div>
              </div>
              <button
                onClick={() => setIsCreateUserOpen(false)}
                className="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleCreateUserSubmit} className="space-y-3.5">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-xs font-mono-data text-slate-400 block mb-1">
                    CÓDIGO DE OPERADOR *
                  </label>
                  <input
                    type="text"
                    required
                    value={newUserEmpCode}
                    onChange={(e) => setNewUserEmpCode(e.target.value.toUpperCase())}
                    placeholder="ej. OP-782"
                    className="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 px-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none"
                  />
                </div>

                <div>
                  <label className="text-xs font-mono-data text-slate-400 block mb-1">
                    ROL ASIGNADO *
                  </label>
                  <select
                    value={newUserRoleId}
                    onChange={(e) => setNewUserRoleId(e.target.value)}
                    className="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 px-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none cursor-pointer"
                  >
                    {roles.map((r) => (
                      <option key={r.id} value={r.id}>
                        {r.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div>
                <label className="text-xs font-mono-data text-slate-400 block mb-1">
                  NOMBRE COMPLETO *
                </label>
                <input
                  type="text"
                  required
                  value={newUserFullName}
                  onChange={(e) => setNewUserFullName(e.target.value)}
                  placeholder="ej. Ricardo Salcedo"
                  className="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 px-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none"
                />
              </div>

              <div>
                <label className="text-xs font-mono-data text-slate-400 block mb-1">
                  CORREO ELECTRÓNICO RTOC
                </label>
                <input
                  type="email"
                  value={newUserEmail}
                  onChange={(e) => setNewUserEmail(e.target.value)}
                  placeholder="ej. rsalcedo@rtoc-operations.com"
                  className="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 px-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none"
                />
              </div>

              <div>
                <label className="text-xs font-mono-data text-slate-400 block mb-1">
                  SECTOR OPERATIVO ASIGNADO
                </label>
                <input
                  type="text"
                  value={newUserSector}
                  onChange={(e) => setNewUserSector(e.target.value)}
                  placeholder="ej. Sector 04 · Compresión y Pad-04"
                  className="w-full bg-slate-950 border border-slate-800 focus:border-emerald-500 px-3 py-2 text-xs font-mono-data text-white rounded-lg outline-none"
                />
              </div>

              <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setIsCreateUserOpen(false)}
                  className="px-4 py-2 text-xs font-mono-data uppercase bg-slate-800 text-slate-300 rounded-lg hover:bg-slate-700 transition-colors"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 text-xs font-mono-data font-bold uppercase bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition-colors shadow-sm"
                >
                  Guardar Operador
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

/**
 * PROYECTO MAPAF - Cliente API de Integración con XAMPP (PHP + MySQL)
 * Conecta el frontend con el servidor Apache y la base de datos MySQL local (mapaf_db)
 */

import {
  TelemetryMetric,
  WellheadUnit,
  AlarmEvent,
  UserRole,
  SecurityUser,
  AuditLogEntry,
} from '../types/scada';

let CURRENT_API_BASE_URL = 'http://localhost/mapaf/api';

export const getApiBaseUrl = () => CURRENT_API_BASE_URL;

export interface XamppStatus {
  connected: boolean;
  phpVersion?: string;
  mysqlVersion?: string;
  database?: string;
  tablesCount?: number;
  tables?: string[];
  serverTime?: string;
  error?: string;
  endpointUsed?: string;
}

export interface SyncDataResponse {
  success: boolean;
  source: string;
  metrics: TelemetryMetric[];
  wellheads: WellheadUnit[];
  alarms: AlarmEvent[];
  roles: UserRole[];
  users: SecurityUser[];
  auditLogs: AuditLogEntry[];
  systemConfig: {
    esdTriggered: boolean;
    esdTriggerTime: string;
    flowState: 'nominal' | 'throttled' | 'tripped';
    productionRate: number;
    plantName: string;
    senaFicha: string;
  };
}

export const mapafApi = {
  /**
   * Verifica el estado de salud del servidor XAMPP, PHP y MySQL.
   * Auto-detecta si el servidor está en puerto 80 (Apache) o puerto 8080 (PHP directo).
   */
  async checkStatus(): Promise<XamppStatus> {
    const candidateUrls = [
      'http://localhost/mapaf/api',
      'http://localhost:8080/api',
      'http://localhost:8080/mapaf/api',
    ];

    for (const baseUrl of candidateUrls) {
      try {
        const res = await fetch(`${baseUrl}/status.php`, {
          method: 'GET',
          headers: { Accept: 'application/json' },
          signal: AbortSignal.timeout(1500),
        });

        if (res.ok) {
          const data = await res.json();
          CURRENT_API_BASE_URL = baseUrl;
          return {
            connected: data.mysql?.connected ?? false,
            phpVersion: data.php?.version,
            mysqlVersion: data.mysql?.version,
            database: data.mysql?.database,
            tablesCount: data.mysql?.tables_count,
            tables: data.mysql?.tables,
            serverTime: data.server_time,
            error: data.mysql?.error,
            endpointUsed: baseUrl,
          };
        }
      } catch {
        // Intentar siguiente URL
      }
    }

    return {
      connected: false,
      error: 'No se pudo conectar ni a http://localhost/mapaf/api/ (Apache) ni a http://localhost:8080/api/ (PHP directo).',
    };
  },

  /**
   * Carga todo el estado sincronizado de telemetría, pozos, alarmas, usuarios y auditoría
   */
  async fetchSyncData(): Promise<SyncDataResponse | null> {
    try {
      const res = await fetch(`${CURRENT_API_BASE_URL}/sync.php`, {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });
      if (!res.ok) return null;
      const data = await res.json();
      if (data.success) {
        return data as SyncDataResponse;
      }
      return null;
    } catch {
      return null;
    }
  },

  /**
   * Actualiza el estado de un cabezal de pozo en MySQL (apertura de choke, válvulas, ESP)
   */
  async updateWellhead(params: {
    id: string;
    chokeOpening?: number;
    status?: string;
    ssvOpen?: boolean;
    annulusBleedOpen?: boolean;
    chemicalInjection?: boolean;
    espFrequency?: number;
    actionDescription?: string;
    userName?: string;
    roleName?: string;
  }): Promise<boolean> {
    try {
      const res = await fetch(`${CURRENT_API_BASE_URL}/wellheads.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(params),
      });
      const data = await res.json();
      return !!data.success;
    } catch {
      return false;
    }
  },

  /**
   * Reconoce una alarma individual en MySQL
   */
  async acknowledgeAlarm(id: string, acknowledgedBy: string): Promise<boolean> {
    try {
      const res = await fetch(`${CURRENT_API_BASE_URL}/alarms.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, acknowledgedBy }),
      });
      const data = await res.json();
      return !!data.success;
    } catch {
      return false;
    }
  },

  /**
   * Reconoce masivamente todas las alarmas en MySQL
   */
  async acknowledgeAllAlarms(acknowledgedBy: string): Promise<boolean> {
    try {
      const res = await fetch(`${CURRENT_API_BASE_URL}/alarms.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'ack_all', acknowledgedBy }),
      });
      const data = await res.json();
      return !!data.success;
    } catch {
      return false;
    }
  },

  /**
   * Dispara o restablece la Parada de Emergencia ESD-1 en MySQL
   */
  async setEsdState(action: 'trip' | 'reset', userName: string): Promise<boolean> {
    try {
      const res = await fetch(`${CURRENT_API_BASE_URL}/esd.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, userName }),
      });
      const data = await res.json();
      return !!data.success;
    } catch {
      return false;
    }
  },

  /**
   * Inserta un nuevo registro en la bitácora de auditoría en MySQL
   */
  async addAuditLog(entry: {
    id?: string;
    timestamp?: string;
    userCode?: string;
    userName: string;
    roleName: string;
    action: string;
    category: 'SEGURIDAD' | 'MANIOBRA' | 'ALARMA' | 'CONFIGURACIÓN';
    status: 'EXITOSO' | 'RECHAZADO' | 'ADVERTENCIA';
    details: string;
  }): Promise<boolean> {
    try {
      const res = await fetch(`${CURRENT_API_BASE_URL}/audit.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(entry),
      });
      const data = await res.json();
      return !!data.success;
    } catch {
      return false;
    }
  },

  /**
   * Actualiza los permisos de un rol en MySQL
   */
  async saveRolePermissions(roleId: string, permissions: string[]): Promise<boolean> {
    try {
      const res = await fetch(`${CURRENT_API_BASE_URL}/roles.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: roleId, permissions }),
      });
      const data = await res.json();
      return !!data.success;
    } catch {
      return false;
    }
  },

  /**
   * Crea o actualiza un usuario en MySQL
   */
  async saveUser(user: SecurityUser): Promise<boolean> {
    try {
      const res = await fetch(`${CURRENT_API_BASE_URL}/users.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(user),
      });
      const data = await res.json();
      return !!data.success;
    } catch {
      return false;
    }
  },
};

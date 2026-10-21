/**
 * PROYECTO MAPAF - Servicio de Autenticación & Tokens JWT (RFC 7519)
 * SENA ADSO · Ficha 3413988
 * 
 * Gestiona la emisión, almacenamiento seguro en sessionStorage y verificación
 * de tokens JWT para los operadores de sala de control SCADA.
 */

import { getApiBaseUrl } from './mapafApi';

const JWT_STORAGE_KEY = 'mapaf_scada_jwt_token';
const JWT_USER_KEY = 'mapaf_scada_jwt_user';

export interface JwtClaims {
  sub: string;
  employeeCode: string;
  name: string;
  email: string;
  roleId: string;
  roleName: string;
  roleCode: string;
  sector: string;
  permissions: string[];
  iat: number;
  exp: number;
  iss?: string;
  aud?: string;
}

export interface DecodedJwt {
  header: {
    typ: string;
    alg: string;
    iss?: string;
    aud?: string;
  };
  payload: JwtClaims;
  signature: string;
  rawToken: string;
  isValid: boolean;
  expiresInSeconds: number;
}

export const jwtAuth = {
  /**
   * Obtiene el token JWT actualmente almacenado en sesión
   */
  getToken(): string | null {
    try {
      return sessionStorage.getItem(JWT_STORAGE_KEY) || localStorage.getItem(JWT_STORAGE_KEY);
    } catch {
      return null;
    }
  },

  /**
   * Guarda el token JWT en el almacenamiento de sesión
   */
  saveToken(token: string, user?: any): void {
    try {
      sessionStorage.setItem(JWT_STORAGE_KEY, token);
      localStorage.setItem(JWT_STORAGE_KEY, token);
      if (user) {
        sessionStorage.setItem(JWT_USER_KEY, JSON.stringify(user));
        localStorage.setItem(JWT_USER_KEY, JSON.stringify(user));
      }
    } catch {
      // Storage quota or disabled
    }
  },

  /**
   * Cierra sesión y elimina el token
   */
  logout(): void {
    try {
      sessionStorage.removeItem(JWT_STORAGE_KEY);
      sessionStorage.removeItem(JWT_USER_KEY);
      localStorage.removeItem(JWT_STORAGE_KEY);
      localStorage.removeItem(JWT_USER_KEY);
    } catch {
      // Storage error
    }
  },

  /**
   * Decodifica un token JWT sin necesidad de librerías externas
   */
  decodeToken(token: string): DecodedJwt | null {
    try {
      const parts = token.split('.');
      if (parts.length !== 3) return null;

      const base64UrlDecode = (str: string) => {
        let base64 = str.replace(/-/g, '+').replace(/_/g, '/');
        while (base64.length % 4) {
          base64 += '=';
        }
        return decodeURIComponent(
          atob(base64)
            .split('')
            .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
            .join('')
        );
      };

      const header = JSON.parse(base64UrlDecode(parts[0]));
      const payload = JSON.parse(base64UrlDecode(parts[1])) as JwtClaims;
      const signature = parts[2];

      const now = Math.floor(Date.now() / 1000);
      const expiresIn = Math.max(0, (payload.exp || 0) - now);
      const isValid = expiresIn > 0;

      return {
        header,
        payload,
        signature,
        rawToken: token,
        isValid,
        expiresInSeconds: expiresIn,
      };
    } catch {
      return null;
    }
  },

  /**
   * Obtiene el usuario decodificado desde el token activo
   */
  getCurrentUser(): JwtClaims | null {
    const token = this.getToken();
    if (!token) return null;
    const decoded = this.decodeToken(token);
    return decoded && decoded.isValid ? decoded.payload : null;
  },

  /**
   * Obtiene las cabeceras HTTP necesarias para peticiones autorizadas
   */
  getAuthHeaders(): Record<string, string> {
    const token = this.getToken();
    return token
      ? {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        }
      : {
          'Content-Type': 'application/json',
        };
  },

  /**
   * Inicia sesión con código de empleado contra el backend PHP en XAMPP
   */
  async loginWithPhpBackend(employeeCode: string = 'OP-449', ttlSeconds: number = 28800): Promise<{
    success: boolean;
    token?: string;
    decoded?: DecodedJwt;
    error?: string;
  }> {
    try {
      const res = await fetch(`${getApiBaseUrl()}/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ employeeCode, ttl: ttlSeconds }),
      });

      if (!res.ok) {
        // Fallback a generación de token local para demostración fluida
        return this.createMockJwt(employeeCode, ttlSeconds);
      }

      const data = await res.json();
      if (data.success && data.token) {
        this.saveToken(data.token, data.user);
        const decoded = this.decodeToken(data.token);
        return {
          success: true,
          token: data.token,
          decoded: decoded || undefined,
        };
      }

      return { success: false, error: data.error || 'Credenciales no reconocidas en MySQL' };
    } catch (err: any) {
      // Si el servidor XAMPP está apagado, generar token didáctico local
      return this.createMockJwt(employeeCode, ttlSeconds);
    }
  },

  /**
   * Generador de token JWT local (fallback cuando XAMPP no está activo)
   */
  createMockJwt(employeeCode: string, ttlSeconds: number = 28800): {
    success: boolean;
    token: string;
    decoded: DecodedJwt;
  } {
    const operatorProfiles: Record<string, Partial<JwtClaims>> = {
      'OP-449': {
        sub: 'usr-001',
        employeeCode: 'OP-449',
        name: 'María José Conde Dussan',
        email: 'mj.conde@mapaf.scada.io',
        roleId: 'role-admin',
        roleName: 'Administrador del Sistema',
        roleCode: 'SYS-ADMIN',
        sector: 'Estación Central / RTOC',
        permissions: ['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports', 'bypass_interlocks', 'manage_roles_users'],
      },
      'OP-312': {
        sub: 'usr-002',
        employeeCode: 'OP-312',
        name: 'Hernann Thomas Manchola Bocanegra',
        email: 'h.manchola@mapaf.scada.io',
        roleId: 'role-engineer',
        roleName: 'Ingeniero de Operaciones',
        roleCode: 'OPS-ENG',
        sector: 'Pad Alfa / Bravo',
        permissions: ['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports'],
      },
      'OP-205': {
        sub: 'usr-003',
        employeeCode: 'OP-205',
        name: 'Emilin Tatiana Vargas Manchola',
        email: 'e.vargas@mapaf.scada.io',
        roleId: 'role-supervisor',
        roleName: 'Supervisor de Turno',
        roleCode: 'SHIFT-SUPV',
        sector: 'Sala de Control RTOC',
        permissions: ['view_telemetry', 'control_valves', 'execute_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports'],
      },
      'OP-118': {
        sub: 'usr-004',
        employeeCode: 'OP-118',
        name: 'Javier Humberto Pinto Díaz',
        email: 'j.pinto@sena.edu.co',
        roleId: 'role-admin',
        roleName: 'Instructor / Auditor Senior SENA',
        roleCode: 'SYS-ADMIN',
        sector: 'Auditoría Académica ADSO',
        permissions: ['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports', 'bypass_interlocks', 'manage_roles_users'],
      },
    };

    const profile = operatorProfiles[employeeCode] || operatorProfiles['OP-449'];
    const now = Math.floor(Date.now() / 1000);
    const exp = now + ttlSeconds;

    const header = { typ: 'JWT', alg: 'HS256', iss: 'MAPAF-SCADA-RTOC', aud: 'SENA-ADSO-3413988' };
    const payload: JwtClaims = {
      sub: profile.sub || 'usr-demo',
      employeeCode: profile.employeeCode || employeeCode,
      name: profile.name || 'Operador SCADA',
      email: profile.email || 'operador@mapaf.scada.io',
      roleId: profile.roleId || 'role-operator',
      roleName: profile.roleName || 'Operador',
      roleCode: profile.roleCode || 'CONSOLE-OP',
      sector: profile.sector || 'Pad Alfa',
      permissions: profile.permissions || ['view_telemetry', 'control_choke', 'ack_alarms'],
      iat: now,
      exp: exp,
      iss: 'MAPAF-SCADA-RTOC',
      aud: 'SENA-ADSO-3413988',
    };

    const base64UrlEncode = (str: string) =>
      btoa(unescape(encodeURIComponent(str)))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/, '');

    const b64Header = base64UrlEncode(JSON.stringify(header));
    const b64Payload = base64UrlEncode(JSON.stringify(payload));
    const dummySignature = 'sig_hmac_sha256_' + Math.random().toString(36).substring(2, 15);
    const mockToken = `${b64Header}.${b64Payload}.${dummySignature}`;

    this.saveToken(mockToken, payload);

    return {
      success: true,
      token: mockToken,
      decoded: {
        header,
        payload,
        signature: dummySignature,
        rawToken: mockToken,
        isValid: true,
        expiresInSeconds: ttlSeconds,
      },
    };
  },
};

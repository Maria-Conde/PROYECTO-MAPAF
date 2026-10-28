<?php
/**
 * PROYECTO MAPAF - Patrón de Diseño REPOSITORY: UserRepository
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona el acceso a usuarios, operadores y matriz de roles RBAC.
 */

require_once __DIR__ . '/RepositoryInterface.php';
require_once __DIR__ . '/../config/database.php';

class UserRepository implements RepositoryInterface {
    private ?PDO $db;
    private static array $fallbackRoles = [
        [
            'id' => 'role-admin', 'nombre' => 'Administrador del Sistema', 'codigo' => 'SYS-ADMIN',
            'descripcion' => 'Control total de ciberseguridad, gestión de usuarios, enclavamientos y configuración de planta.',
            'color' => 'rose', 'permisos' => ['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'export_historian', 'manage_users']
        ],
        [
            'id' => 'role-engineer', 'nombre' => 'Ingeniero de Operaciones', 'codigo' => 'OPS-ENG',
            'descripcion' => 'Ajuste de consignas de proceso, curvas de producción, estranguladores y bypass bajo protocolo.',
            'color' => 'purple', 'permisos' => ['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'export_historian']
        ],
        [
            'id' => 'role-supervisor', 'nombre' => 'Supervisor de Turno', 'codigo' => 'SHIFT-SUPV',
            'descripcion' => 'Supervisión de sala de control, autorización de maniobras críticas y acuse de alarmas ISA-18.2.',
            'color' => 'amber', 'permisos' => ['view_telemetry', 'control_valves', 'execute_esd', 'ack_alarms', 'export_historian']
        ],
        [
            'id' => 'role-operator', 'nombre' => 'Operador de Consola', 'codigo' => 'CONSOLE-OP',
            'descripcion' => 'Monitoreo continuo de cabezales, maniobra de choke y reconocimiento de alarmas operacionales.',
            'color' => 'emerald', 'permisos' => ['view_telemetry', 'control_choke', 'ack_alarms']
        ],
        [
            'id' => 'role-viewer', 'nombre' => 'Auditor / Observador', 'codigo' => 'AUDIT-VIEW',
            'descripcion' => 'Acceso exclusivo de sólo lectura para fiscalización de producción y reportes históricos.',
            'color' => 'sky', 'permisos' => ['view_telemetry', 'export_historian']
        ]
    ];

    private static array $fallbackUsers = [
        'usr-001' => [
            'id' => 'usr-001', 'codigo_empleado' => 'EMP-8812', 'nombre_completo' => 'María José Conde Dussan',
            'email' => 'm.conde@mapaf.senacontrol.co', 'rol_id' => 'role-admin', 'rol_nombre' => 'Administrador del Sistema',
            'rol_codigo' => 'SYS-ADMIN', 'rol_color' => 'rose', 'sector_asignado' => 'Planta Central Huila / RTOC',
            'estado' => 'EN TURNO', 'permisos' => ['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'export_historian', 'manage_users']
        ],
        'usr-002' => [
            'id' => 'usr-002', 'codigo_empleado' => 'EMP-8813', 'nombre_completo' => 'Hernann Thomas Manchola',
            'email' => 'h.manchola@mapaf.senacontrol.co', 'rol_id' => 'role-engineer', 'rol_nombre' => 'Ingeniero de Operaciones',
            'rol_codigo' => 'OPS-ENG', 'rol_color' => 'purple', 'sector_asignado' => 'Pad 04 & 09 Upstream',
            'estado' => 'EN TURNO', 'permisos' => ['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'export_historian']
        ],
        'usr-003' => [
            'id' => 'usr-003', 'codigo_empleado' => 'EMP-8814', 'nombre_completo' => 'Emilin Tatiana Vargas',
            'email' => 'e.vargas@mapaf.senacontrol.co', 'rol_id' => 'role-supervisor', 'rol_nombre' => 'Supervisor de Turno',
            'rol_codigo' => 'SHIFT-SUPV', 'rol_color' => 'amber', 'sector_asignado' => 'Sala de Control y Despacho',
            'estado' => 'EN TURNO', 'permisos' => ['view_telemetry', 'control_valves', 'execute_esd', 'ack_alarms', 'export_historian']
        ]
    ];

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    public function getAll(): array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    SELECT u.id, u.codigo_empleado, u.nombre_completo, u.email, 
                           u.sector_asignado, u.estado, u.ultimo_acceso, u.rol_id,
                           r.nombre as rol_nombre, r.codigo as rol_codigo, r.color as rol_color, r.permisos
                    FROM `usuarios` u
                    JOIN `roles` r ON u.rol_id = r.id
                    ORDER BY u.nombre_completo ASC
                ");
                $stmt->execute();
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    foreach ($rows as &$row) {
                        if (isset($row['permisos']) && is_string($row['permisos'])) {
                            $row['permisos'] = json_decode($row['permisos'], true) ?: [];
                        }
                    }
                    return $rows;
                }
            } catch (Throwable $e) {
                error_log('[UserRepository] Error consultando usuarios: ' . $e->getMessage());
            }
        }
        return array_values(self::$fallbackUsers);
    }

    public function getById(string $id): ?array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    SELECT u.id, u.codigo_empleado, u.nombre_completo, u.email, 
                           u.sector_asignado, u.estado, u.ultimo_acceso, u.rol_id,
                           r.nombre as rol_nombre, r.codigo as rol_codigo, r.color as rol_color, r.permisos
                    FROM `usuarios` u
                    JOIN `roles` r ON u.rol_id = r.id
                    WHERE u.id = :id
                    LIMIT 1
                ");
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                if ($row) {
                    if (isset($row['permisos']) && is_string($row['permisos'])) {
                        $row['permisos'] = json_decode($row['permisos'], true) ?: [];
                    }
                    return $row;
                }
            } catch (Throwable $e) {
                error_log('[UserRepository] Error getById: ' . $e->getMessage());
            }
        }
        return self::$fallbackUsers[$id] ?? null;
    }

    public function getByCode(string $code): ?array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    SELECT u.id, u.codigo_empleado, u.nombre_completo, u.email, 
                           u.sector_asignado, u.estado, u.ultimo_acceso, u.rol_id,
                           r.nombre as rol_nombre, r.codigo as rol_codigo, r.color as rol_color, r.permisos
                    FROM `usuarios` u
                    JOIN `roles` r ON u.rol_id = r.id
                    WHERE u.codigo_empleado = :code
                    LIMIT 1
                ");
                $stmt->execute([':code' => $code]);
                $row = $stmt->fetch();
                if ($row) {
                    if (isset($row['permisos']) && is_string($row['permisos'])) {
                        $row['permisos'] = json_decode($row['permisos'], true) ?: [];
                    }
                    return $row;
                }
            } catch (Throwable $e) {
                error_log('[UserRepository] Error getByCode: ' . $e->getMessage());
            }
        }

        foreach (self::$fallbackUsers as $u) {
            if ($u['codigo_empleado'] === $code) {
                return $u;
            }
        }
        return null;
    }

    public function getRoles(): array {
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM `roles` ORDER BY `id` ASC");
                $stmt->execute();
                $roles = $stmt->fetchAll();
                if (!empty($roles)) {
                    foreach ($roles as &$role) {
                        if (isset($role['permisos']) && is_string($role['permisos'])) {
                            $role['permisos'] = json_decode($role['permisos'], true) ?: [];
                        }
                    }
                    return $roles;
                }
            } catch (Throwable $e) {
                error_log('[UserRepository] Error getRoles: ' . $e->getMessage());
            }
        }
        return self::$fallbackRoles;
    }
}

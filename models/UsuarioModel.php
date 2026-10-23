<?php
/**
 * PROYECTO MAPAF - Modelo de Dominio: Usuarios y Control de Acceso RBAC
 * SENA ADSO - Ficha 3413988
 * 
 * Gestiona operadores, ingenieros y roles de acceso en MySQL.
 */

require_once __DIR__ . '/../config/database.php';

class UsuarioModel {
    private ?PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    /**
     * Obtiene todos los usuarios con sus roles asociados
     */
    public function getAll(): array {
        if (!$this->db) {
            return [];
        }

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

        foreach ($rows as &$row) {
            if (isset($row['permisos']) && is_string($row['permisos'])) {
                $row['permisos'] = json_decode($row['permisos'], true) ?: [];
            }
        }
        return $rows;
    }

    /**
     * Busca un usuario por su código de empleado (ej. EMP-8812)
     */
    public function getByCode(string $code): ?array {
        if (!$this->db) {
            return null;
        }

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

        if ($row && isset($row['permisos']) && is_string($row['permisos'])) {
            $row['permisos'] = json_decode($row['permisos'], true) ?: [];
        }
        return $row ?: null;
    }

    /**
     * Obtiene la matriz de roles y sus permisos
     */
    public function getRoles(): array {
        if (!$this->db) {
            return [];
        }

        $stmt = $this->db->prepare("SELECT * FROM `roles` ORDER BY `id` ASC");
        $stmt->execute();
        $roles = $stmt->fetchAll();

        foreach ($roles as &$role) {
            if (isset($role['permisos']) && is_string($role['permisos'])) {
                $role['permisos'] = json_decode($role['permisos'], true) ?: [];
            }
        }
        return $roles;
    }
}

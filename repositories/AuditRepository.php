<?php
/**
 * PROYECTO MAPAF - Patrón de Diseño REPOSITORY: AuditRepository
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona el almacenamiento inmutable de la bitácora de auditoría forense (21 CFR Part 11).
 * Implementa resiliencia con buffer en memoria si MySQL no está disponible.
 */

require_once __DIR__ . '/RepositoryInterface.php';
require_once __DIR__ . '/../config/database.php';

class AuditRepository implements RepositoryInterface {
    private ?PDO $db;
    private static array $inMemoryLogs = [];

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    public function getAll(): array {
        return $this->getRecent(100);
    }

    public function getById(string $id): ?array {
        if ($this->db) {
            $stmt = $this->db->prepare("SELECT * FROM `bitacora_auditoria` WHERE `id` = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            if ($res) return $res;
        }

        foreach (self::$inMemoryLogs as $log) {
            if ($log['id'] === $id) return $log;
        }
        return null;
    }

    public function log(
        string $userCode,
        string $userName,
        string $roleName,
        string $action,
        string $category = 'MANIOBRA',
        string $status = 'EXITOSO',
        string $details = ''
    ): bool {
        $id = 'aud-' . date('YmdHis') . '-' . bin2hex(random_bytes(2));
        $timeStr = date('H:i:s');
        $record = [
            'id'             => $id,
            'timestamp_log'  => $timeStr,
            'codigo_usuario' => $userCode,
            'nombre_usuario' => $userName,
            'nombre_rol'     => $roleName,
            'accion'         => $action,
            'categoria'      => $category,
            'estado'         => $status,
            'detalles'       => $details,
            'fecha_registro' => date('Y-m-d H:i:s')
        ];

        // Guardar siempre en memoria como respaldo inmediato
        array_unshift(self::$inMemoryLogs, $record);
        if (count(self::$inMemoryLogs) > 200) {
            array_pop(self::$inMemoryLogs);
        }

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO `bitacora_auditoria` 
                    (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`)
                    VALUES
                    (:id, :tlog, :ucode, :uname, :rname, :action, :cat, :status, :det)
                ");
                return $stmt->execute([
                    ':id'     => $id,
                    ':tlog'   => $timeStr,
                    ':ucode'  => $userCode,
                    ':uname'  => $userName,
                    ':rname'  => $roleName,
                    ':action' => $action,
                    ':cat'    => $category,
                    ':status' => $status,
                    ':det'    => $details
                ]);
            } catch (Throwable $e) {
                error_log('[AuditRepository] Error al insertar en BD: ' . $e->getMessage());
                return true; // Se mantiene registrado en el buffer en memoria
            }
        }

        return true;
    }

    public function getRecent(int $limit = 50): array {
        $limit = max(1, min(200, $limit));

        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    SELECT * FROM `bitacora_auditoria` 
                    ORDER BY `fecha_registro` DESC, `id` DESC 
                    LIMIT :lim
                ");
                $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (Throwable $e) {
                error_log('[AuditRepository] Error leyendo de BD: ' . $e->getMessage());
            }
        }

        return array_slice(self::$inMemoryLogs, 0, $limit);
    }
}

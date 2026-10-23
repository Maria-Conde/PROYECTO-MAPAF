<?php
/**
 * PROYECTO MAPAF - Modelo de Dominio: Bitácora de Auditoría Forense (21 CFR Part 11)
 * SENA ADSO - Ficha 3413988
 * 
 * Registro inmutable de cada acción, consigna o cambio operativo con trazabilidad completa.
 */

require_once __DIR__ . '/../config/database.php';

class AuditoriaModel {
    private ?PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    /**
     * Inserta un registro inmutable en la bitácora
     */
    public function log(
        string $userCode,
        string $userName,
        string $roleName,
        string $action,
        string $category = 'MANIOBRA',
        string $status = 'EXITOSO',
        string $details = ''
    ): bool {
        if (!$this->db) {
            return false;
        }

        $id = 'aud-' . substr(uniqid(), -7);
        $timeStr = date('H:i:s');

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
    }

    /**
     * Consulta los registros más recientes de la bitácora
     */
    public function getRecent(int $limit = 50): array {
        if (!$this->db) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $stmt = $this->db->prepare("
            SELECT * FROM `bitacora_auditoria` 
            ORDER BY `fecha_registro` DESC, `id` DESC 
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

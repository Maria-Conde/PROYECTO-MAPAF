<?php
/**
 * PROYECTO MAPAF - Modelo de Dominio: Alarmas Industriales (ANSI/ISA-18.2)
 * SENA ADSO - Ficha 3413988
 * 
 * Gestiona el ciclo de vida de alarmas y mitiga el antipatrón 'Blind Alarm ACK'
 * exigiendo justificación técnica obligatoria y registro de operador.
 */

require_once __DIR__ . '/../config/database.php';

class AlarmaModel {
    private ?PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    /**
     * Lista las alarmas del sistema
     */
    public function getAll(bool $unacknowledgedOnly = false): array {
        if (!$this->db) {
            return [];
        }

        $sql = "SELECT a.*, p.nombre as nombre_pozo 
                FROM `alarmas` a
                LEFT JOIN `pozos` p ON a.pozo_id = p.id ";
        
        if ($unacknowledgedOnly) {
            $sql .= "WHERE a.`reconocida` = 0 ";
        }
        $sql .= "ORDER BY (CASE a.severidad WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 WHEN 'advisory' THEN 3 ELSE 4 END) ASC, a.fecha_registro DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una alarma específica por ID
     */
    public function getById(string $id): ?array {
        if (!$this->db) {
            return null;
        }
        $stmt = $this->db->prepare("SELECT * FROM `alarmas` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Acuse trazable de alarma (ANSI/ISA-18.2)
     * Bloquea el reconocimiento ciego si falta el operador o la justificación.
     */
    public function acknowledge(string $id, string $operatorName, string $reason): array {
        if (!$this->db) {
            return ['success' => false, 'error' => 'Base de datos no disponible'];
        }

        $operatorName = trim($operatorName);
        $reason = trim($reason);

        // Mitigación estricta de antipatrón Blind Alarm Acknowledgment
        if (empty($operatorName)) {
            return [
                'success' => false,
                'error'   => 'Regla ISA-18.2 violada: Debe especificarse el nombre del operador que reconoce la alarma.'
            ];
        }

        if (strlen($reason) < 8) {
            return [
                'success' => false,
                'error'   => 'Regla ISA-18.2 violada: La justificación técnica debe contener al menos 8 caracteres explicativos.'
            ];
        }

        $now = date('H:i:s');
        $stmt = $this->db->prepare("
            UPDATE `alarmas`
            SET `reconocida` = 1,
                `reconocida_por` = :operator,
                `ack_timestamp` = :ack_time,
                `causa_disparo` = :reason
            WHERE `id` = :id
        ");

        $executed = $stmt->execute([
            ':operator' => $operatorName,
            ':ack_time' => $now,
            ':reason'   => $reason,
            ':id'       => $id
        ]);

        return [
            'success' => $executed,
            'ack_timestamp' => $now,
            'operator' => $operatorName
        ];
    }

    /**
     * Cuenta alarmas pendientes de reconocimiento por severidad
     */
    public function getPendingCounts(): array {
        if (!$this->db) {
            return ['critical' => 0, 'warning' => 0, 'advisory' => 0, 'total' => 0];
        }

        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN `severidad` = 'critical' AND `reconocida` = 0 THEN 1 ELSE 0 END) as critical_count,
                SUM(CASE WHEN `severidad` = 'warning' AND `reconocida` = 0 THEN 1 ELSE 0 END) as warning_count,
                SUM(CASE WHEN `severidad` = 'advisory' AND `reconocida` = 0 THEN 1 ELSE 0 END) as advisory_count,
                SUM(CASE WHEN `reconocida` = 0 THEN 1 ELSE 0 END) as total_pending
            FROM `alarmas`
        ");
        $data = $stmt->fetch();

        return [
            'critical' => (int)($data['critical_count'] ?? 0),
            'warning'  => (int)($data['warning_count'] ?? 0),
            'advisory' => (int)($data['advisory_count'] ?? 0),
            'total'    => (int)($data['total_pending'] ?? 0)
        ];
    }
}

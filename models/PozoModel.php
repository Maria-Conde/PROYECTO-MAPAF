<?php
/**
 * PROYECTO MAPAF - Modelo de Dominio: Pozos Petroleros y Cabezales (API 6A)
 * SENA ADSO - Ficha 3413988
 * 
 * Gestiona la persistencia de cabezales de pozo mediante sentencias preparadas PDO.
 */

require_once __DIR__ . '/../config/database.php';

class PozoModel {
    private ?PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: getDb();
    }

    /**
     * Obtiene todos los cabezales de pozo registrados
     */
    public function getAll(): array {
        if (!$this->db) {
            return [];
        }
        $stmt = $this->db->prepare("SELECT * FROM `pozos` ORDER BY `id` ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Busca un pozo por su identificador único
     */
    public function getById(string $id): ?array {
        if (!$this->db) {
            return null;
        }
        $stmt = $this->db->prepare("SELECT * FROM `pozos` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Actualiza la apertura del estrangulador (Choke) de forma segura
     */
    public function setChoke(string $id, float $chokeOpening): bool {
        if (!$this->db) {
            return false;
        }
        $clamped = max(0.0, min(100.0, $chokeOpening));
        $stmt = $this->db->prepare("
            UPDATE `pozos` 
            SET `apertura_choke` = :choke, `objetivo_choke` = :target 
            WHERE `id` = :id
        ");
        return $stmt->execute([
            ':choke'  => $clamped,
            ':target' => $clamped,
            ':id'     => $id
        ]);
    }

    /**
     * Conmuta el estado de la Válvula de Seguridad de Superficie (SSV)
     */
    public function setSsvValve(string $id, bool $isOpen): bool {
        if (!$this->db) {
            return false;
        }
        $stmt = $this->db->prepare("
            UPDATE `pozos` 
            SET `valvula_ssv_abierta` = :ssv 
            WHERE `id` = :id
        ");
        return $stmt->execute([
            ':ssv' => $isOpen ? 1 : 0,
            ':id'  => $id
        ]);
    }

    /**
     * Actualiza el estado operativo del pozo (EN LÍNEA, ESTRANGULADO, DISPARADO, MANTENIMIENTO)
     */
    public function setStatus(string $id, string $status): bool {
        if (!$this->db) {
            return false;
        }
        $allowed = ['EN LÍNEA', 'ESTRANGULADO', 'DISPARADO', 'MANTENIMIENTO'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE `pozos` SET `estado` = :status WHERE `id` = :id");
        return $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
    }

    /**
     * Rearma de forma segura un pozo tras disparo o emergencia
     */
    public function resetWellhead(string $id): bool {
        if (!$this->db) {
            return false;
        }
        $stmt = $this->db->prepare("
            UPDATE `pozos` 
            SET `estado` = 'EN LÍNEA',
                `valvula_ssv_abierta` = 1,
                `apertura_choke` = 15.00,
                `objetivo_choke` = 15.00
            WHERE `id` = :id
        ");
        return $stmt->execute([':id' => $id]);
    }
}

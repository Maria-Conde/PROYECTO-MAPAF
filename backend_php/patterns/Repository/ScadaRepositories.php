<?php
/**
 * PROYECTO MAPAF - Patrón Arquitectónico: REPOSITORY (Repositorio)
 * SENA ADSO · Ficha 3413988
 * 
 * Desacopla la lógica de negocio y los controladores de la capa de acceso a datos SQL (PDO).
 * Proporciona una colección en memoria simulada para las entidades de dominio:
 * Pozos, Telemetría, Alarmas ISA-18.2 y Bitácora de Auditoría.
 */

namespace Mapaf\Patterns\Repository;

use PDO;
use Mapaf\Patterns\Singleton\DatabaseSingleton;

class WellheadRepository {
    private ?PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: DatabaseSingleton::getInstance()->getConnection();
    }

    public function findAll(): array {
        if (!$this->db) return [];
        $stmt = $this->db->query("SELECT * FROM `pozos` ORDER BY `id` ASC");
        return $stmt->fetchAll();
    }

    public function findById(string $id): ?array {
        if (!$this->db) return null;
        $stmt = $this->db->prepare("SELECT * FROM `pozos` WHERE `id` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateChoke(string $id, float $chokeOpening): bool {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("UPDATE `pozos` SET `apertura_choke` = :choke WHERE `id` = :id");
        return $stmt->execute([':choke' => $chokeOpening, ':id' => $id]);
    }

    public function updateSsvValve(string $id, bool $isOpen): bool {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("UPDATE `pozos` SET `valvula_ssv_abierta` = :val WHERE `id` = :id");
        return $stmt->execute([':val' => $isOpen ? 1 : 0, ':id' => $id]);
    }

    public function update(string $id, array $data): bool {
        if (!$this->db || empty($data)) return false;

        $columnMap = [
            'chokeOpening' => 'apertura_choke',
            'chokeTarget' => 'objetivo_choke',
            'ssvOpen' => 'valvula_ssv_abierta',
            'annulusBleedOpen' => 'purga_anular_abierta',
            'chemicalInjection' => 'inyeccion_quimica',
            'status' => 'estado',
            'apertura_choke' => 'apertura_choke',
            'objetivo_choke' => 'objetivo_choke',
            'valvula_ssv_abierta' => 'valvula_ssv_abierta',
            'purga_anular_abierta' => 'purga_anular_abierta',
            'inyeccion_quimica' => 'inyeccion_quimica',
            'estado' => 'estado'
        ];

        $sets = [];
        $params = [':id' => $id];

        foreach ($data as $key => $val) {
            if (isset($columnMap[$key])) {
                $col = $columnMap[$key];
                $paramKey = ':' . $col;
                $sets[] = "`{$col}` = {$paramKey}";
                if (is_bool($val)) {
                    $params[$paramKey] = $val ? 1 : 0;
                } else {
                    $params[$paramKey] = $val;
                }
            }
        }

        if (empty($sets)) return false;

        $sql = "UPDATE `pozos` SET " . implode(', ', $sets) . " WHERE `id` = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function setStatus(string $id, string $status): bool {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("UPDATE `pozos` SET `estado` = :st WHERE `id` = :id");
        return $stmt->execute([':st' => $status, ':id' => $id]);
    }
}

class AlarmRepository {
    private ?PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: DatabaseSingleton::getInstance()->getConnection();
    }

    public function findAll(): array {
        if (!$this->db) return [];
        $stmt = $this->db->query("SELECT * FROM `alarmas` ORDER BY `id` DESC");
        return $stmt->fetchAll();
    }

    public function acknowledge(string $id, string $operator): bool {
        if (!$this->db) return false;
        $stmt = $this->db->prepare("UPDATE `alarmas` SET `reconocida` = 1, `reconocida_por` = :by, `ack_timestamp` = :ts WHERE `id` = :id");
        return $stmt->execute([
            ':by' => $operator,
            ':ts' => date('H:i:s'),
            ':id' => $id
        ]);
    }

    public function acknowledgeAll(string $operator): int {
        if (!$this->db) return 0;
        $stmt = $this->db->prepare("UPDATE `alarmas` SET `reconocida` = 1, `reconocida_por` = :by, `ack_timestamp` = :ts WHERE `reconocida` = 0");
        $stmt->execute([':by' => $operator, ':ts' => date('H:i:s')]);
        return $stmt->rowCount();
    }
}

class AuditRepository {
    private ?PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?: DatabaseSingleton::getInstance()->getConnection();
    }

    public function log(string $userCode, string $userName, string $roleName, string $action, string $category, string $status, string $details): bool {
        if (!$this->db) return false;
        $id = 'aud-' . substr(strval(time()), -5) . rand(10, 99);
        $time = date('H:i:s');
        $stmt = $this->db->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, :ucode, :uname, :rname, :action, :cat, :st, :det)");
        return $stmt->execute([
            ':id' => $id,
            ':time' => $time,
            ':ucode' => $userCode,
            ':uname' => $userName,
            ':rname' => $roleName,
            ':action' => $action,
            ':cat' => $category,
            ':st' => $status,
            ':det' => $details
        ]);
    }

    public function append(string $userCode, string $userName, string $roleName, string $action, string $category, string $status, string $details): bool {
        return $this->log($userCode, $userName, $roleName, $action, $category, $status, $details);
    }
}

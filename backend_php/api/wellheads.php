<?php
/**
 * PROYECTO MAPAF - Endpoint: api/wellheads.php
 * Consulta y actualización de cabezales de pozo en MySQL
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

handleCorsHeaders();

$pdo = getDbConnection(true);
if (!$pdo) {
    sendJsonResponse(['success' => false, 'error' => 'Error de conexión MySQL'], 503);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM `pozos`");
    $rawWells = $stmt->fetchAll();
    $wellheads = array_map(function($w) {
        return [
            'id' => $w['id'],
            'name' => $w['nombre'],
            'sector' => $w['sector'],
            'status' => $w['estado'],
            'tubingPressure' => (float)$w['presion_tubing'],
            'casingPressure' => (float)$w['presion_casing'],
            'flowRate' => (float)$w['caudal_produccion'],
            'temperature' => (float)$w['temperatura'],
            'chokeOpening' => (float)$w['apertura_choke'],
            'chokeTarget' => (float)$w['objetivo_choke'],
            'espFrequency' => (float)$w['frecuencia_esp'],
            'espVibration' => (float)$w['vibracion_esp'],
            'ssvOpen' => (bool)$w['valvula_ssv_abierta'],
            'annulusBleedOpen' => (bool)$w['purga_anular_abierta'],
            'chemicalInjection' => (bool)$w['inyeccion_quimica'],
            'sandAcousticPpm' => (float)$w['arena_acustica_ppm']
        ];
    }, $rawWells);

    sendJsonResponse(['success' => true, 'wellheads' => $wellheads]);
}

if ($method === 'POST' || $method === 'PUT') {
    $data = getJsonRequestBody();
    $wellId = $data['id'] ?? null;

    if (!$wellId) {
        sendJsonResponse(['success' => false, 'error' => 'Se requiere el campo "id" del pozo'], 400);
    }

    $updates = [];
    $params = [':id' => $wellId];

    if (isset($data['chokeOpening'])) {
        $updates[] = "`apertura_choke` = :choke";
        $updates[] = "`objetivo_choke` = :choke_target";
        $params[':choke'] = (float)$data['chokeOpening'];
        $params[':choke_target'] = (float)$data['chokeOpening'];
    }
    if (isset($data['status'])) {
        $updates[] = "`estado` = :status";
        $params[':status'] = $data['status'];
    }
    if (isset($data['ssvOpen'])) {
        $updates[] = "`valvula_ssv_abierta` = :ssv";
        $params[':ssv'] = $data['ssvOpen'] ? 1 : 0;
    }
    if (isset($data['annulusBleedOpen'])) {
        $updates[] = "`purga_anular_abierta` = :bleed";
        $params[':bleed'] = $data['annulusBleedOpen'] ? 1 : 0;
    }
    if (isset($data['chemicalInjection'])) {
        $updates[] = "`inyeccion_quimica` = :chem";
        $params[':chem'] = $data['chemicalInjection'] ? 1 : 0;
    }
    if (isset($data['espFrequency'])) {
        $updates[] = "`frecuencia_esp` = :esp";
        $params[':esp'] = (float)$data['espFrequency'];
    }

    if (empty($updates)) {
        sendJsonResponse(['success' => false, 'error' => 'No hay campos para actualizar'], 400);
    }

    $sql = "UPDATE `pozos` SET " . implode(', ', $updates) . " WHERE `id` = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Registro opcional en bitácora
    $userCode = $data['userCode'] ?? 'OP-449';
    $userName = $data['userName'] ?? 'M. Conde Dussan';
    $roleName = $data['roleName'] ?? 'Operador de Consola';
    $actionDesc = $data['actionDescription'] ?? "Modificación en pozo {$wellId}";

    $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, :ucode, :uname, :rname, :action, 'MANIOBRA', 'EXITOSO', :details)");
    $auditStmt->execute([
        ':id' => 'aud-' . substr(strval(time()), -5) . rand(10, 99),
        ':time' => date('H:i:s'),
        ':ucode' => $userCode,
        ':uname' => $userName,
        ':rname' => $roleName,
        ':action' => $actionDesc,
        ':details' => json_encode($data)
    ]);

    sendJsonResponse(['success' => true, 'message' => "Pozo {$wellId} actualizado exitosamente en MySQL"]);
}

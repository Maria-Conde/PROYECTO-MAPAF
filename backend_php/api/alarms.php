<?php
/**
 * PROYECTO MAPAF - Endpoint: api/alarms.php
 * Consulta y reconocimiento de alarmas ISA-18.2 en MySQL
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
    $stmt = $pdo->query("SELECT * FROM `alarmas` ORDER BY `id` DESC");
    $rawAlarms = $stmt->fetchAll();
    $alarms = array_map(function($a) {
        return [
            'id' => $a['id'],
            'timestamp' => $a['timestamp_evento'],
            'tag' => $a['tag'],
            'subsystem' => $a['subsistema'],
            'description' => $a['descripcion'],
            'value' => $a['valor'],
            'threshold' => $a['umbral'],
            'severity' => $a['severidad'],
            'acknowledged' => (bool)$a['reconocida'],
            'acknowledgedBy' => $a['reconocida_por'],
            'ackTimestamp' => $a['ack_timestamp'],
            'tripCause' => $a['causa_disparo']
        ];
    }, $rawAlarms);

    sendJsonResponse(['success' => true, 'alarms' => $alarms]);
}

if ($method === 'POST') {
    $data = getJsonRequestBody();
    $action = $data['action'] ?? 'ack_single';
    $acknowledgedBy = $data['acknowledgedBy'] ?? 'M. Conde Dussan';
    $ackTimestamp = date('H:i:s');

    if ($action === 'ack_all') {
        $stmt = $pdo->prepare("UPDATE `alarmas` SET `reconocida` = 1, `reconocida_por` = :by, `ack_timestamp` = :ts WHERE `reconocida` = 0");
        $stmt->execute([':by' => $acknowledgedBy, ':ts' => $ackTimestamp]);
        $count = $stmt->rowCount();

        // Registrar en auditoría
        $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, 'OP-449', :uname, 'Operador', 'Reconocimiento Masivo de Alarmas (ACK ALL)', 'ALARMA', 'EXITOSO', :details)");
        $auditStmt->execute([
            ':id' => 'aud-' . substr(strval(time()), -5) . rand(10, 99),
            ':time' => $ackTimestamp,
            ':uname' => $acknowledgedBy,
            ':details' => "Se reconocieron {$count} alarmas activas simultáneamente en consola."
        ]);

        sendJsonResponse(['success' => true, 'message' => "Todas las alarmas ({$count}) han sido reconocidas.", 'count' => $count]);
    } else {
        $alarmId = $data['id'] ?? null;
        if (!$alarmId) {
            sendJsonResponse(['success' => false, 'error' => 'Se requiere el campo "id" de la alarma'], 400);
        }

        $stmt = $pdo->prepare("UPDATE `alarmas` SET `reconocida` = 1, `reconocida_por` = :by, `ack_timestamp` = :ts WHERE `id` = :id");
        $stmt->execute([':by' => $acknowledgedBy, ':ts' => $ackTimestamp, ':id' => $alarmId]);

        // Registrar en auditoría
        $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, 'OP-449', :uname, 'Operador', 'Reconocimiento de Alarma Individual', 'ALARMA', 'EXITOSO', :details)");
        $auditStmt->execute([
            ':id' => 'aud-' . substr(strval(time()), -5) . rand(10, 99),
            ':time' => $ackTimestamp,
            ':uname' => $acknowledgedBy,
            ':details' => "Alarma {$alarmId} reconocida formalmente."
        ]);

        sendJsonResponse(['success' => true, 'message' => "Alarma {$alarmId} reconocida exitosamente.", 'alarmId' => $alarmId]);
    }
}

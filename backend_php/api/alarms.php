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
        $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, :code, :uname, 'Operador', 'Reconocimiento Masivo de Alarmas (ACK ALL)', 'ALARMA', 'EXITOSO', :details)");
        $auditStmt->execute([
            ':id' => 'aud-' . substr(strval(time()), -5) . rand(10, 99),
            ':time' => $ackTimestamp,
            ':code' => $data['operatorCode'] ?? 'OP-449',
            ':uname' => $acknowledgedBy,
            ':details' => "Se reconocieron {$count} alarmas activas simultáneamente bajo norma ISA-18.2."
        ]);

        sendJsonResponse(['success' => true, 'message' => "Todas las alarmas ({$count}) han sido reconocidas.", 'count' => $count]);
    } else {
        $alarmId = $data['id'] ?? null;
        $reason = trim($data['reason'] ?? '');
        $operatorCode = $data['operatorCode'] ?? 'OP-449';
        $roleName = $data['roleName'] ?? 'Operador';

        if (!$alarmId) {
            sendJsonResponse(['success' => false, 'error' => 'Se requiere el campo "id" de la alarma'], 400);
        }

        // Detección y Mitigación de Antipatrón: Blind Alarm Acknowledgment
        if (empty($reason)) {
            // Registrar intento de silenciamiento ciego mitigado
            $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, :code, :uname, :rname, 'Intento de Blind ACK Bloqueado', 'SEGURIDAD', 'RECHAZADO', :details)");
            $auditStmt->execute([
                ':id' => 'aud-' . substr(strval(time()), -5) . rand(10, 99),
                ':time' => $ackTimestamp,
                ':code' => $operatorCode,
                ':uname' => $acknowledgedBy,
                ':rname' => $roleName,
                ':details' => "ESCUDO ANTIPATRÓN: Se bloqueó intento de silenciar la alarma {$alarmId} sin causa técnica obligatoria (ANSI/ISA-18.2)."
            ]);

            sendJsonResponse([
                'success' => false,
                'antipatternMitigated' => 'Blind Alarm Acknowledgment (ANSI/ISA-18.2)',
                'error' => 'ESCUDO DE CALIDAD MAPAF: No se permite silenciar alarmas sin justificación técnica obligatoria registrada en bitácora.'
            ], 422);
        }

        $stmt = $pdo->prepare("UPDATE `alarmas` SET `reconocida` = 1, `reconocida_por` = :by, `ack_timestamp` = :ts, `causa_disparo` = :reason WHERE `id` = :id");
        $stmt->execute([':by' => $acknowledgedBy, ':ts' => $ackTimestamp, ':reason' => $reason, ':id' => $alarmId]);

        // Registrar en auditoría inmutable
        $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, :code, :uname, :rname, 'Reconocimiento Trazable ISA-18.2', 'ALARMA', 'EXITOSO', :details)");
        $auditStmt->execute([
            ':id' => 'aud-' . substr(strval(time()), -5) . rand(10, 99),
            ':time' => $ackTimestamp,
            ':code' => $operatorCode,
            ':uname' => $acknowledgedBy,
            ':rname' => $roleName,
            ':details' => "Alarma {$alarmId} reconocida formalmente. Causa/Justificación: '{$reason}'"
        ]);

        sendJsonResponse([
            'success' => true,
            'pattern' => 'Command & Observer ISA-18.2',
            'domain' => 'ALARMS-CTX',
            'message' => "Alarma {$alarmId} reconocida formalmente y registrada en bitácora.",
            'alarmId' => $alarmId,
            'reason' => $reason
        ]);
    }
}

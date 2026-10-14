<?php
/**
 * PROYECTO MAPAF - Endpoint: api/esd.php
 * Control de Parada de Emergencia (ESD-1) y Despresurización en MySQL
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
    $stmt = $pdo->query("SELECT clave, valor FROM `configuracion_sistema` WHERE clave IN ('esd_triggered', 'esd_trigger_time', 'flow_state', 'production_rate')");
    $cfg = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    sendJsonResponse([
        'success' => true,
        'esdTriggered' => ($cfg['esd_triggered'] ?? '0') === '1',
        'esdTriggerTime' => $cfg['esd_trigger_time'] ?? '',
        'flowState' => $cfg['flow_state'] ?? 'nominal',
        'productionRate' => (float)($cfg['production_rate'] ?? 14820)
    ]);
}

if ($method === 'POST') {
    $data = getJsonRequestBody();
    $action = $data['action'] ?? 'trip'; // 'trip' o 'reset'
    $userCode = $data['userCode'] ?? 'OP-449';
    $userName = $data['userName'] ?? 'M. Conde Dussan';
    $roleName = $data['roleName'] ?? 'Administrador del Sistema';
    $timeStr = date('H:i:s');

    if ($action === 'trip') {
        // Disparar parada de emergencia
        $pdo->prepare("UPDATE `configuracion_sistema` SET `valor` = '1' WHERE `clave` = 'esd_triggered'")->execute();
        $pdo->prepare("UPDATE `configuracion_sistema` SET `valor` = :time WHERE `clave` = 'esd_trigger_time'")->execute([':time' => $timeStr]);
        $pdo->prepare("UPDATE `configuracion_sistema` SET `valor` = 'tripped' WHERE `clave` = 'flow_state'")->execute();
        $pdo->prepare("UPDATE `configuracion_sistema` SET `valor` = '0' WHERE `clave` = 'production_rate'")->execute();

        // Cerrar válvulas y estrangular pozos por seguridad crítica
        $pdo->exec("UPDATE `pozos` SET `valvula_ssv_abierta` = 0, `apertura_choke` = 0, `estado` = 'DISPARADO'");

        // Registrar en auditoría
        $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, :ucode, :uname, :rname, 'DISPARO DE EMERGENCIA ESD-1', 'SEGURIDAD', 'ADVERTENCIA', 'Aislamiento total y cierre de válvulas SSV ejecutado por operador.')");
        $auditStmt->execute([
            ':id' => 'aud-esd-' . substr(strval(time()), -4),
            ':time' => $timeStr,
            ':ucode' => $userCode,
            ':uname' => $userName,
            ':rname' => $roleName
        ]);

        sendJsonResponse([
            'success' => true,
            'message' => 'Parada de Emergencia ESD-1 activada. Todos los pozos y válvulas aislados en MySQL.',
            'esdTriggered' => true,
            'flowState' => 'tripped',
            'productionRate' => 0
        ]);
    } else if ($action === 'reset') {
        // Restablecimiento del sistema tras ESD
        $pdo->prepare("UPDATE `configuracion_sistema` SET `valor` = '0' WHERE `clave` = 'esd_triggered'")->execute();
        $pdo->prepare("UPDATE `configuracion_sistema` SET `valor` = 'nominal' WHERE `clave` = 'flow_state'")->execute();
        $pdo->prepare("UPDATE `configuracion_sistema` SET `valor` = '14820' WHERE `clave` = 'production_rate'")->execute();

        // Normalizar válvulas de cabezal
        $pdo->exec("UPDATE `pozos` SET `valvula_ssv_abierta` = 1, `apertura_choke` = 40.0, `estado` = 'EN LÍNEA' WHERE `id` != 'well-d02'");

        // Registrar en auditoría
        $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, :ucode, :uname, :rname, 'RESTABLECIMIENTO SISTEMA POST-ESD', 'SEGURIDAD', 'EXITOSO', 'Bucle de seguridad rearmado y producción restablecida a condiciones nominales.')");
        $auditStmt->execute([
            ':id' => 'aud-rst-' . substr(strval(time()), -4),
            ':time' => $timeStr,
            ':ucode' => $userCode,
            ':uname' => $userName,
            ':rname' => $roleName
        ]);

        sendJsonResponse([
            'success' => true,
            'message' => 'Sistema post-ESD restablecido exitosamente a condiciones nominales.',
            'esdTriggered' => false,
            'flowState' => 'nominal',
            'productionRate' => 14820
        ]);
    } else {
        sendJsonResponse(['success' => false, 'error' => 'Acción no válida (use "trip" o "reset")'], 400);
    }
}

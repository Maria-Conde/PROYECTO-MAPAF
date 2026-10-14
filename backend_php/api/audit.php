<?php
/**
 * PROYECTO MAPAF - Endpoint: api/audit.php
 * Consulta y registro de bitácora de auditoría y maniobras en MySQL
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
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $stmt = $pdo->prepare("SELECT * FROM `bitacora_auditoria` ORDER BY `fecha_registro` DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rawAudit = $stmt->fetchAll();

    $auditLogs = array_map(function($l) {
        return [
            'id' => $l['id'],
            'timestamp' => $l['timestamp_log'],
            'userCode' => $l['codigo_usuario'],
            'userName' => $l['nombre_usuario'],
            'roleName' => $l['nombre_rol'],
            'action' => $l['accion'],
            'category' => $l['categoria'],
            'status' => $l['estado'],
            'details' => $l['detalles']
        ];
    }, $rawAudit);

    sendJsonResponse(['success' => true, 'auditLogs' => $auditLogs]);
}

if ($method === 'POST') {
    $data = getJsonRequestBody();

    $id = $data['id'] ?? ('aud-' . substr(strval(time()), -5) . rand(10, 99));
    $timestamp = $data['timestamp'] ?? date('H:i:s');
    $userCode = $data['userCode'] ?? 'OP-449';
    $userName = $data['userName'] ?? 'Operador';
    $roleName = $data['roleName'] ?? 'Operador de Consola';
    $action = $data['action'] ?? 'Acción no especificada';
    $category = $data['category'] ?? 'MANIOBRA';
    $status = $data['status'] ?? 'EXITOSO';
    $details = $data['details'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :ts, :code, :name, :role, :action, :cat, :status, :det)");
    $stmt->execute([
        ':id' => $id,
        ':ts' => $timestamp,
        ':code' => $userCode,
        ':name' => $userName,
        ':role' => $roleName,
        ':action' => $action,
        ':cat' => $category,
        ':status' => $status,
        ':det' => $details
    ]);

    sendJsonResponse(['success' => true, 'message' => 'Evento de auditoría registrado en MySQL', 'id' => $id]);
}

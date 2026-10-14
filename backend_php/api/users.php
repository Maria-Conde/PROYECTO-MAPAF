<?php
/**
 * PROYECTO MAPAF - Endpoint: api/users.php
 * Gestión de usuarios, roles y credenciales en MySQL
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

handleCorsHeaders();

$pdo = getDbConnection(true);
if (!$pdo) {
    sendJsonResponse(['success' => false, 'error' => 'Error de conexión MySQL'], 503);
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: Listar usuarios
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, codigo_empleado, nombre_completo, email, rol_id, sector_asignado, estado, ultimo_acceso FROM `usuarios` ORDER BY `fecha_registro` ASC");
    $rawUsers = $stmt->fetchAll();
    $users = array_map(function($u) {
        return [
            'id' => $u['id'],
            'employeeCode' => $u['codigo_empleado'],
            'fullName' => $u['nombre_completo'],
            'email' => $u['email'],
            'roleId' => $u['rol_id'],
            'assignedSector' => $u['sector_asignado'],
            'status' => $u['estado'],
            'lastAccess' => $u['ultimo_acceso']
        ];
    }, $rawUsers);

    sendJsonResponse(['success' => true, 'users' => $users]);
}

// POST: Crear usuario
if ($method === 'POST') {
    $data = getJsonRequestBody();

    $id = $data['id'] ?? ('usr-' . substr(strval(time()), -4));
    $code = $data['employeeCode'] ?? ('EMP-' . rand(1000, 9999));
    $name = $data['fullName'] ?? '';
    $email = $data['email'] ?? '';
    $roleId = $data['roleId'] ?? 'role-operator';
    $sector = $data['assignedSector'] ?? 'General';
    $status = $data['status'] ?? 'ACTIVO';
    $passHash = password_hash('123456', PASSWORD_DEFAULT);

    if (empty($name) || empty($email)) {
        sendJsonResponse(['success' => false, 'error' => 'Nombre y correo electrónico son requeridos'], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO `usuarios` (`id`, `codigo_empleado`, `nombre_completo`, `email`, `password_hash`, `rol_id`, `sector_asignado`, `estado`) VALUES (:id, :code, :name, :email, :pass, :role, :sector, :status) ON DUPLICATE KEY UPDATE `nombre_completo` = :name, `rol_id` = :role, `sector_asignado` = :sector, `estado` = :status");
    $stmt->execute([
        ':id' => $id,
        ':code' => $code,
        ':name' => $name,
        ':email' => $email,
        ':pass' => $passHash,
        ':role' => $roleId,
        ':sector' => $sector,
        ':status' => $status
    ]);

    sendJsonResponse(['success' => true, 'message' => 'Usuario guardado exitosamente en MySQL', 'id' => $id]);
}

// PUT: Actualizar usuario existente
if ($method === 'PUT') {
    $data = getJsonRequestBody();
    $id = $data['id'] ?? null;

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'ID de usuario requerido'], 400);
    }

    $stmt = $pdo->prepare("UPDATE `usuarios` SET `nombre_completo` = :name, `rol_id` = :role, `sector_asignado` = :sector, `estado` = :status WHERE `id` = :id");
    $stmt->execute([
        ':name' => $data['fullName'],
        ':role' => $data['roleId'],
        ':sector' => $data['assignedSector'],
        ':status' => $data['status'],
        ':id' => $id
    ]);

    sendJsonResponse(['success' => true, 'message' => "Usuario {$id} actualizado en MySQL"]);
}

// DELETE: Eliminar usuario
if ($method === 'DELETE') {
    $data = getJsonRequestBody();
    $id = $data['id'] ?? ($_GET['id'] ?? null);

    if (!$id) {
        sendJsonResponse(['success' => false, 'error' => 'ID de usuario requerido para eliminación'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM `usuarios` WHERE `id` = :id");
    $stmt->execute([':id' => $id]);

    sendJsonResponse(['success' => true, 'message' => "Usuario {$id} eliminado"]);
}

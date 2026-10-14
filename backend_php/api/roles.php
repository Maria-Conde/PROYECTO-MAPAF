<?php
/**
 * PROYECTO MAPAF - Endpoint: api/roles.php
 * Gestión de roles y permisos RBAC en MySQL
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
    $stmt = $pdo->query("SELECT * FROM `roles`");
    $rawRoles = $stmt->fetchAll();
    $roles = array_map(function($r) {
        return [
            'id' => $r['id'],
            'name' => $r['nombre'],
            'code' => $r['codigo'],
            'description' => $r['descripcion'],
            'color' => $r['color'],
            'isSystem' => (bool)$r['es_sistema'],
            'permissions' => json_decode($r['permisos'] ?? '[]', true) ?: []
        ];
    }, $rawRoles);

    sendJsonResponse(['success' => true, 'roles' => $roles]);
}

if ($method === 'POST') {
    $data = getJsonRequestBody();
    $roleId = $data['id'] ?? null;
    $permissions = $data['permissions'] ?? null;

    if (!$roleId || !is_array($permissions)) {
        sendJsonResponse(['success' => false, 'error' => 'Se requiere el ID del rol y la lista de permisos'], 400);
    }

    $stmt = $pdo->prepare("UPDATE `roles` SET `permisos` = :perms WHERE `id` = :id");
    $stmt->execute([
        ':perms' => json_encode($permissions),
        ':id' => $roleId
    ]);

    sendJsonResponse(['success' => true, 'message' => "Permisos del rol {$roleId} actualizados en MySQL"]);
}

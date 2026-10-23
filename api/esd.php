<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../controllers/EsdController.php';

handleCorsHeaders();
$controller = new EsdController();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $controller->index();
} elseif ($method === 'POST') {
    $input = getJsonInput();
    $action = $input['action'] ?? 'trigger';
    if ($action === 'reset') {
        $controller->reset();
    } else {
        $controller->trigger();
    }
} else {
    sendJsonResponse(['error' => 'Método no soportado'], 405);
}

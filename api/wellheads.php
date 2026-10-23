<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../controllers/WellheadController.php';

handleCorsHeaders();
$controller = new WellheadController();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $controller->index();
} elseif ($method === 'POST' || $method === 'PUT') {
    $controller->update();
} else {
    sendJsonResponse(['error' => 'Método no soportado'], 405);
}

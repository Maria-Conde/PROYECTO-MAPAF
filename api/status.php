<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../controllers/StatusController.php';

handleCorsHeaders();
$controller = new StatusController();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $controller->index();
} else {
    sendJsonResponse(['error' => 'Método no soportado'], 405);
}

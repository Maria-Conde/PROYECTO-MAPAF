<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../controllers/AlarmController.php';

handleCorsHeaders();
$controller = new AlarmController();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $controller->index();
} elseif ($method === 'POST') {
    $controller->acknowledge();
} else {
    sendJsonResponse(['error' => 'Método no soportado'], 405);
}

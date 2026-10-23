<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../controllers/AuthController.php';

handleCorsHeaders();
$controller = new AuthController();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $controller->users();
} elseif ($method === 'POST') {
    $controller->switchOperator();
} else {
    sendJsonResponse(['error' => 'Método no soportado'], 405);
}

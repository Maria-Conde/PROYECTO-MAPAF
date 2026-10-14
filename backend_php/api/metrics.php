<?php
/**
 * PROYECTO MAPAF - Endpoint: api/metrics.php
 * Consulta y actualización de lecturas de telemetría de sensores en MySQL
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
    $stmt = $pdo->query("SELECT * FROM `telemetria_metricas`");
    $rawMetrics = $stmt->fetchAll();
    $metrics = array_map(function($m) {
        return [
            'id' => $m['id'],
            'tag' => $m['tag'],
            'label' => $m['etiqueta'],
            'value' => (float)$m['valor'],
            'unit' => $m['unidad'],
            'target' => (float)$m['valor_objetivo'],
            'lowTrip' => (float)$m['trip_bajo'],
            'highWarning' => (float)$m['alarma_alta'],
            'highTrip' => (float)$m['trip_alto'],
            'trend' => $m['tendencia'],
            'severity' => $m['severidad'],
            'history' => json_decode($m['historial'] ?? '[]', true) ?: []
        ];
    }, $rawMetrics);

    sendJsonResponse(['success' => true, 'metrics' => $metrics]);
}

if ($method === 'POST') {
    $data = getJsonRequestBody();
    $id = $data['id'] ?? null;
    $value = isset($data['value']) ? (float)$data['value'] : null;

    if (!$id || $value === null) {
        sendJsonResponse(['success' => false, 'error' => 'Se requiere el campo "id" y "value"'], 400);
    }

    // Obtener historial actual
    $stmt = $pdo->prepare("SELECT historial FROM `telemetria_metricas` WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    $history = [];
    if ($row && !empty($row['historial'])) {
        $history = json_decode($row['historial'], true) ?: [];
    }
    $history[] = $value;
    if (count($history) > 12) {
        array_shift($history);
    }

    $trend = $data['trend'] ?? 'stable';
    $severity = $data['severity'] ?? 'nominal';

    $updateStmt = $pdo->prepare("UPDATE `telemetria_metricas` SET `valor` = :val, `trend` = :trend, `severity` = :sev, `historial` = :hist WHERE `id` = :id");
    $updateStmt->execute([
        ':val' => $value,
        ':trend' => $trend,
        ':sev' => $severity,
        ':hist' => json_encode($history),
        ':id' => $id
    ]);

    sendJsonResponse(['success' => true, 'message' => "Métrica {$id} actualizada exitosamente en MySQL"]);
}

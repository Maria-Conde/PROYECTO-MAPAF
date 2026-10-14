<?php
/**
 * PROYECTO MAPAF - Endpoint: api/sync.php
 * Retorna todos los datos operativos de telemetría, pozos, alarmas, roles y auditoría desde MySQL
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

handleCorsHeaders();

$pdo = getDbConnection(true);

if (!$pdo) {
    sendJsonResponse([
        'success' => false,
        'error' => 'No hay conexión con la base de datos MySQL mapaf_db en XAMPP.'
    ], 503);
}

try {
    // 1. Métricas de telemetría
    $stmtMetrics = $pdo->query("SELECT * FROM `telemetria_metricas`");
    $rawMetrics = $stmtMetrics->fetchAll();
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

    // 2. Pozos y cabezales
    $stmtWells = $pdo->query("SELECT * FROM `pozos`");
    $rawWells = $stmtWells->fetchAll();
    $wellheads = array_map(function($w) {
        return [
            'id' => $w['id'],
            'name' => $w['nombre'],
            'sector' => $w['sector'],
            'status' => $w['estado'],
            'tubingPressure' => (float)$w['presion_tubing'],
            'casingPressure' => (float)$w['presion_casing'],
            'flowRate' => (float)$w['caudal_produccion'],
            'temperature' => (float)$w['temperatura'],
            'chokeOpening' => (float)$w['apertura_choke'],
            'chokeTarget' => (float)$w['objetivo_choke'],
            'espFrequency' => (float)$w['frecuencia_esp'],
            'espVibration' => (float)$w['vibracion_esp'],
            'ssvOpen' => (bool)$w['valvula_ssv_abierta'],
            'annulusBleedOpen' => (bool)$w['purga_anular_abierta'],
            'chemicalInjection' => (bool)$w['inyeccion_quimica'],
            'sandAcousticPpm' => (float)$w['arena_acustica_ppm']
        ];
    }, $rawWells);

    // 3. Alarmas
    $stmtAlarms = $pdo->query("SELECT * FROM `alarmas` ORDER BY `id` DESC");
    $rawAlarms = $stmtAlarms->fetchAll();
    $alarms = array_map(function($a) {
        return [
            'id' => $a['id'],
            'timestamp' => $a['timestamp_evento'],
            'tag' => $a['tag'],
            'subsystem' => $a['subsistema'],
            'description' => $a['descripcion'],
            'value' => $a['valor'],
            'threshold' => $a['umbral'],
            'severity' => $a['severidad'],
            'acknowledged' => (bool)$a['reconocida'],
            'acknowledgedBy' => $a['reconocida_por'],
            'ackTimestamp' => $a['ack_timestamp'],
            'tripCause' => $a['causa_disparo']
        ];
    }, $rawAlarms);

    // 4. Roles
    $stmtRoles = $pdo->query("SELECT * FROM `roles`");
    $rawRoles = $stmtRoles->fetchAll();
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

    // 5. Usuarios
    $stmtUsers = $pdo->query("SELECT id, codigo_empleado, nombre_completo, email, rol_id, sector_asignado, estado, ultimo_acceso FROM `usuarios`");
    $rawUsers = $stmtUsers->fetchAll();
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

    // 6. Bitácora de Auditoría
    $stmtAudit = $pdo->query("SELECT * FROM `bitacora_auditoria` ORDER BY `fecha_registro` DESC LIMIT 100");
    $rawAudit = $stmtAudit->fetchAll();
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

    // 7. Configuración del sistema (ESD, Flow, Rate)
    $stmtCfg = $pdo->query("SELECT clave, valor FROM `configuracion_sistema`");
    $cfgRaw = $stmtCfg->fetchAll(PDO::FETCH_KEY_PAIR);

    $systemConfig = [
        'esdTriggered' => ($cfgRaw['esd_triggered'] ?? '0') === '1',
        'esdTriggerTime' => $cfgRaw['esd_trigger_time'] ?? '',
        'flowState' => $cfgRaw['flow_state'] ?? 'nominal',
        'productionRate' => (float)($cfgRaw['production_rate'] ?? 14820),
        'plantName' => $cfgRaw['plant_name'] ?? 'MAPAF RTOC',
        'senaFicha' => $cfgRaw['sena_ficha'] ?? '3413988-ADSO'
    ];

    sendJsonResponse([
        'success' => true,
        'source' => 'MySQL (mapaf_db)',
        'metrics' => $metrics,
        'wellheads' => $wellheads,
        'alarms' => $alarms,
        'roles' => $roles,
        'users' => $users,
        'auditLogs' => $auditLogs,
        'systemConfig' => $systemConfig,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}

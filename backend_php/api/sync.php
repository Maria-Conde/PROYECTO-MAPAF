<?php
/**
 * PROYECTO MAPAF - Endpoint: api/sync.php
 * Sincronización de Telemetría en Tiempo Real con Integración de Patrones GoF y Dominios DDD
 * 
 * Patrones aplicados en este ciclo:
 * - STRATEGY: Cálculo dinámico de producción fiscal (Coriolis AGA-11 vs AGA-3 vs Beggs & Brill)
 * - OBSERVER: TelemetrySubject con notificación reactiva de umbrales ISA-18.2
 * - REPOSITORY: Persistencia y consulta atómica desacoplada
 * - SINGLETON: DatabaseSingleton para conexión única a MySQL mapaf_db
 * - DOMINIOS: Bounded Contexts [TELEMETRY-CTX], [PRODUCTION-CTX], [ALARMS-CTX], [SAFETY-CTX]
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../patterns/Singleton/DatabaseSingleton.php';
require_once __DIR__ . '/../patterns/Strategy/FlowCalculationStrategy.php';
require_once __DIR__ . '/../patterns/Observer/TelemetryObserver.php';

use Mapaf\Patterns\Singleton\DatabaseSingleton;
use Mapaf\Patterns\Strategy\FlowCalculatorContext;
use Mapaf\Patterns\Strategy\CoriolisDirectStrategy;
use Mapaf\Patterns\Strategy\Aga3OrificeStrategy;
use Mapaf\Patterns\Strategy\BeggsAndBrillStrategy;
use Mapaf\Patterns\Observer\TelemetrySubject;
use Mapaf\Patterns\Observer\TelemetryEvent;
use Mapaf\Patterns\Observer\AlarmTriggerObserver;
use Mapaf\Patterns\Observer\AuditLoggerObserver;
use Mapaf\Patterns\Observer\SafetyInterlockObserver;

handleCorsHeaders();

$pdo = DatabaseSingleton::getInstance()->getConnection();

if (!$pdo) {
    sendJsonResponse([
        'success' => false,
        'error' => 'No hay conexión con la base de datos MySQL mapaf_db en XAMPP.'
    ], 503);
}

try {
    // 1. Métricas de telemetría (TELEMETRY-CTX)
    $stmtMetrics = $pdo->query("SELECT * FROM `telemetria_metricas`");
    $rawMetrics = $stmtMetrics->fetchAll();
    
    // Preparar Observador Reactivo (OBSERVER PATTERN)
    $subject = new TelemetrySubject();
    $alarmObserver = new AlarmTriggerObserver();
    $auditObserver = new AuditLoggerObserver();
    $safetyObserver = new SafetyInterlockObserver();
    $subject->attach($alarmObserver);
    $subject->attach($auditObserver);
    $subject->attach($safetyObserver);

    $observerNotifications = [];

    $metrics = array_map(function($m) use ($subject, &$observerNotifications) {
        $val = (float)$m['valor'];
        $sev = $m['severidad'];
        
        // Si cruza umbral crítico o advertencia, el patrón Observer reacciona
        if ($sev === 'critical' || $sev === 'warning') {
            $evt = new TelemetryEvent(
                $m['tag'],
                'Transmisor Industrial',
                $val,
                (float)$m['valor_objetivo'],
                $m['unidad'],
                $sev,
                ['label' => $m['etiqueta']]
            );
            $notif = $subject->notify($evt);
            $observerNotifications[] = [
                'tag' => $m['tag'],
                'severity' => $sev,
                'value' => $val,
                'observersReacted' => count($notif),
                'details' => $notif
            ];
        }

        return [
            'id' => $m['id'],
            'tag' => $m['tag'],
            'label' => $m['etiqueta'],
            'value' => $val,
            'unit' => $m['unidad'],
            'target' => (float)$m['valor_objetivo'],
            'lowTrip' => (float)$m['trip_bajo'],
            'highWarning' => (float)$m['alarma_alta'],
            'highTrip' => (float)$m['trip_alto'],
            'trend' => $m['tendencia'],
            'severity' => $sev,
            'history' => json_decode($m['historial'] ?? '[]', true) ?: []
        ];
    }, $rawMetrics);

    // 2. Pozos y cabezales (WELLHEAD-CTX)
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

    // 3. Patrón STRATEGY: Cálculo Dinámico de Caudal Fiscal (PRODUCTION-CTX)
    $selectedStrategyKey = $_GET['strategy'] ?? 'coriolis';
    $strategies = FlowCalculatorContext::getAvailableStrategies();
    $strategyInstance = $strategies[$selectedStrategyKey] ?? new CoriolisDirectStrategy();
    
    $flowContext = new FlowCalculatorContext($strategyInstance);
    
    // Promedios de variables para el cálculo
    $avgWhtp = count($wellheads) > 0 ? array_sum(array_column($wellheads, 'tubingPressure')) / count($wellheads) : 1850.0;
    $avgTemp = count($wellheads) > 0 ? array_sum(array_column($wellheads, 'temperature')) / count($wellheads) : 78.0;
    $avgChoke = count($wellheads) > 0 ? array_sum(array_column($wellheads, 'chokeOpening')) / count($wellheads) : 65.0;
    $avgEsp = count($wellheads) > 0 ? array_sum(array_column($wellheads, 'espFrequency')) / count($wellheads) : 58.5;

    $calculatedProduction = $flowContext->calculateFlow($avgWhtp, $avgTemp, $avgChoke, $avgEsp);

    // Comparativa de las 3 estrategias para mostrar en el prototipo
    $allStrategiesComparison = [];
    foreach ($strategies as $key => $strat) {
        $tempCtx = new FlowCalculatorContext($strat);
        $res = $tempCtx->calculateFlow($avgWhtp, $avgTemp, $avgChoke, $avgEsp);
        $allStrategiesComparison[$key] = $res->toArray();
    }

    // 4. Alarmas (ALARMS-CTX)
    $stmtAlarms = $pdo->query("SELECT * FROM `alarmas` ORDER BY `id` DESC LIMIT 30");
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

    // 5. Roles y Usuarios (IDENTITY-CTX)
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

    // 6. Bitácora de Auditoría (AUDIT-CTX)
    $stmtAudit = $pdo->query("SELECT * FROM `bitacora_auditoria` ORDER BY `id` DESC LIMIT 50");
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

    // 7. Configuración del sistema (SAFETY-CTX)
    $stmtCfg = $pdo->query("SELECT clave, valor FROM `configuracion_sistema`");
    $cfgRaw = $stmtCfg->fetchAll(PDO::FETCH_KEY_PAIR);

    $systemConfig = [
        'esdTriggered' => ($cfgRaw['esd_triggered'] ?? '0') === '1',
        'esdTriggerTime' => $cfgRaw['esd_trigger_time'] ?? '',
        'flowState' => $cfgRaw['flow_state'] ?? 'nominal',
        'productionRate' => $calculatedProduction->netOilBpd,
        'gasRate' => $calculatedProduction->gasMscfd,
        'gor' => $calculatedProduction->gor,
        'plantName' => $cfgRaw['plant_name'] ?? 'MAPAF RTOC',
        'senaFicha' => $cfgRaw['sena_ficha'] ?? '3413988-ADSO'
    ];

    sendJsonResponse([
        'success' => true,
        'source' => 'MySQL (mapaf_db)',
        'patterns' => [
            'singleton' => 'DatabaseSingleton (Conexión única PDO)',
            'strategy' => [
                'active' => $selectedStrategyKey,
                'name' => $strategyInstance->getName(),
                'standard' => $strategyInstance->getStandard(),
                'calculatedBpd' => $calculatedProduction->netOilBpd,
                'uncertainty' => $calculatedProduction->uncertaintyPercent . '%',
                'comparison' => $allStrategiesComparison
            ],
            'observer' => [
                'totalReacted' => count($observerNotifications),
                'activeEvents' => $observerNotifications
            ],
            'command' => 'ScadaCommand con soporte UNDO habilitado',
            'state' => 'Máquina de estados API 6A en pozos',
            'repository' => 'WellheadRepository, AlarmRepository, AuditRepository',
            'adapter' => 'IndustrialProtocolAdapter (Modbus RTU / OPC-UA)'
        ],
        'domains' => [
            'TELEMETRY-CTX' => count($metrics) . ' métricas activas',
            'WELLHEAD-CTX' => count($wellheads) . ' cabezales supervisados',
            'SAFETY-CTX' => 'ESD-1 ' . ($systemConfig['esdTriggered'] ? 'DISPARADO' : 'NORMAL'),
            'ALARMS-CTX' => count($alarms) . ' eventos ISA-18.2',
            'IDENTITY-CTX' => count($users) . ' operadores RBAC con JWT',
            'AUDIT-CTX' => count($auditLogs) . ' registros inmutables 21 CFR 11',
            'PRODUCTION-CTX' => $calculatedProduction->netOilBpd . ' BPD calculados vía Strategy'
        ],
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

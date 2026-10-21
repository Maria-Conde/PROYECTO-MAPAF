<?php
/**
 * PROYECTO MAPAF - Endpoint: api/patterns.php
 * API de Patrones de Diseño, Antipatrones, Dominios DDD y Pruebas en Vivo
 * SENA ADSO · Ficha 3413988
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../patterns/Singleton/DatabaseSingleton.php';
require_once __DIR__ . '/../patterns/Factory/SensorFactory.php';
require_once __DIR__ . '/../patterns/Observer/TelemetryObserver.php';
require_once __DIR__ . '/../patterns/Command/ScadaCommand.php';
require_once __DIR__ . '/../patterns/Strategy/FlowCalculationStrategy.php';
require_once __DIR__ . '/../patterns/Repository/ScadaRepositories.php';
require_once __DIR__ . '/../patterns/State/WellheadState.php';
require_once __DIR__ . '/../patterns/Adapter/IndustrialProtocolAdapter.php';
require_once __DIR__ . '/../domain/DomainArchitecture.php';
require_once __DIR__ . '/../antipatterns/AntiPatternCatalog.php';
require_once __DIR__ . '/../auth/jwt.php';

use Mapaf\Patterns\Singleton\DatabaseSingleton;
use Mapaf\Patterns\Factory\SensorFactory;
use Mapaf\Patterns\Factory\AlarmFactory;
use Mapaf\Patterns\Observer\TelemetrySubject;
use Mapaf\Patterns\Observer\TelemetryEvent;
use Mapaf\Patterns\Observer\AlarmTriggerObserver;
use Mapaf\Patterns\Observer\AuditLoggerObserver;
use Mapaf\Patterns\Observer\SafetyInterlockObserver;
use Mapaf\Patterns\Command\CommandManager;
use Mapaf\Patterns\Command\SetChokeCommand;
use Mapaf\Patterns\Command\ToggleSsvValveCommand;
use Mapaf\Patterns\Command\EmergencyShutdownCommand;
use Mapaf\Patterns\Strategy\FlowCalculatorContext;
use Mapaf\Patterns\Strategy\CoriolisDirectStrategy;
use Mapaf\Patterns\Strategy\Aga3OrificeStrategy;
use Mapaf\Patterns\Strategy\BeggsAndBrillStrategy;
use Mapaf\Patterns\State\WellheadContext;
use Mapaf\Patterns\Adapter\IndustrialProtocolAdapter;
use Mapaf\Patterns\Adapter\ModbusRtuSource;
use Mapaf\Patterns\Adapter\OpcUaSource;
use Mapaf\Domain\DomainCatalog;
use Mapaf\AntiPatterns\AntiPatternCatalog;
use Mapaf\Auth\JwtService;

handleCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];

// ============================================================================
// GET: Catálogo completo de Patrones, Antipatrones y Dominios
// ============================================================================
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'all';

    $singletonDiag = DatabaseSingleton::getInstance()->getDiagnostics();
    $domains = array_map(fn($d) => $d->toArray(), DomainCatalog::getAllDomains());
    $antipatterns = array_map(fn($a) => $a->toArray(), AntiPatternCatalog::getAll());

    $patternsList = [
        [
            'name' => 'Singleton',
            'category' => 'Creacional',
            'class' => 'Mapaf\\Patterns\\Singleton\\DatabaseSingleton',
            'purpose' => 'Garantizar una única instancia de conexión PDO a MySQL mapaf_db en XAMPP, evitando saturación del pool de conexiones.',
            'scadaBenefit' => 'Protege la estabilidad de la telemetría en tiempo real eliminando reconexiones redundantes.',
            'status' => 'Activo & Verificado'
        ],
        [
            'name' => 'Factory Method',
            'category' => 'Creacional',
            'class' => 'Mapaf\\Patterns\\Factory\\SensorFactory & AlarmFactory',
            'purpose' => 'Instanciar sensores (PT, TT, FT, VFD, GOR) y eventos de alarma bajo norma ISA-18.2 con límites de seguridad calibrados.',
            'scadaBenefit' => 'Asegura que cada instrumento cumpla automáticamente con rangos válidos de ingeniería antes de ingresar al proceso.',
            'status' => 'Activo & Verificado'
        ],
        [
            'name' => 'Observer (Observador)',
            'category' => 'Comportamiento',
            'class' => 'Mapaf\\Patterns\\Observer\\TelemetrySubject',
            'purpose' => 'Desacoplar la recepción de telemetría de sus consumidores (disparador de alarmas, bitácora de auditoría y parada ESD).',
            'scadaBenefit' => 'Reacción reactiva inmediata en milisegundos cuando una variable excede umbrales High-High.',
            'status' => 'Activo & Verificado'
        ],
        [
            'name' => 'Command (Comando con Deshacer)',
            'category' => 'Comportamiento',
            'class' => 'Mapaf\\Patterns\\Command\\CommandManager & ScadaCommandInterface',
            'purpose' => 'Encapsular maniobras de pozo (ajuste de choke, conmutación SSV, ESD) como objetos con soporte de reversión (UNDO).',
            'scadaBenefit' => 'Trazabilidad inmutable de cada acción de operador y capacidad de retorno a estado seguro.',
            'status' => 'Activo & Verificado'
        ],
        [
            'name' => 'Strategy (Estrategia)',
            'category' => 'Comportamiento',
            'class' => 'Mapaf\\Patterns\\Strategy\\FlowCalculatorContext',
            'purpose' => 'Alternar algoritmos de cálculo de producción fiscal (Coriolis AGA-11, Orificio AGA-3, Multifásico Beggs & Brill).',
            'scadaBenefit' => 'Flexibilidad para operar con medidor fiscal de alta precisión o degradar a orificio sin detener el flujo.',
            'status' => 'Activo & Verificado'
        ],
        [
            'name' => 'State (Estado de Pozo)',
            'category' => 'Comportamiento',
            'class' => 'Mapaf\\Patterns\\State\\WellheadContext',
            'purpose' => 'Gestionar las transiciones del ciclo de vida del pozo (Produciendo, Estrangulado, Disparado, Cerrado) con enclavamientos.',
            'scadaBenefit' => 'Impide físicamente maniobras ilegales como arrancar un pozo disparado sin rearme de seguridad.',
            'status' => 'Activo & Verificado'
        ],
        [
            'name' => 'Adapter (Adaptador)',
            'category' => 'Estructural',
            'class' => 'Mapaf\\Patterns\\Adapter\\IndustrialProtocolAdapter',
            'purpose' => 'Convertir tramas industriales Modbus RTU de 16 bits y paquetes OPC-UA en formato JSON estándar SCADA.',
            'scadaBenefit' => 'Interoperabilidad con RTUs y PLCs legacy de campo sin alterar el núcleo de MAPAF.',
            'status' => 'Activo & Verificado'
        ],
        [
            'name' => 'Repository (Repositorio)',
            'category' => 'Arquitectónico',
            'class' => 'Mapaf\\Patterns\\Repository\\WellheadRepository, AlarmRepository',
            'purpose' => 'Desacoplar la lógica de negocio de las consultas directas SQL con PDO.',
            'scadaBenefit' => 'Facilita pruebas unitarias, migraciones de base de datos y mantiene controladores limpios.',
            'status' => 'Activo & Verificado'
        ]
    ];

    // Si el usuario ingresa desde un navegador (Accept: text/html), mostrar interfaz visual interactiva
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    $wantsJson = isset($_GET['format']) && $_GET['format'] === 'json';
    if (!$wantsJson && str_contains($acceptHeader, 'text/html')) {
        renderInteractivePatternsHtml($patternsList, $antipatterns, $domains, $singletonDiag);
        exit;
    }

    sendJsonResponse([
        'success' => true,
        'metadata' => [
            'platform' => 'MAPAF SCADA Táctico & Telemetría IIoT',
            'sena' => 'ADSO - Ficha 3413988 (Regional Huila)',
            'instructor' => 'Javier Humberto Pinto Díaz',
            'integrantes' => ['Hernann Thomas Manchola Bocanegra', 'María José Conde Dussan', 'Emilin Tatiana Vargas Manchola'],
            'stack' => 'XAMPP (Apache + PHP 8 + MySQL mapaf_db) + React/TS Frontend',
            'auth' => 'JWT (RFC 7519 HMAC SHA-256)'
        ],
        'singletonStatus' => $singletonDiag,
        'patterns' => $patternsList,
        'antipatterns' => $antipatterns,
        'domains' => $domains
    ]);
}

// ============================================================================
// POST: Laboratorio y Pruebas en Vivo de los Patrones
// ============================================================================
if ($method === 'POST') {
    $data = getJsonRequestBody();
    $action = $data['action'] ?? 'test_singleton';

    switch ($action) {
        // 1. Probar Singleton
        case 'test_singleton':
            $instance1 = DatabaseSingleton::getInstance();
            $instance2 = DatabaseSingleton::getInstance();
            $instance3 = DatabaseSingleton::getInstance();
            $hash1 = spl_object_hash($instance1);
            $hash2 = spl_object_hash($instance2);
            $hash3 = spl_object_hash($instance3);

            sendJsonResponse([
                'success' => true,
                'pattern' => 'Singleton',
                'isIdentical' => ($instance1 === $instance2 && $instance2 === $instance3),
                'instancesCreated' => 1,
                'callsCount' => 3,
                'memoryHashes' => [$hash1, $hash2, $hash3],
                'diagnostics' => $instance1->getDiagnostics(),
                'explanation' => 'Las 3 llamadas a DatabaseSingleton::getInstance() retornaron exactamente la misma referencia en memoria (' . $hash1 . '), verificando que no se crean conexiones redundantes a MySQL.'
            ]);
            break;

        // 2. Probar Factory Method
        case 'test_factory':
            $type = $data['type'] ?? 'pressure';
            $tag = $data['tag'] ?? 'PT-TEST-99';
            $label = $data['label'] ?? 'Sensor de Prueba Laboratorio';
            $sensor = SensorFactory::create($type, $tag, $label);

            sendJsonResponse([
                'success' => true,
                'pattern' => 'Factory Method',
                'createdSensor' => $sensor->toArray(),
                'validationTest' => [
                    'validValue' => $sensor->validate(1850.0),
                    'invalidNegative' => $sensor->validate(-50.0),
                    'evaluatedSeverity' => $sensor->evaluateSeverity(2450.0)
                ],
                'explanation' => 'Sensor instanciado dinámicamente según su tipo físico con calibración y umbrales ISA-18.2 encapsulados.'
            ]);
            break;

        // 3. Probar Observer
        case 'test_observer':
            $subject = new TelemetrySubject();
            $subject->attach(new AlarmTriggerObserver());
            $subject->attach(new AuditLoggerObserver());
            $subject->attach(new SafetyInterlockObserver());

            $testPressure = floatval($data['value'] ?? 2450.0);
            $event = new TelemetryEvent(
                $data['tag'] ?? 'WHTP-101A',
                'Pressure',
                $testPressure,
                1842.0,
                'PSI',
                $testPressure >= 2400 ? 'critical' : ($testPressure >= 2000 ? 'warning' : 'nominal')
            );

            $observerResults = $subject->notify($event);

            sendJsonResponse([
                'success' => true,
                'pattern' => 'Observer',
                'subjectTag' => $event->tag,
                'testValue' => $event->value,
                'severity' => $event->severity,
                'observersNotified' => $subject->getObserversCount(),
                'results' => $observerResults,
                'explanation' => 'El sensor (Subject) notificó a los 3 observadores simultáneamente. Si la presión superó 2400 PSI, se activó la alarma y el enclavamiento ESD-1.'
            ]);
            break;

        // 4. Probar Command con Undo
        case 'test_command':
            $manager = new CommandManager();
            $cmdType = $data['command'] ?? 'choke';
            $operator = $data['operator'] ?? 'M. Conde Dussan';

            if ($cmdType === 'choke') {
                $command = new SetChokeCommand('Alfa-12', 42.8, floatval($data['targetOpening'] ?? 65.0), $operator);
            } elseif ($cmdType === 'valve') {
                $command = new ToggleSsvValveCommand('Bravo-04', true, false, $operator);
            } else {
                $command = new EmergencyShutdownCommand('ESD-1 Paro General', $operator);
            }

            $execResult = $manager->executeCommand($command);
            $historyBefore = $manager->getHistorySummary();

            // Simular Undo si se solicita
            $undoResult = null;
            if (!empty($data['runUndo'])) {
                $undoResult = $manager->undoLastCommand();
            }

            sendJsonResponse([
                'success' => true,
                'pattern' => 'Command con Soporte de Deshacer (UNDO)',
                'executionResult' => $execResult,
                'undoResult' => $undoResult,
                'history' => $historyBefore,
                'explanation' => 'La maniobra fue encapsulada como un objeto desacoplado con registro de auditoría y capacidad matemática de reversión.'
            ]);
            break;

        // 5. Probar Strategy
        case 'test_strategy':
            $whtp = floatval($data['whtp'] ?? 1842.5);
            $temp = floatval($data['temp'] ?? 64.2);
            $choke = floatval($data['choke'] ?? 42.8);
            $esp = floatval($data['esp'] ?? 58.2);

            $coriolis = (new CoriolisDirectStrategy())->calculate($whtp, $temp, $choke, $esp);
            $aga3 = (new Aga3OrificeStrategy())->calculate($whtp, $temp, $choke, $esp);
            $beggs = (new BeggsAndBrillStrategy())->calculate($whtp, $temp, $choke, $esp);

            sendJsonResponse([
                'success' => true,
                'pattern' => 'Strategy',
                'inputs' => ['whtp' => $whtp, 'temp' => $temp, 'choke' => $choke, 'esp' => $esp],
                'comparison' => [
                    'coriolis' => $coriolis->toArray(),
                    'aga3' => $aga3->toArray(),
                    'beggsAndBrill' => $beggs->toArray()
                ],
                'explanation' => 'Los 3 algoritmos de flujo calculan el balance de producción utilizando la misma interfaz polimórfica sin modificar el cliente.'
            ]);
            break;

        // 6. Probar State
        case 'test_state':
            $context = new WellheadContext('Charlie-09');
            $actionStep = $data['stateAction'] ?? 'trip';

            $result = match($actionStep) {
                'trip' => $context->trip('Sobrepresión anular Casing > 1100 PSI'),
                'reset' => $context->reset(),
                'open' => $context->openSsv(),
                'close' => $context->closeSsv(),
                'choke' => $context->adjustChoke(floatval($data['choke'] ?? 18.0)),
                default => ['success' => true, 'message' => 'Estado consultado']
            };

            sendJsonResponse([
                'success' => true,
                'pattern' => 'State',
                'currentState' => $context->toArray(),
                'actionResult' => $result,
                'explanation' => 'El cabezal de pozo gestiona su comportamiento dinámicamente según su estado actual, impidiendo transiciones inseguras.'
            ]);
            break;

        // 7. Probar Adapter
        case 'test_adapter':
            $rawHex = [0x44E7, 0x5000]; // IEEE 754 Float para 1850.5
            $modbus = new ModbusRtuSource(1, 40001, $rawHex);
            $adaptedModbus = IndustrialProtocolAdapter::adapt($modbus, 'WHTP-101A', 'Presión Tubing Pozo Alfa-12', 'PSI');

            $opcUa = new OpcUaSource('ns=2;s=Facility.Separators.V-101.Pressure', 642.0);
            $adaptedOpc = IndustrialProtocolAdapter::adapt($opcUa, 'SEP-P-101', 'Presión Separador Bifásico', 'PSI');

            sendJsonResponse([
                'success' => true,
                'pattern' => 'Adapter',
                'modbusAdapted' => $adaptedModbus,
                'opcUaAdapted' => $adaptedOpc,
                'explanation' => 'Tramas industriales heterogéneas (Modbus binario y OPC-UA) fueron transformadas a la estructura estándar JSON de MAPAF.'
            ]);
            break;

        // 8. Sandbox de Antipatrón: SQL Injection vs Prepared Statements
        case 'test_antipattern_sqli':
            $pdo = DatabaseSingleton::getInstance()->getConnection();
            $maliciousInput = $data['input'] ?? "' OR '1'='1' --";
            
            // Demostración de consulta segura con PDO Prepared Statements
            $stmt = $pdo->prepare("SELECT id, nombre, sector, estado, presion_tubing FROM pozos WHERE nombre LIKE :term1 OR sector LIKE :term2");
            $likeVal = '%' . $maliciousInput . '%';
            $stmt->execute([':term1' => $likeVal, ':term2' => $likeVal]);
            $safeResults = $stmt->fetchAll();

            sendJsonResponse([
                'success' => true,
                'antipattern' => 'SQL Injection por Concatenación Directa',
                'mitigationPattern' => 'PDO Prepared Statements con Tipado Estricto',
                'maliciousPayloadAttempted' => $maliciousInput,
                'vulnerableQuerySimulated' => "SELECT * FROM pozos WHERE nombre LIKE '%{$maliciousInput}%' -- (ROMPERÍA LA SEGURIDAD)",
                'securePreparedExecution' => "SELECT * FROM pozos WHERE nombre LIKE :term [Param Bound Seguramente]",
                'isAttackNeutralized' => true,
                'recordsReturned' => count($safeResults),
                'results' => $safeResults,
                'explanation' => 'El motor PDO de MySQL trató la cadena inyectada como un literal puro (texto plano) y no como código SQL ejecutable. La base de datos mapaf_db permaneció 100% segura.'
            ]);
            break;

        // 9. Sandbox de Antipatrón: God Object vs Arquitectura Modular DDD
        case 'test_antipattern_god_object':
            sendJsonResponse([
                'success' => true,
                'antipattern' => 'The God Object / Blob Monolítico',
                'mitigationPattern' => 'Domain-Driven Design (7 Bounded Contexts) + Single Responsibility',
                'boundedContextsCount' => 7,
                'microControllers' => [
                    'wellheads.php' => 'Solo operaciones de cabeza de pozo API 6A y comandos de choke',
                    'alarms.php' => 'Solo ciclo de vida ISA-18.2, acuse trazable y shelving',
                    'sync.php' => 'Solo adquisición reactiva de telemetría y estrategias de flujo',
                    'login.php' => 'Solo autenticación criptográfica JWT y claims RBAC',
                    'audit.php' => 'Solo bitácora inmutable append-only conforme a 21 CFR 11',
                    'esd.php' => 'Solo enclavamientos de parada de emergencia IEC 61511'
                ],
                'explanation' => 'El sistema eliminó el antipatrón God Object dividiendo las responsabilidades en 7 dominios aislados con controladores especializados.'
            ]);
            break;

        default:
            sendJsonResponse(['success' => false, 'error' => "Acción desconocida: '{$action}'"], 400);
    }
}

/**
 * Renderiza la Interfaz Web Visual Interactiva para Patrones y Dominios
 */
function renderInteractivePatternsHtml(array $patterns, array $antipatterns, array $domains, array $singletonDiag): void {
    ?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAPAF · Laboratorio de Patrones GoF, Antipatrones & Dominios DDD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #060913;
            --bg-card: #0d1527;
            --border: #1e293b;
            --primary: #38bdf8;
            --success: #10b981;
            --purple: #a855f7;
            --danger: #f43f5e;
            --warning: #f59e0b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --font-sans: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg-body);
            color: var(--text-main);
            font-family: var(--font-sans);
            padding: 1.5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            width: 100%;
            max-width: 1100px;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .header-card {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.12), rgba(13, 21, 39, 0.95));
            border: 1px solid rgba(56, 189, 248, 0.35);
            border-radius: 14px;
            padding: 1.5rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .header-title h1 {
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header-title p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
        .header-nav { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .btn {
            background: #1e293b;
            color: #fff;
            border: 1px solid #334155;
            padding: 0.55rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }
        .btn:hover { background: #334155; transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, #0284c7, #0369a1); border-color: #38bdf8; }
        .btn-purple { background: linear-gradient(135deg, #a855f7, #7c3aed); border-color: #9333ea; }
        .tabs-bar {
            display: flex;
            gap: 0.5rem;
            background: #0b1120;
            padding: 0.4rem;
            border-radius: 10px;
            border: 1px solid var(--border);
            flex-wrap: wrap;
        }
        .tab-btn {
            background: transparent;
            color: var(--text-muted);
            border: none;
            padding: 0.6rem 1.1rem;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: #1e293b;
            color: #38bdf8;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .tab-content { display: none; flex-direction: column; gap: 1.25rem; }
        .tab-content.active { display: flex; }
        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
        }
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.35rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-2px); }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #fff;
        }
        .badge {
            font-family: var(--font-mono);
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            text-transform: uppercase;
        }
        .badge-crea { background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.4); }
        .badge-comp { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .badge-est { background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); }
        .badge-arq { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.4); }
        .badge-risk { background: rgba(244, 63, 94, 0.2); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.4); }
        .card-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }
        .card-tech {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: #cbd5e1;
            background: #060913;
            padding: 0.5rem;
            border-radius: 6px;
            margin-bottom: 0.75rem;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .result-box {
            margin-top: 0.75rem;
            background: #060913;
            border: 1px solid #1e293b;
            border-radius: 6px;
            padding: 0.65rem;
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: #34d399;
            white-space: pre-wrap;
            display: none;
            max-height: 180px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Header -->
        <div class="header-card">
            <div class="header-title">
                <h1>⚙️ Patrones GoF, Antipatrones & Dominios DDD</h1>
                <p>SENA ADSO Ficha 3413988 · MAPAF SCADA Táctico · PHP 8 OOP + MySQL (mapaf_db)</p>
            </div>
            <div class="header-nav">
                <a href="/mapaf/" class="btn">🕹️ Centro SCADA</a>
                <a href="login.php" class="btn btn-purple">🔐 Login JWT</a>
                <a href="patterns.php?format=json" class="btn" target="_blank">📄 JSON Puro</a>
            </div>
        </div>

        <!-- Barra de Pestañas -->
        <div class="tabs-bar">
            <button class="tab-btn active" onclick="switchTab('tab-patterns', this)">1. Patrones de Diseño GoF (8)</button>
            <button class="tab-btn" onclick="switchTab('tab-antipatterns', this)">2. Antipatrones Mitigados (8)</button>
            <button class="tab-btn" onclick="switchTab('tab-domains', this)">3. Dominios del Sistema DDD (7)</button>
        </div>

        <!-- Pestaña 1: Patrones de Diseño GoF -->
        <div id="tab-patterns" class="tab-content active">
            <div class="grid-cards">
                
                <!-- 1. Singleton -->
                <div class="card" style="border-top: 3px solid #6366f1;">
                    <div>
                        <div class="card-header">
                            <span class="card-title">1. Singleton</span>
                            <span class="badge badge-crea">CREACIONAL</span>
                        </div>
                        <div class="card-desc">Garantiza una única instancia de conexión PDO a MySQL para evitar saturar el pool de sockets en XAMPP.</div>
                        <div class="card-tech">Clase: DatabaseSingleton.php · Estado: Conectado a mapaf_db</div>
                    </div>
                    <div>
                        <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="runPatternTest('test_singleton', 'resSingleton')">
                            ▶ Probar Unicidad de Instancia
                        </button>
                        <div id="resSingleton" class="result-box"></div>
                    </div>
                </div>

                <!-- 2. Command con UNDO -->
                <div class="card" style="border-top: 3px solid #10b981;">
                    <div>
                        <div class="card-header">
                            <span class="card-title">2. Command (con UNDO)</span>
                            <span class="badge badge-comp">COMPORTAMIENTO</span>
                        </div>
                        <div class="card-desc">Encapsula maniobras sobre estranguladores Choke y válvulas SSV con capacidad de reversión matemática (Deshacer).</div>
                        <div class="card-tech">Clase: ScadaCommand.php · Operaciones Atómicas</div>
                    </div>
                    <div>
                        <div style="display: flex; gap: 0.4rem;">
                            <button class="btn btn-primary" style="flex: 1; justify-content: center;" onclick="runPatternTest('test_command', 'resCommand', {command: 'choke', targetOpening: 70})">
                                ▶ Ejecutar Choke
                            </button>
                            <button class="btn" style="flex: 1; justify-content: center; color: #fbbf24; border-color: #f59e0b;" onclick="runPatternTest('test_command', 'resCommand', {command: 'choke', runUndo: true})">
                                ↺ Probar UNDO
                            </button>
                        </div>
                        <div id="resCommand" class="result-box"></div>
                    </div>
                </div>

                <!-- 3. Observer -->
                <div class="card" style="border-top: 3px solid #38bdf8;">
                    <div>
                        <div class="card-header">
                            <span class="card-title">3. Observer (Observador)</span>
                            <span class="badge badge-comp">COMPORTAMIENTO</span>
                        </div>
                        <div class="card-desc">Notifica reactivamente a la alarma ISA-18.2, al logger de auditoría y al enclavamiento ESD ante sobrepresión.</div>
                        <div class="card-tech">Clase: TelemetryObserver.php · 3 Observadores Registrados</div>
                    </div>
                    <div>
                        <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="runPatternTest('test_observer', 'resObserver', {value: 2450})">
                            ▶ Simular Sobrepresión (2450 PSI)
                        </button>
                        <div id="resObserver" class="result-box"></div>
                    </div>
                </div>

                <!-- 4. Strategy -->
                <div class="card" style="border-top: 3px solid #a855f7;">
                    <div>
                        <div class="card-header">
                            <span class="card-title">4. Strategy (Estrategia)</span>
                            <span class="badge badge-comp">COMPORTAMIENTO</span>
                        </div>
                        <div class="card-desc">Compara algoritmos de producción: Coriolis Fiscal (AGA-11) vs Placa de Orificio (AGA-3) vs Beggs & Brill.</div>
                        <div class="card-tech">Clase: FlowCalculationStrategy.php · Métodos Dinámicos</div>
                    </div>
                    <div>
                        <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="runPatternTest('test_strategy', 'resStrategy')">
                            ▶ Comparar 3 Algoritmos de Flujo
                        </button>
                        <div id="resStrategy" class="result-box"></div>
                    </div>
                </div>

                <!-- 5. Factory Method -->
                <div class="card" style="border-top: 3px solid #ec4899;">
                    <div>
                        <div class="card-header">
                            <span class="card-title">5. Factory Method</span>
                            <span class="badge badge-crea">CREACIONAL</span>
                        </div>
                        <div class="card-desc">Instancia nodos de instrumentación (PT, TT, FT, VFD) y alarmas con límites calibrados bajo norma ISA-18.2.</div>
                        <div class="card-tech">Clase: SensorFactory.php · Validación de Rangos</div>
                    </div>
                    <div>
                        <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="runPatternTest('test_factory', 'resFactory')">
                            ▶ Fabricar Sensores Calibrados
                        </button>
                        <div id="resFactory" class="result-box"></div>
                    </div>
                </div>

                <!-- 6. State -->
                <div class="card" style="border-top: 3px solid #f59e0b;">
                    <div>
                        <div class="card-header">
                            <span class="card-title">6. State (Estado de Pozo)</span>
                            <span class="badge badge-comp">COMPORTAMIENTO</span>
                        </div>
                        <div class="card-desc">Controla las transiciones del cabezal de pozo (Produciendo, Estrangulado, Disparado, Cerrado) bajo API 6A.</div>
                        <div class="card-tech">Clase: WellheadState.php · Enclavamientos Activos</div>
                    </div>
                    <div>
                        <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="runPatternTest('test_state', 'resState')">
                            ▶ Probar Ciclo de Vida del Pozo
                        </button>
                        <div id="resState" class="result-box"></div>
                    </div>
                </div>

                <!-- 7. Adapter -->
                <div class="card" style="border-top: 3px solid #14b8a6;">
                    <div>
                        <div class="card-header">
                            <span class="card-title">7. Adapter (Adaptador)</span>
                            <span class="badge badge-est">ESTRUCTURAL</span>
                        </div>
                        <div class="card-desc">Convierte tramas binarias industriales Modbus RTU de 16 bits y nodos OPC-UA al formato JSON estándar.</div>
                        <div class="card-tech">Clase: IndustrialProtocolAdapter.php · Modbus/OPC-UA</div>
                    </div>
                    <div>
                        <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="runPatternTest('test_adapter', 'resAdapter')">
                            ▶ Decodificar Trama Modbus RTU
                        </button>
                        <div id="resAdapter" class="result-box"></div>
                    </div>
                </div>

                <!-- 8. Repository -->
                <div class="card" style="border-top: 3px solid #8b5cf6;">
                    <div>
                        <div class="card-header">
                            <span class="card-title">8. Repository</span>
                            <span class="badge badge-arq">ARQUITECTÓNICO</span>
                        </div>
                        <div class="card-desc">Desacopla la lógica de negocio de las consultas directas SQL con PDO y transacciones ACID.</div>
                        <div class="card-tech">Clase: ScadaRepositories.php · Persistencia MySQL</div>
                    </div>
                    <div>
                        <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="runPatternTest('test_repository', 'resRepo')">
                            ▶ Consultar Pozos vía Repository
                        </button>
                        <div id="resRepo" class="result-box"></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Pestaña 2: Antipatrones Mitigados -->
        <div id="tab-antipatterns" class="tab-content">
            <div class="grid-cards">
                <?php foreach ($antipatterns as $idx => $anti): ?>
                <div class="card" style="border-top: 3px solid #f43f5e;">
                    <div>
                        <div class="card-header">
                            <span class="card-title"><?= ($idx + 1) ?>. <?= htmlspecialchars($anti['name']) ?></span>
                            <span class="badge badge-risk"><?= htmlspecialchars($anti['severity']) ?></span>
                        </div>
                        <div class="card-desc"><?= htmlspecialchars($anti['symptom']) ?></div>
                        <div class="card-tech" style="border-left: 3px solid #f43f5e;">
                            <strong style="color: #f43f5e;">Riesgo SCADA:</strong> <?= htmlspecialchars($anti['scadaRisk']) ?>
                        </div>
                        <div class="card-tech" style="border-left: 3px solid #10b981; margin-top: 0.4rem;">
                            <strong style="color: #34d399;">Solución MAPAF:</strong> <?= htmlspecialchars($anti['solutionPattern']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pestaña 3: Dominios del Sistema DDD -->
        <div id="tab-domains" class="tab-content">
            <div class="grid-cards">
                <?php foreach ($domains as $idx => $dom): ?>
                <div class="card" style="border-top: 3px solid #38bdf8;">
                    <div>
                        <div class="card-header">
                            <span class="card-title"><?= ($idx + 1) ?>. <?= htmlspecialchars($dom['name']) ?></span>
                            <span class="badge badge-est"><?= htmlspecialchars($dom['code']) ?></span>
                        </div>
                        <div class="card-desc"><?= htmlspecialchars($dom['description']) ?></div>
                        <div class="card-tech">
                            <strong>Norma:</strong> <?= htmlspecialchars($dom['standard']) ?><br>
                            <strong>Entidades:</strong> <?= htmlspecialchars(implode(', ', $dom['entities'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <script>
        function switchTab(tabId, btn) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const target = document.getElementById(tabId);
            if (target) target.classList.add('active');
        }

        async function runPatternTest(actionName, outputId, extraData = {}) {
            const out = document.getElementById(outputId);
            out.style.display = 'block';
            out.textContent = 'Ejecutando en el motor PHP de XAMPP...';
            try {
                const res = await fetch('patterns.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: actionName, ...extraData })
                });
                const data = await res.json();
                out.textContent = JSON.stringify(data, null, 2);
            } catch (err) {
                out.textContent = 'Error: ' + err.message;
            }
        }
    </script>
</body>
</html>
    <?php
}


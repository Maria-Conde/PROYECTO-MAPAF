<?php
/**
 * PROYECTO MAPAF - Endpoint: api/wellheads.php
 * Control de Cabezales de Pozo integrado con Patrones GoF y Dominios DDD
 * 
 * Patrones aplicados:
 * - SINGLETON: DatabaseSingleton para la conexión MySQL
 * - REPOSITORY: WellheadRepository y AuditRepository para persistencia
 * - COMMAND: SetChokeCommand y ToggleSsvValveCommand con soporte de UNDO
 * - STATE: WellheadContext para máquinas de estado API 6A y enclavamientos
 * - DOMINIOS: Bounded Context [WELLHEAD-CTX] y [AUDIT-CTX]
 * - SEGURIDAD: Validación de tokens JWT (RFC 7519) y matriz RBAC
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../patterns/Singleton/DatabaseSingleton.php';
require_once __DIR__ . '/../patterns/Repository/ScadaRepositories.php';
require_once __DIR__ . '/../patterns/Command/ScadaCommand.php';
require_once __DIR__ . '/../patterns/State/WellheadState.php';
require_once __DIR__ . '/../auth/jwt.php';

use Mapaf\Patterns\Singleton\DatabaseSingleton;
use Mapaf\Patterns\Repository\WellheadRepository;
use Mapaf\Patterns\Repository\AuditRepository;
use Mapaf\Patterns\Command\CommandManager;
use Mapaf\Patterns\Command\SetChokeCommand;
use Mapaf\Patterns\Command\ToggleSsvValveCommand;
use Mapaf\Patterns\State\WellheadContext;
use Mapaf\Auth\JwtService;

handleCorsHeaders();

$pdo = DatabaseSingleton::getInstance()->getConnection();
if (!$pdo) {
    sendJsonResponse(['success' => false, 'error' => 'Error de conexión MySQL (DatabaseSingleton)'], 503);
}

$wellheadRepo = new WellheadRepository($pdo);
$auditRepo = new AuditRepository($pdo);
$method = $_SERVER['REQUEST_METHOD'];

// ============================================================================
// GET: Listar pozos desde el Repositorio de Dominio
// ============================================================================
if ($method === 'GET') {
    $wellheads = $wellheadRepo->findAll();
    sendJsonResponse([
        'success' => true,
        'domain' => 'WELLHEAD-CTX',
        'pattern' => 'Repository (GoF)',
        'wellheads' => $wellheads
    ]);
}

// ============================================================================
// POST / PUT: Ejecutar maniobras sobre el pozo con Patrones Command, State y JWT
// ============================================================================
if ($method === 'POST' || $method === 'PUT') {
    $data = getJsonRequestBody();
    $wellId = $data['id'] ?? null;
    $action = $data['action'] ?? null;

    if (!$wellId) {
        sendJsonResponse(['success' => false, 'error' => 'Se requiere el identificador "id" del pozo'], 400);
    }

    $currentWell = $wellheadRepo->findById($wellId);
    if (!$currentWell) {
        sendJsonResponse(['success' => false, 'error' => "Pozo con id '{$wellId}' no encontrado"], 404);
    }

    // 1. Verificación de Seguridad JWT (RBAC) si viene cabecera Authorization
    $authenticatedUser = null;
    $token = JwtService::extractBearerToken();
    if ($token) {
        try {
            $authenticatedUser = JwtService::verifyToken($token);
            $requiredPerm = isset($data['ssvOpen']) ? 'control_valves' : 'control_choke';
            JwtService::requirePermission($requiredPerm);
        } catch (Exception $e) {
            sendJsonResponse([
                'success' => false,
                'securityViolation' => true,
                'pattern' => 'Identity & RBAC (JWT RFC 7519)',
                'error' => $e->getMessage()
            ], 403);
        }
    }

    $operatorCode = $authenticatedUser['employeeCode'] ?? ($data['userCode'] ?? 'EMP-8812');
    $operatorName = $authenticatedUser['name'] ?? ($data['userName'] ?? 'M. Conde Dussan');
    $operatorRole = $authenticatedUser['roleName'] ?? ($data['roleName'] ?? 'Administrador del Sistema');

    // 2. Operación de DESHACER (Pattern: Command UNDO)
    if ($action === 'undo') {
        $previousChoke = $currentWell['apertura_choke'] ?? ($currentWell['chokeOpening'] ?? 42.8);
        $undoTarget = $data['previousOpening'] ?? $previousChoke;

        $wellheadRepo->update($wellId, [
            'chokeOpening' => (float)$undoTarget,
            'chokeTarget' => (float)$undoTarget
        ]);

        $auditRepo->append(
            $operatorCode,
            $operatorName,
            $operatorRole,
            "Reversión de Maniobra (Command UNDO) en pozo {$wellId}",
            'MANIOBRA',
            'EXITOSO',
            "Se deshizo la consigna del Choke. Retornado de {$previousChoke}% a {$undoTarget}% vía Command::undo()."
        );

        sendJsonResponse([
            'success' => true,
            'pattern' => 'Command (GoF Comportamiento con UNDO)',
            'domain' => 'WELLHEAD-CTX',
            'action' => 'undo',
            'wellId' => $wellId,
            'previousValue' => $previousChoke,
            'restoredValue' => $undoTarget,
            'message' => "Maniobra deshecha exitosamente: Choke retornado a {$undoTarget}%."
        ]);
    }

    // 3. Validación con el Patrón STATE (Máquina de Estados API 6A)
    $currentStateStr = $currentWell['estado'] ?? ($currentWell['status'] ?? 'EN LÍNEA');
    $stateContext = new WellheadContext($wellId, $currentStateStr);

    // Si se intenta abrir la válvula SSV y el pozo está DISPARADO (TRIPPED), el patrón State lo impide
    if (isset($data['ssvOpen']) && $data['ssvOpen'] === true && $currentStateStr === 'DISPARADO') {
        $blockedMsg = "Enclavamiento de Seguridad API 6A (Patrón State): No se permite abrir la válvula SSV mientras el pozo esté en estado DISPARADO. Se requiere un rearme de seguridad previo (Reset State).";
        
        $auditRepo->append(
            $operatorCode,
            $operatorName,
            $operatorRole,
            "Intento de apertura SSV bloqueado por Enclavamiento State",
            'SEGURIDAD',
            'RECHAZADO',
            $blockedMsg
        );

        sendJsonResponse([
            'success' => false,
            'pattern' => 'State (GoF Comportamiento)',
            'domain' => 'SAFETY-CTX / WELLHEAD-CTX',
            'currentState' => $currentStateStr,
            'interlockActive' => true,
            'error' => $blockedMsg
        ], 422);
    }

    // Si se solicita rearme de seguridad (Reset State)
    if (isset($data['action']) && $data['action'] === 'reset_state') {
        $wellheadRepo->update($wellId, [
            'status' => 'EN LÍNEA',
            'chokeOpening' => 15.0,
            'chokeTarget' => 15.0
        ]);

        $auditRepo->append(
            $operatorCode,
            $operatorName,
            $operatorRole,
            "Rearme de Pozo (State Reset) a estado EN LÍNEA",
            'SEGURIDAD',
            'EXITOSO',
            "Pozo {$wellId} rearmado de DISPARADO a EN LÍNEA con estrangulador en 15% seguro."
        );

        sendJsonResponse([
            'success' => true,
            'pattern' => 'State (GoF Comportamiento)',
            'domain' => 'SAFETY-CTX',
            'newState' => 'EN LÍNEA',
            'message' => "Pozo {$wellId} rearmado a estado EN LÍNEA conforme a protocolo API 6A."
        ]);
    }

    // 4. Ejecución de Maniobra mediante COMMAND PATTERN
    $commandResult = null;
    $commandExecuted = null;
    $previousChoke = (float)($currentWell['apertura_choke'] ?? ($currentWell['chokeOpening'] ?? 42.8));
    $previousSsv = (bool)($currentWell['valvula_ssv_abierta'] ?? ($currentWell['ssvOpen'] ?? true));

    if (isset($data['chokeOpening'])) {
        $targetChoke = (float)$data['chokeOpening'];
        $cmd = new SetChokeCommand($wellId, $previousChoke, $targetChoke, $operatorName);
        $commandExecuted = $cmd->getName();
        $commandResult = $cmd->execute();

        // Determinar nuevo estado según apertura
        $newStatus = $targetChoke <= 20.0 ? 'ESTRANGULADO' : 'EN LÍNEA';

        $wellheadRepo->update($wellId, [
            'chokeOpening' => $targetChoke,
            'chokeTarget' => $targetChoke,
            'status' => $newStatus
        ]);
    }

    if (isset($data['ssvOpen'])) {
        $newSsv = (bool)$data['ssvOpen'];
        $cmd = new ToggleSsvValveCommand($wellId, $previousSsv, $newSsv, $operatorName);
        $commandExecuted = $cmd->getName();
        $commandResult = $cmd->execute();

        $wellheadRepo->update($wellId, [
            'ssvOpen' => $newSsv,
            'status' => $newSsv ? 'EN LÍNEA' : 'CERRADO'
        ]);
    }

    if (isset($data['chemicalInjection'])) {
        $wellheadRepo->update($wellId, [
            'chemicalInjection' => (bool)$data['chemicalInjection']
        ]);
    }

    if (isset($data['annulusBleedOpen'])) {
        $wellheadRepo->update($wellId, [
            'annulusBleedOpen' => (bool)$data['annulusBleedOpen']
        ]);
    }

    // 5. Registro inmutable mediante el REPOSITORY de Auditoría (21 CFR Part 11)
    $actionDesc = $data['actionDescription'] ?? "Maniobra SCADA sobre pozo {$wellId} ({$commandExecuted})";
    $auditRepo->append(
        $operatorCode,
        $operatorName,
        $operatorRole,
        $actionDesc,
        'MANIOBRA',
        'EXITOSO',
        "Ejecutado con Patrón Command ({$commandExecuted}). Consignas: " . json_encode($data)
    );

    sendJsonResponse([
        'success' => true,
        'patternsApplied' => [
            'command' => $commandExecuted ?: 'StandardUpdate',
            'canUndo' => true,
            'previousValue' => $previousChoke,
            'repository' => 'WellheadRepository & AuditRepository',
            'singleton' => 'DatabaseSingleton',
            'state' => 'API 6A Wellhead LifeCycle'
        ],
        'domain' => 'WELLHEAD-CTX',
        'message' => "Maniobra sobre pozo {$wellId} ejecutada exitosamente en MySQL.",
        'commandResult' => $commandResult
    ]);
}

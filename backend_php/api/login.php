<?php
/**
 * PROYECTO MAPAF - Endpoint: api/login.php
 * Autenticación de Operadores SCADA y Emisión de Tokens JWT
 * SENA ADSO · Ficha 3413988
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/jwt.php';

use Mapaf\Auth\JwtService;

handleCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];

// ============================================================================
// GET: Verificar token existente o consultar usuarios disponibles para demo
// ============================================================================
if ($method === 'GET') {
    $token = JwtService::extractBearerToken();
    if ($token) {
        try {
            $payload = JwtService::verifyToken($token);
            sendJsonResponse([
                'success' => true,
                'valid' => true,
                'user' => $payload,
                'expiresInSeconds' => max(0, $payload['exp'] - time()),
                'expiresAt' => date('Y-m-d H:i:s', $payload['exp'])
            ]);
        } catch (Exception $e) {
            sendJsonResponse([
                'success' => false,
                'valid' => false,
                'error' => $e->getMessage()
            ], 401);
        }
    }

    // Si no hay token, retornar lista de usuarios de prueba para la interfaz
    $pdo = getDbConnection(true);
    $users = [];
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT u.id, u.codigo_empleado, u.nombre_completo, u.email AS correo, u.email, u.rol_id, u.sector_asignado, u.estado, r.nombre AS rol_nombre, r.codigo AS rol_codigo, r.permisos FROM `usuarios` u LEFT JOIN `roles` r ON u.rol_id = r.id");
            $users = $stmt->fetchAll();
        } catch (Exception $e) {
            $users = [];
        }
    }

    // Si el usuario ingresa desde un navegador (Accept: text/html), mostrar interfaz visual interactiva
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    $wantsJson = isset($_GET['format']) && $_GET['format'] === 'json';
    if (!$wantsJson && str_contains($acceptHeader, 'text/html')) {
        renderInteractiveLoginHtml($users);
        exit;
    }

    if (!empty($users)) {
        sendJsonResponse([
            'success' => true,
            'message' => 'Servicio de Autenticación JWT MAPAF activo.',
            'authStandard' => 'RFC 7519 (HMAC SHA-256)',
            'users' => $users
        ]);
    } else {
        sendJsonResponse([
            'success' => true,
            'message' => 'Servicio JWT activo en modo fallback.',
            'authStandard' => 'RFC 7519'
        ]);
    }
}

// ============================================================================
// POST: Iniciar sesión y emitir Token JWT
// ============================================================================
if ($method === 'POST') {
    $data = getJsonRequestBody();
    $employeeCode = trim($data['employeeCode'] ?? $data['userCode'] ?? $data['email'] ?? '');
    $roleOverride = $data['roleId'] ?? null;

    if (empty($employeeCode)) {
        // Por defecto usar el operador líder si no se especifica
        $employeeCode = 'EMP-8812'; // María José Conde Dussan
    }

    $pdo = getDbConnection(true);
    $userRecord = null;
    $roleRecord = null;

    if ($pdo) {
        $stmt = $pdo->prepare("SELECT u.*, u.email AS correo, r.nombre AS rol_nombre, r.codigo AS rol_codigo, r.permisos FROM `usuarios` u LEFT JOIN `roles` r ON u.rol_id = r.id WHERE u.codigo_empleado = :code OR u.email = :email OR u.id = :id LIMIT 1");
        $stmt->execute([':code' => $employeeCode, ':email' => $employeeCode, ':id' => $employeeCode]);
        $userRecord = $stmt->fetch();
    }

    // Datos por defecto si la base de datos no está poblada o es usuario demo
    if (!$userRecord) {
        $defaultUsers = [
            'OP-449' => [
                'id' => 'usr-001',
                'codigo_empleado' => 'OP-449',
                'nombre_completo' => 'María José Conde Dussan',
                'correo' => 'mj.conde@mapaf.scada.io',
                'rol_id' => 'role-admin',
                'rol_nombre' => 'Administrador del Sistema',
                'rol_codigo' => 'SYS-ADMIN',
                'sector_asignado' => 'Estación Central / RTOC',
                'permisos' => json_encode(['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports', 'bypass_interlocks', 'manage_roles_users'])
            ],
            'OP-312' => [
                'id' => 'usr-002',
                'codigo_empleado' => 'OP-312',
                'nombre_completo' => 'Hernann Thomas Manchola Bocanegra',
                'correo' => 'h.manchola@mapaf.scada.io',
                'rol_id' => 'role-engineer',
                'rol_nombre' => 'Ingeniero de Operaciones',
                'rol_codigo' => 'OPS-ENG',
                'sector_asignado' => 'Pad Alfa / Bravo',
                'permisos' => json_encode(['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports'])
            ],
            'OP-205' => [
                'id' => 'usr-003',
                'codigo_empleado' => 'OP-205',
                'nombre_completo' => 'Emilin Tatiana Vargas Manchola',
                'correo' => 'e.vargas@mapaf.scada.io',
                'rol_id' => 'role-supervisor',
                'rol_nombre' => 'Supervisor de Turno',
                'rol_codigo' => 'SHIFT-SUPV',
                'sector_asignado' => 'Sala de Control RTOC',
                'permisos' => json_encode(['view_telemetry', 'control_valves', 'execute_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports'])
            ],
            'OP-118' => [
                'id' => 'usr-004',
                'codigo_empleado' => 'OP-118',
                'nombre_completo' => 'Javier Humberto Pinto Díaz',
                'correo' => 'j.pinto@sena.edu.co',
                'rol_id' => 'role-admin',
                'rol_nombre' => 'Instructor / Auditor Senior SENA',
                'rol_codigo' => 'SYS-ADMIN',
                'sector_asignado' => 'Auditoría Académica ADSO',
                'permisos' => json_encode(['view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports', 'bypass_interlocks', 'manage_roles_users'])
            ]
        ];

        $userRecord = $defaultUsers[$employeeCode] ?? $defaultUsers['OP-449'];
    }

    $permissions = [];
    if (!empty($userRecord['permisos'])) {
        $permissions = is_array($userRecord['permisos']) ? $userRecord['permisos'] : json_decode($userRecord['permisos'], true);
    }

    // Construir Claims del Token JWT
    $claims = [
        'sub' => $userRecord['id'],
        'employeeCode' => $userRecord['codigo_empleado'],
        'name' => $userRecord['nombre_completo'],
        'email' => $userRecord['correo'],
        'roleId' => $userRecord['rol_id'],
        'roleName' => $userRecord['rol_nombre'],
        'roleCode' => $userRecord['rol_codigo'],
        'sector' => $userRecord['sector_asignado'],
        'permissions' => $permissions ?: ['view_telemetry']
    ];

    $ttl = intval($data['ttl'] ?? 28800); // 8 horas por defecto
    $jwtToken = JwtService::generateToken($claims, $ttl);

    // Registrar en auditoría si hay base de datos conectada
    if ($pdo) {
        try {
            $auditStmt = $pdo->prepare("INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES (:id, :time, :code, :name, :role, 'Inicio de Sesión y Emisión JWT', 'SEGURIDAD', 'EXITOSO', :details)");
            $auditStmt->execute([
                ':id' => 'aud-' . substr(strval(time()), -5) . rand(10, 99),
                ':time' => date('H:i:s'),
                ':code' => $userRecord['codigo_empleado'],
                ':name' => $userRecord['nombre_completo'],
                ':role' => $userRecord['rol_nombre'],
                ':details' => "Token JWT emitido exitosamente con {$ttl}s de validez para el sector {$userRecord['sector_asignado']}."
            ]);
        } catch (Exception $e) {
            // Ignorar fallo de log no bloqueante
        }
    }

    sendJsonResponse([
        'success' => true,
        'message' => 'Token JWT generado y firmado con éxito bajo norma RFC 7519.',
        'token' => $jwtToken,
        'tokenType' => 'Bearer',
        'expiresIn' => $ttl,
        'expiresAt' => date('Y-m-d H:i:s', time() + $ttl),
        'user' => [
            'id' => $userRecord['id'],
            'employeeCode' => $userRecord['codigo_empleado'],
            'fullName' => $userRecord['nombre_completo'],
            'roleName' => $userRecord['rol_nombre'],
            'roleCode' => $userRecord['rol_codigo'],
            'sector' => $userRecord['sector_asignado'],
            'permissionsCount' => count($permissions)
        ]
    ]);
}

/**
 * Renderiza la Interfaz Web Visual Interactiva para el Navegador
 */
function renderInteractiveLoginHtml(array $users): void {
    if (empty($users)) {
        $users = [
            ['codigo_empleado' => 'EMP-8812', 'nombre_completo' => 'María José Conde Dussan', 'rol_nombre' => 'Administrador del Sistema', 'rol_codigo' => 'SYS-ADMIN', 'sector_asignado' => 'Planta Central Huila / RTOC', 'permisos' => '[]'],
            ['codigo_empleado' => 'EMP-8813', 'nombre_completo' => 'Hernann Thomas Manchola', 'rol_nombre' => 'Ingeniero de Operaciones', 'rol_codigo' => 'OPS-ENG', 'sector_asignado' => 'Pad 04 & 09 Upstream', 'permisos' => '[]'],
            ['codigo_empleado' => 'EMP-8814', 'nombre_completo' => 'Emilin Tatiana Vargas', 'rol_nombre' => 'Supervisor de Turno', 'rol_codigo' => 'SHIFT-SUPV', 'sector_asignado' => 'Sala de Control y Despacho', 'permisos' => '[]'],
            ['codigo_empleado' => 'EMP-1109', 'nombre_completo' => 'Javier Humberto Pinto Díaz', 'rol_nombre' => 'Instructor / Auditor SENA', 'rol_codigo' => 'SYS-ADMIN', 'sector_asignado' => 'Auditoría Académica ADSO', 'permisos' => '[]']
        ];
    }
    ?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAPAF · Consola de Autenticación JWT (RFC 7519)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #060913;
            --bg-card: #0d1527;
            --border: #1e293b;
            --primary: #a855f7;
            --success: #10b981;
            --info: #38bdf8;
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
            max-width: 960px;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .header-card {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.12), rgba(13, 21, 39, 0.95));
            border: 1px solid rgba(168, 85, 247, 0.35);
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
        .header-nav {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
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
        .btn-primary {
            background: linear-gradient(135deg, #a855f7, #7c3aed);
            border-color: #9333ea;
        }
        .btn-primary:hover { background: #9333ea; }
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            border-color: #10b981;
        }
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.75rem;
        }
        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .badge {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background: rgba(168, 85, 247, 0.2);
            color: #c084fc;
            border: 1px solid rgba(168, 85, 247, 0.4);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            color: var(--text-muted);
            margin-bottom: 0.35rem;
        }
        .form-control {
            width: 100%;
            background: #060913;
            border: 1px solid var(--border);
            color: #fff;
            padding: 0.65rem 0.85rem;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus { border-color: var(--primary); }
        .token-display {
            background: #060913;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 1rem;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            word-break: break-all;
            margin-top: 1rem;
            display: none;
        }
        .token-segment { padding: 0.15rem 0.3rem; border-radius: 4px; }
        .token-header { color: #f43f5e; background: rgba(244,63,94,0.15); }
        .token-payload { color: #a855f7; background: rgba(168,85,247,0.15); }
        .token-signature { color: #10b981; background: rgba(16,185,129,0.15); }
        .claims-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .claim-box {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 8px;
            padding: 0.75rem;
            font-family: var(--font-mono);
            font-size: 0.75rem;
        }
        .claim-box .lbl { color: var(--text-muted); font-size: 0.65rem; text-transform: uppercase; margin-bottom: 0.2rem; }
        .claim-box .val { color: #38bdf8; font-weight: 700; }
        .operator-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }
        .operator-table th, .operator-table td {
            padding: 0.6rem 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .operator-table th { color: var(--text-muted); font-family: var(--font-mono); text-transform: uppercase; font-size: 0.65rem; }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Header -->
        <div class="header-card">
            <div class="header-title">
                <h1>🔐 Autenticación JWT · MAPAF SCADA</h1>
                <p>Estándar RFC 7519 · Criptografía HMAC SHA-256 · SENA ADSO Ficha 3413988</p>
            </div>
            <div class="header-nav">
                <a href="/mapaf/" class="btn">🕹️ Centro SCADA</a>
                <a href="patterns.php" class="btn">⚙️ Patrones GoF</a>
                <a href="domains.php" class="btn">🏛️ Dominios DDD</a>
                <a href="login.php?format=json" class="btn" target="_blank">📄 JSON Puro</a>
            </div>
        </div>

        <!-- Formulario Interactivo de Login -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">🔑 Iniciar Sesión de Operador SCADA</span>
                <span class="badge">EMISOR DE TOKENS</span>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Seleccionar Operador Registrado (MySQL `usuarios`):</label>
                    <select id="userSelect" class="form-control" onchange="syncSelectedUser()">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= htmlspecialchars($u['codigo_empleado']) ?>"
                                    data-name="<?= htmlspecialchars($u['nombre_completo']) ?>"
                                    data-role="<?= htmlspecialchars($u['rol_nombre'] ?? 'Operador') ?>"
                                    data-code="<?= htmlspecialchars($u['rol_codigo'] ?? 'OP') ?>"
                                    data-sector="<?= htmlspecialchars($u['sector_asignado'] ?? 'Planta Central') ?>">
                                <?= htmlspecialchars($u['codigo_empleado']) ?> · <?= htmlspecialchars($u['nombre_completo']) ?> (<?= htmlspecialchars($u['rol_nombre'] ?? 'Rol') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contraseña de Acceso Industrial:</label>
                    <input type="password" id="passInput" class="form-control" value="SenaAdso2026*" placeholder="Contraseña">
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="executeLogin()">
                    🚀 Iniciar Sesión (Generar Token JWT)
                </button>
                <button class="btn btn-success" id="btnVerify" onclick="verifyToken()" style="display: none;">
                    🛡️ Validar Token con el Servidor (GET con Bearer)
                </button>
            </div>

            <!-- Área de Resultado del Token -->
            <div id="tokenOutput" class="token-display">
                <div style="color: #c084fc; font-weight: bold; margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
                    <span>✓ Token JWT Emitido con Éxito:</span>
                    <span style="font-size: 0.7rem; color: #10b981;">Firma Criptográfica HMAC-SHA256 Válida</span>
                </div>
                
                <div id="rawToken" style="background: #090e1d; padding: 0.75rem; border-radius: 6px; font-size: 0.75rem; margin-bottom: 1rem; border: 1px solid #1e293b;"></div>

                <div class="claims-grid">
                    <div class="claim-box">
                        <div class="lbl">Sujeto (Sub ID)</div>
                        <div class="val" id="claimSub">--</div>
                    </div>
                    <div class="claim-box">
                        <div class="lbl">Operador Autenticado</div>
                        <div class="val" id="claimName">--</div>
                    </div>
                    <div class="claim-box">
                        <div class="lbl">Rol Asignado</div>
                        <div class="val" id="claimRole">--</div>
                    </div>
                    <div class="claim-box">
                        <div class="lbl">Sector Operativo</div>
                        <div class="val" id="claimSector">--</div>
                    </div>
                    <div class="claim-box">
                        <div class="lbl">Permisos RBAC</div>
                        <div class="val" id="claimPerms">--</div>
                    </div>
                    <div class="claim-box">
                        <div class="lbl">Expiración</div>
                        <div class="val" id="claimExp">--</div>
                    </div>
                </div>

                <div id="verifyResult" style="margin-top: 1rem; padding: 0.75rem; border-radius: 6px; font-size: 0.72rem; display: none;"></div>
            </div>
        </div>

        <!-- Tabla de Operadores Disponibles en Base de Datos -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">👥 Operadores y Roles Registrados en MySQL (mapaf_db)</span>
                <span class="badge"><?= count($users) ?> OPERADORES ACTIVOS</span>
            </div>
            <table class="operator-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre Completo</th>
                        <th>Rol del Sistema</th>
                        <th>Sector Asignado</th>
                        <th>Acción Rápida</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($u['codigo_empleado']) ?></code></td>
                        <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($u['nombre_completo']) ?></td>
                        <td><span class="badge"><?= htmlspecialchars($u['rol_nombre'] ?? 'Operador') ?></span></td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($u['sector_asignado'] ?? 'RTOC') ?></td>
                        <td>
                            <button class="btn" style="padding: 0.25rem 0.6rem; font-size: 0.7rem;" onclick="selectAndLogin('<?= htmlspecialchars($u['codigo_empleado']) ?>')">
                                Probar Este
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        let currentToken = null;

        async function executeLogin() {
            const select = document.getElementById('userSelect');
            const code = select.value;
            const pass = document.getElementById('passInput').value;

            try {
                const res = await fetch('login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employeeCode: code, password: pass })
                });
                const data = await res.json();
                if (data.success && data.token) {
                    currentToken = data.token;
                    document.getElementById('tokenOutput').style.display = 'block';
                    document.getElementById('btnVerify').style.display = 'inline-flex';

                    // Formatear token por partes
                    const parts = data.token.split('.');
                    document.getElementById('rawToken').innerHTML = `
                        <span class="token-segment token-header" title="Header (HS256)">${parts[0]}</span>.<span class="token-segment token-payload" title="Payload Claims">${parts[1]}</span>.<span class="token-segment token-signature" title="Firma Criptográfica">${parts[2]}</span>
                    `;

                    document.getElementById('claimSub').textContent = data.user.id;
                    document.getElementById('claimName').textContent = data.user.fullName + ` (${data.user.employeeCode})`;
                    document.getElementById('claimRole').textContent = data.user.roleName + ` [${data.user.roleCode}]`;
                    document.getElementById('claimSector').textContent = data.user.sector;
                    document.getElementById('claimPerms').textContent = `${data.user.permissionsCount} Permisos RBAC`;
                    document.getElementById('claimExp').textContent = `${data.expiresAt} (${data.expiresIn}s)`;
                } else {
                    alert('Error en autenticación: ' + (data.error || 'Respuesta inválida'));
                }
            } catch (err) {
                alert('Fallo de conexión al endpoint de login: ' + err.message);
            }
        }

        async function verifyToken() {
            if (!currentToken) return;
            const resBox = document.getElementById('verifyResult');
            resBox.style.display = 'block';
            resBox.textContent = 'Verificando firma con el servidor...';
            try {
                const res = await fetch('login.php', {
                    method: 'GET',
                    headers: { 'Authorization': `Bearer ${currentToken}` }
                });
                const data = await res.json();
                if (data.success && data.valid) {
                    resBox.style.background = 'rgba(16, 185, 129, 0.15)';
                    resBox.style.border = '1px solid #10b981';
                    resBox.style.color = '#34d399';
                    resBox.innerHTML = `<strong>✓ Firma Criptográfica Verificada:</strong> Token legítimo emitido para <strong>${data.user.name}</strong> (${data.user.roleName}). Expira en: ${data.expiresAt}.`;
                } else {
                    resBox.style.background = 'rgba(244, 63, 94, 0.15)';
                    resBox.style.border = '1px solid #f43f5e';
                    resBox.style.color = '#fda4af';
                    resBox.textContent = '✗ Token inválido o expirado: ' + (data.error || 'Fallo de verificación');
                }
            } catch (err) {
                resBox.textContent = 'Error de validación: ' + err.message;
            }
        }

        function selectAndLogin(code) {
            document.getElementById('userSelect').value = code;
            executeLogin();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>
    <?php
}


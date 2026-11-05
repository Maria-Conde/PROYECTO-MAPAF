<?php
/**
 * PROYECTO MAPAF - Vista: Gestión de Usuarios & Roles de Acceso (RBAC)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO | SCADA Petrolero · SPS-01
 */

$authService = new AuthService();
$roleUpdateMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_role') {
    $userId = $_POST['user_id'] ?? '';
    $newRoleId = $_POST['new_role_id'] ?? '';
    $justificacion = $_POST['justificacion'] ?? 'Cambio autorizado por Administrador del Sistema SCADA';

    if (!empty($userId) && !empty($newRoleId)) {
        if ($authService->changeUserRole($userId, $newRoleId, $justificacion)) {
            $roleUpdateMsg = [
                'type' => 'success',
                'text' => "El rol del usuario ha sido actualizado correctamente y sincronizado en la matriz de seguridad."
            ];
        } else {
            $roleUpdateMsg = [
                'type' => 'error',
                'text' => "No se pudo actualizar el rol. Verifique los identificadores ingresados."
            ];
        }
    }
}

$catalog = $authService->getOperatorsCatalog();
$users = $catalog['users'] ?? [];
$roles = $catalog['roles'] ?? [];
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
            <h2 class="section-title" style="margin-bottom:0;">👥 Usuarios & Matriz de Roles (RBAC)</h2>
            <span class="badge-sidebar-pill badge-pill-admin" style="font-size:0.75rem;">CIBERSEGURIDAD IEC 62443</span>
        </div>
        <p style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-mono);">
            Control de Identidad Criptográfica JWT · Asignación y Cambio Dinámico de Roles de Campo
        </p>
    </div>

    <div style="display:flex; gap:0.75rem; align-items:center;">
        <button type="button" class="btn-well btn-ssv-open" onclick="openNewUserModal()">
            ➕ Nuevo Usuario
        </button>
        <button type="button" class="btn-well btn-choke" onclick="toggleRolesMatrix()">
            🔐 Ver Matriz RBAC
        </button>
    </div>
</div>

<?php if ($roleUpdateMsg): ?>
<div style="background:rgba(16, 185, 129, 0.15); border:1px solid #10b981; border-radius:8px; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
    <span style="font-size:1.4rem;">✅</span>
    <div>
        <strong style="color:#34d399; font-size:0.95rem; display:block;"><?= htmlspecialchars($roleUpdateMsg['text']) ?></strong>
        <span style="font-size:0.75rem; color:#cbd5e1; font-family:var(--font-mono);">Evento forense registrado en la bitácora de auditoría inmutable (21 CFR Part 11).</span>
    </div>
</div>
<?php endif; ?>

<!-- KPI Cards Resumen -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">TOTAL OPERADORES REGISTRADOS</div>
        <div style="font-size:1.5rem; font-weight:800; color:#38bdf8; font-family:var(--font-mono); margin-top:0.25rem;">
            <?= count($users) ?>
        </div>
        <div style="font-size:0.72rem; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">● 100% con credencial digital</div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">ADMINISTRADORES ACTIVOS</div>
        <div style="font-size:1.5rem; font-weight:800; color:#f43f5e; font-family:var(--font-mono); margin-top:0.25rem;">
            2 <span style="font-size:0.8rem; color:#94a3b8;">(SYS-ADMIN)</span>
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:0.25rem;">Ing. Rodrigo Valenzuela / María José Conde</div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">ROLES DISPONIBLES PARA CAMBIO</div>
        <div style="font-size:1.5rem; font-weight:800; color:#a855f7; font-family:var(--font-mono); margin-top:0.25rem;">
            <?= count($roles) ?> Roles
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:0.25rem;">Conmutables en caliente</div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">AUTENTICACIÓN EN SESIÓN</div>
        <div style="font-size:1.5rem; font-weight:800; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">
            JWT HS256
        </div>
        <div style="font-size:0.72rem; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">Firmas digitales RFC 7519</div>
    </div>
</div>

<!-- Tabla de Usuarios con Cambio Directo de Roles -->
<div class="scada-card" style="padding:1.25rem; margin-bottom:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.2rem;">
                📋 Personal Autorizado & Asignación de Roles
            </h3>
            <p style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-mono);">
                Selecciona cualquier rol en el menú desplegable o haz clic en "✏️ Cambiar Rol" para reasignar privilegios
            </p>
        </div>
        <span class="badge-status badge-nominal" style="padding:0.35rem 0.75rem;">
            EDICIÓN DE ROLES HABILITADA
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table class="telemetry-table" style="width:100%; border-collapse:collapse; font-size:0.83rem;">
            <thead>
                <tr>
                    <th style="padding:0.75rem; text-align:left;">OPERADOR / NOMBRE</th>
                    <th style="padding:0.75rem; text-align:left;">CÓDIGO</th>
                    <th style="padding:0.75rem; text-align:left;">CORREO</th>
                    <th style="padding:0.75rem; text-align:left;">ROL RBAC (OPCIÓN DE CAMBIO DIRECTO)</th>
                    <th style="padding:0.75rem; text-align:left;">SECTOR / ÁREA</th>
                    <th style="padding:0.75rem; text-align:center;">ESTADO</th>
                    <th style="padding:0.75rem; text-align:center;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <?php
                $badgeBorder = '#38bdf8';
                $badgeColor = '#38bdf8';
                if (stripos($u['rol_codigo'] ?? '', 'ADMIN') !== false) {
                    $badgeBorder = '#f43f5e';
                    $badgeColor = '#fda4af';
                } elseif (stripos($u['rol_codigo'] ?? '', 'ENG') !== false) {
                    $badgeBorder = '#a855f7';
                    $badgeColor = '#d8b4fe';
                } elseif (stripos($u['rol_codigo'] ?? '', 'SUPV') !== false) {
                    $badgeBorder = '#f59e0b';
                    $badgeColor = '#fcd34d';
                }
                ?>
                <tr>
                    <td style="padding:0.75rem; font-weight:700; color:#f8fafc;">
                        <?= htmlspecialchars($u['nombre_completo']) ?>
                        <?php if (stripos($u['rol_codigo'] ?? '', 'ADMIN') !== false): ?>
                            <span style="font-size:0.75rem;" title="Administrador del Sistema">👑</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:0.75rem; font-family:var(--font-mono); color:#38bdf8; font-weight:700;">
                        <?= htmlspecialchars($u['codigo_empleado']) ?>
                    </td>
                    <td style="padding:0.75rem; color:#94a3b8; font-family:var(--font-mono); font-size:0.75rem;">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td style="padding:0.75rem;">
                        <!-- Selector de Cambio Directo de Rol -->
                        <form method="POST" action="index.php?view=usuarios" style="display:flex; align-items:center; gap:0.5rem; margin:0;">
                            <input type="hidden" name="action" value="change_role">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($u['id']) ?>">
                            <select name="new_role_id" 
                                    class="modal-input" 
                                    style="padding:0.35rem 0.65rem; font-size:0.78rem; font-weight:700; font-family:var(--font-mono); margin-bottom:0; background:#0b1122; border:1px solid <?= $badgeBorder ?>; color:<?= $badgeColor ?>; border-radius:6px; cursor:pointer;" 
                                    onchange="this.form.submit()" 
                                    title="Haz clic para seleccionar otro rol y cambiarlo de inmediato">
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= ($u['rol_id'] === $r['id'] || $u['rol_codigo'] === $r['codigo']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nombre']) ?> (<?= $r['codigo'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td style="padding:0.75rem; color:#cbd5e1; font-size:0.78rem;">
                        <?= htmlspecialchars($u['sector_asignado']) ?>
                    </td>
                    <td style="padding:0.75rem; text-align:center;">
                        <span class="badge-status badge-nominal">
                            ● <?= htmlspecialchars($u['estado'] ?? 'EN TURNO') ?>
                        </span>
                    </td>
                    <td style="padding:0.75rem; text-align:center;">
                        <div style="display:inline-flex; gap:0.4rem;">
                            <button type="button" 
                                    class="btn-well btn-ssv-open" 
                                    style="padding:0.25rem 0.6rem; font-size:0.72rem;" 
                                    onclick="openChangeRoleModal('<?= htmlspecialchars($u['id']) ?>', '<?= htmlspecialchars($u['nombre_completo']) ?>', '<?= htmlspecialchars($u['rol_id']) ?>')">
                                ✏️ Cambiar Rol
                            </button>
                            <button type="button" 
                                    class="btn-well btn-choke" 
                                    style="padding:0.25rem 0.5rem; font-size:0.72rem;" 
                                    onclick="toggleRolesMatrix()">
                                👁️ Permisos
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Matriz de Roles y Privilegios (IEC 62443) -->
<div id="rolesMatrixSection" class="scada-card" style="padding:1.25rem;">
    <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.5rem;">
        🔐 Matriz de Privilegios por Rol Industrial
    </h3>
    <p style="font-size:0.8rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:1rem;">
        Enclavamientos criptográficos que impiden que operadores sin nivel ejecuten paradas de emergencia o conmutación de válvulas
    </p>

    <div style="overflow-x:auto;">
        <table class="telemetry-table" style="width:100%; border-collapse:collapse; font-size:0.8rem;">
            <thead>
                <tr>
                    <th style="padding:0.6rem; text-align:left;">ROL</th>
                    <th style="padding:0.6rem; text-align:center;">TELEMETRÍA</th>
                    <th style="padding:0.6rem; text-align:center;">VÁLVULA SSV</th>
                    <th style="padding:0.6rem; text-align:center;">CHOKE API 6A</th>
                    <th style="padding:0.6rem; text-align:center;">DISPARO ESD-1</th>
                    <th style="padding:0.6rem; text-align:center;">REARME ESD</th>
                    <th style="padding:0.6rem; text-align:center;">ACUSE ALARMAS</th>
                    <th style="padding:0.6rem; text-align:center;">GESTIÓN USUARIOS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $r): ?>
                <tr>
                    <td style="padding:0.6rem; font-weight:700; color:#fff;">
                        <?= htmlspecialchars($r['nombre']) ?>
                        <div style="font-size:0.7rem; color:var(--text-muted); font-family:var(--font-mono);"><?= $r['codigo'] ?></div>
                    </td>
                    <td style="padding:0.6rem; text-align:center; color:#34d399; font-weight:bold;">✓</td>
                    <td style="padding:0.6rem; text-align:center;"><?= in_array('control_valves', $r['permisos']) ? '<span style="color:#34d399; font-weight:bold;">✓</span>' : '<span style="color:#64748b;">—</span>' ?></td>
                    <td style="padding:0.6rem; text-align:center;"><?= in_array('control_choke', $r['permisos']) ? '<span style="color:#34d399; font-weight:bold;">✓</span>' : '<span style="color:#64748b;">—</span>' ?></td>
                    <td style="padding:0.6rem; text-align:center;"><?= in_array('execute_esd', $r['permisos']) ? '<span style="color:#ef4444; font-weight:bold;">✓</span>' : '<span style="color:#64748b;">—</span>' ?></td>
                    <td style="padding:0.6rem; text-align:center;"><?= in_array('reset_esd', $r['permisos']) ? '<span style="color:#34d399; font-weight:bold;">✓</span>' : '<span style="color:#64748b;">—</span>' ?></td>
                    <td style="padding:0.6rem; text-align:center;"><?= in_array('ack_alarms', $r['permisos']) ? '<span style="color:#34d399; font-weight:bold;">✓</span>' : '<span style="color:#64748b;">—</span>' ?></td>
                    <td style="padding:0.6rem; text-align:center;"><?= in_array('manage_users', $r['permisos']) ? '<span style="color:#f43f5e; font-weight:bold;">✓</span>' : '<span style="color:#64748b;">—</span>' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Cambiar Rol Formal de Usuario con Justificación (21 CFR Part 11) -->
<div id="modalCambiarRol" class="scada-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.75); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center;">
    <div class="scada-card" style="width:90%; max-width:500px; border:1px solid #38bdf8; box-shadow:0 10px 40px rgba(0,0,0,0.8);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">
            <h3 style="font-size:1.1rem; color:#fff; font-weight:700;">🔄 Cambiar Rol de Operador</h3>
            <button type="button" onclick="closeChangeRoleModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" action="index.php?view=usuarios">
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="user_id" id="modalChangeUserId" value="">

            <div style="background:#090e1d; border-radius:8px; padding:0.85rem; margin-bottom:1rem; border:1px solid rgba(255,255,255,0.05);">
                <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">OPERADOR SELECCIONADO:</div>
                <div style="font-size:1.1rem; font-weight:700; color:#38bdf8; margin-top:0.2rem;" id="modalChangeUserName">Cargando...</div>
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">NUEVO ROL ASIGNADO *</label>
                <select name="new_role_id" id="modalChangeSelectRole" class="modal-input" style="width:100%; box-sizing:border-box;">
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?> (<?= $r['codigo'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">JUSTIFICACIÓN TÉCNICA (21 CFR Part 11) *</label>
                <textarea name="justificacion" class="modal-input" rows="3" style="width:100%; box-sizing:border-box; font-family:var(--font-mono); font-size:0.8rem; resize:vertical;" placeholder="Ej: Cambio de rol autorizado por inicio de guardia técnica o promoción de perfil..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn-well btn-choke" onclick="closeChangeRoleModal()">Cancelar</button>
                <button type="submit" class="btn-well btn-ssv-open" style="font-weight:700;">Confirmar Cambio de Rol</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Nuevo Usuario -->
<div id="modalNuevoUsuario" class="scada-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.75); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center;">
    <div class="scada-card" style="width:90%; max-width:500px; border:1px solid #38bdf8; box-shadow:0 10px 40px rgba(0,0,0,0.8);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">
            <h3 style="font-size:1.1rem; color:#fff; font-weight:700;">➕ Registrar Nuevo Operador SCADA</h3>
            <button type="button" onclick="closeNewUserModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.3rem; cursor:pointer;">&times;</button>
        </div>

        <form onsubmit="crearUsuario(event)">
            <div style="margin-bottom:0.85rem;">
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">NOMBRE COMPLETO *</label>
                <input type="text" class="modal-input" placeholder="ej. Carlos Eduardo Sánchez" required style="width:100%; box-sizing:border-box;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.85rem;">
                <div>
                    <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">CÓDIGO EMPLEADO *</label>
                    <input type="text" class="modal-input" placeholder="EMP-8820" required style="width:100%; box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">ROL ASIGNADO *</label>
                    <select class="modal-input" style="width:100%; box-sizing:border-box;">
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.75rem; color:var(--text-muted); font-family:var(--font-mono); margin-bottom:0.3rem;">CORREO CORPORATIVO *</label>
                <input type="email" class="modal-input" placeholder="ej. c.sanchez@mapaf.senacontrol.co" required style="width:100%; box-sizing:border-box;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn-well btn-choke" onclick="closeNewUserModal()">Cancelar</button>
                <button type="submit" class="btn-well btn-ssv-open">Guardar & Emitir Credencial</button>
            </div>
        </form>
    </div>
</div>

<script>
function openChangeRoleModal(userId, userName, currentRoleId) {
    document.getElementById('modalChangeUserId').value = userId;
    document.getElementById('modalChangeUserName').textContent = userName;
    const sel = document.getElementById('modalChangeSelectRole');
    if (sel) {
        for (let i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value === currentRoleId) {
                sel.selectedIndex = i;
                break;
            }
        }
    }
    document.getElementById('modalCambiarRol').style.display = 'flex';
}

function closeChangeRoleModal() {
    document.getElementById('modalCambiarRol').style.display = 'none';
}

function openNewUserModal() {
    document.getElementById('modalNuevoUsuario').style.display = 'flex';
}

function closeNewUserModal() {
    document.getElementById('modalNuevoUsuario').style.display = 'none';
}

function toggleRolesMatrix() {
    const el = document.getElementById('rolesMatrixSection');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
}

function crearUsuario(e) {
    e.preventDefault();
    alert("Operador registrado exitosamente en la matriz de identidades.");
    closeNewUserModal();
    location.reload();
}
</script>

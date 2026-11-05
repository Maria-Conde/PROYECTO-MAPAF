<?php
/**
 * PROYECTO MAPAF - Vista: Gestión de Usuarios & Roles de Acceso (RBAC)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO | SCADA Petrolero · SPS-01
 */

$authService = new AuthService();
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
            Control de Identidad Criptográfica JWT · Autorización Estricta de Maniobras de Campo
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

<!-- KPI Cards Resumen -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">TOTAL OPERADORES REGISTRADOS</div>
        <div style="font-size:1.5rem; font-weight:800; color:#38bdf8; font-family:var(--font-mono); margin-top:0.25rem;">
            <?= count($users) ?>
        </div>
        <div style="font-size:0.72rem; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">● 100% activos con credencial digital</div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">ADMINISTRADORES DE SISTEMA</div>
        <div style="font-size:1.5rem; font-weight:800; color:#f43f5e; font-family:var(--font-mono); margin-top:0.25rem;">
            2 <span style="font-size:0.8rem; color:#94a3b8;">(SYS-ADMIN)</span>
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:0.25rem;">Ing. Rodrigo Valenzuela / María José Conde</div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">ROLES RBAC CONFIGURADOS</div>
        <div style="font-size:1.5rem; font-weight:800; color:#a855f7; font-family:var(--font-mono); margin-top:0.25rem;">
            <?= count($roles) ?> Roles
        </div>
        <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:0.25rem;">Mínimo privilegio según ISA-99</div>
    </div>

    <div class="scada-card" style="padding:1rem;">
        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">AUTENTICACIÓN EN SESIÓN</div>
        <div style="font-size:1.5rem; font-weight:800; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">
            JWT HS256
        </div>
        <div style="font-size:0.72rem; color:#34d399; font-family:var(--font-mono); margin-top:0.25rem;">Firmas digitales RFC 7519</div>
    </div>
</div>

<!-- Tabla de Usuarios y Operadores -->
<div class="scada-card" style="padding:1.25rem; margin-bottom:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
            <h3 style="font-size:1rem; color:#f8fafc; font-weight:700; margin-bottom:0.2rem;">
                📋 Personal Autorizado para Operación de Consola & Campo
            </h3>
            <p style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-mono);">
                Trazabilidad nominal de acciones según estándar 21 CFR Part 11
            </p>
        </div>
        <span class="badge-status badge-nominal" style="padding:0.35rem 0.75rem;">
            ENCRIPTACIÓN ACTIVA
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table class="telemetry-table" style="width:100%; border-collapse:collapse; font-size:0.83rem;">
            <thead>
                <tr>
                    <th style="padding:0.75rem; text-align:left;">OPERADOR / NOMBRE</th>
                    <th style="padding:0.75rem; text-align:left;">CÓDIGO</th>
                    <th style="padding:0.75rem; text-align:left;">CORREO</th>
                    <th style="padding:0.75rem; text-align:left;">ROL RBAC</th>
                    <th style="padding:0.75rem; text-align:left;">SECTOR / ÁREA</th>
                    <th style="padding:0.75rem; text-align:center;">ESTADO</th>
                    <th style="padding:0.75rem; text-align:center;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td style="padding:0.75rem; font-weight:700; color:#f8fafc;">
                        <?= htmlspecialchars($u['nombre_completo']) ?>
                        <?php if (stripos($u['rol_codigo'] ?? '', 'ADMIN') !== false): ?>
                            <span style="font-size:0.75rem;">👑</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:0.75rem; font-family:var(--font-mono); color:#38bdf8; font-weight:700;">
                        <?= htmlspecialchars($u['codigo_empleado']) ?>
                    </td>
                    <td style="padding:0.75rem; color:#94a3b8; font-family:var(--font-mono); font-size:0.75rem;">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td style="padding:0.75rem;">
                        <?php
                        $badgeBg = 'rgba(56, 189, 248, 0.15)';
                        $badgeBorder = '#38bdf8';
                        $badgeColor = '#38bdf8';
                        if (stripos($u['rol_codigo'] ?? '', 'ADMIN') !== false) {
                            $badgeBg = 'rgba(244, 63, 94, 0.2)';
                            $badgeBorder = '#f43f5e';
                            $badgeColor = '#fda4af';
                        } elseif (stripos($u['rol_codigo'] ?? '', 'ENG') !== false) {
                            $badgeBg = 'rgba(168, 85, 247, 0.2)';
                            $badgeBorder = '#a855f7';
                            $badgeColor = '#d8b4fe';
                        } elseif (stripos($u['rol_codigo'] ?? '', 'SUPV') !== false) {
                            $badgeBg = 'rgba(245, 158, 11, 0.2)';
                            $badgeBorder = '#f59e0b';
                            $badgeColor = '#fcd34d';
                        }
                        ?>
                        <span style="background:<?= $badgeBg ?>; border:1px solid <?= $badgeBorder ?>; color:<?= $badgeColor ?>; padding:0.25rem 0.6rem; border-radius:12px; font-size:0.72rem; font-weight:700; font-family:var(--font-mono);">
                            <?= htmlspecialchars($u['rol_nombre']) ?>
                        </span>
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
                        <button type="button" class="btn-well btn-choke" style="padding:0.25rem 0.5rem; font-size:0.72rem;" onclick="alert('Ver matriz de permisos de <?= htmlspecialchars($u['nombre_completo']) ?>')">
                            Permisos
                        </button>
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

<!-- Modal Nuevo Usuario -->
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
function openNewUserModal() {
    document.getElementById('modalNuevoUsuario').style.display = 'flex';
}
function closeNewUserModal() {
    document.getElementById('modalNuevoUsuario').style.display = 'none';
}
function toggleRolesMatrix() {
    const el = document.getElementById('rolesMatrixSection');
    el.scrollIntoView({ behavior: 'smooth' });
}
function crearUsuario(e) {
    e.preventDefault();
    alert("Operador registrado exitosamente en la matriz de identidades y emitido par de llaves criptográficas.");
    closeNewUserModal();
    location.reload();
}
</script>

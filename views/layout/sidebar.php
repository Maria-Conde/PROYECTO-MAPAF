<?php
/**
 * PROYECTO MAPAF - Layout: Barra Lateral (Sidebar)
 * SENA ADSO - Ficha 3413988 | SCADA Petrolero · SPS-01
 */
$currentView = $currentView ?? 'dashboard';

// Consulta dinámica de alarmas pendientes para badge
$pendingAlarmsCount = 2;
try {
    $alarmService = new AlarmService();
    $counts = $alarmService->getAlarmCounts();
    $pendingAlarmsCount = $counts['total'] ?? 2;
} catch (Throwable $e) {}
?>
<aside class="app-sidebar">

    <!-- Encabezado de Marca en Sidebar -->
    <div class="sidebar-brand-box">
        <div class="sidebar-logo-icon">🛢️</div>
        <div>
            <div class="sidebar-brand-name">mapaf</div>
            <div class="sidebar-brand-sub">SCADA Petrolero · SPS-01</div>
        </div>
    </div>

    <!-- 1. OPERACIÓN & DESPACHO -->
    <div class="sidebar-category">OPERACIÓN & DESPACHO</div>
    
    <a href="index.php?view=dashboard" class="sidebar-link <?= ($currentView === 'dashboard') ? 'active' : '' ?>">
        <span>📊</span>
        <span class="sidebar-text">Dashboard</span>
    </a>

    <a href="index.php?view=equipos" class="sidebar-link <?= ($currentView === 'equipos') ? 'active' : '' ?>">
        <span>⚙️</span>
        <span class="sidebar-text">Equipos Industriales</span>
    </a>

    <a href="index.php?view=cisternas" class="sidebar-link <?= ($currentView === 'cisternas') ? 'active' : '' ?>">
        <span>🚛</span>
        <span class="sidebar-text">Despacho Cisternas</span>
        <span class="badge-sidebar-pill badge-pill-isla">ISLA</span>
    </a>

    <a href="index.php?view=alarms" class="sidebar-link <?= ($currentView === 'alarms') ? 'active' : '' ?>">
        <span>🔔</span>
        <span class="sidebar-text">Alertas ISA 18.2</span>
        <span class="badge-sidebar-pill badge-pill-count" id="badgePendingAlarms"><?= $pendingAlarmsCount ?></span>
    </a>

    <a href="index.php?view=historial" class="sidebar-link <?= ($currentView === 'historial') ? 'active' : '' ?>">
        <span>📈</span>
        <span class="sidebar-text">Historial de Parámetros</span>
        <span class="badge-sidebar-pill badge-pill-count">3</span>
    </a>

    <!-- 2. PARÁMETROS & CONFIGURACIÓN -->
    <div class="sidebar-category" style="margin-top: 0.85rem;">PARÁMETROS & CONFIGURACIÓN</div>

    <a href="index.php?view=parametros" class="sidebar-link <?= ($currentView === 'parametros') ? 'active' : '' ?>">
        <span>📋</span>
        <span class="sidebar-text">Parámetros del Sistema</span>
    </a>

    <a href="index.php?view=carga_csv" class="sidebar-link <?= ($currentView === 'carga_csv') ? 'active' : '' ?>">
        <span>📥</span>
        <span class="sidebar-text">Carga CSV (Parámetros)</span>
        <span class="badge-sidebar-pill badge-pill-admin">ADMIN</span>
    </a>

    <!-- 3. LICENCIAMIENTO & FACTURACIÓN -->
    <div class="sidebar-category" style="margin-top: 0.85rem;">LICENCIAMIENTO & FACTURACIÓN</div>

    <a href="index.php?view=facturacion" class="sidebar-link <?= ($currentView === 'facturacion') ? 'active' : '' ?>">
        <span>💳</span>
        <span class="sidebar-text">Pago Mensual Empresa</span>
        <span class="badge-sidebar-pill badge-pill-admin">ADMIN</span>
    </a>

    <!-- 4. ADMINISTRACIÓN -->
    <div class="sidebar-category" style="margin-top: 0.85rem;">ADMINISTRACIÓN</div>

    <a href="index.php?view=usuarios" class="sidebar-link <?= ($currentView === 'usuarios') ? 'active' : '' ?>">
        <span>👥</span>
        <span class="sidebar-text">Usuarios & Roles</span>
        <span class="badge-sidebar-pill badge-pill-admin">ADMIN</span>
    </a>

    <!-- 5. ARQUITECTURA & PATRONES -->
    <div class="sidebar-category" style="margin-top: 0.85rem;">ARQUITECTURA & PATRONES</div>

    <a href="index.php?view=patrones" class="sidebar-link <?= ($currentView === 'patrones' || $currentView === 'modules') ? 'active' : '' ?>">
        <span>🧩</span>
        <span class="sidebar-text">Patrones & Arquitectura</span>
        <span class="badge-sidebar-pill badge-pill-cyan">7 PATRONES</span>
    </a>

    <!-- 6. DIAGNÓSTICO -->
    <div class="sidebar-category" style="margin-top: 0.85rem;">DIAGNÓSTICO</div>

    <a href="index.php?view=status" class="sidebar-link <?= ($currentView === 'status') ? 'active' : '' ?>">
        <span>🔌</span>
        <span class="sidebar-text">Estado Servidor</span>
    </a>

    <!-- Tarjeta de Perfil de Usuario en el Footer del Sidebar -->
    <div class="sidebar-user-profile">
        <div class="user-avatar-crown">👑</div>
        <div class="user-info-text">
            <div class="user-name">Ing. Rodrigo Valenzuela</div>
            <div class="user-role-title">Administrador del Sistema SCADA</div>
            <div class="user-badge-row">
                <span class="user-badge-admin">Rol: ADMIN</span>
                <span class="user-handle">@admin</span>
            </div>
        </div>
    </div>

</aside>
<main class="app-content">

    <!-- Banner de Parada de Emergencia ESD-1 -->
    <div id="esdAlertBanner" class="esd-alert-banner">
        <div>
            <strong style="font-size:1.05rem; letter-spacing:0.04em;">⚠️ PARADA DE EMERGENCIA ESD-1 ACTIVADA (IEC 61511)</strong>
            <div style="font-size:0.78rem; opacity:0.9; margin-top:0.2rem;">
                Cabezales aislados por seguridad: Válvulas SSV cerradas y estranguladores en 0.0%. Disparo registrado a las: <span id="esdTriggerTime" style="font-family:var(--font-mono); font-weight:bold;">--:--:--</span>.
            </div>
        </div>
        <button type="button" id="btnResetEsd" class="btn-confirm" style="background:#fff; color:#991b1b; font-weight:bold;">
            🔄 REARMAR PLANTA
        </button>
    </div>

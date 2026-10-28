<?php
/**
 * PROYECTO MAPAF - Layout: Barra Lateral (Sidebar)
 * SENA ADSO - Ficha 3413988
 */
$currentView = $currentView ?? 'dashboard';
?>
<aside class="app-sidebar">
    <div class="sidebar-category">Supervisión SCADA</div>
    
    <a href="index.php?view=dashboard" class="sidebar-link <?= ($currentView === 'dashboard') ? 'active' : '' ?>">
        <span>📊</span>
        <span class="sidebar-text">Tablero General</span>
    </a>

    <a href="index.php?view=wellheads" class="sidebar-link <?= ($currentView === 'wellheads') ? 'active' : '' ?>">
        <span>🛢️</span>
        <span class="sidebar-text">Control de Pozos</span>
    </a>

    <div class="sidebar-category" style="margin-top: 1rem;">Seguridad & Normas</div>

    <a href="index.php?view=alarms" class="sidebar-link <?= ($currentView === 'alarms') ? 'active' : '' ?>">
        <span>🔔</span>
        <span class="sidebar-text">Alarmas ISA-18.2</span>
        <span id="badgePendingAlarms" style="display:none; margin-left:auto; background:var(--danger); color:#fff; font-size:0.65rem; padding:0.1rem 0.45rem; border-radius:999px; font-weight:bold;">0</span>
    </a>

    <a href="index.php?view=audit" class="sidebar-link <?= ($currentView === 'audit') ? 'active' : '' ?>">
        <span>📜</span>
        <span class="sidebar-text">Bitácora de Auditoría</span>
    </a>

    <div class="sidebar-category" style="margin-top: 1rem;">Estructura & Arquitectura</div>

    <a href="index.php?view=modules" class="sidebar-link <?= ($currentView === 'modules') ? 'active' : '' ?>">
        <span>🧩</span>
        <span class="sidebar-text">Módulos del Sistema</span>
    </a>

    <div class="sidebar-category" style="margin-top: 1rem;">Sistema & Servicios</div>

    <a href="index.php?view=status" class="sidebar-link <?= ($currentView === 'status') ? 'active' : '' ?>">
        <span>⚙️</span>
        <span class="sidebar-text">Diagnóstico del Sistema</span>
    </a>

    <a href="setup_database.php" target="_blank" class="sidebar-link" title="Abrir asistente de base de datos">
        <span>💾</span>
        <span class="sidebar-text">Gestión de Base de Datos</span>
    </a>
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

<?php
/**
 * PROYECTO MAPAF - Plataforma Web SCADA Táctico & Telemetría IIoT
 * Conectado con XAMPP (Apache + PHP 8.0 + MySQL mapaf_db)
 * SENA - Regional Huila | Ficha: 3413988-ADSO
 */

require_once __DIR__ . '/config/database.php';
$dbStatus = checkDatabaseStatus();
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROYECTO MAPAF · Centro de Operaciones SCADA & Telemetría IIoT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #060913;
            --bg-surface: #0a0f1d;
            --bg-sidebar: #0b1122;
            --bg-card: #10182c;
            --bg-card-hover: #141f38;
            --border-color: #1a253c;
            --border-active: #34d399;
            --border-subtle: rgba(255, 255, 255, 0.06);
            --primary: #10b981;
            --primary-glow: rgba(16, 185, 129, 0.25);
            --danger: #f43f5e;
            --warning: #f59e0b;
            --info: #0ea5e9;
            --purple: #a855f7;
            --text-main: #f8fafc;
            --text-muted: #8492a6;
            --sidebar-width: 280px;
            --header-height: 60px;
            --font-main: 'Inter', -apple-system, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --font-heading: 'Space Grotesk', sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: var(--font-main);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Top Header */
        header.app-header {
            height: var(--header-height);
            background: rgba(11, 17, 34, 0.98);
            border-bottom: 1px solid var(--border-color);
            padding: 0 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            z-index: 40;
            flex-shrink: 0;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .toggle-sidebar-btn {
            background: #151f35;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 34px;
            height: 34px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.2s;
        }

        .toggle-sidebar-btn:hover {
            color: #fff;
            background: #1e2c4a;
            border-color: #38bdf8;
        }

        .brand-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulse-dot 2s infinite ease-in-out;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.85); }
        }

        .brand-text h1 {
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-text p {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-family: var(--font-mono);
        }

        /* Header Middle KPI ticker */
        .header-kpis {
            display: none;
            align-items: center;
            gap: 1.5rem;
            background: #090e1d;
            border: 1px solid var(--border-color);
            padding: 0.35rem 1.25rem;
            border-radius: 999px;
            font-family: var(--font-mono);
            font-size: 0.75rem;
        }

        @media (min-width: 1100px) {
            .header-kpis { display: flex; }
        }

        .kpi-item {
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .kpi-lbl { color: var(--text-muted); }
        .kpi-val { font-weight: 700; color: #fff; }

        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .status-pill {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            white-space: nowrap;
        }

        .status-pill.error {
            background: rgba(244, 63, 94, 0.1);
            border-color: rgba(244, 63, 94, 0.3);
            color: #f43f5e;
        }

        .btn-action {
            background: #141c2f;
            border: 1px solid var(--border-color);
            color: #cbd5e1;
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .btn-action:hover {
            background: #202b46;
            color: #fff;
            border-color: #475569;
        }

        .btn-action.active-filter {
            background: #1e293b;
            color: #38bdf8;
            border-color: #38bdf8;
            font-weight: 700;
        }

        .btn-danger {
            background: rgba(244, 63, 94, 0.15);
            border-color: rgba(244, 63, 94, 0.4);
            color: #fda4af;
        }

        .btn-danger:hover {
            background: var(--danger);
            color: #fff;
        }

        /* Main Workspace Container (Sidebar + Main) */
        .app-body {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        /* Sidebar Navigation */
        aside.app-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-shrink: 0;
            overflow-y: auto;
            transition: margin-left 0.25s ease, width 0.25s ease;
            z-index: 30;
        }

        aside.app-sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        /* Search input inside Sidebar */
        .sidebar-search-box {
            padding: 0.75rem 0.85rem 0.25rem;
        }

        .sidebar-search-input {
            width: 100%;
            background: #060913;
            border: 1px solid var(--border-color);
            color: #f1f5f9;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            outline: none;
            transition: border-color 0.2s;
        }

        .sidebar-search-input:focus {
            border-color: #38bdf8;
        }

        .sidebar-search-input::placeholder {
            color: #475569;
        }

        .sidebar-menu {
            padding: 0.6rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        .module-group {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            background: rgba(255, 255, 255, 0.015);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            padding: 0.35rem 0.35rem 0.45rem;
        }

        .module-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.35rem 0.5rem 0.25rem;
            cursor: pointer;
            user-select: none;
        }

        .module-group-title {
            font-size: 0.68rem;
            font-family: var(--font-mono);
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .module-group.grp-rtoc .module-group-title { color: #34d399; }
        .module-group.grp-eng .module-group-title { color: #38bdf8; }
        .module-group.grp-arch .module-group-title { color: #c084fc; }
        .module-group.grp-mgmt .module-group-title { color: #fbbf24; }

        .module-group-count {
            font-size: 0.65rem;
            font-family: var(--font-mono);
            background: #151f35;
            color: #94a3b8;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
        }

        .module-items-container {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .nav-link {
            background: transparent;
            border: 1px solid transparent;
            color: #94a3b8;
            padding: 0.5rem 0.65rem;
            font-size: 0.8rem;
            font-family: var(--font-main);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            text-align: left;
        }

        .nav-link-left {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .nav-link-icon {
            font-size: 0.95rem;
            width: 18px;
            text-align: center;
            display: inline-block;
        }

        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-link.active {
            background: #141f38;
            color: #38bdf8;
            font-weight: 600;
            border-color: rgba(56, 189, 248, 0.3);
            box-shadow: inset 3px 0 0 #38bdf8;
        }

        .badge-count {
            background: var(--danger);
            color: #fff;
            font-size: 0.65rem;
            padding: 0.1rem 0.45rem;
            border-radius: 999px;
            font-weight: 700;
            font-family: var(--font-mono);
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 0.85rem;
            border-top: 1px solid var(--border-color);
            background: #090e1c;
            font-size: 0.7rem;
            font-family: var(--font-mono);
            color: var(--text-muted);
        }

        .sidebar-operator {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.45rem;
        }

        .operator-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #1e293b;
            border: 1px solid #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            color: #38bdf8;
            font-weight: bold;
        }

        /* Main Workspace Content */
        main.app-main {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem 1.5rem;
            background: var(--bg-surface);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* Breadcrumb Bar */
        .breadcrumb-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            color: var(--text-muted);
        }

        .breadcrumb-path {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .breadcrumb-path span.current {
            color: #38bdf8;
            font-weight: 600;
        }

        /* Module Header Hero Card (Organized Context) */
        .module-hero {
            background: linear-gradient(135deg, rgba(16, 24, 44, 0.95), rgba(10, 15, 29, 0.95));
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.1rem 1.35rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .module-hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #38bdf8, #10b981);
        }

        .hero-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hero-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: #141f38;
            border: 1px solid rgba(56, 189, 248, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .hero-text h2 {
            font-family: var(--font-heading);
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .hero-text p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        .hero-tags {
            display: flex;
            gap: 0.4rem;
            margin-top: 0.35rem;
        }

        .hero-tag {
            font-family: var(--font-mono);
            font-size: 0.65rem;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
            background: #090e1d;
            border: 1px solid var(--border-color);
            color: #94a3b8;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Sections */
        .section-view { display: none; }
        .section-view.active { display: block; animation: fadeIn 0.2s ease-in-out; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Metric Grid */
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .metric-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.1rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, border-color 0.2s;
        }

        .metric-card:hover {
            border-color: rgba(56, 189, 248, 0.4);
            transform: translateY(-2px);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #0ea5e9);
        }

        .metric-tag {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        .metric-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-top: 0.2rem;
        }

        .metric-val-wrap {
            margin-top: 0.75rem;
            display: flex;
            align-items: baseline;
            gap: 0.4rem;
        }

        .metric-val {
            font-family: var(--font-mono);
            font-size: 1.55rem;
            font-weight: 700;
            color: #fff;
        }

        .metric-unit {
            font-size: 0.8rem;
            color: #94a3b8;
            font-family: var(--font-mono);
        }

        .metric-target {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.4rem;
            display: flex;
            justify-content: space-between;
        }

        /* Toolbar / Filter Row */
        .toolbar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            background: #090e1d;
            border: 1px solid var(--border-color);
            padding: 0.6rem 0.85rem;
            border-radius: 8px;
        }

        .toolbar-filters {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .filter-chip {
            background: #141f38;
            border: 1px solid var(--border-color);
            color: #94a3b8;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            padding: 0.3rem 0.65rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-chip:hover {
            color: #fff;
            border-color: #475569;
        }

        .filter-chip.active {
            background: #0369a1;
            border-color: #38bdf8;
            color: #fff;
            font-weight: 600;
        }

        .search-field {
            background: #060913;
            border: 1px solid var(--border-color);
            color: #f1f5f9;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            padding: 0.35rem 0.7rem;
            border-radius: 5px;
            outline: none;
            min-width: 200px;
        }

        .search-field:focus {
            border-color: #38bdf8;
        }

        /* Sub-Tabs Bar (For Diagrams, Comparisons, Security) */
        .subtabs-bar {
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            overflow-x: auto;
        }

        .subtab-btn {
            background: transparent;
            border: 1px solid transparent;
            color: #94a3b8;
            font-family: var(--font-mono);
            font-size: 0.78rem;
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .subtab-btn:hover {
            background: #10182c;
            color: #cbd5e1;
        }

        .subtab-btn.active {
            background: #151f38;
            border-color: rgba(56, 189, 248, 0.4);
            color: #38bdf8;
            font-weight: 600;
        }

        .subtab-content { display: none; }
        .subtab-content.active { display: block; animation: fadeIn 0.2s; }

        /* Wellhead Cards */
        .wells-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
        }

        .well-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            transition: border-color 0.2s, transform 0.2s;
        }

        .well-card:hover {
            border-color: rgba(56, 189, 248, 0.3);
            transform: translateY(-2px);
        }

        .well-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }

        .well-title {
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
        }

        .well-status {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-online { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; }
        .status-choked { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
        .status-tripped { background: rgba(244, 63, 94, 0.2); color: #f87171; border: 1px solid #f43f5e; }
        .status-maint { background: rgba(148, 163, 184, 0.2); color: #cbd5e1; border: 1px solid #94a3b8; }

        .well-readings {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1.2rem;
            background: rgba(0,0,0,0.25);
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .reading-item { display: flex; flex-direction: column; }
        .reading-lbl { font-size: 0.7rem; color: var(--text-muted); font-family: var(--font-mono); }
        .reading-num { font-size: 1rem; font-weight: 700; font-family: var(--font-mono); color: #f1f5f9; }

        .control-group {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .control-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            color: var(--text-muted);
        }

        .choke-slider {
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #1e293b;
            outline: none;
            -webkit-appearance: none;
            cursor: pointer;
        }

        .choke-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #38bdf8;
            cursor: pointer;
            box-shadow: 0 0 8px #38bdf8;
        }

        .valve-toggles {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6rem;
            margin-top: 1rem;
        }

        .btn-valve {
            background: #141c2f;
            border: 1px solid var(--border-color);
            color: #94a3b8;
            padding: 0.45rem;
            border-radius: 6px;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .btn-valve.open {
            background: rgba(16, 185, 129, 0.15);
            border-color: #10b981;
            color: #34d399;
            font-weight: 700;
        }

        /* Tables & Containers */
        .table-container {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1.25rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .table-header {
            padding: 0.85rem 1.25rem;
            background: #090e1d;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .table-title {
            font-family: var(--font-heading);
            font-size: 0.95rem;
            font-weight: 700;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }

        table.data-table th, table.data-table td {
            padding: 0.75rem 1.15rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        table.data-table th {
            background: rgba(0,0,0,0.25);
            color: var(--text-muted);
            font-family: var(--font-mono);
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        table.data-table tr:hover { background: rgba(255, 255, 255, 0.02); }

        .btn-ack {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid #10b981;
            color: #34d399;
            padding: 0.3rem 0.65rem;
            font-size: 0.72rem;
            font-family: var(--font-mono);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-ack:hover { background: #10b981; color: #000; font-weight: 700; }
        .btn-ack.disabled { opacity: 0.4; pointer-events: none; border-color: #64748b; color: #64748b; background: transparent; }

        /* Callouts & Badges */
        .callout-box {
            background: rgba(16, 185, 129, 0.08);
            border-left: 4px solid #10b981;
            border-radius: 0 8px 8px 0;
            padding: 0.85rem 1.15rem;
            font-size: 0.82rem;
            color: #cbd5e1;
            margin-bottom: 1.25rem;
        }

        .callout-box.info {
            background: rgba(14, 165, 233, 0.08);
            border-left-color: #0ea5e9;
        }

        .comp-badge {
            font-family: var(--font-mono);
            font-size: 0.68rem;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-weight: 700;
        }

        .badge-amber { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; }
        .badge-emerald { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #10b981; }
        .badge-blue { background: rgba(14, 165, 233, 0.2); color: #38bdf8; border: 1px solid #0ea5e9; }
        .badge-purple { background: rgba(168, 85, 247, 0.2); color: #c084fc; border: 1px solid #a855f7; }

        .comparison-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .comp-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.3rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .comp-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; }
        .comp-card.c-local::before { background: #64748b; }
        .comp-card.c-mysql::before { background: #38bdf8; }

        .comp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .comp-title { font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; }

        .comp-list { list-style: none; font-size: 0.82rem; }
        .comp-list li {
            padding: 0.45rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .comp-list li:last-child { border-bottom: none; }
        .comp-key { color: var(--text-muted); font-size: 0.72rem; font-family: var(--font-mono); }
        .comp-val { font-weight: 600; text-align: right; }

        /* Diagrams */
        .diagram-container {
            background: #090e1d;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .diagram-svg { width: 100%; height: auto; display: block; }
        .pipe-flow { stroke-dasharray: 6 6; animation: flowDash 1.2s linear infinite; }
        @keyframes flowDash { to { stroke-dashoffset: -12; } }

        /* Modales Trazables (ISA-18.2 & Antipatrón Blind ACK) */
        .modal-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 120;
        }
        .ack-modal-dialog {
            background: #0d1527;
            border: 1px solid #1e293b;
            border-radius: 12px;
            width: 90%;
            max-width: 520px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
            overflow: hidden;
            animation: fadeIn 0.2s ease-out;
        }
        .ack-modal-header {
            background: #080d19;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-close-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 1.1rem;
            cursor: pointer;
        }
        .modal-close-btn:hover { color: #fff; }
        .ack-modal-body {
            padding: 1.25rem;
        }

        /* Domain Badge Active (DDD Bounded Context) */
        .domain-badge-active {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: #38bdf8;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            font-family: var(--font-mono);
            font-size: 0.72rem;
            font-weight: 600;
        }

        /* Command UNDO & State Machine Styles */
        .btn-undo {
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #fbbf24;
            padding: 0.35rem 0.75rem;
            font-size: 0.72rem;
            font-family: var(--font-mono);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            width: 100%;
            justify-content: center;
            margin-top: 0.45rem;
        }
        .btn-undo:hover {
            background: #f59e0b;
            color: #000;
            font-weight: 700;
        }
        .state-interlock-box {
            background: rgba(244, 63, 94, 0.12);
            border: 1px solid rgba(244, 63, 94, 0.4);
            color: #fecdd3;
            padding: 0.6rem 0.8rem;
            border-radius: 6px;
            font-size: 0.72rem;
            font-family: var(--font-mono);
            margin-top: 0.6rem;
            line-height: 1.35;
        }

        /* Strategy & Observer Panels */
        .strategy-selector-panel {
            background: linear-gradient(135deg, rgba(16, 24, 44, 0.95), rgba(10, 15, 29, 0.95));
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .strategy-btn-group {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.6rem;
            flex-wrap: wrap;
        }
        .strategy-pill-btn {
            background: #141f38;
            border: 1px solid var(--border-color);
            color: #94a3b8;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .strategy-pill-btn:hover { color: #fff; border-color: #38bdf8; }
        .strategy-pill-btn.active {
            background: rgba(168, 85, 247, 0.2);
            border-color: #a855f7;
            color: #c084fc;
            font-weight: 700;
            box-shadow: 0 0 10px rgba(168, 85, 247, 0.2);
        }

        .observer-panel {
            background: rgba(11, 17, 34, 0.85);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.85rem 1.15rem;
            margin-bottom: 1.25rem;
        }
        .observer-reactions-bar {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.6rem;
            flex-wrap: wrap;
        }
        .observer-badge {
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-family: var(--font-mono);
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #151f35;
            color: #94a3b8;
            border: 1px solid var(--border-color);
            transition: all 0.25s;
        }
        .observer-badge.fired {
            background: rgba(16, 185, 129, 0.2);
            border-color: #10b981;
            color: #34d399;
            font-weight: bold;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.3);
        }

        .header-role-select {
            background: #060913;
            border: 1px solid rgba(168, 85, 247, 0.4);
            color: #c084fc;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            font-weight: bold;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            outline: none;
            cursor: pointer;
        }

        /* Toast */
        #toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 0.85rem 1.25rem;
            border-radius: 8px;
            background: #1e293b;
            color: #fff;
            border: 1px solid #334155;
            font-size: 0.85rem;
            font-family: var(--font-mono);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 100;
        }

        #toast.show { transform: translateY(0); opacity: 1; }
        #toast.success { border-color: #10b981; background: #064e3b; color: #a7f3d0; }
        #toast.error { border-color: #f43f5e; background: #881337; color: #fecdd3; }

        /* Barra Superior de Navegación Rápida Directa */
        .app-top-nav {
            background: #090e1d;
            border-bottom: 1px solid var(--border-color);
            padding: 0.45rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            overflow-x: auto;
            flex-shrink: 0;
            z-index: 35;
        }
        .top-nav-container {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            overflow-x: auto;
        }
        .top-nav-btn {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid transparent;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .top-nav-btn:hover {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }
        .top-nav-btn.active {
            background: #141f38;
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.4);
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.15);
        }
        .top-nav-endpoints {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-shrink: 0;
        }
        .endpoint-pill {
            background: rgba(168, 85, 247, 0.12);
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: #c084fc;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-family: var(--font-mono);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.15s;
        }
        .endpoint-pill:hover {
        /* Estilos de Infraestructura y Topología SCADA */
        .infra-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .infra-card {
            background: linear-gradient(135deg, rgba(16, 24, 44, 0.95), rgba(10, 15, 29, 0.95));
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.15rem;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .infra-card:hover {
            border-color: rgba(56, 189, 248, 0.5);
            transform: translateY(-2px);
        }
        .infra-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .infra-card-title {
            font-family: var(--font-heading);
            font-size: 0.92rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .badge-pulse {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-family: var(--font-mono);
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
        }
        .badge-pulse-green {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }
        .badge-pulse-green::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 6px #10b981;
        }
        .layers-timeline {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .layer-box {
            background: #080d1a;
            border: 1px solid var(--border-color);
            border-left: 4px solid #38bdf8;
            border-radius: 8px;
            padding: 0.9rem 1.1rem;
            transition: all 0.2s;
        }
        .layer-box:hover {
            border-color: #38bdf8;
            background: #0c1424;
        }
        .flow-btn-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .flow-pill-btn {
            background: #111a30;
            border: 1px solid var(--border-color);
            color: #94a3b8;
            padding: 0.45rem 0.85rem;
            border-radius: 8px;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .flow-pill-btn:hover {
            color: #fff;
            border-color: #38bdf8;
        }
        .flow-pill-btn.active {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.2);
        }
        .flow-step-item {
            background: #060913;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            font-family: var(--font-mono);
            font-size: 0.75rem;
        }
    </style>
</head>
<body>

    <!-- Header Superior -->
    <header class="app-header">
        <div class="header-brand">
            <button class="toggle-sidebar-btn" onclick="toggleSidebar()" title="Ocultar / Mostrar Módulos (Sidebar)">
                ☰
            </button>
            <div class="brand-indicator"></div>
            <div class="brand-text">
                <h1>PROYECTO MAPAF</h1>
                <p>SENA ADSO 3413988 · Regional Huila | XAMPP (Apache + PHP 8 + MySQL)</p>
            </div>
        </div>

        <!-- Indicadores Rápidos de Planta -->
        <div class="header-kpis">
            <div class="kpi-item">
                <span class="kpi-lbl">PRODUCCIÓN:</span>
                <span class="kpi-val" id="kpiProd">14,820 BPD</span>
            </div>
            <div class="kpi-item">
                <span class="kpi-lbl">ESTADO:</span>
                <span class="kpi-val" style="color: #34d399;" id="kpiFlow">NOMINAL</span>
            </div>
            <div class="kpi-item">
                <span class="kpi-lbl">POZOS ACTIVOS:</span>
                <span class="kpi-val" id="kpiWells">4/4</span>
            </div>
        </div>

        <div class="header-actions">
            <div class="status-pill <?= $dbStatus['connected'] ? '' : 'error' ?>" id="dbPill">
                <span>●</span>
                <span id="dbPillText"><?= $dbStatus['connected'] ? 'MySQL: mapaf_db' : 'MySQL Desconectado' ?></span>
            </div>

            <!-- Selector de Operador & Rol JWT RBAC en Vivo -->
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <span style="font-size: 0.7rem; color: var(--text-muted); font-family: var(--font-mono);">Operador JWT:</span>
                <select id="activeHeaderRole" onchange="switchOperatorRole(this.value)" class="header-role-select" title="Cambiar identidad y rol RBAC activo con token JWT">
                    <option value="OP-449" selected>👑 M. Conde Dussan [SYS-ADMIN]</option>
                    <option value="OP-312">🛠️ H. Manchola [PROCESS-ENG]</option>
                    <option value="OP-205">⚡ E. Vargas [SUPERVISOR]</option>
                    <option value="OP-101">💻 Operador de Sala [CONSOLE-OP]</option>
                    <option value="OP-118">👁️ J. Pinto Díaz [INSTRUCTOR-SENA]</option>
                </select>
            </div>

            <button class="btn-action" onclick="switchModule('jwt', 'Arquitectura & SENA', 'Seguridad & Autenticación JWT')" style="background: rgba(168,85,247,0.15); border-color: rgba(168,85,247,0.4); color: #c084fc; font-weight: bold;">
                🔐 Claims JWT
            </button>

            <button class="btn-action btn-danger" onclick="toggleEsd()" title="Aislamiento Total de Emergencia">
                ⚠️ ESD-1
            </button>

            <a href="/phpmyadmin" target="_blank" class="btn-action" title="Abrir phpMyAdmin de XAMPP">
                🗄️ phpMyAdmin
            </a>
        </div>
    </header>

    <!-- Barra Superior de Navegación Rápida Directa -->
    <nav class="app-top-nav">
        <div class="top-nav-container">
            <button class="top-nav-btn active" data-topmod="overview" onclick="switchModule('overview', 'Operaciones RTOC', 'Visión General de Telemetría')">
                📊 Telemetría RTOC
            </button>
            <button class="top-nav-btn" data-topmod="wells" onclick="switchModule('wells', 'Operaciones RTOC', 'Control de Pozos y Cabezales')">
                🛢️ Pozos & Válvulas
            </button>
            <button class="top-nav-btn" data-topmod="alarms" onclick="switchModule('alarms', 'Operaciones RTOC', 'Consola de Alarmas ISA-18.2')">
                🚨 Alarmas ISA-18.2
            </button>
            <button class="top-nav-btn" data-topmod="schematics" onclick="switchModule('schematics', 'Ingeniería & P&ID', 'Diagramas Técnicos de Proceso')">
                🗺️ Diagramas P&ID
            </button>
            <button class="top-nav-btn" data-topmod="patterns" onclick="switchModule('patterns', 'Arquitectura & SENA', 'Patrones de Software (GoF) & Antipatrones')">
                ⚙️ Patrones GoF (8)
            </button>
            <button class="top-nav-btn" data-topmod="domains" onclick="switchModule('domains', 'Arquitectura & SENA', 'Dominios del Sistema (DDD)')">
                🏛️ Dominios DDD (7)
            </button>
            <button class="top-nav-btn" data-topmod="jwt" onclick="switchModule('jwt', 'Arquitectura & SENA', 'Seguridad & Autenticación JWT')">
                🔐 Autenticación JWT
            </button>
            <button class="top-nav-btn" data-topmod="security" onclick="switchModule('security', 'Gestión & Sistema', 'Seguridad y Roles de Usuario')">
                👥 Usuarios & Roles
            </button>
            <button class="top-nav-btn" data-topmod="audit" onclick="switchModule('audit', 'Gestión & Sistema', 'Bitácora de Maniobras')">
                📜 Bitácora Auditoría
            </button>
            <button class="top-nav-btn" data-topmod="diag" onclick="switchModule('diag', 'Arquitectura & Sistema', 'Infraestructura & Topología SCADA')">
                🏗️ Infraestructura (6 Capas)
            </button>
        </div>
        <div class="top-nav-endpoints">
            <a href="api/patterns.php" class="endpoint-pill" title="Abrir Consola y API REST de Patrones">
                🔍 API Patrones
            </a>
            <a href="api/login.php" class="endpoint-pill" title="Abrir Consola de Autenticación JWT">
                🔑 Consola JWT
            </a>
            <a href="api/domains.php" class="endpoint-pill" title="Abrir Consola de Dominios DDD">
                🏛️ API Dominios
            </a>
            <a href="api/status.php" target="_blank" class="endpoint-pill" title="Consultar Diagnóstico XAMPP">
                ⚡ Status
            </a>
        </div>
    </nav>

    <!-- Cuerpo de la Plataforma -->
    <div class="app-body">

        <!-- Sidebar de Módulos Categorizados y Filtrables -->
        <aside class="app-sidebar" id="appSidebar">
            <!-- Buscador Rápido de Módulos -->
            <div class="sidebar-search-box">
                <input type="text" id="moduleSearch" class="sidebar-search-input" placeholder="🔍 Filtrar módulos..." oninput="filterModules(this.value)">
            </div>

            <div class="sidebar-menu" id="sidebarMenu">

                <!-- GRUPO 1: OPERACIONES RTOC -->
                <div class="module-group grp-rtoc" data-group="rtoc">
                    <div class="module-group-header" onclick="toggleGroup('rtoc')">
                        <div class="module-group-title">
                            <span>🕹️</span>
                            <span>1. Operaciones RTOC</span>
                        </div>
                        <span class="module-group-count">3</span>
                    </div>
                    <div class="module-items-container" id="grp-items-rtoc">
                        <button class="nav-link active" data-mod="overview" onclick="switchModule('overview', 'Operaciones RTOC', 'Visión General de Telemetría')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">📊</span>
                                <span>Visión General</span>
                            </div>
                        </button>
                        <button class="nav-link" data-mod="wells" onclick="switchModule('wells', 'Operaciones RTOC', 'Control de Pozos y Cabezales')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">🛢️</span>
                                <span>Pozos y Válvulas</span>
                            </div>
                        </button>
                        <button class="nav-link" data-mod="alarms" onclick="switchModule('alarms', 'Operaciones RTOC', 'Consola de Alarmas ISA-18.2')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">🚨</span>
                                <span>Alarmas Activas</span>
                            </div>
                            <span class="badge-count" id="alarmBadge">0</span>
                        </button>
                    </div>
                </div>

                <!-- GRUPO 2: INGENIERÍA & P&ID -->
                <div class="module-group grp-eng" data-group="eng">
                    <div class="module-group-header" onclick="toggleGroup('eng')">
                        <div class="module-group-title">
                            <span>📐</span>
                            <span>2. Ingeniería & P&ID</span>
                        </div>
                        <span class="module-group-count">2</span>
                    </div>
                    <div class="module-items-container" id="grp-items-eng">
                        <button class="nav-link" data-mod="schematics" onclick="switchModule('schematics', 'Ingeniería & P&ID', 'Diagramas Técnicos de Proceso')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">🗺️</span>
                                <span>Diagramas de Planta</span>
                            </div>
                        </button>
                        <button class="nav-link" data-mod="tags" onclick="switchModule('tags', 'Ingeniería & P&ID', 'Diccionario de Instrumentación')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">🏷️</span>
                                <span>Diccionario de Tags</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- GRUPO 3: ARQUITECTURA SENA -->
                <div class="module-group grp-arch" data-group="arch">
                    <div class="module-group-header" onclick="toggleGroup('arch')">
                        <div class="module-group-title">
                            <span>📊</span>
                            <span>3. Arquitectura & SENA</span>
                        </div>
                        <span class="module-group-count">4</span>
                    </div>
                    <div class="module-items-container" id="grp-items-arch">
                        <button class="nav-link" data-mod="comparisons" onclick="switchModule('comparisons', 'Arquitectura & SENA', 'Comparativas y Sustentación Técnica')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">📈</span>
                                <span>Comparativas & Guía</span>
                            </div>
                        </button>
                        <button class="nav-link" data-mod="patterns" onclick="switchModule('patterns', 'Arquitectura & SENA', 'Patrones de Software (GoF) & Antipatrones')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">⚙️</span>
                                <span>Patrones & Antipatrones</span>
                            </div>
                        </button>
                        <button class="nav-link" data-mod="domains" onclick="switchModule('domains', 'Arquitectura & SENA', 'Dominios del Sistema (DDD)')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">🏛️</span>
                                <span>Dominios DDD (7)</span>
                            </div>
                        </button>
                        <button class="nav-link" data-mod="jwt" onclick="switchModule('jwt', 'Arquitectura & SENA', 'Seguridad & Autenticación JWT')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">🔐</span>
                                <span>Seguridad & JWT</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- GRUPO 4: GESTIÓN & AUDITORÍA -->
                <div class="module-group grp-mgmt" data-group="mgmt">
                    <div class="module-group-header" onclick="toggleGroup('mgmt')">
                        <div class="module-group-title">
                            <span>🛡️</span>
                            <span>4. Gestión & Sistema</span>
                        </div>
                        <span class="module-group-count">3</span>
                    </div>
                    <div class="module-items-container" id="grp-items-mgmt">
                        <button class="nav-link" data-mod="security" onclick="switchModule('security', 'Gestión & Sistema', 'Seguridad y Roles de Usuario')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">👥</span>
                                <span>Usuarios y Roles</span>
                            </div>
                        </button>
                        <button class="nav-link" data-mod="audit" onclick="switchModule('audit', 'Gestión & Sistema', 'Bitácora de Maniobras')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">📜</span>
                                <span>Bitácora de Turno</span>
                            </div>
                        </button>
                        <button class="nav-link" data-mod="diag" onclick="switchModule('diag', 'Gestión & Sistema', 'Diagnóstico de XAMPP y MySQL')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">⚙️</span>
                                <span>Diagnóstico XAMPP</span>
                            </div>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Footer del Sidebar -->
            <div class="sidebar-footer">
                <div class="sidebar-operator">
                    <div class="operator-avatar">OP</div>
                    <div>
                        <div style="color: #fff; font-weight: 600;">M. Conde Dussan</div>
                        <div style="font-size: 0.65rem; color: #34d399;">Ficha: 3413988-ADSO</div>
                    </div>
                </div>
                <div style="font-size: 0.65rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                    <span>XAMPP PHP 8.0 · MySQL</span>
                    <span id="liveClock">--:--:--</span>
                </div>
            </div>
        </aside>

        <!-- Área de Contenido Principal -->
        <main class="app-main">

            <!-- Barra de Migas de Pan (Breadcrumb) & Dominio DDD Activo -->
            <div class="breadcrumb-bar">
                <div class="breadcrumb-path">
                    <span>MAPAF</span>
                    <span>›</span>
                    <span id="bcCategory">Operaciones RTOC</span>
                    <span>›</span>
                    <span class="current" id="bcModule">Visión General de Telemetría</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <div id="activeDomainBadge" class="domain-badge-active" title="Dominio Delimitado (Bounded Context) activo bajo arquitectura DDD">
                        🏛️ DOMINIO 1: RTOC & Telemetría IIoT [TELEMETRY-CTX]
                    </div>
                    <span style="font-size: 0.7rem; color: var(--text-muted);">Base de Datos: <strong>mapaf_db</strong></span>
                    <button class="btn-action" onclick="loadAllData()" style="padding: 0.25rem 0.6rem; font-size: 0.7rem;">🔄 Refrescar</button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 1. MÓDULO: VISIÓN GENERAL (OVERVIEW)       -->
            <!-- ========================================== -->
            <section id="tab-overview" class="section-view active">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">📊</div>
                        <div class="hero-text">
                            <h2>Centro de Telemetría en Tiempo Real (RTOC)</h2>
                            <p>Supervisión continua de presiones, caudales y variables críticas en Batería Huila.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Tabla: `telemetria_metricas`</span>
                                <span class="hero-tag">Ciclo: 3s</span>
                                <span class="hero-tag">Políticas: ISA-18.2</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button class="btn-action" onclick="loadAllData()">🔄 Actualizar Sensores</button>
                        <button class="btn-action" onclick="switchModule('wells', 'Operaciones RTOC', 'Control de Pozos y Cabezales')">🛢️ Ir a Pozos</button>
                    </div>
                </div>

                <!-- Patrón GoF STRATEGY en Operación: Cálculo Dinámico de Caudal Fiscal -->
                <div class="strategy-selector-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <span class="comp-badge badge-purple" style="margin-bottom: 0.25rem; display: inline-block;">PATRÓN STRATEGY (COMPORTAMIENTO)</span>
                            <h3 style="font-family: var(--font-heading); font-size: 1rem; color: #fff;">Algoritmo Activo de Cálculo y Fiscalización de Flujo</h3>
                            <p style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono);">Al cambiar de estrategia, el backend ejecuta la clase PHP polimórfica sin recargar la página.</p>
                        </div>
                        <div id="strategyLiveKpi" style="font-family: var(--font-mono); font-size: 0.75rem; color: #34d399; background: #060913; padding: 0.4rem 0.75rem; border-radius: 6px; border: 1px solid var(--border-color);">
                            Caudal Calculado: <strong id="strategyBpdDisplay">14,820 BPD</strong> | Gas: <strong id="strategyGasDisplay">3,200 MSCFD</strong>
                        </div>
                    </div>
                    <div class="strategy-btn-group">
                        <button class="strategy-pill-btn active" id="btn-strat-coriolis" onclick="switchFlowStrategy('coriolis')">
                            🔘 Coriolis AGA-11 (Fiscal Másico · Alta Precisión)
                        </button>
                        <button class="strategy-pill-btn" id="btn-strat-aga3" onclick="switchFlowStrategy('aga3')">
                            🔘 Placa de Orificio AGA-3 (Diferencial Clásica)
                        </button>
                        <button class="strategy-pill-btn" id="btn-strat-beggs" onclick="switchFlowStrategy('beggsAndBrill')">
                            🔘 Beggs & Brill (Multifásico Gas/Crudo/Agua)
                        </button>
                    </div>
                    <div id="strategyFormulaBox" style="margin-top: 0.6rem; font-family: var(--font-mono); font-size: 0.7rem; color: #cbd5e1; background: #080d19; padding: 0.45rem 0.75rem; border-radius: 4px; border: 1px solid rgba(255,255,255,0.05);">
                        <strong>Ecuación Activa (AGA-11 Coriolis):</strong> <code>ṁ = ρ · Q_vol | Corrección API MPMS 11.1 a 60°F | Incertidumbre ±0.15%</code>
                    </div>
                </div>

                <!-- Patrón GoF OBSERVER en Operación: Telemetría Reactiva -->
                <div class="observer-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <span class="comp-badge badge-blue">PATRÓN OBSERVER (COMPORTAMIENTO)</span>
                            <strong style="color: #fff; font-size: 0.85rem; margin-left: 0.5rem;">Simulador Reactivo de Inyección de Presión:</strong>
                            <span style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono); display: block; margin-top: 0.15rem;">
                                El sensor (Subject) notifica instantáneamente a los 3 observadores desacoplados.
                            </span>
                        </div>
                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                            <button class="btn-action" onclick="injectTelemetryPressure(1842.5, 'nominal')" style="font-size: 0.7rem;">
                                🟢 Nominal (1,842 PSI)
                            </button>
                            <button class="btn-action" onclick="injectTelemetryPressure(2150.0, 'warning')" style="font-size: 0.7rem; background: rgba(245,158,11,0.15); color: #fbbf24; border-color: #f59e0b;">
                                🟡 Alerta (2,150 PSI)
                            </button>
                            <button class="btn-action btn-danger" onclick="injectTelemetryPressure(3600.0, 'critical')" style="font-size: 0.7rem;">
                                🔴 Crítico HIHI (3,600 PSI)
                            </button>
                        </div>
                    </div>
                    <div class="observer-reactions-bar">
                        <div class="observer-badge" id="obsAlarmBadge">
                            <span>🚨</span> <span>Observer 1: AlarmTriggerObserver (ISA-18.2)</span>
                        </div>
                        <div class="observer-badge" id="obsSafetyBadge">
                            <span>🛡️</span> <span>Observer 2: SafetyInterlockObserver (ESD-1)</span>
                        </div>
                        <div class="observer-badge" id="obsAuditBadge">
                            <span>📜</span> <span>Observer 3: AuditLoggerObserver (MySQL 21 CFR 11)</span>
                        </div>
                    </div>
                </div>

                <!-- Grid de KPIs Primarios -->
                <div class="metric-grid" id="metricsContainer">
                    <!-- Se llena dinámicamente -->
                </div>

                <!-- Tabla de Cumplimiento de Telemetría -->
                <div class="table-container">
                    <div class="table-header">
                        <span class="table-title">Consignas y Desviaciones en Tiempo Real (MySQL `telemetria_metricas`)</span>
                        <span style="font-size: 0.75rem; font-family: var(--font-mono); color: #34d399;">● Actualización automática</span>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Variable / Instrumento</th>
                                <th>Tag P&ID</th>
                                <th>Valor Medido</th>
                                <th>Consigna Óptima</th>
                                <th>Límite Advertencia</th>
                                <th>Límite Parada (Trip)</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Presión de Cabezal (Tubing)</strong></td>
                                <td><code>WHTP-101A</code></td>
                                <td style="color: #38bdf8; font-weight: bold;">1,842.5 PSI</td>
                                <td>1,800.0 PSI</td>
                                <td>2,100.0 PSI</td>
                                <td>2,350.0 PSI</td>
                                <td><span class="comp-badge badge-emerald">NOMINAL</span></td>
                            </tr>
                            <tr>
                                <td><strong>Presión Anular (Casing)</strong></td>
                                <td><code>CASING-101B</code></td>
                                <td style="color: #fbbf24; font-weight: bold;">642.0 PSI</td>
                                <td>600.0 PSI</td>
                                <td>800.0 PSI</td>
                                <td>950.0 PSI</td>
                                <td><span class="comp-badge badge-amber">AVISO PREVENTIVO</span></td>
                            </tr>
                            <tr>
                                <td><strong>Caudal Bruto de Producción</strong></td>
                                <td><code>MFM-201</code></td>
                                <td style="color: #34d399; font-weight: bold;">14,820 BPD</td>
                                <td>15,000 BPD</td>
                                <td>16,500 BPD</td>
                                <td>18,000 BPD</td>
                                <td><span class="comp-badge badge-emerald">ESTABLE (98.8%)</span></td>
                            </tr>
                            <tr>
                                <td><strong>Temperatura Manifold Entrada</strong></td>
                                <td><code>TEMP-FLOW-301</code></td>
                                <td style="color: #38bdf8; font-weight: bold;">78.4 °C</td>
                                <td>75.0 °C</td>
                                <td>92.0 °C</td>
                                <td>105.0 °C</td>
                                <td><span class="comp-badge badge-emerald">NOMINAL</span></td>
                            </tr>
                            <tr>
                                <td><strong>Frecuencia Bomba ESP</strong></td>
                                <td><code>ESP-FREQ-01</code></td>
                                <td style="color: #c084fc; font-weight: bold;">58.2 Hz</td>
                                <td>60.0 Hz</td>
                                <td>63.5 Hz</td>
                                <td>66.0 Hz</td>
                                <td><span class="comp-badge badge-emerald">MODO VFD V/F</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Resumen de Alarmas -->
                <div class="table-container">
                    <div class="table-header">
                        <span class="table-title">Resumen de Alarmas Recientes</span>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn-action" onclick="ackAllAlarms()">Reconocer Todas (ACK ALL)</button>
                            <button class="btn-action" onclick="switchModule('alarms', 'Operaciones RTOC', 'Consola de Alarmas ISA-18.2')">Ver Consola Completa ›</button>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Tag Instrumento</th>
                                <th>Subsistema</th>
                                <th>Descripción</th>
                                <th>Valor Actual</th>
                                <th>Severidad</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="alarmsOverviewBody"></tbody>
                    </table>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 2. MÓDULO: POZOS Y VÁLVULAS                -->
            <!-- ========================================== -->
            <section id="tab-wells" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">🛢️</div>
                        <div class="hero-text">
                            <h2>Control de Pozos y Cabezales de Superficie</h2>
                            <p>Regulación micrométrica de estranguladores Choke y válvulas de seguridad hidráulica (SSV).</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Norma: API 6A</span>
                                <span class="hero-tag">Tabla: `pozos`</span>
                                <span class="hero-tag">Actuación: Hidráulica / Eléctrica</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button class="btn-action" onclick="loadAllData()">🔄 Refrescar Pozos</button>
                    </div>
                </div>

                <!-- Barra de Filtrado y Búsqueda de Pozos -->
                <div class="toolbar-row">
                    <div class="toolbar-filters">
                        <span style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--text-muted); margin-right: 0.25rem;">FILTRO:</span>
                        <button class="filter-chip active" onclick="filterWells('all', this)">Todos (4)</button>
                        <button class="filter-chip" onclick="filterWells('EN LÍNEA', this)">🟢 En Línea</button>
                        <button class="filter-chip" onclick="filterWells('ESTRANGULADO', this)">🟡 Estrangulados</button>
                        <button class="filter-chip" onclick="filterWells('DISPARADO', this)">🔴 Disparados</button>
                    </div>
                    <div>
                        <input type="text" class="search-field" placeholder="Buscar pozo..." oninput="searchWells(this.value)">
                    </div>
                </div>

                <!-- Consola de Comandos y Maniobras Activas (GoF Command Pattern con UNDO) -->
                <div style="background: #090e1d; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <span class="comp-badge badge-emerald">PATRÓN COMMAND (CON UNDO ACTIVO)</span>
                            <span class="comp-badge badge-blue">PATRÓN STATE (API 6A)</span>
                            <span class="comp-badge badge-purple">DOMINIO: WELLHEAD-CTX</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono); margin-top: 0.35rem;">
                            Cada consigna de estrangulador o SSV se despacha como un comando reversible. Si un pozo entra en estado DISPARADO, la máquina de estados bloquea las maniobras hasta su rearme.
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span id="cmdStackCount" style="font-size: 0.72rem; font-family: var(--font-mono); color: #38bdf8;">0 maniobras en pila</span>
                        <button class="btn-action" onclick="undoLastGlobalCommand()" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: #f59e0b; font-weight: bold;">
                            ↺ Deshacer Última Maniobra General
                        </button>
                    </div>
                </div>

                <!-- Grid de Pozos -->
                <div class="wells-grid" id="wellsContainer">
                    <!-- Se llena dinámicamente -->
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 3. MÓDULO: CONSOLA DE ALARMAS              -->
            <!-- ========================================== -->
            <section id="tab-alarms" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">🚨</div>
                        <div class="hero-text">
                            <h2>Consola de Gestión y Acuse de Alarmas (ISA-18.2)</h2>
                            <p>Gestión estandarizada de alarmas de proceso, priorización y persistencia en MySQL.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Norma: ANSI/ISA-18.2</span>
                                <span class="hero-tag">Tabla: `alarmas`</span>
                                <span class="hero-tag">Acuse: Trazable por Operador</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button class="btn-action" onclick="ackAllAlarms()" style="background: rgba(16, 185, 129, 0.2); border-color: #10b981; color: #34d399; font-weight: bold;">
                            ✓ Reconocimiento Masivo (ACK ALL)
                        </button>
                    </div>
                </div>

                <!-- Filtros de Alarmas -->
                <div class="toolbar-row">
                    <div class="toolbar-filters">
                        <span style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--text-muted); margin-right: 0.25rem;">SEVERIDAD:</span>
                        <button class="filter-chip active" onclick="filterAlarms('all', this)">Todas</button>
                        <button class="filter-chip" onclick="filterAlarms('unacked', this)">⚠️ Solo Pendientes</button>
                        <button class="filter-chip" onclick="filterAlarms('critical', this)">🔴 Críticas</button>
                        <button class="filter-chip" onclick="filterAlarms('warning', this)">🟡 Advertencias</button>
                    </div>
                    <div>
                        <input type="text" class="search-field" placeholder="Filtrar por Tag / Sub..." oninput="searchAlarms(this.value)">
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hora Evento</th>
                                <th>Tag</th>
                                <th>Subsistema</th>
                                <th>Descripción</th>
                                <th>Valor Registrado</th>
                                <th>Umbral Límite</th>
                                <th>Severidad</th>
                                <th>Estado Acuse</th>
                                <th>Operador</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="alarmsFullBody"></tbody>
                    </table>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 4. MÓDULO: DIAGRAMAS TÉCNICOS & P&ID       -->
            <!-- ========================================== -->
            <section id="tab-schematics" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">📐</div>
                        <div class="hero-text">
                            <h2>Diagramas de Planta, Cabezal & P&ID</h2>
                            <p>Planos vectoriales interactivos con esquemática de instrumentación y flujos de proceso.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Norma: ANSI/ISA-5.1</span>
                                <span class="hero-tag">Norma: API 6A</span>
                                <span class="hero-tag">Vectorial SVG</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button class="btn-action" onclick="switchModule('tags', 'Ingeniería & P&ID', 'Diccionario de Instrumentación')">🏷️ Ver Diccionario de Tags</button>
                    </div>
                </div>

                <!-- Sub-Tabs para Organizar Diagramas -->
                <div class="subtabs-bar">
                    <button class="subtab-btn active" onclick="switchSubtab('schematics', 'sub-tree', this)">1. Cabezal de Pozo & Árbol (API 6A)</button>
                    <button class="subtab-btn" onclick="switchSubtab('schematics', 'sub-separator', this)">2. Separador Trifásico V-201 (ISA-5.1)</button>
                </div>

                <!-- Subtab 1: Árbol de Navidad -->
                <div id="sub-tree" class="subtab-content active">
                    <div class="diagram-container">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <h3 style="font-family: var(--font-heading); font-size: 1.05rem; color: #fff;">Árbol de Navidad e Instrumentación (Pozo ALFA-12)</h3>
                                <p style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">Ubicación física de los transmisores conectados a MySQL</p>
                            </div>
                            <span class="comp-badge badge-emerald">NORMA API 6A</span>
                        </div>

                        <svg class="diagram-svg" viewBox="0 0 920 400" xmlns="http://www.w3.org/2000/svg">
                            <rect x="40" y="360" width="840" height="15" fill="#1e293b" rx="3" />
                            <line x1="40" y1="360" x2="880" y2="360" stroke="#475569" stroke-dasharray="6 6" />
                            <text x="50" y="372" font-family="'JetBrains Mono', monospace" font-size="9" fill="#94a3b8">BODEGA DE POZO / SUPERFICIE (CELLAR)</text>

                            <!-- Spools -->
                            <rect x="200" y="300" width="100" height="60" fill="#334155" stroke="#475569" stroke-width="2" />
                            <text x="250" y="335" font-family="'JetBrains Mono', monospace" font-size="10" fill="#fff" text-anchor="middle">CASING HEAD</text>

                            <rect x="215" y="240" width="70" height="60" fill="#334155" stroke="#475569" stroke-width="2" />
                            <text x="250" y="275" font-family="'JetBrains Mono', monospace" font-size="9" fill="#fff" text-anchor="middle">TUBING SPOOL</text>

                            <!-- Válvula Maestra Inferior -->
                            <polygon points="220,220 280,220 250,200 220,180 280,180" fill="#0f172a" stroke="#38bdf8" stroke-width="2" />
                            <text x="310" y="205" font-family="'Inter', sans-serif" font-size="11" fill="#cbd5e1">Válvula Maestra Inferior (LMV)</text>

                            <!-- Válvula SSV -->
                            <polygon points="220,160 280,160 250,140 220,120 280,120" fill="#0f172a" stroke="#10b981" stroke-width="2" />
                            <rect x="185" y="125" width="25" height="30" fill="#064e3b" stroke="#10b981" stroke-width="1.5" rx="3" />
                            <text x="310" y="145" font-family="'Inter', sans-serif" font-size="11" fill="#34d399" font-weight="bold">Válvula SSV (Superficie / Hidráulica)</text>

                            <!-- Manómetro Tubing WHTP-101A -->
                            <circle cx="250" cy="45" r="18" fill="#0f172a" stroke="#38bdf8" stroke-width="2" />
                            <text x="250" y="48" font-family="'JetBrains Mono', monospace" font-size="8" fill="#38bdf8" text-anchor="middle" font-weight="bold">PT-101</text>
                            <line x1="250" y1="63" x2="250" y2="120" stroke="#38bdf8" stroke-width="2" />
                            <rect x="280" y="30" width="180" height="28" fill="#1e293b" stroke="#38bdf8" stroke-width="1" rx="4" />
                            <text x="290" y="44" font-family="'JetBrains Mono', monospace" font-size="8" fill="#94a3b8">WHTP-101A (Tubing)</text>
                            <text x="290" y="54" font-family="'JetBrains Mono', monospace" font-size="9" fill="#38bdf8" font-weight="bold">1,842.5 PSI en MySQL</text>

                            <!-- Wing & Choke -->
                            <rect x="280" y="130" width="100" height="20" fill="#334155" stroke="#475569" stroke-width="1.5" />
                            <polygon points="380,125 410,125 395,140 380,155 410,155" fill="#0f172a" stroke="#10b981" stroke-width="2" />

                            <rect x="410" y="130" width="80" height="20" fill="#334155" stroke="#475569" stroke-width="1.5" />
                            <polygon points="490,120 530,120 510,140 490,160 530,160" fill="#1e293b" stroke="#f59e0b" stroke-width="2" />
                            <rect x="500" y="95" width="20" height="25" fill="#78350f" stroke="#f59e0b" stroke-width="1.5" rx="3" />
                            <rect x="540" y="100" width="180" height="32" fill="#1e293b" stroke="#f59e0b" stroke-width="1" rx="4" />
                            <text x="550" y="114" font-family="'JetBrains Mono', monospace" font-size="8" fill="#94a3b8">FCV-101 (Estrangulador Choke)</text>
                            <text x="550" y="125" font-family="'JetBrains Mono', monospace" font-size="9" fill="#fbbf24" font-weight="bold">Apertura: 42.8% regulable</text>

                            <!-- Flujo hacia Manifold -->
                            <path d="M 530,140 L 800,140 L 800,240 L 870,240" fill="none" stroke="#10b981" stroke-width="5" class="pipe-flow" />
                            <polygon points="870,235 885,240 870,245" fill="#10b981" />
                            <text x="680" y="270" font-family="'JetBrains Mono', monospace" font-size="10" fill="#34d399" font-weight="bold">FLUIDO HACIA MANIFOLD MAN-400</text>

                            <!-- Casing -->
                            <rect x="120" y="280" width="80" height="15" fill="#334155" stroke="#475569" stroke-width="1.5" />
                            <circle cx="110" cy="287" r="16" fill="#0f172a" stroke="#fbbf24" stroke-width="2" />
                            <text x="110" y="290" font-family="'JetBrains Mono', monospace" font-size="7" fill="#fbbf24" text-anchor="middle" font-weight="bold">PT-102</text>
                            <rect x="20" y="220" width="160" height="30" fill="#1e293b" stroke="#fbbf24" stroke-width="1" rx="4" />
                            <text x="30" y="234" font-family="'JetBrains Mono', monospace" font-size="8" fill="#94a3b8">CASING-101B (Anular)</text>
                            <text x="30" y="244" font-family="'JetBrains Mono', monospace" font-size="9" fill="#fbbf24" font-weight="bold">642.0 PSI · Aviso</text>
                        </svg>
                    </div>
                </div>

                <!-- Subtab 2: Separador Trifásico -->
                <div id="sub-separator" class="subtab-content">
                    <div class="diagram-container">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <h3 style="font-family: var(--font-heading); font-size: 1.05rem; color: #fff;">Manifold y Separador Trifásico V-201</h3>
                                <p style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">Batería Huila · Separación de Gas, Petróleo y Agua</p>
                            </div>
                            <span class="comp-badge badge-blue">NORMA ANSI/ISA-5.1</span>
                        </div>

                        <svg class="diagram-svg" viewBox="0 0 940 320" xmlns="http://www.w3.org/2000/svg">
                            <rect x="30" y="60" width="90" height="50" fill="#1e293b" stroke="#10b981" stroke-width="1.5" rx="4" />
                            <text x="75" y="85" font-family="'JetBrains Mono', monospace" font-size="9" fill="#fff" text-anchor="middle" font-weight="bold">ALFA-12</text>
                            <text x="75" y="98" font-family="'JetBrains Mono', monospace" font-size="8" fill="#34d399" text-anchor="middle">4,210 BPD</text>

                            <rect x="30" y="130" width="90" height="50" fill="#1e293b" stroke="#10b981" stroke-width="1.5" rx="4" />
                            <text x="75" y="155" font-family="'JetBrains Mono', monospace" font-size="9" fill="#fff" text-anchor="middle" font-weight="bold">BRAVO-04</text>
                            <text x="75" y="168" font-family="'JetBrains Mono', monospace" font-size="8" fill="#34d399" text-anchor="middle">4,890 BPD</text>

                            <rect x="30" y="200" width="90" height="50" fill="#1e293b" stroke="#f59e0b" stroke-width="1.5" rx="4" />
                            <text x="75" y="225" font-family="'JetBrains Mono', monospace" font-size="9" fill="#fff" text-anchor="middle" font-weight="bold">CHARLIE-09</text>
                            <text x="75" y="238" font-family="'JetBrains Mono', monospace" font-size="8" fill="#fbbf24" text-anchor="middle">2,980 BPD</text>

                            <!-- Manifold -->
                            <rect x="180" y="40" width="30" height="230" fill="#334155" stroke="#64748b" stroke-width="2" rx="4" />
                            <text x="195" y="160" font-family="'JetBrains Mono', monospace" font-size="10" fill="#cbd5e1" text-anchor="middle" transform="rotate(-90 195 160)">MANIFOLD MAN-400</text>

                            <line x1="120" y1="85" x2="180" y2="85" stroke="#10b981" stroke-width="3" />
                            <line x1="120" y1="155" x2="180" y2="155" stroke="#10b981" stroke-width="3" />
                            <line x1="120" y1="225" x2="180" y2="225" stroke="#f59e0b" stroke-width="3" />

                            <path d="M 210,155 L 340,155" fill="none" stroke="#10b981" stroke-width="6" class="pipe-flow" />
                            <text x="275" y="145" font-family="'JetBrains Mono', monospace" font-size="9" fill="#94a3b8" text-anchor="middle">14,820 BPD</text>

                            <!-- Separador V-201 -->
                            <rect x="340" y="90" width="220" height="130" fill="#1e293b" stroke="#38bdf8" stroke-width="2" rx="20" />
                            <text x="450" y="125" font-family="'Space Grotesk', sans-serif" font-size="12" fill="#fff" font-weight="bold" text-anchor="middle">SEPARADOR TRIFÁSICO V-201</text>
                            <text x="450" y="145" font-family="'JetBrains Mono', monospace" font-size="9" fill="#94a3b8" text-anchor="middle">Presión: 450 PSI · Temp: 68°C</text>

                            <rect x="350" y="155" width="200" height="18" fill="rgba(245, 158, 11, 0.3)" rx="2" />
                            <text x="450" y="168" font-family="'JetBrains Mono', monospace" font-size="8" fill="#fbbf24" text-anchor="middle">FASE GAS (SUPERIOR)</text>

                            <rect x="350" y="175" width="200" height="20" fill="rgba(16, 185, 129, 0.4)" rx="2" />
                            <text x="450" y="189" font-family="'JetBrains Mono', monospace" font-size="8" fill="#34d399" text-anchor="middle">FASE CRUDO / PETRÓLEO</text>

                            <rect x="350" y="197" width="200" height="15" fill="rgba(14, 165, 233, 0.3)" rx="2" />
                            <text x="450" y="208" font-family="'JetBrains Mono', monospace" font-size="8" fill="#38bdf8" text-anchor="middle">FASE AGUA DE PRODUCCIÓN</text>

                            <!-- Salidas -->
                            <path d="M 450,90 L 450,45 L 680,45" fill="none" stroke="#fbbf24" stroke-width="4" class="pipe-flow" />
                            <rect x="680" y="25" width="180" height="40" fill="#1e293b" stroke="#fbbf24" stroke-width="1.5" rx="6" />
                            <text x="770" y="45" font-family="'Space Grotesk', sans-serif" font-size="10" fill="#fff" font-weight="bold" text-anchor="middle">COMPRESOR GAS C-301</text>
                            <text x="770" y="57" font-family="'JetBrains Mono', monospace" font-size="8" fill="#fbbf24" text-anchor="middle">3,200 MSCFD · 1,100 PSI</text>

                            <path d="M 560,185 L 680,185" fill="none" stroke="#10b981" stroke-width="5" class="pipe-flow" />
                            <rect x="680" y="160" width="180" height="50" fill="#1e293b" stroke="#10b981" stroke-width="1.5" rx="6" />
                            <text x="770" y="180" font-family="'Space Grotesk', sans-serif" font-size="10" fill="#fff" font-weight="bold" text-anchor="middle">FISCALIZACIÓN MFM-201</text>
                            <text x="770" y="195" font-family="'JetBrains Mono', monospace" font-size="8" fill="#34d399" text-anchor="middle">11,620 BPD NETOS A VENTA</text>

                            <path d="M 450,220 L 450,275 L 680,275" fill="none" stroke="#0ea5e9" stroke-width="4" />
                            <rect x="680" y="255" width="180" height="40" fill="#1e293b" stroke="#0ea5e9" stroke-width="1.5" rx="6" />
                            <text x="770" y="275" font-family="'Space Grotesk', sans-serif" font-size="10" fill="#fff" font-weight="bold" text-anchor="middle">TRATAMIENTO AGUA (SWD)</text>
                            <text x="770" y="287" font-family="'JetBrains Mono', monospace" font-size="8" fill="#38bdf8" text-anchor="middle">3,200 BWPD REINYECCIÓN</text>
                        </svg>
                    </div>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 5. MÓDULO: DICCIONARIO DE TAGS             -->
            <!-- ========================================== -->
            <section id="tab-tags" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">🏷️</div>
                        <div class="hero-text">
                            <h2>Diccionario de Instrumentación y Tags de Telemetría</h2>
                            <p>Definición formal de tags ISA-5.1 y su equivalencia con campos de la base de datos MySQL.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Norma: ANSI/ISA-5.1</span>
                                <span class="hero-tag">Metadatos de Campo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tag Industrial</th>
                                <th>Nombre Común del Equipo</th>
                                <th>Unidad Física</th>
                                <th>Rango Seguro</th>
                                <th>Función Técnica en la Batería</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>WHTP-101A</code></td>
                                <td>Transmisor de Presión de Tubing</td>
                                <td>PSI</td>
                                <td>850 – 2,100 PSI</td>
                                <td>Mide la fuerza del fluido al subir por la tubería central de producción.</td>
                            </tr>
                            <tr>
                                <td><code>CASING-101B</code></td>
                                <td>Transmisor de Presión Anular</td>
                                <td>PSI</td>
                                <td>100 – 800 PSI</td>
                                <td>Supervisa la integridad mecánica del espacio anular entre casing y tubing.</td>
                            </tr>
                            <tr>
                                <td><code>MFM-201</code></td>
                                <td>Caudalímetro Másico Coriolis</td>
                                <td>BPD</td>
                                <td>4,000 – 16,500 BPD</td>
                                <td>Mide el flujo másico neto de crudo y fiscaliza la venta en oleoducto.</td>
                            </tr>
                            <tr>
                                <td><code>TEMP-FLOW-301</code></td>
                                <td>Termoresistencia PT-100</td>
                                <td>°C</td>
                                <td>20 – 92 °C</td>
                                <td>Monitorea la temperatura del fluido para prevenir formación de parafinas.</td>
                            </tr>
                            <tr>
                                <td><code>ESP-FREQ-01</code></td>
                                <td>Variador de Frecuencia Bomba ESP</td>
                                <td>Hz</td>
                                <td>35 – 63.5 Hz</td>
                                <td>Regula las RPM del motor sumergible en fondo de pozo.</td>
                            </tr>
                            <tr>
                                <td><code>GOR-ANALYZER</code></td>
                                <td>Analizador Gas-Oil Ratio</td>
                                <td>SCF/BBL</td>
                                <td>200 – 550 SCF/BBL</td>
                                <td>Calcula la relación de volumen de gas libre por barril producido.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 6. MÓDULO: ARQUITECTURA & COMPARATIVAS     -->
            <!-- ========================================== -->
            <section id="tab-comparisons" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">📊</div>
                        <div class="hero-text">
                            <h2>Sustentación de Arquitectura de Software SENA</h2>
                            <p>Justificación técnica comparativa para la Ficha 3413988-ADSO: Patrones, Bases de Datos y Modos de Falla.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">ADSO SENA</span>
                                <span class="hero-tag">ACID vs Local</span>
                                <span class="hero-tag">Microservicios</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subtabs de Comparativas -->
                <div class="subtabs-bar">
                    <button class="subtab-btn active" onclick="switchSubtab('comparisons', 'sub-comp-arch', this)">1. Monolito vs Microservicios</button>
                    <button class="subtab-btn" onclick="switchSubtab('comparisons', 'sub-comp-db', this)">2. LocalStorage vs MySQL (mapaf_db)</button>
                    <button class="subtab-btn" onclick="switchSubtab('comparisons', 'sub-comp-modes', this)">3. Modos de Operación de Planta</button>
                </div>

                <!-- Subtab 1: Arquitectura -->
                <div id="sub-comp-arch" class="subtab-content active">
                    <div class="table-container">
                        <div class="table-header">
                            <div>
                                <span class="table-title">Monolito Tradicional vs Microservicios MAPAF</span>
                                <p style="font-size: 0.75rem; color: var(--text-muted);">Criterios de ingeniería de software para telemetría industrial</p>
                            </div>
                            <span class="comp-badge badge-emerald">ADSO FICHA 3413988</span>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Criterio</th>
                                    <th style="color: #fbbf24;">Arquitectura Monolítica</th>
                                    <th style="color: #34d399;">Microservicios (MAPAF)</th>
                                    <th>Impacto Operativo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Estructura</strong></td>
                                    <td>Un solo bloque unificado y acoplado.</td>
                                    <td>Servicios desacoplados (Auth, Telemetría, Pozos, Alarmas).</td>
                                    <td>Permite evolución y desarrollo en paralelo.</td>
                                </tr>
                                <tr>
                                    <td><strong>Persistencia de Datos</strong></td>
                                    <td>Base centralizada con acoplamiento de esquemas.</td>
                                    <td>MySQL relacional (<code>mapaf_db</code>) normalizado por entidades.</td>
                                    <td>Integridad referencial y persistencia transaccional.</td>
                                </tr>
                                <tr>
                                    <td><strong>Tolerancia a Fallos</strong></td>
                                    <td style="color: #f87171;">Efecto dominó: un error detiene el SCADA completo.</td>
                                    <td style="color: #34d399;">Aislamiento: si falla un servicio, la telemetría continúa.</td>
                                    <td>Disponibilidad 24/7 requerida en pozos upstream.</td>
                                </tr>
                                <tr>
                                    <td><strong>Escalabilidad</strong></td>
                                    <td>Monolítica horizontal completa (alto costo de recursos).</td>
                                    <td>Selectiva: escala solo el módulo bajo alta carga (ej. telemetría).</td>
                                    <td>Uso eficiente de cómputo en servidores XAMPP/Cloud.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Subtab 2: Persistencia -->
                <div id="sub-comp-db" class="subtab-content">
                    <div class="comparison-grid">
                        <div class="comp-card c-local">
                            <div class="comp-header">
                                <span class="comp-title">Persistencia Local (LocalStorage)</span>
                                <span class="comp-badge badge-amber">MODO ANTERIOR</span>
                            </div>
                            <ul class="comp-list">
                                <li><span class="comp-key">Alcance:</span><span class="comp-val" style="color: #f87171;">Aislado a un solo navegador</span></li>
                                <li><span class="comp-key">Concurrencia:</span><span class="comp-val" style="color: #f87171;">Sin multi-operador</span></li>
                                <li><span class="comp-key">Durabilidad:</span><span class="comp-val" style="color: #f87171;">Se borra al limpiar caché</span></li>
                                <li><span class="comp-key">Auditoría:</span><span class="comp-val" style="color: #f87171;">Fácilmente alterable en cliente</span></li>
                            </ul>
                        </div>

                        <div class="comp-card c-mysql">
                            <div class="comp-header">
                                <span class="comp-title">Base de Datos MySQL (mapaf_db en XAMPP)</span>
                                <span class="comp-badge badge-emerald">IMPLEMENTADO ✓</span>
                            </div>
                            <ul class="comp-list">
                                <li><span class="comp-key">Alcance:</span><span class="comp-val" style="color: #34d399;">Centralizado y accesible por red</span></li>
                                <li><span class="comp-key">Concurrencia:</span><span class="comp-val" style="color: #34d399;">Múltiples operadores simultáneos</span></li>
                                <li><span class="comp-key">Durabilidad:</span><span class="comp-val" style="color: #34d399;">Inmutable en disco con motor InnoDB</span></li>
                                <li><span class="comp-key">Auditoría:</span><span class="comp-val" style="color: #34d399;">Tabla bitácora con marcas de tiempo</span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Subtab 3: Modos de Operación -->
                <div id="sub-comp-modes" class="subtab-content">
                    <div class="table-container">
                        <div class="table-header">
                            <span class="table-title">Comportamiento en los Modos de Operación de Planta</span>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Parámetro</th>
                                    <th style="color: #34d399;">🟢 Operación Nominal</th>
                                    <th style="color: #fbbf24;">🟡 Modo Estrangulado</th>
                                    <th style="color: #f87171;">🔴 Parada de Emergencia (ESD-1)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Tasa Producción</strong></td>
                                    <td style="color: #34d399; font-weight: bold;">14,820 BPD (100%)</td>
                                    <td style="color: #fbbf24; font-weight: bold;">11,850 BPD (80%)</td>
                                    <td style="color: #f87171; font-weight: bold;">0 BPD (Aislamiento Total)</td>
                                </tr>
                                <tr>
                                    <td><strong>Válvulas SSV</strong></td>
                                    <td>100% Abiertas</td>
                                    <td>Abiertas</td>
                                    <td style="color: #f87171; font-weight: bold;">Cerradas en &lt; 2 segundos</td>
                                </tr>
                                <tr>
                                    <td><strong>Apertura Choke</strong></td>
                                    <td>42.8% regulado</td>
                                    <td>22.0% restringido</td>
                                    <td>0.0% (Cierre total)</td>
                                </tr>
                                <tr>
                                    <td><strong>Bombas ESP</strong></td>
                                    <td>58.2 Hz (VFD normal)</td>
                                    <td>52.0 Hz (Régimen preventivo)</td>
                                    <td>0.0 Hz (Desconectadas por interlock)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 7. MÓDULO: SEGURIDAD Y ROLES RBAC          -->
            <!-- ========================================== -->
            <section id="tab-security" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">👥</div>
                        <div class="hero-text">
                            <h2>Control de Acceso Basado en Roles (RBAC)</h2>
                            <p>Gestión de operadores, ingenieros y auditores registrados en MySQL.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Tablas: `usuarios`, `roles`</span>
                                <span class="hero-tag">Seguridad Industrial</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.25rem;">
                    <div class="table-container">
                        <div class="table-header">
                            <span class="table-title">Personal Autorizado en Planta</span>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre Completo</th>
                                    <th>Rol</th>
                                    <th>Sector</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="usersBody"></tbody>
                        </table>
                    </div>

                    <div class="table-container">
                        <div class="table-header">
                            <span class="table-title">Matriz de Roles y Niveles de Permiso</span>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Rol</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Permisos</th>
                                </tr>
                            </thead>
                            <tbody id="rolesBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 8. MÓDULO: BITÁCORA DE AUDITORÍA           -->
            <!-- ========================================== -->
            <section id="tab-audit" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">📜</div>
                        <div class="hero-text">
                            <h2>Bitácora de Maniobras y Trazabilidad Operacional</h2>
                            <p>Registro histórico inmutable de consignas de válvulas, acuse de alarmas y eventos críticos.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Tabla: `bitacora_auditoria`</span>
                                <span class="hero-tag">Inmutable (Append-only)</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <button class="btn-action" onclick="loadAuditLogs()">🔄 Recargar Bitácora</button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Operador</th>
                                <th>Rol</th>
                                <th>Acción Ejecutada</th>
                                <th>Categoría</th>
                                <th>Resultado</th>
                                <th>Detalles Técnicos</th>
                            </tr>
                        </thead>
                        <tbody id="auditBody"></tbody>
                    </table>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 7. MÓDULO: PATRONES DE DISEÑO & ANTIPATRONES -->
            <!-- ========================================== -->
            <section id="tab-patterns" class="section-view">
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">⚙️</div>
                        <div class="hero-text">
                            <h2>Patrones de Diseño (GoF) & Antipatrones de Software</h2>
                            <p>Sustentación formal para SENA ADSO (Ficha 3413988): Creacionales, Estructurales, Comportamiento y Arquitectónicos.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">GoF Design Patterns</span>
                                <span class="hero-tag">8 Antipatrones Mitigados</span>
                                <span class="hero-tag">PHP 8.0 OOP</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="api/patterns.php" target="_blank" class="btn-action">🔍 Ver API REST de Patrones (JSON)</a>
                    </div>
                </div>

                <!-- Subtabs de Patrones -->
                <div class="subtabs-bar">
                    <button class="subtab-btn active" onclick="switchSubtab('patterns', 'sub-pat-lab', this)">1. Laboratorio en Vivo (PHP)</button>
                    <button class="subtab-btn" onclick="switchSubtab('patterns', 'sub-pat-anti', this)">2. Antipatrones de Software (8)</button>
                    <button class="subtab-btn" onclick="switchSubtab('patterns', 'sub-pat-matrix', this)">3. Matriz Académica SENA</button>
                </div>

                <!-- Subtab 1: Laboratorio en Vivo -->
                <div id="sub-pat-lab" class="subtab-content active">
                    <div class="comparison-grid" style="grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));">
                        
                        <!-- Singleton -->
                        <div class="comp-card" style="border-top: 3px solid #6366f1;">
                            <div class="comp-header">
                                <span class="comp-title">1. Singleton (DatabaseSingleton.php)</span>
                                <span class="comp-badge badge-blue">CREACIONAL</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                Garantiza una única conexión PDO a MySQL para evitar agotar el pool de sockets en XAMPP.
                            </p>
                            <button class="btn-action" onclick="testPatternPhp('test_singleton', 'singletonResult')" style="width: 100%; justify-content: center;">
                                ▶ Probar Unicidad de Instancia
                            </button>
                            <div id="singletonResult" style="margin-top: 0.75rem; font-family: var(--font-mono); font-size: 0.7rem; color: #34d399; background: #060913; padding: 0.5rem; border-radius: 4px; display: none;"></div>
                        </div>

                        <!-- Command con UNDO -->
                        <div class="comp-card" style="border-top: 3px solid #10b981;">
                            <div class="comp-header">
                                <span class="comp-title">2. Command con UNDO (ScadaCommand.php)</span>
                                <span class="comp-badge badge-emerald">COMPORTAMIENTO</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                Encapsula maniobras críticas de pozo y estrangulador con reversión matemática y auditoría.
                            </p>
                            <div style="display: flex; gap: 0.4rem;">
                                <button class="btn-action" onclick="testPatternPhp('test_command', 'commandResult', {command: 'choke', targetOpening: 65})" style="flex: 1; justify-content: center;">
                                    ▶ Ejecutar Choke
                                </button>
                                <button class="btn-action" onclick="testPatternPhp('test_command', 'commandResult', {command: 'choke', runUndo: true})" style="flex: 1; justify-content: center; background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: #f59e0b;">
                                    ↺ Probar UNDO
                                </button>
                            </div>
                            <div id="commandResult" style="margin-top: 0.75rem; font-family: var(--font-mono); font-size: 0.7rem; color: #38bdf8; background: #060913; padding: 0.5rem; border-radius: 4px; display: none;"></div>
                        </div>

                        <!-- Observer -->
                        <div class="comp-card" style="border-top: 3px solid #38bdf8;">
                            <div class="comp-header">
                                <span class="comp-title">3. Observer (TelemetryObserver.php)</span>
                                <span class="comp-badge badge-blue">COMPORTAMIENTO</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                Notifica reactivamente a la alarma ISA-18.2, al logger de auditoría y al enclavamiento ESD-1 ante sobrepresión.
                            </p>
                            <button class="btn-action" onclick="testPatternPhp('test_observer', 'observerResult', {value: 2450})" style="width: 100%; justify-content: center;">
                                ▶ Simular Sobrepresión (2450 PSI)
                            </button>
                            <div id="observerResult" style="margin-top: 0.75rem; font-family: var(--font-mono); font-size: 0.7rem; color: #f43f5e; background: #060913; padding: 0.5rem; border-radius: 4px; display: none;"></div>
                        </div>

                        <!-- Strategy -->
                        <div class="comp-card" style="border-top: 3px solid #a855f7;">
                            <div class="comp-header">
                                <span class="comp-title">4. Strategy (FlowCalculationStrategy.php)</span>
                                <span class="comp-badge badge-purple">COMPORTAMIENTO</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                Compara algoritmos de producción: Coriolis Fiscal (AGA-11) vs Orificio (AGA-3) vs Beggs & Brill.
                            </p>
                            <button class="btn-action" onclick="testPatternPhp('test_strategy', 'strategyResult')" style="width: 100%; justify-content: center;">
                                ▶ Comparar 3 Algoritmos de Flujo
                            </button>
                            <div id="strategyResult" style="margin-top: 0.75rem; font-family: var(--font-mono); font-size: 0.7rem; color: #c084fc; background: #060913; padding: 0.5rem; border-radius: 4px; display: none;"></div>
                        </div>

                    </div>
                </div>

                <!-- Subtab 2: Antipatrones -->
                <div id="sub-pat-anti" class="subtab-content">

                    <!-- Banco de Pruebas en Vivo de Antipatrones -->
                    <div style="background: linear-gradient(135deg, rgba(16, 24, 44, 0.95), rgba(10, 15, 29, 0.95)); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.25rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <span class="comp-badge badge-emerald">ESCUDO ACTIVO EN EJECUCIÓN</span>
                                <h3 style="font-family: var(--font-heading); font-size: 1.05rem; color: #fff; margin-top: 0.25rem;">Laboratorio Operacional de Mitigación de Antipatrones</h3>
                                <p style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono);">Ejecute pruebas dinámicas para comprobar cómo MAPAF neutraliza malas prácticas arquitectónicas.</p>
                            </div>
                        </div>

                        <div class="comparison-grid" style="grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); margin-bottom: 0;">
                            <!-- Sandbox 1: SQL Injection -->
                            <div class="comp-card" style="border-top: 3px solid #f43f5e;">
                                <div class="comp-header">
                                    <span class="comp-title" style="font-size: 0.95rem;">1. Escudo SQL Injection (PDO)</span>
                                    <span class="comp-badge" style="background: rgba(244,63,94,0.2); color: #f43f5e;">MITIGADO</span>
                                </div>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.65rem;">
                                    Inyecte una carga maliciosa clásica y verifique cómo los Prepared Statements con enlace tipado neutralizan el ataque.
                                </p>
                                <div style="display: flex; gap: 0.35rem; margin-bottom: 0.5rem;">
                                    <input type="text" id="sqliTestInput" value="' OR '1'='1' --" style="flex: 1; background: #060913; border: 1px solid var(--border-color); color: #f43f5e; font-family: var(--font-mono); font-size: 0.72rem; padding: 0.35rem; border-radius: 4px;">
                                    <button class="btn-action" onclick="testPatternPhp('test_antipattern_sqli', 'sqliResultBox', {input: document.getElementById('sqliTestInput').value})" style="background: rgba(244,63,94,0.2); color: #fda4af; border-color: #f43f5e; font-size: 0.7rem;">
                                        🛡️ Probar
                                    </button>
                                </div>
                                <div id="sqliResultBox" style="font-family: var(--font-mono); font-size: 0.7rem; color: #34d399; background: #060913; padding: 0.5rem; border-radius: 4px; display: none;"></div>
                            </div>

                            <!-- Sandbox 2: Blind Alarm Acknowledgment -->
                            <div class="comp-card" style="border-top: 3px solid #f59e0b;">
                                <div class="comp-header">
                                    <span class="comp-title" style="font-size: 0.95rem;">2. Escudo Blind Alarm ACK</span>
                                    <span class="comp-badge badge-amber">ANSI/ISA-18.2</span>
                                </div>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.65rem;">
                                    Pruebe silenciar una alarma sin ingresar justificación técnica para comprobar el bloqueo automático del protocolo.
                                </p>
                                <button class="btn-action" onclick="openAckModal('ALM-001', 'WHTP-101A (Tubing)', 'critical', '2,450.0 PSI', '2,100.0 PSI')" style="width: 100%; justify-content: center; background: rgba(245,158,11,0.15); color: #fbbf24; border-color: #f59e0b;">
                                    ⚡ Abrir Modal ISA-18.2 y Probar Blind ACK
                                </button>
                            </div>

                            <!-- Sandbox 3: God Object vs DDD -->
                            <div class="comp-card" style="border-top: 3px solid #38bdf8;">
                                <div class="comp-header">
                                    <span class="comp-title" style="font-size: 0.95rem;">3. Escudo God Object (7 Dominios)</span>
                                    <span class="comp-badge badge-blue">DDD ARCH</span>
                                </div>
                                <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.65rem;">
                                    Verifique el desacoplamiento modular en 6 microcontroladores especializados y 7 Bounded Contexts.
                                </p>
                                <button class="btn-action" onclick="testPatternPhp('test_antipattern_god_object', 'godObjResultBox')" style="width: 100%; justify-content: center;">
                                    🏛️ Inspeccionar Dominios Aislados
                                </button>
                                <div id="godObjResultBox" style="font-family: var(--font-mono); font-size: 0.7rem; color: #38bdf8; background: #060913; padding: 0.5rem; border-radius: 4px; display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <div class="table-header">
                            <div>
                                <span class="table-title">Catálogo de Antipatrones de Software Mitigados en MAPAF</span>
                                <p style="font-size: 0.75rem; color: var(--text-muted);">Análisis de malas prácticas industriales y sus patrones de solución</p>
                            </div>
                            <span class="comp-badge badge-amber">8 ANTIPATRONES RESUELTOS</span>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Antipatrón</th>
                                    <th>Severidad</th>
                                    <th>Síntoma en Código</th>
                                    <th>Riesgo Operacional SCADA</th>
                                    <th style="color: #34d399;">Patrón Solución en MAPAF</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>The God Object / Blob</strong></td>
                                    <td><span class="comp-badge" style="background: rgba(244,63,94,0.2); color: #f43f5e;">CRÍTICO</span></td>
                                    <td>Un archivo monolítico gigante que hace queries, HTML y matemáticas sin modularizar.</td>
                                    <td>Un error tipográfico en vista detiene la recolección de telemetría de todos los pozos.</td>
                                    <td><strong style="color: #34d399;">Clean Architecture + Repository + DDD</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>SQL Injection por Concatenación</strong></td>
                                    <td><span class="comp-badge" style="background: rgba(244,63,94,0.2); color: #f43f5e;">CRÍTICO</span></td>
                                    <td>Variables de $_POST concatenadas directamente en strings de consulta SQL.</td>
                                    <td>Atacante altera apertura de estranguladores causando sobrepresión y estallido.</td>
                                    <td><strong style="color: #34d399;">Prepared Statements PDO Tipados</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Hardcoded Secrets</strong></td>
                                    <td><span class="comp-badge badge-amber">ALTO</span></td>
                                    <td>Contraseñas de MySQL y claves JWT quemadas en el código fuente del repo.</td>
                                    <td>Suplantación de identidad del operador líder y anulación de paradas ESD.</td>
                                    <td><strong style="color: #34d399;">Singleton de Configuración + getenv()</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Blind Alarm Acknowledgment</strong></td>
                                    <td><span class="comp-badge" style="background: rgba(244,63,94,0.2); color: #f43f5e;">CRÍTICO</span></td>
                                    <td>Silenciar alarmas críticas sin exigir operador autenticado ni causa justificada.</td>
                                    <td>Oculta fugas de gas tóxico H2S violando la norma ANSI/ISA-18.2.</td>
                                    <td><strong style="color: #34d399;">Command Pattern + Firma JWT + Auditoría</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>The Golden Hammer</strong></td>
                                    <td><span class="comp-badge badge-amber">ALTO</span></td>
                                    <td>Pretender usar LocalStorage del navegador para toda la base de datos de la planta.</td>
                                    <td>Falta de concurrencia multioperador y pérdida de datos al limpiar caché.</td>
                                    <td><strong style="color: #34d399;">Persistencia Relacional MySQL (mapaf_db)</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Polling Flood / Busy Waiting</strong></td>
                                    <td><span class="comp-badge badge-blue">MEDIO</span></td>
                                    <td>Peticiones HTTP GET cada 50ms sin control saturando Apache y MySQL.</td>
                                    <td>Agotamiento de sockets ("Too many connections") bloqueando la sala de control.</td>
                                    <td><strong style="color: #34d399;">Observer + Throttling Adaptativo</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Bypass sin Enclavamiento</strong></td>
                                    <td><span class="comp-badge" style="background: rgba(244,63,94,0.2); color: #f43f5e;">CRÍTICO</span></td>
                                    <td>Rearmar parada de emergencia ESD con un simple clic sin confirmación de 3s.</td>
                                    <td>Rearmar un pozo mientras personal mecánico interviene la línea en campo.</td>
                                    <td><strong style="color: #34d399;">State Pattern + HoldInterlockModal (3s)</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Anemic Domain Model</strong></td>
                                    <td><span class="comp-badge badge-blue">MEDIO</span></td>
                                    <td>Clases sin reglas de validación que permiten chokes negativos (-50%) o presiones absurdas.</td>
                                    <td>Corrupción de datos de producción y fallas en cálculos termodinámicos.</td>
                                    <td><strong style="color: #34d399;">Domain-Driven Design (DDD) con Invariantes</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Subtab 3: Matriz Académica -->
                <div id="sub-pat-matrix" class="subtab-content">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Patrón GoF</th>
                                    <th>Tipo</th>
                                    <th>Archivo PHP</th>
                                    <th>Problema Resuelto en MAPAF</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Singleton</strong></td>
                                    <td>Creacional</td>
                                    <td><code>patterns/Singleton/DatabaseSingleton.php</code></td>
                                    <td>Mantiene una única conexión PDO a MySQL para todas las peticiones.</td>
                                </tr>
                                <tr>
                                    <td><strong>Factory Method</strong></td>
                                    <td>Creacional</td>
                                    <td><code>patterns/Factory/SensorFactory.php</code></td>
                                    <td>Crea sensores PT, TT, FT con validaciones y límites ISA-18.2.</td>
                                </tr>
                                <tr>
                                    <td><strong>Observer</strong></td>
                                    <td>Comportamiento</td>
                                    <td><code>patterns/Observer/TelemetryObserver.php</code></td>
                                    <td>Dispara alarmas, bitácora y parada ESD ante sobrepresión en pozo.</td>
                                </tr>
                                <tr>
                                    <td><strong>Command</strong></td>
                                    <td>Comportamiento</td>
                                    <td><code>patterns/Command/ScadaCommand.php</code></td>
                                    <td>Encapsula consignas de choke y válvulas con capacidad de UNDO.</td>
                                </tr>
                                <tr>
                                    <td><strong>Strategy</strong></td>
                                    <td>Comportamiento</td>
                                    <td><code>patterns/Strategy/FlowCalculationStrategy.php</code></td>
                                    <td>Alterna algoritmos de fiscalización AGA-11, AGA-3 y Beggs & Brill.</td>
                                </tr>
                                <tr>
                                    <td><strong>State</strong></td>
                                    <td>Comportamiento</td>
                                    <td><code>patterns/State/WellheadState.php</code></td>
                                    <td>Modela el ciclo de vida del pozo (API 6A) con enclavamientos legales.</td>
                                </tr>
                                <tr>
                                    <td><strong>Adapter</strong></td>
                                    <td>Estructural</td>
                                    <td><code>patterns/Adapter/IndustrialProtocolAdapter.php</code></td>
                                    <td>Convierte tramas Modbus RTU binarias y OPC-UA a JSON estándar.</td>
                                </tr>
                                <tr>
                                    <td><strong>Repository</strong></td>
                                    <td>Arquitectónico</td>
                                    <td><code>patterns/Repository/ScadaRepositories.php</code></td>
                                    <td>Desacopla lógica de negocio del acceso directo a MySQL mapaf_db.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 8. MÓDULO: DOMINIOS DEL SISTEMA (DDD)      -->
            <!-- ========================================== -->
            <section id="tab-domains" class="section-view">
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">🏛️</div>
                        <div class="hero-text">
                            <h2>Dominios del Sistema (Domain-Driven Design - DDD)</h2>
                            <p>Delimitación de los 7 Bounded Contexts de telemetría industrial y normas internacionales.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">7 Bounded Contexts</span>
                                <span class="hero-tag">Normas: ISA-18.2, API 6A, IEC 61511</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="api/domains.php" target="_blank" class="btn-action">🔍 Ver API de Dominios (JSON)</a>
                    </div>
                </div>

                <div class="comparison-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
                    <div class="comp-card" style="border-top: 3px solid #10b981;">
                        <div class="comp-header">
                            <span class="comp-title">1. Telemetría e Instrumentación</span>
                            <span class="comp-badge badge-emerald">ISA-5.1</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Supervisión física de presión, temperatura, flujo y frecuencia ESP.</p>
                        <div style="font-size: 0.7rem; font-family: var(--font-mono); color: #cbd5e1; margin-top: 0.5rem;">
                            <strong>Entidades:</strong> TelemetryMetric, InstrumentNode, SensorCalibration
                        </div>
                    </div>

                    <div class="comp-card" style="border-top: 3px solid #38bdf8;">
                        <div class="comp-header">
                            <span class="comp-title">2. Control de Pozos y Actuadores</span>
                            <span class="comp-badge badge-blue">API 6A</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Apertura micrométrica de Chokes, válvulas de superficie SSV y bombas ESP.</p>
                        <div style="font-size: 0.7rem; font-family: var(--font-mono); color: #cbd5e1; margin-top: 0.5rem;">
                            <strong>Entidades:</strong> WellheadUnit, ChokeActuator, SurfaceSafetyValve
                        </div>
                    </div>

                    <div class="comp-card" style="border-top: 3px solid #f43f5e;">
                        <div class="comp-header">
                            <span class="comp-title">3. Seguridad SIS & Paradas ESD</span>
                            <span class="comp-badge" style="background: rgba(244,63,94,0.2); color: #f43f5e;">IEC 61511</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Enclavamiento causa-efecto ESD-1 total y despresurización a antorcha.</p>
                        <div style="font-size: 0.7rem; font-family: var(--font-mono); color: #cbd5e1; margin-top: 0.5rem;">
                            <strong>Entidades:</strong> EsdInterlockMatrix, SafetyNode, BypassKey
                        </div>
                    </div>

                    <div class="comp-card" style="border-top: 3px solid #f59e0b;">
                        <div class="comp-header">
                            <span class="comp-title">4. Gestión de Alarmas</span>
                            <span class="comp-badge badge-amber">ISA-18.2</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Detección, priorización, filtrado de avalanchas y acuse por operador.</p>
                        <div style="font-size: 0.7rem; font-family: var(--font-mono); color: #cbd5e1; margin-top: 0.5rem;">
                            <strong>Entidades:</strong> AlarmEvent, AlarmDefinition, SuppressionRule
                        </div>
                    </div>

                    <div class="comp-card" style="border-top: 3px solid #a855f7;">
                        <div class="comp-header">
                            <span class="comp-title">5. Identidad & Sesión JWT</span>
                            <span class="comp-badge badge-purple">RFC 7519</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Autenticación criptográfica, firma HMAC SHA-256 y matriz de roles RBAC.</p>
                        <div style="font-size: 0.7rem; font-family: var(--font-mono); color: #cbd5e1; margin-top: 0.5rem;">
                            <strong>Entidades:</strong> SecurityUser, UserRole, JwtToken
                        </div>
                    </div>

                    <div class="comp-card" style="border-top: 3px solid #14b8a6;">
                        <div class="comp-header">
                            <span class="comp-title">6. Auditoría y Trazabilidad</span>
                            <span class="comp-badge" style="background: rgba(20,184,166,0.2); color: #2dd4bf;">21 CFR 11</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Bitácora inmutable append-only con timestamp, código de operador y delta.</p>
                        <div style="font-size: 0.7rem; font-family: var(--font-mono); color: #cbd5e1; margin-top: 0.5rem;">
                            <strong>Entidades:</strong> AuditLogEntry, HistorianArchive
                        </div>
                    </div>

                    <div class="comp-card" style="border-top: 3px solid #6366f1;">
                        <div class="comp-header">
                            <span class="comp-title">7. Producción y Balance Fiscal</span>
                            <span class="comp-badge badge-blue">API MPMS</span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Fiscalización de crudo neto BPD, gas MSCFD y corrección térmica 60°F.</p>
                        <div style="font-size: 0.7rem; font-family: var(--font-mono); color: #cbd5e1; margin-top: 0.5rem;">
                            <strong>Entidades:</strong> ProductionReport, FiscalMeter, WellAllocation
                        </div>
                    </div>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 9. MÓDULO: SEGURIDAD & JWT                 -->
            <!-- ========================================== -->
            <section id="tab-jwt" class="section-view">
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">🔐</div>
                        <div class="hero-text">
                            <h2>Autenticación Criptográfica JWT (RFC 7519)</h2>
                            <p>Emisión y verificación de JSON Web Tokens con algoritmo HMAC SHA-256 en PHP nativo.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Algoritmo: HS256</span>
                                <span class="hero-tag">Claims RBAC</span>
                                <span class="hero-tag">Vigencia: 8 Horas</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="api/login.php" target="_blank" class="btn-action">🔍 Endpoint api/login.php</a>
                    </div>
                </div>

                <div class="table-container" style="padding: 1.25rem;">
                    <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; margin-bottom: 1.25rem;">
                        <div>
                            <label style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--text-muted); display: block; margin-bottom: 0.3rem;">Operador a Autenticar:</label>
                            <select id="jwtOperatorSelect" style="background: #060913; border: 1px solid var(--border-color); color: #fff; padding: 0.45rem; font-family: var(--font-mono); font-size: 0.75rem; border-radius: 6px;">
                                <option value="OP-449">OP-449 · María José Conde Dussan (Administrador)</option>
                                <option value="OP-312">OP-312 · Hernann Thomas Manchola Bocanegra (Ingeniero)</option>
                                <option value="OP-205">OP-205 · Emilin Tatiana Vargas Manchola (Supervisor)</option>
                                <option value="OP-118">OP-118 · Javier Humberto Pinto Díaz (Instructor SENA)</option>
                            </select>
                        </div>
                        <button class="btn-action" onclick="generateJwtFromPhp()" style="background: #a855f7; color: #fff; font-weight: bold;">
                            🔑 Generar y Firmar Token JWT en PHP
                        </button>
                    </div>

                    <div id="jwtLiveOutput" style="display: none; background: #060913; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; font-family: var(--font-mono); font-size: 0.75rem;">
                        <div style="color: #c084fc; font-weight: bold; margin-bottom: 0.5rem;">✓ Token JWT Emitido (Header.Payload.Signature):</div>
                        <div id="jwtTokenString" style="word-break: break-all; color: #34d399; margin-bottom: 1rem; padding: 0.5rem; background: #0b1122; border-radius: 4px;"></div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.75rem;">
                            <div style="background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.3); padding: 0.75rem; border-radius: 6px;">
                                <div style="color: #f43f5e; font-weight: bold;">1. Header:</div>
                                <pre style="font-size: 0.7rem; color: #cbd5e1;">{"typ":"JWT","alg":"HS256"}</pre>
                            </div>
                            <div style="background: rgba(168,85,247,0.1); border: 1px solid rgba(168,85,247,0.3); padding: 0.75rem; border-radius: 6px;">
                                <div style="color: #c084fc; font-weight: bold;">2. Payload (Claims):</div>
                                <div id="jwtPayloadDetails" style="font-size: 0.7rem; color: #cbd5e1; margin-top: 0.25rem;"></div>
                            </div>
                            <div style="background: rgba(14,165,233,0.1); border: 1px solid rgba(14,165,233,0.3); padding: 0.75rem; border-radius: 6px;">
                                <div style="color: #38bdf8; font-weight: bold;">3. Signature:</div>
                                <div style="font-size: 0.7rem; color: #34d399;">HMAC-SHA256 Verificada con éxito</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ========================================== -->
            <!-- 10. MÓDULO: INFRAESTRUCTURA & TOPOLOGÍA SCADA -->
            <!-- ========================================== -->
            <section id="tab-diag" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">🏗️</div>
                        <div class="hero-text">
                            <h2>Infraestructura del Sistema SCADA & Topología de Red IIoT</h2>
                            <p>Arquitectura de 6 Capas para SENA ADSO (Ficha 3413988): Instrumentación, Pasarela de Protocolos, Backend PHP 8, Dominio DDD, Base de Datos MySQL y HMI Web.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Apache 2.4 (Port 80)</span>
                                <span class="hero-tag">MySQL 10.4 (Port 3306)</span>
                                <span class="hero-tag">PHP <?= phpversion() ?> OPcache</span>
                                <span class="hero-tag">Clean Architecture</span>
                                <span class="hero-tag">7 Bounded Contexts</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="/phpmyadmin/index.php?route=/database/structure&db=mapaf_db" target="_blank" class="btn-action">
                            🗄️ Abrir phpMyAdmin (mapaf_db)
                        </a>
                        <button class="btn-action" onclick="pingInfrastructure()">
                            ⚡ Test de Latencia & Salud
                        </button>
                    </div>
                </div>

                <!-- 1. Monitor de Salud de la Infraestructura en Vivo (6 Tarjetas con Ping Reactivo) -->
                <div class="infra-grid">
                    <!-- Servidor Web Apache -->
                    <div class="infra-card">
                        <div class="infra-card-header">
                            <span class="infra-card-title">🌐 Servidor Web Apache</span>
                            <span class="badge-pulse badge-pulse-green">PUERTO 80</span>
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #cbd5e1; line-height: 1.6;">
                            <div><strong>Versión:</strong> Apache 2.4 (Win64)</div>
                            <div><strong>Protocolo:</strong> HTTP/1.1 con Cabeceras CORS</div>
                            <div><strong>Host Local:</strong> <code>localhost</code> (127.0.0.1)</div>
                            <div><strong>Estado:</strong> <span style="color:#34d399; font-weight:bold;">EN LÍNEA (Escuchando)</span></div>
                        </div>
                    </div>

                    <!-- Motor PHP Backend -->
                    <div class="infra-card">
                        <div class="infra-card-header">
                            <span class="infra-card-title">🐘 Motor Backend PHP</span>
                            <span class="badge-pulse badge-pulse-green">PHP <?= phpversion() ?></span>
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #cbd5e1; line-height: 1.6;">
                            <div><strong>SAPI:</strong> <?= php_sapi_name() ?></div>
                            <div><strong>PDO MySQL:</strong> <span style="color:#34d399;">Habilitado (Tipado Nativo)</span></div>
                            <div><strong>Criptografía:</strong> <span style="color:#34d399;">OpenSSL HMAC-SHA256</span></div>
                            <div><strong>Singleton Pool:</strong> <span style="color:#38bdf8;">Instancia Única Activa</span></div>
                        </div>
                    </div>

                    <!-- Base de Datos MySQL -->
                    <div class="infra-card">
                        <div class="infra-card-header">
                            <span class="infra-card-title">🐬 Base de Datos MySQL</span>
                            <span class="badge-pulse badge-pulse-green">PUERTO 3306</span>
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #cbd5e1; line-height: 1.6;">
                            <div><strong>Base de Datos:</strong> <code>mapaf_db</code></div>
                            <div><strong>Tablas Relacionales:</strong> <span style="color:#34d399; font-weight:bold;"><?= count($dbStatus['tables']) ?> tablas verificadas</span></div>
                            <div><strong>Motor de Tablas:</strong> InnoDB (ACID Compliance)</div>
                            <div><strong>Estado:</strong> <span style="color:#34d399; font-weight:bold;">Conectado vía PDO Singleton</span></div>
                        </div>
                    </div>

                    <!-- Motor de Ciberseguridad JWT -->
                    <div class="infra-card">
                        <div class="infra-card-header">
                            <span class="infra-card-title">🛡️ Ciberseguridad JWT</span>
                            <span class="badge-pulse badge-pulse-green">RFC 7519</span>
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #cbd5e1; line-height: 1.6;">
                            <div><strong>Firma:</strong> HMAC-SHA256 (Alg: HS256)</div>
                            <div><strong>Modelo:</strong> RBAC (Role-Based Access Control)</div>
                            <div><strong>Roles Configurados:</strong> 5 perfiles (SYS-ADMIN, etc.)</div>
                            <div><strong>Trazabilidad:</strong> Bitácora 21 CFR Part 11</div>
                        </div>
                    </div>

                    <!-- Enlace Simbólico Junction -->
                    <div class="infra-card">
                        <div class="infra-card-header">
                            <span class="infra-card-title">🔗 Enlace XAMPP (Junction)</span>
                            <span class="badge-pulse badge-pulse-green">HOT-RELOAD</span>
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #cbd5e1; line-height: 1.6;">
                            <div><strong>Directorio XAMPP:</strong> <code>C:\xampp\htdocs\mapaf</code></div>
                            <div><strong>Proyecto Local:</strong> <code>PROYECTO-MAPAF\backend_php</code></div>
                            <div><strong>Tipo:</strong> Junction NTFS (Sincronización Inmediata)</div>
                            <div><strong>Estado:</strong> <span style="color:#34d399; font-weight:bold;">Vinculado 100%</span></div>
                        </div>
                    </div>

                    <!-- Latencia y Telemetría -->
                    <div class="infra-card">
                        <div class="infra-card-header">
                            <span class="infra-card-title">⏱️ Latencia & Telemetría</span>
                            <span class="badge-pulse badge-pulse-green" id="infraPingBadge">TIEMPO REAL</span>
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #cbd5e1; line-height: 1.6;">
                            <div><strong>Ciclo SCADA:</strong> 3.0 segundos (Heartbeat)</div>
                            <div><strong>Ping REST:</strong> <span id="infraLiveLatency" style="color:#34d399; font-weight:bold;">12 ms</span> (Roundtrip)</div>
                            <div><strong>Pila de Maniobras:</strong> <span id="infraStackDisplay" style="color:#a855f7;">0 comandos en pila</span></div>
                            <div><strong>Estado de Red:</strong> <span style="color:#34d399; font-weight:bold;">Excelente (Sin pérdidas)</span></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Diagrama Visual Interactivo de las 6 Capas de la Infraestructura -->
                <div style="background: linear-gradient(135deg, rgba(16, 24, 44, 0.95), rgba(10, 15, 29, 0.95)); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <span class="comp-badge badge-blue">TOPOLOGÍA ARQUITECTÓNICA</span>
                        <h3 style="font-family: var(--font-heading); font-size: 1.05rem; color: #fff; margin-top: 0.25rem;">
                            Las 6 Capas de la Infraestructura SCADA MAPAF
                        </h3>
                        <p style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">
                            Estructura de desacoplamiento modular bajo el estándar ISA-95 (Niveles 1 a 3) y Clean Architecture.
                        </p>
                    </div>

                    <div class="layers-timeline">
                        <!-- Capa 1 -->
                        <div class="layer-box" style="border-left-color: #f43f5e;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong style="color: #f43f5e; font-size: 0.85rem;">CAPA 1: Instrumentación de Campo & Actuadores (Field Level)</strong>
                                <span class="badge-pulse" style="background: rgba(244,63,94,0.15); color: #fda4af; border: 1px solid rgba(244,63,94,0.3);">ISA-5.1 · API 6A</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.35rem;">
                                Sensores físicos y cabezales de pozo: Transmisores de presión (PT-101 Tubing, PT-102 Casing), Caudalímetro Coriolis (FT-201), Temperatura (TT-301), Bomba electrosumergible (ESP-01), Estranguladores Choke (FCV) y Válvulas Maestras SSV de superficie.
                            </p>
                            <div style="font-family: var(--font-mono); font-size: 0.7rem; color: #cbd5e1;">
                                📍 <strong>Entidades:</strong> 4 Cabezales de Pozo (Alfa-12, Bravo-04, Charlie-09, Delta-02) + 6 Variables de Proceso Continuas.
                            </div>
                        </div>

                        <!-- Capa 2 -->
                        <div class="layer-box" style="border-left-color: #f59e0b;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong style="color: #f59e0b; font-size: 0.85rem;">CAPA 2: Pasarela de Adquisición & Adaptadores Industriales (Gateway)</strong>
                                <span class="badge-pulse" style="background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3);">MODBUS · OPC-UA</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.35rem;">
                                Transforma tramas binarias industriales heterogéneas (registros Modbus RTU en formato IEEE 754 float y nodos OPC-UA) hacia el formato JSON unificado de telemetría mediante el patrón GoF <code>IndustrialProtocolAdapter</code>.
                            </p>
                            <div style="font-family: var(--font-mono); font-size: 0.7rem; color: #cbd5e1;">
                                📍 <strong>Archivo Backend:</strong> <code>backend_php/patterns/Adapter/IndustrialProtocolAdapter.php</code>
                            </div>
                        </div>

                        <!-- Capa 3 -->
                        <div class="layer-box" style="border-left-color: #38bdf8;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong style="color: #38bdf8; font-size: 0.85rem;">CAPA 3: API Gateway & Microcontroladores Especializados (REST PHP 8)</strong>
                                <span class="badge-pulse" style="background: rgba(56,189,248,0.15); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3);">MICROCONTROLLERS</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.35rem;">
                                Elimina el antipatrón The God Object al dividir las peticiones en 6 controladores con responsabilidad única:
                            </p>
                            <div style="font-family: var(--font-mono); font-size: 0.7rem; color: #cbd5e1; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <span>• <code>api/sync.php</code> (Telemetría)</span>
                                <span>• <code>api/wellheads.php</code> (Pozos & Command)</span>
                                <span>• <code>api/alarms.php</code> (ISA-18.2)</span>
                                <span>• <code>api/patterns.php</code> (Laboratorio GoF)</span>
                                <span>• <code>api/login.php</code> (JWT RBAC)</span>
                                <span>• <code>api/audit.php</code> (21 CFR 11)</span>
                            </div>
                        </div>

                        <!-- Capa 4 -->
                        <div class="layer-box" style="border-left-color: #a855f7;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong style="color: #a855f7; font-size: 0.85rem;">CAPA 4: Capa de Dominio DDD & Patrones GoF (Core de Negocio)</strong>
                                <span class="badge-pulse" style="background: rgba(168,85,247,0.15); color: #c084fc; border: 1px solid rgba(168,85,247,0.3);">7 BOUNDED CONTEXTS</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.35rem;">
                                Alberga la lógica pura de ingeniería de hidrocarburos: <strong>Command</strong> (con consignas reversibles y UNDO), <strong>State</strong> (enclavamiento API 6A), <strong>Strategy</strong> (AGA-11, AGA-3, Beggs & Brill), <strong>Observer</strong> (notificación reactiva de seguridad) y <strong>Factory Method</strong> (calibración de instrumentos).
                            </p>
                            <div style="font-family: var(--font-mono); font-size: 0.7rem; color: #cbd5e1;">
                                📍 <strong>Contextos:</strong> <code>TELEMETRY-CTX</code>, <code>WELLHEAD-CTX</code>, <code>SAFETY-CTX</code>, <code>ALARMS-CTX</code>, <code>IDENTITY-CTX</code>, <code>AUDIT-CTX</code>, <code>PRODUCTION-CTX</code>.
                            </div>
                        </div>

                        <!-- Capa 5 -->
                        <div class="layer-box" style="border-left-color: #10b981;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong style="color: #10b981; font-size: 0.85rem;">CAPA 5: Persistencia & Repositorios (MySQL mapaf_db)</strong>
                                <span class="badge-pulse" style="background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3);">SINGLETON + REPOSITORIES</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.35rem;">
                                Base de datos relacional InnoDB en MySQL gestionada mediante <code>DatabaseSingleton</code> para reutilizar una única conexión PDO. La lógica SQL queda encapsulada en <code>WellheadRepository</code>, <code>AlarmRepository</code> y <code>AuditRepository</code> mediante Prepared Statements con tipado estricto contra inyección SQL.
                            </p>
                            <div style="font-family: var(--font-mono); font-size: 0.7rem; color: #cbd5e1;">
                                📍 <strong>Persistencia:</strong> 8 tablas relacionales con integridad referencial, índices y auditoría inmutable append-only.
                            </div>
                        </div>

                        <!-- Capa 6 -->
                        <div class="layer-box" style="border-left-color: #38bdf8;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong style="color: #38bdf8; font-size: 0.85rem;">CAPA 6: HMI Web & Consola Operacional de Telemetría (Presentation)</strong>
                                <span class="badge-pulse" style="background: rgba(56,189,248,0.15); color: #38bdf8; border: 1px solid rgba(56,189,248,0.3);">SCADA HMI DASHBOARD</span>
                            </div>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.35rem;">
                                Interfaz táctica interactiva construida en HTML5, Vanilla CSS Glassmorphism y JavaScript ES6 modular. Supervisión RTOC en tiempo real con refresco automático de 3s, controles deslizantes de Choke con rollback visual, modal de acuse ISA-18.2 y conmutador de roles JWT.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 3. Simulador Interactivo de Flujo de Datos E2E (Traceable Data Flow Simulator) -->
                <div style="background: linear-gradient(135deg, rgba(16, 24, 44, 0.95), rgba(10, 15, 29, 0.95)); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <span class="comp-badge badge-emerald">SIMULADOR EN VIVO</span>
                            <h3 style="font-family: var(--font-heading); font-size: 1.05rem; color: #fff; margin-top: 0.25rem;">
                                Rastrear Flujo de Datos E2E a través de la Infraestructura
                            </h3>
                            <p style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">
                                Haz clic en un flujo para visualizar paso a paso cómo viaja la petición entre el navegador, el backend PHP y la base de datos MySQL.
                            </p>
                        </div>
                    </div>

                    <!-- Botones de Selección de Flujo -->
                    <div class="flow-btn-group">
                        <button class="flow-pill-btn active" id="btn-flow-choke" onclick="renderDataFlow('choke')">
                            ▶ 1. Maniobra Choke con UNDO (Command Pattern)
                        </button>
                        <button class="flow-pill-btn" id="btn-flow-alarm" onclick="renderDataFlow('alarm')">
                            ▶ 2. Disparo de Alarma y Acuse Trazable (ISA-18.2)
                        </button>
                        <button class="flow-pill-btn" id="btn-flow-jwt" onclick="renderDataFlow('jwt')">
                            ▶ 3. Autenticación Criptográfica JWT (RFC 7519)
                        </button>
                        <button class="flow-pill-btn" id="btn-flow-esd" onclick="renderDataFlow('esd')">
                            ▶ 4. Parada de Emergencia ESD-1 (IEC 61511)
                        </button>
                    </div>

                    <!-- Consola de Trazabilidad del Flujo -->
                    <div id="flowTraceContainer" style="background: #060913; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
                        <!-- Se llena dinámicamente con renderDataFlow() -->
                    </div>
                </div>

                <!-- 4. Matriz de Endpoints y Puertos del Ecosistema -->
                <div class="table-container" style="margin-bottom: 1.5rem;">
                    <div class="table-header">
                        <div>
                            <span class="table-title">Matriz de Endpoints REST & Puertos del Ecosistema MAPAF</span>
                            <p style="font-size: 0.75rem; color: var(--text-muted);">Mapeo de comunicación entre capas, puertos TCP y controladores PHP</p>
                        </div>
                        <span class="comp-badge badge-purple">8 ENDPOINTS OPERACIONALES</span>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Endpoint REST</th>
                                <th>Método</th>
                                <th>Puerto</th>
                                <th>Dominio DDD</th>
                                <th>Patrón GoF / Estándar</th>
                                <th>Tabla MySQL</th>
                                <th>Propósito en la Infraestructura</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>/api/sync.php</code></td>
                                <td><span style="color:#38bdf8; font-weight:bold;">GET</span></td>
                                <td>80 (HTTP)</td>
                                <td><code>TELEMETRY-CTX</code></td>
                                <td>Strategy & Observer</td>
                                <td><code>telemetria_metricas</code></td>
                                <td>Suministra telemetría reactiva en ciclos continuos de 3 segundos al HMI.</td>
                            </tr>
                            <tr>
                                <td><code>/api/wellheads.php</code></td>
                                <td><span style="color:#10b981; font-weight:bold;">POST/GET</span></td>
                                <td>80 (HTTP)</td>
                                <td><code>WELLHEAD-CTX</code></td>
                                <td>Command & State</td>
                                <td><code>pozos</code></td>
                                <td>Ejecuta maniobras de Choke y válvulas con enclavamiento y reversión (UNDO).</td>
                            </tr>
                            <tr>
                                <td><code>/api/alarms.php</code></td>
                                <td><span style="color:#f59e0b; font-weight:bold;">POST/GET</span></td>
                                <td>80 (HTTP)</td>
                                <td><code>ALARMS-CTX</code></td>
                                <td>Factory Method</td>
                                <td><code>alarmas</code></td>
                                <td>Ciclo de vida de alarmas y protocolo de acuse trazable bajo norma ISA-18.2.</td>
                            </tr>
                            <tr>
                                <td><code>/api/login.php</code></td>
                                <td><span style="color:#10b981; font-weight:bold;">POST/GET</span></td>
                                <td>80 (HTTP)</td>
                                <td><code>IDENTITY-CTX</code></td>
                                <td>RFC 7519 JWT</td>
                                <td><code>usuarios</code>, <code>roles</code></td>
                                <td>Emite y valida tokens firmados con claims criptográficos y permisos RBAC.</td>
                            </tr>
                            <tr>
                                <td><code>/api/patterns.php</code></td>
                                <td><span style="color:#c084fc; font-weight:bold;">POST/GET</span></td>
                                <td>80 (HTTP)</td>
                                <td><code>ARCHITECTURE</code></td>
                                <td>8 Patrones GoF</td>
                                <td><code>mapaf_db</code> (Global)</td>
                                <td>Laboratorio de validación de patrones y sandbox de mitigación de antipatrones.</td>
                            </tr>
                            <tr>
                                <td><code>/api/domains.php</code></td>
                                <td><span style="color:#38bdf8; font-weight:bold;">GET</span></td>
                                <td>80 (HTTP)</td>
                                <td><code>DDD CATALOG</code></td>
                                <td>Domain-Driven Design</td>
                                <td>Entidades en memoria</td>
                                <td>Catálogo de los 7 Bounded Contexts e invariantes de negocio del SCADA.</td>
                            </tr>
                            <tr>
                                <td><code>/api/audit.php</code></td>
                                <td><span style="color:#38bdf8; font-weight:bold;">GET</span></td>
                                <td>80 (HTTP)</td>
                                <td><code>AUDIT-CTX</code></td>
                                <td>Repository</td>
                                <td><code>bitacora_auditoria</code></td>
                                <td>Trazabilidad forense inmutable de maniobras conforme a 21 CFR Part 11.</td>
                            </tr>
                            <tr>
                                <td><code>/api/status.php</code></td>
                                <td><span style="color:#34d399; font-weight:bold;">GET</span></td>
                                <td>80 (HTTP)</td>
                                <td><code>INFRASTRUCTURE</code></td>
                                <td>Singleton (Database)</td>
                                <td>Pool de sockets PDO</td>
                                <td>Diagnóstico de salud de Apache, PHP y disponibilidad del puerto 3306 de MySQL.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 5. Directorio de Archivos y Responsabilidades del Proyecto -->
                <div class="table-container">
                    <div class="table-header">
                        <div>
                            <span class="table-title">Estructura de Directorios del Código Fuente en `backend_php`</span>
                            <p style="font-size: 0.75rem; color: var(--text-muted);">Organización modular conforme al principio de Responsabilidad Única (SRP)</p>
                        </div>
                        <span class="comp-badge badge-blue">ARQUITECTURA MODULAR</span>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Directorio / Ruta</th>
                                <th>Contenido Principal</th>
                                <th>Capa del Sistema</th>
                                <th>Patrones & Normas Implementadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>backend_php/api/</code></td>
                                <td>Controladores REST (`sync.php`, `wellheads.php`, `alarms.php`, etc.)</td>
                                <td>API Gateway</td>
                                <td>Separación de responsabilidades, evita The God Object.</td>
                            </tr>
                            <tr>
                                <td><code>backend_php/patterns/</code></td>
                                <td>Clases de Patrones GoF: Singleton, Command, State, Strategy, Observer, Factory, Repository, Adapter.</td>
                                <td>Capa de Dominio</td>
                                <td>GoF Design Patterns orientados a ingeniería de procesos upstream.</td>
                            </tr>
                            <tr>
                                <td><code>backend_php/domain/</code></td>
                                <td>Modelos de Bounded Contexts (`DomainArchitecture.php`).</td>
                                <td>Capa de Dominio</td>
                                <td>Domain-Driven Design (DDD) con delimitación de 7 contextos.</td>
                            </tr>
                            <tr>
                                <td><code>backend_php/auth/</code></td>
                                <td>Servicio criptográfico JWT y validación RBAC (`jwt.php`).</td>
                                <td>Seguridad</td>
                                <td>RFC 7519 HMAC-SHA256, expiración estricta y control de claims.</td>
                            </tr>
                            <tr>
                                <td><code>backend_php/antipatterns/</code></td>
                                <td>Catálogo de malas prácticas y sus escudos (`AntiPatternCatalog.php`).</td>
                                <td>Calidad de Software</td>
                                <td>Mitigación de Blind Alarm ACK, SQL Injection, God Object, etc.</td>
                            </tr>
                            <tr>
                                <td><code>backend_php/config/</code></td>
                                <td>Configuración de base de datos (`database.php`) y CORS (`cors.php`).</td>
                                <td>Infraestructura</td>
                                <td>Variables de entorno, cabeceras HTTP de seguridad y pool de conexión.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <!-- Modal de Acuse de Alarmas Trazable ISA-18.2 (Mitigación de Antipatrón Blind ACK) -->
    <div id="ackModalBackdrop" class="modal-backdrop" style="display: none;">
        <div class="ack-modal-dialog">
            <div class="ack-modal-header">
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.05rem; color: #fff;">Protocolo de Reconocimiento de Alarmas (ISA-18.2)</h3>
                    <p style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono);">Dominio ALARMS-CTX · Escudo Antipatrón Blind ACK Activo</p>
                </div>
                <button class="modal-close-btn" onclick="closeAckModal()">✕</button>
            </div>
            <div class="ack-modal-body">
                <div style="background: #060913; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem; font-family: var(--font-mono); font-size: 0.78rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
                        <span style="color: var(--text-muted);">Instrumento / Tag:</span>
                        <strong style="color: #38bdf8;" id="modalAlarmTag">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
                        <span style="color: var(--text-muted);">Severidad:</span>
                        <span id="modalAlarmSeverity" style="font-weight: bold;">-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
                        <span style="color: var(--text-muted);">Valor Medido:</span>
                        <span id="modalAlarmValue">-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Operador en Turno (JWT):</span>
                        <span style="color: #c084fc; font-weight: bold;" id="modalAlarmOperator">-</span>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.75rem; font-family: var(--font-mono); color: #cbd5e1; display: block; margin-bottom: 0.35rem;">
                        Causa u Observación Técnica Obligatoria (ANSI/ISA-18.2):
                    </label>
                    <textarea id="modalAlarmReason" placeholder="Describa la causa de raíz identificada y la acción correctiva ejecutada en campo (obligatorio)..." style="width: 100%; height: 75px; background: #060913; border: 1px solid var(--border-color); color: #fff; border-radius: 6px; padding: 0.5rem; font-family: var(--font-mono); font-size: 0.75rem; resize: none;"></textarea>
                </div>

                <div id="modalShieldWarning" style="display: none; background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.4); border-radius: 6px; padding: 0.65rem; margin-bottom: 1rem; font-size: 0.72rem; color: #fecdd3; font-family: var(--font-mono);">
                    ⚠️ <strong>ESCUDO DE ANTIPATRONES MAPAF:</strong> Silenciamiento ciego bloqueado. La norma ANSI/ISA-18.2 prohíbe silenciar alarmas críticas sin causa técnica registrada y trazable en bitácora.
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button class="btn-action" style="background: #334155; color: #fff;" onclick="closeAckModal()">Cancelar</button>
                    <button class="btn-action btn-danger" onclick="submitAckWithReason()">Registrar Reconocimiento Seguro</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = '/mapaf/api';

        let currentData = {
            metrics: [],
            wellheads: [],
            alarms: [],
            roles: [],
            users: [],
            auditLogs: [],
            systemConfig: {}
        };

        let currentWellFilter = 'all';
        let currentWellSearch = '';
        let currentAlarmFilter = 'all';
        let currentAlarmSearch = '';
        let currentStrategy = 'coriolis';

        // 1. Matriz de Dominios (Domain-Driven Design / DDD)
        const DOMAIN_MAP = {
            overview: '🏛️ DOMINIO 1: RTOC & Telemetría IIoT [TELEMETRY-CTX] · ISA-5.1',
            wells: '🏛️ DOMINIO 2: Cabezas de Pozo & Seguridad API 6A [WELLHEAD-CTX]',
            alarms: '🏛️ DOMINIO 4: Gestión de Alarmas ANSI/ISA-18.2 [ALARMS-CTX]',
            schematics: '🏛️ DOMINIO 3: Facilidades de Superficie & Separación [SURFACE-CTX]',
            tags: '🏛️ DOMINIO 3: Instrumentación & Diccionario ISA-5.1 [TAGS-CTX]',
            patterns: '🏛️ DOMINIO ARQUITECTURA: Patrones GoF (8) & Escudo de Antipatrones Activo',
            domains: '🏛️ MAPA DE CONTEXTOS DDD: 7 Bounded Contexts Delimitados Formalmente',
            jwt: '🏛️ DOMINIO 5: Identidad, Seguridad & RBAC RFC 7519 [IDENTITY-RBAC-CTX]',
            security: '🏛️ DOMINIO 5: Usuarios, Roles y Permisos RBAC [USERS-CTX]',
            audit: '🏛️ DOMINIO 7: Bitácora Inmutable & Trazabilidad 21 CFR 11 [AUDIT-CTX]',
            comparisons: '🏛️ DOMINIO ARQUITECTURA: Sustentación ADSO Ficha 3413988',
            diag: '🏛️ DOMINIO INFRAESTRUCTURA: Servidores XAMPP Apache & MySQL'
        };

        // 2. Operador & Seguridad JWT (RBAC en Vivo)
        let currentOperator = {
            code: 'OP-449',
            name: 'María José Conde Dussan',
            role: 'SYS-ADMIN',
            roleName: 'Administrador del Sistema'
        };
        let activeJwtToken = localStorage.getItem('mapaf_jwt_token') || '';

        async function switchOperatorRole(code) {
            const rolesMap = {
                'OP-449': { name: 'María José Conde Dussan', role: 'SYS-ADMIN', roleName: 'Administrador del Sistema' },
                'OP-312': { name: 'Hernann Thomas Manchola', role: 'PROCESS-ENG', roleName: 'Ingeniero de Procesos' },
                'OP-205': { name: 'Emilin Tatiana Vargas', role: 'SUPERVISOR', roleName: 'Supervisor de Planta' },
                'OP-101': { name: 'Operador de Sala', role: 'CONSOLE-OP', roleName: 'Operador de Consola' },
                'OP-118': { name: 'Javier Humberto Pinto Díaz', role: 'INSTRUCTOR-SENA', roleName: 'Instructor SENA' }
            };

            const info = rolesMap[code] || rolesMap['OP-449'];
            currentOperator.code = code;
            currentOperator.name = info.name;
            currentOperator.role = info.role;
            currentOperator.roleName = info.roleName;

            // Solicitar token firmado real al backend PHP
            try {
                const res = await fetch(`${API_BASE}/login.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employeeCode: code, ttl: 28800 })
                });
                const data = await res.json();
                if (data.success && data.token) {
                    activeJwtToken = data.token;
                    localStorage.setItem('mapaf_jwt_token', activeJwtToken);
                    showToast(`Sesión JWT Activa: ${currentOperator.name} [${currentOperator.role}]`);
                }
            } catch(e) {
                showToast(`Operador cambiado a ${currentOperator.name}`);
            }
        }

        function getAuthHeaders() {
            const headers = { 'Content-Type': 'application/json' };
            if (activeJwtToken) {
                headers['Authorization'] = `Bearer ${activeJwtToken}`;
            }
            return headers;
        }

        // 3. Patrón STRATEGY: Selector Dinámico de Algoritmo de Flujo
        async function switchFlowStrategy(strategyKey) {
            currentStrategy = strategyKey;

            document.querySelectorAll('.strategy-pill-btn').forEach(btn => btn.classList.remove('active'));
            const activeBtn = document.getElementById(`btn-strat-${strategyKey.substring(0, 7).toLowerCase()}`) || document.getElementById(`btn-strat-${strategyKey}`);
            if (activeBtn) activeBtn.classList.add('active');

            const formulaBox = document.getElementById('strategyFormulaBox');
            if (formulaBox) {
                if (strategyKey === 'coriolis') {
                    formulaBox.innerHTML = `<strong>Ecuación Activa (AGA-11 Coriolis):</strong> <code>ṁ = ρ · Q_vol | Medición Fiscal Directa | Precisión ±0.15% | Corrección MPMS 11.1</code>`;
                } else if (strategyKey === 'aga3') {
                    formulaBox.innerHTML = `<strong>Ecuación Activa (AGA-3 Placa Orificio):</strong> <code>Q = C_d · E_v · Y · (π/4)·d² · √(2·ΔP·ρ) | Medición por Presión Diferencial</code>`;
                } else {
                    formulaBox.innerHTML = `<strong>Ecuación Activa (Beggs & Brill):</strong> <code>ΔP_total = ΔP_fricción + ΔP_elevación + ΔP_aceleración | Flujo Multifásico Crudo/Gas/Agua</code>`;
                }
            }

            showToast(`Estrategia de flujo: ${strategyKey.toUpperCase()} activada en backend`);
            loadAllData();
        }

        // 4. Patrón OBSERVER: Simulador de Inyección Reactiva
        async function injectTelemetryPressure(psiValue, severity) {
            try {
                const res = await fetch(`${API_BASE}/patterns.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'test_observer', value: psiValue, tag: 'WHTP-101A' })
                });
                const data = await res.json();
                
                // Efecto visual en los observadores
                const b1 = document.getElementById('obsAlarmBadge');
                const b2 = document.getElementById('obsSafetyBadge');
                const b3 = document.getElementById('obsAuditBadge');

                [b1, b2, b3].forEach(b => {
                    if (b) {
                        b.classList.add('fired');
                        setTimeout(() => b.classList.remove('fired'), 3500);
                    }
                });

                if (severity === 'critical') {
                    showToast(`🚨 OBSERVER: Presión crítica ${psiValue} PSI! Alarma generada y pozo aislado en MySQL`, true);
                } else if (severity === 'warning') {
                    showToast(`🟡 OBSERVER: ${psiValue} PSI superó umbral preventivo. Registrado en bitácora.`);
                } else {
                    showToast(`🟢 OBSERVER: ${psiValue} PSI nominal procesado normalmente.`);
                }

                loadAllData();
                loadAuditLogs();
            } catch(e) {
                showToast("Error simulando Observer", true);
            }
        }

        // 5. Patrón COMMAND: Pila de Comandos con Soporte de UNDO
        let localCommandStack = [];

        function recordCommand(cmd) {
            localCommandStack.unshift(cmd);
            if (localCommandStack.length > 20) localCommandStack.pop();
            const countEl = document.getElementById('cmdStackCount');
            if (countEl) countEl.textContent = `${localCommandStack.length} maniobras en pila`;
        }

        async function undoWellChoke(wellId) {
            const lastCmd = localCommandStack.find(c => c.wellId === wellId);
            const targetOpening = lastCmd ? lastCmd.previous : 42.8;

            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        id: wellId,
                        action: 'undo',
                        previousOpening: targetOpening,
                        userName: currentOperator.name,
                        userCode: currentOperator.code,
                        roleName: currentOperator.roleName
                    })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`↺ COMMAND UNDO: Pozo ${wellId} retornado a ${targetOpening}% en MySQL`);
                    const idx = localCommandStack.findIndex(c => c.wellId === wellId);
                    if (idx !== -1) localCommandStack.splice(idx, 1);
                    const countEl = document.getElementById('cmdStackCount');
                    if (countEl) countEl.textContent = `${localCommandStack.length} maniobras en pila`;
                    loadAllData();
                    loadAuditLogs();
                } else {
                    showToast(result.error || "Error revirtiendo maniobra", true);
                }
            } catch(e) {
                showToast("Error de conexión ejecutando UNDO", true);
            }
        }

        async function undoLastGlobalCommand() {
            if (localCommandStack.length === 0) {
                showToast("No hay maniobras pendientes en la pila de comandos.");
                return;
            }
            const last = localCommandStack[0];
            await undoWellChoke(last.wellId);
        }

        // 6. Patrón STATE: Máquina de Estados API 6A
        async function resetWellState(wellId) {
            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        id: wellId,
                        action: 'reset_state',
                        userName: currentOperator.name,
                        userCode: currentOperator.code,
                        roleName: currentOperator.roleName
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`🛡️ REARME API 6A: Pozo ${wellId} rearmado a estado EN LÍNEA`);
                    loadAllData();
                    loadAuditLogs();
                } else {
                    showToast(data.error || "Fallo rearmando pozo", true);
                }
            } catch(e) {
                showToast("Error comunicando con API de pozos", true);
            }
        }

        async function tripWellState(wellId) {
            if (!confirm(`⚠️ ¿Desea simular el disparo de seguridad (Trip State) para el pozo ${wellId}?`)) return;

            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        id: wellId,
                        ssvOpen: false,
                        chokeOpening: 0,
                        actionDescription: `Disparo de Seguridad Simulado (State::trip()) en pozo ${wellId}`,
                        userName: currentOperator.name,
                        userCode: currentOperator.code,
                        roleName: currentOperator.roleName
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`⚡ DISPARO EJECUTADO: Pozo ${wellId} en estado DISPARADO con enclavamiento API 6A`, true);
                    loadAllData();
                    loadAuditLogs();
                }
            } catch(e) {
                showToast("Error disparando pozo", true);
            }
        }

        // 7. Mitigación de Antipatrón: Blind Alarm Acknowledgment (ANSI/ISA-18.2)
        let activeAlarmToAck = null;

        function openAckModal(id, tag, severity, value, threshold) {
            activeAlarmToAck = { id, tag, severity, value, threshold };

            document.getElementById('modalAlarmTag').textContent = tag || id;
            const sevEl = document.getElementById('modalAlarmSeverity');
            sevEl.textContent = (severity || 'CRITICAL').toUpperCase();
            sevEl.style.color = severity === 'critical' ? '#f43f5e' : severity === 'warning' ? '#f59e0b' : '#34d399';
            document.getElementById('modalAlarmValue').textContent = `${value || '-'} (Límite: ${threshold || '-'})`;
            document.getElementById('modalAlarmOperator').textContent = `${currentOperator.name} [${currentOperator.role}]`;
            document.getElementById('modalAlarmReason').value = '';
            document.getElementById('modalShieldWarning').style.display = 'none';

            document.getElementById('ackModalBackdrop').style.display = 'flex';
        }

        function closeAckModal() {
            document.getElementById('ackModalBackdrop').style.display = 'none';
            activeAlarmToAck = null;
        }

        async function confirmAckWithReason() {
            if (!activeAlarmToAck) return;

            const reason = document.getElementById('modalAlarmReason').value.trim();
            if (!reason) {
                document.getElementById('modalShieldWarning').style.display = 'block';
                showToast("🛡️ ESCUDO ANTIPATRÓN: Blind Alarm ACK bloqueado. Ingrese justificación técnica.", true);
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/alarms.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        id: activeAlarmToAck.id,
                        reason: reason,
                        acknowledgedBy: currentOperator.name,
                        operatorCode: currentOperator.code,
                        roleName: currentOperator.roleName
                    })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`✓ Alarma ${activeAlarmToAck.tag} reconocida formalmente con causa en MySQL`);
                    closeAckModal();
                    loadAllData();
                    loadAuditLogs();
                } else if (result.antipatternMitigated) {
                    document.getElementById('modalShieldWarning').style.display = 'block';
                    showToast(result.error, true);
                } else {
                    showToast(result.error || "Error reconociendo alarma", true);
                }
            } catch(e) {
                showToast("Error de conexión al reconocer alarma", true);
            }
        }

        async function simulateBlindAckAttempt() {
            if (!activeAlarmToAck) return;
            try {
                const res = await fetch(`${API_BASE}/alarms.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: activeAlarmToAck.id,
                        reason: '', // Vacío intencional para demostrar mitigación
                        acknowledgedBy: currentOperator.name,
                        operatorCode: currentOperator.code,
                        roleName: currentOperator.roleName
                    })
                });
                const result = await res.json();
                if (!result.success && result.antipatternMitigated) {
                    document.getElementById('modalShieldWarning').style.display = 'block';
                    showToast(`🛡️ ESCUDO ANTIPATRÓN: ${result.error}`, true);
                }
            } catch(e) {
                showToast("Error en prueba de Blind ACK", true);
            }
        }

        function showToast(msg, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'show ' + (isError ? 'error' : 'success');
            setTimeout(() => { toast.className = ''; }, 3500);
        }

        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            sidebar.classList.toggle('collapsed');
        }

        // Toggle Grupo de módulos
        function toggleGroup(grpKey) {
            const items = document.getElementById('grp-items-' + grpKey);
            if (items) {
                items.style.display = items.style.display === 'none' ? 'flex' : 'none';
            }
        }

        // Filtro en vivo de módulos en el sidebar
        function filterModules(query) {
            const q = query.toLowerCase().trim();
            const links = document.querySelectorAll('.sidebar-menu .nav-link');
            const groups = document.querySelectorAll('.module-group');

            links.forEach(l => {
                const text = l.textContent.toLowerCase();
                if (text.includes(q)) {
                    l.style.display = 'flex';
                } else {
                    l.style.display = 'none';
                }
            });

            groups.forEach(g => {
                const visibleLinks = g.querySelectorAll('.nav-link[style*="display: flex"], .nav-link:not([style*="display: none"])');
                if (q && visibleLinks.length === 0) {
                    g.style.display = 'none';
                } else {
                    g.style.display = 'flex';
                }
            });
        }

        // Cambio de módulo principal con actualización de breadcrumb y Dominio DDD
        function switchModule(moduleId, categoryName, moduleName) {
            document.querySelectorAll('.nav-link').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.top-nav-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.section-view').forEach(sec => sec.classList.remove('active'));

            const activeBtn = document.querySelector(`.nav-link[data-mod="${moduleId}"]`);
            if (activeBtn) activeBtn.classList.add('active');

            const activeTopBtn = document.querySelector(`.top-nav-btn[data-topmod="${moduleId}"]`);
            if (activeTopBtn) activeTopBtn.classList.add('active');

            const target = document.getElementById('tab-' + moduleId);
            if (target) target.classList.add('active');

            document.getElementById('bcCategory').textContent = categoryName;
            document.getElementById('bcModule').textContent = moduleName;

            // Actualizar Dominio Delimitado (DDD Bounded Context)
            const domBadge = document.getElementById('activeDomainBadge');
            if (domBadge && DOMAIN_MAP[moduleId]) {
                domBadge.textContent = DOMAIN_MAP[moduleId];
            }

            document.querySelector('.app-main').scrollTop = 0;
        }

        function switchSubtab(moduleKey, subtabId, btnElement) {
            const parent = btnElement.parentElement;
            parent.querySelectorAll('.subtab-btn').forEach(b => b.classList.remove('active'));
            btnElement.classList.add('active');

            const section = document.getElementById('tab-' + moduleKey);
            if (section) {
                section.querySelectorAll('.subtab-content').forEach(c => c.classList.remove('active'));
                const target = document.getElementById(subtabId);
                if (target) target.classList.add('active');
            }
        }

        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').textContent = now.toLocaleTimeString('es-CO');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Carga de datos desde API PHP / MySQL con Estrategia Activa
        async function loadAllData() {
            try {
                const res = await fetch(`${API_BASE}/sync.php?strategy=${currentStrategy}`);
                const data = await res.json();
                if (data.success) {
                    currentData = data;
                    renderMetrics();
                    renderWells();
                    renderAlarms();
                    renderUsersAndRoles();
                    renderAuditLogs();
                    updateDbPill(true);

                    // Actualizar indicadores con cálculo de la Estrategia Activa
                    if (data.calculatedProduction) {
                        const bpdStr = `${Number(data.calculatedProduction.oilRateNetBpd || data.calculatedProduction.totalRateBpd).toLocaleString('es-CO', {maximumFractionDigits: 0})} BPD`;
                        const gasStr = `${Number(data.calculatedProduction.gasRateMscfd || 3200).toLocaleString('es-CO', {maximumFractionDigits: 0})} MSCFD`;
                        
                        document.getElementById('kpiProd').textContent = bpdStr;
                        const bpdDisplay = document.getElementById('strategyBpdDisplay');
                        const gasDisplay = document.getElementById('strategyGasDisplay');
                        if (bpdDisplay) bpdDisplay.textContent = bpdStr;
                        if (gasDisplay) gasDisplay.textContent = gasStr;

                        const flowEl = document.getElementById('kpiFlow');
                        flowEl.textContent = (data.calculatedProduction.flowState || 'NOMINAL').toUpperCase();
                        flowEl.style.color = data.calculatedProduction.flowState === 'tripped' ? '#f43f5e' :
                                             data.calculatedProduction.flowState === 'throttled' ? '#f59e0b' : '#34d399';
                    } else if (data.systemConfig) {
                        document.getElementById('kpiProd').textContent = `${Number(data.systemConfig.productionRate).toLocaleString('es-CO')} BPD`;
                    }
                } else {
                    updateDbPill(false);
                }
            } catch (err) {
                console.error("Error sincronizando con XAMPP:", err);
                updateDbPill(false);
            }
        }

        function updateDbPill(connected) {
            const pill = document.getElementById('dbPill');
            const txt = document.getElementById('dbPillText');
            if (connected) {
                pill.className = 'status-pill';
                txt.textContent = 'MySQL: mapaf_db Conectado';
            } else {
                pill.className = 'status-pill error';
                txt.textContent = 'MySQL Desconectado';
            }
        }

        // 1. Render Metrics
        function renderMetrics() {
            const container = document.getElementById('metricsContainer');
            if (!container) return;
            container.innerHTML = currentData.metrics.map(m => `
                <div class="metric-card">
                    <div class="metric-tag">${m.tag}</div>
                    <div class="metric-label">${m.label}</div>
                    <div class="metric-val-wrap">
                        <span class="metric-val">${Number(m.value).toLocaleString('es-CO', {maximumFractionDigits: 1})}</span>
                        <span class="metric-unit">${m.unit}</span>
                    </div>
                    <div class="metric-target">
                        <span>Obj: ${m.target} ${m.unit}</span>
                        <span style="color: ${m.severity === 'critical' ? '#f43f5e' : m.severity === 'warning' ? '#f59e0b' : '#34d399'}; font-weight: bold;">
                            ${m.severity.toUpperCase()}
                        </span>
                    </div>
                </div>
            `).join('');
        }

        // 2. Render Pozos con Filtros, Patrón Command (UNDO) y Patrón State (API 6A)
        function filterWells(status, chip) {
            currentWellFilter = status;
            document.querySelectorAll('#tab-wells .filter-chip').forEach(c => c.classList.remove('active'));
            if (chip) chip.classList.add('active');
            renderWells();
        }

        function searchWells(term) {
            currentWellSearch = term.toLowerCase().trim();
            renderWells();
        }

        function renderWells() {
            const container = document.getElementById('wellsContainer');
            if (!container) return;

            let filtered = currentData.wellheads || [];
            if (currentWellFilter !== 'all') {
                filtered = filtered.filter(w => w.status === currentWellFilter);
            }
            if (currentWellSearch) {
                filtered = filtered.filter(w => w.name.toLowerCase().includes(currentWellSearch) || w.sector.toLowerCase().includes(currentWellSearch));
            }

            if (filtered.length === 0) {
                container.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--text-muted); font-family: var(--font-mono);">No se encontraron pozos con los filtros seleccionados.</div>`;
                return;
            }

            container.innerHTML = filtered.map(w => {
                const isTripped = w.status === 'DISPARADO';
                const statusClass = w.status === 'EN LÍNEA' ? 'status-online' :
                                    w.status === 'ESTRANGULADO' ? 'status-choked' :
                                    w.status === 'DISPARADO' ? 'status-tripped' : 'status-maint';

                return `
                <div class="well-card" id="well-card-${w.id}">
                    <div class="well-header">
                        <div>
                            <div class="well-title">${w.name}</div>
                            <span style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono);">${w.sector} · API 6A</span>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem;">
                            <span class="well-status ${statusClass}">${w.status}</span>
                            <span style="font-size: 0.62rem; font-family: var(--font-mono); color: #94a3b8;">[STATE PATTERN]</span>
                        </div>
                    </div>

                    <div class="well-readings">
                        <div class="reading-item">
                            <span class="reading-lbl">PRESIÓN TUBING</span>
                            <span class="reading-num">${w.tubingPressure} PSI</span>
                        </div>
                        <div class="reading-item">
                            <span class="reading-lbl">PRESIÓN CASING</span>
                            <span class="reading-num">${w.casingPressure} PSI</span>
                        </div>
                        <div class="reading-item">
                            <span class="reading-lbl">CAUDAL NETO</span>
                            <span class="reading-num">${w.flowRate} BPD</span>
                        </div>
                        <div class="reading-item">
                            <span class="reading-lbl">TEMPERATURA</span>
                            <span class="reading-num">${w.temperature} °C</span>
                        </div>
                    </div>

                    ${isTripped ? `
                        <div class="state-interlock-box">
                            <strong>⚠️ ENCLAVAMIENTO DE SEGURIDAD API 6A ACTIVO:</strong><br>
                            El pozo se encuentra DISPARADO (Trip de seguridad). Las maniobras de estrangulador y apertura de válvula SSV están bloqueadas por el Patrón State.
                        </div>
                        <button class="btn-action" style="background:#10b981; color:#000; font-weight:bold; width:100%; margin-top:0.6rem; justify-content:center;" onclick="resetWellState('${w.id}')">
                            🛡️ Rearmar Pozo (State::reset())
                        </button>
                    ` : `
                        <div class="control-group">
                            <div class="control-label">
                                <span>Estrangulador (Choke FCV) <span style="font-size:0.65rem; color:#a855f7;">[COMMAND PATTERN]</span></span>
                                <span id="choke-lbl-${w.id}" style="color: #38bdf8; font-weight: bold;">${w.chokeOpening}%</span>
                            </div>
                            <input type="range" min="0" max="100" step="0.5" value="${w.chokeOpening}" class="choke-slider"
                                   oninput="document.getElementById('choke-lbl-${w.id}').textContent = this.value + '%'"
                                   onchange="updateWellChoke('${w.id}', this.value, ${w.chokeOpening})">
                            <button class="btn-undo" id="btn-undo-${w.id}" onclick="undoWellChoke('${w.id}')" title="Revertir última consigna aplicando el Patrón Command UNDO">
                                ↺ Deshacer Maniobra (Command UNDO)
                            </button>
                        </div>

                        <div class="valve-toggles">
                            <button class="btn-valve ${w.ssvOpen ? 'open' : ''}" onclick="toggleSsv('${w.id}', ${!w.ssvOpen})">
                                SSV: ${w.ssvOpen ? 'ABIERTA' : 'CERRADA'}
                            </button>
                            <button class="btn-valve ${w.chemicalInjection ? 'open' : ''}" onclick="toggleChem('${w.id}', ${!w.chemicalInjection})">
                                Iny. Química: ${w.chemicalInjection ? 'ACTIVA' : 'INACTIVA'}
                            </button>
                            <button class="btn-action btn-danger" style="font-size:0.7rem; padding:0.25rem 0.5rem;" onclick="tripWellState('${w.id}')" title="Simular disparo de seguridad para activar enclavamiento de State Pattern">
                                ⚡ Simular Disparo
                            </button>
                        </div>
                    `}
                </div>
                `;
            }).join('');
        }

        async function updateWellChoke(id, val, prevVal) {
            const previous = typeof prevVal === 'number' ? prevVal : 42.8;
            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        id,
                        chokeOpening: parseFloat(val),
                        actionDescription: `Ajuste estrangulador a ${val}% (SetChokeCommand)`,
                        userName: currentOperator.name,
                        userCode: currentOperator.code,
                        roleName: currentOperator.roleName
                    })
                });
                const result = await res.json();
                if (result.success) {
                    recordCommand({ wellId: id, name: 'SetChokeCommand', previous: previous, target: parseFloat(val), time: new Date().toLocaleTimeString() });
                    showToast(`Pozo ${id}: Choke regulado a ${val}% en MySQL (Reversible con UNDO)`);
                    loadAllData();
                    loadAuditLogs();
                } else if (result.securityViolation) {
                    showToast(`⛔ RBAC DENEGADO: ${result.error}`, true);
                } else if (result.interlockActive) {
                    showToast(`⚠️ ENCLAVAMIENTO STATE PATTERN: ${result.error}`, true);
                } else {
                    showToast(result.error || "Error en consigna", true);
                }
            } catch (e) {
                showToast("Error de comunicación", true);
            }
        }

        async function toggleSsv(id, newOpen) {
            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        id,
                        ssvOpen: newOpen,
                        actionDescription: `Maniobra Válvula SSV: ${newOpen ? 'Apertura' : 'Cierre'} (ToggleSsvCommand)`,
                        userName: currentOperator.name,
                        userCode: currentOperator.code,
                        roleName: currentOperator.roleName
                    })
                });
                const result = await res.json();
                if (result.success) {
                    recordCommand({ wellId: id, name: 'ToggleSsvCommand', previous: !newOpen, target: newOpen, time: new Date().toLocaleTimeString() });
                    showToast(`Válvula SSV ${newOpen ? 'Abierta' : 'Cerrada'} en MySQL`);
                    loadAllData();
                    loadAuditLogs();
                } else if (result.interlockActive) {
                    showToast(`⚠️ ENCLAVAMIENTO STATE: ${result.error}`, true);
                } else {
                    showToast(result.error || "Error operando válvula", true);
                }
            } catch (e) {
                showToast("Error de conexión", true);
            }
        }

        async function toggleChem(id, newChem) {
            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        id,
                        chemicalInjection: newChem,
                        actionDescription: `Inyección química ${newChem ? 'Activada' : 'Detenida'}`,
                        userName: currentOperator.name,
                        userCode: currentOperator.code,
                        roleName: currentOperator.roleName
                    })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`Inyección química actualizada en MySQL`);
                    loadAllData();
                }
            } catch (e) {
                showToast("Error de conexión", true);
            }
        }

        // 3. Render Alarmas con Filtros y Acuse Trazable ISA-18.2
        function filterAlarms(crit, chip) {
            currentAlarmFilter = crit;
            document.querySelectorAll('#tab-alarms .filter-chip').forEach(c => c.classList.remove('active'));
            if (chip) chip.classList.add('active');
            renderAlarms();
        }

        function searchAlarms(term) {
            currentAlarmSearch = term.toLowerCase().trim();
            renderAlarms();
        }

        function renderAlarms() {
            const unacked = currentData.alarms.filter(a => !a.acknowledged);
            document.getElementById('alarmBadge').textContent = unacked.length;

            const overviewBody = document.getElementById('alarmsOverviewBody');
            const fullBody = document.getElementById('alarmsFullBody');

            // Vista previa en Visión General
            if (overviewBody) {
                overviewBody.innerHTML = currentData.alarms.slice(0, 5).map(a => `
                    <tr>
                        <td style="font-family: var(--font-mono);">${a.timestamp}</td>
                        <td style="font-family: var(--font-mono); font-weight: bold; color: #38bdf8;">${a.tag}</td>
                        <td>${a.subsystem}</td>
                        <td>${a.description}</td>
                        <td style="font-family: var(--font-mono);">${a.value}</td>
                        <td><span style="color: ${a.severity === 'critical' ? '#f43f5e' : a.severity === 'warning' ? '#f59e0b' : '#34d399'}; font-weight: bold;">${a.severity.toUpperCase()}</span></td>
                        <td>
                            <button class="btn-ack ${a.acknowledged ? 'disabled' : ''}" onclick="openAckModal('${a.id}', '${a.tag}', '${a.severity}', '${a.value}', '${a.threshold}')">
                                ${a.acknowledged ? '✓ RECONOCIDA' : 'RECONOCER'}
                            </button>
                        </td>
                    </tr>
                `).join('');
            }

            // Vista completa con filtros en Consola de Alarmas
            if (fullBody) {
                let filtered = currentData.alarms || [];
                if (currentAlarmFilter === 'unacked') {
                    filtered = filtered.filter(a => !a.acknowledged);
                } else if (currentAlarmFilter !== 'all') {
                    filtered = filtered.filter(a => a.severity === currentAlarmFilter);
                }

                if (currentAlarmSearch) {
                    filtered = filtered.filter(a => a.tag.toLowerCase().includes(currentAlarmSearch) || a.subsystem.toLowerCase().includes(currentAlarmSearch) || a.description.toLowerCase().includes(currentAlarmSearch));
                }

                if (filtered.length === 0) {
                    fullBody.innerHTML = `<tr><td colspan="11" style="text-align: center; color: var(--text-muted); padding: 1.5rem; font-family: var(--font-mono);">No hay alarmas para el criterio seleccionado.</td></tr>`;
                    return;
                }

                fullBody.innerHTML = filtered.map(a => `
                    <tr>
                        <td style="font-family: var(--font-mono);">${a.id}</td>
                        <td style="font-family: var(--font-mono);">${a.timestamp}</td>
                        <td style="font-family: var(--font-mono); font-weight: bold; color: #38bdf8;">${a.tag}</td>
                        <td>${a.subsystem}</td>
                        <td>${a.description}</td>
                        <td style="font-family: var(--font-mono);">${a.value}</td>
                        <td style="font-family: var(--font-mono); color: var(--text-muted);">${a.threshold}</td>
                        <td><span style="color: ${a.severity === 'critical' ? '#f43f5e' : a.severity === 'warning' ? '#f59e0b' : '#34d399'}; font-weight: bold;">${a.severity.toUpperCase()}</span></td>
                        <td>${a.acknowledged ? '<span style="color: #34d399;">RECONOCIDA</span>' : '<span style="color: #f43f5e; font-weight: bold;">PENDIENTE</span>'}</td>
                        <td>${a.acknowledgedBy || '-'}</td>
                        <td>
                            <button class="btn-ack ${a.acknowledged ? 'disabled' : ''}" onclick="openAckModal('${a.id}', '${a.tag}', '${a.severity}', '${a.value}', '${a.threshold}')">
                                ${a.acknowledged ? '✓ RECONOCIDA' : 'RECONOCER'}
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }

        async function ackAllAlarms() {
            try {
                const res = await fetch(`${API_BASE}/alarms.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({
                        action: 'ack_all',
                        acknowledgedBy: currentOperator.name,
                        operatorCode: currentOperator.code
                    })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`Todas las alarmas reconocidas bajo protocolo ISA-18.2`);
                    loadAllData();
                    loadAuditLogs();
                }
            } catch (e) {
                showToast("Error de conexión", true);
            }
        }

        // 4. Users & Roles
        function renderUsersAndRoles() {
            const usersBody = document.getElementById('usersBody');
            const rolesBody = document.getElementById('rolesBody');

            if (usersBody) {
                usersBody.innerHTML = currentData.users.map(u => `
                    <tr>
                        <td style="font-family: var(--font-mono);">${u.employeeCode}</td>
                        <td style="font-weight: 600;">${u.fullName}</td>
                        <td><span style="background: rgba(147, 51, 234, 0.2); color: #c084fc; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">${u.roleId}</span></td>
                        <td>${u.assignedSector}</td>
                        <td><span style="color: ${u.status === 'EN TURNO' ? '#34d399' : '#94a3b8'}; font-weight: bold;">${u.status}</span></td>
                    </tr>
                `).join('');
            }

            if (rolesBody) {
                rolesBody.innerHTML = currentData.roles.map(r => `
                    <tr>
                        <td style="font-weight: 600;">${r.name}</td>
                        <td style="font-family: var(--font-mono);">${r.code}</td>
                        <td style="color: var(--text-muted); font-size: 0.8rem;">${r.description}</td>
                        <td><span style="color: #38bdf8; font-family: var(--font-mono);">${r.permissions.length} permisos</span></td>
                    </tr>
                `).join('');
            }
        }

        // 5. Audit Logs
        async function loadAuditLogs() {
            try {
                const res = await fetch(`${API_BASE}/audit.php`);
                const data = await res.json();
                if (data.success) {
                    currentData.auditLogs = data.auditLogs;
                    renderAuditLogs();
                }
            } catch (e) {
                console.error("Error cargando auditoría:", e);
            }
        }

        function renderAuditLogs() {
            const body = document.getElementById('auditBody');
            if (!body) return;
            body.innerHTML = currentData.auditLogs.map(l => `
                <tr>
                    <td style="font-family: var(--font-mono);">${l.timestamp}</td>
                    <td style="font-weight: 600;">${l.userName} (${l.userCode})</td>
                    <td style="color: #c084fc;">${l.roleName}</td>
                    <td><strong>${l.action}</strong></td>
                    <td><span style="background: #1e293b; padding: 0.15rem 0.5rem; border-radius: 4px; font-family: var(--font-mono); font-size: 0.75rem;">${l.category}</span></td>
                    <td><span style="color: ${l.status === 'EXITOSO' ? '#34d399' : '#f43f5e'}; font-weight: bold;">${l.status}</span></td>
                    <td style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">${l.details}</td>
                </tr>
            `).join('');
        }

        // 6. ESD Emergency Trip
        async function toggleEsd() {
            if (!confirm("⚠️ ADVERTENCIA CRÍTICA:\n¿Está seguro de activar la Parada de Emergencia ESD-1?\nSe cerrarán todas las válvulas SSV y se aislarán los pozos.")) {
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/esd.php`, {
                    method: 'POST',
                    headers: getAuthHeaders(),
                    body: JSON.stringify({ action: 'trip', userName: currentOperator.name, userCode: currentOperator.code })
                });
                const result = await res.json();
                if (result.success) {
                    showToast("🚨 ESD-1 ACTIVADO: Pozos y válvulas aislados en MySQL", true);
                    loadAllData();
                    loadAuditLogs();
                } else if (result.securityViolation) {
                    showToast(`⛔ RBAC DENEGADO: ${result.error}`, true);
                }
            } catch (e) {
                showToast("Error de comunicación", true);
            }
        }

        // 7. Pruebas en vivo de Patrones PHP vía API REST
        async function testPatternPhp(actionName, outputElementId, extraParams = {}) {
            const outEl = document.getElementById(outputElementId);
            if (outEl) {
                outEl.style.display = 'block';
                outEl.textContent = 'Ejecutando patrón/mitigación en PHP...';
            }

            try {
                const res = await fetch(`${API_BASE}/patterns.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: actionName, ...extraParams })
                });
                const json = await res.json();
                if (outEl) {
                    outEl.innerHTML = `<strong>✓ Resultado:</strong> ${json.explanation || 'Ejecutado con éxito'}<br><pre style="margin-top:0.3rem; color:#cbd5e1; font-size:0.65rem;">${JSON.stringify(json.diagnostics || json.executionResult || json.results || json.comparison || json, null, 2)}</pre>`;
                }
            } catch (err) {
                if (outEl) outEl.textContent = 'Error invocando API de patrones: ' + err.message;
            }
        }

        // 8. Generador en vivo de JWT desde PHP
        async function generateJwtFromPhp() {
            const selectEl = document.getElementById('jwtOperatorSelect');
            const code = selectEl ? selectEl.value : 'OP-449';
            const outBox = document.getElementById('jwtLiveOutput');
            const tokenStr = document.getElementById('jwtTokenString');
            const payloadDetails = document.getElementById('jwtPayloadDetails');

            try {
                const res = await fetch(`${API_BASE}/login.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ employeeCode: code, ttl: 28800 })
                });
                const data = await res.json();
                if (data.success && data.token) {
                    activeJwtToken = data.token;
                    localStorage.setItem('mapaf_jwt_token', activeJwtToken);

                    if (outBox) outBox.style.display = 'block';
                    if (tokenStr) tokenStr.textContent = data.token;
                    if (payloadDetails) {
                        payloadDetails.innerHTML = `
                            <strong>Operador:</strong> ${data.user.fullName} (${data.user.employeeCode})<br>
                            <strong>Rol:</strong> ${data.user.roleName} [${data.user.roleCode}]<br>
                            <strong>Sector:</strong> ${data.user.sector}<br>
                            <strong>Permisos Autorizados:</strong> ${data.user.permissionsCount} permisos RBAC<br>
                            <strong>Expira en:</strong> ${data.expiresAt}
                        `;
                    }
                    showToast("Token JWT emitido y sincronizado en sesión activa");
                } else {
                    showToast("Error generando JWT: " + (data.error || 'Fallo desconocido'), true);
                }
            } catch (err) {
                showToast("Error de conexión con /api/login.php", true);
            }
        }

        // 9. Funciones de Infraestructura y Topología SCADA
        async function pingInfrastructure() {
            const t0 = performance.now();
            const latencyEl = document.getElementById('infraLiveLatency');
            const badgeEl = document.getElementById('infraPingBadge');

            if (latencyEl) latencyEl.textContent = 'Midiendo...';

            try {
                const res = await fetch(`${API_BASE}/status.php?format=json`);
                const data = await res.json();
                const latency = Math.round(performance.now() - t0);

                if (latencyEl) {
                    latencyEl.textContent = `${latency} ms`;
                    latencyEl.style.color = latency < 50 ? '#34d399' : latency < 150 ? '#f59e0b' : '#f43f5e';
                }

                if (badgeEl) {
                    badgeEl.textContent = `${latency} MS LATENCIA`;
                }

                showToast(`⚡ INFRAESTRUCTURA SALUDABLE: Apache + PHP + MySQL (${latency} ms)`);
            } catch(e) {
                if (latencyEl) {
                    latencyEl.textContent = 'Error';
                    latencyEl.style.color = '#f43f5e';
                }
                showToast("Fallo de comunicación con /api/status.php", true);
            }
        }

        const FLOW_DEFINITIONS = {
            choke: {
                title: "Flujo A: Maniobra de Estrangulador (Choke) con Capacidad de Reversión (UNDO)",
                domain: "WELLHEAD-CTX · API 6A",
                pattern: "GoF Command Pattern con Historial de Rollback",
                steps: [
                    { num: 1, layer: "Capa 6 (HMI Web)", title: "Acción del Operador", desc: "El operador desliza el estrangulador a una nueva consigna (ej. 68.5%) o pulsa 'Deshacer Maniobra'." },
                    { num: 2, layer: "Capa 3 (API Gateway)", title: "Transmisión HTTP/JSON", desc: "Se emite petición POST /api/wellheads.php con payload JSON y cabecera 'Authorization: Bearer <token>'." },
                    { num: 3, layer: "Capa 4 (Seguridad JWT)", title: "Validación RBAC", desc: "JwtService::requirePermission('control_choke') verifica la firma criptográfica y autoriza al usuario." },
                    { num: 4, layer: "Capa 4 (Patrón Command)", title: "Encapsulamiento GoF", desc: "Se instancia SetChokeCommand($wellId, $prev, $target), registrando el valor anterior para revertir si es necesario." },
                    { num: 5, layer: "Capa 5 (Persistencia MySQL)", title: "Transacción Relacional", desc: "WellheadRepository ejecuta Prepared Statement: UPDATE pozos SET apertura_choke = :target WHERE id = :id." },
                    { num: 6, layer: "Capa 5 (Auditoría Forense)", title: "Registro 21 CFR Part 11", desc: "AuditRepository inserta un registro inmutable en bitacora_auditoria con firma del operador." },
                    { num: 7, layer: "Capa 6 (HMI Dashboard)", title: "Confirmación Reactiva", desc: "El servidor retorna HTTP 200 { success: true, canUndo: true } y el HMI actualiza el widget con botón UNDO activo." }
                ]
            },
            alarm: {
                title: "Flujo B: Transgresión de Presión y Notificación Desacoplada (ISA-18.2)",
                domain: "ALARMS-CTX · ANSI/ISA-18.2",
                pattern: "GoF Observer Pattern & Escudo Antipatrón Blind ACK",
                steps: [
                    { num: 1, layer: "Capa 1 (Campo)", title: "Lectura Física de Instrumento", desc: "Transmisor de presión PT-101 detecta sobrepresión en cabezal (ej. 2,450 PSI superando límite de 2,100 PSI)." },
                    { num: 2, layer: "Capa 2 (Pasarela IIoT)", title: "Adaptación de Trama", desc: "IndustrialProtocolAdapter decodifica el registro Modbus binario hacia la entidad TelemetryEvent." },
                    { num: 3, layer: "Capa 4 (Patrón Observer)", title: "Notificación Reactiva", desc: "TelemetrySubject dispara simultáneamente a AlarmTriggerObserver, AuditLoggerObserver y SafetyInterlockObserver." },
                    { num: 4, layer: "Capa 5 (Persistencia MySQL)", title: "Inserción de Alarma", desc: "AlarmRepository inserta la alarma en tabla 'alarmas' con severidad CRITICAL y estado reconocida = 0." },
                    { num: 5, layer: "Capa 6 (HMI Web)", title: "Alerta Operacional", desc: "El panel RTOC despliega el parpadeo en rojo y actualiza el contador de alarmas no reconocidas." },
                    { num: 6, layer: "Capa 4 (Escudo Antipatrón)", title: "Protocolo de Acuse Trazable", desc: "El operador abre el modal ISA-18.2; el sistema exige obligatoriamente la causa técnica antes de silenciar." }
                ]
            },
            jwt: {
                title: "Flujo C: Autenticación Criptográfica JWT y Enlace RBAC",
                domain: "IDENTITY-CTX · RFC 7519",
                pattern: "Arquitectura de Seguridad Basada en Tokens Claims",
                steps: [
                    { num: 1, layer: "Capa 6 (HMI Web)", title: "Inicio de Turno", desc: "El operador selecciona su credencial de turno en el conmutador superior (ej. OP-449 o ENG-204)." },
                    { num: 2, layer: "Capa 3 (API Gateway)", title: "Petición de Login", desc: "El navegador envía POST /api/login.php con el código de empleado y tiempo de expiración TTL." },
                    { num: 3, layer: "Capa 5 (Persistencia MySQL)", title: "Consulta de Credenciales", desc: "El backend consulta las tablas 'usuarios' y 'roles' para obtener los permisos autorizados." },
                    { num: 4, layer: "Capa 4 (Criptografía PHP)", title: "Emisión de Token JWT", desc: "JwtService genera Header (HS256) + Payload (Claims, exp, nbf) y calcula la firma criptográfica con clave secreta." },
                    { num: 5, layer: "Capa 6 (Almacenamiento Local)", title: "Sincronización de Sesión", desc: "El frontend recibe el token Bearer y lo almacena en localStorage para inyectarlo en cada operación." }
                ]
            },
            esd: {
                title: "Flujo D: Parada de Emergencia ESD-1 y Aislamiento de Planta (IEC 61511)",
                domain: "SAFETY-CTX · IEC 61511 / ISA-84",
                pattern: "GoF State Pattern & Enclavamiento de Seguridad",
                steps: [
                    { num: 1, layer: "Capa 6 (HMI Web)", title: "Pulsación de Botón ESD-1", desc: "El operador o el sistema instrumentado activa la Parada de Emergencia ESD-1." },
                    { num: 2, layer: "Capa 3 (API Gateway)", title: "Transmisión de Seguridad", desc: "Se emite POST /api/esd.php con firma del operador y autorización verificada." },
                    { num: 3, layer: "Capa 4 (Patrón State)", title: "Enclavamiento API 6A", desc: "La máquina de estados conmuta los pozos a estado DISPARADO (TRIPPED), bloqueando maniobras manuales." },
                    { num: 4, layer: "Capa 5 (Persistencia MySQL)", title: "Aislamiento en Base de Datos", desc: "Se ejecuta UPDATE pozos SET valvula_ssv_abierta = 0, apertura_choke = 0, estado = 'DISPARADO'." },
                    { num: 5, layer: "Capa 5 (Auditoría Forense)", title: "Trazabilidad Crítica", desc: "Se inserta registro de máxima prioridad en 'bitacora_auditoria' para investigación posterior." },
                    { num: 6, layer: "Capa 6 (HMI Dashboard)", title: "Bloqueo Seguro", desc: "El HMI muestra el banner rojo de enclavamiento; se requiere pulsar 'Rearmar Pozo' para volver a operar." }
                ]
            }
        };

        function renderDataFlow(flowKey) {
            const flow = FLOW_DEFINITIONS[flowKey] || FLOW_DEFINITIONS.choke;
            document.querySelectorAll('.flow-pill-btn').forEach(btn => btn.classList.remove('active'));
            const activeBtn = document.getElementById(`btn-flow-${flowKey}`);
            if (activeBtn) activeBtn.classList.add('active');

            const container = document.getElementById('flowTraceContainer');
            if (!container) return;

            container.innerHTML = `
                <div style="margin-bottom: 0.85rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.6rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong style="color: #38bdf8; font-size: 0.95rem;">${flow.title}</strong>
                        <div style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono); margin-top: 0.2rem;">
                            Dominio: <span style="color: #34d399;">${flow.domain}</span> · Patrón: <span style="color: #c084fc;">${flow.pattern}</span>
                        </div>
                    </div>
                    <span class="badge-pulse badge-pulse-green">PASO A PASO VERIFICADO</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    ${flow.steps.map(s => `
                        <div class="flow-step-item">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <strong style="color: #fff;">${s.num}. ${s.title}</strong>
                                <span style="color: #38bdf8; font-size: 0.7rem; font-weight: bold;">[${s.layer}]</span>
                            </div>
                            <div style="color: #94a3b8; font-size: 0.72rem; line-height: 1.4;">
                                ${s.desc}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Inicializar plataforma con JWT por defecto
        switchOperatorRole('OP-449');
        loadAllData();
        renderDataFlow('choke');
        pingInfrastructure();
        setInterval(loadAllData, 3000);
        setInterval(pingInfrastructure, 15000);
    </script>
</body>
</html>

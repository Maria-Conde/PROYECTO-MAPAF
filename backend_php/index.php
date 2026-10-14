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

            <button class="btn-action btn-danger" onclick="toggleEsd()" title="Aislamiento Total de Emergencia">
                ⚠️ ESD-1
            </button>

            <a href="/phpmyadmin" target="_blank" class="btn-action" title="Abrir phpMyAdmin de XAMPP">
                🗄️ phpMyAdmin
            </a>
        </div>
    </header>

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
                        <span class="module-group-count">1</span>
                    </div>
                    <div class="module-items-container" id="grp-items-arch">
                        <button class="nav-link" data-mod="comparisons" onclick="switchModule('comparisons', 'Arquitectura & SENA', 'Comparativas y Sustentación Técnica')">
                            <div class="nav-link-left">
                                <span class="nav-link-icon">📈</span>
                                <span>Comparativas & Guía</span>
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

            <!-- Barra de Migas de Pan (Breadcrumb) -->
            <div class="breadcrumb-bar">
                <div class="breadcrumb-path">
                    <span>MAPAF</span>
                    <span>›</span>
                    <span id="bcCategory">Operaciones RTOC</span>
                    <span>›</span>
                    <span class="current" id="bcModule">Visión General de Telemetría</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
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
            <!-- 9. MÓDULO: DIAGNÓSTICO XAMPP               -->
            <!-- ========================================== -->
            <section id="tab-diag" class="section-view">
                <!-- Hero Header -->
                <div class="module-hero" style="margin-bottom: 1.25rem;">
                    <div class="hero-left">
                        <div class="hero-icon-box">⚙️</div>
                        <div class="hero-text">
                            <h2>Diagnóstico de Servidores XAMPP & MySQL</h2>
                            <p>Estado de los servicios Apache, PHP, base de datos `mapaf_db` y puertos locales.</p>
                            <div class="hero-tags">
                                <span class="hero-tag">Apache 2.4</span>
                                <span class="hero-tag">PHP <?= phpversion() ?></span>
                                <span class="hero-tag">MySQL 3306</span>
                            </div>
                        </div>
                    </div>
                    <div class="hero-actions">
                        <a href="/phpmyadmin/index.php?route=/database/structure&db=mapaf_db" target="_blank" class="btn-action">
                            🗄️ Abrir phpMyAdmin
                        </a>
                    </div>
                </div>

                <div class="table-container" style="max-width: 820px; margin: 0 auto; padding: 1.5rem;">
                    <table class="data-table" style="margin-bottom: 1.5rem;">
                        <tbody>
                            <tr>
                                <td style="font-weight: 600;">Servidor Web</td>
                                <td>Apache 2.4 (XAMPP para Windows)</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Lenguaje Backend</td>
                                <td>PHP <?= phpversion() ?> (SAPI: <?= php_sapi_name() ?>)</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Base de Datos</td>
                                <td>MySQL <?= htmlspecialchars($dbStatus['mysql_version'] ?? 'Conectada') ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Base de Datos Seleccionada</td>
                                <td><code>mapaf_db</code></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Tablas en `mapaf_db`</td>
                                <td><span style="color: #34d399; font-weight: bold;"><?= count($dbStatus['tables']) ?> tablas verificadas</span></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600;">Ruta del Proyecto en XAMPP</td>
                                <td><code>C:\xampp\htdocs\mapaf\</code></td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="setup_database.php" target="_blank" class="btn-action" style="background: #10b981; color: #000; font-weight: bold;">
                            ⚙️ Re-ejecutar Instalador de Base de Datos
                        </a>
                        <button class="btn-action" onclick="loadAllData()">
                            🔍 Probar Conexión REST en Vivo
                        </button>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast">Notificación</div>

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

            // Si un grupo no tiene items visibles, ocultar el grupo
            groups.forEach(g => {
                const visibleLinks = g.querySelectorAll('.nav-link[style*="display: flex"], .nav-link:not([style*="display: none"])');
                if (q && visibleLinks.length === 0) {
                    g.style.display = 'none';
                } else {
                    g.style.display = 'flex';
                }
            });
        }

        // Cambio de módulo principal con actualización de breadcrumb
        function switchModule(moduleId, categoryName, moduleName) {
            document.querySelectorAll('.nav-link').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.section-view').forEach(sec => sec.classList.remove('active'));

            // Activar botón del sidebar
            const activeBtn = document.querySelector(`.nav-link[data-mod="${moduleId}"]`);
            if (activeBtn) activeBtn.classList.add('active');

            // Activar vista de sección
            const target = document.getElementById('tab-' + moduleId);
            if (target) target.classList.add('active');

            // Actualizar migas de pan
            document.getElementById('bcCategory').textContent = categoryName;
            document.getElementById('bcModule').textContent = moduleName;

            // Scroll arriba en el contenedor principal
            document.querySelector('.app-main').scrollTop = 0;
        }

        // Sub-tabs dentro de un módulo (ej. Diagramas o Comparativas)
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

        // Reloj en vivo
        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').textContent = now.toLocaleTimeString('es-CO');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Carga de datos desde API PHP / MySQL
        async function loadAllData() {
            try {
                const res = await fetch(`${API_BASE}/sync.php`);
                const data = await res.json();
                if (data.success) {
                    currentData = data;
                    renderMetrics();
                    renderWells();
                    renderAlarms();
                    renderUsersAndRoles();
                    renderAuditLogs();
                    updateDbPill(true);

                    // Actualizar tickers en encabezado
                    if (data.systemConfig) {
                        document.getElementById('kpiProd').textContent = `${Number(data.systemConfig.productionRate).toLocaleString('es-CO')} BPD`;
                        const flowEl = document.getElementById('kpiFlow');
                        flowEl.textContent = data.systemConfig.flowState.toUpperCase();
                        flowEl.style.color = data.systemConfig.flowState === 'tripped' ? '#f43f5e' :
                                             data.systemConfig.flowState === 'throttled' ? '#f59e0b' : '#34d399';
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

        // 2. Render Pozos con Filtros
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
                const statusClass = w.status === 'EN LÍNEA' ? 'status-online' :
                                    w.status === 'ESTRANGULADO' ? 'status-choked' :
                                    w.status === 'DISPARADO' ? 'status-tripped' : 'status-maint';

                return `
                <div class="well-card" id="well-card-${w.id}">
                    <div class="well-header">
                        <div>
                            <div class="well-title">${w.name}</div>
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">${w.sector}</span>
                        </div>
                        <span class="well-status ${statusClass}">${w.status}</span>
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

                    <div class="control-group">
                        <div class="control-label">
                            <span>Estrangulador (Choke FCV)</span>
                            <span id="choke-lbl-${w.id}" style="color: #38bdf8; font-weight: bold;">${w.chokeOpening}%</span>
                        </div>
                        <input type="range" min="0" max="100" step="0.5" value="${w.chokeOpening}" class="choke-slider"
                               oninput="document.getElementById('choke-lbl-${w.id}').textContent = this.value + '%'"
                               onchange="updateWellChoke('${w.id}', this.value)">
                    </div>

                    <div class="valve-toggles">
                        <button class="btn-valve ${w.ssvOpen ? 'open' : ''}" onclick="toggleSsv('${w.id}', ${!w.ssvOpen})">
                            SSV: ${w.ssvOpen ? 'ABIERTA' : 'CERRADA'}
                        </button>
                        <button class="btn-valve ${w.chemicalInjection ? 'open' : ''}" onclick="toggleChem('${w.id}', ${!w.chemicalInjection})">
                            Iny. Química: ${w.chemicalInjection ? 'ACTIVA' : 'INACTIVA'}
                        </button>
                    </div>
                </div>
                `;
            }).join('');
        }

        async function updateWellChoke(id, val) {
            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, chokeOpening: parseFloat(val), actionDescription: `Ajuste estrangulador a ${val}%` })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`Pozo ${id}: Choke actualizado a ${val}% en MySQL`);
                    loadAuditLogs();
                }
            } catch (e) {
                showToast("Error actualizando en MySQL", true);
            }
        }

        async function toggleSsv(id, newOpen) {
            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, ssvOpen: newOpen, actionDescription: `Maniobra Válvula SSV: ${newOpen ? 'Apertura' : 'Cierre'}` })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`Válvula SSV ${newOpen ? 'Abierta' : 'Cerrada'} en MySQL`);
                    loadAllData();
                }
            } catch (e) {
                showToast("Error de conexión", true);
            }
        }

        async function toggleChem(id, newChem) {
            try {
                const res = await fetch(`${API_BASE}/wellheads.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, chemicalInjection: newChem, actionDescription: `Inyección química ${newChem ? 'Activada' : 'Detenida'}` })
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

        // 3. Render Alarmas con Filtros
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
                            <button class="btn-ack ${a.acknowledged ? 'disabled' : ''}" onclick="ackAlarm('${a.id}')">
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
                            <button class="btn-ack ${a.acknowledged ? 'disabled' : ''}" onclick="ackAlarm('${a.id}')">
                                ${a.acknowledged ? '✓ RECONOCIDA' : 'RECONOCER'}
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }

        async function ackAlarm(id) {
            try {
                const res = await fetch(`${API_BASE}/alarms.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, acknowledgedBy: 'M. Conde Dussan (Operador)' })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`Alarma ${id} reconocida y guardada en MySQL`);
                    loadAllData();
                }
            } catch (e) {
                showToast("Error reconociendo alarma", true);
            }
        }

        async function ackAllAlarms() {
            try {
                const res = await fetch(`${API_BASE}/alarms.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'ack_all', acknowledgedBy: 'M. Conde Dussan (Operador)' })
                });
                const result = await res.json();
                if (result.success) {
                    showToast(`Todas las alarmas reconocidas exitosamente`);
                    loadAllData();
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
            if (!confirm("⚠️ ADVERTENCIA CRÍTICA:\n¿Está seguro de activar la Parada de Emergencia ESD-1?\nSe cerrarán todas las válvulas SSV y se estrangularán los pozos.")) {
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/esd.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'trip', userName: 'M. Conde Dussan' })
                });
                const result = await res.json();
                if (result.success) {
                    showToast("🚨 ESD-1 ACTIVADO: Pozos y válvulas aislados en MySQL", true);
                    loadAllData();
                }
            } catch (e) {
                showToast("Error de comunicación", true);
            }
        }

        // Inicializar plataforma
        loadAllData();
        setInterval(loadAllData, 3000);
    </script>
</body>
</html>

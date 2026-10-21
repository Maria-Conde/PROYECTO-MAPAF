<?php
/**
 * PROYECTO MAPAF - Endpoint: api/domains.php
 * Catálogo de Dominios del Sistema (Domain-Driven Design - DDD)
 * SENA ADSO · Ficha 3413988
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../domain/DomainArchitecture.php';

use Mapaf\Domain\DomainCatalog;

handleCorsHeaders();

$domains = array_map(fn($d) => $d->toArray(), DomainCatalog::getAllDomains());

// Si el usuario ingresa desde un navegador (Accept: text/html), mostrar interfaz visual interactiva
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
$wantsJson = isset($_GET['format']) && $_GET['format'] === 'json';
if (!$wantsJson && str_contains($acceptHeader, 'text/html')) {
    renderInteractiveDomainsHtml($domains);
    exit;
}

sendJsonResponse([
    'success' => true,
    'totalDomains' => count($domains),
    'architecture' => 'Domain-Driven Design (DDD) - Bounded Contexts',
    'system' => 'MAPAF Tactical SCADA & Telemetría IIoT',
    'domains' => $domains
]);

function renderInteractiveDomainsHtml(array $domains): void {
    ?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAPAF · Arquitectura de Dominios DDD (7 Bounded Contexts)</title>
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
            max-width: 1050px;
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
            border-top: 3px solid #38bdf8;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .card-title { font-size: 0.95rem; font-weight: 700; color: #fff; }
        .badge {
            font-family: var(--font-mono);
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.2);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.4);
        }
        .card-desc { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.4; }
        .card-tech {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: #cbd5e1;
            background: #060913;
            padding: 0.6rem;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.05);
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-card">
            <div class="header-title">
                <h1>🏛️ Arquitectura de Dominios DDD · MAPAF</h1>
                <p>7 Bounded Contexts · Normas ISA-18.2, API 6A, IEC 61511 · SENA ADSO Ficha 3413988</p>
            </div>
            <div class="header-nav">
                <a href="/mapaf/" class="btn">🕹️ Centro SCADA</a>
                <a href="patterns.php" class="btn">⚙️ Patrones GoF</a>
                <a href="login.php" class="btn" style="border-color: #a855f7; color: #c084fc;">🔐 Login JWT</a>
                <a href="domains.php?format=json" class="btn" target="_blank">📄 JSON Puro</a>
            </div>
        </div>

        <div class="grid-cards">
            <?php foreach ($domains as $idx => $d): ?>
            <div class="card">
                <div>
                    <div class="card-header">
                        <span class="card-title"><?= ($idx + 1) ?>. <?= htmlspecialchars($d['name']) ?></span>
                        <span class="badge"><?= htmlspecialchars($d['code']) ?></span>
                    </div>
                    <div class="card-desc"><?= htmlspecialchars($d['description']) ?></div>
                </div>
                <div class="card-tech">
                    <strong style="color: #38bdf8;">Norma Industrial:</strong> <?= htmlspecialchars($d['standard']) ?><br>
                    <strong style="color: #34d399;">Entidades Principales:</strong> <?= htmlspecialchars(implode(', ', $d['entities'])) ?><br>
                    <strong style="color: #fbbf24;">Eventos de Dominio:</strong> <?= htmlspecialchars(implode(', ', $d['domainEvents'])) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
    <?php
}


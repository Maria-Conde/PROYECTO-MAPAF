<?php
/**
 * PROYECTO MAPAF - Instalador y Migrador de Base de Datos mediante Liquibase
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Programa: Análisis y Desarrollo de Software (ADSO) | Ficha: 3413988-ADSO
 * 
 * Uso vía consola:
 *   C:\xampp\php\php.exe setup_database.php
 * Uso vía navegador:
 *   http://localhost/mapaf/setup_database.php
 *   http://localhost/mapaf/setup_database.php?reset=1 (Montaje limpio)
 */

require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/services/LiquibaseService.php';

$isCli = (php_sapi_name() === 'cli');

try {
    $liquibase = new LiquibaseService();

    // Si se pasa ?reset=1 o flag --reset, ejecuta reconstrucción limpia
    $isReset = (isset($_GET['reset']) && $_GET['reset'] === '1') || (isset($argv[1]) && $argv[1] === '--reset');

    if ($isReset) {
        $migrationResult = $liquibase->resetAndMount();
    } else {
        $migrationResult = $liquibase->update();
    }

    $status = $liquibase->getStatus();

    // Obtener estadísticas de tablas de negocio y Liquibase
    $pdo = Database::getInstance()->getConnection();
    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    $counts = [];
    foreach ($tables as $tbl) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
        $counts[$tbl] = (int)$cnt;
    }

    $mysqlVer = $pdo->query("SELECT VERSION()")->fetchColumn();

    $response = [
        'success'           => true,
        'message'           => '¡Base de datos MAPAF (' . DB_NAME . ') montada y sincronizada correctamente mediante Liquibase!',
        'database'          => DB_NAME,
        'mysql_version'     => $mysqlVer,
        'php_version'       => phpversion(),
        'liquibase_version' => $status['liquibaseVersion'],
        'changelog_file'    => $status['changelogFile'],
        'changesets_total'  => $status['totalChangesets'],
        'changesets_applied'=> $status['appliedCount'],
        'tables_count'      => count($tables),
        'tables_summary'    => $counts,
        'timestamp'         => date('Y-m-d H:i:s')
    ];

    if ($isCli) {
        echo "\n=======================================================\n";
        echo " PROYECTO MAPAF - MIGRACIÓN DE BASE DE DATOS LIQUIBASE\n";
        echo " SENA ADSO · Ficha 3413988 | XAMPP MySQL 3306\n";
        echo "=======================================================\n";
        echo " Estado:               ÉXITO\n";
        echo " Base de datos:        " . DB_NAME . "\n";
        echo " Motor MySQL:          " . $mysqlVer . "\n";
        echo " Versión Liquibase:    " . $status['liquibaseVersion'] . "\n";
        echo " Changelog Master:     " . $status['changelogFile'] . "\n";
        echo " ChangeSets Aplicados: " . $status['appliedCount'] . " / " . $status['totalChangesets'] . "\n";
        echo " Tablas Registradas:   " . count($tables) . " (Incluye DATABASECHANGELOG)\n";
        echo "-------------------------------------------------------\n";
        foreach ($counts as $t => $c) {
            $isLiquibaseTbl = in_array(strtolower($t), ['databasechangelog', 'databasechangeloglock']);
            $tag = $isLiquibaseTbl ? " [LIQUIBASE]" : "";
            printf("  • %-24s : %2d filas%s\n", $t, $c, $tag);
        }
        echo "=======================================================\n\n";
        exit(0);
    }

    // Modo Web JSON
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        sendJsonResponse($response);
    }

    // Interfaz visual HTML
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Montaje Base de Datos Liquibase · PROYECTO MAPAF</title>
    <style>
        :root {
            --bg: #060913;
            --card: #0d1527;
            --border: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
            --primary: #10b981;
            --primary-bg: rgba(16, 185, 129, 0.12);
            --cyan: #38bdf8;
            --purple: #a855f7;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        body { background: var(--bg); color: var(--text); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem; }
        .setup-card { max-width: 680px; width: 100%; background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
        .badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.8rem; background: var(--primary-bg); color: var(--primary); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 1rem; }
        h1 { font-size: 1.4rem; margin-bottom: 0.35rem; color: #fff; }
        p { color: var(--muted); font-size: 0.88rem; line-height: 1.5; margin-bottom: 1.25rem; }
        
        .kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1.5rem; }
        .kpi-box { background: #090e1d; border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem; text-align: center; }
        .kpi-box-val { font-size: 1.3rem; font-weight: 700; font-family: monospace; color: var(--cyan); }
        .kpi-box-lbl { font-size: 0.7rem; color: var(--muted); text-transform: uppercase; margin-top: 0.2rem; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.85rem; }
        th, td { padding: 0.65rem 0.75rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--muted); text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; }
        td.count { text-align: right; font-weight: 600; color: var(--primary); font-family: monospace; }
        .btn-row { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.7rem 1.3rem; background: var(--primary); color: #000; font-weight: 700; border-radius: 8px; text-decoration: none; transition: opacity 0.2s; font-size: 0.88rem; }
        .btn:hover { opacity: 0.9; }
        .btn-secondary { background: #1e293b; color: #fff; }
        .btn-secondary:hover { background: #334155; }
    </style>
</head>
<body>
    <div class="setup-card">
        <span class="badge">✓ Liquibase 4.27 · Base de Datos Montada</span>
        <h1>Base de Datos MySQL: <?= htmlspecialchars(DB_NAME) ?></h1>
        <p>El esquema normalizado y el historial de ChangeSets del <strong>PROYECTO MAPAF</strong> han sido aplicados mediante el changelog maestro <code>db.changelog-master.xml</code>.</p>
        
        <div class="kpi-row">
            <div class="kpi-box">
                <div class="kpi-box-val" style="color:var(--primary);"><?= $status['appliedCount'] ?> / <?= $status['totalChangesets'] ?></div>
                <div class="kpi-box-lbl">ChangeSets OK</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-box-val"><?= count($tables) ?></div>
                <div class="kpi-box-lbl">Tablas Creadas</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-box-val" style="color:var(--purple);"><?= htmlspecialchars($status['liquibaseVersion']) ?></div>
                <div class="kpi-box-lbl">Versión Liquibase</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tabla del Sistema / Auditoría</th>
                    <th style="text-align: right;">Filas Cargadas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($counts as $t => $c): 
                    $isLq = in_array(strtolower($t), ['databasechangelog', 'databasechangeloglock']);
                ?>
                <tr>
                    <td>
                        <code><?= htmlspecialchars($t) ?></code>
                        <?php if ($isLq): ?>
                            <span style="font-size:0.68rem; color:var(--purple); font-weight:bold; margin-left:0.4rem;">[LIQUIBASE]</span>
                        <?php endif; ?>
                    </td>
                    <td class="count"><?= $c ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="btn-row">
            <a href="index.php" class="btn">🚀 Abrir Plataforma SCADA</a>
            <a href="setup_database.php?reset=1" class="btn btn-secondary" onclick="return confirm('¿Deseas recrear la base de datos limpia desde Liquibase?')">🔄 Montaje Limpio (Reset)</a>
        </div>
    </div>
</body>
</html>
<?php
} catch (Throwable $e) {
    if ($isCli) {
        fwrite(STDERR, "\n[ERROR] Falló la instalación de la base de datos: " . $e->getMessage() . "\n\n");
        exit(1);
    }
    http_response_code(500);
    echo '<div style="background:#1a0f14; color:#f87171; border:1px solid #7f1d1d; padding:2rem; font-family:sans-serif; max-width:600px; margin:2rem auto; border-radius:8px;">';
    echo '<h2 style="margin-top:0;">⚠️ Error al Inicializar MySQL en XAMPP</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p style="color:#94a3b8; font-size:0.9rem;">Por favor, verifica que el módulo <strong>MySQL</strong> esté en estado <strong>Running</strong> en el Panel de Control de XAMPP (puerto 3306).</p>';
    echo '</div>';
    exit(1);
}

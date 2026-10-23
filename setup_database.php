<?php
/**
 * PROYECTO MAPAF - Instalador y Migrador de Base de Datos MySQL (XAMPP)
 * SENA ADSO - Ficha 3413988
 * 
 * Uso vía consola:
 *   C:\xampp\php\php.exe setup_database.php
 * Uso vía navegador:
 *   http://localhost/mapaf/setup_database.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/cors.php';

$isCli = (php_sapi_name() === 'cli');

try {
    // 1. Conectar al servidor MySQL inicial (sin seleccionar base de datos)
    $dsnInitial = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $pdoOptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    $pdo = new PDO($dsnInitial, DB_USER, DB_PASS, $pdoOptions);

    // 2. Localizar el archivo SQL normalizado
    $sqlFile = __DIR__ . '/sql/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("No se encontró el archivo de esquema SQL en: " . $sqlFile);
    }

    $sqlContent = file_get_contents($sqlFile);

    // 3. Crear base de datos si no existe y usarla
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `" . DB_NAME . "`;");

    // 4. Ejecutar el script SQL completo
    $pdo->exec($sqlContent);

    // 5. Verificar estadísticas de tablas y registros creados
    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    $counts = [];
    foreach ($tables as $tbl) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
        $counts[$tbl] = (int)$cnt;
    }

    $mysqlVer = $pdo->query("SELECT VERSION()")->fetchColumn();

    $response = [
        'success'        => true,
        'message'        => '¡Base de datos MAPAF (' . DB_NAME . ') inicializada y configurada correctamente en XAMPP!',
        'database'       => DB_NAME,
        'mysql_version'  => $mysqlVer,
        'php_version'    => phpversion(),
        'tables_count'   => count($tables),
        'tables_summary' => $counts,
        'timestamp'      => date('Y-m-d H:i:s')
    ];

    if ($isCli) {
        echo "\n=======================================================\n";
        echo " PROYECTO MAPAF - INSTALACIÓN DE BASE DE DATOS MYSQL\n";
        echo " SENA ADSO · Ficha 3413988 | XAMPP MySQL 3306\n";
        echo "=======================================================\n";
        echo " Estado:           ÉXITO\n";
        echo " Base de datos:    " . DB_NAME . "\n";
        echo " Versión MySQL:    " . $mysqlVer . "\n";
        echo " Tablas Creadas:   " . count($tables) . "\n";
        echo "-------------------------------------------------------\n";
        foreach ($counts as $t => $c) {
            printf("  • %-22s : %2d registros\n", $t, $c);
        }
        echo "=======================================================\n\n";
        exit(0);
    }

    // Modo Web
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
    <title>Instalador Base de Datos · PROYECTO MAPAF</title>
    <style>
        :root {
            --bg: #060913;
            --card: #0d1527;
            --border: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
            --primary: #10b981;
            --primary-bg: rgba(16, 185, 129, 0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        body { background: var(--bg); color: var(--text); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem; }
        .setup-card { max-width: 620px; width: 100%; background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
        .badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.8rem; background: var(--primary-bg); color: var(--primary); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 999px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 1rem; }
        h1 { font-size: 1.4rem; margin-bottom: 0.5rem; color: #fff; }
        p { color: var(--muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.85rem; }
        th, td { padding: 0.65rem 0.75rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        td.count { text-align: right; font-weight: 600; color: var(--primary); font-family: monospace; }
        .btn-row { display: flex; gap: 0.75rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.4rem; background: var(--primary); color: #000; font-weight: 700; border-radius: 8px; text-decoration: none; transition: opacity 0.2s; font-size: 0.9rem; }
        .btn:hover { opacity: 0.9; }
        .btn-secondary { background: #1e293b; color: #fff; }
        .btn-secondary:hover { background: #334155; }
    </style>
</head>
<body>
    <div class="setup-card">
        <span class="badge">✓ Instalación Exitosa</span>
        <h1>Base de Datos MySQL: <?= htmlspecialchars(DB_NAME) ?></h1>
        <p>El esquema normalizado y las tablas iniciales del <strong>PROYECTO MAPAF</strong> han sido creados y poblados correctamente en tu servidor XAMPP.</p>
        
        <table>
            <thead>
                <tr>
                    <th>Tabla Normalizada</th>
                    <th style="text-align: right;">Registros Cargados</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($counts as $t => $c): ?>
                <tr>
                    <td><code><?= htmlspecialchars($t) ?></code></td>
                    <td class="count"><?= $c ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="btn-row">
            <a href="index.php" class="btn">🚀 Abrir Plataforma SCADA</a>
            <a href="setup_database.php" class="btn btn-secondary">🔄 Re-migrar Datos</a>
        </div>
    </div>
</body>
</html>
<?php
} catch (Exception $e) {
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

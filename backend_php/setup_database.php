<?php
/**
 * PROYECTO MAPAF - Instalador y Migrador de Base de Datos MySQL
 * Ejecutable vía navegador: http://localhost/mapaf/setup_database.php
 * O vía consola: php setup_database.php
 */

require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/config/database.php';

handleCorsHeaders();

$isCli = (php_sapi_name() === 'cli');

try {
    // 1. Conectar al servidor MySQL (sin seleccionar BD todavía)
    $pdo = getDbConnection(false);
    if (!$pdo) {
        throw new Exception("No se pudo conectar a MySQL en " . DB_HOST . ":" . DB_PORT . ". Verifica que MySQL esté activo en XAMPP.");
    }

    // 2. Cargar el archivo database.sql
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("No se encontró el archivo database.sql en " . $sqlFile);
    }

    $sqlContent = file_get_contents($sqlFile);

    // 3. Ejecutar las consultas SQL
    // Separamos por punto y coma seguro
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `" . DB_NAME . "`;");

    // Ejecutar el script completo
    $pdo->exec($sqlContent);

    // 4. Verificar tablas creadas
    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    $counts = [];
    foreach ($tables as $table) {
        $cntStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
        $counts[$table] = (int)$cntStmt->fetchColumn();
    }

    $response = [
        'success' => true,
        'message' => '¡Base de datos MAPAF (' . DB_NAME . ') inicializada y poblada con éxito!',
        'database' => DB_NAME,
        'mysql_version' => $pdo->query('SELECT VERSION()')->fetchColumn(),
        'php_version' => phpversion(),
        'tables_count' => count($tables),
        'tables_details' => $counts,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if ($isCli) {
        echo "\n=======================================================\n";
        echo " PROYECTO MAPAF - INSTALACIÓN DE BASE DE DATOS MYSQL\n";
        echo "=======================================================\n";
        echo " Estado: ÉXITO\n";
        echo " Base de datos: " . DB_NAME . "\n";
        echo " Tablas creadas: " . count($tables) . "\n";
        foreach ($counts as $tbl => $cnt) {
            echo "  - {$tbl}: {$cnt} registros\n";
        }
        echo "=======================================================\n\n";
    } else {
        // Si se pide como JSON desde la app
        if (isset($_GET['format']) && $_GET['format'] === 'json' || isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            sendJsonResponse($response, 200);
        } else {
            // Renderizar una página HTML limpia y moderna
            header('Content-Type: text/html; charset=utf-8');
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>MAPAF - Inicialización de Base de Datos MySQL</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0b0f19; color: #f1f5f9; padding: 2rem; margin: 0; }
                    .card { max-width: 680px; margin: 0 auto; background: #131b2e; border: 1px solid #1e293b; border-radius: 12px; padding: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
                    .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; background: #064e3b; color: #34d399; font-weight: 600; font-size: 0.85rem; }
                    h1 { margin-top: 0.5rem; font-size: 1.5rem; color: #fff; }
                    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
                    th, td { text-align: left; padding: 0.6rem 0.8rem; border-bottom: 1px solid #1e293b; }
                    th { color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; }
                    .btn { display: inline-block; margin-top: 1.5rem; padding: 0.7rem 1.2rem; background: #10b981; color: #000; text-decoration: none; border-radius: 6px; font-weight: bold; }
                    .btn:hover { background: #059669; color: #fff; }
                </style>
            </head>
            <body>
                <div class="card">
                    <span class="badge">✓ Instalación Correcta</span>
                    <h1>Base de Datos MySQL: mapaf_db</h1>
                    <p style="color: #94a3b8;">La base de datos del proyecto <strong>MAPAF</strong> ha sido creada y poblada correctamente en XAMPP.</p>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Tabla</th>
                                <th>Registros Creados</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($counts as $tbl => $cnt): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($tbl) ?></code></td>
                                <td style="color: #34d399; font-weight: bold;"><?= $cnt ?> filas</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div style="margin-top: 1.5rem; font-size: 0.85rem; color: #64748b;">
                        Servidor: <?= htmlspecialchars(DB_HOST . ':' . DB_PORT) ?> | MySQL: <?= htmlspecialchars($response['mysql_version']) ?> | PHP: <?= htmlspecialchars(phpversion()) ?>
                    </div>

                    <a href="/mapaf/" class="btn">Abrir Panel SCADA MAPAF</a>
                </div>
            </body>
            </html>
            <?php
        }
    }

} catch (Exception $e) {
    $errResponse = [
        'success' => false,
        'error' => $e->getMessage(),
        'database' => DB_NAME,
        'host' => DB_HOST . ':' . DB_PORT
    ];

    if ($isCli) {
        echo "\n[ERROR] Falló la instalación: " . $e->getMessage() . "\n\n";
    } else {
        sendJsonResponse($errResponse, 500);
    }
}

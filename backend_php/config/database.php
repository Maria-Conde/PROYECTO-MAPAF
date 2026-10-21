<?php
/**
 * PROYECTO MAPAF - Configuración y Conexión PDO a MySQL (XAMPP)
 * Conecta con el servidor MySQL local de XAMPP (por defecto: localhost, usuario root, sin contraseña)
 */

require_once __DIR__ . '/../patterns/Singleton/DatabaseSingleton.php';

use Mapaf\Patterns\Singleton\DatabaseSingleton;

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'mapaf_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

function getDbConnection($selectDatabase = true) {
    if ($selectDatabase) {
        return DatabaseSingleton::getInstance()->getConnection();
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        return null;
    }
}

function checkDatabaseStatus() {
    $status = [
        'connected' => false,
        'database_exists' => false,
        'mysql_version' => null,
        'php_version' => phpversion(),
        'error' => null,
        'tables' => []
    ];

    try {
        // 1. Probar conexión al servidor MySQL
        $pdoServer = getDbConnection(false);
        if (!$pdoServer) {
            $status['error'] = 'No se pudo conectar al servidor MySQL en ' . DB_HOST . ':' . DB_PORT . '. Asegúrate de que MySQL esté iniciado en el Panel de Control de XAMPP.';
            return $status;
        }

        $status['connected'] = true;
        $status['mysql_version'] = $pdoServer->query('SELECT VERSION()')->fetchColumn();

        // 2. Verificar si existe la base de datos mapaf_db
        $stmt = $pdoServer->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname");
        $stmt->execute(['dbname' => DB_NAME]);
        $dbFound = $stmt->fetchColumn();

        if ($dbFound) {
            $status['database_exists'] = true;
            $pdoDb = getDbConnection(true);
            if ($pdoDb) {
                $tablesQuery = $pdoDb->query("SHOW TABLES");
                $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
                $status['tables'] = $tables;
            }
        } else {
            $status['error'] = 'El servidor MySQL responde, pero la base de datos "' . DB_NAME . '" no ha sido creada todavía.';
        }

    } catch (Exception $e) {
        $status['error'] = $e->getMessage();
    }

    return $status;
}

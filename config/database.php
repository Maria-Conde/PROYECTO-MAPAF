<?php
/**
 * PROYECTO MAPAF - Gestión de Conexión PDO a MySQL (XAMPP)
 * Implementa el Patrón de Diseño Creacional SINGLETON
 * 
 * Garantiza una única instancia de conexión PDO compartida, eliminando
 * la saturación de conexiones y fugas de memoria en Apache / MySQL.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private ?string $connectionError = null;

    /**
     * Constructor privado: Inicializa la conexión PDO segura
     */
    private function __construct() {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->pdo = null;
            $this->connectionError = $e->getMessage();
        }
    }

    // Prevenir clonación y deserialización
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("No está permitido deserializar la clase Singleton Database.");
    }

    /**
     * Obtiene la instancia única de Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Devuelve la conexión PDO activa o null si no se pudo conectar
     */
    public function getConnection(): ?PDO {
        return $this->pdo;
    }

    /**
     * Devuelve el error de conexión si existió
     */
    public function getError(): ?string {
        return $this->connectionError;
    }

    /**
     * Comprueba si la conexión y la base de datos están listas
     */
    public static function checkHealth(): array {
        $instance = self::getInstance();
        $pdo = $instance->getConnection();

        $info = [
            'connected'       => ($pdo !== null),
            'database_exists' => false,
            'mysql_version'   => null,
            'php_version'     => phpversion(),
            'host'            => DB_HOST . ':' . DB_PORT,
            'database'        => DB_NAME,
            'tables'          => [],
            'error'           => $instance->getError()
        ];

        if ($pdo) {
            try {
                $info['database_exists'] = true;
                $info['mysql_version'] = $pdo->query('SELECT VERSION()')->fetchColumn();
                $tablesQuery = $pdo->query('SHOW TABLES');
                $info['tables'] = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {
                $info['error'] = $e->getMessage();
            }
        } else {
            // Intentar verificar si el servidor MySQL responde aunque la BD no exista
            try {
                $dsnRaw = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
                $rawPdo = new PDO($dsnRaw, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $info['mysql_version'] = $rawPdo->query('SELECT VERSION()')->fetchColumn();
                $info['error'] = 'MySQL está activo, pero la base de datos "' . DB_NAME . '" no ha sido creada. Ejecuta setup_database.php.';
            } catch (Exception $ex) {
                $info['error'] = 'No se pudo conectar a MySQL en ' . DB_HOST . ':' . DB_PORT . '. Verifica que el servicio MySQL esté iniciado en el Panel de XAMPP.';
            }
        }

        return $info;
    }
}

/**
 * Función auxiliar para obtener rápidamente la conexión PDO
 */
function getDb(): ?PDO {
    return Database::getInstance()->getConnection();
}

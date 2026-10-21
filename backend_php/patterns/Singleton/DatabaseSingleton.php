<?php
/**
 * PROYECTO MAPAF - Patrón Creacional: SINGLETON
 * SENA ADSO · Ficha 3413988
 * 
 * Garantiza una única instancia de conexión PDO a MySQL (mapaf_db en XAMPP)
 * para todo el ciclo de vida de la petición HTTP, previniendo fugas de memoria,
 * saturación del pool de conexiones del servidor MySQL y asegurando transacciones atómicas.
 */

namespace Mapaf\Patterns\Singleton;

use PDO;
use PDOException;
use Exception;

class DatabaseSingleton {
    private static ?DatabaseSingleton $instance = null;
    private ?PDO $connection = null;
    private int $connectionCount = 0;
    private string $instantiatedAt;

    /**
     * Constructor privado: Previene la instanciación directa con 'new'
     */
    private function __construct() {
        $this->instantiatedAt = date('Y-m-d H:i:s');
        $this->initConnection();
    }

    /**
     * Clonación privada: Previene la clonación del objeto
     */
    private function __clone() {}

    /**
     * Deserialización privada: Previene la deserialización
     */
    public function __wakeup() {
        throw new Exception("No se permite deserializar una instancia del Singleton DatabaseSingleton.");
    }

    /**
     * Punto de acceso global estático a la instancia única
     */
    public static function getInstance(): DatabaseSingleton {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializa la conexión PDO con parámetros seguros y codificación UTF-8
     */
    private function initConnection(): void {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $db   = getenv('DB_NAME') ?: 'mapaf_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
            $this->connectionCount++;
        } catch (PDOException $e) {
            $this->connection = null;
        }
    }

    /**
     * Obtiene la conexión PDO activa
     */
    public function getConnection(): ?PDO {
        $this->connectionCount++;
        return $this->connection;
    }

    /**
     * Métricas de diagnóstico del Singleton para sustentación técnica
     */
    public function getDiagnostics(): array {
        return [
            'pattern' => 'Singleton (GoF Creacional)',
            'class' => __CLASS__,
            'instantiated_at' => $this->instantiatedAt,
            'access_counter' => $this->connectionCount,
            'is_connected' => $this->connection !== null,
            'driver' => $this->connection ? $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME) : 'none',
            'server_version' => $this->connection ? $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION) : null,
            'hash' => spl_object_hash($this),
        ];
    }
}

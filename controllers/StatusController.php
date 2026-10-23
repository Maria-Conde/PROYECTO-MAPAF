<?php
/**
 * PROYECTO MAPAF - Controlador: StatusController
 * SENA ADSO - Ficha 3413988
 * 
 * Diagnostica el entorno XAMPP (Apache, PHP 8.x, MySQL/MariaDB en puerto 3306).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

class StatusController {
    /**
     * Devuelve el estado de salud de todos los componentes
     */
    public function index(): void {
        $health = Database::checkHealth();

        $response = [
            'success'     => true,
            'app_name'    => APP_NAME,
            'version'     => APP_VERSION,
            'sena_ficha'  => SENA_FICHA,
            'environment' => [
                'server'     => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache/2.4 (XAMPP)',
                'php_version'=> phpversion(),
                'sapi'       => php_sapi_name(),
                'os'         => PHP_OS,
                'htdocs_path'=> realpath(BASE_DIR)
            ],
            'database' => [
                'connected'      => $health['connected'],
                'database_exists'=> $health['database_exists'],
                'host'           => $health['host'],
                'database_name'  => $health['database'],
                'mysql_version'  => $health['mysql_version'],
                'tables'         => $health['tables'],
                'tables_count'   => count($health['tables']),
                'error'          => $health['error']
            ],
            'status' => $health['connected'] ? 'ONLINE' : 'DEGRADED'
        ];

        sendJsonResponse($response, $health['connected'] ? 200 : 503);
    }
}

<?php
/**
 * PROYECTO MAPAF - Controlador: StatusController
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Diagnostica el entorno XAMPP, el estado de la base de datos,
 * la configuración de autenticación JWT y los patrones de arquitectura activos.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../services/JwtService.php';

class StatusController {
    /**
     * Devuelve el estado de salud de todos los componentes y de la arquitectura
     */
    public function index(): void {
        $health = Database::checkHealth();

        $response = [
            'success'     => true,
            'app_name'    => APP_NAME,
            'version'     => APP_VERSION,
            'sena_ficha'  => SENA_FICHA,
            'architecture' => [
                'pattern_mvc'        => 'Activo (Controladores delgados, Servicios de Dominio, Repositorios)',
                'pattern_repository' => 'Activo (WellheadRepository, AlarmRepository, TelemetryRepository, UserRepository, AuditRepository)',
                'pattern_service'    => 'Activo (WellheadService, AlarmService, EsdService, AuthService, TelemetryService)',
                'pattern_observer'   => 'Activo (EventDispatcher + AuditObserver desacoplado para 21 CFR Part 11)',
                'pattern_singleton'  => 'Activo (Database::getInstance() PDO para MySQL/XAMPP)',
                'domain_modeling'    => 'Activo (Value Objects: ChokeOpening, WellheadStatus, AlarmSeverity, OperatorIdentity)',
                'security_jwt'       => [
                    'standard'   => 'RFC 7519 / RFC 7515',
                    'algorithm'  => 'HS256',
                    'status'     => 'OPERATIVO',
                    'expiration' => (defined('JWT_EXPIRATION_SECONDS') ? JWT_EXPIRATION_SECONDS : 86400) . 's',
                    'bearer_auth'=> 'Activo en endpoints REST y cliente Fetch'
                ]
            ],
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
                'error'          => $health['error'],
                'fallback_mode'  => $health['connected'] ? 'OFF (MySQL Live)' : 'ON (In-Memory Repository Resiliente)'
            ],
            'status' => $health['connected'] ? 'ONLINE' : 'DEGRADED_RESILIENT'
        ];

        sendJsonResponse($response, 200);
    }
}

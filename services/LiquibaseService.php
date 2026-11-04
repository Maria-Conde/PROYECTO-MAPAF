<?php
/**
 * PROYECTO MAPAF - Motor de Migraciones y Gestión de Esquema Liquibase
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Programa: Análisis y Desarrollo de Software (ADSO) | Ficha: 3413988-ADSO
 * 
 * Implementa la especificación oficial de Liquibase:
 *  - Gestión atómica de bloqueos mediante DATABASECHANGELOGLOCK
 *  - Registro de auditoría y sumas de verificación MD5 en DATABASECHANGELOG
 *  - Soporte de comandos: update, status, rollback y reset
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../config/config.php';

class LiquibaseService {

    private ?PDO $pdo = null;
    private string $changelogFile = 'db/changelog/db.changelog-master.xml';
    private string $liquibaseVersion = '4.27.0';

    public function __construct(?PDO $pdo = null) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            $this->connect();
        }
    }

    /**
     * Conexión a la base de datos MySQL / MariaDB (creando la base de datos si no existe)
     */
    private function connect(): void {
        $dsnInitial = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $initialPdo = new PDO($dsnInitial, DB_USER, DB_PASS, $options);
            $initialPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            
            $dsnWithDb = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
            $this->pdo = new PDO($dsnWithDb, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->pdo = null;
            throw new RuntimeException("Error de conexión Liquibase con MySQL: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Inicializa las tablas estándar de control de Liquibase
     */
    public function ensureLiquibaseTables(): void {
        if (!$this->pdo) {
            throw new RuntimeException("No hay conexión activa a la base de datos.");
        }

        // 1. Tabla de Bloqueo: DATABASECHANGELOGLOCK
        $sqlLock = "
        CREATE TABLE IF NOT EXISTS `DATABASECHANGELOGLOCK` (
            `ID` INT NOT NULL,
            `LOCKED` TINYINT(1) NOT NULL,
            `LOCKGRANTED` DATETIME DEFAULT NULL,
            `LOCKEDBY` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->pdo->exec($sqlLock);

        // Registro inicial de bloqueo (ID = 1)
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM `DATABASECHANGELOGLOCK` WHERE `ID` = 1");
        if ((int)$stmt->fetchColumn() === 0) {
            $this->pdo->exec("INSERT INTO `DATABASECHANGELOGLOCK` (`ID`, `LOCKED`, `LOCKGRANTED`, `LOCKEDBY`) VALUES (1, 0, NULL, NULL);");
        }

        // 2. Tabla de Historial: DATABASECHANGELOG
        $sqlChangelog = "
        CREATE TABLE IF NOT EXISTS `DATABASECHANGELOG` (
            `ID` VARCHAR(255) NOT NULL,
            `AUTHOR` VARCHAR(255) NOT NULL,
            `FILENAME` VARCHAR(255) NOT NULL,
            `DATEEXECUTED` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `ORDEREXECUTED` INT NOT NULL,
            `EXECTYPE` VARCHAR(10) NOT NULL,
            `MD5SUM` VARCHAR(35) DEFAULT NULL,
            `DESCRIPTION` VARCHAR(255) DEFAULT NULL,
            `COMMENTS` VARCHAR(255) DEFAULT NULL,
            `TAG` VARCHAR(255) DEFAULT NULL,
            `LIQUIBASE` VARCHAR(20) DEFAULT '{$this->liquibaseVersion}',
            `CONTEXTS` VARCHAR(255) DEFAULT NULL,
            `LABELS` VARCHAR(255) DEFAULT NULL,
            `DEPLOYMENT_ID` VARCHAR(10) DEFAULT NULL,
            PRIMARY KEY (`ID`, `AUTHOR`, `FILENAME`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->pdo->exec($sqlChangelog);
    }

    /**
     * Adquiere el bloqueo de migración
     */
    private function acquireLock(): void {
        $host = gethostname() ?: 'localhost';
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("UPDATE `DATABASECHANGELOGLOCK` SET `LOCKED` = 1, `LOCKGRANTED` = ?, `LOCKEDBY` = ? WHERE `ID` = 1 AND `LOCKED` = 0");
        $stmt->execute([$now, $host]);
        if ($stmt->rowCount() === 0) {
            // Verificar si ya está bloqueado
            $lockInfo = $this->pdo->query("SELECT * FROM `DATABASECHANGELOGLOCK` WHERE `ID` = 1")->fetch();
            if ($lockInfo && $lockInfo['LOCKED'] == 1) {
                throw new RuntimeException("Liquibase está bloqueado actualmente por: " . ($lockInfo['LOCKEDBY'] ?? 'otro proceso'));
            }
        }
    }

    /**
     * Libera el bloqueo de migración
     */
    private function releaseLock(): void {
        if ($this->pdo) {
            $this->pdo->exec("UPDATE `DATABASECHANGELOGLOCK` SET `LOCKED` = 0, `LOCKGRANTED` = NULL, `LOCKEDBY` = NULL WHERE `ID` = 1");
        }
    }

    /**
     * Definición de ChangeSets canónicos del proyecto MAPAF
     */
    public function getCanonicalChangeSets(): array {
        return [
            [
                'id'          => '001-create-table-roles',
                'author'      => 'maria-conde',
                'filename'    => 'db/changelog/changesets/001-create-table-roles.xml',
                'description' => 'createTable tableName=roles',
                'comments'    => 'Matriz de Control de Acceso Basado en Roles (RBAC)',
                'up'          => "
                    CREATE TABLE IF NOT EXISTS `roles` (
                      `id` VARCHAR(50) NOT NULL,
                      `nombre` VARCHAR(100) NOT NULL,
                      `codigo` VARCHAR(50) NOT NULL UNIQUE,
                      `descripcion` TEXT DEFAULT NULL,
                      `color` VARCHAR(20) DEFAULT 'purple',
                      `es_sistema` TINYINT(1) DEFAULT 0,
                      `permisos` JSON NOT NULL,
                      `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ",
                'down'        => "DROP TABLE IF EXISTS `roles`;"
            ],
            [
                'id'          => '002-create-table-usuarios',
                'author'      => 'maria-conde',
                'filename'    => 'db/changelog/changesets/002-create-table-usuarios.xml',
                'description' => 'createTable tableName=usuarios',
                'comments'    => 'Cuentas de Operadores y Personal Técnico de Planta',
                'up'          => "
                    CREATE TABLE IF NOT EXISTS `usuarios` (
                      `id` VARCHAR(50) NOT NULL,
                      `codigo_empleado` VARCHAR(50) NOT NULL UNIQUE,
                      `nombre_completo` VARCHAR(150) NOT NULL,
                      `email` VARCHAR(150) NOT NULL UNIQUE,
                      `password_hash` VARCHAR(255) NOT NULL,
                      `rol_id` VARCHAR(50) NOT NULL,
                      `sector_asignado` VARCHAR(100) NOT NULL,
                      `estado` ENUM('ACTIVO','EN TURNO','SUSPENDIDO') DEFAULT 'ACTIVO',
                      `ultimo_acceso` DATETIME DEFAULT CURRENT_TIMESTAMP,
                      `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`),
                      CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ",
                'down'        => "DROP TABLE IF EXISTS `usuarios`;"
            ],
            [
                'id'          => '003-create-table-pozos',
                'author'      => 'maria-conde',
                'filename'    => 'db/changelog/changesets/003-create-table-pozos.xml',
                'description' => 'createTable tableName=pozos',
                'comments'    => 'Cabezales de Producción API 6A y Actuadores Choke / SSV',
                'up'          => "
                    CREATE TABLE IF NOT EXISTS `pozos` (
                      `id` VARCHAR(50) NOT NULL,
                      `nombre` VARCHAR(100) NOT NULL,
                      `sector` VARCHAR(100) NOT NULL,
                      `estado` ENUM('EN LÍNEA','ESTRANGULADO','DISPARADO','MANTENIMIENTO') DEFAULT 'EN LÍNEA',
                      `presion_tubing` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                      `presion_casing` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                      `caudal_produccion` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                      `temperatura` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                      `apertura_choke` DECIMAL(5,2) NOT NULL DEFAULT 40.00,
                      `objetivo_choke` DECIMAL(5,2) NOT NULL DEFAULT 40.00,
                      `frecuencia_esp` DECIMAL(5,2) NOT NULL DEFAULT 58.00,
                      `vibracion_esp` DECIMAL(6,3) NOT NULL DEFAULT 1.800,
                      `valvula_ssv_abierta` TINYINT(1) DEFAULT 1,
                      `purga_anular_abierta` TINYINT(1) DEFAULT 0,
                      `inyeccion_quimica` TINYINT(1) DEFAULT 1,
                      `arena_acustica_ppm` DECIMAL(8,2) DEFAULT 4.20,
                      `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ",
                'down'        => "DROP TABLE IF EXISTS `pozos`;"
            ],
            [
                'id'          => '004-create-table-telemetria-metricas',
                'author'      => 'maria-conde',
                'filename'    => 'db/changelog/changesets/004-create-table-telemetria-metricas.xml',
                'description' => 'createTable tableName=telemetria_metricas',
                'comments'    => 'Sensores Físicos y Monitoreo de Variables de Proceso',
                'up'          => "
                    CREATE TABLE IF NOT EXISTS `telemetria_metricas` (
                      `id` VARCHAR(50) NOT NULL,
                      `tag` VARCHAR(50) NOT NULL UNIQUE,
                      `etiqueta` VARCHAR(150) NOT NULL,
                      `valor` DECIMAL(12,2) NOT NULL,
                      `unidad` VARCHAR(20) NOT NULL,
                      `valor_objetivo` DECIMAL(12,2) NOT NULL,
                      `trip_bajo` DECIMAL(12,2) NOT NULL,
                      `alarma_alta` DECIMAL(12,2) NOT NULL,
                      `trip_alto` DECIMAL(12,2) NOT NULL,
                      `tendencia` ENUM('rising','falling','stable') DEFAULT 'stable',
                      `severidad` ENUM('nominal','advisory','warning','critical') DEFAULT 'nominal',
                      `historial` JSON NOT NULL,
                      `pozo_id` VARCHAR(50) DEFAULT NULL,
                      `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`),
                      CONSTRAINT `fk_telemetria_pozo` FOREIGN KEY (`pozo_id`) REFERENCES `pozos` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ",
                'down'        => "DROP TABLE IF EXISTS `telemetria_metricas`;"
            ],
            [
                'id'          => '005-create-table-alarmas',
                'author'      => 'maria-conde',
                'filename'    => 'db/changelog/changesets/005-create-table-alarmas.xml',
                'description' => 'createTable tableName=alarmas',
                'comments'    => 'Registro y Acuse Trazable de Alarmas ANSI/ISA-18.2',
                'up'          => "
                    CREATE TABLE IF NOT EXISTS `alarmas` (
                      `id` VARCHAR(50) NOT NULL,
                      `timestamp_evento` VARCHAR(50) NOT NULL,
                      `tag` VARCHAR(50) NOT NULL,
                      `pozo_id` VARCHAR(50) DEFAULT NULL,
                      `subsistema` VARCHAR(100) NOT NULL,
                      `descripcion` TEXT NOT NULL,
                      `valor` VARCHAR(50) NOT NULL,
                      `umbral` VARCHAR(50) NOT NULL,
                      `severidad` ENUM('nominal','advisory','warning','critical') DEFAULT 'warning',
                      `reconocida` TINYINT(1) DEFAULT 0,
                      `reconocida_por` VARCHAR(100) DEFAULT NULL,
                      `ack_timestamp` VARCHAR(50) DEFAULT NULL,
                      `causa_disparo` TEXT DEFAULT NULL,
                      `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`),
                      CONSTRAINT `fk_alarmas_pozo` FOREIGN KEY (`pozo_id`) REFERENCES `pozos` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ",
                'down'        => "DROP TABLE IF EXISTS `alarmas`;"
            ],
            [
                'id'          => '006-create-table-bitacora-auditoria',
                'author'      => 'maria-conde',
                'filename'    => 'db/changelog/changesets/006-create-table-bitacora-auditoria.xml',
                'description' => 'createTable tableName=bitacora_auditoria',
                'comments'    => 'Trazabilidad Forense Inmutable 21 CFR Part 11',
                'up'          => "
                    CREATE TABLE IF NOT EXISTS `bitacora_auditoria` (
                      `id` VARCHAR(50) NOT NULL,
                      `timestamp_log` VARCHAR(50) NOT NULL,
                      `codigo_usuario` VARCHAR(50) NOT NULL,
                      `nombre_usuario` VARCHAR(150) NOT NULL,
                      `nombre_rol` VARCHAR(100) NOT NULL,
                      `accion` VARCHAR(255) NOT NULL,
                      `categoria` ENUM('SEGURIDAD','MANIOBRA','ALARMA','CONFIGURACIÓN') NOT NULL,
                      `estado` ENUM('EXITOSO','RECHAZADO','ADVERTENCIA') NOT NULL,
                      `detalles` TEXT NOT NULL,
                      `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ",
                'down'        => "DROP TABLE IF EXISTS `bitacora_auditoria`;"
            ],
            [
                'id'          => '007-create-table-configuracion-sistema',
                'author'      => 'maria-conde',
                'filename'    => 'db/changelog/changesets/007-create-table-configuracion-sistema.xml',
                'description' => 'createTable tableName=configuracion_sistema',
                'comments'    => 'Parámetros Globales y Estado SIS ESD-1',
                'up'          => "
                    CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
                      `clave` VARCHAR(50) NOT NULL,
                      `valor` TEXT NOT NULL,
                      `descripcion` VARCHAR(255) DEFAULT NULL,
                      `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      PRIMARY KEY (`clave`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ",
                'down'        => "DROP TABLE IF EXISTS `configuracion_sistema`;"
            ],
            [
                'id'          => '008-seed-initial-data',
                'author'      => 'maria-conde',
                'filename'    => 'db/changelog/changesets/008-seed-initial-data.xml',
                'description' => 'insertData roles, usuarios, pozos, telemetria, alarmas, auditoria, config',
                'comments'    => 'Población de datos iniciales requeridos para operación SCADA',
                'up'          => "
                    INSERT INTO `roles` (`id`, `nombre`, `codigo`, `descripcion`, `color`, `es_sistema`, `permisos`) VALUES
                    ('role-admin', 'Administrador del Sistema', 'SYS-ADMIN', 'Control total de ciberseguridad, gestión de usuarios, enclavamientos y configuración de planta.', 'rose', 1, '[\"view_telemetry\", \"control_valves\", \"control_choke\", \"execute_esd\", \"reset_esd\", \"ack_alarms\", \"export_historian\", \"manage_users\"]'),
                    ('role-engineer', 'Ingeniero de Operaciones', 'OPS-ENG', 'Ajuste de consignas de proceso, curvas de producción, estranguladores y bypass bajo protocolo.', 'purple', 0, '[\"view_telemetry\", \"control_valves\", \"control_choke\", \"execute_esd\", \"reset_esd\", \"ack_alarms\", \"export_historian\"]'),
                    ('role-operator', 'Operador de Consola', 'CONSOLE-OP', 'Monitoreo continuo de cabezales, maniobra de choke y reconocimiento de alarmas operacionales.', 'emerald', 0, '[\"view_telemetry\", \"control_choke\", \"ack_alarms\"]'),
                    ('role-supervisor', 'Supervisor de Turno', 'SHIFT-SUPV', 'Supervisión de sala de control, autorización de maniobras críticas y acuse de alarmas ISA-18.2.', 'amber', 0, '[\"view_telemetry\", \"control_valves\", \"execute_esd\", \"ack_alarms\", \"export_historian\"]'),
                    ('role-viewer', 'Auditor / Observador', 'AUDIT-VIEW', 'Acceso exclusivo de sólo lectura para fiscalización de producción y reportes históricos.', 'sky', 0, '[\"view_telemetry\", \"export_historian\"]')
                    ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

                    INSERT INTO `usuarios` (`id`, `codigo_empleado`, `nombre_completo`, `email`, `password_hash`, `rol_id`, `sector_asignado`, `estado`) VALUES
                    ('usr-001', 'EMP-8812', 'María José Conde Dussan', 'm.conde@mapaf.senacontrol.co', '\$2y\$10\$wT8yZ00xX/G3.qD...adminhash', 'role-admin', 'Planta Central Huila / RTOC', 'EN TURNO'),
                    ('usr-002', 'EMP-8813', 'Hernann Thomas Manchola', 'h.manchola@mapaf.senacontrol.co', '\$2y\$10\$wT8yZ00xX/G3.qD...adminhash', 'role-engineer', 'Pad 04 & 09 Upstream', 'EN TURNO'),
                    ('usr-003', 'EMP-8814', 'Emilin Tatiana Vargas', 'e.vargas@mapaf.senacontrol.co', '\$2y\$10\$wT8yZ00xX/G3.qD...adminhash', 'role-supervisor', 'Sala de Control y Despacho', 'EN TURNO'),
                    ('usr-004', 'EMP-5521', 'Carlos Méndez Arango', 'c.mendez@mapaf.senacontrol.co', '\$2y\$10\$wT8yZ00xX/G3.qD...userhash', 'role-operator', 'Batería de Separación 02', 'ACTIVO'),
                    ('usr-005', 'EMP-1109', 'Javier Humberto Pinto Díaz', 'j.pinto@sena.edu.co', '\$2y\$10\$wT8yZ00xX/G3.qD...userhash', 'role-admin', 'Auditoría Académica ADSO', 'ACTIVO')
                    ON DUPLICATE KEY UPDATE `nombre_completo` = VALUES(`nombre_completo`);

                    INSERT INTO `pozos` (`id`, `nombre`, `sector`, `estado`, `presion_tubing`, `presion_casing`, `caudal_produccion`, `temperatura`, `apertura_choke`, `objetivo_choke`, `frecuencia_esp`, `vibracion_esp`, `valvula_ssv_abierta`, `purga_anular_abierta`, `inyeccion_quimica`, `arena_acustica_ppm`) VALUES
                    ('well-a12', 'POZO ALFA-12', 'PAD 04-A', 'EN LÍNEA', 1842.50, 642.00, 4210.00, 78.40, 42.80, 42.80, 58.20, 1.840, 1, 0, 1, 4.20),
                    ('well-b04', 'POZO BRAVO-04', 'PAD 04-B', 'EN LÍNEA', 2050.00, 710.00, 4890.00, 81.20, 38.50, 38.50, 60.00, 2.150, 1, 0, 1, 6.80),
                    ('well-c09', 'POZO CHARLIE-09', 'PAD 09-NORTH', 'ESTRANGULADO', 1620.00, 580.00, 2980.00, 74.60, 22.00, 22.00, 52.00, 1.450, 1, 0, 1, 2.10),
                    ('well-d02', 'POZO DELTA-02', 'PAD 09-SOUTH', 'MANTENIMIENTO', 420.00, 150.00, 0.00, 32.10, 0.00, 0.00, 0.00, 0.020, 0, 1, 0, 0.00)
                    ON DUPLICATE KEY UPDATE `presion_tubing` = VALUES(`presion_tubing`);

                    INSERT INTO `telemetria_metricas` (`id`, `tag`, `etiqueta`, `valor`, `unidad`, `valor_objetivo`, `trip_bajo`, `alarma_alta`, `trip_alto`, `tendencia`, `severidad`, `historial`, `pozo_id`) VALUES
                    ('pt-101', 'WHTP-101A', 'PRESIÓN DE CABEZAL (TUBING)', 1842.50, 'PSI', 1800.00, 850.00, 2100.00, 2350.00, 'stable', 'nominal', '[1810, 1822, 1835, 1830, 1845, 1840, 1838, 1842.5]', 'well-a12'),
                    ('pt-102', 'CASING-101B', 'PRESIÓN ANULAR (CASING)', 642.00, 'PSI', 600.00, 100.00, 800.00, 950.00, 'rising', 'advisory', '[610, 618, 622, 630, 635, 638, 640, 642]', 'well-a12'),
                    ('tt-301', 'TEMP-FLOW-301', 'TEMPERATURA DE MANIFOLD', 78.40, '°C', 75.00, 20.00, 92.00, 105.00, 'rising', 'nominal', '[75.2, 75.8, 76.5, 77.0, 77.5, 78.0, 78.2, 78.4]', 'well-a12'),
                    ('ft-201', 'MFM-201', 'CAUDAL BRUTO DE PRODUCCIÓN', 14820.00, 'BPD', 15000.00, 4000.00, 16500.00, 18000.00, 'stable', 'nominal', '[14750, 14780, 14810, 14800, 14830, 14815, 14820, 14820]', 'well-b04'),
                    ('esp-01', 'ESP-FREQ-01', 'FRECUENCIA BOMBA ESP', 58.20, 'Hz', 60.00, 35.00, 63.50, 66.00, 'stable', 'nominal', '[57.8, 58.0, 58.1, 58.2, 58.0, 58.2, 58.1, 58.2]', 'well-a12'),
                    ('gor-402', 'GOR-ANALYZER', 'RELACIÓN GAS/PETRÓLEO (GOR)', 462.80, 'SCF/BBL', 450.00, 200.00, 550.00, 680.00, 'falling', 'nominal', '[475, 471, 468, 465, 464, 463, 463.5, 462.8]', 'well-c09')
                    ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

                    INSERT INTO `alarmas` (`id`, `timestamp_evento`, `tag`, `pozo_id`, `subsistema`, `descripcion`, `valor`, `umbral`, `severidad`, `reconocida`, `reconocida_por`, `ack_timestamp`, `causa_disparo`) VALUES
                    ('alm-101', '14:28:12', 'CASING-101B', 'well-a12', 'PAD 04-A', 'Presión Anular Casing en ascenso acelerado', '642.0 PSI', '> 600.0 PSI Advisory', 'advisory', 0, NULL, NULL, 'Restricción en flujo de retorno'),
                    ('alm-102', '13:52:04', 'VIB-ESP-B04', 'well-b04', 'BOMBA ESP', 'Vibración axial de bomba ESP fuera de tolerancia ISO 10816', '2.15 mm/s', '> 2.0 mm/s Warning', 'warning', 0, NULL, NULL, 'Posible desgaste en rodamiento inferior'),
                    ('alm-103', '11:15:30', 'ESD-LOOP-9', 'well-a12', 'SEGURIDAD RTU', 'Test de bucle de presión neumática completado exitosamente', '120 PSI', '120 PSI Nominal', 'nominal', 1, 'María José Conde', '11:16:05', 'Prueba de rutina programada')
                    ON DUPLICATE KEY UPDATE `descripcion` = VALUES(`descripcion`);

                    INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES
                    ('aud-001', '14:55:00', 'EMP-8812', 'María José Conde Dussan', 'Administrador del Sistema', 'Inicialización de Base de Datos mediante Liquibase Migrations', 'CONFIGURACIÓN', 'EXITOSO', 'Migraciones ejecutadas y registradas en DATABASECHANGELOG.'),
                    ('aud-002', '14:35:12', 'EMP-8812', 'María José Conde Dussan', 'Administrador del Sistema', 'Ajuste de Estrangulador Pozo ALFA-12', 'MANIOBRA', 'EXITOSO', 'Apertura de Choke ajustada a 42.8% (+2.5% de incremento sostenido).'),
                    ('aud-003', '13:50:00', 'EMP-8813', 'Hernann Thomas Manchola', 'Ingeniero de Operaciones', 'Sincronización de Umbrales de Telemetría', 'CONFIGURACIÓN', 'EXITOSO', 'Verificación de consignas para pozo BRAVO-04 y caudalímetro Coriolis.')
                    ON DUPLICATE KEY UPDATE `detalles` = VALUES(`detalles`);

                    INSERT INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`) VALUES
                    ('plant_name', 'MAPAF RTOC - Estación Central de Telemetría', 'Nombre de la instalación'),
                    ('sena_ficha', '3413988-ADSO', 'Ficha del programa SENA ADSO'),
                    ('production_rate', '14820', 'Tasa bruta de producción en BPD'),
                    ('flow_state', 'nominal', 'Estado del flujo general: nominal, throttled, tripped'),
                    ('esd_triggered', '0', 'Estado de Parada de Emergencia ESD-1 (0=Normal, 1=Disparado)'),
                    ('esd_trigger_time', '', 'Hora del último disparo ESD')
                    ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);
                ",
                'down'        => "
                    DELETE FROM `configuracion_sistema`;
                    DELETE FROM `bitacora_auditoria`;
                    DELETE FROM `alarmas`;
                    DELETE FROM `telemetria_metricas`;
                    DELETE FROM `pozos`;
                    DELETE FROM `usuarios`;
                    DELETE FROM `roles`;
                "
            ]
        ];
    }

    /**
     * Calcula el checksum MD5 canónico para un changeSet estilo Liquibase
     */
    private function calculateChecksum(array $cs): string {
        $content = trim($cs['id'] . ':' . $cs['author'] . ':' . $cs['up']);
        return '8:' . md5($content);
    }

    /**
     * Obtiene el estado actual de las migraciones de Liquibase
     */
    public function getStatus(): array {
        $this->ensureLiquibaseTables();

        $canonical = $this->getCanonicalChangeSets();
        
        $executedRows = $this->pdo->query("
            SELECT `ID`, `AUTHOR`, `FILENAME`, `DATEEXECUTED`, `ORDEREXECUTED`, `EXECTYPE`, `MD5SUM`, `DESCRIPTION`
            FROM `DATABASECHANGELOG`
            ORDER BY `ORDEREXECUTED` ASC
        ")->fetchAll();

        $executedMap = [];
        foreach ($executedRows as $row) {
            $executedMap[$row['ID']] = $row;
        }

        $statusList = [];
        $appliedCount = 0;
        $pendingCount = 0;

        foreach ($canonical as $cs) {
            $isApplied = isset($executedMap[$cs['id']]);
            if ($isApplied) {
                $appliedCount++;
                $statusList[] = [
                    'id'           => $cs['id'],
                    'author'       => $cs['author'],
                    'status'       => 'APLICADO',
                    'dateExecuted' => $executedMap[$cs['id']]['DATEEXECUTED'],
                    'order'        => (int)$executedMap[$cs['id']]['ORDEREXECUTED'],
                    'md5'          => $executedMap[$cs['id']]['MD5SUM'],
                    'description'  => $cs['description'],
                    'comments'     => $cs['comments']
                ];
            } else {
                $pendingCount++;
                $statusList[] = [
                    'id'           => $cs['id'],
                    'author'       => $cs['author'],
                    'status'       => 'PENDIENTE',
                    'dateExecuted' => null,
                    'order'        => null,
                    'md5'          => $this->calculateChecksum($cs),
                    'description'  => $cs['description'],
                    'comments'     => $cs['comments']
                ];
            }
        }

        // Obtener estado del candado
        $lockStmt = $this->pdo->query("SELECT * FROM `DATABASECHANGELOGLOCK` WHERE `ID` = 1");
        $lockData = $lockStmt->fetch();

        return [
            'success'          => true,
            'database'         => DB_NAME,
            'changelogFile'    => $this->changelogFile,
            'liquibaseVersion' => $this->liquibaseVersion,
            'locked'           => (bool)($lockData['LOCKED'] ?? false),
            'lockedBy'         => $lockData['LOCKEDBY'] ?? null,
            'lockGranted'      => $lockData['LOCKGRANTED'] ?? null,
            'totalChangesets'  => count($canonical),
            'appliedCount'     => $appliedCount,
            'pendingCount'     => $pendingCount,
            'isUpToDate'       => ($pendingCount === 0),
            'changesets'       => $statusList
        ];
    }

    /**
     * Ejecuta todas las migraciones pendientes (update)
     */
    public function update(): array {
        $this->ensureLiquibaseTables();
        $this->acquireLock();

        $deploymentId = substr(md5(uniqid((string)mt_rand(), true)), 0, 10);
        $canonical = $this->getCanonicalChangeSets();

        // Obtener el orden máximo actual
        $orderStmt = $this->pdo->query("SELECT COALESCE(MAX(`ORDEREXECUTED`), 0) FROM `DATABASECHANGELOG`");
        $currentOrder = (int)$orderStmt->fetchColumn();

        // Obtener IDs ya ejecutados
        $executedIds = $this->pdo->query("SELECT `ID` FROM `DATABASECHANGELOG`")->fetchAll(PDO::FETCH_COLUMN);

        $appliedThisRun = [];

        try {
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

            foreach ($canonical as $cs) {
                if (in_array($cs['id'], $executedIds, true)) {
                    continue;
                }

                $currentOrder++;
                $checksum = $this->calculateChecksum($cs);

                // Ejecutar DDL / DML
                $this->pdo->exec($cs['up']);

                // Registrar en DATABASECHANGELOG
                $insertLog = $this->pdo->prepare("
                    INSERT INTO `DATABASECHANGELOG` (
                        `ID`, `AUTHOR`, `FILENAME`, `DATEEXECUTED`, `ORDEREXECUTED`, 
                        `EXECTYPE`, `MD5SUM`, `DESCRIPTION`, `COMMENTS`, `LIQUIBASE`, `DEPLOYMENT_ID`
                    ) VALUES (?, ?, ?, NOW(), ?, 'EXECUTED', ?, ?, ?, ?, ?)
                ");

                $insertLog->execute([
                    $cs['id'],
                    $cs['author'],
                    $cs['filename'],
                    $currentOrder,
                    $checksum,
                    $cs['description'],
                    $cs['comments'],
                    $this->liquibaseVersion,
                    $deploymentId
                ]);

                $appliedThisRun[] = [
                    'id'          => $cs['id'],
                    'author'      => $cs['author'],
                    'order'       => $currentOrder,
                    'checksum'    => $checksum,
                    'description' => $cs['description']
                ];
            }

            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $this->releaseLock();

        } catch (Throwable $e) {
            $this->releaseLock();
            throw new RuntimeException("Error ejecutando ChangeSet Liquibase: " . $e->getMessage(), 0, $e);
        }

        return [
            'success'         => true,
            'message'         => 'Migraciones de Liquibase ejecutadas exitosamente.',
            'deploymentId'    => $deploymentId,
            'appliedCount'    => count($appliedThisRun),
            'appliedChanges'  => $appliedThisRun,
            'totalChangesets' => count($canonical),
            'isUpToDate'      => true
        ];
    }

    /**
     * Revertir las últimas N migraciones (rollback)
     */
    public function rollback(int $count = 1): array {
        $this->ensureLiquibaseTables();
        $this->acquireLock();

        $canonical = $this->getCanonicalChangeSets();
        $canonicalById = [];
        foreach ($canonical as $cs) {
            $canonicalById[$cs['id']] = $cs;
        }

        // Obtener los últimos N changeSets ejecutados
        $stmt = $this->pdo->prepare("
            SELECT `ID`, `AUTHOR`, `FILENAME`, `ORDEREXECUTED`
            FROM `DATABASECHANGELOG`
            ORDER BY `ORDEREXECUTED` DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $count, PDO::PARAM_INT);
        $stmt->execute();
        $toRollback = $stmt->fetchAll();

        $rolledBack = [];

        try {
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

            foreach ($toRollback as $row) {
                $csId = $row['ID'];
                if (isset($canonicalById[$csId]['down'])) {
                    $this->pdo->exec($canonicalById[$csId]['down']);
                }

                // Eliminar de DATABASECHANGELOG
                $del = $this->pdo->prepare("DELETE FROM `DATABASECHANGELOG` WHERE `ID` = ? AND `AUTHOR` = ?");
                $del->execute([$row['ID'], $row['AUTHOR']]);

                $rolledBack[] = $csId;
            }

            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $this->releaseLock();

        } catch (Throwable $e) {
            $this->releaseLock();
            throw new RuntimeException("Error durante el Rollback de Liquibase: " . $e->getMessage(), 0, $e);
        }

        return [
            'success'        => true,
            'message'        => 'Rollback de Liquibase completado.',
            'count'          => count($rolledBack),
            'rolledBackIds'  => $rolledBack
        ];
    }

    /**
     * Reconstruye la base de datos limpia desde cero usando Liquibase
     */
    public function resetAndMount(): array {
        $dsnInitial = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        $initialPdo = new PDO($dsnInitial, DB_USER, DB_PASS, $options);
        $initialPdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $initialPdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`;");
        $initialPdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $initialPdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->connect();

        // Ejecutar update
        return $this->update();
    }
}

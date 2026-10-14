-- ============================================================================
-- PROYECTO MAPAF - SISTEMA SCADA TÁCTICO & TELEMETRÍA IIOT
-- SENA - CENTRO DE LA INDUSTRIA, LA EMPRESA Y LOS SERVICIOS (REGIONAL HUILA)
-- BASE DE DATOS: MySQL / MariaDB (XAMPP)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `mapaf_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mapaf_db`;

-- ----------------------------------------------------------------------------
-- 1. TABLA: configuracion_sistema
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
  `clave` VARCHAR(50) NOT NULL PRIMARY KEY,
  `valor` TEXT NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`) VALUES
('esd_triggered', '0', 'Estado de Parada de Emergencia ESD-1 (0=Normal, 1=Disparado)'),
('esd_trigger_time', '', 'Hora del último disparo ESD'),
('flow_state', 'nominal', 'Estado del flujo general: nominal, throttled, tripped'),
('production_rate', '14820', 'Tasa bruta de producción en BPD'),
('plant_name', 'MAPAF RTOC - Estación Central de Telemetría', 'Nombre de la instalación'),
('sena_ficha', '3413988-ADSO', 'Ficha del programa SENA ADSO')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

-- ----------------------------------------------------------------------------
-- 2. TABLA: roles
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `descripcion` TEXT NULL,
  `color` VARCHAR(20) DEFAULT 'purple',
  `es_sistema` TINYINT(1) DEFAULT 0,
  `permisos` JSON NOT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `nombre`, `codigo`, `descripcion`, `color`, `es_sistema`, `permisos`) VALUES
('role-admin', 'Administrador del Sistema', 'SYS-ADMIN', 'Control total de configuración, ciberseguridad industrial, gestión de usuarios y parámetros globales.', 'rose', 1, 
 JSON_ARRAY('view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports', 'bypass_interlocks', 'manage_roles_users')),

('role-engineer', 'Ingeniero de Operaciones', 'OPS-ENG', 'Ajuste de consignas de proceso, curvas de producción, estranguladores y bypass bajo protocolo.', 'purple', 0,
 JSON_ARRAY('view_telemetry', 'control_valves', 'control_choke', 'execute_esd', 'reset_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports')),

('role-supervisor', 'Supervisor de Turno', 'SHIFT-SUPV', 'Supervisión de sala de control, autorización de maniobras críticas y reconocimiento de alarmas ISA-18.2.', 'amber', 0,
 JSON_ARRAY('view_telemetry', 'control_valves', 'execute_esd', 'ack_alarms', 'ack_all_alarms', 'export_historian', 'control_cctv', 'generate_reports')),

('role-operator', 'Operador de Consola', 'CONSOLE-OP', 'Monitoreo continuo de cabezales, manifold de recolección y reconocimiento de alarmas operacionales.', 'emerald', 0,
 JSON_ARRAY('view_telemetry', 'control_choke', 'ack_alarms', 'control_cctv')),

('role-viewer', 'Auditor / Observador Externo', 'AUDIT-VIEW', 'Acceso exclusivo de sólo lectura para fiscalización de producción y reportes históricos.', 'sky', 0,
 JSON_ARRAY('view_telemetry', 'export_historian', 'generate_reports'))
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `permisos` = VALUES(`permisos`);

-- ----------------------------------------------------------------------------
-- 3. TABLA: usuarios
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `codigo_empleado` VARCHAR(50) NOT NULL UNIQUE,
  `nombre_completo` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `rol_id` VARCHAR(50) NOT NULL,
  `sector_asignado` VARCHAR(100) NOT NULL,
  `estado` ENUM('ACTIVO', 'EN TURNO', 'SUSPENDIDO') DEFAULT 'ACTIVO',
  `ultimo_acceso` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`id`, `codigo_empleado`, `nombre_completo`, `email`, `password_hash`, `rol_id`, `sector_asignado`, `estado`, `ultimo_acceso`) VALUES
('usr-001', 'EMP-8812', 'María José Conde Dussan', 'm.conde@mapaf.senacontrol.co', '$2y$10$wT8yZ00xX/G3.qD...adminhash', 'role-admin', 'Planta Central Huila / RTOC', 'EN TURNO', NOW()),
('usr-002', 'EMP-8813', 'Hernann Thomas Manchola', 'h.manchola@mapaf.senacontrol.co', '$2y$10$wT8yZ00xX/G3.qD...adminhash', 'role-engineer', 'Pad 04 & 09 Upstream', 'EN TURNO', NOW()),
('usr-003', 'EMP-8814', 'Emilin Tatiana Vargas', 'e.vargas@mapaf.senacontrol.co', '$2y$10$wT8yZ00xX/G3.qD...adminhash', 'role-supervisor', 'Sala de Control y Despacho', 'EN TURNO', NOW()),
('usr-004', 'EMP-5521', 'Carlos Méndez Arango', 'c.mendez@mapaf.senacontrol.co', '$2y$10$wT8yZ00xX/G3.qD...userhash', 'role-operator', 'Batería de Separación 02', 'ACTIVO', NOW()),
('usr-005', 'EMP-1109', 'Javier Humberto Pinto', 'j.pinto@sena.edu.co', '$2y$10$wT8yZ00xX/G3.qD...userhash', 'role-admin', 'Auditoría Académica ADSO', 'ACTIVO', NOW())
ON DUPLICATE KEY UPDATE `nombre_completo` = VALUES(`nombre_completo`), `rol_id` = VALUES(`rol_id`);

-- ----------------------------------------------------------------------------
-- 4. TABLA: telemetria_metricas
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `telemetria_metricas` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `tag` VARCHAR(50) NOT NULL UNIQUE,
  `etiqueta` VARCHAR(150) NOT NULL,
  `valor` DECIMAL(12,2) NOT NULL,
  `unidad` VARCHAR(20) NOT NULL,
  `valor_objetivo` DECIMAL(12,2) NOT NULL,
  `trip_bajo` DECIMAL(12,2) NOT NULL,
  `alarma_alta` DECIMAL(12,2) NOT NULL,
  `trip_alto` DECIMAL(12,2) NOT NULL,
  `tendencia` ENUM('rising', 'falling', 'stable') DEFAULT 'stable',
  `severidad` ENUM('nominal', 'advisory', 'warning', 'critical') DEFAULT 'nominal',
  `historial` JSON NOT NULL,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `telemetria_metricas` (`id`, `tag`, `etiqueta`, `valor`, `unidad`, `valor_objetivo`, `trip_bajo`, `alarma_alta`, `trip_alto`, `tendencia`, `severidad`, `historial`) VALUES
('pt-101', 'WHTP-101A', 'PRESIÓN DE CABEZAL (TUBING)', 1842.50, 'PSI', 1800.00, 850.00, 2100.00, 2350.00, 'stable', 'nominal', JSON_ARRAY(1810, 1822, 1835, 1830, 1845, 1840, 1838, 1842.5)),
('pt-102', 'CASING-101B', 'PRESIÓN ANULAR (CASING)', 642.00, 'PSI', 600.00, 100.00, 800.00, 950.00, 'rising', 'nominal', JSON_ARRAY(610, 618, 622, 630, 635, 638, 640, 642)),
('ft-201', 'MFM-201', 'CAUDAL BRUTO DE PRODUCCIÓN', 14820.00, 'BPD', 15000.00, 4000.00, 16500.00, 18000.00, 'stable', 'nominal', JSON_ARRAY(14750, 14780, 14810, 14800, 14830, 14815, 14820, 14820)),
('tt-301', 'TEMP-FLOW-301', 'TEMPERATURA DE MANIFOLD', 78.40, '°C', 75.00, 20.00, 92.00, 105.00, 'rising', 'nominal', JSON_ARRAY(75.2, 75.8, 76.5, 77.0, 77.5, 78.0, 78.2, 78.4)),
('esp-01', 'ESP-FREQ-01', 'FRECUENCIA BOMBA ESP', 58.20, 'Hz', 60.00, 35.00, 63.50, 66.00, 'stable', 'nominal', JSON_ARRAY(57.8, 58.0, 58.1, 58.2, 58.0, 58.2, 58.1, 58.2)),
('gor-402', 'GOR-ANALYZER', 'RELACIÓN GAS/PETRÓLEO (GOR)', 462.80, 'SCF/BBL', 450.00, 200.00, 550.00, 680.00, 'falling', 'nominal', JSON_ARRAY(475, 471, 468, 465, 464, 463, 463.5, 462.8))
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`), `historial` = VALUES(`historial`);

-- ----------------------------------------------------------------------------
-- 5. TABLA: pozos
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pozos` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `sector` VARCHAR(100) NOT NULL,
  `estado` ENUM('EN LÍNEA', 'ESTRANGULADO', 'DISPARADO', 'MANTENIMIENTO') DEFAULT 'EN LÍNEA',
  `presion_tubing` DECIMAL(10,2) NOT NULL,
  `presion_casing` DECIMAL(10,2) NOT NULL,
  `caudal_produccion` DECIMAL(10,2) NOT NULL,
  `temperatura` DECIMAL(10,2) NOT NULL,
  `apertura_choke` DECIMAL(5,2) NOT NULL DEFAULT 40.0,
  `objetivo_choke` DECIMAL(5,2) NOT NULL DEFAULT 40.0,
  `frecuencia_esp` DECIMAL(5,2) NOT NULL DEFAULT 58.0,
  `vibracion_esp` DECIMAL(6,3) NOT NULL DEFAULT 1.8,
  `valvula_ssv_abierta` TINYINT(1) DEFAULT 1,
  `purga_anular_abierta` TINYINT(1) DEFAULT 0,
  `inyeccion_quimica` TINYINT(1) DEFAULT 1,
  `arena_acustica_ppm` DECIMAL(8,2) DEFAULT 4.2,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pozos` (`id`, `nombre`, `sector`, `estado`, `presion_tubing`, `presion_casing`, `caudal_produccion`, `temperatura`, `apertura_choke`, `objetivo_choke`, `frecuencia_esp`, `vibracion_esp`, `valvula_ssv_abierta`, `purga_anular_abierta`, `inyeccion_quimica`, `arena_acustica_ppm`) VALUES
('well-a12', 'POZO ALFA-12', 'PAD 04-A', 'EN LÍNEA', 1842.50, 642.00, 4210.00, 78.40, 42.80, 42.80, 58.20, 1.84, 1, 0, 1, 4.20),
('well-b04', 'POZO BRAVO-04', 'PAD 04-B', 'EN LÍNEA', 2050.00, 710.00, 4890.00, 81.20, 38.50, 38.50, 60.00, 2.15, 1, 0, 1, 6.80),
('well-c09', 'POZO CHARLIE-09', 'PAD 09-NORTH', 'ESTRANGULADO', 1620.00, 580.00, 2980.00, 74.60, 22.00, 22.00, 52.00, 1.45, 1, 0, 1, 2.10),
('well-d02', 'POZO DELTA-02', 'PAD 09-SOUTH', 'MANTENIMIENTO', 420.00, 150.00, 0.00, 32.10, 0.00, 0.00, 0.00, 0.02, 0, 1, 0, 0.00)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `estado` = VALUES(`estado`);

-- ----------------------------------------------------------------------------
-- 6. TABLA: alarmas
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `alarmas` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `timestamp_evento` VARCHAR(50) NOT NULL,
  `tag` VARCHAR(50) NOT NULL,
  `subsistema` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `valor` VARCHAR(50) NOT NULL,
  `umbral` VARCHAR(50) NOT NULL,
  `severidad` ENUM('nominal', 'advisory', 'warning', 'critical') DEFAULT 'warning',
  `reconocida` TINYINT(1) DEFAULT 0,
  `reconocida_por` VARCHAR(100) NULL,
  `ack_timestamp` VARCHAR(50) NULL,
  `causa_disparo` TEXT NULL,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `alarmas` (`id`, `timestamp_evento`, `tag`, `subsistema`, `descripcion`, `valor`, `umbral`, `severidad`, `reconocida`, `reconocida_por`, `ack_timestamp`, `causa_disparo`) VALUES
('alm-101', '14:28:12', 'CASING-101B', 'PAD 04-A', 'Presión Anular Casing en ascenso acelerado', '642.0 PSI', '> 600.0 PSI Advisory', 'advisory', 0, NULL, NULL, 'Restricción en flujo de retorno'),
('alm-102', '13:52:04', 'VIB-ESP-B04', 'BOMBA ESP', 'Vibración axial de bomba ESP fuera de tolerancia ISO 10816', '2.15 mm/s', '> 2.0 mm/s Warning', 'warning', 0, NULL, NULL, 'Posible desgaste en rodamiento inferior'),
('alm-103', '11:15:30', 'ESD-LOOP-9', 'SEGURIDAD RTU', 'Test de bucle de presión neumática completado exitosamente', '120 PSI', '120 PSI Nominal', 'nominal', 1, 'M. Conde Dussan', '11:16:05', 'Prueba de rutina programada')
ON DUPLICATE KEY UPDATE `descripcion` = VALUES(`descripcion`), `reconocida` = VALUES(`reconocida`);

-- ----------------------------------------------------------------------------
-- 7. TABLA: bitacora_auditoria
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bitacora_auditoria` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `timestamp_log` VARCHAR(50) NOT NULL,
  `codigo_usuario` VARCHAR(50) NOT NULL,
  `nombre_usuario` VARCHAR(150) NOT NULL,
  `nombre_rol` VARCHAR(100) NOT NULL,
  `accion` VARCHAR(255) NOT NULL,
  `categoria` ENUM('SEGURIDAD', 'MANIOBRA', 'ALARMA', 'CONFIGURACIÓN') NOT NULL,
  `estado` ENUM('EXITOSO', 'RECHAZADO', 'ADVERTENCIA') NOT NULL,
  `detalles` TEXT NOT NULL,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES
('aud-9901', '14:55:00', 'OP-449', 'M. Conde Dussan', 'Administrador del Sistema', 'Inicialización de Backend PHP + MySQL en XAMPP', 'CONFIGURACIÓN', 'EXITOSO', 'Conexión verificada con Apache 2.4 y MariaDB/MySQL 10.4 en localhost'),
('aud-9902', '14:35:12', 'OP-449', 'M. Conde Dussan', 'Administrador del Sistema', 'Ajuste de Estrangulador Pozo ALFA-12', 'MANIOBRA', 'EXITOSO', 'Apertura de Choke ajustada a 42.8% (+2.5% de incremento sostenido)'),
('aud-9903', '13:50:00', 'ENG-204', 'H. Manchola Bocanegra', 'Ingeniero de Operaciones', 'Sincronización de Umbrales de Telemetría', 'CONFIGURACIÓN', 'EXITOSO', 'Verificación de consignas para pozo BRAVO-04 y caudalímetro Coriolis')
ON DUPLICATE KEY UPDATE `accion` = VALUES(`accion`);

-- ----------------------------------------------------------------------------
-- 8. TABLA: nodos_proceso (P&ID)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `nodos_proceso` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `tipo` VARCHAR(50) NOT NULL,
  `pos_x` INT NOT NULL,
  `pos_y` INT NOT NULL,
  `presion` DECIMAL(10,2) NOT NULL,
  `temperatura` DECIMAL(10,2) NOT NULL,
  `caudal` DECIMAL(10,2) NOT NULL,
  `valvula_abierta` TINYINT(1) DEFAULT 1,
  `severidad` ENUM('nominal', 'advisory', 'warning', 'critical') DEFAULT 'nominal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `nodos_proceso` (`id`, `nombre`, `codigo`, `tipo`, `pos_x`, `pos_y`, `presion`, `temperatura`, `caudal`, `valvula_abierta`, `severidad`) VALUES
('node-1', 'CABEZAL POZO ALFA-12', 'WHTP-101A', 'wellhead', 120, 240, 1842.50, 78.40, 4210.00, 1, 'nominal'),
('node-2', 'ESTRANGULADOR CHOKE-A', 'FCV-101', 'choke', 280, 240, 1420.00, 77.10, 4210.00, 1, 'nominal'),
('node-3', 'MANIFOLD DE RECOLECCIÓN', 'MAN-400', 'pipeline', 460, 240, 1380.00, 76.50, 14820.00, 1, 'nominal'),
('node-4', 'SEPARADOR TRIFÁSICO V-201', 'SEP-201', 'separator', 680, 240, 450.00, 68.00, 14820.00, 1, 'nominal'),
('node-5', 'COMPRESOR DE GAS C-301', 'CMP-301', 'compressor', 860, 150, 1100.00, 95.00, 3200.00, 1, 'nominal'),
('node-6', 'OLEODUCTO A FISCALIZACIÓN', 'PIPE-DISP', 'pipeline', 860, 330, 280.00, 52.00, 11620.00, 1, 'nominal')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

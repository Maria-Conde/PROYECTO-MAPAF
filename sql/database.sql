-- ==============================================================================
-- PROYECTO MAPAF - Esquema Relacional de Base de Datos MySQL / MariaDB (XAMPP)
-- SENA - Centro de la Industria, la Empresa y los Servicios (Regional Huila)
-- Programa: Análisis y Desarrollo de Software (ADSO) | Ficha: 3413988-ADSO
-- Integrantes: Hernann Manchola, María José Conde, Emilin Tatiana Vargas
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `mapaf_db`
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `mapaf_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. Tabla: roles (Matriz de Control de Acceso Basado en Roles - RBAC)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
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

INSERT INTO `roles` (`id`, `nombre`, `codigo`, `descripcion`, `color`, `es_sistema`, `permisos`) VALUES
('role-admin', 'Administrador del Sistema', 'SYS-ADMIN', 'Control total de ciberseguridad, gestión de usuarios, enclavamientos y configuración de planta.', 'rose', 1, '["view_telemetry", "control_valves", "control_choke", "execute_esd", "reset_esd", "ack_alarms", "export_historian", "manage_users"]'),
('role-engineer', 'Ingeniero de Operaciones', 'OPS-ENG', 'Ajuste de consignas de proceso, curvas de producción, estranguladores y bypass bajo protocolo.', 'purple', 0, '["view_telemetry", "control_valves", "control_choke", "execute_esd", "reset_esd", "ack_alarms", "export_historian"]'),
('role-operator', 'Operador de Consola', 'CONSOLE-OP', 'Monitoreo continuo de cabezales, maniobra de choke y reconocimiento de alarmas operacionales.', 'emerald', 0, '["view_telemetry", "control_choke", "ack_alarms"]'),
('role-supervisor', 'Supervisor de Turno', 'SHIFT-SUPV', 'Supervisión de sala de control, autorización de maniobras críticas y acuse de alarmas ISA-18.2.', 'amber', 0, '["view_telemetry", "control_valves", "execute_esd", "ack_alarms", "export_historian"]'),
('role-viewer', 'Auditor / Observador', 'AUDIT-VIEW', 'Acceso exclusivo de sólo lectura para fiscalización de producción y reportes históricos.', 'sky', 0, '["view_telemetry", "export_historian"]');

-- ------------------------------------------------------------------------------
-- 2. Tabla: usuarios (Cuentas de Operadores y Personal Técnico)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
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

INSERT INTO `usuarios` (`id`, `codigo_empleado`, `nombre_completo`, `email`, `password_hash`, `rol_id`, `sector_asignado`, `estado`) VALUES
('usr-001', 'EMP-8812', 'María José Conde Dussan', 'm.conde@mapaf.senacontrol.co', '$2y$10$wT8yZ00xX/G3.qD...adminhash', 'role-admin', 'Planta Central Huila / RTOC', 'EN TURNO'),
('usr-002', 'EMP-8813', 'Hernann Thomas Manchola', 'h.manchola@mapaf.senacontrol.co', '$2y$10$wT8yZ00xX/G3.qD...adminhash', 'role-engineer', 'Pad 04 & 09 Upstream', 'EN TURNO'),
('usr-003', 'EMP-8814', 'Emilin Tatiana Vargas', 'e.vargas@mapaf.senacontrol.co', '$2y$10$wT8yZ00xX/G3.qD...adminhash', 'role-supervisor', 'Sala de Control y Despacho', 'EN TURNO'),
('usr-004', 'EMP-5521', 'Carlos Méndez Arango', 'c.mendez@mapaf.senacontrol.co', '$2y$10$wT8yZ00xX/G3.qD...userhash', 'role-operator', 'Batería de Separación 02', 'ACTIVO'),
('usr-005', 'EMP-1109', 'Javier Humberto Pinto Díaz', 'j.pinto@sena.edu.co', '$2y$10$wT8yZ00xX/G3.qD...userhash', 'role-admin', 'Auditoría Académica ADSO', 'ACTIVO');

-- ------------------------------------------------------------------------------
-- 3. Tabla: pozos (Cabezales de Producción API 6A y Actuadores)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `pozos`;
CREATE TABLE `pozos` (
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

INSERT INTO `pozos` (`id`, `nombre`, `sector`, `estado`, `presion_tubing`, `presion_casing`, `caudal_produccion`, `temperatura`, `apertura_choke`, `objetivo_choke`, `frecuencia_esp`, `vibracion_esp`, `valvula_ssv_abierta`, `purga_anular_abierta`, `inyeccion_quimica`, `arena_acustica_ppm`) VALUES
('well-a12', 'POZO ALFA-12', 'PAD 04-A', 'EN LÍNEA', 1842.50, 642.00, 4210.00, 78.40, 42.80, 42.80, 58.20, 1.840, 1, 0, 1, 4.20),
('well-b04', 'POZO BRAVO-04', 'PAD 04-B', 'EN LÍNEA', 2050.00, 710.00, 4890.00, 81.20, 38.50, 38.50, 60.00, 2.150, 1, 0, 1, 6.80),
('well-c09', 'POZO CHARLIE-09', 'PAD 09-NORTH', 'ESTRANGULADO', 1620.00, 580.00, 2980.00, 74.60, 22.00, 22.00, 52.00, 1.450, 1, 0, 1, 2.10),
('well-d02', 'POZO DELTA-02', 'PAD 09-SOUTH', 'MANTENIMIENTO', 420.00, 150.00, 0.00, 32.10, 0.00, 0.00, 0.00, 0.020, 0, 1, 0, 0.00);

-- ------------------------------------------------------------------------------
-- 4. Tabla: telemetria_metricas (Sensores Físicos y Monitoreo ISA)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `telemetria_metricas`;
CREATE TABLE `telemetria_metricas` (
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

INSERT INTO `telemetria_metricas` (`id`, `tag`, `etiqueta`, `valor`, `unidad`, `valor_objetivo`, `trip_bajo`, `alarma_alta`, `trip_alto`, `tendencia`, `severidad`, `historial`, `pozo_id`) VALUES
('pt-101', 'WHTP-101A', 'PRESIÓN DE CABEZAL (TUBING)', 1842.50, 'PSI', 1800.00, 850.00, 2100.00, 2350.00, 'stable', 'nominal', '[1810, 1822, 1835, 1830, 1845, 1840, 1838, 1842.5]', 'well-a12'),
('pt-102', 'CASING-101B', 'PRESIÓN ANULAR (CASING)', 642.00, 'PSI', 600.00, 100.00, 800.00, 950.00, 'rising', 'advisory', '[610, 618, 622, 630, 635, 638, 640, 642]', 'well-a12'),
('tt-301', 'TEMP-FLOW-301', 'TEMPERATURA DE MANIFOLD', 78.40, '°C', 75.00, 20.00, 92.00, 105.00, 'rising', 'nominal', '[75.2, 75.8, 76.5, 77.0, 77.5, 78.0, 78.2, 78.4]', 'well-a12'),
('ft-201', 'MFM-201', 'CAUDAL BRUTO DE PRODUCCIÓN', 14820.00, 'BPD', 15000.00, 4000.00, 16500.00, 18000.00, 'stable', 'nominal', '[14750, 14780, 14810, 14800, 14830, 14815, 14820, 14820]', 'well-b04'),
('esp-01', 'ESP-FREQ-01', 'FRECUENCIA BOMBA ESP', 58.20, 'Hz', 60.00, 35.00, 63.50, 66.00, 'stable', 'nominal', '[57.8, 58.0, 58.1, 58.2, 58.0, 58.2, 58.1, 58.2]', 'well-a12'),
('gor-402', 'GOR-ANALYZER', 'RELACIÓN GAS/PETRÓLEO (GOR)', 462.80, 'SCF/BBL', 450.00, 200.00, 550.00, 680.00, 'falling', 'nominal', '[475, 471, 468, 465, 464, 463, 463.5, 462.8]', 'well-c09');

-- ------------------------------------------------------------------------------
-- 5. Tabla: alarmas (Registro y Acuse Trazable ANSI/ISA-18.2)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `alarmas`;
CREATE TABLE `alarmas` (
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

INSERT INTO `alarmas` (`id`, `timestamp_evento`, `tag`, `pozo_id`, `subsistema`, `descripcion`, `valor`, `umbral`, `severidad`, `reconocida`, `reconocida_por`, `ack_timestamp`, `causa_disparo`) VALUES
('alm-101', '14:28:12', 'CASING-101B', 'well-a12', 'PAD 04-A', 'Presión Anular Casing en ascenso acelerado', '642.0 PSI', '> 600.0 PSI Advisory', 'advisory', 0, NULL, NULL, 'Restricción en flujo de retorno'),
('alm-102', '13:52:04', 'VIB-ESP-B04', 'well-b04', 'BOMBA ESP', 'Vibración axial de bomba ESP fuera de tolerancia ISO 10816', '2.15 mm/s', '> 2.0 mm/s Warning', 'warning', 0, NULL, NULL, 'Posible desgaste en rodamiento inferior'),
('alm-103', '11:15:30', 'ESD-LOOP-9', 'well-a12', 'SEGURIDAD RTU', 'Test de bucle de presión neumática completado exitosamente', '120 PSI', '120 PSI Nominal', 'nominal', 1, 'María José Conde', '11:16:05', 'Prueba de rutina programada');

-- ------------------------------------------------------------------------------
-- 6. Tabla: bitacora_auditoria (Trazabilidad Forense Inmutable 21 CFR Part 11)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `bitacora_auditoria`;
CREATE TABLE `bitacora_auditoria` (
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

INSERT INTO `bitacora_auditoria` (`id`, `timestamp_log`, `codigo_usuario`, `nombre_usuario`, `nombre_rol`, `accion`, `categoria`, `estado`, `detalles`) VALUES
('aud-001', '14:55:00', 'EMP-8812', 'María José Conde Dussan', 'Administrador del Sistema', 'Inicialización de Backend PHP + MySQL en XAMPP', 'CONFIGURACIÓN', 'EXITOSO', 'Conexión verificada con Apache 2.4 y MySQL en localhost:3306.'),
('aud-002', '14:35:12', 'EMP-8812', 'María José Conde Dussan', 'Administrador del Sistema', 'Ajuste de Estrangulador Pozo ALFA-12', 'MANIOBRA', 'EXITOSO', 'Apertura de Choke ajustada a 42.8% (+2.5% de incremento sostenido).'),
('aud-003', '13:50:00', 'EMP-8813', 'Hernann Thomas Manchola', 'Ingeniero de Operaciones', 'Sincronización de Umbrales de Telemetría', 'CONFIGURACIÓN', 'EXITOSO', 'Verificación de consignas para pozo BRAVO-04 y caudalímetro Coriolis.');

-- ------------------------------------------------------------------------------
-- 7. Tabla: configuracion_sistema (Parámetros Globales y Estado SIS ESD-1)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `configuracion_sistema`;
CREATE TABLE `configuracion_sistema` (
  `clave` VARCHAR(50) NOT NULL,
  `valor` TEXT NOT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`) VALUES
('plant_name', 'MAPAF RTOC - Estación Central de Telemetría', 'Nombre de la instalación'),
('sena_ficha', '3413988-ADSO', 'Ficha del programa SENA ADSO'),
('production_rate', '14820', 'Tasa bruta de producción en BPD'),
('flow_state', 'nominal', 'Estado del flujo general: nominal, throttled, tripped'),
('esd_triggered', '0', 'Estado de Parada de Emergencia ESD-1 (0=Normal, 1=Disparado)'),
('esd_trigger_time', '', 'Hora del último disparo ESD');

SET FOREIGN_KEY_CHECKS = 1;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mapaf_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `mapaf_db`
--

/*!40000 DROP DATABASE IF EXISTS `mapaf_db`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mapaf_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `mapaf_db`;

--
-- Table structure for table `alarmas`
--

DROP TABLE IF EXISTS `alarmas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alarmas` (
  `id` varchar(50) NOT NULL,
  `timestamp_evento` varchar(50) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `subsistema` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `valor` varchar(50) NOT NULL,
  `umbral` varchar(50) NOT NULL,
  `severidad` enum('nominal','advisory','warning','critical') DEFAULT 'warning',
  `reconocida` tinyint(1) DEFAULT 0,
  `reconocida_por` varchar(100) DEFAULT NULL,
  `ack_timestamp` varchar(50) DEFAULT NULL,
  `causa_disparo` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alarmas`
--

LOCK TABLES `alarmas` WRITE;
/*!40000 ALTER TABLE `alarmas` DISABLE KEYS */;
INSERT INTO `alarmas` VALUES ('alm-101','14:28:12','CASING-101B','PAD 04-A','Presión Anular Casing en ascenso acelerado','642.0 PSI','> 600.0 PSI Advisory','advisory',0,NULL,NULL,'Restricción en flujo de retorno','2026-10-21 18:25:51'),('alm-102','13:52:04','VIB-ESP-B04','BOMBA ESP','Vibración axial de bomba ESP fuera de tolerancia ISO 10816','2.15 mm/s','> 2.0 mm/s Warning','warning',0,NULL,NULL,'Posible desgaste en rodamiento inferior','2026-10-21 18:25:51'),('alm-103','11:15:30','ESD-LOOP-9','SEGURIDAD RTU','Test de bucle de presión neumática completado exitosamente','120 PSI','120 PSI Nominal','nominal',1,'M. Conde Dussan','11:16:05','Prueba de rutina programada','2026-10-21 18:25:51');
/*!40000 ALTER TABLE `alarmas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora_auditoria`
--

DROP TABLE IF EXISTS `bitacora_auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora_auditoria` (
  `id` varchar(50) NOT NULL,
  `timestamp_log` varchar(50) NOT NULL,
  `codigo_usuario` varchar(50) NOT NULL,
  `nombre_usuario` varchar(150) NOT NULL,
  `nombre_rol` varchar(100) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `categoria` enum('SEGURIDAD','MANIOBRA','ALARMA','CONFIGURACIÓN') NOT NULL,
  `estado` enum('EXITOSO','RECHAZADO','ADVERTENCIA') NOT NULL,
  `detalles` text NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora_auditoria`
--

LOCK TABLES `bitacora_auditoria` WRITE;
/*!40000 ALTER TABLE `bitacora_auditoria` DISABLE KEYS */;
INSERT INTO `bitacora_auditoria` VALUES ('aud-0789848','20:38:18','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 18:38:18'),('aud-0914771','20:59:07','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 18:59:07'),('aud-0915187','20:59:11','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 18:59:11'),('aud-0915731','20:59:17','EMP-5521','Carlos Méndez Arango','Operador de Consola','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Batería de Separación 02.','2026-10-21 18:59:17'),('aud-1072261','21:25:22','OP-449','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Estación Central / RTOC.','2026-10-21 19:25:22'),('aud-1072283','21:25:22','OP-449','Ing. Laura Gómez','Operador','Reconocimiento Trazable ISA-18.2','ALARMA','EXITOSO','Alarma ALM-001 reconocida formalmente. Causa/Justificación: \'Se verificó en campo la calibración del transmisor PT-101 y se purgó la línea de venteo.\'','2026-10-21 19:25:22'),('aud-1072292','21:25:22','OP-449','Ing. Laura Gómez','Operador','Intento de Blind ACK Bloqueado','SEGURIDAD','RECHAZADO','ESCUDO ANTIPATRÓN: Se bloqueó intento de silenciar la alarma ALM-001 sin causa técnica obligatoria (ANSI/ISA-18.2).','2026-10-21 19:25:22'),('aud-1082448','21:27:04','OP-449','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Estación Central / RTOC.','2026-10-21 19:27:04'),('aud-1099037','21:29:50','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 19:29:50'),('aud-1099056','21:29:50','EMP-8812','María José Conde Dussan','Administrador del Sistema','Reversión de Maniobra (Command UNDO) en pozo well-a12','MANIOBRA','EXITOSO','Se deshizo la consigna del Choke. Retornado de % a 42.8% vía Command::undo().','2026-10-21 19:29:50'),('aud-1099064','21:29:50','OP-449','M. Conde Dussan','Operador','Intento de Blind ACK Bloqueado','SEGURIDAD','RECHAZADO','ESCUDO ANTIPATRÓN: Se bloqueó intento de silenciar la alarma alm-01 sin causa técnica obligatoria (ANSI/ISA-18.2).','2026-10-21 19:29:50'),('aud-1101021','21:30:10','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 19:30:10'),('aud-1116062','21:32:40','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 19:32:40'),('aud-1117636','21:32:56','EMP-8812','María José Conde Dussan','Administrador del Sistema','Maniobra SCADA sobre pozo well-a12 (SetChokeCommand)','MANIOBRA','EXITOSO','Ejecutado con Patrón Command (SetChokeCommand). Consignas: {\"id\":\"well-a12\",\"chokeOpening\":60}','2026-10-21 19:32:56'),('aud-1117674','21:32:56','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 19:32:56'),('aud-1118110','21:33:01','EMP-8812','María José Conde Dussan','Administrador del Sistema','Rearme de Pozo (State Reset) a estado EN LÍNEA','SEGURIDAD','EXITOSO','Pozo well-a12 rearmado de DISPARADO a EN LÍNEA con estrangulador en 15% seguro.','2026-10-21 19:33:01'),('aud-1118136','21:33:01','EMP-8812','María José Conde Dussan','Administrador del Sistema','Maniobra SCADA sobre pozo well-a12 ()','MANIOBRA','EXITOSO','Ejecutado con Patrón Command (). Consignas: {\"id\":\"well-a12\",\"action\":\"trip\",\"userName\":\"M. Conde Dussan\"}','2026-10-21 19:33:01'),('aud-1118137','21:33:01','OP-449','M. Conde Dussan','Operador','Intento de Blind ACK Bloqueado','SEGURIDAD','RECHAZADO','ESCUDO ANTIPATRÓN: Se bloqueó intento de silenciar la alarma alm-01 sin causa técnica obligatoria (ANSI/ISA-18.2).','2026-10-21 19:33:01'),('aud-1118138','21:33:01','EMP-8812','María José Conde Dussan','Administrador del Sistema','Ajuste operativo choke 68.5%','MANIOBRA','EXITOSO','Ejecutado con Patrón Command (SetChokeCommand). Consignas: {\"id\":\"well-a12\",\"chokeOpening\":68.5,\"actionDescription\":\"Ajuste operativo choke 68.5%\",\"userName\":\"M. Conde Dussan\"}','2026-10-21 19:33:01'),('aud-1118151','21:33:01','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 19:33:01'),('aud-1118177','21:33:01','EMP-8812','María José Conde Dussan','Administrador del Sistema','Reversión de Maniobra (Command UNDO) en pozo well-a12','MANIOBRA','EXITOSO','Se deshizo la consigna del Choke. Retornado de 68.50% a 42.8% vía Command::undo().','2026-10-21 19:33:01'),('aud-1118198','21:33:01','OP-449','M. Conde Dussan','Operador','Reconocimiento Trazable ISA-18.2','ALARMA','EXITOSO','Alarma alm-01 reconocida formalmente. Causa/Justificación: \'Se verificó en campo la calibración del transmisor PT-101 y se purgó la línea de venteo.\'','2026-10-21 19:33:01'),('aud-1124631','21:34:06','OP-449','M. Conde Dussan','Operador','Reconocimiento Trazable ISA-18.2','ALARMA','EXITOSO','Alarma alm-01 reconocida formalmente. Causa/Justificación: \'Se verificó en campo la calibración del transmisor PT-101 y se purgó la línea de venteo.\'','2026-10-21 19:34:06'),('aud-1124642','21:34:06','EMP-8812','María José Conde Dussan','Administrador del Sistema','Maniobra SCADA sobre pozo well-a12 ()','MANIOBRA','EXITOSO','Ejecutado con Patrón Command (). Consignas: {\"id\":\"well-a12\",\"action\":\"trip\",\"userName\":\"M. Conde Dussan\"}','2026-10-21 19:34:06'),('aud-1124646','21:34:06','OP-449','M. Conde Dussan','Operador','Intento de Blind ACK Bloqueado','SEGURIDAD','RECHAZADO','ESCUDO ANTIPATRÓN: Se bloqueó intento de silenciar la alarma alm-01 sin causa técnica obligatoria (ANSI/ISA-18.2).','2026-10-21 19:34:06'),('aud-1124663','21:34:06','EMP-8812','María José Conde Dussan','Administrador del Sistema','Ajuste operativo choke 68.5%','MANIOBRA','EXITOSO','Ejecutado con Patrón Command (SetChokeCommand). Consignas: {\"id\":\"well-a12\",\"chokeOpening\":68.5,\"actionDescription\":\"Ajuste operativo choke 68.5%\",\"userName\":\"M. Conde Dussan\"}','2026-10-21 19:34:06'),('aud-1124677','21:34:06','EMP-8812','María José Conde Dussan','Administrador del Sistema','Reversión de Maniobra (Command UNDO) en pozo well-a12','MANIOBRA','EXITOSO','Se deshizo la consigna del Choke. Retornado de 68.50% a 42.8% vía Command::undo().','2026-10-21 19:34:06'),('aud-1124683','21:34:06','EMP-8812','María José Conde Dussan','Administrador del Sistema','Rearme de Pozo (State Reset) a estado EN LÍNEA','SEGURIDAD','EXITOSO','Pozo well-a12 rearmado de DISPARADO a EN LÍNEA con estrangulador en 15% seguro.','2026-10-21 19:34:06'),('aud-1124699','21:34:06','EMP-8812','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Planta Central Huila / RTOC.','2026-10-21 19:34:06'),('aud-1139050','21:36:30','OP-449','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Estación Central / RTOC.','2026-10-21 19:36:30'),('aud-1150487','21:38:24','OP-449','María José Conde Dussan','Administrador del Sistema','Inicio de Sesión y Emisión JWT','SEGURIDAD','EXITOSO','Token JWT emitido exitosamente con 28800s de validez para el sector Estación Central / RTOC.','2026-10-21 19:38:24'),('aud-9901','14:55:00','OP-449','M. Conde Dussan','Administrador del Sistema','Inicialización de Backend PHP + MySQL en XAMPP','CONFIGURACIÓN','EXITOSO','Conexión verificada con Apache 2.4 y MariaDB/MySQL 10.4 en localhost','2026-10-21 18:25:51'),('aud-9902','14:35:12','OP-449','M. Conde Dussan','Administrador del Sistema','Ajuste de Estrangulador Pozo ALFA-12','MANIOBRA','EXITOSO','Apertura de Choke ajustada a 42.8% (+2.5% de incremento sostenido)','2026-10-21 18:25:51'),('aud-9903','13:50:00','ENG-204','H. Manchola Bocanegra','Ingeniero de Operaciones','Sincronización de Umbrales de Telemetría','CONFIGURACIÓN','EXITOSO','Verificación de consignas para pozo BRAVO-04 y caudalímetro Coriolis','2026-10-21 18:25:51');
/*!40000 ALTER TABLE `bitacora_auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion_sistema`
--

DROP TABLE IF EXISTS `configuracion_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion_sistema` (
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_sistema`
--

LOCK TABLES `configuracion_sistema` WRITE;
/*!40000 ALTER TABLE `configuracion_sistema` DISABLE KEYS */;
INSERT INTO `configuracion_sistema` VALUES ('esd_trigger_time','','Hora del último disparo ESD','2026-10-21 18:25:51'),('esd_triggered','0','Estado de Parada de Emergencia ESD-1 (0=Normal, 1=Disparado)','2026-10-21 18:25:51'),('flow_state','nominal','Estado del flujo general: nominal, throttled, tripped','2026-10-21 18:25:51'),('plant_name','MAPAF RTOC - Estación Central de Telemetría','Nombre de la instalación','2026-10-21 18:25:51'),('production_rate','14820','Tasa bruta de producción en BPD','2026-10-21 18:25:51'),('sena_ficha','3413988-ADSO','Ficha del programa SENA ADSO','2026-10-21 18:25:51');
/*!40000 ALTER TABLE `configuracion_sistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nodos_proceso`
--

DROP TABLE IF EXISTS `nodos_proceso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nodos_proceso` (
  `id` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `pos_x` int(11) NOT NULL,
  `pos_y` int(11) NOT NULL,
  `presion` decimal(10,2) NOT NULL,
  `temperatura` decimal(10,2) NOT NULL,
  `caudal` decimal(10,2) NOT NULL,
  `valvula_abierta` tinyint(1) DEFAULT 1,
  `severidad` enum('nominal','advisory','warning','critical') DEFAULT 'nominal',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nodos_proceso`
--

LOCK TABLES `nodos_proceso` WRITE;
/*!40000 ALTER TABLE `nodos_proceso` DISABLE KEYS */;
INSERT INTO `nodos_proceso` VALUES ('node-1','CABEZAL POZO ALFA-12','WHTP-101A','wellhead',120,240,1842.50,78.40,4210.00,1,'nominal'),('node-2','ESTRANGULADOR CHOKE-A','FCV-101','choke',280,240,1420.00,77.10,4210.00,1,'nominal'),('node-3','MANIFOLD DE RECOLECCIÓN','MAN-400','pipeline',460,240,1380.00,76.50,14820.00,1,'nominal'),('node-4','SEPARADOR TRIFÁSICO V-201','SEP-201','separator',680,240,450.00,68.00,14820.00,1,'nominal'),('node-5','COMPRESOR DE GAS C-301','CMP-301','compressor',860,150,1100.00,95.00,3200.00,1,'nominal'),('node-6','OLEODUCTO A FISCALIZACIÓN','PIPE-DISP','pipeline',860,330,280.00,52.00,11620.00,1,'nominal');
/*!40000 ALTER TABLE `nodos_proceso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pozos`
--

DROP TABLE IF EXISTS `pozos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pozos` (
  `id` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `sector` varchar(100) NOT NULL,
  `estado` enum('EN LÍNEA','ESTRANGULADO','DISPARADO','MANTENIMIENTO') DEFAULT 'EN LÍNEA',
  `presion_tubing` decimal(10,2) NOT NULL,
  `presion_casing` decimal(10,2) NOT NULL,
  `caudal_produccion` decimal(10,2) NOT NULL,
  `temperatura` decimal(10,2) NOT NULL,
  `apertura_choke` decimal(5,2) NOT NULL DEFAULT 40.00,
  `objetivo_choke` decimal(5,2) NOT NULL DEFAULT 40.00,
  `frecuencia_esp` decimal(5,2) NOT NULL DEFAULT 58.00,
  `vibracion_esp` decimal(6,3) NOT NULL DEFAULT 1.800,
  `valvula_ssv_abierta` tinyint(1) DEFAULT 1,
  `purga_anular_abierta` tinyint(1) DEFAULT 0,
  `inyeccion_quimica` tinyint(1) DEFAULT 1,
  `arena_acustica_ppm` decimal(8,2) DEFAULT 4.20,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pozos`
--

LOCK TABLES `pozos` WRITE;
/*!40000 ALTER TABLE `pozos` DISABLE KEYS */;
INSERT INTO `pozos` VALUES ('well-a12','POZO ALFA-12','PAD 04-A','EN LÍNEA',1842.50,642.00,4210.00,78.40,15.00,15.00,58.20,1.840,1,0,1,4.20,'2026-10-21 19:34:06'),('well-b04','POZO BRAVO-04','PAD 04-B','EN LÍNEA',2050.00,710.00,4890.00,81.20,38.50,38.50,60.00,2.150,1,0,1,6.80,'2026-10-21 18:25:51'),('well-c09','POZO CHARLIE-09','PAD 09-NORTH','ESTRANGULADO',1620.00,580.00,2980.00,74.60,22.00,22.00,52.00,1.450,1,0,1,2.10,'2026-10-21 18:25:51'),('well-d02','POZO DELTA-02','PAD 09-SOUTH','MANTENIMIENTO',420.00,150.00,0.00,32.10,0.00,0.00,0.00,0.020,0,1,0,0.00,'2026-10-21 18:25:51');
/*!40000 ALTER TABLE `pozos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(20) DEFAULT 'purple',
  `es_sistema` tinyint(1) DEFAULT 0,
  `permisos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`permisos`)),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES ('role-admin','Administrador del Sistema','SYS-ADMIN','Control total de configuración, ciberseguridad industrial, gestión de usuarios y parámetros globales.','rose',1,'[\"view_telemetry\", \"control_valves\", \"control_choke\", \"execute_esd\", \"reset_esd\", \"ack_alarms\", \"ack_all_alarms\", \"export_historian\", \"control_cctv\", \"generate_reports\", \"bypass_interlocks\", \"manage_roles_users\"]','2026-10-21 18:25:51'),('role-engineer','Ingeniero de Operaciones','OPS-ENG','Ajuste de consignas de proceso, curvas de producción, estranguladores y bypass bajo protocolo.','purple',0,'[\"view_telemetry\", \"control_valves\", \"control_choke\", \"execute_esd\", \"reset_esd\", \"ack_alarms\", \"ack_all_alarms\", \"export_historian\", \"control_cctv\", \"generate_reports\"]','2026-10-21 18:25:51'),('role-operator','Operador de Consola','CONSOLE-OP','Monitoreo continuo de cabezales, manifold de recolección y reconocimiento de alarmas operacionales.','emerald',0,'[\"view_telemetry\", \"control_choke\", \"ack_alarms\", \"control_cctv\"]','2026-10-21 18:25:51'),('role-supervisor','Supervisor de Turno','SHIFT-SUPV','Supervisión de sala de control, autorización de maniobras críticas y reconocimiento de alarmas ISA-18.2.','amber',0,'[\"view_telemetry\", \"control_valves\", \"execute_esd\", \"ack_alarms\", \"ack_all_alarms\", \"export_historian\", \"control_cctv\", \"generate_reports\"]','2026-10-21 18:25:51'),('role-viewer','Auditor / Observador Externo','AUDIT-VIEW','Acceso exclusivo de sólo lectura para fiscalización de producción y reportes históricos.','sky',0,'[\"view_telemetry\", \"export_historian\", \"generate_reports\"]','2026-10-21 18:25:51');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telemetria_metricas`
--

DROP TABLE IF EXISTS `telemetria_metricas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `telemetria_metricas` (
  `id` varchar(50) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `etiqueta` varchar(150) NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `unidad` varchar(20) NOT NULL,
  `valor_objetivo` decimal(12,2) NOT NULL,
  `trip_bajo` decimal(12,2) NOT NULL,
  `alarma_alta` decimal(12,2) NOT NULL,
  `trip_alto` decimal(12,2) NOT NULL,
  `tendencia` enum('rising','falling','stable') DEFAULT 'stable',
  `severidad` enum('nominal','advisory','warning','critical') DEFAULT 'nominal',
  `historial` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`historial`)),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telemetria_metricas`
--

LOCK TABLES `telemetria_metricas` WRITE;
/*!40000 ALTER TABLE `telemetria_metricas` DISABLE KEYS */;
INSERT INTO `telemetria_metricas` VALUES ('esp-01','ESP-FREQ-01','FRECUENCIA BOMBA ESP',58.20,'Hz',60.00,35.00,63.50,66.00,'stable','nominal','[57.8, 58.0, 58.1, 58.2, 58.0, 58.2, 58.1, 58.2]','2026-10-21 18:25:51'),('ft-201','MFM-201','CAUDAL BRUTO DE PRODUCCIÓN',14820.00,'BPD',15000.00,4000.00,16500.00,18000.00,'stable','nominal','[14750, 14780, 14810, 14800, 14830, 14815, 14820, 14820]','2026-10-21 18:25:51'),('gor-402','GOR-ANALYZER','RELACIÓN GAS/PETRÓLEO (GOR)',462.80,'SCF/BBL',450.00,200.00,550.00,680.00,'falling','nominal','[475, 471, 468, 465, 464, 463, 463.5, 462.8]','2026-10-21 18:25:51'),('pt-101','WHTP-101A','PRESIÓN DE CABEZAL (TUBING)',1842.50,'PSI',1800.00,850.00,2100.00,2350.00,'stable','nominal','[1810, 1822, 1835, 1830, 1845, 1840, 1838, 1842.5]','2026-10-21 18:25:51'),('pt-102','CASING-101B','PRESIÓN ANULAR (CASING)',642.00,'PSI',600.00,100.00,800.00,950.00,'rising','nominal','[610, 618, 622, 630, 635, 638, 640, 642]','2026-10-21 18:25:51'),('tt-301','TEMP-FLOW-301','TEMPERATURA DE MANIFOLD',78.40,'°C',75.00,20.00,92.00,105.00,'rising','nominal','[75.2, 75.8, 76.5, 77.0, 77.5, 78.0, 78.2, 78.4]','2026-10-21 18:25:51');
/*!40000 ALTER TABLE `telemetria_metricas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` varchar(50) NOT NULL,
  `codigo_empleado` varchar(50) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol_id` varchar(50) NOT NULL,
  `sector_asignado` varchar(100) NOT NULL,
  `estado` enum('ACTIVO','EN TURNO','SUSPENDIDO') DEFAULT 'ACTIVO',
  `ultimo_acceso` datetime DEFAULT current_timestamp(),
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_empleado` (`codigo_empleado`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_usuario_rol` (`rol_id`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES ('usr-001','EMP-8812','María José Conde Dussan','m.conde@mapaf.senacontrol.co','$2y$10$wT8yZ00xX/G3.qD...adminhash','role-admin','Planta Central Huila / RTOC','EN TURNO','2026-10-21 13:25:51','2026-10-21 18:25:51'),('usr-002','EMP-8813','Hernann Thomas Manchola','h.manchola@mapaf.senacontrol.co','$2y$10$wT8yZ00xX/G3.qD...adminhash','role-engineer','Pad 04 & 09 Upstream','EN TURNO','2026-10-21 13:25:51','2026-10-21 18:25:51'),('usr-003','EMP-8814','Emilin Tatiana Vargas','e.vargas@mapaf.senacontrol.co','$2y$10$wT8yZ00xX/G3.qD...adminhash','role-supervisor','Sala de Control y Despacho','EN TURNO','2026-10-21 13:25:51','2026-10-21 18:25:51'),('usr-004','EMP-5521','Carlos Méndez Arango','c.mendez@mapaf.senacontrol.co','$2y$10$wT8yZ00xX/G3.qD...userhash','role-operator','Batería de Separación 02','ACTIVO','2026-10-21 13:25:51','2026-10-21 18:25:51'),('usr-005','EMP-1109','Javier Humberto Pinto','j.pinto@sena.edu.co','$2y$10$wT8yZ00xX/G3.qD...userhash','role-admin','Auditoría Académica ADSO','ACTIVO','2026-10-21 13:25:51','2026-10-21 18:25:51');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-21 14:40:09

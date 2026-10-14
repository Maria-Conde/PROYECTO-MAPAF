# 🛢️ PROYECTO MAPAF - Conexión con XAMPP, PHP y MySQL

**SENA - Centro de la Industria, la Empresa y los Servicios (Regional Huila)**  
**Programa:** Análisis y Desarrollo de Software (ADSO)  
**Ficha:** `3413988-ADSO`  
**Integrantes:**
- Hernann Thomas Manchola Bocanegra
- María José Conde Dussan
- Emilin Tatiana Vargas Manchola  
**Instructor:** Javier Humberto Pinto Díaz

---

## 🎯 Arquitectura de Conexión Implementada

Tu proyecto **MAPAF** ahora está completamente conectado con **XAMPP**, utilizando **PHP** como lenguaje backend y **MySQL (MariaDB)** como motor de base de datos relacional:

```
[ Frontend MAPAF Web / SCADA ]
            │
            ▼ (HTTP REST / JSON)
[ Servidor Apache 2.4 en XAMPP ]
            │
            ▼
[ Backend en PHP (PDO con UTF-8) ]
   ├── api/status.php     -> Estado del servidor y diagnóstico
   ├── api/sync.php       -> Sincronización general de telemetría y seguridad
   ├── api/metrics.php    -> Lecturas de sensores (presión, caudal, temp, ESP)
   ├── api/wellheads.php  -> Control de estranguladores (choke) y válvulas SSV
   ├── api/alarms.php     -> Reconocimiento de alarmas ISA-18.2 (ACK / ACK ALL)
   ├── api/users.php      -> Gestión de operadores y usuarios (CRUD)
   ├── api/roles.php      -> Matriz de roles y permisos RBAC
   ├── api/audit.php      -> Trazabilidad y bitácora de maniobras
   └── api/esd.php        -> Parada de Emergencia ESD-1 y rearme
            │
            ▼ (Puerto 3306)
[ Base de Datos MySQL: mapaf_db ]
   ├── configuracion_sistema
   ├── roles
   ├── usuarios
   ├── telemetria_metricas
   ├── pozos
   ├── alarmas
   ├── bitacora_auditoria
   └── nodos_proceso
```

---

## 🚀 Cómo Iniciar y Probar el Proyecto

### Opción 1: Con el Lanzador Automático (Recomendado)
Haz doble clic en el archivo:
📁 `iniciar_xampp_mapaf.bat`

Este script verificará que Apache y MySQL estén corriendo y abrirá automáticamente tu navegador en:
👉 `http://localhost/mapaf/`

---

### Opción 2: Desde el Panel de Control de XAMPP
1. Abre **XAMPP Control Panel** desde tu menú de inicio o en `C:\xampp\xampp-control.exe`.
2. Presiona **Start** en el módulo **Apache**.
3. Presiona **Start** en el módulo **MySQL**.
4. Abre cualquier navegador y visita:
   - **Plataforma MAPAF SCADA:** [http://localhost/mapaf/](http://localhost/mapaf/)
   - **Panel phpMyAdmin:** [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - **Instalador / Verificador de BD:** [http://localhost/mapaf/setup_database.php](http://localhost/mapaf/setup_database.php)
   - **Estado de la API:** [http://localhost/mapaf/api/status.php](http://localhost/mapaf/api/status.php)

---

## 🗄️ Parámetros de Conexión a MySQL

| Parámetro | Valor por defecto en XAMPP |
| :--- | :--- |
| **Servidor / Host** | `localhost` ó `127.0.0.1` |
| **Puerto** | `3306` |
| **Usuario** | `root` |
| **Contraseña** | *(vacía, sin contraseña)* |
| **Nombre de Base de Datos** | `mapaf_db` |
| **Cotejamiento (Collation)** | `utf8mb4_unicode_ci` |

---

## 📋 Estructura de Tablas en MySQL (`mapaf_db`)

1. **`telemetria_metricas`**: Contiene las variables de proceso (WHTP-101A, CASING-101B, MFM-201, TEMP-301, ESP-FREQ-01, GOR-402) con sus límites de alarma, unidad, valor objetivo e historial.
2. **`pozos`**: Información de los cabezales de pozo (Alfa-12, Bravo-04, Charlie-09, Delta-02), apertura del estrangulador (choke %), estado de válvula SSV, purga anular, inyección química y frecuencia ESP.
3. **`alarmas`**: Registro de eventos y alarmas bajo norma ISA-18.2 con campo de acuse (`reconocida`, `reconocida_por`, `ack_timestamp`).
4. **`usuarios`**: Cuentas de los miembros del equipo y operadores con código de empleado, sector y rol asignado.
5. **`roles`**: Definición de roles (Administrador, Ingeniero, Supervisor, Operador, Auditor) y sus permisos en formato JSON.
6. **`bitacora_auditoria`**: Trazabilidad completa de cada maniobra ejecutada en el sistema (cambio de choke, apertura de válvulas, disparo ESD, etc.).
7. **`nodos_proceso`**: Puntos del diagrama P&ID de la estación de recolección y separación.
8. **`configuracion_sistema`**: Estado del flujo general y enclavamiento de emergencia ESD-1.

---

## 📁 Archivos Creados en el Proyecto

- **`backend_php/`** (y desplegado en `C:\xampp\htdocs\mapaf`):
  - `config/database.php`: Conexión robusta mediante PDO con diagnóstico automático.
  - `config/cors.php`: Soporte para peticiones AJAX/REST y respuestas JSON.
  - `database.sql`: Script SQL con `CREATE DATABASE`, tablas y datos semilla listos para phpMyAdmin.
  - `setup_database.php`: Script interactivo de instalación y migración de base de datos.
  - `index.php`: Interfaz completa en PHP + HTML5 + CSS para operar el SCADA directamente en XAMPP.
  - `api/status.php`: Healthcheck de XAMPP, PHP y MySQL.
  - `api/sync.php`: Carga y sincronización de datos completa.
  - `api/metrics.php`: Lecturas y actualización de sensores.
  - `api/wellheads.php`: Maniobras de choke y válvulas con guardado en MySQL.
  - `api/alarms.php`: Acuse individual o masivo de alarmas.
  - `api/users.php`: CRUD de operadores.
  - `api/roles.php`: Roles y permisos.
  - `api/audit.php`: Registro de bitácora.
  - `api/esd.php`: Disparo y rearme de Parada de Emergencia.
- **`src/services/mapafApi.ts`**: Cliente TypeScript para conectar la aplicación con la API PHP.
- **`src/components/XamppConnectionModal.tsx`**: Ventana modal en el sistema para monitorear en vivo la conexión con MySQL.
- **`iniciar_xampp_mapaf.bat`**: Acceso directo para levantar servicios y abrir el sistema con un clic.

# 🛢️ PROYECTO MAPAF - Plataforma SCADA Táctico & Telemetría IIoT
## Centro de Monitoreo y Supervisión Industrial Upstream (API 6A / ANSI ISA-18.2 / IEC 61511)

**SENA - Centro de la Industria, la Empresa y los Servicios (Regional Huila)**  
**Programa:** Análisis y Desarrollo de Software (ADSO)  
**Ficha:** `3413988-ADSO`  
**Integrantes:**
- Hernann Thomas Manchola Bocanegra
- María José Conde Dussan
- Emilin Tatiana Vargas Manchola  
**Instructor:** Javier Humberto Pinto Díaz

---

## 🎯 1. Arquitectura del Sistema (PHP Puro + MySQL en XAMPP)

El proyecto **MAPAF** ha sido reestructurado como una solución nativa basada en **PHP 8.x** y **MySQL / MariaDB** bajo el patrón arquitectónico **Modelo-Vista-Controlador (MVC)**, diseñada para desplegarse directamente sobre **XAMPP (Apache + MariaDB/MySQL)** sin dependencias de Node.js, Python ni herramientas de compilación externas.

```
PROYECTO-MAPAF/
│
├── config/                  # Configuración y conexión desacoplada
│   ├── db_config.php        # Credenciales de MySQL y carga de variables de entorno (.env)
│   ├── config.php           # Constantes globales, rutas del proyecto y sesiones
│   ├── database.php         # Clase Database (Patrón Singleton PDO + Prepared Statements)
│   └── cors.php             # Cabeceras HTTP y respuestas JSON estándar
│
├── models/                  # Capa de Persistencia y Acceso a Datos (PDO)
│   ├── PozoModel.php        # Persistencia y maniobras en tabla 'pozos' (API 6A)
│   ├── TelemetriaModel.php  # Sensores físicos, tendencias y KPIs en 'telemetria_metricas'
│   ├── AlarmaModel.php      # Gestión de alarmas con acuse trazable (ANSI/ISA-18.2)
│   ├── EsdModel.php         # Parada de emergencia SIS y rearme en 'configuracion_sistema'
│   ├── UsuarioModel.php     # Operadores, turnos y matriz de roles RBAC en 'usuarios'/'roles'
│   └── AuditoriaModel.php   # Trazabilidad forense inmutable en 'bitacora_auditoria'
│
├── controllers/             # Capa de Lógica de Negocio
│   ├── WellheadController.php   # Maniobra de choke, válvulas SSV y enclavamientos
│   ├── TelemetryController.php  # Lecturas de transmisores en tiempo real
│   ├── AlarmController.php      # Filtrado y reconocimiento formal de alarmas
│   ├── EsdController.php        # Disparo y rearme de Parada de Emergencia ESD-1
│   ├── AuthController.php       # Conmutación de operadores de turno
│   ├── AuditController.php      # Consulta histórica de eventos
│   └── StatusController.php     # Diagnóstico de salud de XAMPP / PHP / MySQL
│
├── api/                     # Endpoints REST ligeros (consumidos vía Fetch API)
│   ├── wellheads.php        # GET pozos / POST consignas choke & SSV
│   ├── telemetry.php        # GET sensores de campo y KPIs consolidados
│   ├── alarms.php           # GET alarmas / POST acuse trazable
│   ├── esd.php              # GET estado ESD / POST disparo y rearme
│   ├── auth.php             # GET operadores / POST cambio de turno
│   ├── audit.php            # GET bitácora histórica
│   └── status.php           # GET diagnóstico en formato JSON
│
├── views/                   # Interfaz de Usuario SCADA Modular
│   ├── layout/
│   │   ├── header.php       # Barra superior con KPIs de planta, LED de estado y operador
│   │   ├── sidebar.php      # Navegación categorizada del centro de control
│   │   └── footer.php       # Pie de página técnico y modales interactivos
│   ├── dashboard.php        # Tablero general: Transmisores, pozos y alarmas en vivo
│   ├── wellheads.php        # Consola de cabezales de pozo API 6A
│   ├── alarms.php           # Consola de alarmas ISA-18.2 con acuse formal
│   ├── audit.php            # Visor forense de bitácora de auditoría (21 CFR Part 11)
│   └── status.php           # Panel de diagnóstico de servicios XAMPP
│
├── assets/                  # Activos de Frontend Nativos (Cero dependencias)
│   ├── css/
│   │   └── scada.css        # Estilos SCADA táctico industrial dark-mode (Vanilla CSS)
│   └── js/
│       └── scada.js         # Cliente asíncrono Fetch, sondeo reactivo 3s y modales
│
├── sql/
│   └── database.sql         # Script SQL normalizado con llaves foráneas e índices InnoDB
│
├── index.php                # Front Controller y punto de entrada de la aplicación
├── setup_database.php       # Asistente web y CLI para inicializar 'mapaf_db'
├── iniciar_xampp_mapaf.bat  # Automatización de arranque y sincronización en XAMPP
├── vincular_con_xampp.ps1   # Script PowerShell para despliegue en C:\xampp\htdocs\mapaf
├── .env.example             # Plantilla de variables de entorno
└── README.md                # Manual y documentación técnica
```

---

## ⚙️ 2. Configuración y Despliegue en XAMPP

### 2.1 Rutas de Instalación en XAMPP
- **Directorio Raíz Web:** `C:\xampp\htdocs\mapaf\`
- **URL en Navegador:** `http://localhost/mapaf/` o `http://localhost:8080/`
- **Intérprete PHP:** `C:\xampp\php\php.exe` (PHP 8.2+)
- **Base de Datos MySQL:** `127.0.0.1:3306`

### 2.2 Conexión de PHP a MySQL (PDO Singleton & Credenciales Desacopladas)
Las credenciales de base de datos se encuentran completamente desacopladas en `config/db_config.php` (con soporte para variables de entorno `.env` y valores de fallback para XAMPP), mientras que la gestión de conexiones y sentencias preparadas se realiza en `config/database.php`:

```php
// config/db_config.php (Archivo dedicado exclusivo para credenciales)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'mapaf_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');
```

**Ventajas de la implementación con Patrón Singleton:**
1. **Prevención de fugas de memoria y saturación del pool:** Reutiliza una única conexión PDO durante el ciclo de vida de cada petición HTTP.
2. **Seguridad contra SQL Injection:** Emula sentencias preparadas nativas (`PDO::ATTR_EMULATE_PREPARES => false`) y usa parámetros vinculados (`:id`, `:choke`, etc.).
3. **Manejo estricto de excepciones:** `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.

---

## 🗄️ 3. Esquema Relacional de Base de Datos (`mapaf_db`)

El esquema se encuentra normalizado en `sql/database.sql` bajo el motor **InnoDB** con codificación `utf8mb4`:

| Tabla | Clave Primaria | Claves Foráneas / Restricciones | Propósito |
| :--- | :--- | :--- | :--- |
| `roles` | `id` (VARCHAR) | `codigo` UNIQUE | Matriz RBAC de permisos por rol |
| `usuarios` | `id` (VARCHAR) | `rol_id` ➔ `roles(id)` | Operadores y personal técnico |
| `pozos` | `id` (VARCHAR) | - | Cabezales de producción API 6A |
| `telemetria_metricas` | `id` (VARCHAR) | `tag` UNIQUE, `pozo_id` ➔ `pozos(id)` | Sensores PT, TT, FT, ESP, GOR |
| `alarmas` | `id` (VARCHAR) | `pozo_id` ➔ `pozos(id)` | Eventos ANSI/ISA-18.2 |
| `bitacora_auditoria` | `id` (VARCHAR) | - | Trazabilidad inmutable 21 CFR Part 11 |
| `configuracion_sistema` | `clave` (VARCHAR) | - | Enclavamientos SIS ESD-1 y planta |

---

## 🚀 4. Puesta en Marcha (Paso a Paso)

### Opción A: Despliegue Automático con Script
1. Abre el Panel de Control de XAMPP e inicia **Apache** y **MySQL**.
2. Haz doble clic en `iniciar_xampp_mapaf.bat` o ejecuta en PowerShell:
   ```powershell
   .\vincular_con_xampp.ps1
   ```
3. El script sincronizará los archivos a `C:\xampp\htdocs\mapaf\`, ejecutará `setup_database.php` y abrirá `http://localhost/mapaf/`.

### Opción B: Despliegue Manual
1. Copia la carpeta `PROYECTO-MAPAF` a `C:\xampp\htdocs\mapaf`.
2. Abre tu navegador en: `http://localhost/mapaf/setup_database.php` para crear la base de datos automáticamente.
3. Ingresa a la consola SCADA en: `http://localhost/mapaf/`.

---

## 📋 5. Resumen de Módulos Conservados vs. Módulos Eliminados

### ✅ Módulos Esenciales Conservados
1. **Tablero Táctico SCADA (Dashboard):** KPIs de producción bruta (BPD), presión media de cabezal y conteo de pozos en línea.
2. **Telemetría de Proceso IIoT:** Transmisores WHTP-101A (PSI), CASING-101B (PSI), TEMP-301 (°C), MFM-201 (BPD), ESP-01 (Hz) y GOR-402 (SCF/BBL).
3. **Control de Cabezales de Pozo (API 6A):** Ajuste de consigna de estrangulador Choke (0-100%), apertura/cierre de válvulas SSV y rearme de pozo disparado.
4. **Gestión de Alarmas ANSI/ISA-18.2:** Consola con severidades (Nominal, Advisory, Warning, Critical) y acuse formal obligatorio.
5. **Sistema Instrumentado de Seguridad (ESD-1 / IEC 61511):** Parada de emergencia con aislamiento total de cabezales y procedimiento de rearme seguro.
6. **Control de Acceso RBAC:** Selección y conmutación de operadores de turno (Administrador, Ingeniero, Supervisor, Operador).
7. **Bitácora de Auditoría Forense (21 CFR Part 11):** Trazabilidad inmutable de maniobras y acuses.
8. **Diagnóstico de Infraestructura XAMPP:** Comprobación del estado de Apache, PHP, MariaDB/MySQL y tablas relacionales.

### ❌ Módulos y Archivos Eliminados (Justificación Técnica)
1. **Pila Node.js / TypeScript / React / Vite / Bun (`src/`, `package.json`, `bun.lock`, `vite.config.ts`, `tsconfig.json`):**
   - *Motivo:* El requerimiento exige expresamente ejecutar únicamente sobre XAMPP con PHP puro en el servidor, sin Node.js. Mantener el bundle React/Vite requería herramientas externas y duplicaba la lógica.
2. **Monolito de 4,473 líneas (`backend_php/index.php`):**
   - *Motivo:* Violaba el principio de responsabilidad única ("God Object / Blob Monolith"). Fue reemplazado por la arquitectura limpia MVC con vistas y controladores modulares.
3. **Módulos Teóricos Abstractos (`backend_php/antipatterns/`, `backend_php/domain/DomainArchitecture.php`, `api/patterns.php`):**
   - *Motivo:* Catálogos de texto que sobrecargaban el repositorio sin aportar funcionalidad operativa al SCADA. Los patrones de diseño reales (Singleton, Prepared Statements, Repository, State) quedaron integrados directamente en el código de producción.
4. **Archivos SQL y Scripts Duplicados (`database.sql`, `mapaf_db.sql`, `test_all_patterns_live.php`):**
   - *Motivo:* Había 3 copias redundantes del dump de base de datos. Se unificaron en `sql/database.sql`.

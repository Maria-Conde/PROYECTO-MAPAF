# 🛢️ PROYECTO MAPAF - Plataforma SCADA Táctico & Telemetría IIoT
## Conexión XAMPP (PHP + MySQL), Patrones de Diseño (GoF), Antipatrones, Dominios DDD y Seguridad JWT

**SENA - Centro de la Industria, la Empresa y los Servicios (Regional Huila)**  
**Programa:** Análisis y Desarrollo de Software (ADSO)  
**Ficha:** `3413988-ADSO`  
**Integrantes:**
- Hernann Thomas Manchola Bocanegra
- María José Conde Dussan
- Emilin Tatiana Vargas Manchola  
**Instructor:** Javier Humberto Pinto Díaz

---

## 🎯 1. Arquitectura de Conexión Integral (XAMPP + PHP + MySQL)

El proyecto **MAPAF** está plenamente conectado con **XAMPP**, utilizando **PHP 8.x** como lenguaje backend bajo arquitectura orientada a objetos (OOP) y **MySQL / MariaDB** como motor de persistencia relacional transaccional (ACID):

```
[ Frontend MAPAF SCADA (React 19 + TypeScript + Tailwind CSS) ]
            │
            ▼ (HTTP REST / JSON / Bearer JWT)
[ Servidor Apache 2.4 en XAMPP (http://localhost/mapaf/) ]
            │
            ▼
[ Backend en PHP (PDO UTF-8 con Patrón Singleton) ]
    ├── auth/jwt.php           -> Motor nativo JWT (RFC 7519 HMAC SHA-256)
    ├── api/login.php          -> Emisión y verificación de tokens JWT RBAC
    ├── api/status.php         -> Diagnóstico de XAMPP, PHP, MySQL y Patrones
    ├── api/sync.php           -> Sincronización completa de estado de planta
    ├── api/patterns.php       -> Laboratorio REST de Patrones y Antipatrones
    ├── api/domains.php        -> Catálogo de Dominios DDD (7 Bounded Contexts)
    ├── api/metrics.php        -> Sensores WHTP, Casing, Temp, Flow, ESP, GOR
    ├── api/wellheads.php      -> Control de Choke (API 6A) y Válvulas SSV
    ├── api/alarms.php         -> Acuse trazable de alarmas (ANSI/ISA-18.2)
    ├── api/esd.php            -> Parada de Emergencia ESD-1 y rearme
    ├── api/users.php          -> Gestión de operadores y credenciales
    ├── api/roles.php          -> Matriz de roles y permisos RBAC
    └── api/audit.php          -> Bitácora inmutable de auditoría (21 CFR Part 11)
            │
            ▼ (Puerto 3306 - Motor InnoDB)
[ Base de Datos MySQL: mapaf_db ]
    ├── configuracion_sistema  -> Enclavamiento ESD-1 y flujo de planta
    ├── roles                  -> Matriz de permisos RBAC en JSON
    ├── usuarios               -> Cuentas de operadores y turnos
    ├── telemetria_metricas    -> Sensores físicos y límites ISA-18.2
    ├── pozos                  -> Cabezales (Alfa, Bravo, Charlie, Delta)
    ├── alarmas                -> Registro de eventos y acuse con firma
    ├── bitacora_auditoria     -> Trazabilidad inmutable de maniobras
    └── nodos_proceso          -> Puntos P&ID de la estación
```

---

## ⚙️ 2. Patrones de Diseño de Software Implementados (GoF & Arquitectura)

Los patrones están implementados tanto en el **Backend PHP** (`backend_php/patterns/`) como en el **Frontend TypeScript** (`src/patterns/scadaPatterns.ts`):

| Categoría | Patrón GoF | Implementación PHP | Implementación TypeScript | Propósito Técnico en SCADA |
| :--- | :--- | :--- | :--- | :--- |
| **Creacional** | **Singleton** | `DatabaseSingleton.php` | `ScadaTelemetryStore.ts` | Garantiza una **única instancia de conexión PDO a MySQL**, eliminando la fuga y saturación de conexiones en XAMPP. |
| **Creacional** | **Factory Method** | `SensorFactory.php` | `SensorMetricFactory.ts` | Instancia sensores industriales (PT, TT, FT, VFD, GOR) con sus calibraciones y límites de seguridad ISA-18.2 encapsulados. |
| **Comportamiento** | **Observer** | `TelemetryObserver.php` | `TelemetryPublisher.ts` | Desacopla la adquisición de telemetría: notifica reactivamente a alarmas, bitácora de auditoría y enclavamientos SIS ante sobrepresión. |
| **Comportamiento** | **Command** | `ScadaCommand.php` | `ScadaCommandManager.ts` | Encapsula maniobras de estrangulador Choke y válvulas SSV como objetos con **soporte matemático de reversión (UNDO)** y trazabilidad. |
| **Comportamiento** | **Strategy** | `FlowCalculationStrategy.php` | `CoriolisStrategy.ts` | Alterna dinámicamente entre algoritmos de fiscalización de crudo: **Coriolis Fiscal (AGA-11)**, **Placa de Orificio (AGA-3)** y **Beggs & Brill**. |
| **Comportamiento** | **State** | `WellheadState.php` | `WellheadStateMachine.ts` | Modela el ciclo de vida seguro del pozo (API 6A: *Produciendo*, *Estrangulado*, *Disparado*, *Cerrado*), impidiendo arranques ilegales. |
| **Estructural** | **Adapter** | `IndustrialProtocolAdapter.php` | `IndustrialProtocolAdapter.ts` | Convierte tramas binarias industriales legacy (Modbus RTU 16-bit hex y OPC-UA) en el formato unificado JSON de MAPAF. |
| **Arquitectónico** | **Repository** | `ScadaRepositories.php` | `mapafApi.ts` | Desacopla la lógica de negocio del acceso directo SQL a MySQL `mapaf_db`. |

---

## ⚠️ 3. Catálogo de Antipatrones de Software Mitigados

En el desarrollo de sistemas industriales de misión crítica, MAPAF identifica y mitiga 8 antipatrones severos:

1. **The God Object / Blob (Objeto Dios Monolítico):**
   - *Riesgo:* Un script único de 4,000 líneas que hace todo; si falla un cálculo, se congela la recolección de toda la planta.
   - *Solución MAPAF:* Arquitectura por capas (Clean Architecture / SoC / Repository Pattern).
2. **SQL Injection via String Concatenation (Inyección SQL):**
   - *Riesgo:* Modificación no autorizada de aperturas de estrangulador por envío de payloads maliciosos.
   - *Solución MAPAF:* Sentencias preparadas inmutables (Prepared Statements PDO) con parámetros tipados.
3. **Hardcoded Secrets (Credenciales Quemadas):**
   - *Riesgo:* Exposición de contraseñas de MySQL y claves JWT en repositorios públicos.
   - *Solución MAPAF:* Carga desde variables de entorno (`getenv()` / `.env`) con clave de fallback protegida.
4. **Blind Alarm Acknowledgment (Reconocimiento Ciego de Alarmas):**
   - *Riesgo:* Silenciar alarmas críticas sin registrar el operador, motivo ni hora (violación grave de ISA-18.2).
   - *Solución MAPAF:* Patrón Command con validación de token JWT y registro de acuse forense.
5. **The Golden Hammer (El Martillo de Oro):**
   - *Riesgo:* Usar LocalStorage del navegador para toda la base de datos industrial, provocando inconsistencia multi-operador.
   - *Solución MAPAF:* Persistencia relacional ACID en MySQL (`mapaf_db`) y uso de LocalStorage únicamente para preferencias de UI.
6. **Polling Flood / Busy Waiting (Sondeo Inundante):**
   - *Riesgo:* Peticiones HTTP continuas cada 50ms que agotan el pool de conexiones de Apache y MySQL.
   - *Solución MAPAF:* Throttling adaptativo (intervalo de 2s - 3s) y notificaciones reactivas.
7. **Bypass sin Enclavamiento (Unchecked Safety Bypass):**
   - *Riesgo:* Rearmar una parada de emergencia (ESD) con un simple clic mientras técnicos trabajan en la línea de flujo.
   - *Solución MAPAF:* Componente `HoldInterlockModal` (confirmación activa de 3 segundos) y máquina de estados con enclavamiento físico.
8. **Anemic Domain Model (Modelo de Dominio Anémico):**
   - *Riesgo:* Clases sin reglas de validación que permiten consignas físicamente imposibles (-50% o 5000% de choke).
   - *Solución MAPAF:* Entidades ricas de dominio (DDD) que protegen sus propios invariantes.

---

## 🏛️ 4. Dominios del Sistema (Domain-Driven Design - 7 Bounded Contexts)

MAPAF estructura la ingeniería de software en 7 Dominios de Negocio delimitados:

1. **Dominio de Telemetría e Instrumentación IIoT (`TELEMETRY-CTX` · Norma ANSI/ISA-5.1):** Supervisa presión, temperatura, flujo másico y variadores de frecuencia.
2. **Dominio de Control de Pozos y Actuadores (`WELLHEAD-CTX` · Norma API Spec 6A):** Regulación micrométrica de estranguladores Choke y válvulas de seguridad hidráulica SSV.
3. **Dominio de Seguridad Funcional y Paradas de Emergencia (`SAFETY-CTX` · Norma IEC 61511 / ISA-84):** Matriz causa-efecto ESD-1 total y despresurización a antorcha.
4. **Dominio de Gestión de Alarmas (`ALARMS-CTX` · Norma ANSI/ISA-18.2):** Detección, priorización, ciclo de vida (Normal, Unacked, Acked, Shelved) y acuse trazable.
5. **Dominio de Identidad, Control de Acceso y Sesión JWT (`IDENTITY-CTX` · Norma RFC 7519 / NIST):** Autenticación criptográfica con HMAC SHA-256 y matriz de roles RBAC.
6. **Dominio de Auditoría Forense y Trazabilidad (`AUDIT-CTX` · Norma 21 CFR Part 11 / ISA-95):** Bitácora inmutable append-only con timestamp, código de operador y delta.
7. **Dominio de Producción y Balance de Flujo (`PRODUCTION-CTX` · Norma API MPMS Ch. 5.6 / AGA-3):** Fiscalización de crudo neto BPD, gas MSCFD y corrección térmica 60°F.

---

## 🔐 5. Seguridad y Autenticación con JSON Web Tokens (JWT)

Implementación nativa sin dependencias en `backend_php/auth/jwt.php`:
- **Algoritmo:** HMAC SHA-256 (`HS256`).
- **Estructura:** `Header.Payload.Signature` con codificación Base64Url (RFC 7519).
- **Vigencia del Token:** 8 Horas (28,800 segundos), equivalente a un turno estándar de sala de control RTOC.
- **Claims incluidos:**
  - `sub`: Identificador de usuario.
  - `employeeCode`: Código de empleado (ej. `OP-449`).
  - `name`: Nombre del operador (ej. `María José Conde Dussan`).
  - `roleId` y `roleCode`: Rol RBAC (ej. `SYS-ADMIN`, `OPS-ENG`, `SHIFT-SUPV`).
  - `sector`: Sector asignado (ej. `Estación Central / RTOC`).
  - `permissions`: Lista estricta de permisos autorizados.
- **Middleware de Protección:** Cabecera HTTP `Authorization: Bearer <token>`.

---

## 🚀 6. Cómo Iniciar y Probar el Proyecto

### Opción 1: Lanzador Automático por Lote
Hacer doble clic en:
📁 `iniciar_xampp_mapaf.bat`

### Opción 2: Desde el Navegador
1. Iniciar **Apache** y **MySQL** en el **Panel de Control de XAMPP**.
2. Abrir los enlaces principales:
   - **Plataforma SCADA (React Web):** `http://localhost:3000` (con `npm run dev`)
   - **Plataforma SCADA (PHP Nativo XAMPP):** [http://localhost/mapaf/](http://localhost/mapaf/)
   - **API de Patrones y Laboratorio:** [http://localhost/mapaf/api/patterns.php](http://localhost/mapaf/api/patterns.php)
   - **API de Dominios DDD:** [http://localhost/mapaf/api/domains.php](http://localhost/mapaf/api/domains.php)
   - **Endpoint de Login JWT:** [http://localhost/mapaf/api/login.php](http://localhost/mapaf/api/login.php)
   - **Instalador de Base de Datos:** [http://localhost/mapaf/setup_database.php](http://localhost/mapaf/setup_database.php)
   - **Panel phpMyAdmin:** [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

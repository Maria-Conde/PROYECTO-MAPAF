<?php
/**
 * PROYECTO MAPAF - Catálogo de Antipatrones de Software (Anti-Patterns)
 * SENA ADSO · Ficha 3413988
 * 
 * Documenta formalmente los antipatrones más peligrosos en el desarrollo de software
 * industrial y SCADA, analizando el síntoma, el código defectuoso (Bad Practice),
 * el riesgo en operaciones petroleras y el patrón de diseño aplicado en MAPAF para su resolución.
 */

namespace Mapaf\AntiPatterns;

class AntiPattern {
    public string $id;
    public string $name;
    public string $category;
    public string $symptom;
    public string $badCodeExample;
    public string $scadaRisk;
    public string $solutionPattern;
    public string $goodCodeExample;
    public string $severity;

    public function __construct(
        string $id,
        string $name,
        string $category,
        string $symptom,
        string $badCodeExample,
        string $scadaRisk,
        string $solutionPattern,
        string $goodCodeExample,
        string $severity
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->category = $category;
        $this->symptom = $symptom;
        $this->badCodeExample = $badCodeExample;
        $this->scadaRisk = $scadaRisk;
        $this->solutionPattern = $solutionPattern;
        $this->goodCodeExample = $goodCodeExample;
        $this->severity = $severity;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'symptom' => $this->symptom,
            'badCode' => $this->badCodeExample,
            'scadaRisk' => $this->scadaRisk,
            'solutionPattern' => $this->solutionPattern,
            'goodCode' => $this->goodCodeExample,
            'severity' => $this->severity
        ];
    }
}

class AntiPatternCatalog {
    /**
     * Retorna la lista exhaustiva de antipatrones analizados en MAPAF
     * @return AntiPattern[]
     */
    public static function getAll(): array {
        return [
            new AntiPattern(
                'anti-god-object',
                'The God Object / Blob (Objeto Dios Monolítico)',
                'Arquitectura & Diseño Estructural',
                'Un único archivo o clase gigante concentra todas las responsabilidades del sistema: consultas a MySQL, cálculos termodinámicos, generación de vistas HTML y autorización.',
                "// index.php monolítico de 4,000 líneas que hace todo:\n\$pdo = new PDO(...);\nif (\$_GET['q'] == 'temp') { echo \$_POST['val'] * 1.8 + 32; }\nif (\$_GET['q'] == 'esd') { mail('gerente@planta.com', 'ESD', 'Paro'); }\necho '<html><body>...</body></html>';",
                'Catastrófico: Un fallo de sintaxis en el módulo de reportes o CSS detiene la adquisición de telemetría de toda la estación de recolección.',
                'Patrón Repository + Microservicios + Clean Architecture',
                "// MAPAF divide en capas autónomas:\n\$repo = new WellheadRepository();\n\$context = new FlowCalculatorContext(new CoriolisDirectStrategy());\n\$result = \$context->calculateFlow(\$whtp, \$temp, \$choke, \$esp);",
                'CRÍTICO'
            ),

            new AntiPattern(
                'anti-sql-injection',
                'SQL Injection via Concatenation (Inyección SQL por Concatenación)',
                'Ciberseguridad Industrial',
                'Concatenar variables recibidas por GET/POST directamente dentro de la cadena de consulta SQL sin escapar ni tipar.',
                "// Código vulnerable a inyección SQL:\n\$id = \$_POST['id'];\n\$sql = \"UPDATE pozos SET apertura_choke = 100 WHERE id = '\" . \$id . \"'\";\n\$pdo->query(\$sql); // Atacante envía: Alfa-12' OR '1'='1",
                'Extremo: Permite que un atacante abra todos los estranguladores Choke al 100% simultáneamente, causando sobrepresión y ruptura de líneas de flujo.',
                'Patrón Repository con PDO Prepared Statements y Parámetros Nombrados',
                "// MAPAF usa sentencias preparadas inmutables:\n\$stmt = \$pdo->prepare('UPDATE pozos SET apertura_choke = :choke WHERE id = :id');\n\$stmt->execute([':choke' => \$choke, ':id' => \$id]);",
                'CRÍTICO'
            ),

            new AntiPattern(
                'anti-hardcoded-secrets',
                'Hardcoded Secrets (Credenciales y Claves Quemadas en Código)',
                'Seguridad & Configuración',
                'Contraseñas de MySQL, tokens de API de IA y claves secretas JWT escritas directamente como cadenas de texto en los archivos del repositorio.',
                "// Clave privada expuesta en el código:\nconst JWT_SECRET = 'mi_password_secreto_1234';\n\$db = new PDO('mysql:host=localhost', 'root', 'clave_super_secreta');",
                'Severo: Cualquier usuario con acceso al repositorio Git puede firmar tokens JWT falsos haciéndose pasar por el Administrador de Planta.',
                'Patrón Singleton de Configuración con Variables de Entorno (.env)',
                "// MAPAF aísla credenciales en entorno seguro:\ndefine('DB_PASS', getenv('DB_PASS') ?: '');\n\$secret = getenv('JWT_SECRET') ?: self::\$fallbackSecret;",
                'ALTO'
            ),

            new AntiPattern(
                'anti-blind-ack',
                'Blind Alarm Acknowledgment (Reconocimiento Ciego de Alarmas)',
                'Operaciones SCADA (ISA-18.2)',
                'Permitir apagar o silenciar alarmas críticas sin exigir autenticación del operador, motivo de la intervención ni registro de hora.',
                "// Botón que apaga alarmas sin trazabilidad:\nfunction silenciarAlarmas() {\n  fetch('/api/apagar_todas.php'); // No guarda quién ni por qué\n}",
                'Fatal: Los operadores silencian alarmas repetitivas sin investigar la causa raíz, encubriendo fugas de gas H2S o sobrepresión inminente.',
                'Patrón Command con Firma Criptográfica JWT y Registro de Auditoría',
                "// MAPAF exige token JWT y graba bitácora inmutable:\n\$payload = JwtService::requirePermission('ack_alarms');\n\$audit->log(\$payload['employeeCode'], \$payload['name'], 'ACK Alarma ISA-18.2');",
                'CRÍTICO'
            ),

            new AntiPattern(
                'anti-golden-hammer',
                'The Golden Hammer (El Martillo de Oro)',
                'Decisión Arquitectónica',
                'Pretender que una sola tecnología o patrón resuelva todos los problemas técnicos (ej. usar LocalStorage del navegador para toda la persistencia SCADA de múltiples operadores).',
                "// Guardar el estado operacional de los pozos en localStorage:\nlocalStorage.setItem('pozos_db', JSON.stringify(pozos)); // No sincroniza entre operadores",
                'Alto: Los operadores en distintas consolas tienen visiones contradictorias de la planta (Split-Brain) y los datos se pierden al limpiar la caché.',
                'Arquitectura Híbrida: Base de Datos Relacional MySQL (mapaf_db) para datos ACID + LocalStorage sólo para preferencias cosméticas de UI',
                "// MAPAF usa MySQL relacional normalizado en XAMPP para el estado compartido:\n\$status = checkDatabaseStatus(); // Valida motor MySQL InnoDB con soporte ACID",
                'ALTO'
            ),

            new AntiPattern(
                'anti-polling-flood',
                'Busy Waiting / Polling Flood (Sondeo Inundante sin Throttling)',
                'Rendimiento & Red',
                'Lanzar peticiones HTTP GET cada 50 milisegundos desde cada terminal de cliente sin debounce, saturando el servidor Apache y la base de datos.',
                "// Sondeo descontrolado:\nsetInterval(() => {\n  fetch('/api/metricas.php'); // 20 peticiones por segundo por cliente\n}, 50);",
                'Medio-Alto: Agotamiento del pool de conexiones MySQL (Error: "Too many connections"), provocando congelamiento de la consola SCADA.',
                'Patrón Observer + Throttling Adaptativo + Sincronización Delta',
                "// MAPAF sondea a intervalos controlados (1000ms - 2000ms) y emite notificaciones delta:\nconst syncWithXampp = async () => { /* interval adaptativo con manejo de errores */ };",
                'MEDIO'
            ),

            new AntiPattern(
                'anti-bypass-no-audit',
                'Bypass sin Enclavamiento (Unchecked Safety Interlock Bypass)',
                'Seguridad Funcional (IEC 61511)',
                'Permitir la apertura de válvulas de pozo o el rearme de una parada de emergencia (ESD) con un simple clic sin validación de estado ni doble confirmación.',
                "// Rearme instantáneo sin verificación:\n<button onclick=\"resetEsd()\">Restablecer ESD</button>",
                'Catastrófico: Rearmar un pozo disparado mientras los técnicos aún están realizando mantenimiento en caliente en la línea de flujo.',
                'Patrón State con Enclavamiento Físico (Hold-to-Confirm) y Matriz Interlock',
                "// MAPAF implementa HoldInterlockModal (mantener presionado 3s) y valida transiciones con State Pattern:\nif (!context.getState().canProduce()) throw new Exception('ENCLAVAMIENTO ACTIVO');",
                'CRÍTICO'
            ),

            new AntiPattern(
                'anti-anemic-domain',
                'Anemic Domain Model (Modelo de Dominio Anémico)',
                'Domain-Driven Design (DDD)',
                'Entidades que solo tienen propiedades públicas o getters/setters sin ninguna regla de validación de negocio, dispersando las reglas en controladores.',
                "// Entidad anémica sin invariantes:\nclass Pozo {\n  public \$aperturaChoke;\n} // Permite \$pozo->aperturaChoke = -999 o 50000%",
                'Medio: Inconsistencia de datos, valores de estrangulador físicamente absurdos o estados contradictorios en el sistema SCADA.',
                'Patrón Domain Model Rico con Invariantes Encapsulados (DDD)',
                "// MAPAF valida invariantes en el propio modelo de dominio:\nclass ChokePercentage {\n  public function __construct(float \$val) {\n    if (\$val < 0.0 || \$val > 100.0) throw new InvalidArgumentException('Rango 0-100%');\n  }\n}",
                'MEDIO'
            )
        ];
    }
}

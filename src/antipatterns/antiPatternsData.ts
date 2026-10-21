/**
 * PROYECTO MAPAF - Catálogo de Antipatrones de Software (Anti-Patterns)
 * SENA ADSO · Ficha 3413988
 * 
 * Modela los antipatrones más críticos en desarrollo de software industrial y SCADA,
 * documentando su síntoma, código defectuoso, riesgo operacional y el patrón
 * de diseño aplicado en MAPAF para su resolución arquitectónica.
 */

export interface AntiPatternItem {
  id: string;
  name: string;
  category: string;
  severity: 'CRÍTICO' | 'ALTO' | 'MEDIO';
  symptom: string;
  scadaRisk: string;
  badCode: string;
  solutionPattern: string;
  goodCode: string;
  appliedInMapaf: string;
}

export const ANTIPATTERNS_DATA: AntiPatternItem[] = [
  {
    id: 'anti-god-object',
    name: 'The God Object / Blob (Objeto Dios Monolítico)',
    category: 'Arquitectura & Estructura',
    severity: 'CRÍTICO',
    symptom: 'Un solo archivo PHP o componente React gigantesco de 4,000 líneas que gestiona persistencia, HTML/CSS, cálculos termodinámicos y autenticación sin separar responsabilidades.',
    scadaRisk: 'Catastrófico: Un error tipográfico en la vista de reportes o CSS detiene la adquisición de datos de telemetría de toda la planta en tiempo real.',
    badCode: `// index.php monolítico que hace todo directamente:
$pdo = new PDO("mysql:host=localhost", "root", "");
if ($_GET['op'] == 'whtp') {
    $val = $_POST['v'] * 14.5; // Cálculo directo
    $pdo->query("UPDATE pozos SET whtp = $val");
}
// 3,000 líneas más mezclando lógica, SQL y HTML...`,
    solutionPattern: 'Patrón Repository + Clean Architecture + Separación de Responsabilidades (SoC)',
    goodCode: `// Arquitectura desacoplada de MAPAF:
$repo = new WellheadRepository();
$context = new FlowCalculatorContext(new CoriolisDirectStrategy());
$result = $context->calculateFlow($whtp, $temp, $choke, $esp);
$repo->updateChoke($wellId, $targetOpening);`,
    appliedInMapaf: 'MAPAF aísla los dominios en clases autónomas: Backend modular en backend_php/patterns/ y servicios especializados en TypeScript.'
  },
  {
    id: 'anti-sql-injection',
    name: 'SQL Injection via String Concatenation (Inyección SQL por Concatenación)',
    category: 'Ciberseguridad Industrial',
    severity: 'CRÍTICO',
    symptom: 'Concatenar variables recibidas por GET/POST directamente dentro de la cadena SQL sin usar sentencias preparadas ni validación de tipo.',
    scadaRisk: 'Extremo: Un atacante puede enviar inyecciones como "Alfa-12\' OR \'1\'=\'1" para abrir estranguladores Choke al 100%, provocando sobrepresión y reventón de tuberías.',
    badCode: `// Vulnerable a inyección SQL maliciosa:
$id = $_POST['well_id'];
$query = "UPDATE pozos SET apertura_choke = 100 WHERE id = '" . $id . "'";
$pdo->query($query); // Atacante inyecta sentencias arbitrarias`,
    solutionPattern: 'Patrón Repository con PDO Prepared Statements y Parámetros Tipados',
    goodCode: `// Protección con Prepared Statements en MAPAF:
$stmt = $pdo->prepare("UPDATE pozos SET apertura_choke = :choke WHERE id = :id");
$stmt->execute([':choke' => $validatedChoke, ':id' => $validatedId]);`,
    appliedInMapaf: 'El 100% de las consultas en backend_php/api/ y backend_php/patterns/Repository/ utilizan PDO con sentencias preparadas estrictas.'
  },
  {
    id: 'anti-hardcoded-secrets',
    name: 'Hardcoded Secrets (Credenciales Quemadas en Código)',
    category: 'Ciberseguridad & Configuración',
    severity: 'ALTO',
    symptom: 'Escribir claves secretas JWT, contraseñas de MySQL y tokens de API como cadenas literales en los archivos de código fuente subidos al repositorio.',
    scadaRisk: 'Severo: Cualquier usuario con acceso al repositorio Git puede falsificar tokens JWT con rol de Administrador y manipular paradas de emergencia.',
    badCode: `// Clave secreta expuesta públicamente en el repositorio:
const JWT_SECRET = 'clave_secreta_12345';
$db = new PDO('mysql:host=localhost', 'root', 'clave_maestra_planta');`,
    solutionPattern: 'Patrón Singleton de Configuración con Variables de Entorno (.env)',
    goodCode: `// Carga segura desde variables de entorno con fallback:
define('DB_PASS', getenv('DB_PASS') ?: '');
$secret = getenv('JWT_SECRET') ?: JwtService::getSecret();`,
    appliedInMapaf: 'Archivo .env.example, variables con getenv() y JwtService::getSecret() centralizado en el backend.'
  },
  {
    id: 'anti-blind-ack',
    name: 'Blind Alarm Acknowledgment (Reconocimiento Ciego de Alarmas)',
    category: 'Operaciones SCADA (ISA-18.2)',
    severity: 'CRÍTICO',
    symptom: 'Permitir apagar o silenciar alarmas críticas con un solo clic sin autenticar al operador ni registrar el motivo operacional de la intervención.',
    scadaRisk: 'Fatal: Oculta eventos catastróficos (fugas de gas tóxico H2S o sobrepresión en pozo) porque los operadores silencian alarmas repetitivas sin investigar.',
    badCode: `// Reconocimiento ciego sin trazabilidad forense:
function silenciarAlarmas() {
    fetch('/api/apagar_todas.php'); // No guarda operador, hora ni causa
}`,
    solutionPattern: 'Patrón Command con Firma Criptográfica JWT y Bitácora de Auditoría Inmutable',
    goodCode: `// MAPAF exige token JWT firmado y graba bitácora bajo norma ISA-18.2:
$operator = JwtService::requirePermission('ack_alarms');
$alarmRepo->acknowledge($alarmId, $operator['name']);
$auditRepo->log($operator['employeeCode'], $operator['name'], 'Reconocimiento Alarma');`,
    appliedInMapaf: 'Cada acuse en AlarmsScreen y api/alarms.php registra timestamp, código de empleado, rol y delta en la tabla bitacora_auditoria.'
  },
  {
    id: 'anti-golden-hammer',
    name: 'The Golden Hammer (El Martillo de Oro)',
    category: 'Decisiones Arquitectónicas',
    severity: 'ALTO',
    symptom: 'Usar la misma herramienta para absolutamente todo (ej. almacenar el estado de toda la planta en el LocalStorage de un solo navegador en vez de una BD relacional).',
    scadaRisk: 'Alto: Cada operador ve una versión contradictoria de la planta (Split-Brain) y todos los datos históricos se borran cuando el usuario limpia la caché.',
    badCode: `// Almacenar telemetría industrial en LocalStorage del cliente:
localStorage.setItem('scada_pozos', JSON.stringify(wells)); // Inexistente para otros operadores`,
    solutionPattern: 'Arquitectura Híbrida Justificada: Base de Datos Relacional MySQL (mapaf_db) con Motor InnoDB + Caché Local',
    goodCode: `// MAPAF centraliza la persistencia relacional en MySQL en XAMPP:
$status = checkDatabaseStatus(); // Valida persistencia ACID centralizada
$sync = mapafApi.fetchSyncData(); // Sincroniza todas las terminales en red`,
    appliedInMapaf: 'MySQL mapaf_db en XAMPP como fuente única de la verdad con soporte ACID y concurrencia multi-operador.'
  },
  {
    id: 'anti-polling-flood',
    name: 'Busy Waiting / Polling Flood (Sondeo Inundante sin Control)',
    category: 'Rendimiento & Red',
    severity: 'MEDIO',
    symptom: 'Lanzar peticiones HTTP GET cada 50ms desde cada cliente sin debounce ni throttling, saturando el servidor Apache y la base de datos MySQL.',
    scadaRisk: 'Medio-Alto: Agotamiento del pool de conexiones ("Too many connections"), bloqueando la recepción de alarmas en tiempo real.',
    badCode: `// Bucle de sondeo descontrolado:
setInterval(() => {
    fetch('/api/metrics.php'); // 20 peticiones por segundo por cada pantalla
}, 50);`,
    solutionPattern: 'Patrón Observer + Throttling Adaptativo + Sincronización Delta',
    goodCode: `// Sondeo inteligente con manejo de errores y cadencia controlada (1.5s - 2.5s):
const syncTimer = setInterval(async () => {
    if (!isSyncing) await syncWithXampp();
}, 2000);`,
    appliedInMapaf: 'Temporizador sincronizado en App.tsx con control de concurrencia y detección de desconexión.'
  },
  {
    id: 'anti-bypass-no-audit',
    name: 'Bypass sin Enclavamiento (Unchecked Safety Interlock Bypass)',
    category: 'Seguridad Funcional (IEC 61511)',
    severity: 'CRÍTICO',
    symptom: 'Permitir abrir válvulas de pozo o restablecer paradas de emergencia (ESD) directamente con un clic ordinario sin confirmación prolongada ni validación de estado.',
    scadaRisk: 'Catastrófico: Rearmar un pozo disparado mientras los técnicos aún se encuentran realizando mantenimiento mecánico en la línea de flujo.',
    badCode: `// Rearme instantáneo sin doble confirmación ni chequeo de enclavamiento:
<button onClick={() => setEsdActive(false)}>Restablecer ESD</button>`,
    solutionPattern: 'Patrón State con Enclavamiento Físico (Hold-to-Confirm) y Matriz Interlock',
    goodCode: `// Interfaz con HoldInterlockModal (mantener presionado 3s) y verificación de rol:
<HoldInterlockModal 
  requiredHoldSeconds={3} 
  onConfirm={() => executeCommand(new ResetEsdCommand())} 
/>`,
    appliedInMapaf: 'Componentes HoldInterlockModal.tsx y EsdModal.tsx que exigen confirmación activa de 3 segundos antes de maniobras críticas.'
  },
  {
    id: 'anti-anemic-domain',
    name: 'Anemic Domain Model (Modelo de Dominio Anémico)',
    category: 'Domain-Driven Design (DDD)',
    severity: 'MEDIO',
    symptom: 'Clases de dominio que son meras bolsas de propiedades con getters y setters sin ninguna regla de validación de ingeniería, dispersando la lógica en controladores.',
    scadaRisk: 'Medio: Reglas de validación contradictorias en diferentes endpoints, permitiendo porcentajes de estrangulador absurdos (ej. -20% o 5000%).',
    badCode: `// Entidad anémica sin invariantes:
class Wellhead {
    public float chokeOpening; // Permite $well->chokeOpening = -999.0;
}`,
    solutionPattern: 'Entidades Ricas de Dominio con Invariantes Encapsulados (DDD)',
    goodCode: `// Objeto de Dominio Rico que protege sus propios invariantes:
class ChokePercentage {
    public function __construct(private float $val) {
        if ($val < 0.0 || $val > 100.0) throw new InvalidArgumentException("Rango 0-100%");
    }
}`,
    appliedInMapaf: 'Los dominios en backend_php/domain/ y patrones en backend_php/patterns/ validan automáticamente rangos físicos ISA-18.2.'
  }
];

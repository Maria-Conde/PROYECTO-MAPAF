<?php
/**
 * PROYECTO MAPAF - Capa de Servicio: AuthService
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona la autenticación centralizada, emisión de tokens JWT,
 * validación de sesiones activas y control de acceso basado en roles (RBAC).
 */

require_once __DIR__ . '/JwtService.php';
require_once __DIR__ . '/EventDispatcher.php';
require_once __DIR__ . '/AuditObserver.php';
require_once __DIR__ . '/../domain/ValueObjects.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

class AuthService {
    private UserRepository $userRepo;
    private JwtService $jwtService;

    public function __construct(?UserRepository $userRepo = null, ?JwtService $jwtService = null) {
        $this->userRepo = $userRepo ?: new UserRepository();
        $this->jwtService = $jwtService ?: new JwtService();
    }

    /**
     * Devuelve la lista completa de operadores y roles para cambio de turno
     */
    public function getOperatorsCatalog(): array {
        return [
            'users' => $this->userRepo->getAll(),
            'roles' => $this->userRepo->getRoles()
        ];
    }

    /**
     * Autentica / conmuta un operador por código de empleado y emite un JWT firmado
     */
    public function authenticateOperator(string $code): array {
        $user = $this->userRepo->getByCode($code);
        if (!$user) {
            throw new ValidationException("Operador con código '{$code}' no registrado en la matriz RBAC.");
        }

        // Generar JWT firmado criptográficamente (HS256)
        $token = $this->jwtService->generateToken($user);

        // Respaldar en sesión PHP para navegación visual en navegador sin romper nada
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['user'] = $user;
            $_SESSION['jwt_token'] = $token;
        }

        // Emitir evento de dominio para trazabilidad forense
        EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
            'Autenticación JWT / Conmutación de Operador',
            'SEGURIDAD',
            $user['codigo_empleado'],
            $user['nombre_completo'],
            $user['rol_nombre'],
            'EXITOSO',
            "Sesión autorizada mediante JWT HS256 para sector {$user['sector_asignado']}."
        ));

        return [
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => defined('JWT_EXPIRATION_SECONDS') ? JWT_EXPIRATION_SECONDS : 86400
        ];
    }

    /**
     * Resuelve y valida el operador activo actual desde cabecera Bearer o sesión
     */
    public function getCurrentOperator(): ?OperatorIdentity {
        // 1. Intentar validar token Bearer de la cabecera HTTP
        $bearer = JwtService::extractBearerToken();
        if ($bearer) {
            $validation = $this->jwtService->validate($bearer);
            if ($validation['valid'] && !empty($validation['payload'])) {
                return OperatorIdentity::fromArray($validation['payload']);
            }
        }

        // 2. Fallback de compatibilidad: Sesión PHP activa
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user'])) {
            return OperatorIdentity::fromArray($_SESSION['user']);
        }

        // 3. Fallback al primer operador en turno (Administrador)
        $all = $this->userRepo->getAll();
        if (!empty($all[0])) {
            return OperatorIdentity::fromArray($all[0]);
        }

        return null;
    }

    /**
     * Exige que el operador tenga un permiso RBAC específico
     * 
     * @throws ValidationException
     */
    public function requirePermission(string $permission, ?OperatorIdentity $operator = null): OperatorIdentity {
        $op = $operator ?: $this->getCurrentOperator();
        if (!$op) {
            throw new ValidationException('Autenticación requerida. Presente un token Bearer válido.');
        }

        if (!$op->hasPermission($permission)) {
            EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
                "Acceso Denegado a Permiso '{$permission}'",
                'SEGURIDAD',
                $op->getCode(),
                $op->getName(),
                $op->getRoleCode(),
                'RECHAZADO',
                "El operador no posee el permiso RBAC '{$permission}'."
            ));
            throw new ValidationException("Acceso Denegado: Su rol ({$op->getRoleCode()}) no posee el permiso '{$permission}'.");
        }

        return $op;
    }
}

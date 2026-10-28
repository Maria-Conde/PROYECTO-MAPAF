<?php
/**
 * PROYECTO MAPAF - Controlador: AuthController
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Gestiona la autenticación centralizada, conmutación de turnos y emisión de JWT.
 */

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../config/cors.php';

class AuthController {
    private AuthService $authService;

    public function __construct(?AuthService $authService = null) {
        $this->authService = $authService ?: new AuthService();
    }

    /**
     * Lista todos los usuarios/operadores y emite/refresca el token del operador activo
     */
    public function users(): void {
        $catalog = $this->authService->getOperatorsCatalog();
        $currentOp = $this->authService->getCurrentOperator();

        // Si hay un operador activo, generar o renovar su token JWT
        $token = null;
        if ($currentOp) {
            try {
                $authResult = $this->authService->authenticateOperator($currentOp->getCode());
                $token = $authResult['token'];
            } catch (Throwable $e) {
                // Silently continue
            }
        }

        sendJsonResponse([
            'success'      => true,
            'current_user' => $currentOp ? [
                'id'              => $currentOp->getId(),
                'codigo_empleado' => $currentOp->getCode(),
                'nombre_completo' => $currentOp->getName(),
                'rol_codigo'      => $currentOp->getRoleCode(),
                'permisos'        => $currentOp->getPermissions()
            ] : ($catalog['users'][0] ?? null),
            'token'        => $token,
            'token_type'   => 'Bearer',
            'users'        => $catalog['users'],
            'roles'        => $catalog['roles']
        ]);
    }

    /**
     * Conmuta el operador de turno actual y genera un nuevo JWT firmado con HS256
     */
    public function switchOperator(): void {
        $input = getJsonInput();
        $code = $input['codigo_empleado'] ?? null;

        if (!$code) {
            sendJsonResponse([
                'success' => false,
                'error'   => 'Parámetro obligatorio "codigo_empleado" no suministrado'
            ], 400);
        }

        try {
            $authData = $this->authService->authenticateOperator($code);
            $user = $authData['user'];

            sendJsonResponse([
                'success'    => true,
                'message'    => "Operador cambiado a: {$user['nombre_completo']} ({$user['rol_nombre']})",
                'user'       => $user,
                'token'      => $authData['token'],
                'token_type' => $authData['token_type'],
                'expires_in' => $authData['expires_in']
            ]);
        } catch (ValidationException $e) {
            sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 404);
        } catch (Throwable $e) {
            sendJsonResponse(['success' => false, 'error' => 'Error de autenticación: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Valida el token actual y retorna el perfil del operador autenticado
     */
    public function me(): void {
        $currentOp = $this->authService->getCurrentOperator();
        if (!$currentOp) {
            sendJsonResponse(['success' => false, 'error' => 'No autenticado'], 401);
        }

        sendJsonResponse([
            'success' => true,
            'user'    => [
                'id'              => $currentOp->getId(),
                'codigo_empleado' => $currentOp->getCode(),
                'nombre_completo' => $currentOp->getName(),
                'rol_codigo'      => $currentOp->getRoleCode(),
                'permisos'        => $currentOp->getPermissions()
            ]
        ]);
    }
}

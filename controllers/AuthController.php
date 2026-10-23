<?php
/**
 * PROYECTO MAPAF - Controlador: AuthController
 * SENA ADSO - Ficha 3413988
 * 
 * Gestiona la autenticación, conmutación de turnos y verificación RBAC.
 */

require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/AuditoriaModel.php';
require_once __DIR__ . '/../config/cors.php';

class AuthController {
    private UsuarioModel $usuarioModel;
    private AuditoriaModel $auditoriaModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
        $this->auditoriaModel = new AuditoriaModel();
    }

    /**
     * Lista todos los usuarios/operadores para cambio de turno
     */
    public function users(): void {
        $users = $this->usuarioModel->getAll();
        $roles = $this->usuarioModel->getRoles();

        sendJsonResponse([
            'success'      => true,
            'current_user' => $_SESSION['user'] ?? ($users[0] ?? null),
            'users'        => $users,
            'roles'        => $roles
        ]);
    }

    /**
     * Conmuta el operador de turno actual
     */
    public function switchOperator(): void {
        $input = getJsonInput();
        $code = $input['codigo_empleado'] ?? null;

        if (!$code) {
            sendJsonResponse(['success' => false, 'error' => 'Código de empleado requerido'], 400);
        }

        $user = $this->usuarioModel->getByCode($code);
        if (!$user) {
            sendJsonResponse(['success' => false, 'error' => 'Usuario no encontrado'], 404);
        }

        // Establecer en sesión
        $_SESSION['user'] = $user;

        $this->auditoriaModel->log(
            $user['codigo_empleado'],
            $user['nombre_completo'],
            $user['rol_nombre'],
            'Inicio de Turno / Conmutación de Operador',
            'SEGURIDAD',
            'EXITOSO',
            "Operador activo fijado en consola SCADA para el sector {$user['sector_asignado']}."
        );

        sendJsonResponse([
            'success' => true,
            'message' => "Operador cambiado a: {$user['nombre_completo']} ({$user['rol_nombre']})",
            'user'    => $user
        ]);
    }
}

<?php
/**
 * PROYECTO MAPAF - Módulo de Autenticación & Autorización JWT (JSON Web Token)
 * SENA ADSO · Ficha 3413988
 * 
 * Implementación nativa de JWT (RFC 7519) con algoritmo HMAC SHA-256 (HS256).
 * Diseñado sin dependencias externas para garantizar compatibilidad total con XAMPP / PHP 8.x.
 * 
 * Proporciona:
 * 1. Firma y emisión de tokens con información de operador, rol, sector y permisos RBAC.
 * 2. Verificación criptográfica de firma inalterable y validación de expiración (exp).
 * 3. Middleware de inspección del header HTTP "Authorization: Bearer <token>".
 */

namespace Mapaf\Auth;

use Exception;

class JwtService {
    private static string $secretKey = 'mapaf-scada-adso-3413988-telemetry-secret-key-2026';

    /**
     * Permite sobreescribir la clave secreta desde variables de entorno
     */
    public static function getSecret(): string {
        return getenv('JWT_SECRET') ?: self::$secretKey;
    }

    /**
     * Codificación Base64 URL-safe (RFC 7515 / RFC 7519)
     */
    public static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificación Base64 URL-safe
     */
    public static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Emite un token JWT firmado para un operador SCADA
     * 
     * @param array $payload Claims del usuario (sub, name, role, permissions, sector)
     * @param int $ttlSeconds Tiempo de vida en segundos (por defecto 8 horas = 28800 s, turno de operador)
     * @return string Token JWT en formato header.payload.signature
     */
    public static function generateToken(array $payload, int $ttlSeconds = 28800): string {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
            'iss' => 'MAPAF-SCADA-RTOC',
            'aud' => 'SENA-ADSO-3413988'
        ];

        $issuedAt = time();
        $expiresAt = $issuedAt + $ttlSeconds;

        $claims = array_merge([
            'iat' => $issuedAt,
            'nbf' => $issuedAt,
            'exp' => $expiresAt,
            'jti' => bin2hex(random_bytes(8))
        ], $payload);

        $base64Header = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $base64Payload = self::base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES));

        $signature = hash_hmac('sha256', "{$base64Header}.{$base64Payload}", self::getSecret(), true);
        $base64Signature = self::base64UrlEncode($signature);

        return "{$base64Header}.{$base64Payload}.{$base64Signature}";
    }

    /**
     * Valida y decodifica un token JWT.
     * Retorna el payload si es válido o lanza excepción si es inválido o expirado.
     * 
     * @param string $token
     * @return array Payload verificado
     * @throws Exception
     */
    public static function verifyToken(string $token): array {
        $parts = explode('.', trim($token));
        if (count($parts) !== 3) {
            throw new Exception("Estructura de token JWT inválida. Se esperan 3 segmentos (Header.Payload.Signature).");
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        // 1. Verificar firma criptográfica
        $expectedSignature = hash_hmac('sha256', "{$base64Header}.{$base64Payload}", self::getSecret(), true);
        $providedSignature = self::base64UrlDecode($base64Signature);

        if (!hash_equals($expectedSignature, $providedSignature)) {
            throw new Exception("Firma criptográfica JWT inválida. El token ha sido manipulado o la clave secreta no coincide.");
        }

        // 2. Decodificar Header y Payload
        $header = json_decode(self::base64UrlDecode($base64Header), true);
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        if (!$header || !$payload) {
            throw new Exception("No se pudo decodificar el JSON del token JWT.");
        }

        if (($header['alg'] ?? '') !== 'HS256') {
            throw new Exception("Algoritmo criptográfico no soportado: " . ($header['alg'] ?? 'desconocido'));
        }

        // 3. Validar expiración (exp)
        $now = time();
        if (isset($payload['exp']) && $now > $payload['exp']) {
            $expiredDate = date('Y-m-d H:i:s', $payload['exp']);
            throw new Exception("Token JWT expirado en {$expiredDate}. Por favor inicie sesión nuevamente.");
        }

        // 4. Validar 'not before' (nbf)
        if (isset($payload['nbf']) && $now < $payload['nbf']) {
            throw new Exception("Token JWT aún no es válido.");
        }

        return $payload;
    }

    /**
     * Extrae el token Bearer de las cabeceras HTTP de la petición actual
     */
    public static function extractBearerToken(): ?string {
        $header = null;

        // Apache / Nginx / PHP FastCGI
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $header = $headers['Authorization'];
            } elseif (isset($headers['authorization'])) {
                $header = $headers['authorization'];
            }
        }

        // Alternativa por parámetro de consulta URL (?token=...)
        if (!$header && isset($_GET['token'])) {
            return trim($_GET['token']);
        }

        if ($header && preg_match('/Bearer\s(\S+)/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Valida si el usuario autenticado tiene el permiso requerido
     */
    public static function requirePermission(string $requiredPermission): array {
        $token = self::extractBearerToken();
        if (!$token) {
            throw new Exception("Acceso no autorizado: Se requiere cabecera 'Authorization: Bearer <token>' para esta maniobra.", 401);
        }

        $payload = self::verifyToken($token);
        $permissions = $payload['permissions'] ?? [];

        // Rol Administrador tiene comodín implícito
        if (($payload['roleCode'] ?? '') === 'SYS-ADMIN') {
            return $payload;
        }

        if (!in_array($requiredPermission, $permissions, true)) {
            throw new Exception("Permiso denegado: El operador '{$payload['name']}' no posee el permiso '{$requiredPermission}' requerido por la matriz RBAC.", 403);
        }

        return $payload;
    }
}

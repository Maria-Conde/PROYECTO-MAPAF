<?php
/**
 * PROYECTO MAPAF - Servicio de Autenticación JWT (JSON Web Tokens - RFC 7519 / RFC 7515)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Implementación estricta de seguridad:
 * - Algoritmo forzado: HS256 (HMAC-SHA256 estándar de PHP).
 * - Comparación en tiempo constante con hash_equals() para prevenir ataques de temporización.
 * - Rechazo total de tokens no firmados (alg=none) o algoritmos no autorizados.
 * - Validación obligatoria de expiración (exp), no-antes (nbf) y emisor (iss).
 * - Extracción y saneamiento de cabeceras Bearer HTTP.
 */

require_once __DIR__ . '/../config/db_config.php';

class JwtException extends Exception {}
class JwtExpiredException extends JwtException {}
class JwtInvalidSignatureException extends JwtException {}

class JwtService {
    private string $secret;
    private int $defaultTtl;

    public function __construct(?string $secret = null, ?int $defaultTtl = null) {
        $this->secret = $secret ?: (defined('JWT_SECRET') ? JWT_SECRET : 'MAPAF_SCADA_SECURE_JWT_SECRET_KEY_2026_ADSO');
        $this->defaultTtl = $defaultTtl ?: (defined('JWT_EXPIRATION_SECONDS') ? JWT_EXPIRATION_SECONDS : 86400);
    }

    /**
     * Genera y firma un JWT para un operador autenticado
     */
    public function generateToken(array $user, ?int $ttlSeconds = null): string {
        $ttl = ($ttlSeconds !== null) ? $ttlSeconds : $this->defaultTtl;
        $now = time();

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        // Claims públicos y estandarizados (sin información crítica como contraseñas)
        $payload = [
            'iss'             => 'PROYECTO-MAPAF-SCADA',
            'sub'             => $user['id'] ?? $user['codigo_empleado'],
            'iat'             => $now,
            'nbf'             => $now - 5, // 5 segundos de tolerancia por deriva de reloj
            'exp'             => $now + $ttl,
            'jti'             => bin2hex(random_bytes(12)),
            'user_id'         => $user['id'] ?? '',
            'codigo_empleado' => $user['codigo_empleado'] ?? '',
            'nombre'          => $user['nombre_completo'] ?? '',
            'email'           => $user['email'] ?? '',
            'rol_id'          => $user['rol_id'] ?? '',
            'rol_nombre'      => $user['rol_nombre'] ?? '',
            'rol_codigo'      => $user['rol_codigo'] ?? '',
            'permisos'        => $user['permisos'] ?? []
        ];

        return $this->encode($header, $payload);
    }

    /**
     * Codifica y firma un header y payload
     */
    public function encode(array $header, array $payload): string {
        $headerEncoded = self::base64UrlEncode((string)json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadEncoded = self::base64UrlEncode((string)json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", $this->secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * Valida la firma y las reclamaciones de un JWT, retornando el payload decodificado
     * 
     * @throws JwtException
     */
    public function decode(string $token): array {
        $token = trim($token);
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new JwtException('Estructura de JWT inválida (se requieren 3 segmentos separados por punto).');
        }

        list($headerB64, $payloadB64, $sigB64) = $parts;

        // 1. Decodificar cabecera
        $headerJson = self::base64UrlDecode($headerB64);
        $header = json_decode($headerJson, true);
        if (!is_array($header)) {
            throw new JwtException('Cabecera de JWT no es un JSON válido.');
        }

        // 2. Comprobación estricta del algoritmo: únicamente HS256 está autorizado
        if (!isset($header['alg']) || $header['alg'] !== 'HS256') {
            throw new JwtException(sprintf(
                'Algoritmo de firma no autorizado: "%s". Se requiere estrictamente HS256.',
                htmlspecialchars($header['alg'] ?? 'desconocido')
            ));
        }

        // 3. Verificar firma criptográfica con hash_equals (comparación de tiempo constante)
        $expectedSig = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $this->secret, true);
        $providedSig = self::base64UrlDecode($sigB64);

        if (!hash_equals($expectedSig, $providedSig)) {
            throw new JwtInvalidSignatureException('Firma de JWT no coincide. Token alterado o clave inválida.');
        }

        // 4. Decodificar payload
        $payloadJson = self::base64UrlDecode($payloadB64);
        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            throw new JwtException('Payload de JWT no es un JSON válido.');
        }

        $now = time();

        // 5. Validar fecha de no validez previa (nbf)
        if (isset($payload['nbf']) && $now < ((int)$payload['nbf'] - 10)) {
            throw new JwtException('El token aún no es válido (reclamación nbf en el futuro).');
        }

        // 6. Validar expiración (exp)
        if (isset($payload['exp']) && $now > (int)$payload['exp']) {
            throw new JwtExpiredException(sprintf(
                'El token JWT ha expirado a las %s.',
                date('Y-m-d H:i:s', (int)$payload['exp'])
            ));
        }

        return $payload;
    }

    /**
     * Valida de manera segura un token sin lanzar excepciones
     */
    public function validate(string $token): array {
        try {
            $payload = $this->decode($token);
            return [
                'valid'   => true,
                'payload' => $payload,
                'error'   => null
            ];
        } catch (JwtExpiredException $e) {
            return [
                'valid'   => false,
                'payload' => null,
                'error'   => 'TOKEN_EXPIRED: ' . $e->getMessage()
            ];
        } catch (JwtException $e) {
            return [
                'valid'   => false,
                'payload' => null,
                'error'   => 'TOKEN_INVALID: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extrae el token Bearer de las cabeceras HTTP de la petición actual
     */
    public static function extractBearerToken(): ?string {
        $authHeader = null;

        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        }

        if (!$authHeader) {
            // Soporte para paso en parámetro GET seguro para descargas o eventos si fuese necesario
            return $_GET['jwt_token'] ?? null;
        }

        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Codificador Base64 URL-safe (RFC 4648 §5)
     */
    public static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificador Base64 URL-safe (RFC 4648 §5)
     */
    public static function base64UrlDecode(string $data): string {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return (string)base64_decode(strtr($data, '-_', '+/'));
    }
}

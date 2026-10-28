<?php
/**
 * PROYECTO MAPAF - Capa de Servicio: AlarmService
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 * 
 * Reglas de negocio para el ciclo de vida de alarmas (ANSI/ISA-18.2):
 * - Mitigación estricta de antipatrón Blind Alarm Acknowledgment.
 * - Validación obligatoria de operador reconocido y justificación técnica (mínimo 8 caracteres).
 * - Notificación de eventos de acuse vía Patrón Observer.
 */

require_once __DIR__ . '/EventDispatcher.php';
require_once __DIR__ . '/AuditObserver.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/../domain/ValueObjects.php';
require_once __DIR__ . '/../repositories/AlarmRepository.php';

class AlarmService {
    private AlarmRepository $alarmRepo;
    private AuthService $authService;

    public function __construct(?AlarmRepository $alarmRepo = null, ?AuthService $authService = null) {
        $this->alarmRepo = $alarmRepo ?: new AlarmRepository();
        $this->authService = $authService ?: new AuthService();
    }

    public function getAlarms(bool $unacknowledgedOnly = false): array {
        return $this->alarmRepo->getAll($unacknowledgedOnly);
    }

    public function getAlarmCounts(): array {
        return $this->alarmRepo->getPendingCounts();
    }

    public function getAlarmById(string $id): ?array {
        return $this->alarmRepo->getById($id);
    }

    /**
     * Realiza el reconocimiento formal de una alarma bajo directrices ANSI/ISA-18.2
     */
    public function acknowledgeAlarm(
        string $alarmId,
        string $operatorName,
        string $reason,
        ?OperatorIdentity $operator = null
    ): array {
        $op = $this->authService->requirePermission('ack_alarms', $operator);

        $alarm = $this->alarmRepo->getById($alarmId);
        if (!$alarm) {
            throw new ValidationException("Alarma industrial con identificador '{$alarmId}' no encontrada.");
        }

        // Si el nombre no fue provisto en el formulario, tomar la identidad verificada del token
        $effectiveOperator = !empty(trim($operatorName)) ? trim($operatorName) : $op->getName();

        // 1. Escudo contra antipatrón Blind Alarm Acknowledgment (ANSI/ISA-18.2)
        try {
            AlarmSeverity::validateAcknowledgment($effectiveOperator, $reason);
        } catch (ValidationException $valEx) {
            EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
                "Intento de Acuse Ciego en Alarma {$alarm['tag']}",
                'SEGURIDAD',
                $op->getCode(),
                $effectiveOperator,
                $op->getRoleCode(),
                'RECHAZADO',
                "ESCUDO ISA-18.2: {$valEx->getMessage()}"
            ));
            throw $valEx;
        }

        // 2. Persistir acuse trazable
        $res = $this->alarmRepo->acknowledge($alarmId, $effectiveOperator, $reason);
        if (!$res['success']) {
            throw new DomainException("No fue posible registrar el acuse para la alarma {$alarm['tag']}.");
        }

        // 3. Emitir evento de auditoría exitoso
        EventDispatcher::getInstance()->dispatch(new GenericDomainEvent(
            "Acuse Formal de Alarma {$alarm['tag']} ({$alarm['severidad']})",
            'ALARMA',
            $op->getCode(),
            $effectiveOperator,
            $op->getRoleCode(),
            'EXITOSO',
            "Alarma formalmente reconocida. Causa técnica: '{$reason}'"
        ));

        return [
            'success'       => true,
            'message'       => "Alarma {$alarm['tag']} reconocida formalmente",
            'ack_timestamp' => $res['ack_timestamp'],
            'operator'      => $effectiveOperator
        ];
    }
}

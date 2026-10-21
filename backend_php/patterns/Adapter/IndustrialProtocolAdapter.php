<?php
/**
 * PROYECTO MAPAF - Patrón Estructural: ADAPTER (Adaptador)
 * SENA ADSO · Ficha 3413988
 * 
 * Adapta los protocolos de comunicación industrial legacy (Modbus RTU sobre RS-485
 * con registros Holding de 16 bits y tramas binarias, y OPC-UA con NodeIDs)
 * al formato unificado JSON de telemetría utilizado por MAPAF SCADA.
 */

namespace Mapaf\Patterns\Adapter;

interface IndustrialSourceInterface {
    public function getProtocolName(): string;
    public function getRawData(): array;
}

class ModbusRtuSource implements IndustrialSourceInterface {
    private int $slaveId;
    private int $startingRegister;
    private array $registers16Bit;

    public function __construct(int $slaveId, int $startingRegister, array $registers16Bit) {
        $this->slaveId = $slaveId;
        $this->startingRegister = $startingRegister;
        $this->registers16Bit = $registers16Bit; // Ej: [0x44E7, 0x5000] para IEEE-754 Float 1850.5
    }

    public function getProtocolName(): string { return 'Modbus RTU (RS-485 / TCP)'; }
    public function getRawData(): array {
        return [
            'slaveId' => $this->slaveId,
            'startRegister' => $this->startingRegister,
            'registers' => $this->registers16Bit,
            'hexString' => implode(' ', array_map(fn($r) => sprintf("0x%04X", $r), $this->registers16Bit))
        ];
    }
}

class OpcUaSource implements IndustrialSourceInterface {
    private string $nodeId;
    private float $serverTimestamp;
    private mixed $variantValue;
    private int $statusCode;

    public function __construct(string $nodeId, mixed $variantValue, int $statusCode = 0x00000000) {
        $this->nodeId = $nodeId;
        $this->serverTimestamp = microtime(true);
        $this->variantValue = $variantValue;
        $this->statusCode = $statusCode;
    }

    public function getProtocolName(): string { return 'OPC-UA (IEC 62541)'; }
    public function getRawData(): array {
        return [
            'nodeId' => $this->nodeId,
            'variant' => $this->variantValue,
            'statusCode' => sprintf("0x%08X (Good)", $this->statusCode),
            'sourceTimestamp' => date('Y-m-d\TH:i:s.v\Z')
        ];
    }
}

/**
 * Adaptador SCADA universal
 */
class IndustrialProtocolAdapter {
    public static function adapt(IndustrialSourceInterface $source, string $targetTag, string $targetLabel, string $unit): array {
        $raw = $source->getRawData();
        $convertedValue = 0.0;

        if ($source instanceof ModbusRtuSource) {
            // Decodifica registros de 16 bits en float IEEE-754 o entero escalado
            $regs = $raw['registers'];
            if (count($regs) >= 2) {
                // Combinar 2 registros de 16 bits en un float de 32 bits (Big Endian)
                $binary = pack('nn', $regs[0], $regs[1]);
                $unpacked = unpack('G', $binary); // G = IEEE-754 32-bit big endian
                $convertedValue = round($unpacked[1] ?? 0.0, 1);
            } else if (count($regs) === 1) {
                // Registro entero escalado por 10
                $convertedValue = round($regs[0] / 10.0, 1);
            }
        } else if ($source instanceof OpcUaSource) {
            $convertedValue = round(floatval($raw['variant']), 1);
        }

        return [
            'tag' => $targetTag,
            'label' => $targetLabel,
            'value' => $convertedValue,
            'unit' => $unit,
            'sourceProtocol' => $source->getProtocolName(),
            'rawFrame' => $raw,
            'adaptedAt' => date('H:i:s'),
            'scadaReady' => true
        ];
    }
}

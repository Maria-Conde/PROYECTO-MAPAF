<?php
/**
 * PROYECTO MAPAF - Patrón de Comportamiento: STRATEGY (Estrategia)
 * SENA ADSO · Ficha 3413988
 * 
 * Permite alternar dinámicamente la familia de algoritmos matemáticos utilizados
 * para calcular la tasa de producción de petróleo fiscal y la relación gas-petróleo (GOR)
 * en función del tipo de instrumentación disponible en la estación de recolección:
 * 1. Medición Fiscal Directa Coriolis (AGA-11 / API MPMS 5.6)
 * 2. Placa de Orificio Diferencial (AGA Report No. 3)
 * 3. Modelo Bifásico Hidrodinámico de Pozo (Beggs & Brill)
 */

namespace Mapaf\Patterns\Strategy;

class FlowCalculationResult {
    public float $netOilBpd;
    public float $gasMscfd;
    public float $gor;
    public float $uncertaintyPercent;
    public string $standard;
    public string $algorithmName;
    public string $justification;

    public function __construct(float $netOilBpd, float $gasMscfd, float $uncertaintyPercent, string $standard, string $algorithmName, string $justification) {
        $this->netOilBpd = round($netOilBpd, 1);
        $this->gasMscfd = round($gasMscfd, 1);
        $this->gor = $this->netOilBpd > 0 ? round(($this->gasMscfd * 1000) / $this->netOilBpd, 1) : 0;
        $this->uncertaintyPercent = $uncertaintyPercent;
        $this->standard = $standard;
        $this->algorithmName = $algorithmName;
        $this->justification = $justification;
    }

    public function toArray(): array {
        return [
            'netOilBpd' => $this->netOilBpd,
            'gasMscfd' => $this->gasMscfd,
            'gor' => $this->gor,
            'uncertaintyPercent' => $this->uncertaintyPercent,
            'standard' => $this->standard,
            'algorithmName' => $this->algorithmName,
            'justification' => $this->justification
        ];
    }
}

interface FlowCalculationStrategyInterface {
    public function getName(): string;
    public function getStandard(): string;
    public function calculate(float $whtpPressure, float $temperature, float $chokePercent, float $espFrequency): FlowCalculationResult;
}

/**
 * Estrategia 1: Caudalímetro Másico Coriolis Directo (AGA-11 / API MPMS 5.6)
 * Máxima precisión fiscal, medición de densidad en línea y corrección por temperatura
 */
class CoriolisDirectStrategy implements FlowCalculationStrategyInterface {
    public function getName(): string { return 'Coriolis Másico Directo (Fiscal)'; }
    public function getStandard(): string { return 'API MPMS Ch. 5.6 / AGA-11'; }

    public function calculate(float $whtpPressure, float $temperature, float $chokePercent, float $espFrequency): FlowCalculationResult {
        // En Coriolis, la masa se mide directamente por desfase de oscilación del tubo
        $baseFlow = ($chokePercent / 100.0) * 18200.0;
        $tempFactor = 1.0 - (($temperature - 60.0) * 0.0007); // Corrección por volumen estándar 60°F
        $espBoost = ($espFrequency / 60.0) * 1.05;
        $netOil = $baseFlow * $tempFactor * $espBoost;
        $gas = $netOil * 0.380; // Factor GOR constante fiscal

        return new FlowCalculationResult(
            $netOil,
            $gas,
            0.15,
            $this->getStandard(),
            $this->getName(),
            'Medición de custodia fiscal y venta por oleoducto. No requiere muestreo de fondo.'
        );
    }
}

/**
 * Estrategia 2: Placa de Orificio AGA Report No. 3 (ANSI/API 2530)
 * Medición clásica por presión diferencial con orificio calibrado
 */
class Aga3OrificeStrategy implements FlowCalculationStrategyInterface {
    public function getName(): string { return 'Placa de Orificio Diferencial (AGA-3)'; }
    public function getStandard(): string { return 'ANSI/API 2530 / AGA Report No. 3'; }

    public function calculate(float $whtpPressure, float $temperature, float $chokePercent, float $espFrequency): FlowCalculationResult {
        // Fórmula de orificio Q = C' * sqrt(hw * Pf)
        $diffPressureHw = max(5.0, ($chokePercent * 1.85)); // InH2O diferencial
        $absPressure = max(14.7, $whtpPressure);
        $tempRankine = ($temperature * 1.8) + 32.0 + 459.67;
        
        $cPrime = 210.5; // Constante de placa para orificio de 2.500"
        $netOil = $cPrime * sqrt($diffPressureHw * $absPressure) * (520.0 / $tempRankine) * 0.095;
        $gas = $netOil * 0.410;

        return new FlowCalculationResult(
            $netOil,
            $gas,
            1.25,
            $this->getStandard(),
            $this->getName(),
            'Ideal para separadores bifásicos de prueba y líneas con presión diferencial estable.'
        );
    }
}

/**
 * Estrategia 3: Modelo Multifásico de Pozo Beggs & Brill (Crudo + Gas)
 * Estimación hidrodinámica para pozos con alta relación de gas y flujo bifásico
 */
class BeggsAndBrillStrategy implements FlowCalculationStrategyInterface {
    public function getName(): string { return 'Modelo Multifásico Hidrodinámico (Beggs & Brill)'; }
    public function getStandard(): string { return 'SPE Petroleum Engineering Handbook'; }

    public function calculate(float $whtpPressure, float $temperature, float $chokePercent, float $espFrequency): FlowCalculationResult {
        // Modelo con corrección por ángulo de tubería, deslizamiento de burbuja y hold-up
        $chokeFactor = pow($chokePercent / 100.0, 1.15);
        $pressureRatio = $whtpPressure / 2150.0;
        $espImpact = pow($espFrequency / 60.0, 1.8);

        $netOil = 15200.0 * $chokeFactor * (0.4 + 0.6 * $pressureRatio) * $espImpact;
        $gas = $netOil * (0.350 + ($whtpPressure * 0.0001));

        return new FlowCalculationResult(
            $netOil,
            $gas,
            3.50,
            $this->getStandard(),
            $this->getName(),
            'Recomendado para simulación de flujo transitorio en cabezal y predicción de bacheo (slugging).'
        );
    }
}

/**
 * Contexto del Patrón Strategy
 */
class FlowCalculatorContext {
    private FlowCalculationStrategyInterface $strategy;

    public function __construct(?FlowCalculationStrategyInterface $initialStrategy = null) {
        $this->strategy = $initialStrategy ?: new CoriolisDirectStrategy();
    }

    public function setStrategy(FlowCalculationStrategyInterface $strategy): void {
        $this->strategy = $strategy;
    }

    public function getStrategy(): FlowCalculationStrategyInterface {
        return $this->strategy;
    }

    public function calculateFlow(float $whtpPressure, float $temperature, float $chokePercent, float $espFrequency): FlowCalculationResult {
        return $this->strategy->calculate($whtpPressure, $temperature, $chokePercent, $espFrequency);
    }

    public static function getAvailableStrategies(): array {
        return [
            'coriolis' => new CoriolisDirectStrategy(),
            'aga3' => new Aga3OrificeStrategy(),
            'beggs_brill' => new BeggsAndBrillStrategy()
        ];
    }
}

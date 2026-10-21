/**
 * PROYECTO MAPAF - Implementación de Patrones de Diseño (GoF) en TypeScript
 * SENA ADSO · Ficha 3413988
 * 
 * Implementaciones cliente con interactividad en tiempo real para el laboratorio
 * didáctico y el simulador de sala de control SCADA.
 */

// ============================================================================
// 1. PATRÓN CREACIONAL: SINGLETON (Cliente SCADA Único)
// ============================================================================
export class ScadaTelemetryStore {
  private static instance: ScadaTelemetryStore | null = null;
  private connectionId: string;
  private accessCounter: number = 0;
  private createdAt: string;

  private constructor() {
    this.connectionId = 'STORE-' + Math.random().toString(36).substring(2, 9).toUpperCase();
    this.createdAt = new Date().toLocaleTimeString();
  }

  public static getInstance(): ScadaTelemetryStore {
    if (!ScadaTelemetryStore.instance) {
      ScadaTelemetryStore.instance = new ScadaTelemetryStore();
    }
    ScadaTelemetryStore.instance.accessCounter++;
    return ScadaTelemetryStore.instance;
  }

  public getDiagnostics() {
    return {
      connectionId: this.connectionId,
      accessCounter: this.accessCounter,
      createdAt: this.createdAt,
      pattern: 'Singleton (Creacional)',
    };
  }
}

// ============================================================================
// 2. PATRÓN CREACIONAL: FACTORY METHOD (Fábrica de Sensores e Instrumentos)
// ============================================================================
export interface IndustrialSensor {
  tag: string;
  label: string;
  unit: string;
  target: number;
  lowTrip: number;
  highWarning: number;
  highTrip: number;
  currentValue: number;
  severity: 'nominal' | 'advisory' | 'warning' | 'critical';
  validate: (val: number) => boolean;
  evaluateSeverity: (val: number) => 'nominal' | 'advisory' | 'warning' | 'critical';
}

export class SensorMetricFactory {
  public static createSensor(
    type: 'pressure' | 'temperature' | 'flow' | 'frequency' | 'gor',
    tag: string,
    label: string,
    customTarget?: number
  ): IndustrialSensor {
    const evaluate = (val: number, low: number, warn: number, trip: number) => {
      if (val >= trip || val <= low) return 'critical';
      if (val >= warn) return 'warning';
      return 'nominal';
    };

    switch (type) {
      case 'pressure': {
        const target = customTarget ?? 1842.5;
        const low = 600, warn = 2000, trip = 2400;
        return {
          tag,
          label,
          unit: 'PSI',
          target,
          lowTrip: low,
          highWarning: warn,
          highTrip: trip,
          currentValue: target,
          severity: 'nominal',
          validate: (val: number) => val >= 0 && val <= 5000,
          evaluateSeverity: (val: number) => evaluate(val, low, warn, trip),
        };
      }
      case 'temperature': {
        const target = customTarget ?? 64.2;
        const low = 15, warn = 85, trip = 95;
        return {
          tag,
          label,
          unit: '°C',
          target,
          lowTrip: low,
          highWarning: warn,
          highTrip: trip,
          currentValue: target,
          severity: 'nominal',
          validate: (val: number) => val >= -20 && val <= 200,
          evaluateSeverity: (val: number) => evaluate(val, low, warn, trip),
        };
      }
      case 'flow': {
        const target = customTarget ?? 14820;
        const low = 4000, warn = 16500, trip = 18500;
        return {
          tag,
          label,
          unit: 'BPD',
          target,
          lowTrip: low,
          highWarning: warn,
          highTrip: trip,
          currentValue: target,
          severity: 'nominal',
          validate: (val: number) => val >= 0 && val <= 35000,
          evaluateSeverity: (val: number) => evaluate(val, low, warn, trip),
        };
      }
      case 'frequency': {
        const target = customTarget ?? 58.2;
        const low = 35.0, warn = 63.5, trip = 66.0;
        return {
          tag,
          label,
          unit: 'Hz',
          target,
          lowTrip: low,
          highWarning: warn,
          highTrip: trip,
          currentValue: target,
          severity: 'nominal',
          validate: (val: number) => val >= 30.0 && val <= 70.0,
          evaluateSeverity: (val: number) => evaluate(val, low, warn, trip),
        };
      }
      case 'gor': {
        const target = customTarget ?? 380;
        const low = 100, warn = 520, trip = 650;
        return {
          tag,
          label,
          unit: 'SCF/BBL',
          target,
          lowTrip: low,
          highWarning: warn,
          highTrip: trip,
          currentValue: target,
          severity: 'nominal',
          validate: (val: number) => val >= 0 && val <= 2000,
          evaluateSeverity: (val: number) => evaluate(val, low, warn, trip),
        };
      }
    }
  }
}

// ============================================================================
// 3. PATRÓN DE COMPORTAMIENTO: OBSERVER (Telemetría Reactiva)
// ============================================================================
export interface TelemetryNotification {
  tag: string;
  value: number;
  unit: string;
  severity: 'nominal' | 'advisory' | 'warning' | 'critical';
  timestamp: string;
}

export type ObserverCallback = (event: TelemetryNotification) => void;

export class TelemetryPublisher {
  private observers: Map<string, ObserverCallback> = new Map();

  public subscribe(name: string, callback: ObserverCallback): () => void {
    this.observers.set(name, callback);
    return () => this.observers.delete(name);
  }

  public notify(event: TelemetryNotification): void {
    this.observers.forEach((callback) => callback(event));
  }

  public getSubscriberNames(): string[] {
    return Array.from(this.observers.keys());
  }
}

// ============================================================================
// 4. PATRÓN DE COMPORTAMIENTO: COMMAND (Comando con Deshacer / UNDO)
// ============================================================================
export interface IScadaCommand {
  id: string;
  description: string;
  operator: string;
  timestamp: string;
  execute: () => { success: boolean; message: string; data?: any };
  undo: () => { success: boolean; message: string; data?: any };
}

export class ScadaCommandManager {
  private history: IScadaCommand[] = [];
  private undoneHistory: IScadaCommand[] = [];

  public execute(command: IScadaCommand): { success: boolean; message: string } {
    const result = command.execute();
    if (result.success) {
      this.history.push(command);
    }
    return result;
  }

  public undoLast(): { success: boolean; message: string; undoneCommand?: IScadaCommand } {
    if (this.history.length === 0) {
      return { success: false, message: 'No hay comandos en el historial para deshacer.' };
    }
    const command = this.history.pop()!;
    const result = command.undo();
    if (result.success) {
      this.undoneHistory.push(command);
    }
    return { ...result, undoneCommand: command };
  }

  public getHistory(): IScadaCommand[] {
    return [...this.history].reverse();
  }

  public getCount(): number {
    return this.history.length;
  }
}

// ============================================================================
// 5. PATRÓN DE COMPORTAMIENTO: STRATEGY (Cálculo Fiscal de Caudal)
// ============================================================================
export interface FlowCalculationResult {
  netOilBpd: number;
  gasMscfd: number;
  gor: number;
  uncertaintyPercent: number;
  standard: string;
  algorithmName: string;
  description: string;
}

export interface IFlowStrategy {
  name: string;
  standard: string;
  calculate: (whtp: number, temp: number, choke: number, esp: number) => FlowCalculationResult;
}

export class CoriolisStrategy implements IFlowStrategy {
  name = 'Coriolis Másico Directo (Fiscal)';
  standard = 'API MPMS Ch. 5.6 / AGA-11';
  calculate(whtp: number, temp: number, choke: number, esp: number): FlowCalculationResult {
    const baseFlow = (choke / 100.0) * 18200.0;
    const tempCorrection = 1.0 - (temp - 60.0) * 0.0007;
    const espBoost = (esp / 60.0) * 1.05;
    const netOil = Math.round(baseFlow * tempCorrection * espBoost * 10) / 10;
    const gas = Math.round(netOil * 0.38 * 10) / 10;
    const gor = netOil > 0 ? Math.round((gas * 1000) / netOil) : 0;
    return {
      netOilBpd: netOil,
      gasMscfd: gas,
      gor,
      uncertaintyPercent: 0.15,
      standard: this.standard,
      algorithmName: this.name,
      description: 'Medición directa fiscal por tubo vibratorio Coriolis. Máxima precisión de custodia comercial.',
    };
  }
}

export class Aga3Strategy implements IFlowStrategy {
  name = 'Placa de Orificio Diferencial (AGA-3)';
  standard = 'ANSI/API 2530 / AGA Report No. 3';
  calculate(whtp: number, temp: number, choke: number, _esp: number): FlowCalculationResult {
    const diffHw = Math.max(5.0, choke * 1.85);
    const absP = Math.max(14.7, whtp);
    const tempR = temp * 1.8 + 32.0 + 459.67;
    const cPrime = 210.5;
    const netOil = Math.round(cPrime * Math.sqrt(diffHw * absP) * (520.0 / tempR) * 0.095 * 10) / 10;
    const gas = Math.round(netOil * 0.41 * 10) / 10;
    const gor = netOil > 0 ? Math.round((gas * 1000) / netOil) : 0;
    return {
      netOilBpd: netOil,
      gasMscfd: gas,
      gor,
      uncertaintyPercent: 1.25,
      standard: this.standard,
      algorithmName: this.name,
      description: 'Presión diferencial sobre placa de orificio calibrada de 2.500". Norma tradicional para gas y crudo ligero.',
    };
  }
}

export class BeggsBrillStrategy implements IFlowStrategy {
  name = 'Modelo Multifásico Hidrodinámico (Beggs & Brill)';
  standard = 'SPE Petroleum Handbook';
  calculate(whtp: number, _temp: number, choke: number, esp: number): FlowCalculationResult {
    const chokeFactor = Math.pow(choke / 100.0, 1.15);
    const pRatio = whtp / 2150.0;
    const espImpact = Math.pow(esp / 60.0, 1.8);
    const netOil = Math.round(15200.0 * chokeFactor * (0.4 + 0.6 * pRatio) * espImpact * 10) / 10;
    const gas = Math.round(netOil * (0.35 + whtp * 0.0001) * 10) / 10;
    const gor = netOil > 0 ? Math.round((gas * 1000) / netOil) : 0;
    return {
      netOilBpd: netOil,
      gasMscfd: gas,
      gor,
      uncertaintyPercent: 3.5,
      standard: this.standard,
      algorithmName: this.name,
      description: 'Flujo multifásico con cálculo de colgamiento de líquido (hold-up) y régimen de bacheo transitorio.',
    };
  }
}

// ============================================================================
// 6. PATRÓN DE COMPORTAMIENTO: STATE (Máquina de Estados de Pozo)
// ============================================================================
export type WellStateId = 'PRODUCIENDO' | 'ESTRANGULADO' | 'DISPARADO' | 'CERRADO';

export interface WellStateInfo {
  state: WellStateId;
  label: string;
  color: string;
  canProduce: boolean;
  description: string;
}

export class WellheadStateMachine {
  private currentState: WellStateId = 'PRODUCIENDO';
  private history: { from: WellStateId; to: WellStateId; timestamp: string; reason: string }[] = [];

  constructor(initial: WellStateId = 'PRODUCIENDO') {
    this.currentState = initial;
  }

  public getState(): WellStateId {
    return this.currentState;
  }

  public getStateInfo(): WellStateInfo {
    switch (this.currentState) {
      case 'PRODUCIENDO':
        return {
          state: 'PRODUCIENDO',
          label: 'En Producción Nominal',
          color: '#10b981',
          canProduce: true,
          description: 'Válvula SSV abierta, estrangulador regulado > 25% y parámetros de presión estables.',
        };
      case 'ESTRANGULADO':
        return {
          state: 'ESTRANGULADO',
          label: 'Estrangulado Preventivo',
          color: '#f59e0b',
          canProduce: true,
          description: 'Apertura de Choke < 25% para contener contrapresión en el manifold de recolección.',
        };
      case 'DISPARADO':
        return {
          state: 'DISPARADO',
          label: 'Disparo de Seguridad (Tripped)',
          color: '#f43f5e',
          canProduce: false,
          description: 'Válvulas SSV cerradas por sobrepresión o parada ESD-1. Enclavamiento activo.',
        };
      case 'CERRADO':
        return {
          state: 'CERRADO',
          label: 'Cerrado Seguro (Shut-In)',
          color: '#64748b',
          canProduce: false,
          description: 'Pozo despresurizado y estático listo para arranque controlado o mantenimiento.',
        };
    }
  }

  public transition(target: WellStateId, reason: string): { success: boolean; message: string } {
    // Validar transiciones legales
    if (this.currentState === 'DISPARADO' && target === 'PRODUCIENDO') {
      return {
        success: false,
        message: 'ENCLAVAMIENTO ACTIVO: No puede pasar de DISPARADO directamente a PRODUCIENDO sin antes rearmar a CERRADO.',
      };
    }

    const prev = this.currentState;
    this.currentState = target;
    this.history.unshift({
      from: prev,
      to: target,
      timestamp: new Date().toLocaleTimeString(),
      reason,
    });

    return {
      success: true,
      message: `Transición exitosa: ${prev} ➔ ${target} (${reason})`,
    };
  }

  public getHistory() {
    return this.history;
  }
}

// ============================================================================
// 7. PATRÓN ESTRUCTURAL: ADAPTER (Adaptador Industrial Modbus / OPC-UA)
// ============================================================================
export interface RawModbusFrame {
  slaveId: number;
  registerAddress: number;
  hexBytes: number[];
}

export interface RawOpcUaPacket {
  nodeId: string;
  sourceTimestamp: string;
  variantValue: number;
  statusCode: string;
}

export class IndustrialProtocolAdapter {
  public static adaptModbus(frame: RawModbusFrame, targetTag: string, unit: string): any {
    // Simular decodificación de 2 registros de 16 bits en Float IEEE 754
    const floatValue =
      frame.hexBytes.length >= 2
        ? Math.round(((frame.hexBytes[0] * 256 + frame.hexBytes[1]) / 10.0) * 10) / 10
        : 1850.5;

    return {
      tag: targetTag,
      value: floatValue,
      unit,
      sourceProtocol: 'Modbus RTU (RS-485)',
      rawFrame: {
        slave: frame.slaveId,
        register: frame.registerAddress,
        hex: frame.hexBytes.map((b) => '0x' + b.toString(16).toUpperCase().padStart(4, '0')).join(' '),
      },
      adaptedTimestamp: new Date().toLocaleTimeString(),
      status: 'SCADA_READY',
    };
  }

  public static adaptOpcUa(packet: RawOpcUaPacket, targetTag: string, unit: string): any {
    return {
      tag: targetTag,
      value: packet.variantValue,
      unit,
      sourceProtocol: 'OPC-UA (IEC 62541)',
      rawPacket: {
        nodeId: packet.nodeId,
        status: packet.statusCode,
        sourceTime: packet.sourceTimestamp,
      },
      adaptedTimestamp: new Date().toLocaleTimeString(),
      status: 'SCADA_READY',
    };
  }
}

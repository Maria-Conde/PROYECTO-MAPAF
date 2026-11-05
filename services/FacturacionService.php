<?php
/**
 * PROYECTO MAPAF - Servicio: Licenciamiento & Facturación Industrial
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

class FacturacionService {

    public function getSubscriptionInfo(): array {
        return [
            'empresa'            => 'Ecopetrol & Consorcio Huila Upstream',
            'nit'                => '899.999.068-1',
            'plan_nombre'        => 'Plan Enterprise Upstream SCADA (SPS-01)',
            'costo_mensual_usd'  => 2450.00,
            'estado'             => 'ACTIVA',
            'periodo_vigente'    => 'Noviembre 2026',
            'fecha_proximo_corte'=> '2026-11-28',
            'recursos_contratados'=> [
                'pozos_max'         => 10,
                'pozos_en_uso'      => 6,
                'frecuencia_rtu'    => '1 segundo (Tiempo Real)',
                'islas_despacho'    => 3,
                'almacenamiento'    => '1 Año Historian Redundante',
                'usuarios_concurrentes'=> 'Ilimitados (RBAC Activo)'
            ]
        ];
    }

    public function getInvoicesHistory(): array {
        return [
            [
                'id'         => 'INV-2026-10',
                'periodo'    => 'Octubre 2026',
                'fecha_pago' => '2026-10-28',
                'monto_usd'  => 2450.00,
                'metodo'     => 'Transferencia Bancaria ACH / Bancolombia',
                'estado'     => 'PAGADO',
                'referencia' => 'REF-ACH-88410294'
            ],
            [
                'id'         => 'INV-2026-09',
                'periodo'    => 'Septiembre 2026',
                'fecha_pago' => '2026-09-28',
                'monto_usd'  => 2450.00,
                'metodo'     => 'Orden de Compra Corporativa',
                'estado'     => 'PAGADO',
                'referencia' => 'OC-CORP-99210'
            ],
            [
                'id'         => 'INV-2026-08',
                'periodo'    => 'Agosto 2026',
                'fecha_pago' => '2026-08-28',
                'monto_usd'  => 2450.00,
                'metodo'     => 'Tarjeta Corporativa Visa',
                'estado'     => 'PAGADO',
                'referencia' => 'AUTH-VISA-4412'
            ]
        ];
    }

    public function simularPago(string $metodo, string $referencia): array {
        return [
            'success'    => true,
            'message'    => 'Transacción aprobada exitosamente por pasarela bancaria.',
            'recibo_id'  => 'REC-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'monto_usd'  => 2450.00,
            'metodo'     => $metodo,
            'referencia' => $referencia,
            'timestamp'  => date('Y-m-d H:i:s')
        ];
    }
}

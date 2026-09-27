<?php

namespace App\Services\Finance;

use App\Models\TaxLedgerEntry;

class TaxCalculationEngineService
{
    /**
     * Calcula el desglose fiscal SAT para una orden de venta mexicana (TASK-FIN-02).
     */
    public function computeOrderTaxes(
        float $pvpGross,
        string $orderRef,
        string $gateway = 'stripe',
        ?string $periodMonth = null,
        ?string $cfdiUuid = null
    ): TaxLedgerEntry {
        // Formulas fiscales mexicanas SAT:
        // Base Gravable = PVP / 1.16
        $taxableBase = round($pvpGross / 1.16, 2);
        // IVA Trasladado (16%) = PVP - Base Gravable
        $ivaTransferred = round($pvpGross - $taxableBase, 2);
        // Retenciones Plataformas Tecnologicas SAT (Art. 113-A / 18-J LIVA)
        $retentionIsr1 = round($taxableBase * 0.01, 2); // 1.0% ISR retenido por pasarela
        $retentionIva8 = round($taxableBase * 0.08, 2); // 8.0% IVA retenido por pasarela
        // IVA Neto a pagar al SAT por la transaccion
        $netIvaPayable = round($ivaTransferred - $retentionIva8, 2);

        return TaxLedgerEntry::create([
            'period_month'             => $periodMonth ?: date('Y-m'),
            'transaction_date'         => today(),
            'order_reference'          => $orderRef,
            'pvp_gross_amount'         => $pvpGross,
            'taxable_base'             => $taxableBase,
            'iva_transferred_16'       => $ivaTransferred,
            'iva_creditable_16'        => 0.00,
            'platform_retention_isr_1' => $retentionIsr1,
            'platform_retention_iva_8' => $retentionIva8,
            'net_iva_payable'          => $netIvaPayable,
            'channel_gateway'          => $gateway,
            'cfdi_uuid'                => $cfdiUuid,
        ]);
    }

    /**
     * Genera el consolidado de metricas fiscales para el periodo consultado.
     */
    public function getPeriodTaxSummary(string $periodMonth): array
    {
        $entries = TaxLedgerEntry::where('period_month', $periodMonth)->get();

        $grossRevenue = (float) $entries->sum('pvp_gross_amount');
        $taxableBase = (float) $entries->sum('taxable_base');
        $ivaTransferred = (float) $entries->sum('iva_transferred_16');
        $ivaCreditable = (float) $entries->sum('iva_creditable_16');
        $retentionIsr = (float) $entries->sum('platform_retention_isr_1');
        $retentionIva = (float) $entries->sum('platform_retention_iva_8');
        $netSatIva = (float) $entries->sum('net_iva_payable');

        return [
            'period' => $periodMonth,
            'kpis' => [
                'gross_revenue' => $grossRevenue,
                'taxable_base' => $taxableBase,
                'iva_transferred_16' => $ivaTransferred,
                'iva_creditable_16' => $ivaCreditable,
                'platform_retentions_isr_1' => $retentionIsr,
                'platform_retentions_iva_8' => $retentionIva,
                'net_sat_iva_payable' => $netSatIva,
            ],
            'formulas' => [
                'base' => 'PVP / 1.16',
                'iva_trasladado' => 'PVP - Base Gravable',
                'iva_neto' => 'IVA Trasladado - IVA Acreditable - Retenciones IVA Pasarela',
            ],
        ];
    }
}

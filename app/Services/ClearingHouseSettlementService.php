<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\AllyClearingSettlement;
use App\Models\VendorBill;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClearingHouseSettlementService
{
    /**
     * Calcula el balance neto determinista compensando prospectos vs comisiones de venta (TASK-ALY-04).
     * Fórmula: Saldo Neto = (Leads Dir A x Tarifa) - Comisiones Dir B
     */
    public function calculateNetBalance(Ally|int $ally): array
    {
        $allyModel = $ally instanceof Ally ? $ally : Ally::with(['campaignsDirA.leadCaptures', 'couponsDirB'])->findOrFail($ally);

        // Crédito por prospectos capturados (Dirección A)
        $leadsCount = 0;
        foreach ($allyModel->campaignsDirA as $camp) {
            $leadsCount += $camp->leadCaptures()->count() ?: $camp->leads_count;
        }
        $leadsCredit = (float) $leadsCount * (float) $allyModel->lead_acquisition_cost;

        // Débito por comisiones devengadas de ventas referidas (Dirección B)
        $commissionsDebit = (float) $allyModel->couponsDirB()->sum('commission_earned');

        // Saldo Neto
        $netBalance = $leadsCredit - $commissionsDebit;

        return [
            'ally_id' => $allyModel->id,
            'ally_code' => $allyModel->code,
            'ally_name' => $allyModel->name,
            'leads_count' => $leadsCount,
            'lead_cost_unit' => (float) $allyModel->lead_acquisition_cost,
            'leads_credit' => $leadsCredit,
            'commissions_debit' => $commissionsDebit,
            'net_balance' => $netBalance,
            'is_favorable_to_store' => $netBalance >= 0,
            'formula' => sprintf(
                'Saldo Neto = (%d Leads x $%.2f) - $%.2f Comisiones = $%.2f MXN',
                $leadsCount,
                $allyModel->lead_acquisition_cost,
                $commissionsDebit,
                $netBalance
            ),
        ];
    }

    /**
     * Ejecuta la liquidación del balance contable e inyecta la cuenta por pagar en Finanzas (TASK-INT-19).
     */
    public function executeSettlement(Ally|int $ally, string $settlementType = 'spei_transfer', ?string $notes = null): AllyClearingSettlement
    {
        return DB::transaction(function () use ($ally, $settlementType, $notes) {
            $allyModel = $ally instanceof Ally ? $ally : Ally::findOrFail($ally);
            $calc = $this->calculateNetBalance($allyModel);

            $settlementCode = 'SETTL-' . date('Ym') . '-' . Str::padLeft($allyModel->id, 2, '0');
            $periodLabel = date('01/m/Y') . ' - ' . date('t/m/Y');
            $referenceNumber = 'SPEI-BNX-' . strtoupper(Str::random(8));

            $settlement = AllyClearingSettlement::create([
                'code' => $settlementCode,
                'ally_id' => $allyModel->id,
                'period_label' => $periodLabel,
                'leads_credit' => $calc['leads_credit'],
                'commissions_debit' => $calc['commissions_debit'],
                'net_balance' => $calc['net_balance'],
                'settlement_type' => $settlementType,
                'status' => 'executed',
                'accounting_reference' => $referenceNumber . ($notes ? " ($notes)" : ''),
                'settled_at' => now(),
            ]);

            // 1. Si es transferencia SPEI y hay saldo a favor del aliado, inyectar a Cuentas por Pagar (TASK-INT-19)
            if ($settlementType === 'spei_transfer' && $calc['net_balance'] < 0) {
                VendorBill::create([
                    'bill_number' => 'BILL-' . $settlementCode,
                    'vendor_type' => 'ally_settlement',
                    'vendor_name' => $allyModel->name,
                    'ally_id' => $allyModel->id,
                    'amount' => abs($calc['net_balance']),
                    'bank_clabe' => $allyModel->bank_clabe,
                    'bank_name' => $allyModel->bank_name,
                    'status' => 'scheduled',
                    'due_date' => now()->next('Friday')->setTime(14, 0),
                    'payment_reference' => $referenceNumber,
                ]);
            }

            // 2. Si es Canje en Especie, asentar trazabilidad
            if ($settlementType === 'in_kind_apparel') {
                $settlement->accounting_reference = 'SALIDA_CONVENIO_ALIADO #CANJE-' . strtoupper(Str::random(6));
                $settlement->save();
            }

            // 3. Actualizar saldo en el aliado
            $allyModel->clearing_balance = 0.00;
            $allyModel->save();

            return $settlement;
        });
    }
}

<?php

namespace App\Services\Finance;

use App\Models\OperationalTask;
use App\Models\ThreeWayMatching;
use App\Models\VendorBill;
use DomainException;
use Illuminate\Support\Facades\DB;

class ThreeWayMatchingService
{
    /**
     * Evalua el cotejo de 3 vias Inbound (OC <-> Rampa <-> Factura Proveedor) (TASK-INT-07).
     */
    public function evaluateInboundMatch(
        string $poCode,
        string $supplierId,
        string $supplierName,
        int $poQty,
        float $poTotal,
        int $whReceivedQty,
        int $whDamagedQty,
        string $invoiceNumber,
        int $invoiceQty,
        float $invoiceGross,
        string $inspectedBy = 'Rampa Control Central',
        ?int $vendorBillId = null
    ): ThreeWayMatching {
        return DB::transaction(function () use (
            $poCode,
            $supplierId,
            $supplierName,
            $poQty,
            $poTotal,
            $whReceivedQty,
            $whDamagedQty,
            $invoiceNumber,
            $invoiceQty,
            $invoiceGross,
            $inspectedBy,
            $vendorBillId
        ) {
            $unitPricePo = $poQty > 0 ? ($poTotal / $poQty) : 0.00;
            $netGoodReceived = max(0, $whReceivedQty - $whDamagedQty);

            $hasQtyDiscrepancy = ($netGoodReceived !== $invoiceQty) || ($netGoodReceived !== $poQty);
            $hasPriceDiscrepancy = abs($invoiceGross - ($netGoodReceived * $unitPricePo)) > 0.05;

            $matchStatus = 'matched';
            $speiBlocked = false;
            $debitNoteAmount = 0.00;
            $payableNet = $invoiceGross;
            $reason = null;

            if ($hasQtyDiscrepancy || $hasPriceDiscrepancy) {
                $matchStatus = 'discrepancy_held';
                $speiBlocked = true;
                $missingOrDamagedUnits = ($poQty - $netGoodReceived);
                $debitNoteAmount = round($missingOrDamagedUnits * $unitPricePo, 2);
                $payableNet = round(max(0, $invoiceGross - $debitNoteAmount), 2);
                $reason = "Discrepancia detectada: {$missingOrDamagedUnits} unidades faltantes o mermadas. Retencion preventiva SPEI activa.";

                // Crear tarea operativa para retencion de pago en Cuentas por Pagar
                OperationalTask::firstOrCreate(
                    [
                        'category' => 'finance_matching_hold',
                        'reference_code' => $poCode,
                        'status' => 'pending',
                    ],
                    [
                        'title' => "Bloqueo preventivo SPEI por discrepancia en {$poCode}",
                        'description' => "Retener pago de factura {$invoiceNumber} ({$supplierName}) por MXN \${$invoiceGross}. Se requiere nota de debito compensatoria por MXN \${$debitNoteAmount}.",
                        'priority' => 'high',
                        'due_at' => now()->addDays(3),
                        'action_payload' => [
                            'po_code' => $poCode,
                            'supplier_name' => $supplierName,
                            'invoice_number' => $invoiceNumber,
                            'debit_note_suggested' => $debitNoteAmount,
                            'payable_net_amount' => $payableNet,
                        ],
                    ]
                );
            }

            // Vincular o crear VendorBill en Cuentas por Pagar
            $bill = null;
            if ($vendorBillId) {
                $bill = VendorBill::find($vendorBillId);
            }
            if (!$bill) {
                $bill = VendorBill::where('bill_number', $invoiceNumber)->first();
            }
            if (!$bill) {
                $bill = VendorBill::create([
                    'bill_number' => $invoiceNumber,
                    'vendor_type' => 'apparel_workshop',
                    'vendor_name' => $supplierName,
                    'amount' => $payableNet,
                    'bank_clabe' => '012180004567890123',
                    'bank_name' => 'BBVA Mexico',
                    'status' => 'pending',
                    'is_spei_blocked' => $speiBlocked,
                    'due_date' => now()->addDays(15),
                ]);
            } else {
                $bill->update([
                    'amount' => $payableNet,
                    'is_spei_blocked' => $speiBlocked,
                ]);
            }

            $matching = ThreeWayMatching::create([
                'po_id'                  => $poCode,
                'po_code'                => $poCode,
                'supplier_id'            => $supplierId,
                'supplier_name'          => $supplierName,
                'po_qty'                 => $poQty,
                'po_total_amount'        => $poTotal,
                'warehouse_received_qty' => $whReceivedQty,
                'warehouse_damaged_qty'  => $whDamagedQty,
                'invoice_number'         => $invoiceNumber,
                'invoice_qty'            => $invoiceQty,
                'invoice_gross_amount'   => $invoiceGross,
                'match_status'           => $matchStatus,
                'debit_note_number'      => null,
                'debit_note_amount'      => $debitNoteAmount,
                'payable_net_amount'     => $payableNet,
                'spei_payout_blocked'    => $speiBlocked,
                'discrepancy_reason'     => $reason,
                'inspected_by'           => $inspectedBy,
                'vendor_bill_id'         => $bill->id,
            ]);

            $bill->update(['three_way_matching_id' => $matching->id]);

            return $matching;
        });
    }

    /**
     * Emite una nota de debito para solventar discrepancia y liberar pago SPEI neto.
     */
    public function issueDebitNote(int $matchingId, string $debitNoteNumber, ?float $customDebitAmount = null): ThreeWayMatching
    {
        return DB::transaction(function () use ($matchingId, $debitNoteNumber, $customDebitAmount) {
            $match = ThreeWayMatching::findOrFail($matchingId);

            $debitAmount = $customDebitAmount !== null ? $customDebitAmount : (float) $match->debit_note_amount;
            $newPayable = max(0, (float) $match->invoice_gross_amount - $debitAmount);

            $match->debit_note_number = $debitNoteNumber;
            $match->debit_note_amount = $debitAmount;
            $match->payable_net_amount = round($newPayable, 2);
            $match->match_status = 'debit_note_issued';
            $match->spei_payout_blocked = false; // Liberado tras netear
            $match->discrepancy_reason = "Compensado con Nota de Debito {$debitNoteNumber} por MXN \${$debitAmount}. Pago neto autorizado.";
            $match->save();

            if ($match->vendor_bill_id) {
                $bill = VendorBill::find($match->vendor_bill_id);
                if ($bill) {
                    $bill->update([
                        'amount' => $match->payable_net_amount,
                        'is_spei_blocked' => false,
                        'status' => 'scheduled',
                    ]);
                }
            }

            // Resolver tarea operativa de retencion
            OperationalTask::where('category', 'finance_matching_hold')
                ->where('reference_code', $match->po_code)
                ->where('status', 'pending')
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

            return $match;
        });
    }

    /**
     * Autoriza la dispersion SPEI unicamente si el cotejo de 3 vias esta solventado.
     */
    public function authorizeSpeiPayout(int $matchingId, ?string $paymentReference = null): ThreeWayMatching
    {
        return DB::transaction(function () use ($matchingId, $paymentReference) {
            $match = ThreeWayMatching::findOrFail($matchingId);

            if ($match->spei_payout_blocked) {
                throw new DomainException("No se puede dispersar SPEI: la orden {$match->po_code} cuenta con retencion preventiva activa por discrepancias no compensadas.");
            }

            $ref = $paymentReference ?: ('SPEI-' . strtoupper(uniqid()));

            $match->match_status = 'approved_for_payment';
            $match->save();

            if ($match->vendor_bill_id) {
                $bill = VendorBill::find($match->vendor_bill_id);
                if ($bill) {
                    $bill->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_reference' => $ref,
                        'is_spei_blocked' => false,
                    ]);
                }
            }

            return $match;
        });
    }

    /**
     * Sincroniza una liquidacion B2B de convenio como cuenta por pagar centralizada (TASK-INT-19).
     */
    public function syncAllyClearingAsVendorBill(
        string $settlementFolio,
        string $allyName,
        float $netAmount,
        string $bankClabe = '012180009988776655',
        string $bankName = 'Banorte'
    ): VendorBill {
        return VendorBill::firstOrCreate(
            ['bill_number' => $settlementFolio],
            [
                'vendor_type' => 'ally_settlement',
                'vendor_name' => $allyName,
                'amount' => $netAmount,
                'bank_clabe' => $bankClabe,
                'bank_name' => $bankName,
                'status' => 'scheduled',
                'is_spei_blocked' => false,
                'due_date' => now()->addDays(7),
            ]
        );
    }
}

<?php

namespace App\Listeners\Finance;

use App\Events\Inventory\PurchaseOrderReceived;
use App\Models\VendorBill;
use App\Models\ThreeWayMatching;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class CreateVendorBillOnPoReceived
{
    public function handle(PurchaseOrderReceived $event)
    {
        if (!Schema::hasTable('vendor_bills')) {
            return;
        }

        $po = $event->purchaseOrder;
        $totalCostReceivedInBatch = $event->totalCostReceived;
        $totalUnitsReceivedInBatch = $event->totalUnitsReceived;
        $isFullyReceived = $event->isFullyReceived;

        $operatorName = 'Operador Almacen';
        if ($event->operatorId) {
            $operator = User::find($event->operatorId);
            if ($operator) $operatorName = $operator->name;
        }

        $supplier = $po->supplier ?: ($po->supplier_id ? Supplier::find($po->supplier_id) : null);
        $vendorName = $supplier?->name ?? 'Proveedor General';
        $vendorType = 'wholesale';
        if ($supplier && in_array($supplier->type, ['apparel_workshop', 'cosmetics_lab', 'courier_carrier', 'wholesale', 'dropship_api', 'dropship_scrape', 'local'], true)) {
            $vendorType = $supplier->type;
        }

        $paymentTerms = $supplier?->payment_terms ?? 'contado';
        $dueDays = 30;
        if (preg_match('/(\d+)/', (string) $paymentTerms, $matches)) {
            $dueDays = (int) $matches[1];
        } elseif (in_array(strtolower((string) $paymentTerms), ['contado', 'cash', 'inmediato'], true)) {
            $dueDays = 2;
        }
        $dueDate = now()->addDays($dueDays);

        $taxRate = ($po->subtotal > 0 && $po->tax > 0) ? ((float)$po->tax / (float)$po->subtotal) : 0.16;
        $batchTax = round($totalCostReceivedInBatch * $taxRate, 2);
        $batchGrossAmount = round($totalCostReceivedInBatch + $batchTax, 2);

        $creditDeduction = (float) ($po->credit_deduction_amount ?? 0);
        $payableNetAmount = max(0.00, round($batchGrossAmount - $creditDeduction, 2));

        $paymentRef = 'OC #' . $po->po_number;
        if ($creditDeduction > 0) {
            $paymentRef .= sprintf(' (NC -$%.2f)', $creditDeduction);
        }

        $billNumber = 'BILL-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $po->po_number), 0, 20)) . '-' . $po->id;

        $vendorBill = VendorBill::where(function ($q) use ($po, $billNumber) {
                $q->where('bill_number', $billNumber)
                  ->orWhere('payment_reference', 'LIKE', '%' . $po->po_number . '%');
            })
            ->first();

        if (!$vendorBill) {
            $vendorBill = VendorBill::create([
                'bill_number' => $billNumber,
                'vendor_type' => $vendorType,
                'vendor_name' => $vendorName,
                'supplier_id' => $po->supplier_id,
                'amount' => $payableNetAmount,
                'bank_clabe' => $supplier?->credentials['bank_clabe'] ?? null,
                'bank_name' => $supplier?->credentials['bank_name'] ?? null,
                'status' => 'pending',
                'is_spei_blocked' => false,
                'due_date' => $dueDate,
                'payment_reference' => $paymentRef,
            ]);
        } else {
            $vendorBill->amount = round($vendorBill->amount + $payableNetAmount, 2);
            $vendorBill->save();
        }

        if (Schema::hasTable('three_way_matchings')) {
            $poQty = (int) ($po->total_units ?: ($po->items()->sum('quantity_ordered') ?: $totalUnitsReceivedInBatch));
            $poTotalAmount = (float) ($po->total_amount ?: $batchGrossAmount);
            $totalWhReceived = (int) $po->items()->sum('quantity_received');

            $threeWayMatching = ThreeWayMatching::updateOrCreate(
                [
                    'po_code' => $po->po_number,
                    'supplier_id' => (string) ($po->supplier_id ?? '0'),
                ],
                [
                    'po_id' => (string) $po->id,
                    'supplier_name' => $vendorName,
                    'po_qty' => $poQty,
                    'po_total_amount' => $poTotalAmount,
                    'warehouse_received_qty' => $totalWhReceived,
                    'warehouse_damaged_qty' => 0,
                    'invoice_number' => $billNumber,
                    'invoice_qty' => $totalUnitsReceivedInBatch,
                    'invoice_gross_amount' => $batchGrossAmount,
                    'match_status' => $isFullyReceived ? 'matched' : 'discrepancy_held',
                    'debit_note_number' => $po->credit_agreement_id ? ('NC-' . $po->credit_agreement_id) : null,
                    'debit_note_amount' => $creditDeduction,
                    'payable_net_amount' => $payableNetAmount,
                    'spei_payout_blocked' => !$isFullyReceived,
                    'inspected_by' => $operatorName,
                    'vendor_bill_id' => $vendorBill->id,
                ]
            );

            if (!$vendorBill->three_way_matching_id) {
                $vendorBill->three_way_matching_id = $threeWayMatching->id;
                $vendorBill->save();
            }
        }
    }
}


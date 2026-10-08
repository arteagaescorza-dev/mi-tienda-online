<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseKardex;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OperationalTask;
use App\Models\ShiftLog;
use App\Models\WarehouseRampEntry;
use App\Models\VendorBill;
use App\Models\ThreeWayMatching;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InboundReceiptService
{
    /**
     * Procesa la recepción física de mercancía amparada por una Orden de Compra.
     * Recalcula el Costo Promedio Ponderado (CPP), asienta en Kardex y transiciona dropship a mixto.
     */
    public function receivePurchaseOrder(PurchaseOrder|int $purchaseOrder, array $itemsReceived = [], ?int $userId = null, ?string $notes = null, array $sanitaryData = []): array
    {
        return DB::transaction(function () use ($purchaseOrder, $itemsReceived, $userId, $notes, $sanitaryData) {
            $po = $purchaseOrder instanceof PurchaseOrder 
                ? $purchaseOrder 
                : PurchaseOrder::with(['items', 'supplier'])->findOrFail($purchaseOrder);

            $allPoItems = $po->items;
            $totalUnitsReceivedInBatch = 0;
            $totalCostReceivedInBatch = 0.0;
            $itemsProcessed = [];

            $batchNumber = $sanitaryData['batch_number'] ?? null;
            $expirationDate = $sanitaryData['expiration_date'] ?? null;
            $paoMonths = !empty($sanitaryData['pao_months']) ? (int) $sanitaryData['pao_months'] : null;
            $hasSanitary = !empty($batchNumber) || !empty($expirationDate) || !empty($paoMonths);

            foreach ($allPoItems as $item) {
                // Cantidad recibida para esta línea (por defecto todo lo pendiente si no se especifica)
                $qtyToReceive = $itemsReceived[$item->id]
                    ?? $itemsReceived[strtolower((string) $item->size_name)]
                    ?? ($item->quantity_ordered - $item->quantity_received);
                if ($qtyToReceive <= 0) {
                    continue;
                }

                $item->quantity_received += $qtyToReceive;
                $item->save();

                $totalUnitsReceivedInBatch += $qtyToReceive;
                $lineCost = $qtyToReceive * (float) $item->unit_cost;
                $totalCostReceivedInBatch += $lineCost;

                // 1. Obtener o crear inventario para la variante
                $variant = $item->variant ?: ProductVariant::find($item->product_variant_id);
                $product = $item->product ?: ($variant ? $variant->product : Product::find($item->product_id));

                if ($variant) {
                    $inventory = Inventory::firstOrCreate(
                        ['variant_id' => $variant->id, 'location' => 'warehouse-1'],
                        ['quantity' => 0, 'reserved' => 0, 'type' => 'physical']
                    );

                    $prevQty = $inventory->quantity;
                    $newQty = $prevQty + $qtyToReceive;

                    // 2. Recálculo del Costo Promedio Ponderado (CPP) - TASK-INV-04
                    $prevCost = (float) ($variant->cost ?? ($product ? $product->cost : $item->unit_cost));
                    if ($prevQty > 0 && $prevCost > 0) {
                        $newCpp = (($prevQty * $prevCost) + ($qtyToReceive * (float)$item->unit_cost)) / $newQty;
                    } else {
                        $newCpp = (float) $item->unit_cost;
                    }

                    // Actualizar costo y trazabilidad sanitaria en variante y producto (TASK-INV-06)
                    $variant->cost = $newCpp;
                    if ($hasSanitary) {
                        $attrs = $variant->attributes ?? [];
                        if (!empty($batchNumber)) $attrs['batch_number'] = $batchNumber;
                        if (!empty($expirationDate)) $attrs['expiration_date'] = $expirationDate;
                        if (!empty($paoMonths)) $attrs['pao_months'] = $paoMonths;
                        $attrs['requires_sanitary_control'] = true;
                        $variant->attributes = $attrs;
                    }
                    $variant->save();

                    if ($product) {
                        $product->cost = $newCpp;
                        // 3. Regla de Transición Automática dropship -> both/mixto (TASK-INV-08)
                        if ($product->type === 'dropship') {
                            $product->type = 'both';
                        }
                        $meta = $product->meta ?? [];
                        if ($hasSanitary) {
                            if (!empty($batchNumber)) $meta['batch_number'] = $batchNumber;
                            if (!empty($expirationDate)) $meta['expiration_date'] = $expirationDate;
                            if (!empty($paoMonths)) $meta['pao_months'] = $paoMonths;
                            $meta['requires_sanitary_control'] = true;
                        }

                        // Trazabilidad de Costos MOQ por Lote de Compra (Regla 1)
                        $moqTierLabel = $po->moq_tier_label ?? ($po->po_snapshot['moq_tier_label'] ?? null);
                        if ($moqTierLabel || $item->unit_cost > 0) {
                            $layers = $meta['moq_cost_layers'] ?? [];
                            $layers[] = [
                                'order_id' => $po->po_number,
                                'supplier' => $po->supplier?->name ?? 'Proveedor',
                                'moq_tier_label' => $moqTierLabel ?: 'Compra Estándar',
                                'unit_cost' => (float) $item->unit_cost,
                                'quantity' => $qtyToReceive,
                                'received_at' => now()->toIso8601String(),
                                'batch_number' => $batchNumber,
                            ];
                            $meta['moq_cost_layers'] = $layers;
                            $meta['last_moq_tier'] = $moqTierLabel ?: 'Compra Estándar';
                            $meta['last_moq_cost'] = (float) $item->unit_cost;
                        }

                        $product->meta = $meta;
                        $product->save();
                    }

                    // 4. Asentamiento inmutable en Kardex (inventory_movements)
                    $inventory->quantity = $newQty;
                    $inventory->save();

                    $sanitaryTag = $hasSanitary 
                        ? sprintf(' [Lote: %s | Cad: %s | PAO: %dM]', $batchNumber ?: 'N/A', $expirationDate ?: 'N/A', $paoMonths ?: 12) 
                        : '';
                    $moqTag = !empty($moqTierLabel) ? sprintf(' [Nivel MOQ: %s @ $%.2f]', $moqTierLabel, (float)$item->unit_cost) : '';

                    $movementNote = sprintf(
                        'ENTRADA_COMPRA OC #%s%s - Valuacion CPP: $%0.2f -> $%0.2f (Lote @ $%0.2f)%s%s',
                        $po->po_number,
                        $moqTag,
                        $prevCost,
                        $newCpp,
                        $item->unit_cost,
                        $sanitaryTag,
                        $notes ? " [$notes]" : ''
                    );
                    if (strlen($movementNote) > 250) {
                        $movementNote = substr($movementNote, 0, 247) . '...';
                    }

                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'user_id' => $userId,
                        'quantity' => $qtyToReceive,
                        'quantity_before' => $prevQty,
                        'quantity_after' => $newQty,
                        'reason' => $movementNote,
                    ]);

                    $itemsProcessed[] = [
                        'item_id' => $item->id,
                        'product' => $product ? $product->title : 'SKU #' . $item->product_id,
                        'variant_id' => $variant->id,
                        'quantity_received' => $qtyToReceive,
                        'previous_cost' => $prevCost,
                        'new_cpp' => round($newCpp, 2),
                        'batch_number' => $batchNumber,
                        'expiration_date' => $expirationDate,
                        'pao_months' => $paoMonths,
                    ];
                }
            }

            // 5. Determinar estado de la Orden de Compra
            $isFullyReceived = $po->items()->whereRaw('quantity_received < quantity_ordered')->count() === 0;
            $po->status = $isFullyReceived ? 'received' : 'partially_received';
            $po->received_at = now();
            if ($notes) {
                $po->notes = ($po->notes ? $po->notes . "\n" : '') . "Recepcion Inbound: " . $notes;
            }
            if ($hasSanitary) {
                $sanitaryNote = sprintf('Trazabilidad Sanitaria: Lote %s | Caducidad %s | PAO %dM', $batchNumber ?: 'N/A', $expirationDate ?: 'N/A', $paoMonths ?: 12);
                $po->notes = ($po->notes ? $po->notes . "\n" : '') . $sanitaryNote;
            }
            $po->save();

            // 6. Asentar en Purchase Kardex
            $sanitaryDescTag = $hasSanitary 
                ? sprintf(' (Lote Sanitario: %s, Cad: %s, PAO: %dM)', $batchNumber ?: 'N/A', $expirationDate ?: 'N/A', $paoMonths ?: 12) 
                : '';

            PurchaseKardex::create([
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'movement_type' => 'INBOUND_RECEIPT',
                'description' => sprintf(
                    'Recepcion Inbound de %d unidades amparadas en OC #%s por total de $%.2f MXN%s',
                    $totalUnitsReceivedInBatch,
                    $po->po_number,
                    $totalCostReceivedInBatch,
                    $sanitaryDescTag
                ),
                'total_amount' => $totalCostReceivedInBatch,
            ]);

            // Determinar operador responsable
            $operatorName = 'Operador Almacen';
            if ($userId) {
                $operator = \App\Models\User::find($userId);
                if ($operator) {
                    $operatorName = $operator->name;
                }
            } elseif (auth()->check()) {
                $operatorName = auth()->user()->name;
            }

            // 7. Hub 1 (Action Center & Almacen): Transicionar OperationalTask y registrar en Bitacora de Turno
            $task = null;
            if (Schema::hasTable('operational_tasks')) {
                $task = OperationalTask::where(function ($query) use ($po) {
                        $query->where('reference_code', $po->po_number)
                              ->orWhere('action_payload->po_number', $po->po_number)
                              ->orWhere('action_payload->purchase_order_id', $po->id);
                    })
                    ->where('status', '!=', 'completed')
                    ->latest()
                    ->first();

                if ($task) {
                    $task->status = $isFullyReceived ? 'completed' : 'in_progress';
                    if ($isFullyReceived) {
                        $task->completed_at = now();
                        $task->completed_by = $operatorName;
                    }
                    $task->save();

                    if (Schema::hasTable('shift_logs')) {
                        ShiftLog::create([
                            'operational_task_id' => $task->id,
                            'event_type' => 'inbound_received',
                            'message' => sprintf(
                                'Recepcion Inbound de OC #%s: %d unidades ingresadas a bodega%s.',
                                $po->po_number,
                                $totalUnitsReceivedInBatch,
                                $isFullyReceived ? ' (Orden completada)' : ' (Recepcion parcial)'
                            ),
                            'operator_name' => $operatorName,
                            'metadata' => [
                                'po_id' => $po->id,
                                'po_number' => $po->po_number,
                                'units_received' => $totalUnitsReceivedInBatch,
                                'cost_received' => $totalCostReceivedInBatch,
                                'is_fully_received' => $isFullyReceived,
                                'batch_number' => $batchNumber,
                            ],
                        ]);
                    }
                }
            }

            // 8. Hub 6 (Logistica & Rampa): Liberar entrada de muelle en warehouse_ramp_entries
            $rampEntry = null;
            if (Schema::hasTable('warehouse_ramp_entries')) {
                $cleanPo = preg_replace('/[^A-Za-z0-9]/', '', (string) $po->po_number);

                if ($task && !empty($task->action_payload['ramp_entry_id'])) {
                    $rampEntry = WarehouseRampEntry::find($task->action_payload['ramp_entry_id']);
                }

                if (!$rampEntry) {
                    $rampEntry = WarehouseRampEntry::where('status', 'in_dock')
                        ->where(function ($q) use ($po, $cleanPo) {
                            if (!empty($po->tracking_number)) {
                                $q->orWhere('entry_folio', 'LIKE', '%' . $po->tracking_number . '%');
                            }
                            if (!empty($cleanPo)) {
                                $q->orWhere('entry_folio', 'LIKE', '%' . substr($cleanPo, 0, 15) . '%');
                            }
                            if ($po->supplier && !empty($po->supplier->name)) {
                                $q->orWhere('carrier_or_supplier', 'LIKE', '%' . $po->supplier->name . '%');
                            }
                            if (!empty($po->carrier)) {
                                $q->orWhere('carrier_or_supplier', 'LIKE', '%' . $po->carrier . '%');
                            }
                        })
                        ->latest()
                        ->first();
                }

                if ($rampEntry && $rampEntry->status !== 'cleared') {
                    $rampEntry->status = 'unloaded';
                    $rampEntry->cleared_at = now();
                    $rampEntry->packages_received_count = ($rampEntry->packages_received_count ?: 0) + $totalUnitsReceivedInBatch;
                    $rampEntry->save();
                }
            }

            // 9. Hub 5 (Finanzas & Cuentas por Pagar): Pre-asentar VendorBill y 3-Way Matching
            $vendorBill = null;
            $threeWayMatching = null;
            if (Schema::hasTable('vendor_bills')) {
                $supplier = $po->supplier ?: ($po->supplier_id ? \App\Models\Supplier::find($po->supplier_id) : null);
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

            return [
                'success' => true,
                'po_id' => $po->id,
                'po_number' => $po->po_number,
                'status' => $po->status,
                'units_received' => $totalUnitsReceivedInBatch,
                'total_cost' => $totalCostReceivedInBatch,
                'batch_number' => $batchNumber,
                'expiration_date' => $expirationDate,
                'pao_months' => $paoMonths,
                'items_processed' => $itemsProcessed,
                'operational_task_id' => $task?->id,
                'ramp_entry_id' => $rampEntry?->id,
                'vendor_bill_id' => $vendorBill?->id,
                'three_way_matching_id' => $threeWayMatching?->id,
            ];
        });
    }
}

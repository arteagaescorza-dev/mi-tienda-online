<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseKardex;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

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

                    // 2. Recálculo del Costo Promedio Ponderado (CPP) - TASK-INV-04 & Blindaje Fiscal Hub 5
                    $incomingUnitCost = (float) $item->unit_cost;
                    $prevCost = (float) ($variant->cost ?? ($product ? $product->cost : 0.00));

                    if ($incomingUnitCost > 0) {
                        if ($prevQty > 0 && $prevCost > 0) {
                            $newCpp = round((($prevQty * $prevCost) + ($qtyToReceive * $incomingUnitCost)) / $newQty, 2);
                        } else {
                            $newCpp = round($incomingUnitCost, 2);
                        }
                    } else {
                        // Recepción a costo $0.00 (bonificación comercial o muestra):
                        // Preservar costo previo para evitar distorsión artificial en márgenes contables
                        $referencePrice = (float) ($variant->price ?: ($product ? $product->price : 0.00));
                        $newCpp = $prevCost > 0 ? $prevCost : round($referencePrice * 0.40, 2);
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
            $operatorId = $userId ?: (auth()->check() ? auth()->id() : null);

            // 7. Emitir evento cross-hub delegando tareas a otros módulos
            event(new \App\Events\Inventory\PurchaseOrderReceived(
                $po,
                $itemsReceived,
                $totalUnitsReceivedInBatch,
                $totalCostReceivedInBatch,
                $operatorId,
                $sanitaryData,
                $isFullyReceived
            ));

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
            ];
        });
    }
}

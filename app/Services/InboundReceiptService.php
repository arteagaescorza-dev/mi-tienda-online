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
    public function receivePurchaseOrder(PurchaseOrder|int $purchaseOrder, array $itemsReceived = [], ?int $userId = null, ?string $notes = null): array
    {
        return DB::transaction(function () use ($purchaseOrder, $itemsReceived, $userId, $notes) {
            $po = $purchaseOrder instanceof PurchaseOrder 
                ? $purchaseOrder 
                : PurchaseOrder::with(['items', 'supplier'])->findOrFail($purchaseOrder);

            $allPoItems = $po->items;
            $totalUnitsReceivedInBatch = 0;
            $totalCostReceivedInBatch = 0.0;
            $itemsProcessed = [];

            foreach ($allPoItems as $item) {
                // Cantidad recibida para esta línea (por defecto todo lo pendiente si no se especifica)
                $qtyToReceive = $itemsReceived[$item->id] ?? ($item->quantity_ordered - $item->quantity_received);
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

                    // Actualizar costo en variante y producto
                    $variant->cost = $newCpp;
                    $variant->save();

                    if ($product) {
                        $product->cost = $newCpp;
                        // 3. Regla de Transición Automática dropship -> both/mixto (TASK-INV-08)
                        if ($product->type === 'dropship') {
                            $product->type = 'both';
                        }
                        $product->save();
                    }

                    // 4. Asentamiento inmutable en Kardex (inventory_movements)
                    $inventory->quantity = $newQty;
                    $inventory->save();

                    $movementNote = sprintf(
                        'ENTRADA_COMPRA OC #%s - Valuación CPP: $%0.2f -> $%0.2f (Lote @ $%0.2f) %s',
                        $po->po_number,
                        $prevCost,
                        $newCpp,
                        $item->unit_cost,
                        $notes ? "[$notes]" : ''
                    );

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
                    ];
                }
            }

            // 5. Determinar estado de la Orden de Compra
            $isFullyReceived = $po->items()->whereRaw('quantity_received < quantity_ordered')->count() === 0;
            $po->status = $isFullyReceived ? 'received' : 'partially_received';
            $po->received_at = now();
            if ($notes) {
                $po->notes = ($po->notes ? $po->notes . "\n" : '') . "Recepción Inbound: " . $notes;
            }
            $po->save();

            // 6. Asentar en Purchase Kardex
            PurchaseKardex::create([
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'movement_type' => 'INBOUND_RECEIPT',
                'description' => sprintf(
                    'Recepción Inbound de %d unidades amparadas en OC #%s por total de $%.2f MXN',
                    $totalUnitsReceivedInBatch,
                    $po->po_number,
                    $totalCostReceivedInBatch
                ),
                'total_amount' => $totalCostReceivedInBatch,
            ]);

            return [
                'success' => true,
                'po_id' => $po->id,
                'po_number' => $po->po_number,
                'status' => $po->status,
                'units_received' => $totalUnitsReceivedInBatch,
                'total_cost' => $totalCostReceivedInBatch,
                'items_processed' => $itemsProcessed,
            ];
        });
    }
}

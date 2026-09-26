<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseKardex;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $torzo = Supplier::where('name', 'Torzo')->first();
        $adrenaline = Supplier::where('name', 'Adrenaline Forte')->first();

        if (!$torzo || !$adrenaline) {
            return;
        }

        // 1. PO Torzo (En Tránsito)
        $torzoProd = Product::with('variants')
            ->whereHas('supplierProducts', fn ($q) => $q->where('supplier_id', $torzo->id))
            ->first();

        if ($torzoProd) {
            $po1 = PurchaseOrder::updateOrCreate(
                ['po_number' => 'PO-2026-001'],
                [
                    'supplier_id' => $torzo->id,
                    'status' => 'in_transit',
                    'currency' => 'MXN',
                    'sent_at' => now()->subDays(2),
                    'estimated_delivery_at' => now()->addDays(1),
                    'notes' => 'FedEx Express (Guía #FX-882910-MX)',
                ]
            );

            $po1->items()->delete();
            $totalUnits1 = 0;
            $subtotal1 = 0;

            foreach ($torzoProd->variants as $variant) {
                $rawSize = data_get($variant->attributes, 'size', 'M');
                $qty = in_array(strtolower($rawSize), ['m', 'g', 'l']) ? 15 : 8;
                $unitCost = (float) ($variant->cost ?: ($torzoProd->cost ?: ($torzoProd->price * 0.5)));
                $lineTotal = $qty * $unitCost;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po1->id,
                    'product_id' => $torzoProd->id,
                    'product_variant_id' => $variant->id,
                    'size_name' => (string) $rawSize,
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                ]);

                $totalUnits1 += $qty;
                $subtotal1 += $lineTotal;
            }

            $tax1 = round($subtotal1 * 0.16, 2);
            $po1->update([
                'total_units' => $totalUnits1,
                'subtotal' => $subtotal1,
                'tax' => $tax1,
                'total_amount' => $subtotal1 + $tax1,
            ]);

            PurchaseKardex::firstOrCreate(
                [
                    'purchase_order_id' => $po1->id,
                    'movement_type' => 'PO_CREATED',
                ],
                [
                    'supplier_id' => $torzo->id,
                    'description' => "Emisión de PO #{$po1->po_number} a Torzo ({$totalUnits1} uds, {$torzoProd->title})",
                    'total_amount' => $subtotal1 + $tax1,
                ]
            );
        }

        // 2. PO Adrenaline Forte (Confirmada / Por Despachar)
        $adrenProd = Product::with('variants')
            ->whereHas('supplierProducts', fn ($q) => $q->where('supplier_id', $adrenaline->id))
            ->first();

        if ($adrenProd) {
            $po2 = PurchaseOrder::updateOrCreate(
                ['po_number' => 'PO-2026-002'],
                [
                    'supplier_id' => $adrenaline->id,
                    'status' => 'confirmed',
                    'currency' => 'MXN',
                    'sent_at' => now()->subDay(),
                    'estimated_delivery_at' => now()->addDays(3),
                    'notes' => 'DHL Express (Guía #DHL-994012-MX)',
                ]
            );

            $po2->items()->delete();
            $totalUnits2 = 0;
            $subtotal2 = 0;

            foreach ($adrenProd->variants as $variant) {
                $rawSize = data_get($variant->attributes, 'size', 'M');
                $qty = in_array(strtolower($rawSize), ['m', 'g', 'l']) ? 12 : 6;
                $unitCost = (float) ($variant->cost ?: ($adrenProd->cost ?: ($adrenProd->price * 0.5)));
                $lineTotal = $qty * $unitCost;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po2->id,
                    'product_id' => $adrenProd->id,
                    'product_variant_id' => $variant->id,
                    'size_name' => (string) $rawSize,
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineTotal,
                ]);

                $totalUnits2 += $qty;
                $subtotal2 += $lineTotal;
            }

            $tax2 = round($subtotal2 * 0.16, 2);
            $po2->update([
                'total_units' => $totalUnits2,
                'subtotal' => $subtotal2,
                'tax' => $tax2,
                'total_amount' => $subtotal2 + $tax2,
            ]);

            PurchaseKardex::firstOrCreate(
                [
                    'purchase_order_id' => $po2->id,
                    'movement_type' => 'PO_CREATED',
                ],
                [
                    'supplier_id' => $adrenaline->id,
                    'description' => "Emisión de PO #{$po2->po_number} a Adrenaline Forte ({$totalUnits2} uds, {$adrenProd->title})",
                    'total_amount' => $subtotal2 + $tax2,
                ]
            );
        }
    }
}

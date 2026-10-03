<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\SupplierMoqSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierMoqSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_moq_sheets_including_torzo_default(): void
    {
        $response = $this->getJson('/api/v1/inventory/settings/moq-sheets');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'supplier_name',
                        'title',
                        'tiers',
                        'rows',
                        'is_active',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('supplier_moq_sheets', [
            'supplier_name' => 'Torzo B2B',
            'title' => 'Torzo Precios Mayoreo 2026',
        ]);
    }

    public function test_can_store_and_update_custom_moq_sheet(): void
    {
        $sheetPayload = [
            'supplier_name' => 'ModaIntima Textil MX',
            'title' => 'Tabulador Mayoreo Algodón 2026',
            'validity' => 'Julio 2026',
            'currency' => 'MXN',
            'tiers' => [
                ['key' => 'menudeo', 'label' => 'Menudeo', 'min_qty' => 1],
                ['key' => 'moq_60', 'label' => '60 prendas', 'min_qty' => 60],
                ['key' => 'moq_120', 'label' => '120 prendas', 'min_qty' => 120],
            ],
            'rows' => [
                [
                    'category' => 'Boxers',
                    'prices' => ['menudeo' => 150, 'moq_60' => 85, 'moq_120' => 75],
                ],
            ],
            'is_active' => true,
        ];

        $res = $this->postJson('/api/v1/inventory/settings/moq-sheets', $sheetPayload);
        $res->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $id = $res->json('data.id');

        $updateRes = $this->putJson("/api/v1/inventory/settings/moq-sheets/{$id}", [
            'title' => 'Tabulador Mayoreo Algodón Actualizado',
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('data.title', 'Tabulador Mayoreo Algodón Actualizado');
    }

    public function test_rule_1_product_tracks_multiple_moq_cost_layers_from_different_purchases(): void
    {
        $supplier = Supplier::create([
            'name' => 'Torzo B2B',
            'contact_name' => 'Carlos Mendoza',
            'email' => 'b2b@torzo.com',
            'phone' => '+52 55 1234 5678',
            'currency' => 'MXN',
            'lead_time_days' => 5,
        ]);

        $product = \App\Models\Product::create([
            'title' => 'Boxer Torzo Pro',
            'slug' => 'boxer-torzo-pro',
            'sku' => 'TRZ-BX-01',
            'price' => 280.00,
            'cost' => 140.00,
            'type' => 'physical',
            'status' => 'active',
            'meta' => [],
        ]);

        $variant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'title' => 'Talla M',
            'sku' => 'TRZ-BX-01-M',
            'price' => 280.00,
            'cost' => 140.00,
        ]);

        $inventory = \App\Models\Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 0,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        // Compra 1: 50 unidades al nivel MOQ 50 ($140/u)
        $po1 = \App\Models\PurchaseOrder::create([
            'po_number' => 'OC-MOQ-50',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 50,
            'total_amount' => 7000.00,
            'moq_tier_label' => '50 prendas',
        ]);
        $poItem1 = \App\Models\PurchaseOrderItem::create([
            'purchase_order_id' => $po1->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => 'M',
            'quantity_ordered' => 50,
            'quantity_received' => 0,
            'unit_cost' => 140.00,
            'total_cost' => 7000.00,
        ]);

        $service = app(\App\Services\InboundReceiptService::class);
        $res1 = $service->receivePurchaseOrder($po1, [$poItem1->id => 50], null, 'Recepción Lote Escala 50');
        $this->assertTrue($res1['success']);

        $product->refresh();
        $this->assertNotEmpty($product->meta['moq_cost_layers']);
        $this->assertCount(1, $product->meta['moq_cost_layers']);
        $this->assertEquals('50 prendas', $product->meta['moq_cost_layers'][0]['moq_tier_label']);
        $this->assertEquals(140.00, $product->meta['moq_cost_layers'][0]['unit_cost']);

        // Compra 2: 200 unidades al nivel MOQ 200 ($130/u) más barato
        $po2 = \App\Models\PurchaseOrder::create([
            'po_number' => 'OC-MOQ-200',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 200,
            'total_amount' => 26000.00,
            'moq_tier_label' => '200 prendas',
        ]);
        $poItem2 = \App\Models\PurchaseOrderItem::create([
            'purchase_order_id' => $po2->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => 'M',
            'quantity_ordered' => 200,
            'quantity_received' => 0,
            'unit_cost' => 130.00,
            'total_cost' => 26000.00,
        ]);

        $res2 = $service->receivePurchaseOrder($po2, [$poItem2->id => 200], null, 'Recepción Lote Escala 200');
        $this->assertTrue($res2['success']);

        // Trazabilidad de Costos MOQ (Regla 1): Un mismo producto con capas de costo diferentes
        $product->refresh();
        $layers = $product->meta['moq_cost_layers'];
        $this->assertCount(2, $layers);
        $this->assertEquals('50 prendas', $layers[0]['moq_tier_label']);
        $this->assertEquals(140.00, $layers[0]['unit_cost']);
        $this->assertEquals('200 prendas', $layers[1]['moq_tier_label']);
        $this->assertEquals(130.00, $layers[1]['unit_cost']);

        // Verificación de recálculo de CPP: (50 * 140 + 200 * 130) / 250 = (7000 + 26000) / 250 = 33000 / 250 = 132.00
        $variant->refresh();
        $this->assertEquals(132.00, (float) $product->cost);
        $this->assertEquals(132.00, (float) $variant->cost);
    }
}

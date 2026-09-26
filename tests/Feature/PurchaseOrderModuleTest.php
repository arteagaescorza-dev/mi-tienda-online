<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_inventory_displays_real_purchase_orders_and_suppliers(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $supplier = Supplier::factory()->create(['name' => 'Torzo Test Supplier']);
        $product = Product::factory()->create(['title' => 'Boxer Torzo Pro', 'cost' => 120.00]);
        $variant = ProductVariant::factory()->for($product)->create(['attributes' => ['size' => 'M']]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-TEST',
            'supplier_id' => $supplier->id,
            'status' => 'in_transit',
            'total_units' => 20,
            'subtotal' => 2400.00,
            'tax' => 384.00,
            'total_amount' => 2784.00,
            'currency' => 'MXN',
            'notes' => 'FedEx Express Guía Test',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => 'M',
            'quantity_ordered' => 20,
            'quantity_received' => 0,
            'unit_cost' => 120.00,
            'total_cost' => 2400.00,
        ]);

        $response = $this->actingAs($user)->get('/admin/inventario-catalogo');

        $response->assertOk();
        $response->assertSee('PO-2026-TEST');
        $response->assertSee('Torzo Test Supplier');
        $response->assertSee('serverPurchaseOrders');
        $response->assertSee('serverSuppliers');
        $response->assertSee('serverFuturePurchaseOrders');
    }

    public function test_inbound_receipt_api_receives_purchase_order_and_updates_real_inventory(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Torzo Inbound']);
        $product = Product::factory()->create(['title' => 'Jockstrap Test', 'type' => 'dropship', 'cost' => 100.00]);
        $variant = ProductVariant::factory()->for($product)->create(['attributes' => ['size' => 'M']]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 5,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-INBOUND-001',
            'supplier_id' => $supplier->id,
            'status' => 'in_transit',
            'total_units' => 15,
            'total_amount' => 1500.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => 'M',
            'quantity_ordered' => 15,
            'quantity_received' => 0,
            'unit_cost' => 100.00,
            'total_cost' => 1500.00,
        ]);

        $response = $this->postJson("/api/v1/inventory/purchases/orders/{$po->id}/receive", [
            'items' => ['m' => 15],
            'notes' => 'Recepción física completa en andén',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => 'received',
            'units_received' => 15,
        ]);

        // Stock físico debe haber pasado de 5 a 20
        $inventory->refresh();
        $this->assertEquals(20, $inventory->quantity);

        // Producto pasa de dropship a both (mixto)
        $product->refresh();
        $this->assertEquals('both', $product->type);

        // Debe haberse registrado en Kardex de inventario
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => 15,
            'quantity_before' => 5,
            'quantity_after' => 20,
        ]);

        // Debe haberse registrado en Kardex de compras
        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $po->id,
            'movement_type' => 'INBOUND_RECEIPT',
        ]);
    }
}

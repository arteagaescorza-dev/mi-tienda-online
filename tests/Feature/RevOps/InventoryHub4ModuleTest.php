<?php

namespace Tests\Feature\RevOps;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseKardex;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierCreditAgreement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryHub4ModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_pending_credit_agreements(): void
    {
        $supplier = Supplier::create([
            'name' => 'Silk & Cotton Iberia SL',
            'contact_email' => 'ventas@silkcotton.com',
        ]);

        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $supplier->id,
            'reference_folio' => 'NC-SILK-2026-03',
            'agreement_type' => 'shortage_discount',
            'amount' => 1160.00,
            'status' => 'pending',
            'resolution_notes' => 'Merma textil en lote previo',
        ]);

        $response = $this->getJson('/api/v1/inventory/purchases/credit-agreements');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment([
                'id' => $agreement->id,
                'supplier' => 'Silk & Cotton Iberia SL',
                'ref' => 'NC-SILK-2026-03',
                'amount' => 1160.00,
                'status' => 'Pendiente de Aplicar',
            ]);
    }

    public function test_can_store_purchase_order_with_items_and_kardex(): void
    {
        $supplier = Supplier::create([
            'name' => 'Torzo B2B',
            'contact_email' => 'pedidos@torzo.com',
        ]);

        $product = Product::create([
            'sku' => 'BOX-PIMA-001',
            'title' => 'Boxer Brief Algodon Pima Negro',
            'slug' => 'boxer-brief-algodon-pima-negro',
            'price' => 380.00,
            'cost' => 120.00,
            'type' => 'dropship',
        ]);

        $variantM = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'BOX-PIMA-001-M',
            'price' => 380.00,
            'attributes' => ['size' => 'M'],
        ]);

        $variantG = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'BOX-PIMA-001-G',
            'price' => 380.00,
            'attributes' => ['size' => 'G'],
        ]);

        $payload = [
            'po_number' => 'PO-2026-9901',
            'supplier_id' => $supplier->id,
            'notes' => 'Pedido de reabastecimiento regular',
            'payment_terms' => 'Credito Comercial 30 dias',
            'items' => [
                [
                    'sku' => 'BOX-PIMA-001',
                    'product_name' => 'Boxer Brief Algodon Pima Negro',
                    'unit_cost' => 100.00,
                    'sizes' => [
                        'm' => 10,
                        'g' => 15,
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/inventory/purchases/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.poNumber', 'PO-2026-9901')
            ->assertJsonPath('order.totalPieces', 25)
            ->assertJsonPath('order.subtotal', 2500)
            ->assertJsonPath('order.tax', 400)
            ->assertJsonPath('order.total', 2900);

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-2026-9901',
            'supplier_id' => $supplier->id,
            'total_units' => 25,
            'subtotal' => 2500.00,
            'tax' => 400.00,
            'total_amount' => 2900.00,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'size_name' => 'M',
            'quantity_ordered' => 10,
            'unit_cost' => 100.00,
            'total_cost' => 1000.00,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'size_name' => 'G',
            'quantity_ordered' => 15,
            'unit_cost' => 100.00,
            'total_cost' => 1500.00,
        ]);

        $this->assertDatabaseHas('purchase_kardex', [
            'supplier_id' => $supplier->id,
            'movement_type' => 'PO_CREATED',
            'total_amount' => 2900.00,
        ]);
    }

    public function test_can_store_purchase_order_with_credit_agreement_deduction(): void
    {
        $supplier = Supplier::create([
            'name' => 'ModaIntima Textil MX',
            'contact_email' => 'b2b@modaintima.mx',
        ]);

        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $supplier->id,
            'reference_folio' => 'NC-MODA-2026-01',
            'agreement_type' => 'shortage_discount',
            'amount' => 500.00,
            'status' => 'pending',
            'resolution_notes' => 'Bonificacion acordada por faltante en rampa de entrega previa',
        ]);

        $payload = [
            'po_number' => 'PO-2026-9902',
            'supplier_id' => $supplier->id,
            'has_credit_deduction' => true,
            'credit_agreement_id' => $agreement->id,
            'items' => [
                [
                    'sku' => 'LENC-MODAL-002',
                    'product_name' => 'Bikini Encaje Modal',
                    'unit_cost' => 200.00,
                    'qty' => 10, // Total bruto: 2000.00
                ],
            ],
        ];

        // Bruto: 2,000.00
        // Deduccion: 500.00
        // Gravable: 1,500.00
        // IVA 16%: 240.00
        // Total: 1,740.00
        $response = $this->postJson('/api/v1/inventory/purchases/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.subtotal', 1500)
            ->assertJsonPath('order.tax', 240)
            ->assertJsonPath('order.total', 1740)
            ->assertJsonPath('order.deductionAmount', 500);

        // El acuerdo debe quedar aplicado
        $agreement->refresh();
        $this->assertEquals('applied', $agreement->status);
        $this->assertNotNull($agreement->purchase_order_id);

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-2026-9902',
            'subtotal' => 1500.00,
            'tax' => 240.00,
            'total_amount' => 1740.00,
        ]);

        $this->assertDatabaseHas('purchase_kardex', [
            'movement_type' => 'PO_CREATED',
            'total_amount' => 1740.00,
        ]);
    }

    public function test_can_dispatch_purchase_order(): void
    {
        $supplier = Supplier::create([
            'name' => 'Torzo Fabricante',
            'contact_email' => 'despacho@torzo.com',
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-7777',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 50,
            'subtotal' => 5000.00,
            'tax' => 800.00,
            'total_amount' => 5800.00,
        ]);

        $payload = [
            'carrier' => 'DHL Express Nacional',
            'tracking_number' => 'DHL-TRACK-99120',
            'recipient_email' => 'taller@torzo.com',
            'notes' => 'Embarque consolidado con guia prioritaria.',
        ];

        $response = $this->postJson('/api/v1/inventory/purchases/orders/' . $po->id . '/dispatch', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('po_number', 'PO-2026-7777')
            ->assertJsonPath('status', 'En Camino');

        $po->refresh();
        $this->assertEquals('in_transit', $po->status);
        $this->assertNotNull($po->sent_at);
        $this->assertStringContainsString('DHL Express Nacional', $po->notes);
        $this->assertStringContainsString('DHL-TRACK-99120', $po->notes);

        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $po->id,
            'movement_type' => 'PO_SENT',
            'total_amount' => 5800.00,
        ]);
    }

    public function test_portal_inventory_hydrates_credit_agreements(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        $supplier = Supplier::create([
            'name' => 'Proveedor Acuerdos B2B',
            'contact_email' => 'acuerdos@proveedor.com',
        ]);

        SupplierCreditAgreement::create([
            'supplier_id' => $supplier->id,
            'reference_folio' => 'NC-ACUERDO-PRUEBA',
            'amount' => 750.00,
            'status' => 'pending',
            'resolution_notes' => 'Acuerdo por retardo logistico',
        ]);

        $response = $this->actingAs($user)->get('/admin/inventario-catalogo');

        $response->assertOk()
            ->assertSee('NC-ACUERDO-PRUEBA')
            ->assertSee('Proveedor Acuerdos B2B')
            ->assertSee('window.serverCreditAgreements', false);
    }
}

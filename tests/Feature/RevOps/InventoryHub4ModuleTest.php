<?php

namespace Tests\Feature\RevOps;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBundle;
use App\Models\BundleItem;
use App\Models\BomDeficitProcurement;
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

    public function test_can_get_bundles_with_bottleneck_capacity(): void
    {
        $prod1 = Product::create([
            'sku' => 'COMP-BOM-001',
            'title' => 'Boxer Micro-Modal',
            'slug' => 'boxer-micro-modal',
            'price' => 350.00,
            'cost' => 110.00,
            'type' => 'dropship',
        ]);
        $var1 = ProductVariant::create([
            'product_id' => $prod1->id,
            'sku' => 'COMP-BOM-001-M',
            'price' => 350.00,
            'attributes' => ['size' => 'M'],
        ]);
        Inventory::create([
            'variant_id' => $var1->id,
            'location' => 'A-01-A',
            'quantity' => 50,
            'reserved' => 0,
        ]);

        $prod2 = Product::create([
            'sku' => 'COMP-BOM-002',
            'title' => 'Jabon Intimo pH Balance',
            'slug' => 'jabon-intimo-ph-balance',
            'price' => 180.00,
            'cost' => 60.00,
            'type' => 'dropship',
        ]);
        $var2 = ProductVariant::create([
            'product_id' => $prod2->id,
            'sku' => 'COMP-BOM-002-U',
            'price' => 180.00,
            'attributes' => ['size' => 'U'],
        ]);
        Inventory::create([
            'variant_id' => $var2->id,
            'location' => 'C-01-A',
            'quantity' => 14,
            'reserved' => 0,
        ]);

        $bundle = ProductBundle::create([
            'name' => 'Caja Mensual Marte Nivel 1',
            'slug' => 'SUB-BOX-MARTE-LVL1',
            'bundle_type' => 'box_subscription',
            'regular_price' => 600.00,
            'bundle_price' => 499.00,
            'is_active' => true,
        ]);

        BundleItem::create([
            'product_bundle_id' => $bundle->id,
            'product_id' => $prod1->id,
            'product_variant_id' => $var1->id,
            'quantity' => 2,
        ]);

        BundleItem::create([
            'product_bundle_id' => $bundle->id,
            'product_id' => $prod2->id,
            'product_variant_id' => $var2->id,
            'quantity' => 1,
        ]);

        // Comp 1: 50 available / 2 per box = 25 boxes
        // Comp 2: 14 available / 1 per box = 14 boxes -> Bottleneck is 14 boxes!

        $response = $this->getJson('/api/v1/inventory/bundles');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment([
                'id' => $bundle->id,
                'sku' => 'SUB-BOX-MARTE-LVL1',
                'name' => 'Caja Mensual Marte Nivel 1',
                'bottleneck_capacity' => 14,
            ]);
    }

    public function test_can_store_and_update_bundle_recipe(): void
    {
        $product = Product::create([
            'sku' => 'ITEM-RECIPE-01',
            'title' => 'Jockstrap Athletic Carbon',
            'slug' => 'jockstrap-athletic-carbon',
            'price' => 390.00,
            'cost' => 130.00,
            'type' => 'dropship',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'ITEM-RECIPE-01-M',
            'price' => 390.00,
            'attributes' => ['size' => 'm'],
        ]);

        $payload = [
            'sku' => 'KIT-DEPORTE-PRO',
            'name' => 'Kit Deportivo Pro Edicion Limitada',
            'type' => 'promo',
            'regular_price' => 850.00,
            'bundle_price' => 699.00,
            'is_active' => true,
            'components' => [
                [
                    'sku' => 'ITEM-RECIPE-01',
                    'size' => 'm',
                    'qty' => 3,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/inventory/bundles', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('bundle.sku', 'KIT-DEPORTE-PRO')
            ->assertJsonPath('bundle.name', 'Kit Deportivo Pro Edicion Limitada')
            ->assertJsonPath('bundle.components_count', 1);

        $this->assertDatabaseHas('product_bundles', [
            'slug' => 'KIT-DEPORTE-PRO',
            'name' => 'Kit Deportivo Pro Edicion Limitada',
            'bundle_type' => 'pack_multiple',
            'bundle_price' => 699.00,
            'regular_price' => 850.00,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('bundle_items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_can_audit_bom_deficit_and_generate_draft_purchase_orders(): void
    {
        $supplier = Supplier::create([
            'name' => 'Torzo Labs & Textile Co.',
            'contact_email' => 'produccion@torzolabs.com',
        ]);

        $product = Product::create([
            'sku' => 'RAW-TEX-001',
            'title' => 'Boxer Trunks Modal Seamless',
            'slug' => 'boxer-trunks-modal-seamless',
            'price' => 340.00,
            'cost' => 120.00,
            'type' => 'dropship',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'RAW-TEX-001-M',
            'price' => 340.00,
            'attributes' => ['size' => 'm'],
        ]);

        // Stock disponible: 15 unidades
        Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'A-01-B',
            'quantity' => 15,
            'reserved' => 0,
        ]);

        $bundle = ProductBundle::create([
            'name' => 'Caja Mensual Marte Nivel 2: Estilo',
            'slug' => 'SUB-BOX-MARTE-LVL2',
            'bundle_type' => 'box_subscription',
            'regular_price' => 1120.00,
            'bundle_price' => 899.00,
            'is_active' => true,
        ]);

        BundleItem::create([
            'product_bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2, // Requiere 2 piezas por caja
        ]);

        // Lote objetivo: 20 cajas -> Requiere 40 piezas. Disponibles: 15. Deficit: 25 piezas.
        $payload = [
            'bundle_sku' => 'SUB-BOX-MARTE-LVL2',
            'target_units' => 20,
            'batch_reference' => 'BATCH-TEST-2026',
        ];

        $response = $this->postJson('/api/v1/inventory/bundles/audit-deficit', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('has_deficit', true)
            ->assertJsonPath('target_units', 20)
            ->assertJsonPath('total_required_pieces', 40)
            ->assertJsonPath('total_available_pieces', 15)
            ->assertJsonPath('total_deficit_pieces', 25);

        // Registro de deficit en base de datos
        $this->assertDatabaseHas('bom_deficit_procurements', [
            'bundle_id' => $bundle->id,
            'component_product_id' => $product->id,
            'deficit_units' => 25,
            'status' => 'po_drafted',
        ]);

        // Creacion automatica de Orden de Compra en borrador
        $this->assertDatabaseHas('purchase_orders', [
            'status' => 'draft',
            'total_units' => 25,
            'subtotal' => 3000.00, // 25 * 120.00
            'tax' => 480.00,       // 3000 * 0.16
            'total_amount' => 3480.00,
        ]);

        // Partidas de la orden de compra
        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $product->id,
            'quantity_ordered' => 25,
            'unit_cost' => 120.00,
            'total_cost' => 3000.00,
        ]);
    }

    public function test_portal_inventory_hydrates_product_bundles_in_blade(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $bundle = ProductBundle::create([
            'name' => 'Caja VIP Total Experiencia',
            'slug' => 'SUB-BOX-VIP-TEST',
            'bundle_type' => 'box_subscription',
            'regular_price' => 1790.00,
            'bundle_price' => 1399.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/inventario-catalogo');

        $response->assertOk()
            ->assertSee('SUB-BOX-VIP-TEST')
            ->assertSee('Caja VIP Total Experiencia')
            ->assertSee('window.serverProductBundles', false);
    }
}


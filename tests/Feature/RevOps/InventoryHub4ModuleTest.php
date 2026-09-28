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
use App\Models\CycleCount;
use App\Models\InventoryMovement;
use App\Models\OperationalTask;
use App\Models\ShiftLog;
use App\Models\WarehouseRampEntry;
use App\Models\VendorBill;
use App\Models\ThreeWayMatching;
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

    public function test_can_receive_purchase_order_with_sanitary_traceability_and_cpp_valuation(): void
    {
        $supplier = Supplier::create([
            'name' => 'Terra Labs Cosmetics MX',
            'contact_email' => 'inbound@terralabs.com',
        ]);

        $product = Product::create([
            'sku' => 'SERUM-INT-001',
            'title' => 'Suero Intimo Hidratante pH 4.5',
            'slug' => 'suero-intimo-hidratante-ph-45',
            'price' => 450.00,
            'cost' => 140.00, // Costo previo
            'type' => 'dropship', // Inicia como dropship
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SERUM-INT-001-50ML',
            'price' => 450.00,
            'cost' => 140.00,
            'attributes' => ['size' => '50ml'],
        ]);

        // Stock previo en rampa: 10 unidades a $140 = $1,400
        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 10,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        // Orden de compra: 20 unidades a $110 = $2,200
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-SAN-001',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 20,
            'subtotal' => 2200.00,
            'tax' => 352.00,
            'total_amount' => 2552.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => '50ml',
            'quantity_ordered' => 20,
            'quantity_received' => 0,
            'unit_cost' => 110.00,
            'total_cost' => 2200.00,
        ]);

        // Recepcion Inbound con trazabilidad sanitaria
        // Calculo CPP esperado: (10 * 140 + 20 * 110) / (10 + 20) = (1400 + 2200) / 30 = 3600 / 30 = 120.00
        $payload = [
            'items' => [
                $poItem->id => 20,
            ],
            'notes' => 'Inspeccion sanitaria en rampa satisfactoria. Precinto de seguridad integro.',
            'batch_number' => 'LOT-2026-COSM-8841',
            'expiration_date' => '2028-11-30',
            'pao_months' => 12,
        ];

        $response = $this->postJson("/api/v1/inventory/purchases/orders/{$po->id}/receive", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'received')
            ->assertJsonPath('units_received', 20)
            ->assertJsonPath('batch_number', 'LOT-2026-COSM-8841')
            ->assertJsonPath('expiration_date', '2028-11-30')
            ->assertJsonPath('pao_months', 12);

        // Verificacion de nuevo inventario fisico (10 + 20 = 30)
        $inventory->refresh();
        $this->assertEquals(30, $inventory->quantity);

        // Verificacion de valuacion contable CPP
        $variant->refresh();
        $product->refresh();
        $this->assertEquals(120.00, (float) $variant->cost);
        $this->assertEquals(120.00, (float) $product->cost);

        // Verificacion de atributos sanitarios en variante
        $this->assertEquals('LOT-2026-COSM-8841', data_get($variant->attributes, 'batch_number'));
        $this->assertEquals('2028-11-30', data_get($variant->attributes, 'expiration_date'));
        $this->assertEquals(12, data_get($variant->attributes, 'pao_months'));
        $this->assertTrue(data_get($variant->attributes, 'requires_sanitary_control'));

        // Verificacion de metadatos sanitarios en producto
        $this->assertEquals('LOT-2026-COSM-8841', data_get($product->meta, 'batch_number'));
        $this->assertEquals('2028-11-30', data_get($product->meta, 'expiration_date'));
        $this->assertEquals(12, data_get($product->meta, 'pao_months'));

        // Verificacion de transicion dropship -> both
        $this->assertEquals('both', $product->type);

        // Verificacion de asiento inmutable en Kardex de Inventario
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => 20,
            'quantity_before' => 10,
            'quantity_after' => 30,
        ]);

        // Verificacion de asiento mercantil en Kardex de Compras
        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $po->id,
            'movement_type' => 'INBOUND_RECEIPT',
            'supplier_id' => $supplier->id,
        ]);

        // Verificacion de estado final de la OC
        $po->refresh();
        $this->assertEquals('received', $po->status);
        $this->assertNotNull($po->received_at);
        $this->assertStringContainsString('LOT-2026-COSM-8841', $po->notes);
    }

    public function test_can_record_cycle_count_with_ira_score_and_kardex_reconciliation(): void
    {
        $product = Product::create([
            'sku' => 'AUDIT-SKU-001',
            'title' => 'Boxer Pima Auditoria',
            'slug' => 'boxer-pima-auditoria',
            'price' => 380.00,
            'cost' => 120.00,
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'AUDIT-SKU-001-M',
            'price' => 380.00,
            'attributes' => ['size' => 'M'],
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 20,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $payload = [
            'counts' => [
                [
                    'sku' => 'AUDIT-SKU-001-M',
                    'physical_counted' => 18,
                    'reason' => 'Merma en estante por prueba de calidad',
                ],
            ],
            'reconcile_kardex' => true,
        ];

        $response = $this->postJson('/api/v1/inventory/cycle-counts', $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('ira_accuracy_score', 90)
            ->assertJsonPath('status', 'reconciled')
            ->assertJsonPath('total_units_counted', 18)
            ->assertJsonPath('total_variance_units', 2);

        // Verificacion de persistencia de auditoria
        $this->assertDatabaseHas('cycle_counts', [
            'sample_sku_count' => 1,
            'total_units_counted' => 18,
            'total_variance_units' => 2,
            'ira_accuracy_score' => 90.00,
            'status' => 'reconciled',
        ]);

        // Verificacion de ajuste de inventario
        $inventory->refresh();
        $this->assertEquals(18, $inventory->quantity);

        // Verificacion de movimiento de auditoria en Kardex
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => -2,
            'quantity_before' => 20,
            'quantity_after' => 18,
        ]);
    }

    public function test_portal_inventory_hydrates_cycle_counts_in_blade(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        CycleCount::create([
            'count_date' => now()->toDateString(),
            'sample_sku_count' => 3,
            'total_units_counted' => 45,
            'total_variance_units' => 1,
            'ira_accuracy_score' => 97.83,
            'audited_by' => 'Auditor Certificado',
            'status' => 'reconciled',
            'details_json' => [
                ['sku' => 'TEST-SKU-1', 'variance' => 1],
            ],
        ]);

        $response = $this->actingAs($user)->get('/admin/inventario-catalogo');

        $response->assertOk()
            ->assertSee('window.serverCycleCounts', false)
            ->assertSee('Auditor Certificado');
    }

    public function test_can_adjust_stock_increase_and_persist_kardex_movement(): void
    {
        $product = Product::create([
            'sku' => 'TEST-ADJ-001',
            'title' => 'Boxer Test Ajuste',
            'slug' => 'boxer-test-ajuste',
            'price' => 250.00,
            'cost' => 120.00,
            'type' => 'physical',
            'visible' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-ADJ-001-M',
            'price' => 250.00,
            'cost' => 120.00,
            'attributes' => ['size' => 'M'],
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 20,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $response = $this->postJson('/api/v1/inventory/adjust', [
            'sku' => 'TEST-ADJ-001',
            'type' => 'add',
            'quantity' => 15,
            'reason' => 'Entrada rapida de recepcion',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.previous_stock', 20)
            ->assertJsonPath('data.new_variant_stock', 35)
            ->assertJsonPath('data.new_local_stock', 35)
            ->assertJsonPath('data.delta', 15);

        $inventory->refresh();
        $this->assertEquals(35, $inventory->quantity);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => 15,
            'quantity_before' => 20,
            'quantity_after' => 35,
            'reason' => 'Entrada rapida de recepcion',
        ]);
    }

    public function test_can_adjust_stock_decrease_and_prevent_negative_stock(): void
    {
        $product = Product::create([
            'sku' => 'TEST-ADJ-002',
            'title' => 'Boxer Test Salida',
            'slug' => 'boxer-test-salida',
            'price' => 250.00,
            'cost' => 120.00,
            'type' => 'physical',
            'visible' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-ADJ-002-CH',
            'price' => 250.00,
            'cost' => 120.00,
            'attributes' => ['size' => 'CH'],
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 10,
            'reserved' => 2,
            'type' => 'physical',
        ]);

        $failResponse = $this->postJson('/api/v1/inventory/adjust', [
            'sku' => 'TEST-ADJ-002',
            'type' => 'sub',
            'quantity' => 9,
            'reason' => 'Salida excesiva',
        ]);

        $failResponse->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $okResponse = $this->postJson('/api/v1/inventory/adjust', [
            'sku' => 'TEST-ADJ-002',
            'type' => 'sub',
            'quantity' => 5,
            'reason' => 'Merma operativa de empaque',
        ]);

        $okResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.previous_stock', 10)
            ->assertJsonPath('data.new_variant_stock', 5)
            ->assertJsonPath('data.delta', -5);

        $inventory->refresh();
        $this->assertEquals(5, $inventory->quantity);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => -5,
            'quantity_before' => 10,
            'quantity_after' => 5,
            'reason' => 'Merma operativa de empaque',
        ]);
    }

    public function test_panoramic_modal_update_complete_persists_sizes_and_kardex(): void
    {
        $product = Product::create([
            'sku' => 'TEST-ADJ-003',
            'title' => 'Boxer Panoramic Modal',
            'slug' => 'boxer-panoramic-modal',
            'price' => 300.00,
            'cost' => 150.00,
            'type' => 'physical',
            'visible' => true,
        ]);

        $variantM = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-ADJ-003-M',
            'price' => 300.00,
            'cost' => 150.00,
            'attributes' => ['size' => 'M'],
        ]);

        $invM = Inventory::create([
            'variant_id' => $variantM->id,
            'location' => 'warehouse-1',
            'quantity' => 10,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $response = $this->putJson('/api/v1/inventory/products/TEST-ADJ-003/update-complete', [
            'title' => 'Boxer Panoramic Modal Editado',
            'pvp' => 320.00,
            'cost' => 150.00,
            'sizes' => [
                'm' => 18,
                'g' => 12,
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $invM->refresh();
        $this->assertEquals(18, $invM->quantity);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $invM->id,
            'quantity' => 8,
            'quantity_before' => 10,
            'quantity_after' => 18,
        ]);

        $variantG = ProductVariant::where('product_id', $product->id)
            ->where('sku', 'TEST-ADJ-003-G')
            ->first();
        $this->assertNotNull($variantG);

        $invG = Inventory::where('variant_id', $variantG->id)->first();
        $this->assertNotNull($invG);
        $this->assertEquals(12, $invG->quantity);
    }

    public function test_can_list_suppliers_with_extranet_access_flag(): void
    {
        $supplier = Supplier::create([
            'name' => 'Textiles El Aguila SA',
            'contact_name' => 'Roberto Garza',
            'phone' => '+52 55 1234 5678',
            'contact_email' => 'ventas@aguila.com',
            'city' => 'Puebla',
            'type' => 'local',
            'status' => 'connected',
        ]);

        User::create([
            'name' => 'Roberto Garza',
            'email' => 'ventas@aguila.com',
            'password' => bcrypt('password123'),
            'role' => 'supplier',
            'supplier_id' => $supplier->id,
        ]);

        $response = $this->getJson('/api/v1/inventory/suppliers');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonFragment([
                'name' => 'Textiles El Aguila SA',
                'hasExtranetAccess' => true,
            ]);
    }

    public function test_can_create_supplier_persisted_in_database(): void
    {
        $response = $this->postJson('/api/v1/inventory/suppliers', [
            'name' => 'Confecciones Finas del Bajio',
            'tradeName' => 'Bajio Textil',
            'contactName' => 'Laura Esquivel',
            'phone' => '+52 477 123 4567',
            'email' => 'contacto@bajiotextil.com',
            'city' => 'Leon',
            'type' => 'workshop',
            'leadTimeDays' => 4,
            'paymentTerms' => 'credito_30',
            'status' => 'connected',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Bajio Textil')
            ->assertJsonPath('data.city', 'Leon')
            ->assertJsonPath('data.leadTimeDays', 4);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Bajio Textil',
            'contact_name' => 'Laura Esquivel',
            'phone' => '+52 477 123 4567',
            'city' => 'Leon',
            'lead_time_days' => 4,
            'status' => 'connected',
        ]);
    }

    public function test_can_update_supplier_conditions(): void
    {
        $supplier = Supplier::create([
            'name' => 'Proveedor Original',
            'contact_name' => 'Mario Gomez',
            'phone' => '+52 55 9999 8888',
            'contact_email' => 'mario@original.com',
            'city' => 'CDMX',
            'type' => 'wholesale',
            'status' => 'connected',
            'lead_time_days' => 5,
        ]);

        $response = $this->putJson("/api/v1/inventory/suppliers/{$supplier->id}", [
            'tradeName' => 'Proveedor Editado SA',
            'city' => 'Monterrey',
            'leadTimeDays' => 2,
            'paymentTerms' => 'credito_60',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Proveedor Editado SA')
            ->assertJsonPath('data.city', 'Monterrey')
            ->assertJsonPath('data.leadTimeDays', 2);

        $supplier->refresh();
        $this->assertEquals('Proveedor Editado SA', $supplier->name);
        $this->assertEquals('Monterrey', $supplier->city);
        $this->assertEquals(2, $supplier->lead_time_days);
    }

    public function test_can_toggle_supplier_status_and_archive(): void
    {
        $supplier = Supplier::create([
            'name' => 'Proveedor a Pausar',
            'contact_name' => 'Ana Soto',
            'phone' => '+52 55 1111 2222',
            'contact_email' => 'ana@pausar.com',
            'status' => 'connected',
        ]);

        $pauseResponse = $this->postJson("/api/v1/inventory/suppliers/{$supplier->id}/toggle-status", [
            'status' => 'paused',
        ]);

        $pauseResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'paused');

        $supplier->refresh();
        $this->assertEquals('paused', $supplier->status);

        $archiveResponse = $this->postJson("/api/v1/inventory/suppliers/{$supplier->id}/toggle-status", [
            'status' => 'archived',
        ]);

        $archiveResponse->assertOk()
            ->assertJsonPath('data.status', 'archived');

        $supplier->refresh();
        $this->assertEquals('archived', $supplier->status);
    }

    public function test_can_invite_supplier_to_hub7_extranet_creating_user(): void
    {
        $supplier = Supplier::create([
            'name' => 'Taller Textil B2B Express',
            'contact_name' => 'Carlos B2B',
            'phone' => '+52 55 3333 4444',
            'contact_email' => 'carlos@textilexpress.com',
            'status' => 'connected',
        ]);

        $response = $this->postJson("/api/v1/inventory/suppliers/{$supplier->id}/invite-extranet");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.has_extranet_access', true);

        $this->assertDatabaseHas('users', [
            'email' => 'carlos@textilexpress.com',
            'role' => 'supplier',
            'supplier_id' => $supplier->id,
        ]);
    }

    public function test_inbound_receipt_completes_hub1_operational_task_and_creates_shift_log(): void
    {
        $supplier = Supplier::create([
            'name' => 'Proveedor Textil Central',
            'type' => 'wholesale',
        ]);

        $product = Product::create([
            'sku' => 'TSHIRT-PIMA-001',
            'title' => 'Playera Pima Blanco',
            'slug' => 'playera-pima-blanco',
            'price' => 299.00,
            'cost' => 100.00,
            'type' => 'dropship',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TSHIRT-PIMA-001-M',
            'price' => 299.00,
            'attributes' => ['size' => 'M'],
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-HUB1-2026-001',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 10,
            'subtotal' => 1000.00,
            'tax' => 160.00,
            'total_amount' => 1160.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => 'M',
            'quantity_ordered' => 10,
            'quantity_received' => 0,
            'unit_cost' => 100.00,
            'total_cost' => 1000.00,
        ]);

        // Simular tarea creada por despacho previo en Hub 1
        $task = OperationalTask::create([
            'title' => 'Recepcion Inbound en Rampa: PO-HUB1-2026-001 (10 uds)',
            'category' => 'almacen',
            'priority' => 'high',
            'assigned_role' => 'warehouse',
            'status' => 'pending',
            'reference_code' => $po->po_number,
            'action_payload' => [
                'purchase_order_id' => $po->id,
                'po_number' => $po->po_number,
            ],
        ]);

        $response = $this->postJson("/api/v1/inventory/purchases/orders/{$po->id}/receive", [
            'items' => [$poItem->id => 10],
            'notes' => 'Conteo fisico conforme en rampa',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'received')
            ->assertJsonPath('units_received', 10);

        // Verificar que la tarea de Hub 1 se completo
        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);

        // Verificar registro en Bitacora de Turno (ShiftLog)
        $this->assertDatabaseHas('shift_logs', [
            'operational_task_id' => $task->id,
            'event_type' => 'inbound_received',
        ]);
    }

    public function test_inbound_receipt_releases_hub6_dock_ramp_entry(): void
    {
        $supplier = Supplier::create([
            'name' => 'Laboratorio Cosmetico B2B',
            'type' => 'wholesale',
        ]);

        $product = Product::create([
            'sku' => 'SERUM-VIT-C-01',
            'title' => 'Serum Vitamina C 30ml',
            'slug' => 'serum-vitamina-c-30ml',
            'price' => 450.00,
            'cost' => 150.00,
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SERUM-VIT-C-01-STD',
            'price' => 450.00,
            'attributes' => ['volume' => '30ml'],
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-HUB6-7788',
            'supplier_id' => $supplier->id,
            'status' => 'in_transit',
            'carrier' => 'Paquetexpress',
            'tracking_number' => 'PEX-99887766',
            'total_units' => 15,
            'subtotal' => 2250.00,
            'tax' => 360.00,
            'total_amount' => 2610.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => '30ml',
            'quantity_ordered' => 15,
            'quantity_received' => 0,
            'unit_cost' => 150.00,
            'total_cost' => 2250.00,
        ]);

        // Simular entrada en rampa de Hub 6
        $rampEntry = WarehouseRampEntry::create([
            'entry_folio' => 'RAMP-POHUB67788-001',
            'carrier_or_supplier' => 'Paquetexpress / Laboratorio Cosmetico B2B',
            'driver_name' => 'Juan Chofer',
            'status' => 'in_dock',
            'packages_received_count' => 0,
            'docked_at' => now()->subHour(),
        ]);

        $response = $this->postJson("/api/v1/inventory/purchases/orders/{$po->id}/receive", [
            'items' => [$poItem->id => 15],
            'notes' => 'Descarga completa en muelle',
            'batch_number' => 'LOTE-2026-C1',
            'expiration_date' => '2028-12-31',
            'pao_months' => 12,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Verificar liberacion de rampa en Hub 6
        $rampEntry->refresh();
        $this->assertEquals('unloaded', $rampEntry->status);
        $this->assertNotNull($rampEntry->cleared_at);
        $this->assertEquals(15, $rampEntry->packages_received_count);
    }

    public function test_inbound_receipt_generates_hub5_vendor_bill_and_three_way_matching(): void
    {
        $supplier = Supplier::create([
            'name' => 'Taller de Confeccion Guadalajara',
            'type' => 'wholesale',
            'payment_terms' => 'credito_30',
            'credentials' => [
                'bank_name' => 'BBVA Bancomer',
                'bank_clabe' => '012180001234567890',
            ],
        ]);

        $product = Product::create([
            'sku' => 'PANT-CHINO-001',
            'title' => 'Pantalon Chino Beige',
            'slug' => 'pantalon-chino-beige',
            'price' => 599.00,
            'cost' => 200.00,
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PANT-CHINO-001-32',
            'price' => 599.00,
            'attributes' => ['size' => '32'],
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-HUB5-3322',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 8,
            'subtotal' => 1600.00,
            'tax' => 256.00,
            'total_amount' => 1856.00,
            'credit_deduction_amount' => 100.00, // NC previa aplicada
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => '32',
            'quantity_ordered' => 8,
            'quantity_received' => 0,
            'unit_cost' => 200.00,
            'total_cost' => 1600.00,
        ]);

        $response = $this->postJson("/api/v1/inventory/purchases/orders/{$po->id}/receive", [
            'items' => [$poItem->id => 8],
            'notes' => 'Recepcion integral con nota de credito aplicada',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // 1600 cost + 256 tax = 1856 - 100 NC = 1756 neto
        $this->assertDatabaseHas('vendor_bills', [
            'supplier_id' => $supplier->id,
            'vendor_name' => 'Taller de Confeccion Guadalajara',
            'amount' => 1756.00,
            'status' => 'pending',
            'is_spei_blocked' => false,
        ]);

        // Verificar 3-way matching en Hub 5
        $this->assertDatabaseHas('three_way_matchings', [
            'po_code' => 'PO-HUB5-3322',
            'match_status' => 'matched',
            'spei_payout_blocked' => false,
            'warehouse_received_qty' => 8,
            'payable_net_amount' => 1756.00,
        ]);
    }

    public function test_inbound_partial_receipt_marks_task_in_progress_and_discrepancy_held(): void
    {
        $supplier = Supplier::create([
            'name' => 'Fabrica Calzado Leon',
            'type' => 'wholesale',
            'payment_terms' => 'contado',
        ]);

        $product = Product::create([
            'sku' => 'SHOES-OXFORD-01',
            'title' => 'Zapatos Oxford Cuero Cafe',
            'slug' => 'zapatos-oxford-cuero-cafe',
            'price' => 1299.00,
            'cost' => 600.00,
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SHOES-OXFORD-01-27',
            'price' => 1299.00,
            'attributes' => ['size' => '27'],
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-PARTIAL-5544',
            'supplier_id' => $supplier->id,
            'status' => 'confirmed',
            'total_units' => 20,
            'subtotal' => 12000.00,
            'tax' => 1920.00,
            'total_amount' => 13920.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'size_name' => '27',
            'quantity_ordered' => 20,
            'quantity_received' => 0,
            'unit_cost' => 600.00,
            'total_cost' => 12000.00,
        ]);

        $task = OperationalTask::create([
            'title' => 'Recepcion Inbound: PO-PARTIAL-5544 (20 uds)',
            'category' => 'almacen',
            'priority' => 'normal',
            'assigned_role' => 'warehouse',
            'status' => 'pending',
            'reference_code' => $po->po_number,
        ]);

        // Recepcion parcial: solo 5 de 20
        $response = $this->postJson("/api/v1/inventory/purchases/orders/{$po->id}/receive", [
            'items' => [$poItem->id => 5],
            'notes' => 'Arribo parcial: proveedor dividio envio',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'partially_received')
            ->assertJsonPath('units_received', 5);

        // Tarea debe estar in_progress en Hub 1, no completed
        $task->refresh();
        $this->assertEquals('in_progress', $task->status);
        $this->assertNull($task->completed_at);

        // 3-Way matching debe retener SPEI por recepcion parcial
        $this->assertDatabaseHas('three_way_matchings', [
            'po_code' => 'PO-PARTIAL-5544',
            'match_status' => 'discrepancy_held',
            'spei_payout_blocked' => true,
            'warehouse_received_qty' => 5,
        ]);
    }
}




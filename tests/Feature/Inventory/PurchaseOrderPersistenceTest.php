<?php

namespace Tests\Feature\Inventory;

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

class PurchaseOrderPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Supplier $supplier;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'portal']);
        $this->supplier = Supplier::factory()->create([
            'name' => 'Torzo Fabricacion B2B',
            'contact_email' => 'taller@torzomx.test',
        ]);

        $this->product = Product::factory()->create([
            'title' => 'Boxer Modal Transpirable',
            'sku' => 'TXT-TRZ-1001-M',
            'cost' => 150.00,
            'price' => 380.00,
        ]);

        $this->variant = ProductVariant::factory()->for($this->product)->create([
            'sku' => 'TXT-TRZ-1001-M',
            'attributes' => ['size' => 'M'],
        ]);
    }

    public function test_can_get_purchase_orders_list_with_filters(): void
    {
        $po1 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0001',
            'supplier_id' => $this->supplier->id,
            'status' => 'confirmed',
            'priority' => 'alta',
            'total_units' => 50,
            'subtotal' => 7500.00,
            'tax' => 1200.00,
            'total_amount' => 8700.00,
            'notes' => 'Pedido de alta prioridad',
        ]);

        $po2 = PurchaseOrder::create([
            'po_number' => 'PO-2026-0002',
            'supplier_id' => $this->supplier->id,
            'status' => 'in_transit',
            'priority' => 'media',
            'total_units' => 20,
            'subtotal' => 3000.00,
            'tax' => 480.00,
            'total_amount' => 3480.00,
            'carrier' => 'Fletera Castores',
            'tracking_number' => 'CST-991204',
        ]);

        // Consulta general
        $response = $this->actingAs($this->user)->getJson('/api/v1/inventory/purchases/orders');
        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'po_number', 'supplier_id', 'status', 'total_units', 'total_amount']
            ],
            'total',
        ]);
        $this->assertEquals(2, $response->json('total'));

        // Filtro por estatus
        $responseFiltered = $this->actingAs($this->user)->getJson('/api/v1/inventory/purchases/orders?status=in_transit');
        $responseFiltered->assertOk();
        $this->assertEquals(1, $responseFiltered->json('total'));
        $this->assertEquals('PO-2026-0002', $responseFiltered->json('data.0.po_number'));
    }

    public function test_can_get_single_purchase_order_detail_with_items_and_kardex(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-0010',
            'supplier_id' => $this->supplier->id,
            'status' => 'confirmed',
            'priority' => 'critica',
            'total_units' => 30,
            'subtotal' => 4500.00,
            'tax' => 720.00,
            'total_amount' => 5220.00,
            'po_snapshot' => [
                'po_number' => 'PO-2026-0010',
                'supplier_name' => $this->supplier->name,
                'items_count' => 1,
            ],
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'size_name' => 'M',
            'quantity_ordered' => 30,
            'unit_cost' => 150.00,
            'total_cost' => 4500.00,
        ]);

        PurchaseKardex::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'movement_type' => 'PO_CREATED',
            'description' => 'Emision de prueba',
            'total_amount' => 5220.00,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/inventory/purchases/orders/{$po->id}");
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'order' => [
                'id' => $po->id,
                'po_number' => 'PO-2026-0010',
                'priority' => 'critica',
                'items' => [
                    [
                        'size_name' => 'M',
                        'quantity_ordered' => 30,
                        'unit_cost' => '150.00',
                    ]
                ],
                'kardex' => [
                    [
                        'movement_type' => 'PO_CREATED',
                    ]
                ]
            ],
        ]);
    }

    public function test_store_purchase_order_persists_relational_items_priority_and_snapshot_atomically(): void
    {
        $payload = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-2026-PERSIST-1',
            'priority' => 'alta',
            'notes' => 'Lote para surtir suscripciones',
            'payment_terms' => 'Credito Comercial 30 dias',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'sku' => $this->product->sku,
                    'unit_cost' => 150.00,
                    'sizes' => [
                        'ch' => 10,
                        'm' => 20,
                        'g' => 10,
                    ],
                ]
            ],
            'po_snapshot' => [
                'po_number' => 'PO-2026-PERSIST-1',
                'clausula_discrepancia' => 'Ajuste segun rampa',
            ],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/purchases/orders', $payload);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'order' => [
                'poNumber' => 'PO-2026-PERSIST-1',
                'totalPieces' => 40,
                'subtotal' => 6000.00, // 40 * 150
                'tax' => 960.00,       // 6000 * 0.16
                'total' => 6960.00,
            ],
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-2026-PERSIST-1',
            'supplier_id' => $this->supplier->id,
            'priority' => 'alta',
            'total_units' => 40,
            'subtotal' => 6000.00,
            'tax' => 960.00,
            'total_amount' => 6960.00,
        ]);

        $po = PurchaseOrder::where('po_number', 'PO-2026-PERSIST-1')->first();
        $this->assertNotNull($po);
        $this->assertNotNull($po->po_snapshot);
        $this->assertEquals('PO-2026-PERSIST-1', $po->po_snapshot['po_number']);

        // Verifica partidas guardadas (CH=10, M=20, G=10)
        $this->assertEquals(3, $po->items()->count());
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'size_name' => 'M',
            'quantity_ordered' => 20,
            'unit_cost' => 150.00,
            'total_cost' => 3000.00,
        ]);

        // Verifica asiento en Purchase Kardex
        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'movement_type' => 'PO_CREATED',
            'total_amount' => 6960.00,
        ]);
    }

    public function test_store_purchase_order_applies_supplier_credit_deduction_and_calculates_16_percent_vat(): void
    {
        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-TEST-0089',
            'agreement_type' => 'shortage_discount',
            'amount' => 1000.00,
            'status' => 'pending',
            'resolution_notes' => 'Compensacion acordada por 5 piezas faltantes en envio anterior',
        ]);

        $payload = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-2026-CREDIT-1',
            'priority' => 'media',
            'has_credit_deduction' => true,
            'credit_agreement_id' => $agreement->id,
            'credit_note_ref' => $agreement->reference_folio,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'sku' => $this->product->sku,
                    'unit_cost' => 150.00,
                    'sizes' => [
                        'm' => 20, // 20 * 150 = 3000.00 bruto
                    ],
                ]
            ],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/purchases/orders', $payload);

        $response->assertCreated();

        // Bruto = 3000.00
        // Deduccion = 1000.00
        // Base Gravable = 2000.00
        // IVA 16% = 320.00
        // Total = 2320.00
        $response->assertJson([
            'success' => true,
            'order' => [
                'subtotal' => 2000.00,
                'tax' => 320.00,
                'total' => 2320.00,
                'deductionAmount' => 1000.00,
                'creditNoteRef' => 'NC-TEST-0089',
            ],
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-2026-CREDIT-1',
            'credit_agreement_id' => $agreement->id,
            'gross_subtotal' => 3000.00,
            'credit_deduction_amount' => 1000.00,
            'subtotal' => 2000.00,
            'tax' => 320.00,
            'total_amount' => 2320.00,
        ]);

        // El acuerdo debe haber pasado a 'applied'
        $agreement->refresh();
        $this->assertEquals('applied', $agreement->status);
        $this->assertNotNull($agreement->purchase_order_id);
    }

    public function test_dispatch_purchase_order_updates_status_carrier_tracking_and_kardex(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-DISP-1',
            'supplier_id' => $this->supplier->id,
            'status' => 'confirmed',
            'total_units' => 25,
            'subtotal' => 3750.00,
            'tax' => 600.00,
            'total_amount' => 4350.00,
        ]);

        $payload = [
            'carrier' => 'Transportes Castores',
            'tracking_number' => 'CST-778811',
            'recipient_email' => 'embarques@torzomx.test',
            'notes' => 'Recoleccion programada andén 2',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/v1/inventory/purchases/orders/{$po->id}/dispatch", $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'po_number' => 'PO-2026-DISP-1',
            'status' => 'En Camino',
        ]);

        $po->refresh();
        $this->assertEquals('in_transit', $po->status);
        $this->assertEquals('Transportes Castores', $po->carrier);
        $this->assertEquals('CST-778811', $po->tracking_number);
        $this->assertNotNull($po->sent_at);

        // Debe registrarse el movimiento PO_SENT en Purchase Kardex
        $this->assertDatabaseHas('purchase_kardex', [
            'purchase_order_id' => $po->id,
            'movement_type' => 'PO_SENT',
            'total_amount' => 4350.00,
        ]);
    }

    public function test_get_purchase_kardex_endpoint_returns_movement_history(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-KARDEX-1',
            'supplier_id' => $this->supplier->id,
            'status' => 'in_transit',
            'total_units' => 10,
            'total_amount' => 1740.00,
        ]);

        PurchaseKardex::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'movement_type' => 'PO_CREATED',
            'description' => 'Creacion de orden',
            'total_amount' => 1740.00,
        ]);

        PurchaseKardex::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'movement_type' => 'PO_SENT',
            'description' => 'Despacho con Castores',
            'total_amount' => 1740.00,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/inventory/purchases/kardex');
        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'movement_type', 'description', 'total_amount']
            ],
            'total',
        ]);

        $this->assertEquals(2, $response->json('total'));
    }
}

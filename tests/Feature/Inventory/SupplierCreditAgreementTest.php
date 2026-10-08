<?php

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseKardex;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierCreditAgreement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierCreditAgreementTest extends TestCase
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
            'title' => 'Boxer Microfibra Sin Costuras',
            'sku' => 'TXT-TRZ-2002-L',
            'cost' => 140.00,
        ]);

        $this->variant = ProductVariant::factory()->for($this->product)->create([
            'sku' => 'TXT-TRZ-2002-L',
            'attributes' => ['size' => 'G'],
        ]);
    }

    public function test_can_get_credit_agreements_with_status_filter(): void
    {
        $agreement1 = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-TORZO-2026-0001',
            'agreement_type' => 'shortage_discount',
            'discrepancy_type' => 'missing_units',
            'missing_units_count' => 5,
            'unit_cost_applied' => 140.00,
            'amount' => 700.00,
            'status' => 'pending',
            'resolution_notes' => 'Faltaron 5 piezas en rampa',
        ]);

        $agreement2 = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-TORZO-2026-0002',
            'agreement_type' => 'quality_defect',
            'discrepancy_type' => 'damaged_units',
            'damaged_units_count' => 3,
            'unit_cost_applied' => 140.00,
            'amount' => 420.00,
            'status' => 'applied',
            'resolution_notes' => 'Prendas con mancha de aceite de maquina',
        ]);

        // Consulta pendientes
        $response = $this->actingAs($this->user)->getJson('/api/v1/inventory/purchases/credit-agreements?status=pending');
        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertCount(1, $response->json('agreements'));
        $this->assertEquals('NC-TORZO-2026-0001', $response->json('agreements.0.ref'));
        $this->assertEquals(700.00, $response->json('agreements.0.amount'));
        $this->assertEquals('Pendiente de Aplicar', $response->json('agreements.0.status'));

        // Consulta aplicadas
        $responseApplied = $this->actingAs($this->user)->getJson('/api/v1/inventory/purchases/credit-agreements?status=applied');
        $responseApplied->assertOk();
        $this->assertCount(1, $responseApplied->json('agreements'));
        $this->assertEquals('NC-TORZO-2026-0002', $responseApplied->json('agreements.0.ref'));
        $this->assertEquals('Aplicada', $responseApplied->json('agreements.0.status'));
    }

    public function test_store_credit_agreement_calculates_amount_from_units_and_cost_and_emits_kardex(): void
    {
        $payload = [
            'supplier_id' => $this->supplier->id,
            'discrepancy_type' => 'missing_units',
            'missing_units_count' => 8,
            'damaged_units_count' => 2,
            'unit_cost_applied' => 140.00,
            'authorized_by_vendor_contact' => 'Lic. Roberto Morales (Jefe de Taller Torzo)',
            'resolution_notes' => 'Acuerdo de compensacion por 10 piezas totales no recibidas',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/purchases/credit-agreements', $payload);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'agreement' => [
                'supplier_id' => $this->supplier->id,
                'supplier' => $this->supplier->name,
                'amount' => 1400.00, // (8 + 2) * 140 = 1400.00
                'status' => 'Pendiente de Aplicar',
                'missing_units' => 8,
                'damaged_units' => 2,
                'unit_cost' => 140.00,
                'authorized_by' => 'Lic. Roberto Morales (Jefe de Taller Torzo)',
            ],
        ]);

        $this->assertDatabaseHas('supplier_credit_agreements', [
            'supplier_id' => $this->supplier->id,
            'amount' => 1400.00,
            'status' => 'pending',
            'missing_units_count' => 8,
            'damaged_units_count' => 2,
        ]);

        // Verifica que se emitio asiento en Purchase Kardex
        $this->assertDatabaseHas('purchase_kardex', [
            'supplier_id' => $this->supplier->id,
            'movement_type' => 'DISCREPANCY_REGISTERED',
            'total_amount' => 1400.00,
        ]);
    }

    public function test_can_get_single_credit_agreement_detail_with_relations(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-ORIGIN-001',
            'supplier_id' => $this->supplier->id,
            'status' => 'received',
            'total_units' => 100,
            'total_amount' => 14000.00,
        ]);

        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'reference_folio' => 'NC-TORZO-DET-1',
            'amount' => 560.00,
            'status' => 'pending',
            'discrepancy_minutes' => 'Minuta levantada en andén 1 con chofer de fletera.',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/inventory/purchases/credit-agreements/{$agreement->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'agreement' => [
                'id' => $agreement->id,
                'reference_folio' => 'NC-TORZO-DET-1',
                'amount' => '560.00',
                'purchase_order' => [
                    'po_number' => 'PO-ORIGIN-001',
                ],
            ],
        ]);
    }

    public function test_can_void_credit_agreement_if_pending_and_emits_kardex(): void
    {
        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-TO-VOID-1',
            'amount' => 840.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/inventory/purchases/credit-agreements/{$agreement->id}/void", [
            'void_reason' => 'El taller envio las 6 piezas faltantes en camioneta propia al dia siguiente',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Acuerdo compensatorio anulado correctamente.',
        ]);

        $agreement->refresh();
        $this->assertEquals('void', $agreement->status);
        $this->assertStringContainsString('El taller envio las 6 piezas faltantes', $agreement->resolution_notes);

        // Verifica asiento en Purchase Kardex
        $this->assertDatabaseHas('purchase_kardex', [
            'supplier_id' => $this->supplier->id,
            'movement_type' => 'CREDIT_VOIDED',
            'total_amount' => 840.00,
        ]);
    }

    public function test_cannot_void_credit_agreement_if_already_applied(): void
    {
        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-APPLIED-ALREADY',
            'amount' => 500.00,
            'status' => 'applied',
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/inventory/purchases/credit-agreements/{$agreement->id}/void");

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'No es posible anular un acuerdo que ya fue aplicado en una orden de compra.',
        ]);

        $agreement->refresh();
        $this->assertEquals('applied', $agreement->status);
    }

    public function test_credit_agreement_is_atomically_deducted_in_purchase_order_and_transitions_to_applied(): void
    {
        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-FLOW-099',
            'amount' => 600.00,
            'status' => 'pending',
            'resolution_notes' => 'Discrepancia en remision REM-1234',
        ]);

        $payload = [
            'supplier_id' => $this->supplier->id,
            'po_number' => 'PO-2026-COMP-099',
            'has_credit_deduction' => true,
            'credit_agreement_id' => $agreement->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'sku' => $this->product->sku,
                    'unit_cost' => 140.00,
                    'sizes' => [
                        'g' => 10, // 10 * 140 = 1400.00
                    ],
                ]
            ],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/purchases/orders', $payload);

        $response->assertCreated();

        // 1400 - 600 = 800 base gravable
        // 800 * 0.16 = 128 IVA
        // 800 + 128 = 928 Total
        $response->assertJson([
            'success' => true,
            'order' => [
                'subtotal' => 800.00,
                'tax' => 128.00,
                'total' => 928.00,
                'deductionAmount' => 600.00,
            ],
        ]);

        $agreement->refresh();
        $this->assertEquals('applied', $agreement->status);

        $po = PurchaseOrder::where('po_number', 'PO-2026-COMP-099')->first();
        $this->assertNotNull($po);
        $this->assertEquals($po->id, $agreement->purchase_order_id);
        $this->assertEquals($po->id, $agreement->applied_to_po_id);
    }

    public function test_can_update_credit_agreement_and_records_kardex_traceability(): void
    {
        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-TORZO-2026-0044',
            'amount' => 500.00,
            'status' => 'pending',
            'resolution_notes' => 'Monto inicial pactado',
        ]);

        $updatePayload = [
            'amount' => 850.00,
            'resolution_notes' => 'Ajuste renegociado con taller por 2 prendas extra defectuosas',
            'authorized_by_vendor_contact' => 'Lic. Roberto Morales',
        ];

        $response = $this->actingAs($this->user)->putJson('/api/v1/inventory/purchases/credit-agreements/' . $agreement->id, $updatePayload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'agreement' => [
                'ref' => 'NC-TORZO-2026-0044',
                'amount' => 850.00,
            ],
        ]);

        $agreement->refresh();
        $this->assertEquals(850.00, (float) $agreement->amount);
        $this->assertEquals('Ajuste renegociado con taller por 2 prendas extra defectuosas', $agreement->resolution_notes);

        // Verificar trazabilidad inmutable en PurchaseKardex
        $kardex = PurchaseKardex::where('movement_type', 'DISCREPANCY_UPDATED')
            ->where('supplier_id', $this->supplier->id)
            ->latest()
            ->first();

        $this->assertNotNull($kardex);
        $this->assertStringContainsString('NC-TORZO-2026-0044', $kardex->description);
        $this->assertStringContainsString('850.00', $kardex->description);
    }

    public function test_cannot_update_applied_credit_agreement(): void
    {
        $agreement = SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-TORZO-2026-0099',
            'amount' => 1200.00,
            'status' => 'applied',
            'resolution_notes' => 'Ya liquidada en PO',
        ]);

        $response = $this->actingAs($this->user)->putJson('/api/v1/inventory/purchases/credit-agreements/' . $agreement->id, [
            'amount' => 1500.00,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_can_query_all_agreements_with_status_all(): void
    {
        SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-STATUS-PENDING',
            'amount' => 100.00,
            'status' => 'pending',
        ]);
        SupplierCreditAgreement::create([
            'supplier_id' => $this->supplier->id,
            'reference_folio' => 'NC-STATUS-APPLIED',
            'amount' => 200.00,
            'status' => 'applied',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/inventory/purchases/credit-agreements?status=all');

        $response->assertOk();
        $refs = collect($response->json('agreements'))->pluck('ref')->all();
        $this->assertContains('NC-STATUS-PENDING', $refs);
        $this->assertContains('NC-STATUS-APPLIED', $refs);
    }
}

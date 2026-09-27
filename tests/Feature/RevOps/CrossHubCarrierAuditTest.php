<?php

namespace Tests\Feature\RevOps;

use App\Models\OperationalTask;
use App\Models\Order;
use App\Models\PackageType;
use App\Models\Shipment;
use App\Models\ShippingDiscrepancyClaim;
use App\Models\ShippingReconciliation;
use App\Models\ShippingReconciliationItem;
use App\Models\User;
use App\Services\Finance\ShippingInvoiceAuditorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossHubCarrierAuditTest extends TestCase
{
    use RefreshDatabase;

    private function portalUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'finanzas.crosshub@mitienda.test'],
            [
                'name' => 'Auditor de Finanzas',
                'password' => bcrypt('password'),
                'role' => 'portal',
            ]
        );
    }

    public function test_iata_volumetric_calculation_and_expected_rate_formula(): void
    {
        $service = new ShippingInvoiceAuditorService();

        // IATA formula: (L * W * H) / 5000
        // (40 * 30 * 20) / 5000 = 24000 / 5000 = 4.80 kg
        $volumetricWeight = $service->calculateVolumetricWeightIata(40, 30, 20);
        $this->assertEquals(4.80, $volumetricWeight);

        // Expected rate for DHL: base 145 for 1kg, plus (4.80 - 1) * 25 = 3.8 * 25 = 95 => 240 MXN
        $expectedRate = $service->calculateExpectedRate('dhl', 4.80);
        $this->assertEquals(240.00, $expectedRate);

        // Zero dimensions should yield 0
        $zeroVolumetric = $service->calculateVolumetricWeightIata(0, 0, 0);
        $this->assertEquals(0.0, $zeroVolumetric);
    }

    public function test_reconcile_shipping_invoice_report_detects_overcharge_discrepancies(): void
    {
        $packageType = PackageType::create([
            'code' => 'BOX-AUDIT-M',
            'name' => 'Caja Mediana Estandar',
            'type' => 'box',
            'length_cm' => 30,
            'width_cm' => 25,
            'height_cm' => 20, // Volumetric: (30 * 25 * 20) / 5000 = 3.0 kg
            'unit_cost' => 15.00,
            'stock_available' => 50,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-AUDIT-101',
            'status' => 'shipped',
            'total' => 1250.00,
            'shipping_address' => ['city' => 'Guadalajara', 'state' => 'JAL'],
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'DHL-AUDIT-9901',
            'courier' => 'dhl',
            'status' => 'delivered',
            'weight_actual_kg' => 1.50,
            'package_type_id' => $packageType->id,
        ]);

        $rawCsv = "guia,peso_kg,costo_facturado,pedido\nDHL-AUDIT-9901,6.50,450.00,ORD-AUDIT-101";

        $service = app(ShippingInvoiceAuditorService::class);
        $recon = $service->auditCarrierInvoiceReport(
            carrierId: 'dhl',
            invoiceFolio: 'FAC-DHL-AUDIT-001',
            rawCsv: $rawCsv,
            auditedBy: 'Auditor Test'
        );

        $this->assertInstanceOf(ShippingReconciliation::class, $recon);
        $this->assertDatabaseHas('shipping_reconciliations', [
            'id' => $recon->id,
            'carrier' => 'dhl',
            'invoice_folio' => 'FAC-DHL-AUDIT-001',
            'discrepant_tracks_count' => 1,
        ]);

        $this->assertDatabaseHas('shipping_reconciliation_items', [
            'shipping_reconciliation_id' => $recon->id,
            'tracking_number' => 'DHL-AUDIT-9901',
            'audit_status' => 'discrepancy',
            'warehouse_volumetric_kg' => 3.0,
            'carrier_billed_weight_kg' => 6.50,
        ]);

        $item = $recon->items()->first();
        $this->assertNotNull($item);
        $this->assertTrue($item->isOvercharged());
        $this->assertGreaterThan(0, $item->overcharge_amount_mxn);
    }

    public function test_portal_web_upload_shipping_invoice_reconciles_successfully(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-WEB-AUDIT-201',
            'status' => 'shipped',
            'total' => 890.00,
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'EST-AUDIT-7701',
            'courier' => 'estafeta',
            'status' => 'delivered',
            'weight_actual_kg' => 2.00,
        ]);

        $rawCsv = "guia,peso_kg,costo_facturado,pedido\nEST-AUDIT-7701,2.00,120.00,ORD-WEB-AUDIT-201";

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.finance.shipping_audit.upload'), [
                'carrier_id' => 'estafeta',
                'invoice_folio' => 'FAC-EST-WEB-7701',
                'raw_csv' => $rawCsv,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shipping_reconciliations', [
            'carrier' => 'estafeta',
            'invoice_folio' => 'FAC-EST-WEB-7701',
        ]);
    }

    public function test_filing_shipping_discrepancy_claim_creates_claim_and_operational_task(): void
    {
        $recon = ShippingReconciliation::create([
            'reconciliation_code' => 'REC-COU-202609-TEST',
            'reconciliation_folio' => 'REC-COU-202609-TEST',
            'carrier' => 'fedex',
            'carrier_id' => 'fedex',
            'carrier_name' => 'FedEx Express',
            'invoice_folio' => 'FAC-FDX-CLAIM-01',
            'invoice_number' => 'FAC-FDX-CLAIM-01',
            'total_tracks_count' => 1,
            'matched_tracks_count' => 0,
            'discrepant_tracks_count' => 1,
            'total_invoiced_amount' => 500.00,
            'total_expected_amount' => 250.00,
            'total_overcharge_amount' => 250.00,
            'status' => 'discrepancy_detected',
            'audited_by' => 'Finanzas Lead',
        ]);

        $item = ShippingReconciliationItem::create([
            'shipping_reconciliation_id' => $recon->id,
            'reconciliation_id' => $recon->id,
            'tracking_number' => 'FDX-CLAIM-12345',
            'carrier' => 'fedex',
            'carrier_billed_weight_kg' => 10.0,
            'warehouse_weight_kg' => 3.0,
            'carrier_invoiced_rate_mxn' => 500.00,
            'expected_rate_mxn' => 250.00,
            'overcharge_amount_mxn' => 250.00,
            'overcharge_pct' => 100.0,
            'audit_status' => 'discrepancy',
            'discrepancy_reason' => 'Sobrepeso indebido reportado por carrier',
        ]);

        $service = app(ShippingInvoiceAuditorService::class);
        $claim = $service->fileCarrierClaimDossier(
            reconciliation: $recon,
            selectedItemIds: [$item->id],
            claimNotes: 'Bascula descalibrada en hub del carrier',
            createdBy: 'Auditor Test'
        );

        $this->assertInstanceOf(ShippingDiscrepancyClaim::class, $claim);
        $this->assertDatabaseHas('shipping_discrepancy_claims', [
            'id' => $claim->id,
            'carrier_id' => 'fedex',
            'total_disputed_amount' => 250.00,
            'disputed_items_count' => 1,
            'status' => 'submitted',
        ]);

        // Item status updated to claim_filed
        $item->refresh();
        $this->assertEquals('claim_filed', $item->audit_status);
        $this->assertEquals($claim->id, $item->shipping_discrepancy_claim_id);

        // Operational task created for payment retention hold
        $this->assertDatabaseHas('operational_tasks', [
            'category' => 'finance_discrepancy_hold',
            'priority' => 'high',
            'status' => 'pending',
            'reference_code' => $claim->claim_folio,
        ]);

        $task = OperationalTask::where('category', 'finance_discrepancy_hold')->latest()->first();
        $this->assertNotNull($task);
        $this->assertStringContainsString($claim->claim_folio, $task->title);
    }

    public function test_api_reconcile_and_claim_endpoints(): void
    {
        $user = $this->portalUser();

        // 1. API reconcile endpoint
        $csvContent = "guia,peso_kg,costo_facturado,pedido\nSKY-API-001,3.2,180.00,ORD-API-99";
        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/finance/shipping-audit/reconcile', [
                'carrier' => 'skydropx',
                'invoice_folio' => 'FAC-SKY-API-001',
                'raw_csv' => $csvContent,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'reconciliation' => [
                    'id',
                    'reconciliation_code',
                    'carrier',
                    'total_shipments_audited',
                ],
            ]);

        $reconId = $response->json('reconciliation.id');
        $item = ShippingReconciliationItem::where('shipping_reconciliation_id', $reconId)->first();

        // 2. API claim endpoint
        if ($item) {
            $claimResponse = $this->actingAs($user, 'web')
                ->postJson("/api/v1/finance/shipping-reconciliations/{$reconId}/file-claim", [
                    'item_ids' => [$item->id],
                    'notes' => 'Reclamacion via API automated audit',
                ]);

            $claimResponse->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonStructure([
                    'success',
                    'claim' => [
                        'id',
                        'claim_folio',
                        'carrier',
                    ],
                ]);
        }
    }
}

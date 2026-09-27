<?php

namespace Tests\Feature\RevOps;

use App\Models\OperationalTask;
use App\Models\TaxLedgerEntry;
use App\Models\ThreeWayMatching;
use App\Models\User;
use App\Models\VendorBill;
use App\Services\Finance\TaxCalculationEngineService;
use App\Services\Finance\ThreeWayMatchingService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossHubThreeWayMatchingTest extends TestCase
{
    use RefreshDatabase;

    private function portalUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'finanzas.matching@mitienda.test'],
            [
                'name' => 'Auditor Finanzas Inbound',
                'password' => bcrypt('password'),
                'role' => 'portal',
            ]
        );
    }

    public function test_inbound_match_with_identical_quantities_and_amounts_is_matched_and_unblocks_spei(): void
    {
        $service = app(ThreeWayMatchingService::class);

        $matching = $service->evaluateInboundMatch(
            poCode: 'OC-TEST-001',
            supplierId: 'VND-TORZO',
            supplierName: 'Torzo Labs',
            poQty: 100,
            poTotal: 15000.00, // $150 / unit
            whReceivedQty: 100,
            whDamagedQty: 0,
            invoiceNumber: 'FAC-PERFECT-01',
            invoiceQty: 100,
            invoiceGross: 15000.00,
            inspectedBy: 'Auditor Test'
        );

        $this->assertEquals('matched', $matching->match_status);
        $this->assertFalse($matching->spei_payout_blocked);
        $this->assertEquals(0.00, (float) $matching->debit_note_amount);
        $this->assertEquals(15000.00, (float) $matching->payable_net_amount);

        $this->assertDatabaseHas('three_way_matchings', [
            'id' => $matching->id,
            'po_code' => 'OC-TEST-001',
            'match_status' => 'matched',
            'spei_payout_blocked' => 0,
        ]);

        $this->assertDatabaseHas('vendor_bills', [
            'bill_number' => 'FAC-PERFECT-01',
            'amount' => 15000.00,
            'is_spei_blocked' => 0,
        ]);
    }

    public function test_inbound_match_with_missing_and_damaged_units_triggers_discrepancy_and_blocks_spei(): void
    {
        $service = app(ThreeWayMatchingService::class);

        // OC: 150 units at $150 = $22,500.00
        // Rampa: 142 received, 8 damaged => net good 134 units (16 missing/damaged total)
        // Invoice: 150 units, $22,500.00
        // Required debit note: 16 * $150 = $2,400.00
        // Payable net: 22,500 - 2,400 = $20,100.00
        $matching = $service->evaluateInboundMatch(
            poCode: 'OC-TEST-082',
            supplierId: 'VND-TORZO',
            supplierName: 'Torzo Labs',
            poQty: 150,
            poTotal: 22500.00,
            whReceivedQty: 142,
            whDamagedQty: 8,
            invoiceNumber: 'FAC-DAMAGED-02',
            invoiceQty: 150,
            invoiceGross: 22500.00
        );

        $this->assertEquals('discrepancy_held', $matching->match_status);
        $this->assertTrue($matching->spei_payout_blocked);
        $this->assertEquals(2400.00, (float) $matching->debit_note_amount);
        $this->assertEquals(20100.00, (float) $matching->payable_net_amount);

        // Assert operational task generated for payment retention hold
        $this->assertDatabaseHas('operational_tasks', [
            'category' => 'finance_matching_hold',
            'reference_code' => 'OC-TEST-082',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        // Vendor bill marked as blocked
        $this->assertDatabaseHas('vendor_bills', [
            'bill_number' => 'FAC-DAMAGED-02',
            'is_spei_blocked' => 1,
            'amount' => 20100.00,
        ]);
    }

    public function test_authorizing_spei_payout_on_blocked_matching_throws_exception(): void
    {
        $service = app(ThreeWayMatchingService::class);

        $matching = $service->evaluateInboundMatch(
            poCode: 'OC-BLOCKED-099',
            supplierId: 'VND-TORZO',
            supplierName: 'Torzo Labs',
            poQty: 100,
            poTotal: 10000.00,
            whReceivedQty: 80,
            whDamagedQty: 10,
            invoiceNumber: 'FAC-BLOCKED-01',
            invoiceQty: 100,
            invoiceGross: 10000.00
        );

        $this->assertTrue($matching->spei_payout_blocked);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('retencion preventiva activa');

        $service->authorizeSpeiPayout($matching->id);
    }

    public function test_issuing_debit_note_compensates_variance_and_unblocks_spei_payout(): void
    {
        $service = app(ThreeWayMatchingService::class);

        $matching = $service->evaluateInboundMatch(
            poCode: 'OC-REMEDY-101',
            supplierId: 'VND-TORZO',
            supplierName: 'Torzo Labs',
            poQty: 100,
            poTotal: 10000.00,
            whReceivedQty: 90,
            whDamagedQty: 0,
            invoiceNumber: 'FAC-REMEDY-01',
            invoiceQty: 100,
            invoiceGross: 10000.00
        );

        $this->assertTrue($matching->spei_payout_blocked);

        // Issue compensatory debit note
        $remedied = $service->issueDebitNote(
            matchingId: $matching->id,
            debitNoteNumber: 'NC-2026-8801'
        );

        $this->assertEquals('debit_note_issued', $remedied->match_status);
        $this->assertFalse($remedied->spei_payout_blocked);
        $this->assertEquals('NC-2026-8801', $remedied->debit_note_number);
        $this->assertEquals(9000.00, (float) $remedied->payable_net_amount);

        // Task resolved
        $this->assertDatabaseHas('operational_tasks', [
            'category' => 'finance_matching_hold',
            'reference_code' => 'OC-REMEDY-101',
            'status' => 'completed',
        ]);

        // Payout can now be authorized cleanly
        $approved = $service->authorizeSpeiPayout($remedied->id, 'SPEI-AUTH-889900');
        $this->assertEquals('approved_for_payment', $approved->match_status);

        // VendorBill updated to paid
        $this->assertDatabaseHas('vendor_bills', [
            'bill_number' => 'FAC-REMEDY-01',
            'status' => 'paid',
            'payment_reference' => 'SPEI-AUTH-889900',
        ]);
    }

    public function test_tax_calculation_engine_computes_sat_base_iva_and_retentions(): void
    {
        $taxEngine = app(TaxCalculationEngineService::class);

        // PVP Gross = 1160.00
        // Base Gravable = 1160 / 1.16 = 1000.00
        // IVA Trasladado (16%) = 160.00
        // Retencion ISR (1%) = 10.00
        // Retencion IVA (8%) = 80.00
        // IVA Neto = 160 - 80 = 80.00
        $entry = $taxEngine->computeOrderTaxes(
            pvpGross: 1160.00,
            orderRef: 'ORD-TAX-TEST-001',
            gateway: 'stripe',
            periodMonth: '2026-09'
        );

        $this->assertInstanceOf(TaxLedgerEntry::class, $entry);
        $this->assertEquals(1000.00, (float) $entry->taxable_base);
        $this->assertEquals(160.00, (float) $entry->iva_transferred_16);
        $this->assertEquals(10.00, (float) $entry->platform_retention_isr_1);
        $this->assertEquals(80.00, (float) $entry->platform_retention_iva_8);
        $this->assertEquals(80.00, (float) $entry->net_iva_payable);

        $summary = $taxEngine->getPeriodTaxSummary('2026-09');
        $this->assertEquals('2026-09', $summary['period']);
        $this->assertEquals(1160.00, $summary['kpis']['gross_revenue']);
        $this->assertEquals(1000.00, $summary['kpis']['taxable_base']);
        $this->assertEquals(80.00, $summary['kpis']['net_sat_iva_payable']);
    }

    public function test_api_endpoints_for_three_way_matching_and_tax_summary(): void
    {
        $user = $this->portalUser();

        // 1. POST verify endpoint
        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/finance/three-way-matching/verify', [
                'po_code' => 'OC-API-550',
                'supplier_id' => 'VND-API',
                'supplier_name' => 'Proveedor API B2B',
                'po_qty' => 50,
                'po_total_amount' => 7500.00,
                'warehouse_received_qty' => 45,
                'warehouse_damaged_qty' => 5,
                'invoice_number' => 'FAC-API-550',
                'invoice_qty' => 50,
                'invoice_gross_amount' => 7500.00,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('matching.match_status', 'discrepancy_held')
            ->assertJsonPath('matching.spei_payout_blocked', true);

        $matchingId = $response->json('matching.id');

        // 2. GET inbound-matching summary
        $summaryResp = $this->actingAs($user, 'web')
            ->getJson('/api/v1/finance/inbound-matching');

        $summaryResp->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'summary' => [
                    'total_inbound_audited',
                    'matched_count',
                    'discrepancy_held_count',
                    'total_held_amount_mxn',
                ],
                'records',
            ]);

        // 3. POST debit-note compensation
        $debitResp = $this->actingAs($user, 'web')
            ->postJson("/api/v1/finance/three-way-matching/{$matchingId}/debit-note", [
                'debit_note_number' => 'NC-API-9900',
            ]);

        $debitResp->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('matching.spei_payout_blocked', false);

        // 4. POST authorize-spei
        $speiResp = $this->actingAs($user, 'web')
            ->postJson("/api/v1/finance/three-way-matching/{$matchingId}/authorize-spei", [
                'payment_reference' => 'SPEI-API-SUCCESS',
            ]);

        $speiResp->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('matching.match_status', 'approved_for_payment');

        // 5. GET tax summary
        $taxResp = $this->actingAs($user, 'web')
            ->getJson('/api/v1/finance/taxes/summary?period=2026-09');

        $taxResp->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'period',
                'kpis' => [
                    'gross_revenue',
                    'taxable_base',
                    'iva_transferred_16',
                    'net_sat_iva_payable',
                ],
                'formulas',
            ]);
    }

    public function test_portal_web_actions_for_matching_debit_note_and_spei(): void
    {
        $user = $this->portalUser();

        // 1. Web verify 3-way matching
        $verifyResp = $this->actingAs($user)
            ->post(route('portal.finance.three_way_matching.verify'), [
                'po_code' => 'OC-WEB-770',
                'supplier_id' => 'VND-WEB',
                'supplier_name' => 'Textil Web CDMX',
                'po_qty' => 80,
                'po_total_amount' => 12000.00,
                'warehouse_received_qty' => 75,
                'warehouse_damaged_qty' => 5,
                'invoice_number' => 'FAC-WEB-770',
                'invoice_qty' => 80,
                'invoice_gross_amount' => 12000.00,
            ]);

        $verifyResp->assertRedirect();
        $verifyResp->assertSessionHas('success');

        $matching = ThreeWayMatching::where('po_code', 'OC-WEB-770')->first();
        $this->assertNotNull($matching);
        $this->assertTrue($matching->spei_payout_blocked);

        // 2. Web issue debit note
        $dnResp = $this->actingAs($user)
            ->post(route('portal.finance.three_way_matching.debit_note', $matching->id), [
                'debit_note_number' => 'NC-WEB-1234',
                'debit_amount' => 1500.00,
            ]);

        $dnResp->assertRedirect();
        $matching->refresh();
        $this->assertFalse($matching->spei_payout_blocked);

        // 3. Web authorize SPEI
        $speiResp = $this->actingAs($user)
            ->post(route('portal.finance.three_way_matching.authorize_spei', $matching->id), [
                'payment_reference' => 'SPEI-WEB-998877',
            ]);

        $speiResp->assertRedirect();
        $matching->refresh();
        $this->assertEquals('approved_for_payment', $matching->match_status);
    }
}

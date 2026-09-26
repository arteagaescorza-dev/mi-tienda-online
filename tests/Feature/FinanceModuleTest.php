<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PayoutBatch;
use App\Models\CustomerSubscription;
use App\Models\VendorBill;
use App\Models\OrderFinancialMargin;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;
    private function portalUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'portal.finance@test.com'],
            [
                'name' => 'Portal Finance Tester',
                'password' => bcrypt('secret123'),
                'role' => 'portal',
            ]
        );
    }

    public function test_portal_user_can_view_finance_hub_with_real_metrics_and_waterfall(): void
    {
        $response = $this->actingAs($this->portalUser())
            ->get(route('portal.finance'));

        $response->assertOk()
            ->assertSee('Finanzas')
            ->assertSee('Conciliación', false)
            ->assertSee('Flujo de Caja')
            ->assertSee('MRR')
            ->assertSee('Suscripciones')
            ->assertSee('Margen Neto Real')
            ->assertSee('Cuentas por Pagar')
            ->assertSee('Cascada de Absorción de Costos')
            ->assertSee('EBITDA');
    }

    public function test_can_reconcile_a_payout_batch(): void
    {
        $batch = PayoutBatch::firstOrCreate(
            ['payout_id_external' => 'po_test_reconcile_999'],
            [
                'gateway' => 'stripe',
                'gross_amount' => 10000.00,
                'fee_amount' => 360.00,
                'tax_withheld' => 60.00,
                'net_deposited' => 9580.00,
                'bank_account_target' => 'BBVA CLABE *9012',
                'status' => 'pending',
            ]
        );

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.finance.payouts.reconcile', $batch->id));

        $response->assertRedirect(route('portal.finance', ['tab' => 'reconciliation']));
        $this->assertDatabaseHas('payout_batches', [
            'id' => $batch->id,
            'status' => 'reconciled',
        ]);
    }

    public function test_can_retry_subscription_charge_via_dunning(): void
    {
        $sub = CustomerSubscription::firstOrCreate(
            ['subscription_folio' => 'SUB-TEST-DUNNING-01'],
            [
                'tier_name' => 'Caja Marte Nivel 1 (Básico)',
                'universe' => 'marte',
                'monthly_amount' => 599.00,
                'status' => 'past_due',
                'failed_attempts_count' => 2,
            ]
        );

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.finance.subscriptions.retry', $sub->id));

        $response->assertRedirect(route('portal.finance', ['tab' => 'subscriptions']));
        $this->assertDatabaseHas('customer_subscriptions', [
            'id' => $sub->id,
            'status' => 'active',
            'failed_attempts_count' => 0,
        ]);
        $this->assertDatabaseHas('dunning_retry_logs', [
            'subscription_id' => $sub->id,
            'was_recovered' => true,
        ]);
    }

    public function test_can_pay_vendor_bill_via_spei(): void
    {
        $bill = VendorBill::firstOrCreate(
            ['bill_number' => 'BILL-TEST-SPEI-01'],
            [
                'vendor_type' => 'apparel_workshop',
                'vendor_name' => 'Taller Textil Test',
                'amount' => 5000.00,
                'status' => 'pending',
                'bank_clabe' => '012180015678901234',
                'bank_name' => 'BBVA México',
            ]
        );

        $response = $this->actingAs($this->portalUser())
            ->post(route('portal.finance.vendor_bills.pay', $bill->id));

        $response->assertRedirect(route('portal.finance', ['tab' => 'expenses']));
        $this->assertDatabaseHas('vendor_bills', [
            'id' => $bill->id,
            'status' => 'paid',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyCouponDirB;
use App\Models\AllyLeadCapture;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Lead;
use App\Models\LeadInteraction;
use App\Models\OperationalTask;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\StockLock;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\OrderProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Hub7AlliesAndInterhub3217Test extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin.hub7@mitienda.online',
        ]);
    }

    public function test_clearing_settlement_spei_fails_if_ally_lacks_valid_18_digit_clabe(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-NOCLABE',
            'name' => 'Club Sin CLABE',
            'category' => 'nightclub',
            'contact_name' => 'Gerente Taquilla',
            'bank_clabe' => '12345', // Inválida (menos de 18 dígitos)
            'bank_name' => 'BBVA México',
            'clearing_balance' => -2500.00,
            'lead_acquisition_cost' => 50.00,
            'portal_token' => 'tok_' . Str::random(16),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('portal.allies.settlement.execute', $ally), [
                'settlement_type' => 'spei_transfer',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('CLABE', $response->json('message'));
        $this->assertDatabaseMissing('ally_clearing_settlements', [
            'ally_id' => $ally->id,
            'settlement_type' => 'spei_transfer',
        ]);
    }

    public function test_clearing_settlement_spei_succeeds_when_ally_has_valid_18_digit_clabe(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-VALIDCLABE',
            'name' => 'Paradiso VIP',
            'category' => 'nightclub',
            'contact_name' => 'Directora Relaciones',
            'bank_clabe' => '012180015678901234', // 18 dígitos exactos
            'bank_name' => 'BBVA Bancomer',
            'clearing_balance' => -5000.00,
            'lead_acquisition_cost' => 50.00,
            'portal_token' => 'tok_' . Str::random(16),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('portal.allies.settlement.execute', $ally), [
                'settlement_type' => 'spei_transfer',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('ally_clearing_settlements', [
            'ally_id' => $ally->id,
            'settlement_type' => 'spei_transfer',
            'status' => 'executed',
        ]);
    }

    public function test_ally_submitting_campaign_from_extranet_triggers_operational_task_and_notification(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-EXTRANET',
            'name' => 'Teatro Metropólitan B2B',
            'category' => 'cultural',
            'portal_token' => 'tok_secret_metropolitan_12345',
            'status' => 'active',
        ]);

        $response = $this->postJson(route('portal.ally.extranet.campaign.submit', ['token' => $ally->portal_token]), [
            'title' => 'Noche de Gala y Cóctel Exclusivo',
            'benefit' => '2x1 en accesos presentando voucher oficial',
            'instructions' => 'Acceso por puerta 4',
            'universe' => 'marte',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $campaign = AllyCampaignDirA::where('ally_id', $ally->id)->latest('id')->first();
        $this->assertNotNull($campaign);
        $this->assertEquals('pending_approval', $campaign->status);

        // Verificar que se haya creado la tarea operativa para Marketing en Hub 1
        $task = OperationalTask::where('reference_code', '#ALY-CMP-' . $campaign->id)->first();
        $this->assertNotNull($task);
        $this->assertEquals('marketing', $task->assigned_role);
        $this->assertEquals('high', $task->priority);
        $this->assertEquals('pending', $task->status);

        // Verificar despacho de notificación con deep link
        $notif = UserNotification::where('operational_task_id', $task->id)->first();
        $this->assertNotNull($notif);
        $this->assertEquals('marketing', $notif->target_role);
        $this->assertStringContainsString('/admin/convenios', $notif->deep_link);
    }

    public function test_approving_or_rejecting_campaign_completes_operational_task(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-FESTIVAL',
            'name' => 'Festival Primavera',
            'category' => 'events',
            'portal_token' => 'tok_festival_12345',
            'status' => 'active',
        ]);

        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => 'Pase VIP Primavera',
            'slug' => 'pase-vip-primavera-123',
            'benefit' => 'Acceso zona lounge',
            'status' => 'pending_approval',
        ]);

        $task = OperationalTask::create([
            'title' => 'Revisión Convenio: Festival Primavera',
            'reference_code' => '#ALY-CMP-' . $campaign->id,
            'category' => 'convenios',
            'priority' => 'high',
            'assigned_role' => 'marketing',
            'status' => 'pending',
            'action_payload' => ['campaign_id' => $campaign->id],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('portal.allies.campaign.approve', ['id' => $campaign->id]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $campaign->refresh();
        $this->assertEquals('active', $campaign->status);

        $task->refresh();
        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_ally_lead_capture_observer_synchronizes_with_hub3_crm_and_reengages_won_or_lost_leads(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-CRM-TEST',
            'name' => 'Gimnasio Metropolis',
            'category' => 'fitness',
            'portal_token' => 'tok_' . Str::random(16),
            'status' => 'active',
        ]);

        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => 'Membresía Free Pass 7 Días',
            'slug' => 'membresia-free-pass-7-dias',
            'benefit' => 'Pase de cortesía por 7 días',
            'status' => 'active',
        ]);

        // Lead existente previo en estado 'lost' (cerrado/perdido en CRM)
        $existingLead = Lead::create([
            'name' => 'Carlos Prospecto',
            'email' => 'carlos.prospecto@gmail.com',
            'phone' => '5511223344',
            'source' => 'facebook_ads',
            'status' => 'lost',
            'universe' => 'marte',
            'lead_score' => 40,
        ]);

        // Simular emisión de voucher en Hub 7
        $capture = AllyLeadCapture::create([
            'ally_id' => $ally->id,
            'campaign_id' => $campaign->id,
            'lead_name' => 'Carlos Prospecto Actualizado',
            'lead_email' => 'carlos.prospecto@gmail.com',
            'lead_whatsapp' => '5511223344',
            'universe' => 'marte',
            'voucher_code' => '#METRO-7788',
            'is_redeemed' => false,
        ]);

        $existingLead->refresh();

        // Debe reactivarse a 'new' para la fuerza de ventas de Hub 3
        $this->assertEquals('new', $existingLead->status);
        $this->assertEquals('ally_campaign', $existingLead->source);
        $this->assertEquals('#METRO-7788', $existingLead->metadata['last_voucher_code']);
        $this->assertEquals($ally->id, $existingLead->metadata['last_ally_id']);

        // Debe haber generado una interacción en el historial forense de CRM
        $interaction = LeadInteraction::where('lead_id', $existingLead->id)->latest('id')->first();
        $this->assertNotNull($interaction);
        $this->assertEquals('voucher_issued', $interaction->interaction_type);
        $this->assertEquals('ally_campaign', $interaction->channel);
    }

    public function test_checkout_rejects_archived_or_paused_ally_coupons_with_validation_exception(): void
    {
        $ally = Ally::create([
            'code' => 'ALLY-COUPON-TEST',
            'name' => 'Circuito B2B',
            'category' => 'events',
            'portal_token' => 'tok_' . Str::random(16),
            'status' => 'active',
        ]);

        $coupon = AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'CIRCUITO25',
            'discount_pct' => 25.00,
            'commission_rate' => 10.00,
            'status' => 'active',
            'utm_source' => 'circuito_b2b',
            'utm_campaign' => 'circuito_vip',
        ]);

        $orderProcessing = app(OrderProcessingService::class);

        // 1. Cupón activo -> válido
        $val1 = $orderProcessing->validateCoupon('CIRCUITO25', 1000.00);
        $this->assertTrue($val1['valid']);
        $this->assertEquals(250.00, $val1['discount_amount']);

        // 2. Archivar aliado -> cupón pasa a 'paused'
        $this->actingAs($this->adminUser)->postJson(route('portal.allies.archive', $ally));
        $coupon->refresh();
        $this->assertEquals('paused', $coupon->status);

        // 3. Validar de nuevo -> no es válido
        $val2 = $orderProcessing->validateCoupon('CIRCUITO25', 1000.00);
        $this->assertFalse($val2['valid']);

        // 4. Intentar procesar transacción de checkout con el cupón pausado debe arrojar ValidationException
        $product = Product::create([
            'sku' => 'TEST-CHK-01',
            'slug' => 'test-chk-01-slug',
            'title' => 'Prenda para Prueba Cupón',
            'price' => 500.00,
            'cost' => 150.00,
            'type' => 'physical',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-CHK-01-M',
            'price' => 500.00,
            'cost' => 150.00,
            'attributes' => ['size' => 'M'],
        ]);
        Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => 10,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $cartId = 'cart_chk_test_' . Str::random(8);
        $cart = Cart::create([
            'session_id' => $cartId,
            'status' => 'active',
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 500.00,
        ]);

        $lock = StockLock::create([
            'cart_id' => $cartId,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'status' => 'active',
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->expectException(ValidationException::class);

        $orderProcessing->processCheckout([
            'cart_id' => $cartId,
            'customer_email' => 'comprador@multiverso.test',
            'customer_name' => 'Comprador Multiverso',
            'customer_phone' => '5599887766',
            'coupon_code' => 'CIRCUITO25',
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 500.00,
                ],
            ],
            'shipping_address' => [
                'street' => 'Av. Revolución',
                'exterior_number' => '450',
                'postal_code' => '03800',
                'colonia' => 'San Pedro de los Pinos',
                'city' => 'CDMX',
                'state' => 'CDMX',
            ],
            'payment_method' => 'card',
        ]);
    }
}

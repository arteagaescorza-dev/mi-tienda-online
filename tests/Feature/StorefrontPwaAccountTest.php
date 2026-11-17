<?php

namespace Tests\Feature;

use App\Models\CustomerSubscription;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPwaAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function createSampleOrder(string $folio = '#ORD-2026-1001', string $email = 'comprador@torzo.mx', string $status = 'processing'): Order
    {
        $product = Product::create([
            'title' => 'Brief Torzo Anatomico Carbon',
            'slug' => 'brief-torzo-carbon',
            'sku' => 'TRZ-BRF-001',
            'price' => 389.00,
            'cost' => 110.00,
            'visible' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TRZ-BRF-001-M',
            'attributes' => ['size' => 'M'],
            'price' => 389.00,
            'cost' => 110.00,
            'visible' => true,
        ]);

        $order = Order::create([
            'order_number' => $folio,
            'subtotal' => 389.00,
            'shipping_cost' => 149.00,
            'discount_amount' => 0.00,
            'total' => 538.00,
            'status' => $status,
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'currency' => 'MXN',
            'shipping_address' => [
                'name' => 'Roberto Gomez',
                'email' => $email,
                'phone' => '5512345678',
                'street' => 'Av. Insurgentes Sur 120',
                'postal_code' => '06700',
                'colony' => 'Roma Norte',
                'city' => 'Cuauhtemoc',
                'state' => 'Ciudad de Mexico',
            ],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 389.00,
            'cost_price' => 110.00,
            'line_total' => 389.00,
            'fulfillment_status' => 'fulfilled',
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'courier' => 'dhl_express',
            'tracking_number' => 'DHL-MX-88992211',
            'status' => 'in_transit',
            'shipping_cost' => 149.00,
            'label_url' => 'https://shipping.torzo.mx/labels/DHL-MX-88992211.pdf',
        ]);

        return $order;
    }

    public function test_can_query_orders_by_email(): void
    {
        $this->createSampleOrder('#ORD-2026-1001', 'comprador@torzo.mx');

        $response = $this->getJson('/api/v1/store/account/orders?email=comprador@torzo.mx');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'order_number' => '#ORD-2026-1001',
                'tracking_number' => 'DHL-MX-88992211',
                'carrier' => 'DHL Express Nacional',
            ]);
    }

    public function test_can_query_orders_by_folio(): void
    {
        $this->createSampleOrder('#ORD-2026-9999', 'cliente@marte.com');

        $response = $this->getJson('/api/v1/store/account/orders?folio=' . urlencode('#ORD-2026-9999'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.order_number', '#ORD-2026-9999');
    }

    public function test_can_track_order_live_telemetry(): void
    {
        $this->createSampleOrder('#ORD-2026-7777', 'tracking@torzo.mx');

        $response = $this->getJson('/api/v1/store/account/tracking/%23ORD-2026-7777');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tracking' => [
                    'found' => true,
                    'order_number' => '#ORD-2026-7777',
                    'carrier' => 'DHL Express Nacional',
                    'tracking_number' => 'DHL-MX-88992211',
                    'shipment_status' => 'in_transit',
                ],
            ]);

        $this->assertCount(4, $response->json('tracking.timeline'));
        $this->assertTrue($response->json('tracking.timeline.0.completed'));
        $this->assertTrue($response->json('tracking.timeline.1.completed'));
        $this->assertTrue($response->json('tracking.timeline.2.completed'));
    }

    public function test_tracking_nonexistent_order_returns_404(): void
    {
        $response = $this->getJson('/api/v1/store/account/tracking/NONEXISTENT-FOLIO');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_mixed_order_tracking_identifies_dual_branch(): void
    {
        $order = $this->createSampleOrder('#MIX-2026-5555', 'mixto@torzo.mx');

        $response = $this->getJson('/api/v1/store/account/tracking/%23MIX-2026-5555');

        $response->assertStatus(200)
            ->assertJsonPath('tracking.is_mixed', true)
            ->assertJsonPath('tracking.order_number', '#MIX-2026-5555');
    }

    public function test_can_get_subscriptions_and_available_mrr_tiers(): void
    {
        $response = $this->getJson('/api/v1/store/account/subscriptions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'subscriptions',
                'available_tiers' => [
                    '*' => ['id', 'tier_name', 'price', 'universe', 'benefits'],
                ],
            ]);

        $this->assertCount(3, $response->json('available_tiers'));
    }

    public function test_can_subscribe_to_mrr_tier(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/store/account/subscriptions/subscribe', [
            'tier_name' => 'Caja Marte Nivel 2 (Plus)',
            'monthly_amount' => 599.00,
            'universe' => 'marte',
            'card_last4' => '4242',
            'card_brand' => 'Visa',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'subscription' => [
                    'tier_name' => 'Caja Marte Nivel 2 (Plus)',
                    'monthly_amount' => 599.00,
                    'status' => 'active',
                    'card_last4' => '4242',
                ],
            ]);

        $this->assertDatabaseHas('customer_subscriptions', [
            'tier_name' => 'Caja Marte Nivel 2 (Plus)',
            'status' => 'active',
        ]);
    }

    public function test_can_pause_and_resume_subscription(): void
    {
        $subscription = CustomerSubscription::create([
            'subscription_folio' => '#SUB-2026-TEST01',
            'tier_name' => 'Caja Marte Nivel 1 (Basico)',
            'monthly_amount' => 399.00,
            'status' => 'active',
            'card_last4' => '1234',
            'card_brand' => 'Mastercard',
            'next_billing_at' => now()->addMonth(),
        ]);

        // Pausar
        $pauseRes = $this->postJson("/api/v1/store/account/subscriptions/{$subscription->id}/pause");
        $pauseRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'subscription' => [
                    'status' => 'paused',
                ],
            ]);

        $this->assertEquals('paused', $subscription->fresh()->status);

        // Reanudar
        $resumeRes = $this->postJson("/api/v1/store/account/subscriptions/{$subscription->id}/resume");
        $resumeRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'subscription' => [
                    'status' => 'active',
                ],
            ]);

        $this->assertEquals('active', $subscription->fresh()->status);
    }

    public function test_can_update_anatomical_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/store/account/profile', [
            'waist_cm' => 84,
            'preferred_cut' => 'jockstrap',
            'silhouettes' => [
                'jocks' => true,
                'boxers' => false,
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'profile' => [
                    'waist_cm' => 84,
                    'recommended_size' => 'M',
                    'preferred_cut' => 'jockstrap',
                ],
            ]);
    }

    public function test_customer_can_register_and_login_via_auth_modal_endpoints(): void
    {
        $reg = $this->postJson('/auth/register', [
            'name' => 'Cliente Real Prueba',
            'email' => 'cliente.real@ejemplo.com',
            'phone' => '5511223344',
            'password' => 'Secreta1234!',
            'password_confirmation' => 'Secreta1234!',
        ]);

        $reg->assertStatus(201)
            ->assertJsonPath('user.email', 'cliente.real@ejemplo.com')
            ->assertJsonPath('user.role', 'customer');

        $this->assertDatabaseHas('users', [
            'email' => 'cliente.real@ejemplo.com',
            'role' => 'customer',
        ]);

        $this->postJson('/auth/logout')->assertOk();

        $login = $this->postJson('/auth/login', [
            'email' => 'cliente.real@ejemplo.com',
            'password' => 'Secreta1234!',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.email', 'cliente.real@ejemplo.com');
    }
}

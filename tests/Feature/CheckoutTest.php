<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_screens_are_reachable_for_an_authenticated_customer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/checkout')->assertOk()->assertSee('Tu carrito');
        $this->actingAs($user)->get('/checkout/datos')->assertOk()->assertSee('Datos de envio');
        $this->actingAs($user)->get('/checkout/pago')->assertOk()->assertSee('Metodo de pago');
    }

    public function test_cart_route_renders_the_shared_checkout_progress_indicator(): void
    {
        $response = $this->get('/carrito');

        $response->assertOk()
            ->assertSee('Progreso del checkout')
            ->assertSee('Carrito')
            ->assertSee('Datos')
            ->assertSee('Pago')
            ->assertSee('Confirmacion');

        $this->assertSame(1, substr_count($response->getContent(), 'class="checkout-progress"'));
    }

    public function test_user_can_create_an_order_from_an_active_cart(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant();
        $cart = $this->createCartWithItem($user, $variant, 2);
        Inventory::create(['variant_id' => $variant->id, 'quantity' => 5, 'reserved' => 0]);

        $response = $this->actingAs($user)->postJson('/checkout', $this->validAddress());

        $response->assertCreated();
        $response->assertJsonPath('order.user_id', $user->id);
        $response->assertJsonPath('order.total', '39.98');
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 39.98]);
        $this->assertDatabaseHas('orders', ['id' => $response->json('order.id')]);
        $this->assertSame('64000', $response->json('order.shipping_address.postal_code'));
        $this->assertSame('paqueteria', $response->json('order.shipping_address.delivery_method'));
        $this->assertDatabaseHas('order_items', ['variant_id' => $variant->id, 'quantity' => 2, 'line_total' => 39.98]);
        $this->assertDatabaseHas('inventories', ['variant_id' => $variant->id, 'reserved' => 2]);
        $this->assertDatabaseHas('carts', ['id' => $cart->id, 'status' => 'ordered']);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function test_checkout_rejects_an_empty_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/checkout', $this->validAddress());

        $response->assertUnprocessable();
        $response->assertJsonPath('errors.cart.0', 'El carrito esta vacio.');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_does_not_create_an_order_when_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant();
        $cart = $this->createCartWithItem($user, $variant, 2);
        Inventory::create(['variant_id' => $variant->id, 'quantity' => 1, 'reserved' => 0]);

        $response = $this->actingAs($user)->postJson('/checkout', $this->validAddress());

        $response->assertUnprocessable();
        $response->assertJsonPath('errors.cart.0', "Stock insuficiente para la variante {$variant->id}.");
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('inventories', ['variant_id' => $variant->id, 'reserved' => 0]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id, 'quantity' => 2]);
    }

    public function test_checkout_applies_coupon_and_calculates_financial_margin(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant();
        $this->createCartWithItem($user, $variant, 2);
        Inventory::create(['variant_id' => $variant->id, 'quantity' => 5, 'reserved' => 0]);

        \App\Models\Coupon::create([
            'code' => 'PROMO20',
            'title' => 'Descuento 20%',
            'discount_type' => 'percent',
            'discount_value' => 20.00,
            'min_order_amount' => 10.00,
            'is_active' => true,
        ]);

        $payload = array_merge($this->validAddress(), [
            'coupon_code' => 'PROMO20',
            'universe' => 'terra',
        ]);

        $response = $this->actingAs($user)->postJson('/checkout', $payload);

        $response->assertCreated();
        // 39.98 - 20% (8.00) = 31.98
        $response->assertJsonPath('order.total', '31.98');
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 31.98]);

        // Verify Coupon usage metrics
        $this->assertDatabaseHas('coupons', [
            'code' => 'PROMO20',
            'used_count' => 1,
            'revenue_generated' => 31.98,
        ]);

        // Verify Financial Margin record
        $this->assertDatabaseHas('order_financial_margins', [
            'order_id' => $response->json('order.id'),
            'universe' => 'terra',
            'gross_revenue' => 31.98,
            'shipping_courier_cost' => 125.00,
            'packaging_cost' => 16.00,
        ]);

        // Verify Courier Shipment record
        $this->assertDatabaseHas('shipments', [
            'order_id' => $response->json('order.id'),
            'courier' => 'dhl_express',
            'status' => 'label_created',
        ]);
    }

    public function test_checkout_attributes_order_to_ally_coupon_and_calculates_commissions(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->for(Product::factory())->create(['price' => 50.00]);
        $this->createCartWithItem($user, $variant, 2); // 100.00
        Inventory::create(['variant_id' => $variant->id, 'quantity' => 10, 'reserved' => 0]);

        $ally = \App\Models\Ally::create([
            'code' => 'ALLY-TEST-01',
            'name' => 'Circuito Fiesta Monterrey',
            'category' => 'circuit_party',
            'portal_token' => 'token-test-12345',
            'commission_rate' => 12.00,
        ]);

        \App\Models\AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'FIESTA15',
            'discount_pct' => 15.00,
            'commission_rate' => 12.00,
            'universe' => 'marte',
            'utm_source' => 'instagram_story',
            'utm_campaign' => 'fiesta_marte_2026',
            'status' => 'active',
        ]);

        $payload = array_merge($this->validAddress(), [
            'coupon_code' => 'FIESTA15',
            'universe' => 'marte',
        ]);

        $response = $this->actingAs($user)->postJson('/checkout', $payload);

        $response->assertCreated();
        // 100.00 - 15% (15.00) = 85.00
        $response->assertJsonPath('order.total', '85.00');

        // Verify Ally Coupon metrics: 85.00 revenue, 12% commission = 10.20
        $this->assertDatabaseHas('ally_coupons_dir_b', [
            'coupon_code' => 'FIESTA15',
            'orders_count' => 1,
            'revenue_attributed' => 85.00,
            'commission_earned' => 10.20,
        ]);
    }

    public function test_checkout_with_personal_pickup_sets_zero_shipping_cost_and_no_shipment(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant();
        $this->createCartWithItem($user, $variant, 1);
        Inventory::create(['variant_id' => $variant->id, 'quantity' => 5, 'reserved' => 0]);

        $payload = array_merge($this->validAddress(), [
            'delivery_method' => 'personal',
        ]);

        $response = $this->actingAs($user)->postJson('/checkout', $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('order_financial_margins', [
            'order_id' => $response->json('order.id'),
            'shipping_courier_cost' => 0.00,
        ]);
        $this->assertDatabaseMissing('shipments', [
            'order_id' => $response->json('order.id'),
        ]);
    }

    private function createVariant(): ProductVariant
    {
        return ProductVariant::factory()->for(Product::factory())->create(['price' => 19.99]);
    }

    private function createCartWithItem(User $user, ProductVariant $variant, int $quantity): Cart
    {
        $cart = Cart::create(['user_id' => $user->id, 'status' => 'active']);

        CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
            'price' => $variant->price,
        ]);

        return $cart;
    }

    private function validAddress(): array
    {
        return [
            'name' => 'Cliente Prueba',
            'email' => 'cliente@example.com',
            'mobile' => '8112345678',
            'birth_date' => '1990-05-10',
            'street' => 'Calle Uno',
            'exterior_number' => '123',
            'interior_number' => '4B',
            'neighborhood' => 'Centro',
            'postal_code' => '64000',
            'city' => 'Monterrey',
            'state' => 'Nuevo Leon',
            'delivery_references' => 'Frente al parque',
            'delivery_method' => 'paqueteria',
        ];
    }
}
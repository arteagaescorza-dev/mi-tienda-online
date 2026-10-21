<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\AllyCouponDirB;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderFinancialMargin;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\StockLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPwaCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function createProductWithStock(string $sku, int $quantity = 10, float $price = 450.00, float $cost = 120.00, string $brand = 'Dajo', string $type = 'physical'): array
    {
        $product = Product::create([
            'title' => 'Prenda Anatómica ' . $sku,
            'slug' => 'prenda-' . strtolower($sku),
            'sku' => $sku,
            'price' => $price,
            'cost' => $cost,
            'type' => $type,
            'visible' => true,
            'brand' => $brand,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku . '-M',
            'attributes' => ['size' => 'M', 'color' => 'Negro'],
            'price' => $price,
            'cost' => $cost,
            'visible' => true,
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-1',
            'quantity' => $quantity,
            'reserved' => 0,
        ]);

        return [$product, $variant, $inventory];
    }

    public function test_can_validate_postal_code_and_calculates_shipping_fee(): void
    {
        // Subtotal menor a $999 -> flete $149 MXN
        $response = $this->postJson('/api/v1/store/postal-code/lookup', [
            'postal_code' => '06000',
            'subtotal' => 450.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'is_valid' => true,
            'shipping_fee' => 149.00,
            'free_shipping_eligible' => false,
        ]);

        // Subtotal mayor o igual a $999 -> flete GRATIS
        $responseFree = $this->postJson('/api/v1/store/postal-code/lookup', [
            'postal_code' => '06000',
            'subtotal' => 1200.00,
        ]);

        $responseFree->assertStatus(200);
        $responseFree->assertJson([
            'is_valid' => true,
            'shipping_fee' => 0.00,
            'free_shipping_eligible' => true,
        ]);
    }

    public function test_can_validate_standard_coupon(): void
    {
        Coupon::create([
            'code' => 'TORZO10',
            'discount_type' => 'percent',
            'discount_value' => 10.00,
            'min_order_amount' => 300.00,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/store/coupons/validate', [
            'code' => 'TORZO10',
            'subtotal' => 500.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'type' => 'standard',
            'code' => 'TORZO10',
            'discount_pct' => 10.00,
            'discount_amount' => 50.00,
        ]);
    }

    public function test_can_validate_ally_coupon_b2b(): void
    {
        $ally = Ally::create([
            'code' => 'ALY-PRIDE',
            'name' => 'Circuito Pride CDMX',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'contact_name' => 'Rodrigo Morales',
            'contact_email' => 'rodrigo@circuitopride.mx',
            'portal_token' => 'tok_pride_123',
            'clearing_balance' => 0.00,
            'status' => 'active',
        ]);

        AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'PRIDE15',
            'discount_pct' => 15.00,
            'commission_rate' => 12.00,
            'universe' => 'marte',
            'utm_source' => 'circuito_pride',
            'utm_campaign' => 'verano_2026',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/store/coupons/validate', [
            'code' => 'PRIDE15',
            'subtotal' => 1000.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'type' => 'ally_b2b',
            'code' => 'PRIDE15',
            'discount_pct' => 15.00,
            'discount_amount' => 150.00,
            'ally_id' => $ally->id,
        ]);
    }

    public function test_checkout_transmutes_stock_lock_to_purchased_and_deducts_inventory_and_records_kardex(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithStock('TEST-ORD-01', 10, 500.00, 150.00);

        // 1. Agregar prenda al carrito (genera stock_lock y reserved = 2)
        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_chk_transact_01',
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('stock_locks', [
            'cart_id' => 'sess_chk_transact_01',
            'product_variant_id' => $variant->id,
            'status' => 'active',
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 10,
            'reserved' => 2,
        ]);

        // 2. Ejecutar Checkout
        $checkoutData = [
            'cart_id' => 'sess_chk_transact_01',
            'customer' => [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos@ejemplo.com',
                'phone' => '5598765432',
            ],
            'shipping' => [
                'street' => 'Av. Insurgentes Sur',
                'exterior_number' => '1200',
                'colony' => 'Del Valle',
                'postal_code' => '03100',
                'city' => 'Benito Juarez',
                'state' => 'Ciudad de Mexico',
                'references' => 'Entre Eje 6 y Eje 7',
            ],
            'payment_method' => 'spei_bbva',
            'universe' => 'marte',
        ];

        $response = $this->postJson('/api/v1/store/checkout', $checkoutData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Compra formalizada con exito.',
        ]);

        $orderNumber = $response->json('order.order_number');
        $this->assertStringStartsWith('#ORD-2026-', $orderNumber);

        // 3. Verificar que el candado transmuto a 'purchased'
        $this->assertDatabaseHas('stock_locks', [
            'cart_id' => 'sess_chk_transact_01',
            'product_variant_id' => $variant->id,
            'status' => 'purchased',
        ]);

        // 4. Verificar descuento fisico y liberacion de reservado en Inventario
        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 8,
            'reserved' => 0,
        ]);

        // 5. Verificar asiento inmutable en Kardex
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => -2,
            'quantity_before' => 10,
            'quantity_after' => 8,
        ]);

        // 6. Verificar creación de Orden y Margen Financiero
        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber,
            'payment_method' => 'spei_bbva',
        ]);

        $this->assertDatabaseHas('order_financial_margins', [
            'order_number' => $orderNumber,
            'universe' => 'marte',
        ]);

        // 7. Verificar guia de envio DHL
        $this->assertDatabaseHas('shipments', [
            'courier' => 'dhl_express',
        ]);
    }

    public function test_mixed_cart_generates_mix_folio_prefix(): void
    {
        [$prodA, $varA] = $this->createProductWithStock('MIX-PHYSICAL', 5, 450.00);
        [$prodB, $varB] = $this->createProductWithStock('MIX-ONDEMAND', 0, 600.00);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_mixed_checkout',
            'variant_id' => $varA->id,
            'quantity' => 1,
        ]);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_mixed_checkout',
            'variant_id' => $varB->id,
            'quantity' => 1,
        ]);

        $checkoutData = [
            'cart_id' => 'sess_mixed_checkout',
            'customer' => [
                'name' => 'Alejandro Vega',
                'email' => 'alejandro@ejemplo.com',
                'phone' => '5588776655',
            ],
            'shipping' => [
                'street' => 'Calle Amberes',
                'exterior_number' => '45',
                'colony' => 'Juarez',
                'postal_code' => '06600',
                'city' => 'Cuauhtemoc',
                'state' => 'Ciudad de Mexico',
            ],
            'payment_method' => 'credit_debit_card',
            'universe' => 'tiresias',
        ];

        $response = $this->postJson('/api/v1/store/checkout', $checkoutData);

        $response->assertStatus(201);
        $orderNumber = $response->json('order.order_number');
        $this->assertStringStartsWith('#MIX-2026-', $orderNumber);

        // Prenda fisica entregada/descontada, prenda on-demand en backorder
        $this->assertDatabaseHas('order_items', [
            'variant_id' => $varA->id,
            'fulfillment_status' => 'fulfilled',
        ]);

        $this->assertDatabaseHas('order_items', [
            'variant_id' => $varB->id,
            'fulfillment_status' => 'backorder',
        ]);
    }

    public function test_mixed_cart_with_scraped_product_generates_mix_folio_and_backorders_scraped_item(): void
    {
        [$prodA, $varA] = $this->createProductWithStock('MIX-PHYS-02', 10, 400.00, 100.00, 'Dajo', 'physical');
        // Scraped product has 50 units in warehouse-1, but because it is scraped/dropship, it must be Line B
        [$prodScraped, $varScraped] = $this->createProductWithStock('MIX-SCRAPED-02', 50, 700.00, 250.00, 'Torzo', 'dropship');

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_scraped_checkout',
            'variant_id' => $varA->id,
            'quantity' => 1,
        ]);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_scraped_checkout',
            'variant_id' => $varScraped->id,
            'quantity' => 1,
        ]);

        $checkoutData = [
            'cart_id' => 'sess_scraped_checkout',
            'customer' => [
                'name' => 'Luis Hernandez',
                'email' => 'luis@ejemplo.com',
                'phone' => '5511223344',
            ],
            'shipping' => [
                'street' => 'Calle Londres',
                'exterior_number' => '10',
                'colony' => 'Juarez',
                'postal_code' => '06600',
                'city' => 'Cuauhtemoc',
                'state' => 'Ciudad de Mexico',
            ],
            'payment_method' => 'spei_bbva',
            'universe' => 'marte',
        ];

        $response = $this->postJson('/api/v1/store/checkout', $checkoutData);

        $response->assertStatus(201);
        $orderNumber = $response->json('order.order_number');
        $this->assertStringStartsWith('#MIX-2026-', $orderNumber);

        // Physical item fulfilled, Scraped item marked as backorder
        $this->assertDatabaseHas('order_items', [
            'variant_id' => $varA->id,
            'fulfillment_status' => 'fulfilled',
        ]);

        $this->assertDatabaseHas('order_items', [
            'variant_id' => $varScraped->id,
            'fulfillment_status' => 'backorder',
        ]);
    }

    public function test_checkout_with_ally_coupon_attributes_ally_and_increments_commission(): void
    {
        [$product, $variant] = $this->createProductWithStock('ALLY-COMM-01', 5, 800.00);

        $ally = Ally::create([
            'code' => 'ALY-SPARTACUS',
            'name' => 'Club Spartacus',
            'category' => 'circuit_party',
            'universe' => 'marte',
            'contact_name' => 'Javier Ramos',
            'contact_email' => 'javier@spartacus.mx',
            'portal_token' => 'tok_spartacus_456',
            'clearing_balance' => 0.00,
            'status' => 'active',
        ]);

        $allyCoupon = AllyCouponDirB::create([
            'ally_id' => $ally->id,
            'coupon_code' => 'SPARTACUS20',
            'discount_pct' => 20.00,
            'commission_rate' => 10.00,
            'universe' => 'marte',
            'utm_source' => 'club_spartacus',
            'utm_campaign' => 'evento_aniversario',
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_ally_chk',
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/v1/store/checkout', [
            'cart_id' => 'sess_ally_chk',
            'coupon_code' => 'SPARTACUS20',
            'customer' => ['name' => 'Ramiro Ortiz', 'email' => 'ramiro@ejemplo.com', 'phone' => '5544332211'],
            'shipping' => ['street' => 'Calle Londres', 'exterior_number' => '10', 'colony' => 'Juarez', 'postal_code' => '06600', 'city' => 'CDMX', 'state' => 'CDMX'],
            'payment_method' => 'spei_bbva',
        ]);

        $response->assertStatus(201);
        $orderNumber = $response->json('order.order_number');

        $order = Order::where('order_number', $orderNumber)->first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->attributed_via_id);

        $allyCoupon->refresh();
        $this->assertEquals(1, $allyCoupon->orders_count);
        $this->assertGreaterThan(0, (float) $allyCoupon->commission_earned);
    }

    public function test_checkout_fails_gracefully_with_empty_cart(): void
    {
        $response = $this->postJson('/api/v1/store/checkout', [
            'cart_id' => 'sess_totally_empty',
            'customer' => ['name' => 'Test User', 'email' => 'test@test.com', 'phone' => '5500000000'],
            'shipping' => ['street' => 'Test', 'exterior_number' => '1', 'colony' => 'Test', 'postal_code' => '06000', 'city' => 'CDMX', 'state' => 'CDMX'],
            'payment_method' => 'spei_bbva',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }
}

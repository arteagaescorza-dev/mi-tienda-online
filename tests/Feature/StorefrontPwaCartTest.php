<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPwaCartTest extends TestCase
{
    use RefreshDatabase;

    protected function createProductWithStock(string $sku, int $quantity = 10, float $price = 450.00, string $brand = 'Dajo', string $type = 'physical'): array
    {
        $product = Product::create([
            'title' => 'Jockstrap Ergonomico ' . $sku,
            'slug' => 'jockstrap-' . strtolower($sku),
            'sku' => $sku,
            'price' => $price,
            'cost' => 120.00,
            'type' => $type,
            'visible' => true,
            'brand' => $brand,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku . '-M',
            'attributes' => ['size' => 'M', 'color' => 'Negro'],
            'price' => $price,
            'cost' => 120.00,
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

    public function test_can_get_cart_details_via_api_and_defaults_to_empty(): void
    {
        $response = $this->getJson('/api/v1/store/cart?cart_id=sess_empty_test');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cart_id' => 'sess_empty_test',
            'summary' => [
                'total_items' => 0,
                'subtotal' => 0,
                'has_line_a' => false,
                'has_line_b' => false,
                'is_mixed_cart' => false,
            ],
            'items' => [],
        ]);
    }

    public function test_can_add_item_with_physical_stock_and_creates_stock_lock_with_ttl(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithStock('TEST-MARTE-01', 5, 399.00);

        $response = $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_lock_01',
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_items' => 2,
                'has_line_a' => true,
                'has_line_b' => false,
                'is_mixed_cart' => false,
                'reservation' => [
                    'has_active_reservation' => true,
                    'locked_variants_count' => 1,
                ],
            ],
        ]);

        $this->assertDatabaseHas('stock_locks', [
            'cart_id' => 'sess_lock_01',
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'reserved' => 2,
        ]);
    }

    public function test_adding_item_with_zero_stock_enters_as_on_demand_line_b_without_locking(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithStock('TEST-ONDEMAND-01', 0, 550.00);

        $response = $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_ondemand_01',
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_items' => 1,
                'has_line_a' => false,
                'has_line_b' => true,
                'is_mixed_cart' => false,
                'reservation' => [
                    'has_active_reservation' => false,
                ],
            ],
        ]);

        $this->assertDatabaseMissing('stock_locks', [
            'cart_id' => 'sess_ondemand_01',
            'product_variant_id' => $variant->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'reserved' => 0,
        ]);
    }

    public function test_mixed_cart_groups_line_a_and_line_b_with_correct_classification(): void
    {
        [$prodA, $varA] = $this->createProductWithStock('TEST-LINEA-01', 8, 400.00);
        [$prodB, $varB] = $this->createProductWithStock('TEST-LINEB-01', 0, 600.00);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_mixed_01',
            'variant_id' => $varA->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_mixed_01',
            'variant_id' => $varB->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_items' => 2,
                'subtotal' => 1000.00,
                'has_line_a' => true,
                'has_line_b' => true,
                'is_mixed_cart' => true,
                'free_shipping_eligible' => true,
            ],
        ]);

        $this->assertCount(1, $response->json('line_a_items'));
        $this->assertCount(1, $response->json('line_b_items'));
    }

    public function test_scraped_product_is_classified_as_line_b_and_does_not_create_stock_lock(): void
    {
        [$prodA, $varA] = $this->createProductWithStock('TEST-STOCK-01', 5, 300.00, 'Dajo', 'physical');
        [$prodScraped, $varScraped] = $this->createProductWithStock('TEST-SCRAPED-01', 20, 500.00, 'Torzo', 'dropship');

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_scraped_split_01',
            'variant_id' => $varA->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_scraped_split_01',
            'variant_id' => $varScraped->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_items' => 2,
                'has_line_a' => true,
                'has_line_b' => true,
                'is_mixed_cart' => true,
                'reservation' => [
                    'has_active_reservation' => true,
                    'locked_variants_count' => 1,
                ],
            ],
        ]);

        // Verify that stock lock exists ONLY for product A, NOT for the scraped product
        $this->assertDatabaseHas('stock_locks', [
            'cart_id' => 'sess_scraped_split_01',
            'product_variant_id' => $varA->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseMissing('stock_locks', [
            'cart_id' => 'sess_scraped_split_01',
            'product_variant_id' => $varScraped->id,
            'status' => 'active',
        ]);

        // Verify items array line types
        $items = collect($response->json('items'));
        $itemA = $items->firstWhere('variant_id', $varA->id);
        $itemB = $items->firstWhere('variant_id', $varScraped->id);

        $this->assertEquals('line_a', $itemA['line_type']);
        $this->assertFalse($itemA['is_scraped']);

        $this->assertEquals('line_b', $itemB['line_type']);
        $this->assertTrue($itemB['is_scraped']);
        $this->assertEquals('Bajo Pedido', $itemB['stock_badge']);
    }

    public function test_updating_item_quantity_adjusts_stock_lock_and_reserved_inventory(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithStock('TEST-UPDATE-01', 10, 350.00);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_update_01',
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $cart = Cart::where('session_id', 'sess_update_01')->first();
        $item = CartItem::where('cart_id', $cart->id)->first();

        $response = $this->patchJson("/api/v1/store/cart/items/{$item->id}", [
            'cart_id' => 'sess_update_01',
            'quantity' => 4,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_items' => 4,
            ],
        ]);

        $this->assertDatabaseHas('stock_locks', [
            'cart_id' => 'sess_update_01',
            'product_variant_id' => $variant->id,
            'quantity' => 4,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'reserved' => 4,
        ]);
    }

    public function test_removing_item_releases_stock_lock_and_decrements_reserved(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithStock('TEST-REMOVE-01', 10, 420.00);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_remove_01',
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $cart = Cart::where('session_id', 'sess_remove_01')->first();
        $item = CartItem::where('cart_id', $cart->id)->first();

        $response = $this->deleteJson("/api/v1/store/cart/items/{$item->id}", [
            'cart_id' => 'sess_remove_01',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_items' => 0,
            ],
        ]);

        $this->assertDatabaseHas('stock_locks', [
            'cart_id' => 'sess_remove_01',
            'product_variant_id' => $variant->id,
            'status' => 'released',
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'reserved' => 0,
        ]);
    }

    public function test_clear_cart_releases_all_stock_locks_and_empties_cart(): void
    {
        [$prod1, $var1, $inv1] = $this->createProductWithStock('TEST-CLEAR-01', 5, 300.00);
        [$prod2, $var2, $inv2] = $this->createProductWithStock('TEST-CLEAR-02', 5, 400.00);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_clear_01',
            'variant_id' => $var1->id,
            'quantity' => 2,
        ]);

        $this->postJson('/api/v1/store/cart/items', [
            'cart_id' => 'sess_clear_01',
            'variant_id' => $var2->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/v1/store/cart/clear', [
            'cart_id' => 'sess_clear_01',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_items' => 0,
            ],
        ]);

        $this->assertDatabaseMissing('stock_locks', [
            'cart_id' => 'sess_clear_01',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('inventories', ['id' => $inv1->id, 'reserved' => 0]);
        $this->assertDatabaseHas('inventories', ['id' => $inv2->id, 'reserved' => 0]);
    }

    public function test_cleanup_artisan_command_releases_expired_stock_locks(): void
    {
        [$product, $variant, $inventory] = $this->createProductWithStock('TEST-EXPIRE-01', 10, 500.00);

        // Crear lock expirado y reservar en inventario
        $inventory->reserved = 3;
        $inventory->save();

        StockLock::create([
            'cart_id' => 'sess_expired_01',
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'expires_at' => now()->subMinutes(10),
            'status' => 'active',
        ]);

        $this->artisan('stock-locks:cleanup')
            ->assertExitCode(0);

        $this->assertDatabaseHas('stock_locks', [
            'cart_id' => 'sess_expired_01',
            'status' => 'released',
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'reserved' => 0,
        ]);
    }
}

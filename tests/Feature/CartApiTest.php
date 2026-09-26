<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    private const CART_TOKEN = 'test-cart-token';

    public function test_cart_show_creates_and_returns_an_empty_cart(): void
    {
        $response = $this->withToken(self::CART_TOKEN)->getJson('/api/cart');

        $response->assertOk();
        $response->assertJsonPath('session_id', self::CART_TOKEN);
        $response->assertJsonCount(0, 'items');
        $this->assertDatabaseHas('carts', ['session_id' => self::CART_TOKEN]);
    }

    public function test_cart_can_add_an_item(): void
    {
        $variant = $this->createVariant();

        $response = $this->withToken(self::CART_TOKEN)->postJson('/api/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertOk();
        $response->assertJsonPath('variant_id', $variant->id);
        $response->assertJsonPath('quantity', 2);
        $this->assertDatabaseHas('cart_items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => $variant->price,
        ]);
    }

    public function test_cart_item_quantity_can_be_updated(): void
    {
        $item = $this->createCartItem();

        $response = $this->patchJson("/api/cart/items/{$item->id}", [
            'quantity' => 4,
        ]);

        $response->assertOk();
        $response->assertJsonPath('id', $item->id);
        $response->assertJsonPath('quantity', 4);
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 4]);
    }

    public function test_cart_item_can_be_removed(): void
    {
        $item = $this->createCartItem();

        $response = $this->deleteJson("/api/cart/items/{$item->id}");

        $response->assertOk();
        $response->assertJsonPath('message', 'Item removed');
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_cart_can_be_synchronized_and_prices_are_taken_from_the_variant(): void
    {
        $firstVariant = $this->createVariant();
        $secondVariant = $this->createVariant();
        Cart::create(['session_id' => self::CART_TOKEN])->items()->create([
            'variant_id' => $firstVariant->id,
            'quantity' => 1,
            'price' => 1,
        ]);

        $response = $this->withToken(self::CART_TOKEN)->putJson('/api/cart/sync', [
            'items' => [
                ['variant_id' => $secondVariant->id, 'quantity' => 3],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'items');
        $response->assertJsonPath('items.0.variant_id', $secondVariant->id);
        $this->assertEquals((float) $secondVariant->price, (float) $response->json('items.0.price'));
        $this->assertDatabaseMissing('cart_items', ['variant_id' => $firstVariant->id]);
        $this->assertDatabaseHas('cart_items', [
            'variant_id' => $secondVariant->id,
            'quantity' => 3,
            'price' => $secondVariant->price,
        ]);
    }

    private function createVariant(): ProductVariant
    {
        return ProductVariant::factory()->for(Product::factory())->create();
    }

    private function createCartItem(): CartItem
    {
        return CartItem::create([
            'cart_id' => Cart::create(['session_id' => self::CART_TOKEN])->id,
            'variant_id' => $this->createVariant()->id,
            'quantity' => 1,
            'price' => 19.99,
        ]);
    }
}
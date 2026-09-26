<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_their_own_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Order::create([
            'order_number' => 'ORD-MINE',
            'user_id' => $user->id,
            'status' => 'processing',
            'total' => 129.00,
            'created_via' => 'web',
        ]);
        Order::create([
            'order_number' => 'ORD-OTHER',
            'user_id' => $otherUser->id,
            'status' => 'delivered',
            'total' => 229.00,
            'created_via' => 'web',
        ]);

        $this->actingAs($user)
            ->get('/mis-pedidos')
            ->assertOk()
            ->assertSee('ORD-MINE')
            ->assertDontSee('ORD-OTHER');
    }

    public function test_order_page_shows_order_lines_with_variant_details(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['title' => 'Conjunto Test']);
        $variant = ProductVariant::factory()->for($product)->create([
            'attributes' => ['size' => 'M', 'color' => 'negro'],
            'price' => 79.90,
        ]);
        $order = Order::create([
            'order_number' => 'ORD-LINEAS',
            'user_id' => $user->id,
            'status' => 'processing',
            'total' => 159.80,
            'created_via' => 'web',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 79.90,
            'line_total' => 159.80,
        ]);

        $this->actingAs($user)
            ->get('/mis-pedidos')
            ->assertOk()
            ->assertSee('Conjunto Test')
            ->assertSee('M / negro')
            ->assertSee('Cantidad: 2')
            ->assertSee('Subtotal:');
    }

    public function test_user_without_orders_sees_empty_state(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/mis-pedidos')
            ->assertOk()
            ->assertSee('Aun no tienes pedidos');
    }
}
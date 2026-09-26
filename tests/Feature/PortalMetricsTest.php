<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Inventory;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_current_operational_metrics(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        Order::create([
            'order_number' => 'ORD-TODAY',
            'status' => 'pending',
            'total' => 250,
            'created_via' => 'web',
        ]);
        Lead::create(['name' => 'Lead actual', 'email' => 'lead@example.com', 'status' => 'new']);
        Cart::create(['status' => 'abandoned']);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('MXN $250.00')
            ->assertSee('Carritos abandonados')
            ->assertSee('1');
    }

    public function test_inventory_uses_available_stock_grouped_by_product(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $product = Product::factory()->create(['sku' => 'SKU-REAL']);
        $variant = ProductVariant::factory()->for($product)->create();
        Inventory::create([
            'variant_id' => $variant->id,
            'quantity' => 10,
            'reserved' => 3,
            'type' => 'physical',
        ]);

        $this->actingAs($user)
            ->get('/admin/inventario-catalogo')
            ->assertOk()
            ->assertSee('SKU-REAL')
            ->assertSee($product->title)
            ->assertSee('7')
            ->assertSee('7 piezas')
            ->assertSee('Saludable');
    }
}
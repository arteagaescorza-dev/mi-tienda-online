<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_returns_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'current_page',
            'last_page',
            'per_page',
        ]);
        $response->assertJsonCount(3, 'data');
    }

    public function test_storefront_renders_variant_id_and_color_options(): void
    {
        $product = Product::factory()->create();
        $firstVariant = ProductVariant::factory()->for($product)->create([
            'attributes' => ['size' => 'S', 'color' => 'negro'],
        ]);
        ProductVariant::factory()->for($product)->create([
            'attributes' => ['size' => 'S', 'color' => 'blanco'],
        ]);

        $response = $this->get('/productos');

        $response->assertOk();
        $response->assertSee('data-product-variant-id="' . $firstVariant->id . '"', false);
        $response->assertSee('data-color-option="negro"', false);
        $response->assertSee('data-color-option="blanco"', false);
    }

    public function test_mobile_cart_link_is_present_in_the_header(): void
    {
        $this->get('/productos')
            ->assertOk()
            ->assertSee('class="mobile-cart-link"', false)
            ->assertSee('href="/carrito"', false);
    }

    public function test_mobile_navigation_drawer_keeps_login_action(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-mobile-nav-drawer', false)
            ->assertSee('data-mobile-nav-backdrop', false)
            ->assertSee('data-mobile-nav-close', false)
            ->assertSee('data-open-login', false)
            ->assertSee('href="/carrito"', false)
            ->assertSee('href="/contacto"', false);
    }

    public function test_storefront_footer_contains_editorial_navigation_and_delivery_information(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Contemporary fashion studio')
            ->assertSee('Pagos y envios')
            ->assertSee('Paqueteria nacional')
            ->assertSee('Entrega personal')
            ->assertSee('Escribenos por WhatsApp')
            ->assertSee('href="/privacidad"', false)
            ->assertSee('href="/cookies"', false);
    }
}

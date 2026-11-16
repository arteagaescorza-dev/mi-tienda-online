<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StorefrontPwaHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_successful_storefront_pwa_view(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('store.pwa.home');

        // PWA Meta & Asset Links
        $response->assertSee('manifest.json');
        $response->assertSee('css/store-pwa.css');
        $response->assertSee('js/storefront/store-pwa.js');

        // Header sin barra superior de universos (Regla 4: gestionada en Explorar y Drawer)
        $response->assertDontSee('universe-selector-bar', false);
        $response->assertSee('zara-multiverse-filter-bar', false);
        $response->assertSee('data-universe="marte"', false);

        // Minimalist Marquee & Brand Pillars
        $response->assertSee('ENVÍOS EXPRESS A TODO MÉXICO', false);
        $response->assertSee('TARJETAS BANCARIAS &amp; OXXO PAY SIN FRICCIÓN', false);
        $response->assertSee('EMPAQUE 100% DISCRETO', false);

        // 5-Button Bottom Dock & Desktop Nav (Regla 4.13)
        $response->assertSee('data-dock-tab="home"', false);
        $response->assertSee('data-dock-tab="explore"', false);
        $response->assertSee('data-dock-tab="catalog"', false);
        $response->assertSee('data-dock-tab="cart"', false);
        $response->assertSee('data-dock-tab="account"', false);

        // Botón de Búsqueda y Wishlist (Reglas 1 y 5)
        $response->assertSee('data-action="toggle-search"', false);
        $response->assertSee('data-action="open-wishlist"', false);
        $response->assertSee('id="mobileFilterToggleBtn"', false);
    }

    public function test_home_displays_real_products_from_database_with_stock_and_variants(): void
    {
        $product = Product::create([
            'sku' => 'PWA-JCK-001',
            'title' => 'Jockstrap Athletic Carbon Microfibra',
            'slug' => 'jockstrap-athletic-carbon-microfibra',
            'brand' => 'JOCKSTRAPS',
            'price' => 250.00,
            'cost' => 110.00,
            'visible' => true,
            'meta' => [
                'category' => 'JOCKSTRAPS',
                'garment_type' => 'JOCKSTRAP',
            ],
        ]);

        $variantM = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PWA-JCK-001-M',
            'price' => 250.00,
            'attributes' => ['size' => 'M', 'color' => 'Negro'],
            'visible' => true,
        ]);

        $variantG = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PWA-JCK-001-G',
            'price' => 250.00,
            'attributes' => ['size' => 'G', 'color' => 'Negro'],
            'visible' => true,
        ]);

        Inventory::create([
            'variant_id' => $variantM->id,
            'location' => 'warehouse-central',
            'quantity' => 10,
            'reserved' => 2,
            'type' => 'physical',
        ]);

        Inventory::create([
            'variant_id' => $variantG->id,
            'location' => 'warehouse-central',
            'quantity' => 5,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Jockstrap Athletic Carbon Microfibra');
        $response->assertSee('PWA-JCK-001');
        $response->assertSee('MXN $250.00');
        $response->assertSee('Stock Físico');
        $response->assertSee('data-size="M"', false);
        $response->assertSee('data-size="G"', false);
    }

    public function test_home_displays_on_demand_badge_when_stock_is_zero(): void
    {
        $product = Product::create([
            'sku' => 'PWA-TNK-999',
            'title' => 'Tanktop Ribbed Deep Cut On-Demand',
            'slug' => 'tanktop-ribbed-deep-cut-on-demand',
            'brand' => 'TANK TOPS',
            'price' => 320.00,
            'visible' => true,
            'meta' => [
                'category' => 'TANK TOPS',
            ],
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PWA-TNK-999-U',
            'price' => 320.00,
            'attributes' => ['size' => 'U'],
            'visible' => true,
        ]);

        Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-central',
            'quantity' => 0,
            'reserved' => 0,
            'type' => 'dropship',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Tanktop Ribbed Deep Cut On-Demand');
        $response->assertSee('Bajo Pedido');
    }

    public function test_home_switches_universe_via_query_parameter(): void
    {
        // 1. Venus Universe
        $respVenus = $this->get('/?universo=venus');
        $respVenus->assertOk();
        $respVenus->assertSee('VENUS');
        $respVenus->assertSee('LENCERÍA DE AUTOR');

        // 2. Tiresias Universe
        $respTiresias = $this->get('/?universo=tiresias');
        $respTiresias->assertOk();
        $respTiresias->assertSee('TIRESIAS');
        $respTiresias->assertSee('LIBERTAD DE EXPRESIÓN');

        // 3. Terra Universe
        $respTerra = $this->get('/?universo=terra');
        $respTerra->assertOk();
        $respTerra->assertSee('TERRA');
        $respTerra->assertSee('EL ECOSISTEMA COMPLETO DE MODA ÍNTIMA Y ESTILO');
    }

    public function test_home_provides_initial_products_hydration_payload(): void
    {
        Product::create([
            'sku' => 'PWA-HYD-001',
            'title' => 'Prenda Hidratacion Reactiva PWA',
            'slug' => 'prenda-hidratacion-reactiva-pwa',
            'price' => 199.00,
            'visible' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('window.__INITIAL_STORE_PRODUCTS__', false);
        $response->assertSee('Prenda Hidratacion Reactiva PWA');
        $response->assertSee('window.__ACTIVE_UNIVERSE__', false);
    }

    public function test_home_output_html_has_strictly_zero_emojis(): void
    {
        Product::create([
            'sku' => 'PWA-EMOJI-CHECK',
            'title' => 'Verificacion Estricta Cero Emojis',
            'slug' => 'verificacion-estricta-cero-emojis',
            'price' => 150.00,
            'visible' => true,
        ]);

        $response = $this->get('/');
        $response->assertOk();

        $content = $response->getContent();
        preg_match_all('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content, $matches);

        $this->assertCount(0, $matches[0], 'El HTML de la Storefront PWA no debe contener emojis bajo la politica estricta.');
    }
}

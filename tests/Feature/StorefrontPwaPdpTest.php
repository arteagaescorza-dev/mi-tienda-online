<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPwaPdpTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdp_api_returns_product_details_with_variants_stock_and_fabric(): void
    {
        $product = Product::create([
            'sku' => 'PDP-TEST-001',
            'title' => 'Jockstrap Velvet Ergonomico',
            'slug' => 'jockstrap-velvet-ergonomico',
            'brand' => 'JOCKSTRAPS',
            'price' => 280.00,
            'cost' => 120.00,
            'visible' => true,
            'meta' => [
                'category' => 'JOCKSTRAPS',
                'fabric_composition' => '95% Modal Pima / 5% Spandex. Elastico jacquard 45mm.',
            ],
        ]);

        $variantM = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PDP-TEST-001-M',
            'price' => 280.00,
            'attributes' => ['size' => 'M', 'color' => 'Negro'],
            'visible' => true,
        ]);

        $variantG = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PDP-TEST-001-G',
            'price' => 280.00,
            'attributes' => ['size' => 'G', 'color' => 'Negro'],
            'visible' => true,
        ]);

        // Variant M: 10 physical - 2 reserved = 8 available
        Inventory::create([
            'variant_id' => $variantM->id,
            'location' => 'warehouse-central',
            'quantity' => 10,
            'reserved' => 2,
            'type' => 'physical',
        ]);

        // Variant G: 0 physical = 0 available (Bajo Pedido)
        Inventory::create([
            'variant_id' => $variantG->id,
            'location' => 'warehouse-central',
            'quantity' => 0,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        $response = $this->getJson('/api/v1/store/products/' . $product->slug);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => (string) $product->id,
                    'sku' => 'PDP-TEST-001',
                    'name' => 'Jockstrap Velvet Ergonomico',
                    'slug' => 'jockstrap-velvet-ergonomico',
                    'price' => 280.0,
                    'price_formatted' => 'MXN $280.00',
                    'fabric_composition' => '95% Modal Pima / 5% Spandex. Elastico jacquard 45mm.',
                    'stock_type' => 'ready',
                    'stock_label' => 'Stock Físico',
                    'total_available_stock' => 8,
                ],
            ]);

        $variants = $response->json('data.variants');
        $this->assertCount(2, $variants);

        // Check Variant M is available with physical badge
        $mItem = collect($variants)->firstWhere('size', 'M');
        $this->assertNotNull($mItem);
        $this->assertEquals(8, $mItem['stock_available']);
        $this->assertTrue($mItem['is_available']);
        $this->assertEquals('Stock Físico', $mItem['stock_badge']);

        // Check Variant G is 0 available with on-demand badge
        $gItem = collect($variants)->firstWhere('size', 'G');
        $this->assertNotNull($gItem);
        $this->assertEquals(0, $gItem['stock_available']);
        $this->assertFalse($gItem['is_available']);
        $this->assertEquals('Bajo Pedido', $gItem['stock_badge']);
    }

    public function test_pdp_api_returns_404_when_product_not_found(): void
    {
        $response = $this->getJson('/api/v1/store/products/producto-inexistente-404');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_web_product_route_renders_dedicated_full_screen_pdp_without_drawers(): void
    {
        $product = Product::create([
            'sku' => 'PDP-DIRECT-01',
            'title' => 'Boxer Brief Modal Soft',
            'slug' => 'boxer-brief-modal-soft',
            'brand' => 'BRIEF/BOXERS',
            'price' => 280.00,
            'visible' => true,
            'meta' => [
                'fabric_composition' => '95% Modal Pima / 5% Spandex',
            ],
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PDP-DIRECT-01-M',
            'price' => 280.00,
            'attributes' => ['size' => 'M'],
            'visible' => true,
        ]);

        Inventory::create([
            'variant_id' => $variant->id,
            'location' => 'warehouse-central',
            'quantity' => 12,
            'reserved' => 2,
            'type' => 'physical',
        ]);

        $response = $this->get('/productos/' . $product->slug);

        $response->assertOk();
        $response->assertViewIs('store.pwa.product-detail');
        $response->assertSee('Boxer Brief Modal Soft');
        $response->assertSee('PDP-DIRECT-01');
        $response->assertSee('MXN $280.00');
        $response->assertSee('95% Modal Pima / 5% Spandex');
        $response->assertSee('AGREGAR AL CARRITO');
        $response->assertSee('Guía Oficial de Tallas Torzo');
        $response->assertSee('Protocolo de Lavado & Cuidado de Prendas');
        $response->assertSee('Aviso Normativo y Sanitario PROFECO');
        $response->assertSee('application/ld+json', false);
        $response->assertDontSee('openProductModal("' . $product->id . '");', false);
    }

    public function test_web_product_route_redirects_when_product_not_found(): void
    {
        $response = $this->get('/productos/slug-inexistente-999');

        $response->assertRedirect('/');
    }

    public function test_size_guide_table_contains_full_size_curve_up_to_3xl(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('CH (Chica)');
        $response->assertSee('70 - 76 cm');
        $response->assertSee('M (Mediana)');
        $response->assertSee('78 - 84 cm');
        $response->assertSee('G (Grande)');
        $response->assertSee('86 - 92 cm');
        $response->assertSee('XG / XL (Extra Grande)');
        $response->assertSee('94 - 102 cm');
        $response->assertSee('2XL (Doble Extra Grande)');
        $response->assertSee('104 - 110 cm');
        $response->assertSee('3XL (Triple Extra Grande)');
        $response->assertSee('112 - 120 cm');
    }

    public function test_pdp_api_response_has_strictly_zero_emojis(): void
    {
        $product = Product::create([
            'sku' => 'PDP-EMOJI-TEST',
            'title' => 'Prenda Sin Emojis Test',
            'slug' => 'prenda-sin-emojis-test',
            'price' => 199.00,
            'visible' => true,
        ]);

        $response = $this->getJson('/api/v1/store/products/' . $product->slug);
        $response->assertOk();

        $content = $response->getContent();
        preg_match_all('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content, $matches);

        $this->assertCount(0, $matches[0], 'La respuesta del endpoint PDP no debe contener emojis.');
    }

    public function test_pdp_full_screen_html_has_strictly_zero_emojis_and_supports_multiverse(): void
    {
        $product = Product::create([
            'sku' => 'PDP-EMOJI-HTML',
            'title' => 'Bikini Contour Multiverse',
            'slug' => 'bikini-contour-multiverse',
            'price' => 240.00,
            'visible' => true,
        ]);

        $response = $this->get('/productos/' . $product->slug . '?universo=venus');
        $response->assertOk();
        $response->assertViewIs('store.pwa.product-detail');
        $response->assertSee('VENUS');

        $content = $response->getContent();
        preg_match_all('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content, $matches);

        $this->assertCount(0, $matches[0], 'El HTML de la PDP no debe contener emojis bajo la política estricta.');
    }

    public function test_pdp_does_not_render_whatsapp_button_under_project_rule(): void
    {
        $product = Product::create([
            'sku' => 'PDP-NO-WA-01',
            'title' => 'Brief Modal Sin WhatsApp',
            'slug' => 'brief-modal-sin-whatsapp',
            'price' => 299.00,
            'visible' => true,
        ]);

        $response = $this->get('/productos/' . $product->slug);

        $response->assertOk();
        $response->assertViewIs('store.pwa.product-detail');
        // Regla 1: Las páginas independientes de cada producto NO muestran botón de WhatsApp
        $response->assertDontSee('pdp-btn-whatsapp', false);
        $response->assertDontSee('CONSULTAR POR WHATSAPP', false);
        $response->assertDontSee('id="pdpWhatsappLink"', false);
    }

    public function test_pdp_renders_image_modal_and_supports_swipe_gallery(): void
    {
        $product = Product::create([
            'sku' => 'PDP-GALLERY-01',
            'title' => 'Thong Sensorial con Galeria Swipe',
            'slug' => 'thong-sensorial-con-galeria-swipe',
            'price' => 250.00,
            'visible' => true,
            'meta' => [
                'image' => '/uploads/thong-front.webp',
                'images' => [
                    '/uploads/thong-front.webp',
                    '/uploads/thong-side.webp',
                    '/uploads/thong-back.webp',
                ],
            ],
        ]);

        $response = $this->get('/productos/' . $product->slug);

        $response->assertOk();
        $response->assertViewIs('store.pwa.product-detail');
        // Regla 2: Apertura de modal de imágenes y swipe interactivo
        $response->assertSee('id="pdpGalleryModal"', false);
        $response->assertSee('pdp-gallery-modal', false);
        $response->assertSee('pdp-gallery-viewport', false);
        $response->assertSee('id="pdpGalleryTrack"', false);
        $response->assertSee('pdp-image-zoom-trigger', false);
        $response->assertSee('openPdpGalleryModal', false);
        $response->assertSee('closePdpGalleryModal', false);
        $response->assertSee('navigatePdpGallery', false);
        $response->assertSee('initPdpGallerySwipe', false);
        $response->assertSee('/uploads/thong-front.webp', false);
        $response->assertSee('/uploads/thong-side.webp', false);
        $response->assertSee('/uploads/thong-back.webp', false);
    }
}

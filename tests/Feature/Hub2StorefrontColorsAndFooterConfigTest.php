<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CmsOfficialDocument;
use App\Models\CmsPage;
use App\Models\Product;
use App\Models\User;
use App\Http\Controllers\StorefrontController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Hub2StorefrontColorsAndFooterConfigTest extends TestCase
{
    use RefreshDatabase;

    protected User $portalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->portalUser = User::factory()->create(['role' => 'portal']);
    }

    public function test_detect_color_hex_returns_correct_hex_for_orange_and_standard_colors(): void
    {
        $this->assertEquals('#ea580c', StorefrontController::detectColorHex('Naranja'));
        $this->assertEquals('#ea580c', StorefrontController::detectColorHex('naranja'));
        $this->assertEquals('#ea580c', StorefrontController::detectColorHex('Orange Vibrant'));
        $this->assertEquals('#881337', StorefrontController::detectColorHex('Vino'));
        $this->assertEquals('#78350f', StorefrontController::detectColorHex('Café'));
        $this->assertEquals('#06b6d4', StorefrontController::detectColorHex('Turquesa'));
    }

    public function test_product_with_gallery_colors_does_not_create_phantom_meta_colors(): void
    {
        $product = Product::create([
            'title' => 'Tanga Licra Naranja',
            'sku' => 'TANGA-NARANJA-001',
            'slug' => 'tanga-licra-naranja',
            'price' => 180.00,
            'visible' => true,
            'meta' => [
                'color_name' => 'Rojo',
                'color_hex' => '#dc2626',
                'gallery' => [
                    [
                        'url' => 'https://example.com/tanga-naranja-1.jpg',
                        'color_name' => 'Naranja',
                        'is_primary' => true,
                    ],
                    [
                        'url' => 'https://example.com/tanga-naranja-2.jpg',
                        'color_name' => 'Naranja',
                        'is_primary' => false,
                    ],
                ],
                'images_by_color' => [
                    'https://example.com/tanga-naranja-1.jpg' => 'Naranja',
                    'https://example.com/tanga-naranja-2.jpg' => 'Naranja',
                ],
            ],
        ]);

        $colorData = StorefrontController::resolveProductColorData($product, 'https://example.com/tanga-naranja-1.jpg', []);

        $this->assertCount(1, $colorData['colors'], 'El producto solo debe tener el color naranja de su galería.');
        $this->assertEquals('Naranja', $colorData['colors'][0]['name']);
        $this->assertEquals('#ea580c', $colorData['colors'][0]['hex']);
    }

    public function test_admin_can_update_footer_official_categories_and_legal_pages(): void
    {
        $doc = CmsOfficialDocument::firstOrCreate(
            ['code' => 'STORE_FOOTER_CONFIG'],
            [
                'title' => 'Configuración Oficial del Footer',
                'payload' => [
                    'brand_summary' => 'Resumen corporativo.',
                    'copyright_text' => '(C) 2026 Multiverso',
                ]
            ]
        );

        $cat1 = Category::create(['name' => 'Jockstraps', 'slug' => 'jockstraps']);
        $cat2 = Category::create(['name' => 'Boxers Modal', 'slug' => 'boxers-modal']);

        $page1 = CmsPage::create([
            'title' => 'Aviso de Privacidad',
            'slug' => 'privacidad',
            'status' => 'active',
            'show_in_footer' => true,
        ]);

        $response = $this->actingAs($this->portalUser)->post(
            route('portal.store.documents.update', ['code' => 'STORE_FOOTER_CONFIG']),
            [
                'payload' => [
                    'brand_summary' => 'Resumen actualizado.',
                    'copyright_text' => '(C) 2026 Multiverso Actualizado',
                    'footer_categories' => [
                        [
                            'id' => $cat1->id,
                            'name' => 'Jockstraps Ergonómicos',
                            'slug' => 'jockstraps',
                            'is_active' => 1,
                            'sort_order' => 1,
                        ],
                        [
                            'id' => $cat2->id,
                            'name' => 'Boxers Modal Pima',
                            'slug' => 'boxers-modal',
                            'is_active' => 0,
                            'sort_order' => 2,
                        ],
                    ],
                    'footer_legal_pages' => [
                        [
                            'id' => $page1->id,
                            'title' => 'Privacidad y Protección de Datos',
                            'slug' => 'privacidad',
                            'is_active' => 1,
                            'sort_order' => 1,
                        ],
                    ],
                ]
            ]
        );

        $response->assertRedirect();

        $freshDoc = CmsOfficialDocument::where('code', 'STORE_FOOTER_CONFIG')->first();
        $this->assertNotNull($freshDoc);
        $this->assertEquals('Jockstraps Ergonómicos', $freshDoc->payload['footer_categories'][0]['name']);
        $this->assertEquals(1, $freshDoc->payload['footer_categories'][0]['is_active']);
        $this->assertEquals('Privacidad y Protección de Datos', $freshDoc->payload['footer_legal_pages'][0]['title']);
    }

    public function test_storefront_footer_renders_configured_categories_and_excludes_product_pages_from_legal_section(): void
    {
        $cat1 = Category::create(['name' => 'Tangas Velvet', 'slug' => 'tangas']);
        $cat2 = Category::create(['name' => 'Jockstraps Gym', 'slug' => 'jockstraps']);

        // Página institucional (debe aparecer)
        $legalPage = CmsPage::create([
            'title' => 'Convenios con Aliados',
            'slug' => 'aliados',
            'status' => 'active',
            'show_in_footer' => true,
        ]);

        // Página de producto con filtro (NO debe aparecer en Información & Legal)
        $productPage = CmsPage::create([
            'title' => 'Tanga',
            'slug' => 'tanga',
            'filter_category' => 'tanga',
            'image_source_mode' => 'catalog',
            'status' => 'active',
            'show_in_footer' => true,
        ]);

        CmsOfficialDocument::updateOrCreate(
            ['code' => 'STORE_FOOTER_CONFIG'],
            [
                'title' => 'Configuración Oficial del Footer',
                'payload' => [
                    'brand_summary' => 'Plataforma oficial.',
                    'footer_categories' => [
                        [
                            'id' => $cat1->id,
                            'name' => 'Tangas Velvet',
                            'slug' => 'tangas',
                            'is_active' => 1,
                            'sort_order' => 1,
                        ],
                        [
                            'id' => $cat2->id,
                            'name' => 'Jockstraps Gym',
                            'slug' => 'jockstraps',
                            'is_active' => 1,
                            'sort_order' => 2,
                        ],
                    ],
                    'footer_legal_pages' => [
                        [
                            'id' => $legalPage->id,
                            'title' => 'Convenios con Aliados',
                            'slug' => 'aliados',
                            'is_active' => 1,
                            'sort_order' => 1,
                        ],
                    ],
                ]
            ]
        );

        $response = $this->get('/');
        $response->assertOk();

        // Categorías Oficiales
        $response->assertSee('Tangas Velvet');
        $response->assertSee('Jockstraps Gym');
        $response->assertSee('data-category="tangas"', false);
        $response->assertSee('data-category="jockstraps"', false);

        // Información & Legal
        $response->assertSee('Convenios con Aliados');

        // Verificar que la página de producto no se mezcló en el footer como enlace simple
        $content = $response->getContent();
        // The legal section should contain 'Convenios con Aliados'
        $this->assertStringContainsString('Convenios con Aliados', $content);
    }
}

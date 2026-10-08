<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsStorefrontConfig;
use App\Models\Product;
use Database\Seeders\CmsHub2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontDynamicSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsHub2Seeder::class);

        // Crear al menos un producto visible para renderizar catálogo
        Product::create([
            'sku' => 'TEST-JOCK-01',
            'title' => 'Jockstrap Modal Velvet Black',
            'slug' => 'jockstrap-modal-velvet-black',
            'price' => 350.00,
            'visible' => true,
            'brand' => 'JOCKSTRAPS',
        ]);
    }

    /**
     * Test home renders dynamic sections and CSS variables from DB.
     */
    public function test_home_renders_dynamic_sections_and_theme_variables(): void
    {
        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('hero-editorial-section', false);
        $response->assertSee('brand-pillars-bar', false);
        $response->assertSee('leadMagnetHomeCard', false);
        $response->assertSee('hub2DynamicTheme', false);
        $response->assertSee('--brand-primary: #09090b', false);
        $response->assertSee('--brand-accent: #4f46e5', false);
    }

    /**
     * Test home renders custom HTML/CSS/JS section created in Hub 2.
     */
    public function test_home_renders_custom_html_css_js_section(): void
    {
        CmsSection::create([
            'universe' => 'all',
            'name' => 'Banner Promocional Verano',
            'type' => 'custom_html_css_js',
            'title' => 'Banner Verano',
            'custom_html' => '<div id="bannerVerano2026">¡DESCUENTO DE VERANO 20% OFF!</div>',
            'custom_css' => '#bannerVerano2026 { background: red; color: white; padding: 20px; }',
            'custom_js' => 'console.log("Banner verano activo");',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('¡DESCUENTO DE VERANO 20% OFF!', false);
        $response->assertSee('#bannerVerano2026 { background: red;', false);
        $response->assertSee('console.log("Banner verano activo");', false);
    }

    /**
     * Test active navbar pages are rendered in navigation links.
     */
    public function test_navbar_renders_dynamic_pages_from_cms(): void
    {
        CmsPage::create([
            'slug' => 'nuestra-historia',
            'title' => 'Nuestra Historia',
            'show_in_navbar' => true,
            'navbar_order' => 10,
            'status' => 'active',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Nuestra Historia', false);
        $response->assertSee('/pagina/nuestra-historia', false);
    }

    /**
     * Test draft sections are not displayed on public storefront.
     */
    public function test_draft_sections_are_excluded_from_public_storefront(): void
    {
        CmsSection::create([
            'universe' => 'marte',
            'name' => 'Sección Secreta en Borrador',
            'type' => 'custom_html_css_js',
            'custom_html' => '<div id="secretoBorrador">CONTENIDO NO PUBLICADO</div>',
            'sort_order' => 99,
            'status' => 'draft',
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertDontSee('CONTENIDO NO PUBLICADO', false);
    }

    /**
     * Test hero section in structured mode renders full width, background, gradient, dynamic CTA buttons, and excludes custom HTML.
     */
    public function test_hero_structured_mode_renders_fullwidth_buttons_and_gradient(): void
    {
        $sec = CmsSection::create([
            'universe' => 'all',
            'name' => 'Hero Verano Full Width',
            'type' => 'hero_carousel',
            'title' => 'HERO TITULO ESTRUCTURADO',
            'badge' => 'NUEVA COLECCION',
            'subtitle' => 'Prendas unicas con envio express',
            'custom_html' => '<div id="heroDuplicadoBug">ESTE CODIGO NO DEBE SALIR EN MODO ESTRUCTURADO</div>',
            'settings' => [
                'hero_mode' => 'structured',
                'full_width' => true,
                'background_image' => '/uploads/sections/hero_verano.webp',
                'enable_gradient' => true,
                'gradient_color_start' => '#0f172a',
                'gradient_color_end' => '#1e1b4b',
                'gradient_direction' => 'to bottom',
                'gradient_opacity' => 85,
                'cta_primary_text' => 'VER JOCKS EXCLUSIVOS',
                'cta_primary_url' => '#section-42',
                'cta_secondary_text' => 'GUIA DETALLADA',
                'cta_secondary_url' => '/pagina/guia-tallas',
            ],
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('hero-full-width', false);
        $response->assertSee('id="section-' . $sec->id . '"', false);
        $response->assertSee('HERO TITULO ESTRUCTURADO', false);
        $response->assertSee('VER JOCKS EXCLUSIVOS', false);
        $response->assertSee('#section-42', false);
        $response->assertSee('GUIA DETALLADA', false);
        $response->assertSee('/pagina/guia-tallas', false);
        $response->assertSee('/uploads/sections/hero_verano.webp', false);
        $response->assertSee('linear-gradient(to bottom,', false);
        // MUTUAL EXCLUSIVITY: Structured mode must NOT render custom HTML!
        $response->assertDontSee('ESTE CODIGO NO DEBE SALIR EN MODO ESTRUCTURADO', false);
    }

    /**
     * Test hero section in custom mode renders isolated custom code without duplicating structured card.
     */
    public function test_hero_custom_mode_renders_isolated_custom_code_without_duplication(): void
    {
        $sec = CmsSection::create([
            'universe' => 'all',
            'name' => 'Hero Custom Exclusivo',
            'type' => 'hero_carousel',
            'title' => 'TITULO QUE NO DEBE APARECER EN MODO CUSTOM',
            'custom_html' => '<div id="miHeroCustomPuro">SOLO CODIGO CUSTOM RENDERIZADO</div>',
            'settings' => [
                'hero_mode' => 'custom',
                'full_width' => true,
            ],
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('SOLO CODIGO CUSTOM RENDERIZADO', false);
        // MUTUAL EXCLUSIVITY: Custom mode must NOT render the structured hero card title!
        $response->assertDontSee('TITULO QUE NO DEBE APARECER EN MODO CUSTOM', false);
    }

    /**
     * Test catalog_section renders section title, subtitle, and filtered products on homepage.
     */
    public function test_catalog_section_renders_title_and_filtered_products(): void
    {
        Product::create([
            'sku' => 'URBAN-001',
            'title' => 'Bikini Comic Urbano Exclusivo',
            'slug' => 'bikini-comic-urbano-exclusivo',
            'price' => 420.00,
            'visible' => true,
            'brand' => 'Dajo Underwear',
            'section_name' => 'lo-mas-urbano',
        ]);

        CmsSection::create([
            'universe' => 'all',
            'name' => 'Urbano',
            'type' => 'catalog_section',
            'title' => 'VITRINA URBANO 2026',
            'subtitle' => 'Lo más cotizado del streetwear íntimo',
            'query_rules' => [
                'section' => 'Urbano',
                'slug' => 'lo-mas-urbano',
            ],
            'sort_order' => 10,
            'status' => 'active',
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('VITRINA URBANO 2026', false);
        $response->assertSee('Lo más cotizado del streetwear íntimo', false);
        $response->assertSee('Bikini Comic Urbano Exclusivo', false);
    }
}


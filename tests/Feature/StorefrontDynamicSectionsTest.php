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
        $response->assertSee('NUESTRA HISTORIA', false);
        $response->assertSee('/nuestra-historia', false);
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
}

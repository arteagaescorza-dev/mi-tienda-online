<?php

namespace Tests\Feature;

use App\Models\CmsSection;
use App\Models\User;
use Database\Seeders\CmsHub2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroHorizontalWidthAndButtonsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CmsHub2Seeder::class);
    }

    /**
     * Test horizontal 100% width adds hero-full-width-h class.
     */
    public function test_hero_renders_horizontal_full_width(): void
    {
        CmsSection::create([
            'universe' => 'marte',
            'name' => 'Hero Ancho Completo',
            'type' => 'hero',
            'title' => 'Colección 100% Horizontal',
            'status' => 'active',
            'order' => 1,
            'settings' => [
                'full_width_horizontal' => '1',
                'hero_has_buttons' => '1',
                'cta_primary_text' => 'COMPRAR AHORA',
            ],
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('hero-full-width-h', false);
        $response->assertSee('COMPRAR AHORA', false);
    }

    /**
     * Test hero without buttons renders full hero click link and omits buttons.
     */
    public function test_hero_without_buttons_renders_full_clickable_hero(): void
    {
        CmsSection::create([
            'universe' => 'marte',
            'name' => 'Hero Banner Clic Integral',
            'type' => 'hero',
            'title' => 'Banner Sin Botones',
            'status' => 'active',
            'order' => 1,
            'settings' => [
                'hero_has_buttons' => '0',
                'hero_click_url' => '/catalogo/ropa-interior',
                'cta_primary_text' => 'ESTE BOTON NO DEBE SALIR',
            ],
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('hero-full-click-link', false);
        $response->assertSee('/catalogo/ropa-interior', false);
        $response->assertDontSee('ESTE BOTON NO DEBE SALIR', false);
    }

    /**
     * Test structural parity 7.5 between Create and Edit modals in Portal Store CMS.
     */
    public function test_portal_store_has_structural_parity_for_hero_features(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/tienda-cms?universo=marte');

        $response->assertStatus(200);

        // Inputs en modal de creación
        $response->assertSee('id="inpCreateFullWidthHorizontal"', false);
        $response->assertSee('id="inpCreateHeroHasButtons"', false);
        $response->assertSee('id="createHeroFullClickWrap"', false);
        $response->assertSee('id="createHeroClickUrl"', false);

        // Inputs en modal de edición (Paridad 7.5)
        $response->assertSee('id="inpEditFullWidthHorizontal"', false);
        $response->assertSee('id="inpEditHeroHasButtons"', false);
        $response->assertSee('id="editHeroFullClickWrap"', false);
        $response->assertSee('id="editHeroClickUrl"', false);
    }

    /**
     * Test hero renders custom image focal point in storefront background-position.
     */
    public function test_hero_renders_custom_image_focal_point_in_storefront(): void
    {
        CmsSection::create([
            'universe' => 'marte',
            'name' => 'Hero Foco Superior Derecha',
            'type' => 'hero',
            'title' => 'Foco 9 Puntos',
            'status' => 'active',
            'order' => 1,
            'settings' => [
                'background_image' => 'images/banner-test.jpg',
                'image_focal_point' => 'right top',
                'hero_has_buttons' => '1',
            ],
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('background-position: right top !important', false);
    }

    /**
     * Test hero video background renders object-position with focal point in storefront.
     */
    public function test_hero_video_renders_object_position_with_focal_point(): void
    {
        CmsSection::create([
            'universe' => 'marte',
            'name' => 'Hero Video Foco Inferior Izquierda',
            'type' => 'hero',
            'title' => 'Video Foco 9 Puntos',
            'status' => 'active',
            'order' => 1,
            'settings' => [
                'background_type' => 'video',
                'background_video' => 'videos/sample.mp4',
                'image_focal_point' => 'left bottom',
                'hero_has_buttons' => '1',
            ],
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('object-position: left bottom', false);
    }

    /**
     * Test 9-cell focal matrix parity 7.5 in both Create and Edit modals.
     */
    public function test_portal_store_has_9_cell_focal_matrix_parity(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/tienda-cms?universo=marte');

        $response->assertStatus(200);

        // Modal Crear
        $response->assertSee('id="inpCreateHeroFocalPoint"', false);
        $response->assertSee('id="createHeroFocalMatrix"', false);
        $response->assertSee('id="createHeroFocalPointLabel"', false);

        // Modal Editar (Paridad 7.5)
        $response->assertSee('id="inpEditHeroFocalPoint"', false);
        $response->assertSee('id="editHeroFocalMatrix"', false);
        $response->assertSee('id="editHeroFocalPointLabel"', false);

        // 9 celdas presentes en ambos modales
        $points = [
            'left top', 'center top', 'right top',
            'left center', 'center center', 'right center',
            'left bottom', 'center bottom', 'right bottom'
        ];

        foreach ($points as $p) {
            $response->assertSee('data-focal="' . $p . '"', false);
        }
    }

    /**
     * Test sections render zero margin spacing by default in storefront.
     */
    public function test_sections_render_zero_spacing_by_default_in_storefront(): void
    {
        CmsSection::create([
            'universe' => 'marte',
            'name' => 'Hero Sin Espaciado Explicito',
            'type' => 'hero',
            'title' => 'Hero Pegado Sin Gaps',
            'status' => 'active',
            'order' => 1,
            'settings' => [
                'hero_has_buttons' => '1',
            ],
        ]);

        CmsSection::create([
            'universe' => 'marte',
            'name' => 'Pilares Pegados',
            'type' => 'brand_pillars',
            'title' => 'Pilares Sin Gap',
            'status' => 'active',
            'order' => 2,
            'settings' => [],
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        // Default zero spacing margin applied
        $response->assertSee('margin-top: 0px !important; margin-bottom: 0px !important;', false);
        $response->assertSee('padding-top: 0 !important; padding-bottom: 0 !important;', false);
    }

    /**
     * Test sections render custom margin spacing when configured in settings.
     */
    public function test_sections_render_custom_spacing_when_configured(): void
    {
        CmsSection::create([
            'universe' => 'marte',
            'name' => 'Hero Con Espacio Personalizado',
            'type' => 'hero',
            'title' => 'Hero Con Margen',
            'status' => 'active',
            'order' => 1,
            'settings' => [
                'spacing_top' => '24',
                'spacing_bottom' => '48',
                'hero_has_buttons' => '1',
            ],
        ]);

        $response = $this->get('/?universo=marte');

        $response->assertStatus(200);
        $response->assertSee('margin-top: 24px !important; margin-bottom: 48px !important;', false);
    }

    /**
     * Test Rule 7.5 structural parity for spacing controls between create and edit modals.
     */
    public function test_portal_store_has_structural_parity_for_section_spacing_controls(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/tienda-cms?universo=marte');

        $response->assertStatus(200);

        // Modal Crear Espaciados
        $response->assertSee('id="createSpacingWrap"', false);
        $response->assertSee('id="inpCreateSpacingTop"', false);
        $response->assertSee('id="rangeCreateSpacingTop"', false);
        $response->assertSee('id="inpCreateSpacingBottom"', false);
        $response->assertSee('id="rangeCreateSpacingBottom"', false);

        // Modal Editar Espaciados (Paridad Estructural 7.5)
        $response->assertSee('id="editSpacingWrap"', false);
        $response->assertSee('id="inpEditSpacingTop"', false);
        $response->assertSee('id="rangeEditSpacingTop"', false);
        $response->assertSee('id="inpEditSpacingBottom"', false);
        $response->assertSee('id="rangeEditSpacingBottom"', false);

        // Nombres de campo settings
        $response->assertSee('name="settings[spacing_top]"', false);
        $response->assertSee('name="settings[spacing_bottom]"', false);
    }
}


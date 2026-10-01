<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CmsOfficialDocument;
use App\Models\CmsSection;
use App\Models\CmsStorefrontConfig;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CmsHub2Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Hub2AdminStoreControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $portalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->portalUser = User::factory()->create(['role' => 'portal']);
        $this->seed(CmsHub2Seeder::class);
    }

    /**
     * Test admin store CMS view loads successfully with all database records.
     */
    public function test_admin_store_cms_loads_sections_and_documents_from_db(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.store'));

        $response->assertStatus(200);
        $response->assertViewHas('sections');
        $response->assertViewHas('storefronts');
        $response->assertViewHas('sizeGuide');
        $response->assertViewHas('careProtocols');
        $response->assertSee('Secciones Modulares del Home');
        $response->assertSee('Hero Editorial Principal');
    }

    /**
     * Test updating storefront colors and hero titles persists in CmsStorefrontConfig.
     */
    public function test_updating_vitrina_persists_in_cms_storefront_config(): void
    {
        $payload = [
            'name' => 'Marte Extreme',
            'accentColor' => '#6366f1',
            'backgroundColor' => '#f1f5f9',
            'heroTitle' => 'POTENCIA Y LIBERTAD ERGONÓMICA',
            'heroSubtitle' => 'Diseño textil revolucionario con soporte ultra anatómico.',
            'heroCtaText' => 'COMPRAR AHORA',
        ];

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.store.vitrinas.update', ['universe' => 'marte']), $payload);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cms_storefront_configs', [
            'universe' => 'marte',
            'brand_name' => 'Marte Extreme',
            'accent_color' => '#6366f1',
            'hero_title' => 'POTENCIA Y LIBERTAD ERGONÓMICA',
        ]);
    }

    /**
     * Test creating a new modular section in Hub 2.
     */
    public function test_creating_new_modular_section(): void
    {
        $payload = [
            'universe' => 'marte',
            'name' => 'Nueva Vitrina Jockstraps 2026',
            'type' => 'curated_products',
            'title' => 'JOCKSTRAPS DE COMPETICIÓN',
            'subtitle' => 'Microfibra transpirable',
            'badge' => 'DESTACADO',
            'query_rules' => ['brand' => 'JOCKSTRAPS'],
        ];

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.store.sections.store'), $payload);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cms_sections', [
            'name' => 'Nueva Vitrina Jockstraps 2026',
            'universe' => 'marte',
            'type' => 'curated_products',
            'status' => 'active',
        ]);
    }

    /**
     * Test reordering sections atomically with JSON order array.
     */
    public function test_reordering_sections_atomically(): void
    {
        $sec1 = CmsSection::first();
        $sec2 = CmsSection::skip(1)->first();

        $response = $this->actingAs($this->portalUser)
            ->postJson(route('portal.store.sections.reorder'), [
                'order' => [$sec2->id, $sec1->id],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(1, $sec2->fresh()->sort_order);
        $this->assertEquals(2, $sec1->fresh()->sort_order);
    }

    /**
     * Test toggling section status between active and draft.
     */
    public function test_toggling_section_status(): void
    {
        $section = CmsSection::first();
        $this->assertEquals('active', $section->status);

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.store.sections.toggle', ['section' => $section->id]));

        $response->assertSessionHas('success');
        $this->assertEquals('draft', $section->fresh()->status);
    }

    /**
     * Test deleting a section.
     */
    public function test_deleting_a_section(): void
    {
        $section = CmsSection::create([
            'universe' => 'marte',
            'name' => 'Sección Temporal a Borrar',
            'type' => 'brand_pillars',
            'sort_order' => 99,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->portalUser)
            ->delete(route('portal.store.sections.destroy', ['section' => $section->id]));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('cms_sections', ['id' => $section->id]);
    }

    /**
     * Test updating official documents (SIZE_GUIDE_TORZO).
     */
    public function test_updating_official_document(): void
    {
        $newPayload = [
            ['size' => 'M', 'waistCm' => '78 - 85 cm', 'hipsCm' => '93 - 100 cm', 'pantsSize' => '32', 'advice' => 'Ajuste atlético'],
        ];

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.store.documents.update', ['code' => 'SIZE_GUIDE_TORZO']), [
                'title' => 'Guía Anatómica Oficial 2026',
                'payload' => $newPayload,
            ]);

        $response->assertSessionHas('success');

        $doc = CmsOfficialDocument::where('code', 'SIZE_GUIDE_TORZO')->first();
        $this->assertEquals('Guía Anatómica Oficial 2026', $doc->title);
        $this->assertEquals('78 - 85 cm', $doc->payload[0]['waistCm']);
    }

    /**
     * Test updating a modular section (universe, status, title, rules).
     */
    public function test_updating_modular_section_and_universe(): void
    {
        $section = CmsSection::first();

        $updatePayload = [
            'name' => 'Sección Actualizada Marte a Venus',
            'universe' => 'venus',
            'status' => 'draft',
            'title' => 'NUEVO TÍTULO EDITADO',
            'subtitle' => 'Subtítulo actualizado para pruebas',
            'badge' => 'OFERTA EXCLUSIVA',
            'layout' => 'carousel',
            'limit' => 12,
            'query_rules' => ['brand' => 'CALVIN'],
        ];

        $response = $this->actingAs($this->portalUser)
            ->put(route('portal.store.sections.update', ['section' => $section->id]), $updatePayload);

        $response->assertSessionHas('success');

        $fresh = $section->fresh();
        $this->assertEquals('Sección Actualizada Marte a Venus', $fresh->name);
        $this->assertEquals('venus', $fresh->universe);
        $this->assertEquals('draft', $fresh->status);
        $this->assertEquals('NUEVO TÍTULO EDITADO', $fresh->title);
        $this->assertEquals('CALVIN', $fresh->query_rules['brand']);
    }

    /**
     * Test archiving a section transitions its status to 'archived'.
     */
    public function test_archiving_a_modular_section(): void
    {
        $section = CmsSection::create([
            'universe' => 'marte',
            'name' => 'Sección Para Archivo',
            'type' => 'curated_products',
            'sort_order' => 50,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.store.sections.archive', ['section' => $section->id]));

        $response->assertSessionHas('success');
        $this->assertEquals('archived', $section->fresh()->status);
    }

    /**
     * Test creating a promotional modal section with custom HTML, CSS and JS.
     */
    public function test_creating_promo_modal_section_with_custom_code(): void
    {
        $payload = [
            'universe' => 'marte',
            'name' => 'Modal Promocional Flash Sale',
            'type' => 'promo_modal',
            'title' => 'FLASH SALE 24H',
            'custom_html' => '<div class="flash-popup"><h2>50% OFF</h2></div>',
            'custom_css' => '.flash-popup { background: #000; color: #fff; }',
            'custom_js' => 'console.log("Promo modal loaded");',
        ];

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.store.sections.store'), $payload);

        $response->assertSessionHas('success');

        $modalSection = CmsSection::where('name', 'Modal Promocional Flash Sale')->first();
        $this->assertNotNull($modalSection);
        $this->assertEquals('promo_modal', $modalSection->type);
        $this->assertEquals('<div class="flash-popup"><h2>50% OFF</h2></div>', $modalSection->custom_html);
        $this->assertEquals('.flash-popup { background: #000; color: #fff; }', $modalSection->custom_css);
        $this->assertEquals('console.log("Promo modal loaded");', $modalSection->custom_js);
    }

    /**
     * Test product card sections strictly strip custom code textareas.
     */
    public function test_product_card_section_strips_custom_code(): void
    {
        $payload = [
            'universe' => 'all',
            'name' => 'Catálogo Curado Seguro',
            'type' => 'curated_products',
            'title' => 'PRODUCTOS SEGUROS',
            'custom_html' => '<script>alert("hack");</script>',
            'custom_css' => 'body { display: none; }',
            'custom_js' => 'alert("danger");',
        ];

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.store.sections.store'), $payload);

        $response->assertSessionHas('success');

        $section = CmsSection::where('name', 'Catálogo Curado Seguro')->first();
        $this->assertNotNull($section);
        $this->assertNull($section->custom_html);
        $this->assertNull($section->custom_css);
        $this->assertNull($section->custom_js);
    }

    /**
     * Test updating vitrina returns JSON response when requested via AJAX.
     */
    public function test_updating_vitrina_via_ajax_returns_json(): void
    {
        $payload = [
            'brand_name' => 'Venus Deluxe',
            'accent_color' => '#db2777',
            'background_color' => '#ffffff',
            'hero_title' => 'ELEGANCIA CONTEMPORÁNEA',
            'hero_subtitle' => 'Línea de seda y microfibra premium.',
            'hero_cta_text' => 'DESCUBRIR',
            'hero_cta_url' => '/venus/coleccion',
        ];

        $response = $this->actingAs($this->portalUser)
            ->postJson(route('portal.store.vitrinas.update', ['universe' => 'venus']), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'universe' => 'venus',
        ]);

        $this->assertDatabaseHas('cms_storefront_configs', [
            'universe' => 'venus',
            'brand_name' => 'Venus Deluxe',
            'accent_color' => '#db2777',
        ]);
    }

    /**
     * Test updating vitrina with full 8-token palette, logos, and favicon.
     */
    public function test_updating_vitrina_with_full_palette_and_branding_assets(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $logoFile = \Illuminate\Http\UploadedFile::fake()->image('brand-logo.png', 300, 100);
        $logoMobileFile = \Illuminate\Http\UploadedFile::fake()->image('brand-mobile.png', 100, 100);
        $faviconFile = \Illuminate\Http\UploadedFile::fake()->image('favicon.ico', 32, 32);

        $payload = [
            'brand_name' => 'Tiresias Vanguard',
            'kicker' => 'Moda Afirmativa & Diseño Fluido',
            'primary_color' => '#1e1b4b',
            'secondary_color' => '#312e81',
            'accent_color' => '#7c3aed',
            'background_color' => '#f8fafc',
            'surface_color' => '#ffffff',
            'text_color' => '#0f172a',
            'muted_text_color' => '#64748b',
            'border_color' => '#cbd5e1',
            'logo_file' => $logoFile,
            'logo_mobile_file' => $logoMobileFile,
            'favicon_file' => $faviconFile,
        ];

        $response = $this->actingAs($this->portalUser)
            ->post(route('portal.store.vitrinas.update', ['universe' => 'tiresias']), $payload);

        $response->assertSessionHas('success');

        $config = CmsStorefrontConfig::where('universe', 'tiresias')->first();
        $this->assertNotNull($config);
        $this->assertEquals('Tiresias Vanguard', $config->brand_name);
        $this->assertEquals('#1e1b4b', $config->primary_color);
        $this->assertEquals('#312e81', $config->secondary_color);
        $this->assertEquals('#7c3aed', $config->accent_color);
        $this->assertEquals('#f8fafc', $config->background_color);
        $this->assertEquals('#ffffff', $config->surface_color);
        $this->assertEquals('#0f172a', $config->text_color);
        $this->assertEquals('#64748b', $config->muted_text_color);
        $this->assertEquals('#cbd5e1', $config->border_color);
        $this->assertStringContainsString('uploads/branding/', $config->logo_url);
        $this->assertStringContainsString('uploads/branding/', $config->logo_mobile_url);
        $this->assertStringContainsString('uploads/branding/', $config->favicon_url);
    }

    /**
     * Test admin store CMS renders 5 unified tabs, global universe selector and accordion structure.
     */
    public function test_admin_store_cms_unified_accordion_and_global_universe_selector(): void
    {
        $response = $this->actingAs($this->portalUser)
            ->get(route('portal.store'));

        $response->assertStatus(200);

        // 1. Selector de Vitrina Global presente
        $response->assertSee('universe-selector-bar', false);
        $response->assertSee('data-action="switch-store-universe"', false);
        $response->assertSee('data-universe="marte"', false);
        $response->assertSee('data-universe="venus"', false);
        $response->assertSee('data-universe="tiresias"', false);
        $response->assertSee('data-universe="terra"', false);
        $response->assertSee('id="activeUniverseContextBadge"', false);

        // 2. 5 Pestañas Principales en Subnav Grid
        $response->assertSee('data-tab="visual"', false);
        $response->assertSee('data-tab="content_rules"', false);
        $response->assertSee('data-tab="pages"', false);
        $response->assertSee('data-tab="tracking"', false);
        $response->assertSee('data-tab="filters"', false);

        // 3. Panel de Contenido & Reglas Unificado con 4 Secciones de Acordeón
        $response->assertSee('id="storeContentRulesPanel"', false);
        $response->assertSee('id="accordionSecBanners"', false);
        $response->assertSee('id="accordionSecShipping"', false);
        $response->assertSee('id="accordionSecEditorial"', false);
        $response->assertSee('id="accordionSecMagazine"', false);

        // 4. Panel de Páginas & Navbar
        $response->assertSee('id="storePagesPanel"', false);

        // 5. Secciones Modulares presentes dentro del Editor Visual
        $response->assertSee('id="storeVisualPanel"', false);
        $response->assertSee('id="sectionsTable"', false);
    }
}


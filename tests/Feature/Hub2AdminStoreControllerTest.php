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
}

<?php

namespace Tests\Feature\Inventory;

use App\Models\CmsSection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSectionSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_cms_section_accessors_and_serialization()
    {
        $sec = CmsSection::create([
            'name' => 'Línea Básicos Esenciales',
            'title' => 'LÍNEA BÁSICOS ESENCIALES',
            'subtitle' => 'Prendas lisas de alta rotación para uso diario continuo',
            'universe' => 'all',
            'type' => 'catalog_section',
            'badge' => 'E-Commerce',
            'query_rules' => ['section' => 'Línea Básicos Esenciales'],
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $this->assertEquals('basicos', $sec->slug);
        $this->assertEquals('Prendas lisas de alta rotación para uso diario continuo', $sec->description);
        $this->assertTrue($sec->is_active);
        $this->assertFalse($sec->is_archived);

        $json = $sec->toArray();
        $this->assertArrayHasKey('slug', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertArrayHasKey('is_active', $json);
        $this->assertArrayHasKey('is_archived', $json);
        $this->assertEquals('basicos', $json['slug']);
        $this->assertTrue($json['is_active']);
    }

    public function test_api_can_crud_store_sections_with_slug_and_description()
    {
        // 1. Create section
        $createResp = $this->actingAs($this->adminUser)->postJson('/api/v1/inventory/settings/store-sections', [
            'name' => 'Línea Deportiva Activewear',
            'slug' => 'activewear',
            'description' => 'Prendas técnicas transpirables',
            'is_active' => true,
        ]);

        $createResp->assertStatus(201);
        $secId = $createResp->json('data.id');
        $this->assertNotNull($secId);
        $this->assertEquals('activewear', $createResp->json('data.slug'));

        // 2. Update section
        $updateResp = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/settings/store-sections/{$secId}", [
            'name' => 'Línea Deportiva & Activewear Pro',
            'slug' => 'activewear-pro',
            'description' => 'Actualizado: compresión y secado rápido',
            'is_active' => true,
        ]);

        $updateResp->assertStatus(200);
        $this->assertDatabaseHas('cms_sections', [
            'id' => $secId,
            'name' => 'Línea Deportiva & Activewear Pro',
        ]);

        // 3. Toggle active
        $toggleResp = $this->actingAs($this->adminUser)->patchJson("/api/v1/inventory/settings/store-sections/{$secId}/toggle-active");
        $toggleResp->assertStatus(200);
        $this->assertDatabaseHas('cms_sections', [
            'id' => $secId,
            'status' => 'draft',
        ]);

        // 4. Delete section
        $deleteResp = $this->actingAs($this->adminUser)->deleteJson("/api/v1/inventory/settings/store-sections/{$secId}");
        $deleteResp->assertStatus(200);
        $this->assertDatabaseMissing('cms_sections', [
            'id' => $secId,
        ]);
    }

    public function test_product_in_one_section_does_not_block_deleting_other_sections()
    {
        // Section 1: basicos (has product)
        $section1 = CmsSection::create([
            'name' => 'Línea Básicos Esenciales',
            'title' => 'LÍNEA BÁSICOS ESENCIALES',
            'subtitle' => 'Prendas básicas',
            'universe' => 'all',
            'type' => 'catalog_section',
            'status' => 'active',
        ]);

        // Section 2: hero (no product)
        $section2 = CmsSection::create([
            'name' => 'Hero Editorial Principal',
            'title' => 'HERO',
            'universe' => 'all',
            'type' => 'hero_slider',
            'status' => 'active',
        ]);

        // Create product assigned only to 'basicos'
        Product::create([
            'sku' => 'SKU-TEST-001',
            'title' => 'Bóxer Básico',
            'slug' => 'boxer-basico',
            'section_name' => 'basicos',
            'type' => 'propio',
            'price' => 200,
            'cost' => 80,
            'meta' => [
                'website_section' => 'basicos',
            ],
        ]);

        // Section 2 has slug 'hero-editorial', which is different from 'basicos'
        $this->assertEquals('basicos', $section1->slug);
        $this->assertEquals('hero-editorial', $section2->slug);
        $this->assertNotEquals($section1->slug, $section2->slug);

        // Section 2 can be deleted via API
        $deleteResp = $this->actingAs($this->adminUser)->deleteJson("/api/v1/inventory/settings/store-sections/{$section2->id}");
        $deleteResp->assertStatus(200);
        $this->assertDatabaseMissing('cms_sections', [
            'id' => $section2->id,
        ]);
    }
}

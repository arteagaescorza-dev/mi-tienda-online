<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCreationDrawerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_generate_commercial_description_and_seo_tags_with_ai(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/products/generate-description', [
            'name' => 'Boxer Trunk Microfibra Sport Air',
            'brand' => 'Torzo Tech',
            'category' => 'Boxers',
            'fabric_type' => 'Microfibra deportiva transpirable',
            'color_name' => 'Negro Carbono',
            'design_type' => 'sin_diseno',
            'pvp' => 450.00,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'title',
                    'description',
                    'bullet_points',
                    'seo_tags',
                    'suggested_tags',
                    'category',
                    'brand',
                    'metadata',
                ],
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['description']);
        $this->assertGreaterThanOrEqual(4, count($data['bullet_points']));
        $this->assertNotEmpty($data['seo_tags']);
        $this->assertStringContainsString('Torzo Tech', $data['title']);
    }

    public function test_generate_description_handles_cosmetics_dimension(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/products/generate-description', [
            'name' => 'Gel Hidratante Revitalizante Facial',
            'brand' => 'DermaCare MX',
            'category' => 'Cosmetica / Skincare',
            'color_name' => 'Tono 02 Claro',
            'variant_dimension' => 'shade',
            'pvp' => 320.00,
        ]);

        $response->assertOk();
        $data = $response->json('data');
        $this->assertStringContainsString('Gel Hidratante', $data['title']);
        $this->assertNotEmpty($data['description']);
        $this->assertNotEmpty($data['seo_tags']);
    }

    public function test_generate_description_handles_minimal_payload_gracefully(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/products/generate-description', []);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $data = $response->json('data');
        $this->assertNotEmpty($data['title']);
        $this->assertNotEmpty($data['description']);
    }

    public function test_check_duplicate_accepts_flexible_name_and_pvp_parameters(): void
    {
        Product::create([
            'sku' => 'TXT-CK-0010',
            'title' => 'Boxer Brief Classic Cotton Stretch',
            'slug' => 'boxer-brief-classic-cotton-stretch',
            'brand' => 'Calvin Klein',
            'price' => 599.00,
            'cost' => 200.00,
            'stock' => 15,
            'is_active' => true,
        ]);

        // Consulta pasando name y pvp en lugar de title y price
        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/products/check-duplicate', [
            'name' => 'Boxer Brief Classic Cotton Stretch',
            'brand' => 'Calvin Klein',
            'pvp' => 599.00,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.has_potential_duplicate', true)
            ->assertJsonPath('data.risk_level', 'duplicate_match');

        $matches = $response->json('data.matches');
        $this->assertNotEmpty($matches);
        $this->assertEquals('TXT-CK-0010', $matches[0]['sku']);
    }

    public function test_generate_sku_integration(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/products/generate-sku', [
            'category' => 'Boxers',
            'brand' => 'Torzo B2B',
            'variant_code' => 'M',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.category_prefix', 'TXT')
            ->assertJsonPath('data.brand_code', 'TB');

        $this->assertMatchesRegularExpression('/^TXT-TB-\d{4}-M$/', $response->json('data.sku'));
    }

    public function test_can_store_product_via_panoramic_modal_endpoint(): void
    {
        $payload = [
            'sku' => 'MOD-PAN-9999-L',
            'name' => 'Boxer Panoramic Test',
            'brand' => 'Torzo Tech',
            'category' => 'Boxers',
            'pvp' => 450.00,
            'cost' => 180.00,
            'type' => 'physical',
            'stock' => 25,
            'sizes' => [
                ['name' => 'L', 'stock' => 25],
            ],
            'fabric_type' => 'Microfibra',
            'color_name' => 'Negro',
            'location' => 'RACK-B-01',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/inventory/products', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.sku', 'MOD-PAN-9999-L')
            ->assertJsonPath('data.title', 'Boxer Panoramic Test');

        $this->assertDatabaseHas('products', [
            'sku' => 'MOD-PAN-9999-L',
            'title' => 'Boxer Panoramic Test',
            'brand' => 'Torzo Tech',
            'price' => 450.00,
            'cost' => 180.00,
            'type' => 'physical',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => 'MOD-PAN-9999-L-L',
            'price' => 450.00,
        ]);
    }
}


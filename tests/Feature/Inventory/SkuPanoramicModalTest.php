<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkuPanoramicModalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_get_sku_detail_with_financials_and_stock_kpis(): void
    {
        $product = Product::create([
            'sku' => 'TXT-TRZ-0001',
            'title' => 'Boxer Trunk Modal Seamless',
            'slug' => 'boxer-trunk-modal-seamless-txt-trz-0001',
            'brand' => 'Torzo Tech',
            'price' => 450.00,
            'cost' => 150.00,
            'type' => 'physical',
            'visible' => true,
            'description' => 'Prenda deportiva de alto rendimiento.',
            'meta' => [
                'category' => 'Boxers',
                'subcategory' => 'Trunks',
                'regular_price' => 500.00,
                'discount_pct' => 10,
                'weighted_average_cost' => 145.00,
                'sizes' => ['ch' => 10, 'm' => 25, 'g' => 20, 'xl' => 5],
                'local_stock' => 60,
                'committed_stock' => 5,
                'locked_stock' => 2,
                'location' => 'A-01-B',
                'fabric_type' => 'Modal Seamless',
                'color_name' => 'Negro Carbono',
                'requires_sanitary_control' => false,
            ],
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/inventory/products/TXT-TRZ-0001/detail');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.sku', 'TXT-TRZ-0001')
            ->assertJsonPath('data.brand', 'Torzo Tech')
            ->assertJsonPath('data.stock.local_stock', 60)
            ->assertJsonPath('data.stock.committed_stock', 5)
            ->assertJsonPath('data.stock.locked_stock', 2)
            ->assertJsonPath('data.stock.available_stock', 53)
            ->assertJsonPath('data.financials.discount_pct', 10)
            ->assertJsonPath('data.financials.margin_health', 'healthy');

        $this->assertEquals(500.00, (float) $response->json('data.financials.regular_price'));
        $this->assertEquals(405.00, (float) $response->json('data.financials.effective_price'));
        $this->assertEquals(145.00, (float) $response->json('data.financials.weighted_average_cost'));
        $this->assertEquals(260.00, (float) $response->json('data.financials.unit_profit'));
    }

    public function test_get_sku_detail_returns_404_for_unknown_sku(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/inventory/products/NONEXISTENT-SKU/detail');

        $response->assertNotFound()
            ->assertJsonPath('status', 'error');
    }

    public function test_can_update_product_completely_and_recalculate_metrics(): void
    {
        $product = Product::create([
            'sku' => 'TXT-TRZ-0002',
            'title' => 'Jockstrap Athletic Carbon',
            'slug' => 'jockstrap-athletic-carbon',
            'brand' => 'Torzo Tech',
            'price' => 380.00,
            'cost' => 120.00,
            'type' => 'physical',
            'visible' => true,
        ]);

        $updatePayload = [
            'name' => 'Jockstrap Athletic Carbon Pro 2026',
            'brand' => 'Torzo Tech MX',
            'category' => 'Jocks',
            'subcategory' => 'Athletic',
            'type' => 'propio',
            'pvp' => 420.00,
            'regular_price' => 500.00,
            'discount_pct' => 15, // 420 * 0.85 = 357.00
            'cost' => 130.00,
            'weighted_average_cost' => 135.00,
            'sizes' => ['ch' => 15, 'm' => 30, 'g' => 25, 'xl' => 10], // Total = 80
            'location' => 'B-02-A',
            'fabric_type' => 'Microfibra DryFit',
            'color_name' => 'Rojo Escarlata',
            'color_hex' => '#dc2626',
            'design_type' => 'sin_diseno',
            'website_section' => 'temporada',
            'is_published' => true,
            'description' => 'Edicion profesional actualizada con soporte anatomico.',
        ];

        $response = $this->actingAs($this->user)->putJson('/api/v1/inventory/products/TXT-TRZ-0002/update-complete', $updatePayload);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.sku', 'TXT-TRZ-0002')
            ->assertJsonPath('data.local_stock', 80);

        $this->assertEquals(357.00, (float) $response->json('data.effective_price'));

        // Verificar en BD
        $product->refresh();
        $this->assertEquals('Jockstrap Athletic Carbon Pro 2026', $product->title);
        $this->assertEquals(420.00, (float) $product->price);
        $this->assertEquals(80, $product->meta['local_stock']);
        $this->assertEquals('B-02-A', $product->meta['location']);
        $this->assertEquals(357.00, $product->meta['effective_price']);
    }

    public function test_update_complete_validates_required_pricing_fields(): void
    {
        $response = $this->actingAs($this->user)->putJson('/api/v1/inventory/products/ANY-SKU/update-complete', [
            'name' => 'Incomplete Product',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['pvp', 'cost']);
    }
}

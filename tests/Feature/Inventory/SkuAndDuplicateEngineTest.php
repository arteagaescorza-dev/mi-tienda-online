<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkuAndDuplicateEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'revops@tienda.test',
            'role' => 'portal',
        ]);
    }

    public function test_can_generate_standardized_sku_for_textile_product_with_brand(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/generate-sku', [
                'category' => 'Ropa Interior',
                'brand' => 'Torzo',
                'title' => 'Boxer Brief Deportivo Microfibra',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.category_prefix', 'TXT')
            ->assertJsonPath('data.brand_code', 'TRZ')
            ->assertJsonPath('data.sequence', 1)
            ->assertJsonPath('data.formatted_sequence', '0001')
            ->assertJsonPath('data.sku', 'TXT-TRZ-0001')
            ->assertJsonPath('data.base_sku', 'TXT-TRZ-0001');
    }

    public function test_increments_consecutive_sequence_when_previous_skus_exist(): void
    {
        // Crear productos con SKUs previos
        Product::create([
            'sku' => 'TXT-TRZ-0001',
            'title' => 'Boxer Brief Inicial',
            'slug' => 'boxer-brief-inicial',
            'brand' => 'Torzo',
            'price' => 299.00,
            'cost' => 120.00,
        ]);

        Product::create([
            'sku' => 'TXT-TRZ-0002',
            'title' => 'Boxer Trunk Clasico',
            'slug' => 'boxer-trunk-clasico',
            'brand' => 'Torzo',
            'price' => 310.00,
            'cost' => 130.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/generate-sku', [
                'category' => 'Ropa Interior',
                'brand' => 'Torzo',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.sequence', 3)
            ->assertJsonPath('data.formatted_sequence', '0003')
            ->assertJsonPath('data.sku', 'TXT-TRZ-0003')
            ->assertJsonPath('data.base_sku', 'TXT-TRZ-0003');
    }

    public function test_appends_variant_suffix_for_size_and_color(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/generate-sku', [
                'category' => 'Ropa Interior',
                'brand' => 'Calvin Klein',
                'variant' => [
                    'size' => 'M',
                    'color' => 'NEG',
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.category_prefix', 'TXT')
            ->assertJsonPath('data.brand_code', 'CK')
            ->assertJsonPath('data.variant_code', 'M-NEG')
            ->assertJsonPath('data.base_sku', 'TXT-CK-0001')
            ->assertJsonPath('data.sku', 'TXT-CK-0001-M-NEG');
    }

    public function test_resolves_prefixes_for_fragrance_cosmetics_and_accessories(): void
    {
        // 1. Perfumeria -> FRG
        $respFrg = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/generate-sku', [
                'category' => 'Perfumeria y Aromas',
                'brand' => 'Terra',
                'variant' => '100ML',
            ]);

        $respFrg->assertStatus(200)
            ->assertJsonPath('data.category_prefix', 'FRG')
            ->assertJsonPath('data.brand_code', 'TRA')
            ->assertJsonPath('data.sku', 'FRG-TRA-0001-100ML');

        // 2. Cosmetica -> CSM
        $respCsm = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/generate-sku', [
                'category' => 'Cosmetica y Labiales',
                'brand' => 'Venus',
            ]);

        $respCsm->assertStatus(200)
            ->assertJsonPath('data.category_prefix', 'CSM')
            ->assertJsonPath('data.brand_code', 'VNS')
            ->assertJsonPath('data.sku', 'CSM-VNS-0001');

        // 3. Accesorios -> ACC
        $respAcc = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/generate-sku', [
                'category' => 'Accesorios y Arneses',
                'brand' => 'Andrew Christian',
            ]);

        $respAcc->assertStatus(200)
            ->assertJsonPath('data.category_prefix', 'ACC')
            ->assertJsonPath('data.brand_code', 'AC')
            ->assertJsonPath('data.sku', 'ACC-AC-0001');
    }

    public function test_avoids_database_collision_by_incrementing_sequence(): void
    {
        $prod = Product::create([
            'sku' => 'TXT-TRZ-0001',
            'title' => 'Producto Base',
            'slug' => 'producto-base',
            'brand' => 'Torzo',
            'price' => 200,
            'cost' => 100,
        ]);

        ProductVariant::create([
            'product_id' => $prod->id,
            'sku' => 'TXT-TRZ-0002',
            'price' => 200,
            'cost' => 100,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/generate-sku', [
                'category' => 'textil',
                'brand' => 'Torzo',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.sku', 'TXT-TRZ-0003');
    }

    public function test_detects_exact_duplicate_by_sku_with_100_percent_score(): void
    {
        Product::create([
            'sku' => 'TXT-TRZ-0010',
            'title' => 'Boxer Brief Microfibra Negro',
            'slug' => 'boxer-brief-microfibra-negro',
            'brand' => 'Torzo',
            'price' => 399.00,
            'cost' => 160.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/check-duplicate', [
                'title' => 'Cualquier Titulo Nuevo',
                'sku' => 'TXT-TRZ-0010',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.has_potential_duplicate', true)
            ->assertJsonPath('data.highest_score', 100)
            ->assertJsonPath('data.risk_level', 'duplicate_match');

        $this->assertNotEmpty($response->json('data.matches'));
        $this->assertEquals('TXT-TRZ-0010', $response->json('data.matches.0.sku'));
    }

    public function test_detects_high_risk_duplicate_by_similar_title_and_same_brand(): void
    {
        Product::create([
            'sku' => 'TXT-TRZ-0020',
            'title' => 'Boxer Brief Deportivo Microfibra',
            'slug' => 'boxer-brief-deportivo-microfibra',
            'brand' => 'Torzo',
            'price' => 399.00,
            'cost' => 150.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/check-duplicate', [
                'title' => 'Boxer Deportivo Microfibra Brief',
                'brand' => 'Torzo',
                'price' => 399.00,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.has_potential_duplicate', true)
            ->assertJsonPath('data.risk_level', 'duplicate_match');

        $score = $response->json('data.highest_score');
        $this->assertGreaterThanOrEqual(80.0, $score);
    }

    public function test_detects_warning_when_title_is_moderately_similar(): void
    {
        Product::create([
            'sku' => 'TXT-TRZ-0030',
            'title' => 'Trusa Clasica Algodon Elastano',
            'slug' => 'trusa-clasica-algodon-elastano',
            'brand' => 'Torzo',
            'price' => 250.00,
            'cost' => 90.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/check-duplicate', [
                'title' => 'Trusa Algodon Blanca',
                'brand' => 'Torzo',
                'price' => 250.00,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $risk = $response->json('data.risk_level');
        $this->assertTrue(in_array($risk, ['warning', 'duplicate_match'], true));
    }

    public function test_reports_no_duplicate_when_candidate_is_completely_distinct(): void
    {
        Product::create([
            'sku' => 'TXT-TRZ-0040',
            'title' => 'Boxer Brief Deportivo Microfibra',
            'slug' => 'boxer-brief-deportivo-microfibra-2',
            'brand' => 'Torzo',
            'price' => 399.00,
            'cost' => 150.00,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/check-duplicate', [
                'title' => 'Perfume Amaderado Noche Misteriosa',
                'brand' => 'Terra',
                'price' => 899.00,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.has_potential_duplicate', false)
            ->assertJsonPath('data.risk_level', 'none');
        $this->assertEmpty($response->json('data.matches'));
    }

    public function test_excludes_specified_product_id_during_update_duplicate_check(): void
    {
        $existing = Product::create([
            'sku' => 'TXT-TRZ-0050',
            'title' => 'Boxer Trunk Sin Costuras',
            'slug' => 'boxer-trunk-sin-costuras',
            'brand' => 'Torzo',
            'price' => 320.00,
            'cost' => 110.00,
        ]);

        // Cotejar con el mismo titulo pero excluyendo su propio ID
        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products/check-duplicate', [
                'title' => 'Boxer Trunk Sin Costuras',
                'brand' => 'Torzo',
                'exclude_product_id' => $existing->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.has_potential_duplicate', false)
            ->assertJsonPath('data.risk_level', 'none');
        $this->assertEmpty($response->json('data.matches'));
    }
}

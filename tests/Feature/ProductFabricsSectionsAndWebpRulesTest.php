<?php

namespace Tests\Feature;

use App\Models\ApparelColor;
use App\Models\ApparelFabric;
use App\Models\CmsSection;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ImageNormalizationService;
use App\Services\Inventory\SkuGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductFabricsSectionsAndWebpRulesTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_test@mitienda.test'],
            [
                'name' => 'Admin Test User',
                'password' => bcrypt('password123'),
                'role' => 'admin'
            ]
        );
    }

    /**
     * Regla 1: Asignación de variantes de color por fotografía en la ficha de producto
     */
    public function test_rule_1_product_drawer_updates_image_color_map_and_gallery()
    {
        $product = Product::create([
            'sku' => 'TEST-IMG-001',
            'title' => 'Boxer Prueba Color Map',
            'name' => 'Boxer Prueba Color Map',
            'slug' => 'boxer-prueba-color-map',
            'category' => 'Boxers',
            'type' => 'propio',
            'cost' => 100,
            'price' => 250,
            'pvp' => 250,
            'status' => 'En Stock',
            'color' => 'Negro Profundo'
        ]);

        $payload = [
            'name' => 'Boxer Prueba Color Map Modificado',
            'cost' => 100,
            'pvp' => 250,
            'images' => [
                'https://cdn.mitienda.com/photo1.webp',
                'https://cdn.mitienda.com/photo2.webp'
            ],
            'images_by_color' => [
                'https://cdn.mitienda.com/photo1.webp' => 'Negro Profundo',
                'https://cdn.mitienda.com/photo2.webp' => 'Rojo Borgoña'
            ],
            'color_name' => 'Negro Profundo',
            'fabric_type' => 'Modal Seamless',
            'website_section' => 'basicos'
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/inventory/products/{$product->sku}/update-complete", $payload);

        $response->assertStatus(200);

        $product->refresh();
        $this->assertCount(2, $product->images);
        $this->assertEquals('Negro Profundo', $product->images_by_color['https://cdn.mitienda.com/photo1.webp'] ?? null);
        $this->assertEquals('Rojo Borgoña', $product->images_by_color['https://cdn.mitienda.com/photo2.webp'] ?? null);
        $this->assertEquals('basicos', $product->section_name);
        $this->assertEquals('Modal Seamless', $product->fabric);
    }

    /**
     * Regla 2: Conversión obligatoria a WebP y normalización uniforme (1080x1350)
     */
    public function test_rule_2_image_upload_normalizes_to_webp_standard_1080x1350()
    {
        Storage::fake('public');

        $uploadedFile = UploadedFile::fake()->image('sample_photo.jpg', 400, 300);

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/upload-image', [
                'image' => $uploadedFile
            ]);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue($json['success'] ?? false);
        $this->assertEquals('webp', $json['format'] ?? ($json['data']['format'] ?? null));
        $this->assertEquals(1080, $json['width'] ?? ($json['data']['width'] ?? null));
        $this->assertEquals(1350, $json['height'] ?? ($json['data']['height'] ?? null));
        $this->assertStringEndsWith('.webp', $json['url'] ?? ($json['data']['url'] ?? ''));
    }

    /**
     * Regla 3: Alta in situ de colores desde workstation y API de ajustes
     */
    public function test_rule_3_quick_color_creation_api()
    {
        $colorPayload = [
            'name' => 'Galaxia Tornasol Prueba ' . uniqid(),
            'hex' => 'linear-gradient(135deg, #6366f1 0%, #ec4899 50%, #f59e0b 100%)',
            'type' => 'iridescent',
            'is_active' => true
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/settings/colors', $colorPayload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('apparel_colors', [
            'name' => $colorPayload['name'],
            'color_type' => 'iridescent'
        ]);
    }

    /**
     * Regla 4: SKU dinámico conforme a las cualidades del producto (proveedor, marca, categoría, tela, color)
     */
    public function test_rule_4_dynamic_sku_recalculates_with_product_qualities()
    {
        $generator = app(SkuGeneratorService::class);

        $res1 = $generator->generateSku([
            'category' => 'Boxers',
            'brand' => 'ModaIntima MX',
            'fabric' => 'Modal Seamless',
            'color' => 'Negro Profundo'
        ]);
        $sku1 = $res1['sku'];

        $res2 = $generator->generateSku([
            'category' => 'Jocks',
            'brand' => 'Torzo B2B',
            'fabric' => 'Seda / Satín Premium',
            'color' => 'Rojo Borgoña'
        ]);
        $sku2 = $res2['sku'];

        $this->assertStringContainsString('-MOD-', $sku1);
        $this->assertStringContainsString('-SED-', $sku2);
        $this->assertNotEquals($sku1, $sku2);
    }

    /**
     * Regla 5: CRUD de telas en ajustes de inventario
     */
    public function test_rule_5_fabric_crud_endpoints()
    {
        // 1. Create Fabric
        $createRes = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/settings/fabrics', [
                'name' => 'Bambú Orgánico Test ' . uniqid(),
                'description' => 'Fibra ecológica 100% biodegradable',
                'is_active' => true
            ]);
        $createRes->assertStatus(201);
        $fabricId = $createRes->json('data.id');

        // 2. List Fabrics
        $listRes = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/inventory/settings/fabrics');
        $listRes->assertStatus(200);

        // 3. Update Fabric
        $updateRes = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/inventory/settings/fabrics/{$fabricId}", [
                'name' => 'Bambú Orgánico Actualizado',
                'description' => 'Descripción modificada'
            ]);
        $updateRes->assertStatus(200);

        // 4. Archive & Restore Fabric
        $archiveRes = $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/inventory/settings/fabrics/{$fabricId}/archive");
        $archiveRes->assertStatus(200);

        $restoreRes = $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/inventory/settings/fabrics/{$fabricId}/restore");
        $restoreRes->assertStatus(200);

        // 5. Delete Fabric
        $delRes = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/inventory/settings/fabrics/{$fabricId}");
        $delRes->assertStatus(200);
    }

    /**
     * Regla 6: Asociación directa de telas con productos
     */
    public function test_rule_6_fabric_associates_directly_with_products()
    {
        $fabric = ApparelFabric::firstOrCreate(
            ['name' => 'Algodón Pima Peruano'],
            ['slug' => 'algodon-pima-peruano', 'description' => 'Fibra de hilo largo', 'is_active' => true]
        );

        $product = Product::create([
            'sku' => 'TEST-FAB-001',
            'title' => 'Camisilla Pima Cotton',
            'name' => 'Camisilla Pima Cotton',
            'slug' => 'camisilla-pima-cotton',
            'category' => 'Camisillas',
            'type' => 'propio',
            'cost' => 120,
            'price' => 320,
            'pvp' => 320,
            'status' => 'En Stock',
            'fabric' => $fabric->name,
            'fabric_id' => $fabric->id
        ]);

        $this->assertEquals($fabric->id, $product->fabric_id);
        $this->assertEquals('Algodón Pima Peruano', $product->fabric);
        $this->assertEquals($fabric->id, $product->fabricRelation->id);
    }

    /**
     * Regla 7: CRUD de secciones de e-commerce y vinculación directa
     */
    public function test_rule_7_store_sections_crud_and_product_relation()
    {
        // 1. Create section
        $secRes = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/settings/store-sections', [
                'name' => 'Línea Noche & Seducción ' . uniqid(),
                'slug' => 'noche-seduccion-' . uniqid(),
                'description' => 'Prendas transparentes y diseño sensual',
                'is_active' => true
            ]);
        $secRes->assertStatus(201);
        $secId = $secRes->json('data.id');

        // 2. List sections
        $listRes = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/inventory/settings/store-sections');
        $listRes->assertStatus(200);

        // 3. Update section
        $upRes = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/inventory/settings/store-sections/{$secId}", [
                'name' => 'Línea Noche Actualizada',
                'description' => 'Descripción editada'
            ]);
        $upRes->assertStatus(200);

        // 4. Toggle active
        $toggleRes = $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/inventory/settings/store-sections/{$secId}/toggle-active");
        $toggleRes->assertStatus(200);

        // 5. Delete section
        $delRes = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/inventory/settings/store-sections/{$secId}");
        $delRes->assertStatus(200);
    }
}

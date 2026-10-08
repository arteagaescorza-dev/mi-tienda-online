<?php

namespace Tests\Feature\Inventory;

use App\Models\ApparelSizeCurve;
use App\Models\ApparelSize;
use App\Models\FragranceVolume;
use App\Models\Category;
use App\Models\User;
use App\Services\Inventory\VariantDimensionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantDimensionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@marte.test',
            'role' => 'portal',
        ]);
    }

    public function test_can_bootstrap_and_get_default_size_curves(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/inventory/settings/size-curves');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $curves = $response->json('data');
        $this->assertNotEmpty($curves);

        // Curva americana debe existir por defecto
        $americana = collect($curves)->firstWhere('slug', 'curva-americana-estandar');
        $this->assertNotNull($americana);
        $this->assertTrue($americana['is_default']);
        $this->assertCount(8, $americana['sizes']); // XS a 4XL
    }

    public function test_can_create_update_toggle_and_archive_size_curve(): void
    {
        // 1. Crear curva personalizada
        $createResponse = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/settings/size-curves', [
                'name' => 'Curva Especial Festival',
                'category_scope' => 'festival',
                'description' => 'Tallas especiales para arneses y tops de festival',
                'is_default' => false,
                'is_active' => true,
                'sizes' => [
                    ['code' => 'CH-M', 'label' => 'Chica-Mediana', 'sort_order' => 1],
                    ['code' => 'G-XL', 'label' => 'Grande-Extra Grande', 'sort_order' => 2],
                ],
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $curveId = $createResponse->json('data.id');
        $this->assertDatabaseHas('apparel_size_curves', ['id' => $curveId, 'name' => 'Curva Especial Festival']);
        $this->assertDatabaseHas('apparel_sizes', ['size_curve_id' => $curveId, 'code' => 'CH-M']);
        $this->assertDatabaseHas('apparel_sizes', ['size_curve_id' => $curveId, 'code' => 'G-XL']);

        // 2. Actualizar curva
        $updateResponse = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/inventory/settings/size-curves/{$curveId}", [
                'name' => 'Curva Especial Circuito 2026',
                'category_scope' => 'circuito',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Curva Especial Circuito 2026');

        // 3. Conmutar activo/inactivo
        $toggleResponse = $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/inventory/settings/size-curves/{$curveId}/toggle-active");

        $toggleResponse->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        // 4. Archivar (Soft delete)
        $archiveResponse = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/settings/size-curves/{$curveId}/archive");

        $archiveResponse->assertStatus(200);
        $this->assertSoftDeleted('apparel_size_curves', ['id' => $curveId]);

        // 5. Restaurar
        $restoreResponse = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/settings/size-curves/{$curveId}/restore");

        $restoreResponse->assertStatus(200);
        $this->assertDatabaseHas('apparel_size_curves', ['id' => $curveId, 'deleted_at' => null]);
    }

    public function test_can_add_update_and_delete_individual_sizes_in_curve(): void
    {
        $curve = ApparelSizeCurve::create([
            'name' => 'Curva Trajes de Bano',
            'slug' => 'curva-trajes-bano',
            'is_active' => true,
        ]);

        // 1. Agregar talla
        $addResponse = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/settings/size-curves/{$curve->id}/sizes", [
                'code' => 'CH',
                'label' => 'Chica / Slim',
                'sort_order' => 1,
                'waist_min_cm' => 70.0,
                'waist_max_cm' => 75.0,
            ]);

        $addResponse->assertStatus(201);
        $sizeId = $addResponse->json('data.id');

        // 2. Modificar talla
        $updateResponse = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/inventory/settings/sizes/{$sizeId}", [
                'label' => 'Chica / Ajuste Atletic',
                'waist_min_cm' => 72.0,
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.label', 'Chica / Ajuste Atletic');

        // 3. Conmutar activo/inactivo
        $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/inventory/settings/sizes/{$sizeId}/toggle-active")
            ->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        // 4. Eliminar talla
        $deleteResponse = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/inventory/settings/sizes/{$sizeId}");

        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('apparel_sizes', ['id' => $sizeId]);
    }

    public function test_can_manage_fragrance_volumes_crud(): void
    {
        // 1. Bootstrap y consulta
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/inventory/settings/fragrance-volumes');

        $response->assertStatus(200);
        $volumes = $response->json('data');
        $this->assertNotEmpty($volumes);

        // 2. Crear nuevo volumen
        $createResponse = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/settings/fragrance-volumes', [
                'value' => 250.00,
                'unit' => 'ml',
                'label' => '250ml Botella Spa',
                'description' => 'Presentacion para aceites corporales y salones',
                'is_active' => true,
            ]);

        $createResponse->assertStatus(201);
        $volumeId = $createResponse->json('data.id');

        // 3. Editar volumen
        $updateResponse = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/inventory/settings/fragrance-volumes/{$volumeId}", [
                'label' => '250ml Formato Spa Profesional',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.label', '250ml Formato Spa Profesional');

        // 4. Toggle activo
        $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/inventory/settings/fragrance-volumes/{$volumeId}/toggle-active")
            ->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        // 5. Archivar
        $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/settings/fragrance-volumes/{$volumeId}/archive")
            ->assertStatus(200);
        $this->assertSoftDeleted('fragrance_volumes', ['id' => $volumeId]);

        // 6. Restaurar
        $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/settings/fragrance-volumes/{$volumeId}/restore")
            ->assertStatus(200);
        $this->assertDatabaseHas('fragrance_volumes', ['id' => $volumeId, 'deleted_at' => null]);

        // 7. Eliminar definitivo
        $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/inventory/settings/fragrance-volumes/{$volumeId}")
            ->assertStatus(200);
        $this->assertDatabaseMissing('fragrance_volumes', ['id' => $volumeId]);
    }

    public function test_can_manage_categories_and_subcategories_crud(): void
    {
        // 1. Bootstrap y consulta
        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/inventory/settings/categories');

        $response->assertStatus(200);
        $categories = $response->json('data');
        $this->assertNotEmpty($categories);

        // 2. Crear categoria raiz
        $createRoot = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/settings/categories', [
                'name' => 'Moda Denim & Jeans',
                'description' => 'Pantalones vaqueros y bermudas de mezclilla',
                'is_active' => true,
            ]);

        $createRoot->assertStatus(201);
        $rootId = $createRoot->json('data.id');

        // 3. Crear subcategoria hija
        $createChild = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/settings/categories', [
                'name' => 'Skinny Jeans',
                'description' => 'Corte ajustado stretch',
                'parent_id' => $rootId,
                'is_active' => true,
            ]);

        $createChild->assertStatus(201);
        $childId = $createChild->json('data.id');
        $this->assertDatabaseHas('categories', ['id' => $childId, 'parent_id' => $rootId]);

        // 4. Modificar categoria
        $this->actingAs($this->adminUser)
            ->putJson("/api/v1/inventory/settings/categories/{$childId}", [
                'name' => 'Super Skinny Stretch Jeans',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Super Skinny Stretch Jeans');

        // 5. Toggle activo
        $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/inventory/settings/categories/{$childId}/toggle-active")
            ->assertStatus(200)
            ->assertJsonPath('data.is_active', false);

        // 6. Archivar
        $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/settings/categories/{$childId}/archive")
            ->assertStatus(200);
        $this->assertSoftDeleted('categories', ['id' => $childId]);

        // 7. Restaurar
        $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/settings/categories/{$childId}/restore")
            ->assertStatus(200);
        $this->assertDatabaseHas('categories', ['id' => $childId, 'deleted_at' => null]);

        // 8. Eliminar definitivo
        $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/inventory/settings/categories/{$childId}")
            ->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $childId]);
    }

    public function test_portal_inventory_view_contains_variant_dimensions_data(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.inventory'));

        $response->assertStatus(200);
        $response->assertViewHas('portalSizeCurves');
        $response->assertViewHas('portalFragranceVolumes');
        $response->assertViewHas('portalCategories');
    }

    /**
     * Regla: Las curvas de tallas se pueden asociar a marcas y proveedores y editar correctamente.
     */
    public function test_size_curves_can_be_associated_to_suppliers_and_brands_and_updated(): void
    {
        $supplier = \App\Models\Supplier::create([
            'name' => 'Torzo Textil',
            'legal_name' => 'Torzo Ropa S.A.',
            'vendor_code' => 'SUP-TRZ-CURV',
            'origin' => 'manual',
            'status' => 'connected',
        ]);

        $brand = \App\Models\Brand::create([
            'name' => 'Torzo Underwear',
            'slug' => 'torzo-underwear',
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ]);

        // 1. Crear curva asociada a proveedor y marca
        $createRes = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/settings/size-curves', [
                'name' => 'Curva Torzo Fit',
                'category_scope' => 'textil',
                'supplier_id' => $supplier->id,
                'brand_id' => $brand->id,
                'description' => 'Curva ajustada para Torzo Underwear',
                'is_default' => false,
                'is_active' => true,
                'sizes' => [
                    ['code' => 'CH', 'label' => 'Chica Torzo', 'sort_order' => 1],
                    ['code' => 'M', 'label' => 'Mediana Torzo', 'sort_order' => 2],
                ],
            ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.supplier_id', $supplier->id)
            ->assertJsonPath('data.brand_id', $brand->id)
            ->assertJsonPath('data.supplier.name', 'Torzo Textil')
            ->assertJsonPath('data.brand.name', 'Torzo Underwear');

        $curveId = $createRes->json('data.id');
        $this->assertDatabaseHas('apparel_size_curves', [
            'id' => $curveId,
            'name' => 'Curva Torzo Fit',
            'supplier_id' => $supplier->id,
            'brand_id' => $brand->id,
        ]);

        // 2. Actualizar la curva (cambiar nombre y desasociar marca / reasociar)
        $updateRes = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/inventory/settings/size-curves/{$curveId}", [
                'name' => 'Curva Torzo Ultra Stretch 2026',
                'category_scope' => 'textil',
                'supplier_id' => $supplier->id,
                'brand_id' => null,
                'description' => 'Actualizada para abarcar todas las marcas del proveedor',
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Curva Torzo Ultra Stretch 2026')
            ->assertJsonPath('data.supplier_id', $supplier->id)
            ->assertJsonPath('data.brand_id', null);

        $this->assertDatabaseHas('apparel_size_curves', [
            'id' => $curveId,
            'name' => 'Curva Torzo Ultra Stretch 2026',
            'supplier_id' => $supplier->id,
            'brand_id' => null,
        ]);

        // 3. Consultar listado y verificar carga de relaciones
        $listRes = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/inventory/settings/size-curves');

        $listRes->assertStatus(200);
        $found = collect($listRes->json('data'))->firstWhere('id', $curveId);
        $this->assertNotNull($found);
        $this->assertEquals('Curva Torzo Ultra Stretch 2026', $found['name']);
        $this->assertEquals($supplier->id, $found['supplier_id']);
        $this->assertNotNull($found['supplier']);
        $this->assertEquals('Torzo Textil', $found['supplier']['name']);
    }
}


<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandCrudAndFiltersRulesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de creación, consulta, actualización, toggle-active, archivo, restauración y borrado de marcas.
     */
    public function test_complete_brand_crud_lifecycle_via_api(): void
    {
        // 1. Crear un proveedor
        $supplier = Supplier::create([
            'name' => 'Textiles MX',
            'legal_name' => 'Textiles Especializados de México SA de CV',
            'vendor_code' => 'SUP-TXM-01',
            'status' => 'connected',
            'origin' => 'manual',
        ]);

        // 2. Dar de alta una nueva marca comercial vinculada al proveedor
        $createPayload = [
            'name' => 'Aura Lingerie',
            'slug' => 'aura-lingerie',
            'supplier_id' => $supplier->id,
            'description' => 'Lencería de encaje y diseño sensual de alta costura.',
            'logo_url' => 'https://example.com/aura-logo.png',
            'website' => 'https://auralingerie.example.com',
            'is_active' => true,
        ];

        $postRes = $this->postJson('/api/v1/inventory/brands', $createPayload);
        $postRes->assertStatus(201);
        $postRes->assertJsonPath('status', 'success');
        $postRes->assertJsonPath('data.name', 'Aura Lingerie');
        $postRes->assertJsonPath('data.slug', 'aura-lingerie');
        $postRes->assertJsonPath('data.supplier_id', $supplier->id);

        $brandId = $postRes->json('data.id');
        $this->assertDatabaseHas('brands', [
            'id' => $brandId,
            'name' => 'Aura Lingerie',
            'supplier_id' => $supplier->id,
            'is_active' => true,
            'is_archived' => false,
        ]);

        // Verificar que el proveedor sincronizó su array de marcas
        $supplier->refresh();
        $this->assertContains('Aura Lingerie', $supplier->brands);

        // 3. Listar marcas maestras
        $listRes = $this->getJson('/api/v1/inventory/brands');
        $listRes->assertStatus(200);
        $listRes->assertJsonPath('status', 'success');
        $this->assertNotEmpty($listRes->json('data'));

        // 4. Ver detalle de marca
        $showRes = $this->getJson("/api/v1/inventory/brands/{$brandId}");
        $showRes->assertStatus(200);
        $showRes->assertJsonPath('data.name', 'Aura Lingerie');
        $showRes->assertJsonPath('data.supplier.tradeName', 'Textiles MX');

        // 5. Actualizar marca
        $updateRes = $this->putJson("/api/v1/inventory/brands/{$brandId}", [
            'name' => 'Aura Lingerie Premium',
            'description' => 'Descripción actualizada de alta gama',
        ]);
        $updateRes->assertStatus(200);
        $updateRes->assertJsonPath('data.name', 'Aura Lingerie Premium');

        // 6. Alternar estado activo
        $toggleRes = $this->patchJson("/api/v1/inventory/brands/{$brandId}/toggle-active");
        $toggleRes->assertStatus(200);
        $toggleRes->assertJsonPath('data.is_active', false);

        // 7. Archivar marca
        $archiveRes = $this->postJson("/api/v1/inventory/brands/{$brandId}/archive");
        $archiveRes->assertStatus(200);
        $archiveRes->assertJsonPath('data.is_archived', true);
        $archiveRes->assertJsonPath('data.is_active', false);

        // 8. Restaurar marca
        $restoreRes = $this->postJson("/api/v1/inventory/brands/{$brandId}/restore");
        $restoreRes->assertStatus(200);
        $restoreRes->assertJsonPath('data.is_archived', false);
        $restoreRes->assertJsonPath('data.is_active', true);

        // 9. Reasignar / Asociar a otro proveedor o desvincular
        $assignRes = $this->postJson("/api/v1/inventory/brands/{$brandId}/assign-supplier", [
            'supplier_id' => null,
        ]);
        $assignRes->assertStatus(200);
        $assignRes->assertJsonPath('data.supplier_id', null);

        // 10. Eliminar marca
        $deleteRes = $this->deleteJson("/api/v1/inventory/brands/{$brandId}");
        $deleteRes->assertStatus(200);
        $this->assertSoftDeleted('brands', ['id' => $brandId]);
    }

    /**
     * Test de relación en árbol proveedor -> marcas y deducción de getBrandsTree.
     */
    public function test_supplier_and_brand_tree_relationship(): void
    {
        $supplier = Supplier::create([
            'name' => 'Corsetería León',
            'legal_name' => 'Fábrica de Corsetería de León SA de CV',
            'vendor_code' => 'SUP-CLN-02',
            'origin' => 'manual',
            'status' => 'connected',
            'brands' => ['Leonisa Basic'],
        ]);

        $brand1 = Brand::create([
            'name' => 'Leonisa Gold',
            'slug' => 'leonisa-gold',
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ]);

        $tree = $supplier->getBrandsTree();
        $brandNames = array_column($tree, 'name');

        $this->assertContains('Leonisa Basic', $brandNames);
        $this->assertContains('Leonisa Gold', $brandNames);
    }

    /**
     * Test de reactividad de Ficha Fiscal (z-index 120, openProviderFiscalModal),
     * pestaña de Marcas Comerciales, selector brandFilter y modal de marcas en Hub 4.
     */
    public function test_hub_4_inventory_blade_contains_brands_crud_and_fiscal_reactivity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('portal.inventory'));
        $response->assertStatus(200);

        // Reactividad y z-index de Ficha Fiscal
        $response->assertSee('z-[120]', false);
        $response->assertSee('style="z-index: 120 !important;', false);
        $response->assertSee('openProviderFiscalModal(prov)', false);

        // Pestaña y vista maestra de Marcas Comerciales
        $response->assertSee("activeSettingsTab = 'brands'", false);
        $response->assertSee("activeSettingsTab === 'brands'", false);
        $response->assertSee('Gestor de Marcas Comerciales', false);
        $response->assertSee('+ Nueva Marca Comercial', false);
        $response->assertSee('brandModalOpen', false);

        // Filtro de Marca en la barra superior de inventario
        $response->assertSee('x-model="brandFilter"', false);
        $response->assertSee('availableBrandNames', false);

        // Botones en árbol de proveedores
        $response->assertSee('+ Desprender Nueva Marca', false);
        $response->assertSee('openEditBrandByName', false);
        $response->assertSee('removeBrandFromSupplier', false);

        // Gobernanza de marcas dentro del modal de proveedor (Pestaña 3)
        $response->assertSee('Gobernanza de Marcas Comerciales Desprendidas', false);
        $response->assertSee('Crear &amp; Vincular', false);
        $response->assertSee('getDetailedBrandsForCurrentProvider()', false);
        $response->assertSee('toggleActiveBrandFromProvider', false);
        $response->assertSee('toggleArchiveBrandFromProvider', false);
        $response->assertSee('deleteOrDetachBrandFromProvider', false);
    }

    /**
     * Test de Storefront y Hub 2 con árbol de proveedores y marcas.
     */
    public function test_storefront_and_hub_2_contain_brands_tree_and_supplier_filters(): void
    {
        // 1. Hub 2 Tienda & CMS
        $admin = User::factory()->create(['role' => 'admin']);
        $hub2Res = $this->actingAs($admin)->get(route('portal.store'));
        $hub2Res->assertStatus(200);
        $hub2Res->assertSee('query_rules[brand]', false);
        $hub2Res->assertSee('query_rules[supplier]', false);

        // Validar ruta específica reportada por usuario con tab=pages&universe=marte
        $hub2PagesRes = $this->actingAs($admin)->get('/admin/tienda-cms?tab=pages&universe=marte');
        $hub2PagesRes->assertStatus(200);

        // 2. Storefront Catálogo con árbol de marcas y proveedores
        $storefrontRes = $this->get(route('store.products'));
        $storefrontRes->assertStatus(200);
        $storefrontRes->assertSee('Marcas & Proveedores (Árbol)', false);
        $storefrontRes->assertSee('data-filter-group="supplier"', false);
        $storefrontRes->assertSee('data-filter-group="brand"', false);
    }

    /**
     * Test de separación estricta: Las líneas de producto / tipos de prenda (TANGA, BIKINI, JOCKSTRAPS, etc.)
     * NUNCA deben permitirse como marcas comerciales ni deducirse en getBrandsTree.
     */
    public function test_product_line_categories_cannot_be_commercial_brands_and_are_excluded_from_tree(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Intentar crear una marca llamada 'TANGA' o 'JOCKSTRAPS' debe ser rechazado con 422
        $resTanga = $this->actingAs($admin)->postJson('/api/v1/inventory/brands', [
            'name' => 'TANGA',
        ]);
        $resTanga->assertStatus(422);
        $resTanga->assertJsonFragment([
            'status' => 'error',
        ]);

        $resJock = $this->actingAs($admin)->postJson('/api/v1/inventory/brands', [
            'name' => 'JOCKSTRAPS',
        ]);
        $resJock->assertStatus(422);

        // 2. getBrandsTree en un proveedor que tenga productos con categorías en 'brand' o en su array brands
        $supplier = Supplier::create([
            'name' => 'Torzo Premium',
            'legal_name' => 'Torzo S.A.P.I.',
            'vendor_code' => 'SUP-TRZ-99',
            'origin' => 'scraping',
            'status' => 'connected',
            'brands' => ['Torzo', 'TANGA', 'BIKINI', 'JOCKSTRAPS'],
        ]);

        $tree = $supplier->getBrandsTree();
        $brandNames = array_column($tree, 'name');

        $this->assertContains('Torzo', $brandNames);
        $this->assertNotContains('TANGA', $brandNames);
        $this->assertNotContains('BIKINI', $brandNames);
        $this->assertNotContains('JOCKSTRAPS', $brandNames);
        $this->assertTrue(Supplier::isProductLineCategory('tanga'));
        $this->assertTrue(Supplier::isProductLineCategory('Brief/Boxers'));
        $this->assertFalse(Supplier::isProductLineCategory('Torzo'));
    }

    /**
     * Regla 1: Toda marca de producto se debe asociar a un proveedor obligatoriamente.
     */
    public function test_mandatory_supplier_id_rule_1_on_brand_creation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Sin supplier_id debe fallar 422
        $resNoSup = $this->actingAs($admin)->postJson('/api/v1/inventory/brands', [
            'name' => 'Marca Huérfana',
        ]);
        $resNoSup->assertStatus(422);
        $resNoSup->assertJsonValidationErrors(['supplier_id']);

        // 2. Con supplier_id inexistente debe fallar 422
        $resInvalidSup = $this->actingAs($admin)->postJson('/api/v1/inventory/brands', [
            'name' => 'Marca Huérfana',
            'supplier_id' => 999999,
        ]);
        $resInvalidSup->assertStatus(422);
        $resInvalidSup->assertJsonValidationErrors(['supplier_id']);

        // 3. Con supplier_id válido se asocia y actualiza el árbol de marcas del proveedor
        $supplier = Supplier::create([
            'name' => 'Torzo Sexywear Direct',
            'legal_name' => 'Torzo Sexywear SA de CV',
            'vendor_code' => 'SUP-TRZ-RULE1',
            'origin' => 'manual',
            'status' => 'connected',
            'brands' => [],
        ]);

        $resValid = $this->actingAs($admin)->postJson('/api/v1/inventory/brands', [
            'name' => 'Mazho Underwear',
            'supplier_id' => $supplier->id,
        ]);
        $resValid->assertStatus(201);
        $resValid->assertJsonPath('data.supplier_id', $supplier->id);

        $supplier->refresh();
        $this->assertContains('Mazho Underwear', $supplier->brands);
    }

    /**
     * Regla 2: Soporte de fotos manuales con variante de color en workstation y curaduría.
     */
    public function test_images_with_color_variant_assignment_rule_2(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $supplier = Supplier::create([
            'name' => 'Proveedor Moda',
            'legal_name' => 'Moda SA',
            'vendor_code' => 'SUP-MODA-01',
            'origin' => 'manual',
            'status' => 'connected',
        ]);

        // Workstation alta de producto con imágenes y colores asignados
        $payload = [
            'name' => 'Bikini Microfibra Escarlata',
            'sku' => 'BIK-ESCARLATA-01',
            'pvp' => 299.00,
            'cost' => 120.00,
            'stock' => 15,
            'brand' => 'Moda Sexy',
            'supplier_id' => $supplier->id,
            'images' => [
                [
                    'url' => 'https://example.com/rojo-front.jpg',
                    'color_name' => 'Rojo Pasión',
                    'is_primary' => true,
                ],
                [
                    'url' => 'https://example.com/negro-back.jpg',
                    'color_name' => 'Negro',
                    'is_primary' => false,
                ],
            ],
        ];

        $res = $this->actingAs($admin)->postJson('/api/v1/inventory/products', $payload);
        $res->assertStatus(201);
        $res->assertJsonPath('status', 'success');

        $productId = $res->json('data.id');
        $product = \App\Models\Product::find($productId);
        $this->assertNotNull($product);
        $this->assertEquals('https://example.com/rojo-front.jpg', $product->meta['image'] ?? null);
        $this->assertIsArray($product->meta);
        $this->assertNotEmpty($product->meta['gallery'] ?? []);
        $this->assertEquals('Rojo Pasión', $product->meta['gallery'][0]['color_name'] ?? null);
        $this->assertEquals('Negro', $product->meta['gallery'][1]['color_name'] ?? null);
    }

    /**
     * Regla 3: Crear categoría y subcategoría in situ y reflejarlo en el árbol.
     */
    public function test_quick_category_and_subcategory_creation_rule_3(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Crear Categoría Padre
        $parentRes = $this->actingAs($admin)->postJson('/api/v1/inventory/settings/categories', [
            'name' => 'Ropa Interior Sensual',
            'description' => 'Categoría creada in situ desde Workstation',
        ]);
        $parentRes->assertStatus(201);
        $parentId = $parentRes->json('data.id');

        // Crear Subcategoría vinculada a la padre
        $subRes = $this->actingAs($admin)->postJson('/api/v1/inventory/settings/categories', [
            'name' => 'Harness y Correas',
            'parent_id' => $parentId,
            'description' => 'Subcategoría creada in situ desde Curaduría',
        ]);
        $subRes->assertStatus(201);
        $subId = $subRes->json('data.id');

        $this->assertDatabaseHas('categories', [
            'id' => $subId,
            'name' => 'Harness y Correas',
            'parent_id' => $parentId,
        ]);
    }
}


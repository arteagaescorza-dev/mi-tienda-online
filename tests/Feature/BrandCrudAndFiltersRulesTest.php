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
}

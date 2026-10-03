<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ScrapedPendingItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrapedLifecycleAndArchivedProductsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_test@mitienda.test'],
            [
                'name' => 'Admin Tester',
                'password' => bcrypt('password123'),
                'role' => 'admin',
            ]
        );
    }

    /**
     * Regla 2: Los productos scrapeados en curaduría (pending) NO deben mostrarse en el catálogo operativo.
     * Solo los productos aprobados, activos y no archivados se muestran en el catálogo operativo.
     */
    public function test_operational_catalog_excludes_curation_items_and_archived_products()
    {
        $supplier = Supplier::create([
            'name' => 'Torzo B2B México',
            'contact_name' => 'Agente Torzo',
            'email' => 'torzo@test.com',
        ]);

        // Ítem en curaduría pendiente (no aprobado)
        $pendingItem = ScrapedPendingItem::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'TOR-RAW-999',
            'external_sku' => 'TOR-RAW-999',
            'external_title' => 'Suspensorio Torzo En Curaduría',
            'suggested_category' => 'Suspensorios',
            'scraped_cost' => 120.00,
            'curated_price' => 299.00,
            'supplier_stock' => 50,
            'curation_status' => 'pending',
        ]);

        // Producto activo en catálogo operativo
        $activeProduct = Product::create([
            'sku' => 'PRP-TEST-001',
            'title' => 'Boxer Activo Operativo',
            'name' => 'Boxer Activo Operativo',
            'slug' => 'boxer-activo-operativo',
            'price' => 250.00,
            'cost' => 80.00,
            'is_archived' => false,
            'visible' => true,
            'type' => 'physical',
        ]);

        // Producto archivado
        $archivedProduct = Product::create([
            'sku' => 'ARC-TEST-002',
            'title' => 'Tanga Archivada Fuera de Catálogo',
            'name' => 'Tanga Archivada Fuera de Catálogo',
            'slug' => 'tanga-archivada-fuera-catalogo',
            'price' => 180.00,
            'cost' => 60.00,
            'is_archived' => true,
            'visible' => false,
            'discontinued_reason' => 'Retirado por proveedor en scraping',
            'type' => 'physical',
        ]);

        // El catálogo operativo consulta productos con is_archived = false y visible = true
        $catalogProducts = Product::where('is_archived', false)->where('visible', true)->get();

        $this->assertTrue($catalogProducts->contains('id', $activeProduct->id));
        $this->assertFalse($catalogProducts->contains('id', $archivedProduct->id));
        
        // Verifica que el ítem pendiente en staging no existe en la tabla de productos
        $this->assertNull(Product::where('sku', 'TOR-RAW-999')->first());
    }

    /**
     * Regla 3: La tabla de proveedores y scrapers permite ajustar el tiempo que se lanzará la actualización del escrapeo.
     */
    public function test_supplier_scrape_frequency_can_be_updated_via_api()
    {
        $supplier = Supplier::create([
            'name' => 'Proveedor Frecuencia Test',
            'contact_name' => 'Contacto',
            'email' => 'frecuencia@test.com',
            'scrape_frequency' => 'every_6_hours',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->patchJson("/api/v1/inventory/suppliers/{$supplier->id}/frequency", [
                'frequency' => 'every_30_minutes',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'frequency' => 'every_30_minutes',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'scrape_frequency' => 'every_30_minutes',
        ]);
    }

    /**
     * Regla 4: Modal de discrepancia B2B por producto descontinuado en origen.
     * Opción A: Retirar del catálogo (Ocultar vitrina).
     */
    public function test_resolve_discontinued_product_action_hide()
    {
        $product = Product::create([
            'sku' => 'DISC-HIDE-001',
            'title' => 'Producto Para Ocultar',
            'name' => 'Producto Para Ocultar',
            'slug' => 'producto-para-ocultar',
            'price' => 300.00,
            'visible' => true,
            'is_archived' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/products/{$product->id}/resolve-discontinued", [
                'action' => 'hide',
                'reason' => 'Proveedor reporta pausa temporal',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'hide',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'visible' => false,
        ]);
    }

    /**
     * Regla 4: Modal de discrepancia B2B por producto descontinuado en origen.
     * Opción B: Archivar producto.
     */
    public function test_resolve_discontinued_product_action_archive()
    {
        $product = Product::create([
            'sku' => 'DISC-ARC-002',
            'title' => 'Producto Para Archivar',
            'name' => 'Producto Para Archivar',
            'slug' => 'producto-para-archivar',
            'price' => 320.00,
            'visible' => true,
            'is_archived' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/products/{$product->id}/resolve-discontinued", [
                'action' => 'archive',
                'reason' => 'Descatalogado por mayorista Torzo en escrapeo',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'archive',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_archived' => true,
            'visible' => false,
            'discontinued_reason' => 'Descatalogado por mayorista Torzo en escrapeo',
        ]);
    }

    /**
     * Regla 4: Modal de discrepancia B2B por producto descontinuado en origen.
     * Opción C: Eliminar producto definitivamente.
     */
    public function test_resolve_discontinued_product_action_delete()
    {
        $product = Product::create([
            'sku' => 'DISC-DEL-003',
            'title' => 'Producto Para Eliminar',
            'name' => 'Producto Para Eliminar',
            'slug' => 'producto-para-eliminar',
            'price' => 350.00,
            'visible' => true,
            'is_archived' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/products/{$product->id}/resolve-discontinued", [
                'action' => 'delete',
                'reason' => 'Prenda retirada y no se volverá a fabricar',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'delete',
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Regla 5: Herramienta de productos archivados y descontinuados (Reactivar y Eliminar).
     */
    public function test_archived_products_tool_list_reactivate_and_destroy()
    {
        $archivedProduct = Product::create([
            'sku' => 'TOOL-ARC-001',
            'title' => 'Prenda Archivada en Herramienta',
            'name' => 'Prenda Archivada en Herramienta',
            'slug' => 'prenda-archivada-herramienta',
            'price' => 280.00,
            'cost' => 90.00,
            'visible' => false,
            'is_archived' => true,
            'discontinued_reason' => 'Retirado temporalmente en scraping',
        ]);

        // 1. Consulta el listado de archivados
        $listResponse = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/inventory/archived-products');

        $listResponse->assertStatus(200);
        $listResponse->assertJsonFragment([
            'sku' => 'TOOL-ARC-001',
            'isArchived' => true,
        ]);

        // 2. Reactiva el producto para que vuelva al catálogo operativo
        $reactivateResponse = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/products/{$archivedProduct->id}/reactivate");

        $reactivateResponse->assertStatus(200);
        $reactivateResponse->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $archivedProduct->id,
            'is_archived' => 0,
            'visible' => 1,
            'discontinued_reason' => null,
        ]);

        // 3. Eliminar producto
        $deleteResponse = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/inventory/products/{$archivedProduct->id}");

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $archivedProduct->id,
        ]);
    }

    /**
     * Regla 1: El catálogo operativo contiene la acción de archivar producto y lo envía a la herramienta Archivados y descontinuados.
     */
    public function test_operational_catalog_archive_action_sends_product_to_archived_tool()
    {
        $product = Product::create([
            'sku' => 'OP-ARCH-001',
            'title' => 'Prenda Operativa Para Archivar',
            'slug' => 'prenda-operativa-para-archivar',
            'brand' => 'ModaIntima',
            'price' => 350.00,
            'visible' => 1,
            'is_archived' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/products/{$product->id}/resolve-discontinued", [
                'action' => 'archive',
                'reason' => 'Archivado manualmente desde el Catálogo Operativo (Regla 1)',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'action' => 'archive',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_archived' => 1,
            'visible' => 0,
            'discontinued_reason' => 'Archivado manualmente desde el Catálogo Operativo (Regla 1)',
        ]);
    }

    /**
     * Regla 5: El workstation de alta permite pre-guardar en borrador; no aparece en el catálogo operativo hasta que es aprobado.
     */
    public function test_workstation_pre_save_draft_and_approval_flow()
    {
        // 1. Pre-guardar producto como borrador (approval_status = 'draft')
        $draftPayload = [
            'sku' => 'DFT-TEST-001',
            'name' => 'Bóxer Modal Pre-guardado Borrador',
            'category' => 'Boxers',
            'subcategory' => 'Trunk',
            'brand' => 'ModaIntima Textil MX',
            'fabric_type' => 'Modal Seamless',
            'color_name' => 'Negro Profundo',
            'color_hex' => '#0f172a',
            'color_type' => 'solid',
            'pvp' => 380.00,
            'cost' => 140.00,
            'approval_status' => 'draft',
            'sizes' => ['ch' => 5, 'm' => 10, 'g' => 5],
        ];

        $createResponse = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/inventory/products', $draftPayload);

        $createResponse->assertStatus(201);
        $createResponse->assertJson([
            'status' => 'success',
            'data' => [
                'approval_status' => 'draft',
            ],
        ]);

        $createdId = $createResponse->json('data.id');
        $this->assertNotNull($createdId);

        // El borrador debe estar en la BD con visible = false y approval_status = draft
        $this->assertDatabaseHas('products', [
            'id' => $createdId,
            'sku' => 'DFT-TEST-001',
            'visible' => 0,
        ]);

        // 2. Verificar que en la vista del portal /admin/inventario-catalogo está en portalDraftProducts y NO en portalProducts
        $portalResponse = $this->actingAs($this->adminUser)
            ->get('/admin/inventario-catalogo');

        $portalResponse->assertStatus(200);
        $portalResponse->assertViewHas('portalDraftProducts', function ($drafts) use ($createdId) {
            return collect($drafts)->contains('id', $createdId);
        });
        $portalResponse->assertViewHas('portalProducts', function ($products) use ($createdId) {
            return !collect($products)->contains('id', $createdId);
        });

        // 3. Aprobar oficialmente el producto borrador (Regla 5)
        $approveResponse = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/products/{$createdId}/approve");

        $approveResponse->assertStatus(200);
        $approveResponse->assertJson([
            'status' => 'success',
            'data' => [
                'approval_status' => 'approved',
                'visible' => true,
            ],
        ]);

        // Ahora el producto debe tener visible = 1
        $this->assertDatabaseHas('products', [
            'id' => $createdId,
            'sku' => 'DFT-TEST-001',
            'visible' => 1,
        ]);

        // 4. Verificar que ahora SÍ aparece en portalProducts y NO en portalDraftProducts
        $portalAfterResponse = $this->actingAs($this->adminUser)
            ->get('/admin/inventario-catalogo');

        $portalAfterResponse->assertStatus(200);
        $portalAfterResponse->assertViewHas('portalProducts', function ($products) use ($createdId) {
            return collect($products)->contains('id', $createdId);
        });
        $portalAfterResponse->assertViewHas('portalDraftProducts', function ($drafts) use ($createdId) {
            return !collect($drafts)->contains('id', $createdId);
        });
    }
}

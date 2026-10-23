<?php

namespace Tests\Feature;

use App\Models\ApparelFabric;
use App\Models\CmsSection;
use App\Models\Product;
use App\Models\ScrapedPendingItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrapedPendingItemsCurationTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_curation@mitienda.test'],
            [
                'name' => 'Admin Curation Tester',
                'password' => bcrypt('password123'),
                'role' => 'admin',
            ]
        );
    }

    /**
     * Verifica que la bandeja de curaduría de scraping carga los registros reales y no mocks estáticos.
     */
    public function test_scraped_pending_items_are_loaded_in_inventory_view_without_hardcoded_mocks()
    {
        $supplier = Supplier::create([
            'name' => 'Torzo B2B México',
            'contact_name' => 'Proveedor Torzo',
            'email' => 'ventas@torzo.test',
        ]);

        $item1 = ScrapedPendingItem::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'EXT-REAL-101',
            'external_sku' => 'TOR-REAL-101',
            'external_title' => 'Tanga Microfibra Torzo Real',
            'suggested_category' => 'Tanga',
            'suggested_subcategory' => 'Minimalista',
            'suggested_fabric' => 'Microfibra Spandex / Licra',
            'detected_color_name' => 'Rojo Pasión',
            'detected_color_hex' => '#dc2626',
            'color_type' => 'solid',
            'external_price' => 110.00,
            'scraped_cost' => 110.00,
            'curated_price' => 249.00,
            'supplier_stock' => 85,
            'curation_status' => 'pending',
            'origin_url' => 'https://torzo.kexe.mx/productos/tanga-real-101',
            'images' => ['https://torzo.kexe.mx/imagenes_productos/real101.jpg'],
            'sizes' => ['ch' => 20, 'm' => 30, 'g' => 25, 'xl' => 10],
        ]);

        $item2 = ScrapedPendingItem::create([
            'supplier_id' => $supplier->id,
            'external_id' => 'EXT-REAL-102',
            'external_sku' => 'TOR-REAL-102',
            'external_title' => 'Jockstrap Deportivo Torzo Real',
            'suggested_category' => 'Jockstraps',
            'suggested_subcategory' => 'Athletic',
            'suggested_fabric' => 'Algodón & Modal Suave',
            'detected_color_name' => 'Azul Marino',
            'detected_color_hex' => '#1e3a8a',
            'color_type' => 'solid',
            'external_price' => 135.00,
            'scraped_cost' => 135.00,
            'curated_price' => 299.00,
            'supplier_stock' => 40,
            'curation_status' => 'pending',
            'origin_url' => 'https://torzo.kexe.mx/productos/jock-real-102',
            'images' => ['https://torzo.kexe.mx/imagenes_productos/real102.jpg'],
            'sizes' => ['ch' => 10, 'm' => 15, 'g' => 10, 'xl' => 5],
        ]);

        $response = $this->actingAs($this->adminUser)->get('/admin/inventario-catalogo');

        $response->assertStatus(200);

        // Validar que la variable portalScrapedPendingItems contiene los ítems reales
        $response->assertViewHas('portalScrapedPendingItems', function ($items) use ($item1, $item2) {
            $ids = collect($items)->pluck('id')->all();
            return in_array($item1->id, $ids) && in_array($item2->id, $ids);
        });

        // Validar que los mocks viejos erradicados no estén en el contenido
        $response->assertDontSee('TOR-RAW-019');
        $response->assertDontSee('Holographic Prism Festival Brief');
        $response->assertDontSee('Bamboo Comfort Trunks');
    }

    /**
     * Verifica que el endpoint de curaduría aprueba la prenda e inserta el producto con tela y sección.
     */
    public function test_curate_scraped_item_approval_creates_product_with_fabric_and_section()
    {
        $fabric = ApparelFabric::firstOrCreate(
            ['name' => 'Microfibra Spandex / Licra'],
            [
                'slug' => 'microfibra-spandex-licra',
                'description' => 'Fibra de alta elasticidad y secado rápido',
                'is_active' => true,
            ]
        );

        $section = CmsSection::firstOrCreate(
            ['name' => 'verano_fresh'],
            [
                'title' => 'Verano Fresh',
                'type' => 'curated_products',
                'status' => 'active',
            ]
        );

        $pendingItem = ScrapedPendingItem::create([
            'external_id' => 'TOR-EXT-999',
            'external_sku' => 'TOR-APPR-001',
            'external_title' => 'Boxer Torzo Seamless Active',
            'suggested_category' => 'Boxers',
            'suggested_fabric' => 'Microfibra Spandex / Licra',
            'detected_color_name' => 'Negro Profundo',
            'detected_color_hex' => '#0f172a',
            'external_price' => 125.00,
            'scraped_cost' => 125.00,
            'supplier_stock' => 60,
            'curation_status' => 'pending',
            'images' => ['https://torzo.kexe.mx/imagenes_productos/boxer_appr1.webp'],
        ]);

        $payload = [
            'action' => 'approve',
            'curated_title' => 'Boxer Curado Torzo Premium',
            'curated_brand' => 'Torzo Oficial',
            'curated_category' => 'Boxers',
            'curated_fabric' => 'Microfibra Spandex / Licra',
            'curated_color' => 'Negro Profundo',
            'curated_design_type' => 'sin_diseno',
            'curated_website_section' => 'verano_fresh',
            'curated_price' => 349.00,
            'curated_images' => ['https://torzo.kexe.mx/imagenes_productos/boxer_appr1.webp'],
            'image_color_map' => [
                'https://torzo.kexe.mx/imagenes_productos/boxer_appr1.webp' => 'Negro Profundo',
            ],
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/scraping/pending/{$pendingItem->id}/curate", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'approved',
            ]);

        // Verificar actualización en la tabla de pending items
        $this->assertDatabaseHas('scraped_pending_items', [
            'id' => $pendingItem->id,
            'curation_status' => 'approved',
            'curated_title' => 'Boxer Curado Torzo Premium',
            'curated_price' => 349.00,
        ]);

        // Verificar creación del producto en catálogo maestro
        $this->assertDatabaseHas('products', [
            'sku' => 'TOR-APPR-001',
            'title' => 'Boxer Curado Torzo Premium',
            'brand' => 'Torzo Oficial',
            'fabric' => 'Microfibra Spandex / Licra',
            'fabric_id' => $fabric->id,
            'section_name' => 'verano_fresh',
            'type' => 'dropship',
            'price' => 349.00,
        ]);

        $product = Product::where('sku', 'TOR-APPR-001')->first();
        $this->assertNotNull($product);
        $this->assertEquals('verano_fresh', $product->section_name);
        $this->assertEquals($fabric->id, $product->fabric_id);
        $this->assertTrue($product->isScraped());
        $this->assertEquals(0, data_get($product->meta, 'local_stock'));
        $this->assertEquals(0, $product->variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('quantity')));
    }

    /**
     * Política Oficial: Todos los productos scrapeados / dropship muestran 0 de stock local
     * tanto en Hub 4 (admin) como en Hub 2 / Storefront (PDP y vitrinas).
     */
    public function test_scraped_and_dropship_products_strictly_display_zero_local_stock_in_hub4_and_storefront()
    {
        $supplier = Supplier::create([
            'name' => 'Torzo Dropship Supplier',
            'type' => 'dropship_scrape',
            'contact_name' => 'Torzo Contact',
            'email' => 'dropship@torzo.test',
        ]);

        $product = Product::create([
            'sku' => 'TOR-DROP-001',
            'title' => 'Suspensorio Dropship Scrapeado',
            'slug' => 'suspensorio-dropship-scrapeado',
            'brand' => 'Torzo',
            'type' => 'dropship',
            'price' => 299.00,
            'cost' => 120.00,
            'visible' => true,
            'is_archived' => false,
            'meta' => [
                'is_scraped' => true,
                'provider' => 'Torzo',
                'category' => 'Jockstraps',
                'supplier_stock' => 50,
                'sizes' => ['ch' => 10, 'm' => 20, 'g' => 15],
            ],
        ]);

        // Sincronizar variantes y tallas
        $syncService = app(\App\Services\Catalog\ProductCatalogSyncService::class);
        $syncService->syncProduct($product, $product->meta);

        // 1. En modelo y BD: isScraped es true y las existencias físicas locales son 0
        $this->assertTrue($product->isScraped());
        $product->refresh();
        $this->assertEquals(0, $product->variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('quantity')));
        $this->assertEquals(0, data_get($product->meta, 'local_stock'));
        $this->assertEquals(0, data_get($product->meta, 'physical_stock'));

        // 2. En Hub 4 (Gestor de Inventario en Portal)
        $portalResponse = $this->actingAs($this->adminUser)->get('/admin/inventario-catalogo');
        $portalResponse->assertStatus(200);
        $portalResponse->assertViewHas('portalProducts', function ($products) use ($product) {
            $item = collect($products)->firstWhere('id', $product->id);
            if (!$item) return false;

            return $item['localStock'] === 0
                && $item['physicalStock'] === 0
                && $item['committedStock'] === 0
                && $item['type'] === 'dropship'
                && $item['supplierType'] === 'dropship'
                && $item['isScraped'] === true
                && $item['is_scraped'] === true
                && $item['supplierStock'] === 50
                && $item['status'] === 'Sincronizado';
        });

        // 3. En Storefront PWA (Servicio de Detalle de Producto PDP)
        $catalogService = app(\App\Services\Storefront\StorefrontCatalogService::class);
        $detailPayload = $catalogService->getProductDetailPayload($product->slug);

        $this->assertNotNull($detailPayload);
        $this->assertTrue($detailPayload['is_scraped']);
        $this->assertEquals(0, $detailPayload['total_physical_stock']);
        $this->assertEquals(0, $detailPayload['total_available_stock']);
        $this->assertEquals('on_demand', $detailPayload['stock_type']);
        $this->assertEquals('Bajo Pedido', $detailPayload['stock_label']);

        // Todas las variantes de la ficha de producto se marcan como Bajo Pedido
        foreach ($detailPayload['variants'] as $variant) {
            $this->assertEquals(0, $variant['stock_physical']);
            $this->assertEquals(0, $variant['stock_available']);
            $this->assertEquals('Bajo Pedido', $variant['stock_badge']);
        }
    }

    /**
     * Politica Oficial: Los productos scrapeados y dropshipping no participan del ROP (Reorder Point).
     * No generan alertas de reabastecimiento en Hub 4 ni participan en filtros de bajo ROP.
     */
    public function test_scraped_and_dropship_products_strictly_do_not_participate_in_rop()
    {
        $supplier = Supplier::create([
            'name' => 'Torzo ROP Dropship Supplier',
            'type' => 'dropship_scrape',
            'contact_name' => 'Torzo Contact',
            'email' => 'rop_dropship@torzo.test',
        ]);

        $product = Product::create([
            'sku' => 'PRV-ROP-TEST-001',
            'title' => 'Prenda Scrapeada No ROP',
            'slug' => 'prenda-scrapeada-no-rop',
            'brand' => 'Torzo',
            'type' => 'dropship',
            'price' => 350.00,
            'cost' => 150.00,
            'visible' => true,
            'is_archived' => false,
            'meta' => [
                'is_scraped' => true,
                'provider' => 'Torzo',
                'category' => 'Underwear',
                'supplier_stock' => 50,
                'sizes' => ['ch' => 5, 'm' => 10],
            ],
        ]);

        $syncService = app(\App\Services\Catalog\ProductCatalogSyncService::class);
        $syncService->syncProduct($product, $product->meta);

        $portalResponse = $this->actingAs($this->adminUser)->get('/admin/inventario-catalogo');
        $portalResponse->assertStatus(200);
        $portalResponse->assertViewHas('portalProducts', function ($products) use ($product) {
            $item = collect($products)->firstWhere('id', $product->id);
            if (!$item) return false;

            return $item['isScraped'] === true
                && $item['is_scraped'] === true
                && $item['supplierType'] === 'dropship'
                && $item['type'] === 'dropship'
                && $item['localStock'] === 0;
        });

        // Aseguramos que la vista contenga la logica reactiva que excluye items scrapeados de ROP
        $content = $portalResponse->getContent();
        $this->assertStringContainsString('isDropshipOrScraped(item)', $content);
        $this->assertStringContainsString('On-Demand', $content);
    }

    /**
     * Verifica que el descarte de una prenda scrapeada actualiza el estado a rejected con su motivo.
     */
    public function test_curate_scraped_item_rejection_marks_as_rejected_with_reason()
    {
        $pendingItem = ScrapedPendingItem::create([
            'external_id' => 'TOR-EXT-DISCARD',
            'external_sku' => 'TOR-DISC-001',
            'external_title' => 'Prenda Defectuosa o Sin Stock',
            'curation_status' => 'pending',
            'external_price' => 80.00,
        ]);

        $payload = [
            'action' => 'reject',
            'rejection_reason' => 'Modelo descontinuado por el proveedor Torzo',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/inventory/scraping/pending/{$pendingItem->id}/curate", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'rejected',
            ]);

        $this->assertDatabaseHas('scraped_pending_items', [
            'id' => $pendingItem->id,
            'curation_status' => 'rejected',
            'rejection_reason' => 'Modelo descontinuado por el proveedor Torzo',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\SupplierSyncRun;
use App\Models\SupplierColorMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupplierSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_can_sync_supplier_catalog_and_dropship_stock(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        $response = $this->actingAs($user)->postJson(
            "/admin/proveedores/{$supplier->id}/sincronizar",
            [
                'products' => [[
                    'supplier_sku' => 'SUP-001',
                    'product_id' => $product->id,
                    'supplier_url' => 'https://supplier.test/products/1',
                    'price' => 49.90,
                    'availability' => 'in_stock',
                    'raw_snapshot' => ['stock' => 12],
                ]],
            ]
        );

        $response->assertCreated()->assertJson(['synced' => 1]);
        $this->assertDatabaseHas('supplier_products', [
            'supplier_id' => $supplier->id,
            'supplier_sku' => 'SUP-001',
            'availability' => 'in_stock',
        ]);
        $this->assertDatabaseHas('inventories', [
            'variant_id' => $variant->id,
            'location' => 'supplier-' . $supplier->id,
            'type' => 'dropship',
            'quantity' => 12,
        ]);
    }

    public function test_customer_cannot_sync_supplier_catalog(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $supplier = Supplier::factory()->create();

        $this->actingAs($user)
            ->postJson("/admin/proveedores/{$supplier->id}/sincronizar", ['products' => []])
            ->assertForbidden();
    }

    public function test_supplier_scrape_command_normalizes_and_persists_torzo_catalog(): void
    {
        $supplier = Supplier::factory()->create([
            'type' => 'dropship_scrape',
            'website' => 'https://torzo.kexe.mx/',
        ]);
        Http::fake([
            'https://torzo.kexe.mx/api/productos/catalogo' => Http::response([
                'productos' => [[
                    'sku' => 'TORZO-001',
                    'titulo' => 'Biker Negro',
                    'precio' => '199.90',
                    'item1' => 'M',
                    'stockItem1' => 8,
                ]],
            ]),
        ]);

        $this->artisan('supplier:scrape', ['supplier_id' => $supplier->id])
            ->assertExitCode(0);

        $this->assertDatabaseHas('supplier_products', [
            'supplier_id' => $supplier->id,
            'supplier_sku' => 'TORZO-001',
            'availability' => 'in_stock',
            'price' => 199.90,
        ]);
        $this->assertDatabaseHas('supplier_sync_runs', [
            'supplier_id' => $supplier->id,
            'status' => 'success',
            'synced_count' => 1,
        ]);
        Http::assertSentCount(1);
    }

    public function test_sync_maps_supplier_variations_to_matching_local_variants(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $small = ProductVariant::factory()->for($product)->create(['attributes' => ['size' => 'S']]);
        $large = ProductVariant::factory()->for($product)->create(['attributes' => ['size' => 'L']]);

        app(\App\Services\SupplierCatalogSyncService::class)->sync($supplier, [[
            'supplier_sku' => 'SUP-SIZES',
            'product_id' => $product->id,
            'availability' => 'in_stock',
            'raw_snapshot' => [
                'stock' => 7,
                'variations' => [
                    ['size' => 'S', 'stock' => 2],
                    ['size' => 'L', 'stock' => 5],
                ],
            ],
        ]]);

        $this->assertDatabaseHas('inventories', [
            'variant_id' => $small->id,
            'type' => 'dropship',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('inventories', [
            'variant_id' => $large->id,
            'type' => 'dropship',
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('supplier_variant_mappings', [
            'product_variant_id' => $small->id,
            'supplier_variant_key' => 'S-default',
            'stock' => 2,
        ]);
        $this->assertDatabaseHas('supplier_variant_mappings', [
            'product_variant_id' => $large->id,
            'supplier_variant_key' => 'L-default',
            'stock' => 5,
        ]);
        $this->actingAs($user)
            ->get('/admin/inventario-catalogo')
            ->assertOk()
            ->assertSee('S-default')
            ->assertSee($product->title);
    }

    public function test_sync_skips_variant_when_supplier_color_is_missing_and_local_colors_are_ambiguous(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create(['attributes' => ['size' => 'M', 'color' => 'negro']]);
        ProductVariant::factory()->for($product)->create(['attributes' => ['size' => 'M', 'color' => 'blanco']]);

        app(\App\Services\SupplierCatalogSyncService::class)->sync($supplier, [[
            'supplier_sku' => 'SUP-AMBIGUOUS',
            'product_id' => $product->id,
            'availability' => 'in_stock',
            'raw_snapshot' => ['stock' => 4, 'variations' => [['size' => 'M', 'stock' => 4]]],
        ]]);

        $this->assertDatabaseCount('supplier_variant_mappings', 0);
        $this->assertDatabaseCount('inventories', 0);
    }

    public function test_portal_can_configure_color_and_sync_uses_the_local_name(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['attributes' => ['size' => 'M', 'color' => 'negro']]);

        $this->actingAs($user)
            ->postJson("/admin/proveedores/{$supplier->id}/colores", [
                'external_color' => 'Black',
                'local_color' => 'negro',
            ])
            ->assertCreated();

        app(\App\Services\SupplierCatalogSyncService::class)->sync($supplier, [[
            'supplier_sku' => 'SUP-COLOR',
            'product_id' => $product->id,
            'availability' => 'in_stock',
            'raw_snapshot' => ['stock' => 3, 'variations' => [['size' => 'M', 'color' => 'Black', 'stock' => 3]]],
        ]]);

        $this->assertDatabaseHas('supplier_color_mappings', [
            'supplier_id' => $supplier->id,
            'external_color' => 'Black',
            'local_color' => 'negro',
        ]);
        $this->assertDatabaseHas('supplier_variant_mappings', [
            'product_variant_id' => $variant->id,
            'stock' => 3,
        ]);
    }

    public function test_portal_can_view_and_manage_supplier_color_rules(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $supplier = Supplier::factory()->create(['name' => 'Torzo']);
        SupplierColorMapping::create([
            'supplier_id' => $supplier->id,
            'external_color' => 'Black',
            'local_color' => 'negro',
        ]);

        $this->actingAs($user)
            ->get('/admin/proveedores/colores')
            ->assertOk()
            ->assertSee('Torzo')
            ->assertSee('Black')
            ->assertSee('negro');

        $this->actingAs($user)
            ->post('/admin/proveedores/' . $supplier->id . '/colores', [
                'external_color' => 'White',
                'local_color' => 'blanco',
            ])
            ->assertRedirect('/admin/proveedores/colores');

        $this->assertDatabaseHas('supplier_color_mappings', [
            'supplier_id' => $supplier->id,
            'external_color' => 'White',
            'local_color' => 'blanco',
        ]);
    }

    public function test_supplier_scrape_command_records_http_failure(): void
    {
        $supplier = Supplier::factory()->create([
            'type' => 'dropship_scrape',
            'website' => 'https://torzo.kexe.mx/',
        ]);
        Http::fake([
            'https://torzo.kexe.mx/api/productos/catalogo' => Http::response([], 503),
        ]);

        $this->artisan('supplier:scrape', ['supplier_id' => $supplier->id])
            ->assertExitCode(0);

        $this->assertDatabaseHas('supplier_sync_runs', [
            'supplier_id' => $supplier->id,
            'status' => 'failed',
            'error' => 'Torzo respondio HTTP 503.',
        ]);
    }

    public function test_portal_inventory_displays_sync_history(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $supplier = Supplier::factory()->create(['name' => 'Proveedor Historial']);
        SupplierSyncRun::create([
            'supplier_id' => $supplier->id,
            'status' => 'failed',
            'synced_count' => 0,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'error' => 'HTTP 503',
        ]);

        $this->actingAs($user)
            ->get('/admin/inventario-catalogo')
            ->assertOk()
            ->assertSee('Proveedor Historial')
            ->assertSee('Failed')
            ->assertSee('HTTP 503');
    }

    public function test_portal_inventory_filters_sync_history(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $firstSupplier = Supplier::factory()->create(['name' => 'Proveedor Visible']);
        $secondSupplier = Supplier::factory()->create(['name' => 'Proveedor Oculto']);
        SupplierSyncRun::create([
            'supplier_id' => $firstSupplier->id,
            'status' => 'success',
            'started_at' => now(),
        ]);
        SupplierSyncRun::create([
            'supplier_id' => $secondSupplier->id,
            'status' => 'failed',
            'started_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/admin/inventario-catalogo?supplier_id=' . $firstSupplier->id . '&status=success')
            ->assertOk()
            ->assertSee('Proveedor Visible')
            ->assertDontSee('HTTP 503');
    }

    public function test_prune_command_removes_only_old_sync_runs(): void
    {
        $supplier = Supplier::factory()->create();
        SupplierSyncRun::create([
            'supplier_id' => $supplier->id,
            'status' => 'success',
            'started_at' => now()->subDays(100),
        ]);
        $recent = SupplierSyncRun::create([
            'supplier_id' => $supplier->id,
            'status' => 'success',
            'started_at' => now()->subDays(10),
        ]);

        $this->artisan('supplier:prune-runs', ['--days' => 90])->assertExitCode(0);

        $this->assertDatabaseCount('supplier_sync_runs', 1);
        $this->assertDatabaseHas('supplier_sync_runs', ['id' => $recent->id]);
    }
}
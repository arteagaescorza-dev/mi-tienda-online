<?php

namespace Tests\Feature;

use App\Models\ApparelColor;
use App\Models\ApparelFabric;
use App\Models\ApparelSize;
use App\Models\ApparelSizeCurve;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Catalog\ProductCatalogSyncService;
use App\Services\Storefront\StorefrontCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Hub4ToStorefrontSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin@multiverse.test',
        ]);
    }

    public function test_product_update_in_hub4_syncs_categories_to_pivot_and_meta_and_storefront(): void
    {
        $product = Product::create([
            'sku' => 'SKU-SYNC-01',
            'title' => 'Bikini Tricolor Mexico',
            'slug' => 'bikini-tricolor-mexico-sku-sync-01',
            'brand' => 'Torzo Sexywear',
            'price' => 299.00,
            'cost' => 120.00,
            'type' => 'dropship',
            'visible' => true,
            'meta' => [
                'category' => 'General',
                'color_name' => 'Tricolor',
            ],
        ]);

        $response = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/products/{$product->sku}/update-complete", [
            'name' => 'Bikini Tricolor Mexico',
            'category' => 'Bikinis',
            'subcategory' => 'Banderas',
            'brand' => 'Torzo Sexywear',
            'pvp' => 299.00,
            'cost' => 120.00,
            'color_name' => 'Tricolor',
            'color_hex' => '#15803d',
            'sizes' => [
                'ch' => 0,
                'm' => 0,
                'g' => 0,
                'xl' => 0,
                '2xl' => 0,
                '3xl' => 0,
            ],
        ]);

        $response->assertOk();

        $product->refresh();

        // 1. Verificar relación en pivot category_product
        $categoryNames = $product->categories()->pluck('name')->toArray();
        $this->assertContains('Bikinis', $categoryNames);
        $this->assertContains('Banderas', $categoryNames);

        // 2. Verificar meta sincronizado
        $this->assertEquals('Bikinis', data_get($product->meta, 'category'));
        $this->assertEquals('Banderas', data_get($product->meta, 'subcategory'));

        // 3. Verificar que StorefrontController refleja la categoría y colores en el catálogo
        $storefrontController = app(\App\Http\Controllers\StorefrontController::class);
        $pwaProducts = $storefrontController->getPwaStoreProducts();
        $card = collect($pwaProducts)->firstWhere('sku', 'SKU-SYNC-01');

        $this->assertNotNull($card);
        $this->assertEquals('bikinis', $card['category']);
        $this->assertEquals('Bikinis', $card['categoryName']);
    }

    public function test_product_with_custom_color_registers_in_apparel_colors_and_renders_gradient(): void
    {
        $product = Product::create([
            'sku' => 'SKU-SYNC-COLOR',
            'title' => 'Bikini Patriota',
            'slug' => 'bikini-patriota-sku-sync-color',
            'brand' => 'Torzo Sexywear',
            'price' => 250.00,
            'cost' => 100.00,
            'type' => 'dropship',
            'visible' => true,
            'meta' => [
                'category' => 'Bikinis',
            ],
        ]);

        $response = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/products/{$product->sku}/update-complete", [
            'name' => 'Bikini Patriota',
            'category' => 'Bikinis',
            'pvp' => 250.00,
            'cost' => 100.00,
            'color_name' => 'Tricolor',
            'color_hex' => '#15803d',
            'sizes' => ['ch' => 0, 'm' => 0],
        ]);

        $response->assertOk();

        // Verificar que ApparelColor existe y tiene el degradado tricolor
        $apparelColor = ApparelColor::where('name', 'Tricolor')->first();
        $this->assertNotNull($apparelColor);
        $this->assertEquals('gradient', $apparelColor->color_type);
        $this->assertStringContainsString('linear-gradient', $apparelColor->gradient_css);
        $this->assertStringContainsString('#15803d', $apparelColor->gradient_css);
        $this->assertStringContainsString('#b91c1c', $apparelColor->gradient_css);

        // Verificar tarjeta en Storefront
        $storefrontController = app(\App\Http\Controllers\StorefrontController::class);
        $pwaProducts = $storefrontController->getPwaStoreProducts();
        $card = collect($pwaProducts)->firstWhere('sku', 'SKU-SYNC-COLOR');

        $this->assertNotNull($card);
        $this->assertNotEmpty($card['colors']);
        $colorItem = $card['colors'][0];
        $this->assertEquals('Tricolor', $colorItem['name']);
        $this->assertEquals('gradient', $colorItem['type']);
        $this->assertStringContainsString('linear-gradient', $colorItem['hex']);
    }

    public function test_product_without_explicit_sizes_resolves_curve_sizes_never_defaults_to_uni(): void
    {
        // 1. Crear curva de tallas para la marca Torzo Sexywear
        $curve = ApparelSizeCurve::create([
            'name' => 'Curva Torzo Sexywear',
            'slug' => 'curva-torzo-sexywear',
            'category_scope' => 'textil',
            'is_default' => false,
            'is_active' => true,
        ]);

        foreach (['CH', 'M', 'G', 'XL', '2XL', '3XL'] as $idx => $code) {
            ApparelSize::create([
                'size_curve_id' => $curve->id,
                'code' => $code,
                'label' => "Talla {$code}",
                'sort_order' => $idx + 1,
                'is_active' => true,
            ]);
        }

        // 2. Crear producto sin tallas previas
        $product = Product::create([
            'sku' => 'SKU00294-TEST',
            'title' => 'Bikini Mexico Especial',
            'slug' => 'bikini-mexico-especial',
            'brand' => 'Torzo Sexywear',
            'price' => 280.00,
            'cost' => 110.00,
            'type' => 'dropship',
            'visible' => true,
            'meta' => [
                'category' => 'Bikinis',
                'color_name' => 'Tricolor',
                'sizes' => null,
            ],
        ]);

        $syncService = app(ProductCatalogSyncService::class);
        $syncService->syncProduct($product);

        $product->refresh();

        // 3. Verificar que se hayan creado variantes para cada talla de la curva
        $variantSizes = $product->variants->map(fn($v) => data_get($v->attributes, 'size'))->toArray();
        $this->assertContains('CH', $variantSizes);
        $this->assertContains('M', $variantSizes);
        $this->assertContains('G', $variantSizes);
        $this->assertContains('XL', $variantSizes);
        $this->assertContains('2XL', $variantSizes);
        $this->assertContains('3XL', $variantSizes);
        $this->assertNotContains('UNI', $variantSizes);

        // 4. Verificar Storefront Catalog
        $storefrontController = app(\App\Http\Controllers\StorefrontController::class);
        $pwaProducts = $storefrontController->getPwaStoreProducts();
        $card = collect($pwaProducts)->firstWhere('sku', 'SKU00294-TEST');

        $this->assertNotNull($card);
        $this->assertContains('CH', $card['sizes']);
        $this->assertContains('M', $card['sizes']);
        $this->assertNotContains('UNI', $card['sizes']);

        // 5. Verificar PDP
        $pdpService = app(StorefrontCatalogService::class);
        $pdp = $pdpService->getProductDetailPayload('SKU00294-TEST');
        $this->assertNotNull($pdp);
        $this->assertContains('CH', $pdp['sizes']);
        $this->assertContains('M', $pdp['sizes']);
        $this->assertNotContains('UNI', $pdp['sizes']);
    }

    public function test_category_update_in_settings_propagates_to_associated_products(): void
    {
        $category = Category::create([
            'name' => 'Bikinis Antiguos',
            'slug' => 'bikinis-antiguos',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-CAT-PROP',
            'title' => 'Bikini Retro',
            'slug' => 'bikini-retro',
            'brand' => 'Torzo Sexywear',
            'price' => 200.00,
            'cost' => 80.00,
            'type' => 'dropship',
            'visible' => true,
            'meta' => [
                'category' => 'Bikinis Antiguos',
            ],
        ]);
        $product->categories()->attach($category->id);

        // Actualizar el nombre de la categoría en configuración
        $response = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/settings/categories/{$category->id}", [
            'name' => 'Bikinis Modernos',
            'is_active' => true,
        ]);

        $response->assertOk();

        $product->refresh();
        $this->assertEquals('Bikinis Modernos', data_get($product->meta, 'category'));
    }

    public function test_color_update_in_settings_propagates_to_associated_products(): void
    {
        $color = ApparelColor::create([
            'name' => 'Naranja Neón',
            'hex_code' => '#ff5500',
            'color_type' => 'solid',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-COLOR-PROP',
            'title' => 'Tanga Neón',
            'slug' => 'tanga-neon',
            'brand' => 'Mazho',
            'price' => 180.00,
            'cost' => 60.00,
            'type' => 'dropship',
            'visible' => true,
            'meta' => [
                'color_name' => 'Naranja Neón',
                'color_hex' => '#ff5500',
            ],
        ]);

        // Actualizar el hex del color en configuración
        $response = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/settings/colors/{$color->id}", [
            'name' => 'Naranja Neón Intenso',
            'hex' => '#ff7700',
            'color_type' => 'solid',
            'is_active' => true,
        ]);

        $response->assertOk();

        $product->refresh();
        $this->assertEquals('Naranja Neón Intenso', data_get($product->meta, 'color_name'));
        $this->assertEquals('#ff7700', data_get($product->meta, 'color_hex'));
    }

    public function test_price_inventory_stock_description_and_fabric_syncs_to_variants_and_storefront(): void
    {
        $product = Product::create([
            'sku' => 'SKU-FULL-SYNC',
            'title' => 'Bóxer Ergonómico Active',
            'slug' => 'boxer-ergonomico-active',
            'brand' => 'Torzo Sexywear',
            'price' => 199.00,
            'cost' => 80.00,
            'type' => 'physical',
            'visible' => true,
            'description' => 'Descripción inicial genérica.',
            'fabric' => 'Algodón Peinado 100%',
            'meta' => [
                'category' => 'Boxers',
                'fabric' => 'Algodón Peinado 100%',
                'sizes' => ['ch' => 2, 'm' => 4],
            ],
        ]);

        // Crear variantes iniciales
        $variantCh = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-FULL-SYNC-CH',
            'price' => 199.00,
            'cost' => 80.00,
            'attributes' => ['size' => 'CH'],
        ]);
        \App\Models\Inventory::create([
            'variant_id' => $variantCh->id,
            'location' => 'warehouse-1',
            'quantity' => 2,
            'reserved' => 0,
            'type' => 'physical',
        ]);

        // Actualizar producto desde Hub 4 con nuevo precio, costo, descripción, tela y cantidades
        $response = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/products/{$product->sku}/update-complete", [
            'name' => 'Bóxer Ergonómico Active Pro',
            'category' => 'Boxers',
            'brand' => 'Torzo Sexywear',
            'pvp' => 349.00,
            'cost' => 135.00,
            'discount_pct' => 10,
            'description' => 'Prenda deportiva de alto rendimiento confeccionada en microfibra transpirable con copa ergonómica.',
            'fabric_type' => 'Microfibra Spandex 88/12',
            'sizes' => [
                'ch' => 7,
                'm' => 12,
            ],
        ]);

        $response->assertOk();

        $product->refresh();

        // 1. Verificar persistencia de precio, costo, tela y descripción en Product
        $this->assertEquals(349.00, (float) $product->price);
        $this->assertEquals(135.00, (float) $product->cost);
        $this->assertEquals('Microfibra Spandex 88/12', $product->fabric);
        $this->assertStringContainsString('microfibra transpirable', $product->description);

        // 2. Verificar sincronización en variantes (precio y costo sincronizados)
        $variantCh->refresh();
        $this->assertEquals(349.00, (float) $variantCh->price);
        $this->assertEquals(135.00, (float) $variantCh->cost);

        // 3. Verificar inventarios actualizados (7 en CH, 12 en M = 19 total)
        $chInv = \App\Models\Inventory::where('variant_id', $variantCh->id)->first();
        $this->assertEquals(7, (int) $chInv->quantity);

        $variantM = ProductVariant::where('product_id', $product->id)->where('sku', 'SKU-FULL-SYNC-M')->first();
        $this->assertNotNull($variantM);
        $this->assertEquals(349.00, (float) $variantM->price);
        $mInv = \App\Models\Inventory::where('variant_id', $variantM->id)->first();
        $this->assertEquals(12, (int) $mInv->quantity);

        // 4. Verificar Storefront Catalog
        $storefrontController = app(\App\Http\Controllers\StorefrontController::class);
        $pwaProducts = $storefrontController->getPwaStoreProducts();
        $card = collect($pwaProducts)->firstWhere('sku', 'SKU-FULL-SYNC');

        $this->assertNotNull($card);
        $this->assertEquals(349.00, (float) $card['price']);
        $this->assertEquals(19, (int) $card['availableStock']);
        $this->assertEquals('Microfibra Spandex 88/12', $card['fabric_type']);
        $this->assertStringContainsString('microfibra transpirable', $card['description']);

        // 5. Verificar PDP
        $pdpService = app(StorefrontCatalogService::class);
        $pdp = $pdpService->getProductDetailPayload('SKU-FULL-SYNC');
        $this->assertNotNull($pdp);
        $this->assertEquals(349.00, (float) $pdp['price']);
        $this->assertEquals(19, (int) $pdp['total_available_stock']);
        $this->assertEquals('Microfibra Spandex 88/12', $pdp['fabric']);
        $this->assertStringContainsString('Microfibra Spandex 88/12', $pdp['fabric_composition']);
        $this->assertStringContainsString('microfibra transpirable', $pdp['description']);
    }

    public function test_fabric_and_brand_update_in_settings_propagates_to_all_linked_products(): void
    {
        $supplier = Supplier::create([
            'name' => 'Fabricante Textil MX',
            'trade_name' => 'Fabricante Textil',
            'contact_name' => 'Juan Perez',
            'email' => 'taller@textil.mx',
            'phone' => '5551234567',
            'status' => 'active',
        ]);

        $brand = Brand::create([
            'name' => 'Torzo Clásico',
            'slug' => 'torzo-clasico',
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ]);

        $fabric = ApparelFabric::create([
            'name' => 'Modal Seda',
            'slug' => 'modal-seda',
            'description' => '90% Modal Seda / 10% Elastano',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-PROP-FAB-BRD',
            'title' => 'Bikini Seda',
            'slug' => 'bikini-seda',
            'brand' => 'Torzo Clásico',
            'fabric' => 'Modal Seda',
            'fabric_id' => $fabric->id,
            'price' => 250.00,
            'cost' => 100.00,
            'type' => 'dropship',
            'visible' => true,
            'meta' => [
                'brand' => 'Torzo Clásico',
                'fabric' => 'Modal Seda',
                'fabric_type' => 'Modal Seda',
            ],
        ]);

        // 1. Actualizar tela en configuración
        $resFabric = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/settings/fabrics/{$fabric->id}", [
            'name' => 'Modal Seda Satinado',
            'description' => '95% Modal Seda Satinado / 5% Spandex Ultra Soft',
            'is_active' => true,
        ]);
        $resFabric->assertOk();

        // 2. Actualizar marca en configuración
        $resBrand = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/settings/brands/{$brand->id}", [
            'name' => 'Torzo Black Label',
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ]);
        $resBrand->assertOk();

        // 3. Verificar que el producto reflejó ambos cambios automáticamente
        $product->refresh();
        $this->assertEquals('Modal Seda Satinado', $product->fabric);
        $this->assertEquals('Modal Seda Satinado', data_get($product->meta, 'fabric'));
        $this->assertEquals('Torzo Black Label', $product->brand);
        $this->assertEquals('Torzo Black Label', data_get($product->meta, 'brand'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\CmsSection;
use App\Models\CmsTrackingSetting;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class Hub2CmsTrackingAndHub4SyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin@tienda.test',
        ]);
    }

    public function test_tracking_diagnostics_endpoint_validates_formats_and_returns_clean_response(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('portal.store.tracking.test'), [
            'gtm_id' => 'GTM-ABC1234',
            'ga4_measurement_id' => 'G-ABC1234567',
            'facebook_pixel_id' => '123456789012345',
            'facebook_capi_token' => 'EAABbCcDd123456TestToken',
            'facebook_test_event_code' => 'TEST12345',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'all_passed',
            'diagnostics' => [
                'gtm' => ['name', 'status', 'message'],
                'ga4' => ['name', 'status', 'message'],
                'pixel' => ['name', 'status', 'message'],
                'capi' => ['name', 'status', 'message'],
            ],
            'timestamp',
        ]);

        $json = $response->json();
        $this->assertEquals('success', $json['diagnostics']['gtm']['status']);
        $this->assertEquals('success', $json['diagnostics']['ga4']['status']);
        $this->assertEquals('success', $json['diagnostics']['pixel']['status']);
    }

    public function test_tracking_diagnostics_detects_malformed_ids(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('portal.store.tracking.test'), [
            'gtm_id' => 'INVALID_GTM',
            'ga4_measurement_id' => 'UA-12345-1',
            'facebook_pixel_id' => 'NOT_A_NUMBER',
        ]);

        $response->assertOk();
        $json = $response->json();
        $this->assertFalse($json['all_passed']);
        $this->assertEquals('warning', $json['diagnostics']['gtm']['status']);
        $this->assertEquals('warning', $json['diagnostics']['ga4']['status']);
        $this->assertEquals('warning', $json['diagnostics']['pixel']['status']);
    }

    public function test_product_update_in_hub4_invalidates_hub2_storefront_caches(): void
    {
        Cache::put('cms_storefront_marte', ['cached_title' => 'Marte Antiguo'], 3600);
        Cache::put('cms_storefront_venus', ['cached_title' => 'Venus Antiguo'], 3600);
        $this->assertTrue(Cache::has('cms_storefront_marte'));

        $product = Product::create([
            'sku' => 'SKU-OBSERVER-01',
            'title' => 'Jockstrap Dynamic Sync',
            'slug' => 'jockstrap-dynamic-sync',
            'brand' => 'JOCKSTRAPS',
            'price' => 350.00,
            'cost' => 150.00,
            'visible' => true,
        ]);

        // La creación debe invalidar la caché de vitrinas
        $this->assertFalse(Cache::has('cms_storefront_marte'));
        $this->assertFalse(Cache::has('cms_storefront_venus'));

        // Volvemos a popular caché
        Cache::put('cms_storefront_marte', ['cached_title' => 'Marte Segunda Vez'], 3600);
        $this->assertTrue(Cache::has('cms_storefront_marte'));

        // La actualización de precio debe invalidar la caché inmediatamente
        $product->update(['price' => 399.00]);
        $this->assertFalse(Cache::has('cms_storefront_marte'));
    }

    public function test_variant_update_and_inventory_change_invalidates_hub2_storefront_caches(): void
    {
        $product = Product::create([
            'sku' => 'SKU-VARIANT-SYNC',
            'title' => 'Brief Modal Anatómico',
            'slug' => 'brief-modal-anatomico',
            'brand' => 'BRIEF/BOXERS',
            'price' => 280.00,
            'cost' => 100.00,
            'visible' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-VARIANT-SYNC-M',
            'price' => 280.00,
            'attributes' => ['size' => 'M', 'color' => 'Negro'],
        ]);

        $inventory = Inventory::create([
            'variant_id' => $variant->id,
            'quantity' => 15,
            'reserved' => 0,
        ]);

        Cache::put('cms_storefront_marte', ['cached' => true], 3600);
        $this->assertTrue(Cache::has('cms_storefront_marte'));

        // Cambio de precio en la variante
        $variant->update(['price' => 310.00]);
        $this->assertFalse(Cache::has('cms_storefront_marte'));

        // Modificación de inventario en rampa / rma
        Cache::put('cms_storefront_marte', ['cached' => true], 3600);
        $this->assertTrue(Cache::has('cms_storefront_marte'));

        $inventory->update(['quantity' => 10, 'reserved' => 2]);
        $this->assertFalse(Cache::has('cms_storefront_marte'));
    }

    public function test_creating_or_updating_cms_section_from_hub4_invalidates_storefront_cache(): void
    {
        Cache::put('cms_storefront_marte', ['cached' => true], 3600);
        $this->assertTrue(Cache::has('cms_storefront_marte'));

        $res = $this->actingAs($this->adminUser)->postJson('/api/v1/inventory/settings/store-sections', [
            'name' => 'Textiles Deportivos 2026',
            'title' => 'TEXTILES DEPORTIVOS 2026',
            'universe' => 'marte',
            'type' => 'catalog_section',
            'badge' => 'E-Commerce',
            'is_active' => true,
        ]);

        $res->assertCreated();
        $this->assertFalse(Cache::has('cms_storefront_marte'));
    }
}


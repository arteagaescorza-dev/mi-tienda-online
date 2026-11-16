<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Storage\MediaStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CloudMigrationAndMcpTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_storage_service_stores_and_deletes_on_public_disk_in_local_mode(): void
    {
        config(['filesystems.default' => 'local']);
        $service = app(MediaStorageService::class);

        $file = UploadedFile::fake()->image('banner.jpg', 200, 200);
        $stored = $service->storeUploadedFile($file, 'uploads/test_media');

        $this->assertSame('public', $stored['disk']);
        $this->assertStringStartsWith('/storage/uploads/test_media/', $stored['url']);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($stored['path']));

        $service->deleteByUrl($stored['url']);
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists($stored['path']));
    }

    public function test_mcp_endpoint_requires_valid_bearer_token_when_unauthenticated(): void
    {
        $response = $this->postJson('/api/v1/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.code', -32001);
    }

    public function test_mcp_server_enforces_zero_stock_and_rop_exclusion_for_scraped_products(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $physicalProduct = Product::create([
            'sku' => 'PHY-001',
            'title' => 'Brief Físico Algodón',
            'slug' => 'brief-fisico-algodon',
            'brand' => 'TEST',
            'type' => 'physical',
            'price' => 450,
            'cost' => 150,
            'visible' => true,
            'is_archived' => false,
        ]);
        $phyVariant = ProductVariant::create([
            'product_id' => $physicalProduct->id,
            'sku' => 'PHY-001-M',
            'price' => 450,
            'cost' => 150,
            'visible' => true,
        ]);
        Inventory::create([
            'variant_id' => $phyVariant->id,
            'location' => 'warehouse-1',
            'quantity' => 2,
            'reserved' => 0,
            'type' => 'physical',
            'min_threshold' => 5,
        ]);

        $scrapedProduct = Product::create([
            'sku' => 'DRP-001',
            'title' => 'Arnes Torzo Bajo Pedido',
            'slug' => 'arnes-torzo-bajo-pedido',
            'brand' => 'TORZO',
            'type' => 'dropship',
            'price' => 890,
            'cost' => 300,
            'visible' => true,
            'is_archived' => false,
            'meta' => ['is_scraped' => true],
        ]);
        $drpVariant = ProductVariant::create([
            'product_id' => $scrapedProduct->id,
            'sku' => 'DRP-001-U',
            'price' => 890,
            'cost' => 300,
            'visible' => true,
        ]);
        Inventory::create([
            'variant_id' => $drpVariant->id,
            'location' => 'warehouse-1',
            'quantity' => 0,
            'reserved' => 0,
            'type' => 'physical',
            'min_threshold' => 5,
        ]);

        // 1. tools/list como superadmin incluye finance_get_margin_summary
        $listRes = $this->actingAs($superadmin)->postJson('/api/v1/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ]);
        $listRes->assertOk();
        $toolNames = collect($listRes->json('result.tools'))->pluck('name')->all();
        $this->assertContains('finance_get_margin_summary', $toolNames);

        // 2. storefront_search_products retorna local_stock = 0 para scraped
        $searchRes = $this->actingAs($superadmin)->postJson('/api/v1/mcp', [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/call',
            'params' => [
                'name' => 'storefront_search_products',
                'arguments' => ['query' => 'Torzo'],
            ],
        ]);
        $searchRes->assertOk()
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.items.0.local_stock', 0)
            ->assertJsonPath('result.structuredContent.items.0.fulfillment_mode', 'on_demand');

        // 3. inventory_get_rop_alerts solo retorna el producto físico, jamás el scrapeado
        $ropRes = $this->actingAs($superadmin)->postJson('/api/v1/mcp', [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/call',
            'params' => [
                'name' => 'inventory_get_rop_alerts',
                'arguments' => ['limit' => 20],
            ],
        ]);
        $ropRes->assertOk()
            ->assertJsonPath('result.isError', false)
            ->assertJsonPath('result.structuredContent.count', 1)
            ->assertJsonPath('result.structuredContent.alerts.0.sku', 'PHY-001-M');
    }

    public function test_internal_cron_endpoint_validates_secret_and_whitelisted_commands(): void
    {
        putenv('CRON_SECRET=test-cron-secret-123');
        $_ENV['CRON_SECRET'] = 'test-cron-secret-123';

        // Sin secreto -> 401
        $this->postJson('/api/internal/cron/run', [
            'command' => 'stock:cleanup-expired-locks',
        ])->assertStatus(401);

        // Comando no permitido -> 422
        $this->withHeaders(['X-Cron-Secret' => 'test-cron-secret-123'])
            ->postJson('/api/internal/cron/run', [
                'command' => 'migrate:fresh',
            ])->assertStatus(422);

        // Comando válido con secreto válido -> 200
        $this->withHeaders(['X-Cron-Secret' => 'test-cron-secret-123'])
            ->postJson('/api/internal/cron/run', [
                'command' => 'stock:cleanup-expired-locks',
            ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('command', 'stock:cleanup-expired-locks');
    }
}

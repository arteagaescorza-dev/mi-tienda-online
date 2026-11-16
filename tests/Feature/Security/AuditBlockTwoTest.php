<?php

namespace Tests\Feature\Security;

use App\Models\Product;
use App\Models\User;
use App\Services\Mcp\McpServerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditBlockTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_aud05_dashboard_shift_logs_and_copilot_status_require_authorization(): void
    {
        $this->getJson('/api/v1/dashboard/shift-logs')->assertStatus(403);
        $this->getJson('/api/v1/dashboard/copilot/status')->assertStatus(403);

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->getJson('/api/v1/dashboard/shift-logs')->assertStatus(403);
        $this->actingAs($customer)->getJson('/api/v1/dashboard/copilot/status')->assertStatus(403);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->getJson('/api/v1/dashboard/shift-logs')->assertStatus(200);
    }

    public function test_aud07_mcp_portal_tools_fail_closed_without_actor_and_handle_string_fabric(): void
    {
        $mcp = app(McpServerService::class);

        $res = $mcp->callTool('inventory_get_rop_alerts', [], null);
        $this->assertTrue($res['isError']);

        Product::create([
            'sku' => 'SKU-FABRIC-STR',
            'title' => 'Playera Algodon',
            'slug' => 'playera-algodon',
            'price' => 499,
            'fabric' => 'Algodon Peinado',
            'type' => 'physical',
            'visible' => true,
            'is_archived' => false,
        ]);

        $search = $mcp->callTool('storefront_search_products', ['query' => 'Playera'], null);
        $this->assertFalse($search['isError']);
        $this->assertSame('Algodon Peinado', $search['structuredContent']['items'][0]['fabric']);
    }
}

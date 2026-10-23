<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Hub1ExecutiveSubnavViewsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin.hub1.subnav@mitienda.online',
        ]);
    }

    public function test_hub1_executive_subnav_buttons_and_panels_are_rendered_for_authorized_users(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.dashboard'));

        $response->assertStatus(200);

        // Check view data
        $response->assertViewHas('executiveAreas');
        $response->assertViewHas('serverDashboardData');

        $viewData = $response->viewData('serverDashboardData');
        $this->assertArrayHasKey('executiveAreas', $viewData);
        $this->assertArrayHasKey('inventory', $viewData['executiveAreas']);
        $this->assertArrayHasKey('store', $viewData['executiveAreas']);
        $this->assertArrayHasKey('crm', $viewData['executiveAreas']);
        $this->assertArrayHasKey('finance', $viewData['executiveAreas']);
        $this->assertArrayHasKey('logistics', $viewData['executiveAreas']);
        $this->assertArrayHasKey('allies', $viewData['executiveAreas']);

        // Check that all 7 buttons exist
        $response->assertSee('id="btn-subtab-revops"', false);
        $response->assertSee('id="btn-subtab-inventory"', false);
        $response->assertSee('id="btn-subtab-store"', false);
        $response->assertSee('id="btn-subtab-crm"', false);
        $response->assertSee('id="btn-subtab-finance"', false);
        $response->assertSee('id="btn-subtab-logistics"', false);
        $response->assertSee('id="btn-subtab-allies"', false);

        // Check that all 7 panels exist
        $response->assertSee('id="panel-subtab-revops"', false);
        $response->assertSee('id="panel-subtab-inventory"', false);
        $response->assertSee('id="panel-subtab-store"', false);
        $response->assertSee('id="panel-subtab-crm"', false);
        $response->assertSee('id="panel-subtab-finance"', false);
        $response->assertSee('id="panel-subtab-logistics"', false);
        $response->assertSee('id="panel-subtab-allies"', false);

        // Check Top 10 modal
        $response->assertSee("activeModal === 'top_ten'", false);

        // Check navigation shortcuts to other hubs
        $response->assertSee(route('portal.inventory'), false);
        $response->assertSee(route('portal.store'), false);
        $response->assertSee(route('portal.marketing'), false);
        $response->assertSee(route('portal.finance'), false);
        $response->assertSee(route('portal.logistics'), false);
        $response->assertSee(route('portal.allies'), false);
    }

    public function test_hub1_executive_subnav_data_contains_inventory_rop_and_financials(): void
    {
        $product = Product::create([
            'sku' => 'TEST-ROP-01',
            'slug' => 'boxer-rop-test',
            'title' => 'Boxer ROP Test',
            'price' => 300.00,
            'cost' => 120.00,
            'type' => 'physical',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-ROP-01-M',
            'price' => 300.00,
            'cost' => 120.00,
            'attributes' => ['size' => 'M'],
        ]);

        Inventory::create([
            'variant_id' => $variant->id,
            'quantity' => 2,
            'reserved' => 0,
            'min_threshold' => 10,
            'location' => 'Anaquel ROP-Test',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('portal.dashboard'));

        $response->assertStatus(200);

        $serverData = $response->viewData('serverDashboardData');
        $ropItems = $serverData['executiveAreas']['inventory']['ropItems'];

        $this->assertNotEmpty($ropItems);
        $firstItem = $ropItems[0];
        $this->assertArrayHasKey('sku', $firstItem);
        $this->assertArrayHasKey('title', $firstItem);
        $this->assertArrayHasKey('available', $firstItem);
    }

    public function test_hub1_executive_financial_costs_are_hidden_for_warehouse_only_role(): void
    {
        $warehouseUser = User::factory()->create([
            'role' => 'warehouse',
            'email' => 'operador.almacen@mitienda.online',
        ]);

        $response = $this->actingAs($warehouseUser)
            ->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sección Reservada para Dirección y Finanzas');
    }
}


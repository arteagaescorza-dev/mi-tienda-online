<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\CustomerSubscription;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderFinancialMargin;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\User;
use App\Models\VendorBill;
use Database\Seeders\SimulationOperationalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalSimulationAndPurgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulation_operational_seeder_populates_realistic_orders_and_kpis(): void
    {
        $this->seed(SimulationOperationalSeeder::class);

        // 1. Verify orders created across timeline
        $this->assertGreaterThanOrEqual(15, Order::count());
        $this->assertGreaterThan(0, Order::whereDate('created_at', today())->count());

        // 2. Verify products and inventories with ROP breach
        $this->assertGreaterThanOrEqual(4, Product::count());
        $this->assertGreaterThanOrEqual(4, ProductVariant::count());

        $ropBreaches = Inventory::whereRaw('(quantity - reserved) <= min_threshold')->count();
        $this->assertGreaterThanOrEqual(4, $ropBreaches);

        // 3. Verify financial margins and MRR
        $this->assertGreaterThan(0, OrderFinancialMargin::count());
        $this->assertGreaterThan(0, CustomerSubscription::where('status', 'active')->count());

        // 4. Verify vendor bills and allies
        $this->assertGreaterThanOrEqual(3, VendorBill::count());
        $this->assertGreaterThanOrEqual(3, Ally::where('status', 'active')->count());
        $this->assertGreaterThan(0, Shipment::count());
    }

    public function test_purge_command_surgically_removes_transactions_while_preserving_catalog_and_staff(): void
    {
        // 1. Seed master staff users
        $admin = User::create([
            'name' => 'Admin Mickey',
            'email' => 'mickey.revops@mitienda.online',
            'password' => bcrypt('password'),
            'role' => 'portal',
        ]);

        $warehouseOp = User::create([
            'name' => 'Carlos Almacén',
            'email' => 'carlos.almacen@mitienda.online',
            'password' => bcrypt('password'),
            'role' => 'warehouse',
        ]);

        // 2. Seed simulation data
        $this->seed(SimulationOperationalSeeder::class);

        $this->assertGreaterThan(0, Order::count());
        $this->assertGreaterThan(0, OrderFinancialMargin::count());
        $this->assertGreaterThan(0, Shipment::count());
        $this->assertGreaterThan(0, CustomerSubscription::count());
        $this->assertGreaterThan(0, VendorBill::count());

        $initialProductCount = Product::count();
        $initialVariantCount = ProductVariant::count();
        $this->assertGreaterThan(0, $initialProductCount);

        // 3. Execute purge command with --force
        $this->artisan('app:purge-test-transactions', ['--force' => true])
            ->assertExitCode(0);

        // 4. Assert Dominio B (Transactional tables) is 100% PURGED
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, OrderFinancialMargin::count());
        $this->assertEquals(0, Shipment::count());
        $this->assertEquals(0, CustomerSubscription::count());
        $this->assertEquals(0, VendorBill::count());
        $this->assertEquals(0, User::where('role', 'customer')->count());

        // 5. Assert Dominio A (Master / Catalog tables) remains 100% INTACT
        $this->assertEquals($initialProductCount, Product::count());
        $this->assertEquals($initialVariantCount, ProductVariant::count());
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'email' => 'mickey.revops@mitienda.online']);
        $this->assertDatabaseHas('users', ['id' => $warehouseOp->id, 'email' => 'carlos.almacen@mitienda.online']);

        // 6. Assert reserved inventories reset to zero
        $this->assertEquals(0, Inventory::where('reserved', '>', 0)->count());
    }
}

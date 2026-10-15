<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_user_can_increase_inventory_and_audit_the_movement(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $inventory = $this->createInventory(10, 2);

        $response = $this->actingAs($user)->postJson(
            "/admin/inventario/{$inventory->id}/movimientos",
            ['type' => 'increase', 'quantity' => 5, 'reason' => 'Recepcion de proveedor']
        );

        $response->assertCreated();
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'quantity' => 15, 'reserved' => 2]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'user_id' => $user->id,
            'quantity' => 5,
            'quantity_before' => 10,
            'quantity_after' => 15,
        ]);
    }

    public function test_portal_user_can_decrease_only_available_inventory(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $inventory = $this->createInventory(10, 3);

        $response = $this->actingAs($user)->postJson(
            "/admin/inventario/{$inventory->id}/movimientos",
            ['type' => 'decrease', 'quantity' => 7, 'reason' => 'Venta manual']
        );

        $response->assertCreated();
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'quantity' => 3, 'reserved' => 3]);
        $this->assertDatabaseHas('inventory_movements', ['quantity' => -7, 'quantity_after' => 3]);
    }

    public function test_decrease_is_rejected_when_it_would_consume_reserved_stock(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $inventory = $this->createInventory(10, 3);

        $response = $this->actingAs($user)->postJson(
            "/admin/inventario/{$inventory->id}/movimientos",
            ['type' => 'decrease', 'quantity' => 8, 'reason' => 'Ajuste invalido']
        );

        $response->assertUnprocessable();
        $response->assertJsonPath('errors.quantity.0', 'La salida supera el stock disponible.');
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'quantity' => 10, 'reserved' => 3]);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_inventory_form_registers_a_movement_and_redirects_to_inventory(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $inventory = $this->createInventory(4, 0);

        $this->actingAs($user)
            ->post("/admin/inventario/{$inventory->id}/movimientos", [
                'type' => 'increase',
                'quantity' => 2,
                'reason' => 'Conteo de almacen',
            ])
            ->assertRedirect(route('portal.inventory'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'quantity' => 6]);
    }

    public function test_admin_can_view_inventory_catalog(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get(route('portal.inventory'));
        $response->assertOk();
    }

    private function createInventory(int $quantity, int $reserved): Inventory
    {
        $variant = ProductVariant::factory()->for(Product::factory())->create();

        return Inventory::create([
            'variant_id' => $variant->id,
            'quantity' => $quantity,
            'reserved' => $reserved,
            'type' => 'physical',
        ]);
    }
}
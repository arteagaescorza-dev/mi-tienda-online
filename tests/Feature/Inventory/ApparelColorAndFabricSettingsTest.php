<?php

namespace Tests\Feature\Inventory;

use App\Models\ApparelColor;
use App\Models\ApparelFabric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApparelColorAndFabricSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_solid_color_accessors_and_serialization()
    {
        $color = ApparelColor::create([
            'name' => 'Negro Azabache',
            'slug' => 'negro-azabache',
            'color_type' => 'solid',
            'hex_code' => '#111827',
            'gradient_css' => null,
            'is_active' => true,
        ]);

        $this->assertEquals('#111827', $color->hex);
        $this->assertEquals('solid', $color->type);

        $json = $color->toArray();
        $this->assertArrayHasKey('hex', $json);
        $this->assertArrayHasKey('type', $json);
        $this->assertEquals('#111827', $json['hex']);
        $this->assertEquals('solid', $json['type']);
    }

    public function test_iridescent_gradient_color_accessors_and_serialization()
    {
        $color = ApparelColor::create([
            'name' => 'Aurora Boreal',
            'slug' => 'aurora-boreal',
            'color_type' => 'gradient',
            'hex_code' => '#6366f1',
            'gradient_css' => 'linear-gradient(135deg, #6366f1 0%, #ec4899 50%, #f59e0b 100%)',
            'is_active' => true,
        ]);

        $this->assertEquals('linear-gradient(135deg, #6366f1 0%, #ec4899 50%, #f59e0b 100%)', $color->hex);
        $this->assertEquals('iridescent', $color->type);

        $json = $color->toArray();
        $this->assertArrayHasKey('hex', $json);
        $this->assertArrayHasKey('type', $json);
        $this->assertEquals('linear-gradient(135deg, #6366f1 0%, #ec4899 50%, #f59e0b 100%)', $json['hex']);
        $this->assertEquals('iridescent', $json['type']);
    }

    public function test_api_can_create_update_toggle_and_delete_colors()
    {
        // 1. Create solid color
        $response = $this->actingAs($this->adminUser)->postJson('/api/v1/inventory/settings/colors', [
            'name' => 'Azul Índigo',
            'color_type' => 'solid',
            'hex_code' => '#3b82f6',
            'hex' => '#3b82f6',
        ]);
        $response->assertStatus(201);
        $colorId = $response->json('data.id');
        $this->assertNotNull($colorId);

        // 2. Update to iridescent
        $updateResp = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/settings/colors/{$colorId}", [
            'name' => 'Azul Galáctico Tornasol',
            'color_type' => 'gradient',
            'type' => 'iridescent',
            'hex' => 'linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%)',
            'gradient_css' => 'linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%)',
            'is_active' => true,
        ]);
        $updateResp->assertStatus(200);
        $this->assertDatabaseHas('apparel_colors', [
            'id' => $colorId,
            'name' => 'Azul Galáctico Tornasol',
            'color_type' => 'gradient',
        ]);

        // 3. Toggle active
        $toggleResp = $this->actingAs($this->adminUser)->patchJson("/api/v1/inventory/settings/colors/{$colorId}/toggle-active");
        $toggleResp->assertStatus(200);
        $this->assertDatabaseHas('apparel_colors', [
            'id' => $colorId,
            'is_active' => false,
        ]);

        // 4. Delete
        $deleteResp = $this->actingAs($this->adminUser)->deleteJson("/api/v1/inventory/settings/colors/{$colorId}");
        $deleteResp->assertStatus(200);
        $this->assertDatabaseMissing('apparel_colors', [
            'id' => $colorId,
        ]);
    }

    public function test_api_can_create_update_toggle_and_delete_fabrics()
    {
        // 1. Create fabric
        $response = $this->actingAs($this->adminUser)->postJson('/api/v1/inventory/settings/fabrics', [
            'name' => 'Modal Premium Spandex',
            'description' => '90% modal peinado, 10% spandex transpirable',
        ]);
        $response->assertStatus(201);
        $fabricId = $response->json('data.id');
        $this->assertNotNull($fabricId);

        // 2. Update fabric
        $updateResp = $this->actingAs($this->adminUser)->putJson("/api/v1/inventory/settings/fabrics/{$fabricId}", [
            'name' => 'Modal Orgánico Spandex Ultra',
            'description' => 'Actualizado: 92% modal orgánico, 8% elastano',
            'is_active' => true,
        ]);
        $updateResp->assertStatus(200);
        $this->assertDatabaseHas('apparel_fabrics', [
            'id' => $fabricId,
            'name' => 'Modal Orgánico Spandex Ultra',
        ]);

        // 3. Toggle fabric
        $toggleResp = $this->actingAs($this->adminUser)->patchJson("/api/v1/inventory/settings/fabrics/{$fabricId}/toggle-active");
        $toggleResp->assertStatus(200);
        $this->assertDatabaseHas('apparel_fabrics', [
            'id' => $fabricId,
            'is_active' => false,
        ]);

        // 4. Delete fabric
        $deleteResp = $this->actingAs($this->adminUser)->deleteJson("/api/v1/inventory/settings/fabrics/{$fabricId}");
        $deleteResp->assertStatus(200);
        $this->assertDatabaseMissing('apparel_fabrics', [
            'id' => $fabricId,
        ]);
    }
}

<?php

namespace Tests\Feature\Security;

use App\Models\Ally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditBlockOneSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_aud01_non_admin_and_guest_cannot_escalate_role_via_switch_role(): void
    {
        $this->postJson('/api/v1/governance/roles/switch', ['role' => 'superadmin'])
            ->assertStatus(403);

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->postJson('/admin/roles/switch', ['role' => 'superadmin'])
            ->assertStatus(403);

        $warehouse = User::factory()->create(['role' => 'warehouse']);
        $this->actingAs($warehouse)
            ->postJson('/api/v1/governance/roles/switch', ['role' => 'superadmin'])
            ->assertStatus(403);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->postJson('/admin/roles/switch', ['role' => 'vendor'])
            ->assertStatus(200)
            ->assertJson(['active_role' => 'vendor']);
    }

    public function test_aud02_x_user_role_header_cannot_bypass_financial_privacy(): void
    {
        $warehouse = User::factory()->create(['role' => 'warehouse']);

        $this->actingAs($warehouse)
            ->withHeaders(['X-User-Role' => 'superadmin'])
            ->getJson('/api/v1/finance/taxes/summary')
            ->assertStatus(403);
    }

    public function test_aud03_invalid_ally_portal_token_returns_404_and_valid_token_isolates_ally(): void
    {
        $allyA = Ally::create([
            'code' => 'ALY-ALPHA',
            'name' => 'Aliado Alpha Secreto',
            'category' => 'sauna',
            'universe' => 'marte',
            'portal_token' => 'tok_alpha_valid_123',
            'is_active' => true,
        ]);

        $allyB = Ally::create([
            'code' => 'ALY-BETA',
            'name' => 'Aliado Beta Privado',
            'category' => 'club',
            'universe' => 'venus',
            'portal_token' => 'tok_beta_valid_456',
            'is_active' => true,
        ]);

        $this->get('/portal/aliado/token_inexistente_999')
            ->assertStatus(404);

        $this->get('/portal/aliado/' . $allyA->portal_token)
            ->assertOk()
            ->assertSee('Aliado Alpha Secreto', false)
            ->assertDontSee('Aliado Beta Privado', false);
    }

    public function test_aud04_leads_routes_require_portal_authentication_and_authorization(): void
    {
        $this->get('/leads')->assertRedirect(route('login'));
        $this->get('/leads/board')->assertRedirect(route('login'));
        $this->get('/leads/import')->assertRedirect(route('login'));

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/leads')->assertStatus(403);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/leads')->assertOk();
    }
}

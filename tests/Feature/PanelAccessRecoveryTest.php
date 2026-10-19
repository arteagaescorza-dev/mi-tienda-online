<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_not_forbidden(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));

        $adminRedirect = $this->get('/admin');
        $adminRedirect->assertRedirect(route('login'));
    }

    public function test_vendor_session_on_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'portal']);
        $response = $this->actingAs($user)
            ->withSession(['active_role' => 'vendor'])
            ->get('/admin/dashboard');
        $response->assertOk();
    }

    public function test_superadmin_can_access_all_panel_hubs(): void
    {
        $superadmin = User::factory()->create([
            'email' => 'mike@gmail.com',
            'role' => 'superadmin',
        ]);

        $this->actingAs($superadmin)
            ->get('/admin')
            ->assertRedirect(route('portal.dashboard'));

        $this->actingAs($superadmin)
            ->get('/admin/dashboard')
            ->assertOk();

        $this->actingAs($superadmin)
            ->get('/admin/inventario-catalogo')
            ->assertOk();

        $this->actingAs($superadmin)
            ->get('/admin/convenios')
            ->assertOk();

        $this->actingAs($superadmin)
            ->get('/admin/finanzas')
            ->assertOk();
    }

    public function test_user_can_access_panel_with_switched_roles(): void
    {
        $user = User::factory()->create([
            'role' => 'portal',
        ]);

        foreach (['marketing', 'packer', 'picker', 'warehouse', 'operator', 'finance', 'purchases'] as $role) {
            $this->actingAs($user)
                ->withSession(['active_role' => $role])
                ->get('/admin/dashboard')
                ->assertOk();
        }
    }

    public function test_partner_portal_accessible_with_token_without_login(): void
    {
        Ally::create([
            'code' => 'ALLY-TEST-XYZ',
            'name' => 'Partner Test XYZ',
            'category' => 'spa_wellness',
            'universe' => 'marte',
            'contact_name' => 'Test Partner',
            'contact_whatsapp' => '+525500000000',
            'contact_email' => 'partner@test.com',
            'portal_token' => 'tok_partner_test_xyz',
            'commission_type' => 'percentage',
            'commission_rate' => 10.0,
            'lead_acquisition_cost' => 15.0,
            'bank_clabe' => '012180015678901234',
            'bank_name' => 'BBVA México',
            'clearing_balance' => 0.0,
            'status' => 'active',
        ]);

        $this->get('/portal/aliado/tok_partner_test_xyz')
            ->assertOk()
            ->assertSee('tok_partner_test_xyz', false);
    }
}

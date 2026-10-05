<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_user_can_access_the_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_portal_user_can_access_the_store_cms_hub(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        $this->actingAs($user)
            ->get('/admin/tienda-cms')
            ->assertOk()
            ->assertSee('Editor Visual Multiverso', false)
            ->assertSee('Banners &amp; Marquesinas', false)
            ->assertSee('Reglas de Flete', false)
            ->assertSee('Guía Oficial de Tallas', false)
            ->assertSee('Metro Chabacano', false);
    }

    public function test_customer_cannot_access_the_store_cms_hub(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/tienda-cms')
            ->assertForbidden();
    }

    public function test_portal_user_can_access_the_logistics_hub(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        $this->actingAs($user)
            ->get('/admin/logistica')
            ->assertOk()
            ->assertSee('Mesa de Expedición')
            ->assertSee('DHL Express')
            ->assertSee('Cajas');
    }

    public function test_portal_user_can_access_the_marketing_hub(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        $this->actingAs($user)
            ->get('/admin/marketing')
            ->assertOk()
            ->assertSee('Pipeline & Leads', false)
            ->assertSee('Scoring RFM', false)
            ->assertSee('Chatbot Marteaga', false)
            ->assertSee('Campañas & Emailing', false)
            ->assertSee('Cupones & Promos', false)
            ->assertSee('Telemetría Ads & Copiloto', false);
    }

    public function test_customer_cannot_access_the_marketing_hub(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/marketing')
            ->assertForbidden();
    }

    public function test_portal_user_can_access_the_finance_hub(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        $this->actingAs($user)
            ->get('/admin/finanzas')
            ->assertOk()
            ->assertSee('Flujo de Caja')
            ->assertSee('P&L Waterfall', false)
            ->assertSee('Facturación')
            ->assertSee('MXN $');
    }

    public function test_customer_cannot_access_the_finance_hub(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/finanzas')
            ->assertForbidden();
    }

    public function test_customer_cannot_access_the_logistics_hub(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/logistica')
            ->assertForbidden();
    }

    public function test_customer_cannot_access_the_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_portal_user_can_render_all_pending_module_screens(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        foreach (['chatbot', 'atribucion', 'emailing', 'cupones', 'conciliacion', 'logistica', 'garantias'] as $module) {
            $this->actingAs($user)
                ->get('/admin/modulos/' . $module)
                ->assertOk()
                ->assertSee('Modulo preparado para su implementacion');
        }
    }

    public function test_portal_user_can_access_the_allies_hub(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        $this->actingAs($user)
            ->get('/admin/convenios')
            ->assertOk()
            ->assertSee('Directorio &amp; Acuerdos', false)
            ->assertSee('Campañas Dir A', false)
            ->assertSee('Atribución Dir B', false)
            ->assertSee('Cámara de Compensación', false)
            ->assertSee('Previsualizar Partner Portal', false)
            ->assertSee('Paradiso Circuit CDMX', false)
            ->assertSee('SPEI', false);
    }

    public function test_customer_cannot_access_the_allies_hub(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/convenios')
            ->assertForbidden();
    }

    public function test_tokenized_partner_portal_can_be_accessed(): void
    {
        $this->get('/portal/aliado/tok_paradiso_2026')
            ->assertOk()
            ->assertSee('Paradiso Circuit CDMX', false)
            ->assertSee('tok_paradiso_2026', false);
    }

    public function test_customer_cannot_access_pending_module_screens(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
            ->get('/admin/modulos/chatbot')
            ->assertForbidden();
    }

    public function test_login_page_renders_with_form_and_action(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('action="' . route('login') . '"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('Acceso al Panel', false)
            ->assertDontSee('Filtro rápido', false)
            ->assertDontSee('Guia de tallas', false)
            ->assertDontSee('data-store-search', false);
    }

    public function test_portal_user_can_login_via_web_form_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'operativo@mitienda.test',
            'password' => bcrypt('secret123'),
            'role' => 'portal',
        ]);

        $response = $this->post('/login', [
            'email' => 'operativo@mitienda.test',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials_and_redirects_back_with_errors(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'inexistente@mitienda.test',
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_json_login_returns_json_response(): void
    {
        $user = User::factory()->create([
            'email' => 'api.user@mitienda.test',
            'password' => bcrypt('secret123'),
            'role' => 'portal',
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'api.user@mitienda.test',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'api.user@mitienda.test')
            ->assertJsonPath('user.type', 'portal');
    }

    public function test_authenticated_user_can_logout_via_post(): void
    {
        $user = User::factory()->create(['role' => 'portal']);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
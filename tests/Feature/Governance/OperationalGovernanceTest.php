<?php

namespace Tests\Feature\Governance;

use App\Models\EnvironmentAuditLog;
use App\Models\SystemAuditLog;
use App\Models\SystemEnvironmentConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalGovernanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que un usuario administrativo pueda acceder al Hub 8 de Gobernanza.
     */
    public function test_governance_hub_accessible_to_authorized_portal_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'portal',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/gobernanza');

        $response->assertStatus(200);
        $response->assertSee('Hub 8');
        $response->assertSee('Gobernanza Global');
        $response->assertSee('Matriz de Gobernanza y Privacidad Estricta');
    }

    /**
     * Verifica que las 5 pestañas de subnav se rendericen sin errores.
     */
    public function test_governance_hub_renders_all_five_subnav_tabs(): void
    {
        $admin = User::factory()->create([
            'role' => 'portal',
        ]);

        $this->actingAs($admin);

        $tabs = [
            'privacidad-rbac' => 'Matriz de Gobernanza',
            'auditoria'       => 'Bitacora de Operaciones Criticas',
            'notificaciones'  => 'Alertas Operativas con Deep Links',
            'mensajeria'      => 'Timeline Oficial',
            'ajustes-entorno' => 'Conmutador de Entorno de Ejecucion',
        ];

        foreach ($tabs as $tabKey => $expectedText) {
            $response = $this->get("/admin/gobernanza?tab={$tabKey}");
            $response->assertStatus(200);
            $response->assertSee($expectedText);
        }
    }

    /**
     * Verifica la conmutacion de entorno entre Sandbox y Produccion con registro de auditoria (TASK-SYS-ENV).
     */
    public function test_environment_switcher_updates_config_and_logs_audit(): void
    {
        $admin = User::factory()->create([
            'role' => 'portal',
        ]);

        $this->actingAs($admin);

        $payload = [
            'module' => 'checkout',
            'target_environment' => 'production',
            'lock_mock_payments' => false,
        ];

        $response = $this->post('/admin/gobernanza/entorno', $payload);

        $response->assertRedirect('/admin/gobernanza?tab=ajustes-entorno');
        $response->assertSessionHas('success');

        // 1. Validar actualizacion en SystemEnvironmentConfig
        $this->assertDatabaseHas('system_environment_configs', [
            'module' => 'checkout',
            'active_environment' => 'production',
        ]);

        // 2. Validar asiento en EnvironmentAuditLog
        $this->assertDatabaseHas('environment_audit_logs', [
            'module' => 'checkout',
            'to_environment' => 'production',
        ]);

        // 3. Validar asiento en SystemAuditLog
        $this->assertDatabaseHas('system_audit_logs', [
            'module' => 'system_governance',
            'action' => 'environment_switched',
            'reference_code' => 'checkout',
        ]);
    }

    /**
     * Verifica que un invitado no autenticado sea redirigido al login al intentar acceder a gobernanza.
     */
    public function test_unauthenticated_user_redirected_from_governance(): void
    {
        $response = $this->get('/admin/gobernanza');
        $response->assertRedirect('/login');
    }
}

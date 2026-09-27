<?php

namespace Tests\Feature\Governance;

use App\Models\OperationalTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class FinancialPrivacyTest extends TestCase
{
    use RefreshDatabase;

    private function createRoleUser(string $role, string $email): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Usuario ' . ucfirst($role),
                'password' => bcrypt('password'),
                'role' => $role,
            ]
        );
    }

    public function test_admin_and_finance_can_access_financial_portal_routes(): void
    {
        $admin = $this->createRoleUser('admin', 'admin.gov@mitienda.test');
        $this->actingAs($admin);

        $response = $this->get(route('portal.finance'));
        $response->assertStatus(200);

        $finance = $this->createRoleUser('finance', 'finance.gov@mitienda.test');
        $this->actingAs($finance);

        $responseFin = $this->get(route('portal.finance'));
        $responseFin->assertStatus(200);
    }

    public function test_operative_roles_are_blocked_from_financial_portal_routes(): void
    {
        $warehouseUser = $this->createRoleUser('warehouse', 'almacen.gov@mitienda.test');
        $this->actingAs($warehouseUser);

        $response = $this->get(route('portal.finance'));
        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('error');

        $salesUser = $this->createRoleUser('sales', 'ventas.gov@mitienda.test');
        $this->actingAs($salesUser);

        $responseSales = $this->get(route('portal.finance'));
        $responseSales->assertRedirect(route('portal.dashboard'));
        $responseSales->assertSessionHas('error');
    }

    public function test_operative_roles_are_blocked_from_financial_api_endpoints(): void
    {
        $warehouseUser = $this->createRoleUser('warehouse', 'almacen.api@mitienda.test');
        $this->actingAs($warehouseUser);

        $response = $this->getJson('/api/v1/finance/taxes/summary');
        $response->assertStatus(403);
        $response->assertJsonFragment([
            'error' => 'Acceso denegado por politica de privacidad financiera (TASK-RBAC-03).',
        ]);
    }

    public function test_admin_can_access_financial_api_endpoints(): void
    {
        $admin = $this->createRoleUser('admin', 'admin.api@mitienda.test');
        $this->actingAs($admin);

        $response = $this->getJson('/api/v1/finance/taxes/summary');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'period',
            'kpis' => [
                'gross_revenue',
                'taxable_base',
                'iva_transferred_16',
                'net_sat_iva_payable',
            ],
        ]);
    }

    public function test_response_sanitization_removes_financial_keys_for_restricted_roles(): void
    {
        $warehouseUser = $this->createRoleUser('warehouse', 'almacen.filter@mitienda.test');
        $this->actingAs($warehouseUser);

        // Consumir endpoint del middleware directamente o via request
        $middleware = new \App\Http\Middleware\EnforceFinancialPrivacy();
        $request = \Illuminate\Http\Request::create('/api/v1/orders/1', 'GET');
        $request->setUserResolver(fn () => $warehouseUser);

        $innerResponse = response()->json([
            'order_id' => 101,
            'status' => 'processing',
            'margin_net' => 450.00,
            'profit_margin_pct' => 45.2,
            'unit_cost' => 120.00,
            'gross_revenue' => 1000.00,
            'customer_name' => 'Cliente Protegido',
        ]);

        $filteredResponse = $middleware->handle($request, fn () => $innerResponse);
        $data = json_decode($filteredResponse->getContent(), true);

        // Claves confidenciales eliminadas
        $this->assertArrayNotHasKey('margin_net', $data);
        $this->assertArrayNotHasKey('profit_margin_pct', $data);
        $this->assertArrayNotHasKey('unit_cost', $data);
        $this->assertArrayNotHasKey('gross_revenue', $data);

        // Claves operativas conservadas
        $this->assertArrayHasKey('order_id', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('customer_name', $data);
    }

    public function test_role_switching_updates_session_and_financial_permissions(): void
    {
        $admin = $this->createRoleUser('admin', 'admin.switch@mitienda.test');
        $this->actingAs($admin);

        $this->assertTrue($admin->canAccessFinancialData());

        // Conmutar a rol operativo de almacen
        $response = $this->postJson(route('portal.roles.switch'), ['role' => 'warehouse']);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'active_role' => 'warehouse',
            'can_access_financials' => false,
        ]);

        $this->assertEquals('warehouse', $admin->active_role);
        $this->assertFalse($admin->canAccessFinancialData());

        // Conmutar nuevamente a admin
        $responseAdmin = $this->postJson(route('portal.roles.switch'), ['role' => 'admin']);
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertJson([
            'success' => true,
            'active_role' => 'admin',
            'can_access_financials' => true,
        ]);

        $this->assertEquals('admin', $admin->active_role);
        $this->assertTrue($admin->canAccessFinancialData());
    }

    public function test_operational_tasks_are_scoped_by_user_role(): void
    {
        // Crear tareas de distintos departamentos
        OperationalTask::create([
            'title' => 'Empacar lote urgente',
            'category' => 'packing',
            'assigned_role' => 'warehouse',
            'priority' => 'critical',
            'status' => 'pending',
        ]);

        OperationalTask::create([
            'title' => 'Emitir Orden de Compra por ROP',
            'category' => 'compras',
            'assigned_role' => 'purchases',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        OperationalTask::create([
            'title' => 'Retencion preventiva de SPEI por discrepancia',
            'category' => 'finance_matching_hold',
            'assigned_role' => 'finance',
            'priority' => 'critical',
            'status' => 'pending',
        ]);

        // Consulta filtrada para rol warehouse
        $warehouseTasks = OperationalTask::forUserRole('warehouse')->get();
        $this->assertTrue($warehouseTasks->contains('category', 'packing'));
        $this->assertFalse($warehouseTasks->contains('category', 'finance_matching_hold'));

        // Consulta filtrada para rol finance
        $financeTasks = OperationalTask::forUserRole('finance')->get();
        $this->assertTrue($financeTasks->contains('category', 'finance_matching_hold'));
        $this->assertFalse($financeTasks->contains('category', 'packing'));

        // Consulta para rol admin devuelve todas
        $adminTasks = OperationalTask::forUserRole('admin')->get();
        $this->assertGreaterThanOrEqual(3, $adminTasks->count());
    }

    public function test_blade_directive_can_view_financials_evaluates_correctly(): void
    {
        $admin = $this->createRoleUser('admin', 'admin.blade@mitienda.test');
        $this->actingAs($admin);

        $bladeAdmin = Blade::render('@canViewFinancials VER_FINANZAS @else OCULTAR_FINANZAS @endcanViewFinancials');
        $this->assertStringContainsString('VER_FINANZAS', $bladeAdmin);
        $this->assertStringNotContainsString('OCULTAR_FINANZAS', $bladeAdmin);

        $warehouse = $this->createRoleUser('warehouse', 'almacen.blade@mitienda.test');
        $this->actingAs($warehouse);

        $bladeWarehouse = Blade::render('@canViewFinancials VER_FINANZAS @else OCULTAR_FINANZAS @endcanViewFinancials');
        $this->assertStringContainsString('OCULTAR_FINANZAS', $bladeWarehouse);
        $this->assertStringNotContainsString('VER_FINANZAS', $bladeWarehouse);
    }
}

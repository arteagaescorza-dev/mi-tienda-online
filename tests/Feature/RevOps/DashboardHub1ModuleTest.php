<?php

namespace Tests\Feature\RevOps;

use App\Models\User;
use App\Models\Order;
use App\Models\Lead;
use App\Models\Cart;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\OperationalTask;
use App\Models\ShiftLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardHub1ModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_dashboard_renders_with_triple_rbac_and_hides_financials_for_warehouse_role(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'admin.revops@mitienda.online',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-2026-TEST-001',
            'user_id' => $adminUser->id,
            'total' => 1850.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'shipping_address' => 'Av. Insurgentes Sur 1200, Benito Juarez, CDMX',
        ]);

        Lead::create([
            'name' => 'Roberto Garza',
            'email' => 'roberto@example.com',
            'status' => 'new',
            'metadata' => ['score' => 88],
        ]);

        Cart::create([
            'status' => 'abandoned',
            'guest_token' => 'guest-cart-token-001',
        ]);

        $supplier = Supplier::create([
            'name' => 'Torzo Test',
            'contact_email' => 'torzo@test.com',
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-2026-TEST-01',
            'supplier_id' => $supplier->id,
            'status' => 'in_transit',
            'total_units' => 40,
            'subtotal' => 4400.00,
            'tax' => 704.00,
            'total_amount' => 5104.00,
        ]);

        // 1. Verificacion para Super Admin / Direccion
        $responseAdmin = $this->actingAs($adminUser)->get('/admin/dashboard');

        $responseAdmin->assertOk()
            ->assertSee('window.serverDashboardData', false)
            ->assertSee('Dashboard Ejecutivo')
            ->assertSee('Bandeja Operativa')
            ->assertSee('Atención & Ventas', false)
            ->assertSee('Feed RevOps: En Vivo')
            ->assertSee('Ventas hoy');

        // 2. Verificacion para Rol de Almacen (Politica estricta de privacidad financiera)
        $responseWarehouse = $this->actingAs($adminUser)->get('/admin/dashboard?role=warehouse');

        $responseWarehouse->assertOk()
            ->assertSee('window.serverDashboardData', false)
            ->assertSee('Modo Almacén & Empaque Físico (Carlos Almacén)', false)
            ->assertSee('Pedidos por empacar')
            ->assertSee('Recepciones en tránsito')
            ->assertSee('Politica de Privacidad Financiera Activa');

        // 3. Verificacion para Rol de Ventas y Atencion
        $responseSales = $this->actingAs($adminUser)->get('/admin/dashboard?role=sales');

        $responseSales->assertOk()
            ->assertSee('window.serverDashboardData', false)
            ->assertSee('Modo Atención & Ventas Activo (Ana Atención)', false)
            ->assertSee('Leads activos sin responder')
            ->assertSee('Carritos abandonados hoy')
            ->assertSee('Pedidos pausados por dirección');
    }

    public function test_can_fetch_operational_tasks_with_sla_status_and_complete_task(): void
    {
        // 1. Consulta inicial de tareas operativas via API
        $response = $this->getJson('/api/v1/dashboard/tasks');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'total',
                'tasks' => [
                    '*' => [
                        'id',
                        'title',
                        'category',
                        'priority',
                        'status',
                        'due_at',
                        'sla_status',
                        'due_time_formatted',
                    ],
                ],
            ]);

        $tasks = $response->json('tasks');
        $this->assertGreaterThanOrEqual(4, count($tasks));

        // 2. Filtrado por categoria (empaque)
        $empaqueResponse = $this->getJson('/api/v1/dashboard/tasks?category=empaque');
        $empaqueResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);
        $empaqueTasks = $empaqueResponse->json('tasks');
        $this->assertTrue(collect($empaqueTasks)->every(fn ($t) => $t['category'] === 'empaque'));

        // 3. Completar una tarea operativa
        $firstTask = $tasks[0];
        $taskId = $firstTask['id'];

        $completeResponse = $this->postJson("/api/v1/dashboard/tasks/{$taskId}/complete", [
            'operator_name' => 'Carlos Almacén',
            'notes' => 'Checklist física verificada y sellada para corte DHL 14:00 hrs.',
        ]);

        $completeResponse->assertOk()
            ->assertJson([
                'success' => true,
                'task_id' => $taskId,
                'status' => 'completed',
                'completed_by' => 'Carlos Almacén',
            ])
            ->assertJsonStructure([
                'success',
                'task_id',
                'status',
                'completed_at',
                'completed_by',
                'shift_log' => [
                    'id',
                    'message',
                    'operator_name',
                    'time',
                ],
            ]);

        // Verificacion en base de datos
        $this->assertDatabaseHas('operational_tasks', [
            'id' => $taskId,
            'status' => 'completed',
            'completed_by' => 'Carlos Almacén',
        ]);

        $this->assertDatabaseHas('shift_logs', [
            'operational_task_id' => $taskId,
            'event_type' => 'task_completed',
            'operator_name' => 'Carlos Almacén',
        ]);

        // 4. Verificacion de consulta de bitácora del turno (Shift Logs)
        $shiftLogResponse = $this->getJson('/api/v1/dashboard/shift-logs');
        $shiftLogResponse->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'logs' => [
                    '*' => [
                        'id',
                        'event_type',
                        'message',
                        'operator_name',
                        'time',
                    ],
                ],
            ]);
    }

    public function test_portal_dashboard_hydrates_operational_tasks_and_shift_logs_in_blade(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'portal',
            'email' => 'operativo.almacen@mitienda.online',
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/dashboard?role=warehouse');

        $response->assertOk()
            ->assertSee('window.serverDashboardData', false)
            ->assertSee('Corte Diario DHL Express (14:00 hrs)')
            ->assertSee('Tareas Físicas de Bodega & Picking', false)
            ->assertSee('Bitácora Inmutable del Turno (Shift Log)', false)
            ->assertSee('Mesa de Picking & Packing: Pedido #MIX-1048', false)
            ->assertSee('Recepción Inbound en Rampa: Torzo B2B #OC-2026-003', false)
            ->assertSee('Surtido Interno ROP: Anaquel A-01', false)
            ->assertSee('Despacho Manifiesto DHL Express (Corte 14:00 hrs)', false);
    }
}

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
use App\Models\SupportCase;
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

    public function test_can_fetch_sales_incidents_and_filter_by_type(): void
    {
        $response = $this->getJson('/api/v1/dashboard/sales/incidents');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'incidents' => [
                    '*' => [
                        'id',
                        'case_number',
                        'case_type',
                        'title',
                        'customer_name',
                        'customer_phone',
                        'status',
                        'priority',
                        'metadata',
                        'formatted_date',
                    ],
                ],
            ]);

        $incidents = $response->json('incidents');
        $this->assertGreaterThanOrEqual(4, count($incidents));

        // Filtrar carritos abandonados
        $cartsResponse = $this->getJson('/api/v1/dashboard/sales/incidents?type=abandoned_carts');
        $cartsResponse->assertOk()
            ->assertJson(['success' => true]);
        $carts = $cartsResponse->json('incidents');
        $this->assertTrue(collect($carts)->every(fn ($item) => $item['case_type'] === 'abandoned_cart'));

        // Filtrar direcciones dudosas
        $addressResponse = $this->getJson('/api/v1/dashboard/sales/incidents?type=doubtful_addresses');
        $addressResponse->assertOk();
        $addresses = $addressResponse->json('incidents');
        $this->assertTrue(collect($addresses)->every(fn ($item) => $item['case_type'] === 'doubtful_address'));
    }

    public function test_can_rescue_abandoned_cart_via_whatsapp_with_tokenized_link(): void
    {
        // Obtener un caso de carrito abandonado inicial
        $this->getJson('/api/v1/dashboard/sales/incidents');
        $case = SupportCase::where('case_type', 'abandoned_cart')->first();
        $this->assertNotNull($case);

        $response = $this->postJson('/api/v1/dashboard/sales/rescue-whatsapp', [
            'case_id' => $case->id,
            'operator_name' => 'Ana Atención',
            'discount_code' => 'RESCUE10',
            'custom_message' => 'Hola Andrea, te dejamos un descuento para terminar tu compra.',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'case_id' => $case->id,
            ])
            ->assertJsonStructure([
                'success',
                'case_id',
                'whatsapp_url',
                'tokenized_url',
                'shift_log' => [
                    'id',
                    'message',
                    'operator_name',
                    'time',
                ],
            ]);

        $whatsappUrl = $response->json('whatsapp_url');
        $tokenizedUrl = $response->json('tokenized_url');

        $this->assertStringContainsString('https://wa.me/', $whatsappUrl);
        $this->assertStringContainsString('rescue_token=', $tokenizedUrl);
        $this->assertStringContainsString('coupon=RESCUE10', $tokenizedUrl);

        // Verificar actualizacion del caso
        $case->refresh();
        $this->assertEquals('resolved', $case->status);
        $this->assertNotNull($case->resolved_at);
        $this->assertEquals('Ana Atención', $case->metadata['assigned_to']);

        // Verificar registro en shift_logs
        $this->assertDatabaseHas('shift_logs', [
            'event_type' => 'cart_rescued',
            'operator_name' => 'Ana Atención',
        ]);
    }

    public function test_can_validate_predictive_address_and_release_order_to_warehouse(): void
    {
        // Inicializar incidentes para tener ORD-1092
        $this->getJson('/api/v1/dashboard/sales/incidents');
        $case = SupportCase::where('case_type', 'doubtful_address')->first();
        $this->assertNotNull($case);

        $response = $this->postJson('/api/v1/dashboard/sales/validate-address', [
            'case_id' => $case->id,
            'order_number' => 'ORD-1092',
            'operator_name' => 'Ana Atención',
            'street' => 'Av. Chapultepec Sur',
            'ext_number' => '1480',
            'int_number' => 'Piso 4 Oficina B',
            'colonia' => 'Americana',
            'postal_code' => '44160',
            'city' => 'Guadalajara',
            'state' => 'Jalisco',
            'references' => 'Torre empresarial frente al banco',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'order_number' => 'ORD-1092',
                'order_status' => 'processing',
            ])
            ->assertJsonStructure([
                'success',
                'order_number',
                'order_status',
                'normalized_address',
                'shift_log',
            ]);

        // Verificar que la orden en base de datos cambio a processing y tiene el exterior number
        $order = Order::where('order_number', 'ORD-1092')->first();
        $this->assertNotNull($order);
        $this->assertEquals('processing', $order->status);
        $this->assertStringContainsString('#1480', $order->shipping_address);
        $this->assertStringContainsString('44160', $order->shipping_address);

        // Caso resuelto
        $case->refresh();
        $this->assertEquals('resolved', $case->status);

        // Bitácora registrada
        $this->assertDatabaseHas('shift_logs', [
            'event_type' => 'address_validated',
            'operator_name' => 'Ana Atención',
        ]);
    }

    public function test_sepomex_lookup_returns_colonias_and_location(): void
    {
        // Caso valido: 44100
        $response = $this->getJson('/api/v1/dashboard/sales/sepomex-lookup?cp=44100');
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'postal_code' => '44100',
                'state' => 'Jalisco',
                'city' => 'Guadalajara',
            ]);

        $colonias = $response->json('colonias');
        $this->assertIsArray($colonias);
        $this->assertContains('Americana', $colonias);

        // Caso valido: 06600 (Juarez CDMX)
        $responseCdmx = $this->getJson('/api/v1/dashboard/sales/sepomex-lookup?cp=06600');
        $responseCdmx->assertOk()
            ->assertJson([
                'success' => true,
                'postal_code' => '06600',
                'state' => 'Ciudad de México',
                'city' => 'Cuauhtémoc',
            ]);

        // Caso CP no encontrado en fallback pero valido formato
        $responseUnknown = $this->getJson('/api/v1/dashboard/sales/sepomex-lookup?cp=99999');
        $responseUnknown->assertOk()
            ->assertJson([
                'success' => true,
                'postal_code' => '99999',
            ]);
    }
}


<?php

namespace Tests\Feature\Governance;

use App\Models\OperationalTask;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Governance\NotificationDispatcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationDispatcherService $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = app(NotificationDispatcherService::class);
    }

    /**
     * Verifica que el despachador cree alertas con deep links navegables precisos.
     */
    public function test_dispatcher_creates_operational_notification_with_accurate_deep_link(): void
    {
        $notification = $this->dispatcher->dispatchInventoryRopAlert(
            sku: 'TOR-DRP-003',
            currentStock: 2,
            ropThreshold: 10,
            productName: 'Polera Dry-Fit Torzo'
        );

        $this->assertDatabaseHas('user_notifications', [
            'id' => $notification->id,
            'title' => 'Alerta ROP: Stock Critico en TOR-DRP-003',
            'category' => 'inventory',
            'target_role' => 'warehouse',
            'type' => 'critical',
            'reference_code' => 'TOR-DRP-003',
            'deep_link' => '/portal/inventario?sku=TOR-DRP-003',
            'is_read' => false,
        ]);

        $this->assertEquals('red', $notification->badge_color);
        $this->assertStringContainsString('Hace', $notification->time_ago);
    }

    /**
     * Verifica que el feed de notificaciones filtre estrictamente por el rol operativo del usuario.
     */
    public function test_notification_feed_filters_by_user_operational_role(): void
    {
        $warehouseUser = User::factory()->create([
            'role' => 'warehouse',
        ]);

        // 1. Notificacion para almacen
        $notifWarehouse = $this->dispatcher->dispatch([
            'title' => 'Conteo Ciclico Pendiente',
            'message' => 'Pasillo 4 requiere auditoria.',
            'category' => 'inventory',
            'target_role' => 'warehouse',
        ]);

        // 2. Notificacion para compras
        $notifPurchases = $this->dispatcher->dispatch([
            'title' => 'Confirmacion de Proveedor',
            'message' => 'Proveedor aprobo OC-100.',
            'category' => 'purchases',
            'target_role' => 'purchases',
        ]);

        $feed = $this->dispatcher->getFeedForUser($warehouseUser);

        $this->assertEquals(1, $feed['unread_count']);
        $this->assertEquals($notifWarehouse->id, $feed['items']->first()->id);
        $this->assertFalse($feed['items']->contains('id', $notifPurchases->id));
    }

    /**
     * Verifica que el personal operativo restringido NO pueda acceder a notificaciones financieras (TASK-RBAC-03).
     */
    public function test_financial_privacy_strictly_enforced_in_notifications_feed(): void
    {
        $logisticsUser = User::factory()->create([
            'role' => 'logistics',
        ]);

        // Notificacion financiera confidencial
        $this->dispatcher->dispatchFinanceHoldAlert(
            orderFolio: 'ORD-2026-999',
            amount: 145000.50,
            reason: 'Discrepancia contable de matching SAT y SPEI'
        );

        // Notificacion logistica legitima
        $logisticsAlert = $this->dispatcher->dispatchLogisticsBlockedAlert(
            guia: 'GUIA-88219',
            carrier: 'Estafeta',
            reason: 'Retencion en aduana local'
        );

        $this->actingAs($logisticsUser);

        $response = $this->getJson('/api/v1/governance/notifications');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'unread_count' => 1,
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($logisticsAlert->id, $data[0]['id']);
        $this->assertEquals('logistics', $data[0]['category']);

        // Confirmar que no hay rastro de la alerta financiera en la respuesta JSON
        $response->assertDontSee('ORD-2026-999');
        $response->assertDontSee('145000.50');
    }

    /**
     * Verifica el marcado individual de una notificacion como leida.
     */
    public function test_marking_individual_notification_as_read_via_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $notif = $this->dispatcher->dispatch([
            'title' => 'Mantenimiento Programado',
            'message' => 'Servidor en modo mantenimiento nocturno.',
            'category' => 'system',
            'type' => 'info',
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/api/v1/governance/notifications/{$notif->id}/mark-read");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Notificacion marcada como leida.',
            'unread_count' => 0,
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'id' => $notif->id,
            'is_read' => true,
        ]);
        $this->assertNotNull(UserNotification::find($notif->id)->read_at);
    }

    /**
     * Verifica el marcado masivo de todas las notificaciones pendientes.
     */
    public function test_marking_all_notifications_as_read(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->dispatcher->dispatch(['title' => 'Alerta 1', 'message' => 'Mensaje 1', 'category' => 'system']);
        $this->dispatcher->dispatch(['title' => 'Alerta 2', 'message' => 'Mensaje 2', 'category' => 'system']);
        $this->dispatcher->dispatch(['title' => 'Alerta 3', 'message' => 'Mensaje 3', 'category' => 'system']);

        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/governance/notifications/mark-all-read');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'marked_count' => 3,
            'unread_count' => 0,
        ]);

        $this->assertEquals(0, UserNotification::where('is_read', false)->count());
    }

    /**
     * Verifica la sincronizacion y creacion automatica de alertas desde OperationalTask.
     */
    public function test_dispatch_from_operational_task_generates_linked_notification(): void
    {
        $task = OperationalTask::create([
            'title' => 'Auditoria ROP Urgente: TOR-DRP-001',
            'category' => 'inventory_rop',
            'priority' => 'critical',
            'assigned_role' => 'warehouse',
            'status' => 'pending',
            'reference_code' => 'TOR-DRP-001',
            'description' => 'Disponibilidad por debajo del minimo requerido.',
        ]);

        $notification = $this->dispatcher->dispatchFromOperationalTask($task);

        $this->assertEquals($task->id, $notification->operational_task_id);
        $this->assertEquals('critical', $notification->type);
        $this->assertEquals('inventory', $notification->category);
        $this->assertEquals('/portal/inventario?sku=TOR-DRP-001', $notification->deep_link);
        $this->assertEquals('warehouse', $notification->target_role);
    }

    /**
     * Verifica que solo roles con privilegios directivos puedan emitir notificaciones personalizadas.
     */
    public function test_custom_notification_dispatch_permissions(): void
    {
        $warehouseUser = User::factory()->create(['role' => 'warehouse']);
        $adminUser = User::factory()->create(['role' => 'admin']);

        $payload = [
            'title' => 'Comunicado de Bodega',
            'message' => 'Anden 2 disponible.',
            'category' => 'logistics',
            'type' => 'info',
        ];

        // 1. Usuario restringido recibe 403
        $this->actingAs($warehouseUser);
        $resForbidden = $this->postJson('/api/v1/governance/notifications/dispatch', $payload);
        $resForbidden->assertStatus(403);

        // 2. Administrador emite con exito (201)
        $this->actingAs($adminUser);
        $resSuccess = $this->postJson('/api/v1/governance/notifications/dispatch', $payload);
        $resSuccess->assertStatus(201);
        $resSuccess->assertJson([
            'success' => true,
            'message' => 'Notificacion operativa despachada exitosamente.',
        ]);
    }
}

<?php

namespace Tests\Feature\Governance;

use App\Models\InterdepartmentalMessage;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Governance\InterdepartmentalChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterdepartmentalChatTest extends TestCase
{
    use RefreshDatabase;

    protected InterdepartmentalChatService $chatService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatService = app(InterdepartmentalChatService::class);
    }

    /**
     * Verifica que el servicio auto-detecte folios en etiquetas como @Pedido:, @SKU:, @OC:, etc.
     */
    public function test_send_chat_message_auto_detects_folio_tags(): void
    {
        $user = User::factory()->create([
            'name' => 'Carlos Almacen',
            'role' => 'warehouse',
        ]);

        $message = $this->chatService->sendMessage(
            channel: 'almacen',
            message: 'Se recibio embarque B2B para @Pedido:#MIX-1048 en rampa 2.',
            priority: 'normal',
            user: $user
        );

        $this->assertDatabaseHas('interdepartmental_messages', [
            'id' => $message->id,
            'channel' => 'almacen',
            'sender_id' => $user->id,
            'sender_name' => 'Carlos Almacen',
            'sender_role' => 'warehouse',
            'reference_type' => 'order',
            'reference_code' => '#MIX-1048',
            'is_resolved' => false,
        ]);

        $this->assertEquals('@Pedido:#MIX-1048', $message->tag_badge);
        $this->assertEquals('slate', $message->priority_badge_color);
    }

    /**
     * Verifica que mensajes urgentes dirigidos a un rol despachen una notificacion operativa.
     */
    public function test_urgent_chat_message_dispatches_operational_notification(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana Ventas',
            'role' => 'sales',
        ]);

        $message = $this->chatService->sendMessage(
            channel: 'almacen',
            message: 'Urgente: verificar stock en fisico de @SKU:BOX-COT-001 antes de recoleccion.',
            priority: 'urgent',
            user: $user,
            targetRole: 'warehouse'
        );

        $this->assertEquals('urgent', $message->priority);
        $this->assertEquals('red', $message->priority_badge_color);

        $this->assertDatabaseHas('user_notifications', [
            'type' => 'urgent',
            'target_role' => 'warehouse',
            'reference_code' => 'BOX-COT-001',
        ]);
    }

    /**
     * Verifica la consulta del timeline filtrado por canal con conteo de no resueltos.
     */
    public function test_get_channel_messages_and_unresolved_count(): void
    {
        // 1. Mensaje en almacen no resuelto
        $msg1 = InterdepartmentalMessage::create([
            'channel' => 'almacen',
            'sender_name' => 'Operador 1',
            'sender_role' => 'warehouse',
            'message' => 'Llegaron tarimas dañadas.',
            'priority' => 'high',
            'is_resolved' => false,
        ]);

        // 2. Mensaje en almacen resuelto
        $msg2 = InterdepartmentalMessage::create([
            'channel' => 'almacen',
            'sender_name' => 'Operador 2',
            'sender_role' => 'warehouse',
            'message' => 'Tarimas reubicadas.',
            'priority' => 'normal',
            'is_resolved' => true,
            'resolved_by' => 'Supervisor',
            'resolved_at' => now(),
        ]);

        // 3. Mensaje en ventas
        InterdepartmentalMessage::create([
            'channel' => 'ventas',
            'sender_name' => 'Atencion',
            'sender_role' => 'sales',
            'message' => 'Consulta de cliente.',
            'priority' => 'normal',
            'is_resolved' => false,
        ]);

        $data = $this->chatService->getChannelMessages('almacen');

        $this->assertEquals('almacen', $data['channel']);
        $this->assertEquals(1, $data['unresolved_count']);
        $this->assertCount(2, $data['messages']);
    }

    /**
     * Verifica la resolucion de una nota operativa a traves del endpoint API.
     */
    public function test_resolving_chat_message_via_api(): void
    {
        $supervisor = User::factory()->create([
            'name' => 'Mickey RevOps',
            'role' => 'admin',
        ]);

        $message = InterdepartmentalMessage::create([
            'channel' => 'logistica',
            'sender_name' => 'Operador Envios',
            'sender_role' => 'logistics',
            'message' => 'Falta guia para paquete #ORD-991.',
            'priority' => 'high',
            'is_resolved' => false,
        ]);

        $this->actingAs($supervisor);

        $response = $this->postJson("/api/v1/governance/chat/messages/{$message->id}/resolve");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Mensaje operativo marcado como resuelto.',
        ]);

        $this->assertDatabaseHas('interdepartmental_messages', [
            'id' => $message->id,
            'is_resolved' => true,
            'resolved_by' => 'Mickey RevOps',
        ]);
    }

    /**
     * Verifica el resumen global de mensajes pendientes por canal.
     */
    public function test_chat_summary_aggregates_unresolved_per_channel(): void
    {
        InterdepartmentalMessage::create([
            'channel' => 'almacen',
            'sender_name' => 'Op 1',
            'sender_role' => 'warehouse',
            'message' => 'Nota 1',
            'is_resolved' => false,
        ]);
        InterdepartmentalMessage::create([
            'channel' => 'almacen',
            'sender_name' => 'Op 2',
            'sender_role' => 'warehouse',
            'message' => 'Nota 2',
            'is_resolved' => false,
        ]);
        InterdepartmentalMessage::create([
            'channel' => 'ventas',
            'sender_name' => 'Op 3',
            'sender_role' => 'sales',
            'message' => 'Nota 3',
            'is_resolved' => false,
        ]);

        $response = $this->getJson('/api/v1/governance/chat/summary');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'almacen' => 2,
                'ventas' => 1,
                'total_unresolved' => 3,
            ],
        ]);
    }

    /**
     * Verifica el envio de un nuevo mensaje mediante la API RESTful.
     */
    public function test_send_chat_message_endpoint(): void
    {
        $user = User::factory()->create([
            'name' => 'Operador Logistica',
            'role' => 'logistics',
        ]);

        $this->actingAs($user);

        $payload = [
            'channel' => 'logistica',
            'message' => 'DHL recolecto guia @Guia:DHL-88912 con 5 paquetes.',
            'priority' => 'normal',
        ];

        $response = $this->postJson('/api/v1/governance/chat/messages', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'notice' => 'Mensaje interdepartamental transmitido exitosamente.',
        ]);

        $this->assertDatabaseHas('interdepartmental_messages', [
            'channel' => 'logistica',
            'reference_type' => 'shipment',
            'reference_code' => 'DHL-88912',
            'sender_name' => 'Operador Logistica',
        ]);
    }
}

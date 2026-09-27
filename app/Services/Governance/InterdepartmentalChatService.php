<?php

namespace App\Services\Governance;

use App\Models\InterdepartmentalMessage;
use App\Models\User;
use Illuminate\Support\Collection;

class InterdepartmentalChatService
{
    public function __construct(
        protected NotificationDispatcherService $notificationDispatcher
    ) {}

    /**
     * Canales oficiales permitidos en la plataforma de gobernanza.
     */
    public const CHANNELS = [
        'almacen'   => 'Almacen & Compras',
        'ventas'    => 'Atencion & Ventas',
        'logistica' => 'Logistica & Guias',
        'general'   => 'RevOps General',
    ];

    /**
     * Envia un nuevo mensaje interdepartamental formalmente anclado a folios de negocio (TASK-COM-01).
     */
    public function sendMessage(
        string $channel,
        string $message,
        ?string $refType = null,
        ?string $refCode = null,
        string $priority = 'normal',
        ?User $user = null,
        ?string $senderName = null,
        ?string $senderRole = null,
        ?string $targetRole = null
    ): InterdepartmentalMessage {
        // Auto-deteccion de etiquetas en el texto si no se proporcionaron explicitamente
        if (!$refCode) {
            if (preg_match('/@(Pedido|Order):#?([A-Za-z0-9\-_]+)/i', $message, $matches)) {
                $refType = 'order';
                $refCode = '#' . ltrim($matches[2], '#');
            } elseif (preg_match('/@(SKU|Producto):([A-Za-z0-9\-_]+)/i', $message, $matches)) {
                $refType = 'sku';
                $refCode = strtoupper($matches[2]);
            } elseif (preg_match('/@(OC|PO|Compra):([A-Za-z0-9\-_]+)/i', $message, $matches)) {
                $refType = 'po';
                $refCode = strtoupper($matches[2]);
            } elseif (preg_match('/@(Guia|Tracking|Envio):([A-Za-z0-9\-_]+)/i', $message, $matches)) {
                $refType = 'shipment';
                $refCode = strtoupper($matches[2]);
            } elseif (preg_match('/@(RMA|Devolucion):([A-Za-z0-9\-_]+)/i', $message, $matches)) {
                $refType = 'rma';
                $refCode = strtoupper($matches[2]);
            }
        }

        $activeRole = $user ? (session('active_role') ?: $user->active_role ?: $user->role) : ($senderRole ?: 'operator');
        $resolvedSenderName = $senderName ?: ($user ? $user->name : 'Operador');

        $record = InterdepartmentalMessage::create([
            'channel' => in_array($channel, array_keys(self::CHANNELS), true) ? $channel : 'general',
            'sender_id' => $user?->id,
            'sender_name' => $resolvedSenderName,
            'sender_role' => $activeRole,
            'target_role' => $targetRole,
            'message' => trim($message),
            'reference_type' => $refType,
            'reference_code' => $refCode,
            'priority' => in_array($priority, ['urgent', 'high', 'normal'], true) ? $priority : 'normal',
            'is_resolved' => false,
            'resolved_at' => null,
            'resolved_by' => null,
        ]);

        // Si el mensaje es de prioridad urgente, despachar notificacion operativa cruzada
        if ($priority === 'urgent' && $targetRole) {
            $this->notificationDispatcher->dispatch([
                'title' => "Mensaje Urgente en #{$channel}",
                'message' => "{$resolvedSenderName}: " . substr(trim($message), 0, 100),
                'type' => 'urgent',
                'category' => $channel === 'almacen' ? 'inventory' : ($channel === 'logistica' ? 'logistica' : 'sales'),
                'target_role' => $targetRole,
                'reference_code' => $refCode,
                'action_label' => 'Abrir Chat Inter-Areas',
                'metadata' => [
                    'message_id' => $record->id,
                    'channel' => $channel,
                ],
            ]);
        }

        return $record;
    }

    /**
     * Consulta el timeline de mensajes de un canal con marcas de autoría relativas al usuario.
     */
    public function getChannelMessages(string $channel, int $limit = 50, ?User $viewer = null): array
    {
        $this->seedBaselineMessagesIfEmpty();

        $messages = InterdepartmentalMessage::channel($channel)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        $viewerId = $viewer?->id;

        $formatted = $messages->map(function ($msg) use ($viewerId) {
            $array = $msg->toArray();
            $array['isMine'] = $viewerId ? ($msg->sender_id === $viewerId) : false;
            $array['tag'] = $msg->tag_badge;
            $array['time'] = $msg->formatted_time;
            $array['text'] = $msg->message;
            $array['sender'] = $msg->sender_name;
            $array['role'] = $msg->sender_role;
            return $array;
        });

        $unresolvedCount = InterdepartmentalMessage::channel($channel)->unresolved()->count();

        return [
            'channel' => $channel,
            'channel_title' => self::CHANNELS[$channel] ?? 'Canal Operativo',
            'unresolved_count' => $unresolvedCount,
            'messages' => $formatted,
        ];
    }

    /**
     * Marca un mensaje operativo como atendido/resuelto.
     */
    public function resolveMessage(int $messageId, ?User $resolver = null): bool
    {
        $message = InterdepartmentalMessage::find($messageId);
        if (!$message) {
            return false;
        }

        $resolverName = $resolver ? $resolver->name : 'Supervisor RevOps';
        return $message->resolve($resolverName);
    }

    /**
     * Resumen de mensajes pendientes por canal para badges reactivos.
     */
    public function getChannelsSummary(): array
    {
        $this->seedBaselineMessagesIfEmpty();

        $summary = [];
        $totalUnresolved = 0;

        foreach (array_keys(self::CHANNELS) as $ch) {
            $count = InterdepartmentalMessage::channel($ch)->unresolved()->count();
            $summary[$ch] = $count;
            $totalUnresolved += $count;
        }

        $summary['total_unresolved'] = $totalUnresolved;

        return $summary;
    }

    /**
     * Sembrado de baseline para garantizar paridad con el mock inicial sin romper la continuidad del panel.
     */
    public function seedBaselineMessagesIfEmpty(): void
    {
        if (InterdepartmentalMessage::count() > 0) {
            return;
        }

        $baseline = [
            [
                'channel' => 'almacen',
                'sender_name' => 'Carlos (Almacen)',
                'sender_role' => 'warehouse',
                'message' => 'Buenos dias equipo. Ya llego la paqueteria con el embarque de Torzo B2B. Lote de 150 piezas de Boxer Pima verificado.',
                'reference_type' => 'order',
                'reference_code' => '#MIX-1048',
                'priority' => 'normal',
                'is_resolved' => false,
            ],
            [
                'channel' => 'almacen',
                'sender_name' => 'Mickey RevOps',
                'sender_role' => 'admin',
                'message' => 'Excelente noticia. Por favor procede a liberar de inmediato el Carrito Mixto #MIX-1048 para que salga hoy en la recoleccion.',
                'reference_type' => 'order',
                'reference_code' => '#MIX-1048',
                'priority' => 'high',
                'is_resolved' => true,
                'resolved_by' => 'Carlos (Almacen)',
                'resolved_at' => now()->subMinutes(15),
            ],
            [
                'channel' => 'ventas',
                'sender_name' => 'Ana (Ventas)',
                'sender_role' => 'sales',
                'message' => 'Un cliente en WhatsApp solicita saber si la talla G de la trusa modal viene ajustada al muslo.',
                'reference_type' => 'sku',
                'reference_code' => 'BOX-COT-001',
                'priority' => 'normal',
                'is_resolved' => false,
            ],
            [
                'channel' => 'logistica',
                'sender_name' => 'Operador Envios',
                'sender_role' => 'logistics',
                'message' => 'DHL confirmo recoleccion a las 14:00 hrs. Necesitamos tener listas las 14 ordenes regulares y las 3 mixtas.',
                'reference_type' => 'shipment',
                'reference_code' => 'DHL-RECOL-01',
                'priority' => 'urgent',
                'is_resolved' => false,
            ],
            [
                'channel' => 'general',
                'sender_name' => 'Mickey RevOps',
                'sender_role' => 'admin',
                'message' => 'Aviso general: Se completo la auditoria de inventario fisico y Kardex con 100% de conciliacion. Buen trabajo.',
                'reference_type' => null,
                'reference_code' => null,
                'priority' => 'normal',
                'is_resolved' => true,
                'resolved_by' => 'Mickey RevOps',
                'resolved_at' => now()->subDays(1),
            ],
        ];

        foreach ($baseline as $item) {
            InterdepartmentalMessage::create($item);
        }
    }
}

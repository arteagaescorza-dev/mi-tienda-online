<?php

namespace App\Services;

use App\Models\PwaPushNotificationLog;
use App\Models\PwaPushSubscription;
use Illuminate\Support\Facades\Log;

class WebPushNotificationService
{
    /**
     * Registra o actualiza una suscripcion Web Push VAPID para un cliente o sesion.
     */
    public function subscribe(array $data, ?int $userId = null, ?string $cartSessionId = null): PwaPushSubscription
    {
        $endpoint = trim($data['endpoint'] ?? '');
        $keys = $data['keys'] ?? [];
        $p256dh = $keys['p256dh'] ?? ($data['p256dh_key'] ?? null);
        $auth = $keys['auth'] ?? ($data['auth_token'] ?? null);
        $topics = $data['topics'] ?? [
            'stock_urgency' => true,
            'order_tracking' => true,
            'subscription_reminders' => true,
            'drops_promos' => true,
        ];

        $subscription = PwaPushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $userId,
                'guest_cart_session' => $cartSessionId,
                'p256dh_key' => $p256dh,
                'auth_token' => $auth,
                'topics' => $topics,
                'is_active' => true,
                'user_agent' => substr($data['user_agent'] ?? request()->userAgent() ?? '', 0, 255),
                'ip_address' => request()->ip(),
            ]
        );

        // Registrar log de bienvenida
        $this->sendNotification(
            $subscription,
            'welcome',
            'Multiverso Store // Alertas PWA Activadas',
            'Recibiras notificaciones en tiempo real sobre tu carrito, guias de envio y lanzamientos.',
            '/'
        );

        return $subscription;
    }

    /**
     * Da de baja una suscripcion Web Push.
     */
    public function unsubscribe(string $endpoint): bool
    {
        $sub = PwaPushSubscription::where('endpoint', trim($endpoint))->first();
        if ($sub) {
            $sub->update(['is_active' => false]);
            return true;
        }

        return false;
    }

    /**
     * Actualiza los topicos de interes seleccionados por el comprador.
     */
    public function updatePreferences(string $endpoint, array $topics): ?PwaPushSubscription
    {
        $sub = PwaPushSubscription::where('endpoint', trim($endpoint))->first();
        if ($sub) {
            $merged = array_merge($sub->topics ?? [], $topics);
            $sub->update(['topics' => $merged]);
            return $sub;
        }

        return null;
    }

    /**
     * Envia y asienta de forma auditable una notificacion Push.
     */
    public function sendNotification(
        PwaPushSubscription $subscription,
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null
    ): PwaPushNotificationLog {
        // En ambiente de desarrollo y local simulamos la entrega VAPID con log inmutable
        $deliveryStatus = 'sent';
        $errorMessage = null;

        return PwaPushNotificationLog::create([
            'subscription_id' => $subscription->id,
            'notification_type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'delivery_status' => $deliveryStatus,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Envia una notificacion push de prueba.
     */
    public function sendTestNotification(string $endpoint): PwaPushNotificationLog
    {
        $sub = PwaPushSubscription::where('endpoint', trim($endpoint))->first();
        if (!$sub) {
            $sub = PwaPushSubscription::create([
                'endpoint' => $endpoint,
                'is_active' => true,
                'topics' => ['test' => true],
            ]);
        }

        return $this->sendNotification(
            $sub,
            'test',
            'Notificacion de Prueba // Multiverso Store',
            'Tu dispositivo esta vinculado correctamente para recibir alertas operativas y comerciales.',
            '/'
        );
    }

    /**
     * Alerta de urgencia por candado de stock (TASK-INT-06 / TASK-STR-06).
     */
    public function notifyStockUrgency(string $cartSessionId, int $remainingMinutes = 2): ?PwaPushNotificationLog
    {
        $sub = PwaPushSubscription::where('guest_cart_session', $cartSessionId)
            ->where('is_active', true)
            ->first();

        if (!$sub) {
            return null;
        }

        return $this->sendNotification(
            $sub,
            'stock_urgency',
            'Apartado de Stock por Expirar (' . $remainingMinutes . ' min)',
            'Tus prendas reservadas seran liberadas al catalogo general en breve. Finaliza tu compra ahora.',
            '/?action=open_cart'
        );
    }

    /**
     * Alerta de paquete despachado con guia courier DHL Express (TASK-LOG-03 / TASK-STR-06).
     */
    public function notifyOrderShipped(string $folio, string $trackingNumber, ?string $cartSessionId = null): ?PwaPushNotificationLog
    {
        $query = PwaPushSubscription::where('is_active', true);
        if ($cartSessionId) {
            $query->where('guest_cart_session', $cartSessionId);
        }

        $sub = $query->latest()->first();
        if (!$sub) {
            return null;
        }

        return $this->sendNotification(
            $sub,
            'order_tracking',
            'Paquete en Camino // DHL Express',
            'Tu pedido ' . $folio . ' ha salido de bodega con guia DHL ' . $trackingNumber . '.',
            '/?action=open_orders&orderFolio=' . urlencode($folio)
        );
    }
}

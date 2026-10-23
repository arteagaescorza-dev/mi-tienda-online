<?php

namespace App\Services\Governance;

use App\Models\OperationalTask;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Collection;

class NotificationDispatcherService
{
    /**
     * Construye un deep link interno preciso hacia el modulo y folio operativo (TASK-UX-03).
     */
    public function generateDeepLink(string $category, ?string $referenceCode = null, array $params = []): string
    {
        $baseRoute = match (strtolower($category)) {
            'inventory', 'almacen', 'inventario', 'rop' => '/portal/inventario',
            'logistics', 'logistica', 'shipping', 'envios' => '/portal/logistica',
            'purchases', 'compras', 'proveedores' => '/portal/proveedores',
            'finance', 'finanzas', 'contabilidad' => '/portal/finanzas',
            'sales', 'ventas', 'atencion', 'support' => '/portal/atencion',
            'allies', 'convenios', 'b2b' => '/admin/convenios',
            default => '/portal/dashboard',
        };

        $queryParams = $params;

        if ($referenceCode) {
            match (strtolower($category)) {
                'inventory', 'almacen', 'inventario', 'rop' => $queryParams['sku'] = $referenceCode,
                'logistics', 'logistica', 'shipping', 'envios' => $queryParams['guia'] = $referenceCode,
                'purchases', 'compras', 'proveedores' => $queryParams['po'] = $referenceCode,
                'finance', 'finanzas', 'contabilidad' => $queryParams['folio'] = $referenceCode,
                'sales', 'ventas', 'atencion', 'support' => $queryParams['ticket'] = $referenceCode,
                default => $queryParams['ref'] = $referenceCode,
            };
        }

        if (empty($queryParams)) {
            return $baseRoute;
        }

        return $baseRoute . '?' . http_build_query($queryParams);
    }

    /**
     * Despacha una notificacion operativa individual en el sistema.
     */
    public function dispatch(array $data): UserNotification
    {
        $category = $data['category'] ?? 'general';
        $referenceCode = $data['reference_code'] ?? null;
        $deepLink = $data['deep_link'] ?? $this->generateDeepLink($category, $referenceCode, $data['params'] ?? []);

        return UserNotification::create([
            'user_id' => $data['user_id'] ?? null,
            'target_role' => $data['target_role'] ?? null,
            'operational_task_id' => $data['operational_task_id'] ?? null,
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info',
            'category' => $category,
            'reference_code' => $referenceCode,
            'deep_link' => $deepLink,
            'action_label' => $data['action_label'] ?? 'Atender Folio en 1 Clic',
            'is_read' => false,
            'read_at' => null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Emite una alerta critica de inventario bajo Punto de Reorden (ROP).
     */
    public function dispatchInventoryRopAlert(
        string $sku,
        int $currentStock,
        int $ropThreshold,
        ?string $productName = null
    ): UserNotification {
        $nameStr = $productName ? " ({$productName})" : '';
        return $this->dispatch([
            'title' => "Alerta ROP: Stock Critico en {$sku}",
            'message' => "La referencia {$sku}{$nameStr} cuenta con solo {$currentStock} unidades disponibles (Umbral ROP: {$ropThreshold}). Requiere reabastecimiento urgente.",
            'type' => 'critical',
            'category' => 'inventory',
            'target_role' => 'warehouse',
            'reference_code' => $sku,
            'action_label' => 'Auditar ROP en Inventario',
            'metadata' => [
                'sku' => $sku,
                'current_stock' => $currentStock,
                'rop_threshold' => $ropThreshold,
            ],
        ]);
    }

    /**
     * Emite una alerta de logistica por guia de envio o manifiesto bloqueado.
     */
    public function dispatchLogisticsBlockedAlert(
        string $guia,
        string $carrier,
        string $reason
    ): UserNotification {
        return $this->dispatch([
            'title' => "Bloqueo Logistico: Guia {$guia}",
            'message' => "El envio con transportadora {$carrier} (Folio: {$guia}) fue retenido: {$reason}.",
            'type' => 'urgent',
            'category' => 'logistics',
            'target_role' => 'logistics',
            'reference_code' => $guia,
            'action_label' => 'Mesa de Expedicion',
            'metadata' => [
                'carrier' => $carrier,
                'reason' => $reason,
            ],
        ]);
    }

    /**
     * Emite una alerta financiera de retencion o matching SPEI (TASK-FIN-02).
     */
    public function dispatchFinanceHoldAlert(
        string $orderFolio,
        float $amount,
        string $reason
    ): UserNotification {
        $formattedAmount = number_format($amount, 2);
        return $this->dispatch([
            'title' => "Retencion Financiera: Pedido {$orderFolio}",
            'message' => "El pedido {$orderFolio} por un monto de \${$formattedAmount} MXN requiere revision contable: {$reason}.",
            'type' => 'warning',
            'category' => 'finance',
            'target_role' => 'finance',
            'reference_code' => $orderFolio,
            'action_label' => 'Conciliar en Finanzas',
            'metadata' => [
                'order_folio' => $orderFolio,
                'amount' => $amount,
                'reason' => $reason,
            ],
        ]);
    }

    /**
     * Emite una alerta de orden de compra retrasada o discrepancia de proveedor.
     */
    public function dispatchPurchasesDelayedAlert(
        string $poCode,
        string $supplierName,
        string $detail
    ): UserNotification {
        return $this->dispatch([
            'title' => "Demora en OC {$poCode}",
            'message' => "La orden de compra {$poCode} asignada al proveedor {$supplierName} presenta incidencia: {$detail}.",
            'type' => 'warning',
            'category' => 'purchases',
            'target_role' => 'purchases',
            'reference_code' => $poCode,
            'action_label' => 'Ver Orden de Compra',
            'metadata' => [
                'po_code' => $poCode,
                'supplier' => $supplierName,
            ],
        ]);
    }

    /**
     * Convierte y sincroniza una tarea operativa (OperationalTask) en una notificacion navegable.
     */
    public function dispatchFromOperationalTask(OperationalTask $task): UserNotification
    {
        $severity = match ($task->priority) {
            'critical' => 'critical',
            'high' => 'urgent',
            'low' => 'info',
            default => 'warning',
        };

        // Mapear categoria a modulo estandar
        $category = match (true) {
            str_contains($task->category, 'inventory') || str_contains($task->category, 'rop') || str_contains($task->category, 'biohazard') => 'inventory',
            str_contains($task->category, 'fulfillment') || str_contains($task->category, 'shipping') || str_contains($task->category, 'manifest') => 'logistics',
            str_contains($task->category, 'finance') || str_contains($task->category, 'settlement') || str_contains($task->category, 'spei') => 'finance',
            str_contains($task->category, 'purchas') || str_contains($task->category, 'procurement') => 'purchases',
            str_contains($task->category, 'rma') => 'logistics',
            default => 'general',
        };

        return $this->dispatch([
            'title' => $task->title,
            'message' => $task->description ?: "Tarea operativa pendiente de atencion (Prioridad: {$task->priority}).",
            'type' => $severity,
            'category' => $category,
            'target_role' => $task->assigned_role,
            'reference_code' => $task->reference_code,
            'operational_task_id' => $task->id,
            'action_label' => 'Atender Tarea en 1 Clic',
            'metadata' => [
                'operational_task_id' => $task->id,
                'category' => $task->category,
                'sla_status' => $task->sla_status,
            ],
        ]);
    }

    /**
     * Obtiene el feed completo categorizado para la campanita del navbar del usuario autenticado.
     */
    public function getFeedForUser(
        User $user,
        ?string $category = null,
        bool $unreadOnly = false,
        int $limit = 25
    ): array {
        $query = UserNotification::forUserOrRole($user);

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($unreadOnly) {
            $query->unread();
        }

        $allNotifications = $query->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $unreadCount = UserNotification::forUserOrRole($user)->unread()->count();
        $totalCount = UserNotification::forUserOrRole($user)->count();

        // Categorizacion por pestanas UI
        $critical = $allNotifications->filter(fn($n) => in_array($n->type, ['critical', 'urgent'], true))->values();
        $info = $allNotifications->filter(fn($n) => !in_array($n->type, ['critical', 'urgent'], true))->values();

        return [
            'unread_count' => $unreadCount,
            'total' => $totalCount,
            'items' => $allNotifications->values(),
            'categories' => [
                'all' => $allNotifications->values(),
                'critical' => $critical,
                'info' => $info,
            ],
        ];
    }

    /**
     * Marca una notificacion individual como leida garantizando autorizacion.
     */
    public function markAsRead(int $notificationId, User $user): bool
    {
        $notification = UserNotification::forUserOrRole($user)->find($notificationId);

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Marca todas las notificaciones pendientes del usuario/rol como leidas.
     */
    public function markAllAsRead(User $user): int
    {
        return UserNotification::forUserOrRole($user)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Obtiene el total de notificaciones no leidas para el contador reactivo.
     */
    public function getUnreadCount(User $user): int
    {
        return UserNotification::forUserOrRole($user)->unread()->count();
    }
}

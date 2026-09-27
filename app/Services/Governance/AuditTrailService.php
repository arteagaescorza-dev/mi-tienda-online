<?php

namespace App\Services\Governance;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SystemAuditLog;
use App\Models\ThreeWayMatching;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditTrailService
{
    /**
     * Registra un evento de auditoria inmutable en el sistema (TASK-DSH-09).
     */
    public function logAction(
        string $action,
        string $module,
        ?string $referenceCode = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?Model $entity = null,
        ?User $user = null
    ): SystemAuditLog {
        $actor = $user ?: (auth()->check() ? auth()->user() : null);

        $userName = $actor ? $actor->name : 'Sistema';
        $role = $actor ? ($actor->active_role ?? $actor->role ?? 'system') : 'system';

        return SystemAuditLog::create([
            'user_id' => $actor ? $actor->id : null,
            'user_name' => $userName,
            'role' => $role,
            'action' => $action,
            'module' => $module,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity ? $entity->getKey() : null,
            'reference_code' => $referenceCode,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'ip_address' => request()->ip() ?: '127.0.0.1',
            'user_agent' => request()->userAgent() ?: 'CLI/Internal',
            'created_at' => now(),
        ]);
    }

    /**
     * Registra un cambio en el precio de venta de un producto o variante.
     */
    public function logPriceChange(
        ProductVariant|Product $product,
        float $oldPrice,
        float $newPrice,
        string $reason,
        ?User $user = null
    ): SystemAuditLog {
        $sku = $product->sku ?? ('PROD-' . $product->id);

        return $this->logAction(
            action: 'price_change',
            module: 'inventory',
            referenceCode: $sku,
            oldValues: ['price' => $oldPrice],
            newValues: ['price' => $newPrice],
            description: "Modificacion de precio para SKU {$sku}: de \${$oldPrice} a \${$newPrice}. Motivo: {$reason}",
            entity: $product,
            user: $user
        );
    }

    /**
     * Registra un ajuste manual o rectificacion en el inventario fisico.
     */
    public function logStockAdjustment(
        Inventory $inventory,
        int $oldQty,
        int $newQty,
        string $reason,
        ?User $user = null
    ): SystemAuditLog {
        $sku = $inventory->variant ? $inventory->variant->sku : ('INV-' . $inventory->id);

        return $this->logAction(
            action: 'manual_stock_adjustment',
            module: 'inventory',
            referenceCode: $sku,
            oldValues: ['quantity' => $oldQty],
            newValues: ['quantity' => $newQty],
            description: "Ajuste de inventario para {$sku} en ubicacion {$inventory->location}: de {$oldQty} a {$newQty}. Motivo: {$reason}",
            entity: $inventory,
            user: $user
        );
    }

    /**
     * Registra el desbloqueo y autorizacion de pago SPEI tras solventar una discrepancia.
     */
    public function logSpeiUnblock(
        ThreeWayMatching $matching,
        string $paymentRef,
        ?User $user = null
    ): SystemAuditLog {
        return $this->logAction(
            action: 'spei_unblock',
            module: 'finance',
            referenceCode: $matching->po_code,
            oldValues: [
                'match_status' => 'discrepancy_held',
                'spei_payout_blocked' => 1,
            ],
            newValues: [
                'match_status' => 'approved_for_payment',
                'spei_payout_blocked' => 0,
                'payment_reference' => $paymentRef,
                'payable_net_amount' => $matching->payable_net_amount,
            ],
            description: "Autorizacion forzada de dispersion SPEI para OC {$matching->po_code} con referencia {$paymentRef}",
            entity: $matching,
            user: $user
        );
    }

    /**
     * Registra la sobrescritura de estatus de una orden por parte de un administrador.
     */
    public function logStatusOverride(
        Order $order,
        string $oldStatus,
        string $newStatus,
        string $reason,
        ?User $user = null
    ): SystemAuditLog {
        return $this->logAction(
            action: 'status_override',
            module: 'orders',
            referenceCode: $order->order_number,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus],
            description: "Sobrescritura manual de estado de orden {$order->order_number}: de {$oldStatus} a {$newStatus}. Motivo: {$reason}",
            entity: $order,
            user: $user
        );
    }

    /**
     * Registra la conmutacion de rol operativo en sesion.
     */
    public function logRoleSwitch(
        User $user,
        string $previousRole,
        string $newRole
    ): SystemAuditLog {
        return $this->logAction(
            action: 'role_switch',
            module: 'governance',
            referenceCode: $user->email,
            oldValues: ['active_role' => $previousRole],
            newValues: ['active_role' => $newRole],
            description: "Conmutacion de rol para usuario {$user->name}: de {$previousRole} a {$newRole}",
            entity: $user,
            user: $user
        );
    }

    /**
     * Consulta paginada y filtrada del historial de auditoria.
     */
    public function getAuditHistory(
        ?string $module = null,
        ?string $action = null,
        ?string $term = null,
        int $perPage = 25
    ): LengthAwarePaginator {
        return SystemAuditLog::query()
            ->forModule($module)
            ->forAction($action)
            ->search($term)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtiene metricas globales de auditoria para la cabina de gobernanza.
     */
    public function getAuditStats(): array
    {
        $today = today();

        return [
            'total_events' => SystemAuditLog::count(),
            'events_today' => SystemAuditLog::whereDate('created_at', $today)->count(),
            'price_changes_count' => SystemAuditLog::where('action', 'price_change')->count(),
            'stock_adjustments_count' => SystemAuditLog::where('action', 'manual_stock_adjustment')->count(),
            'spei_unblocks_count' => SystemAuditLog::where('action', 'spei_unblock')->count(),
            'status_overrides_count' => SystemAuditLog::where('action', 'status_override')->count(),
        ];
    }
}

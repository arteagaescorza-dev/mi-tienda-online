<?php

namespace App\Services;

use App\Models\StockLock;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Exception;

class StockReservationService
{
    /**
     * Bloquea y reserva stock temporalmente para un carrito activo en checkout (TASK-INT-06).
     * TTL predeterminado: 5 minutos (300 segundos) segun especificacion PWA.
     */
    public function reserveStock(string $cartId, int $variantId, int $quantity = 1, ?int $userId = null, int $durationMinutes = 5): StockLock
    {
        return DB::transaction(function () use ($cartId, $variantId, $quantity, $userId, $durationMinutes) {
            $inventory = Inventory::where('variant_id', $variantId)
                ->where('location', 'warehouse-1')
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw new Exception("No existe registro de inventario para la variante #{$variantId}");
            }

            // Candado activo existente para el mismo carrito y variante
            $existingLock = StockLock::where('cart_id', $cartId)
                ->where('product_variant_id', $variantId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            $previouslyReserved = $existingLock ? $existingLock->quantity : 0;
            $quantityDelta = $quantity - $previouslyReserved;

            $available = $inventory->quantity - $inventory->reserved;
            if ($quantityDelta > 0 && $available < $quantityDelta) {
                throw new Exception("Stock disponible insuficiente ({$available} unidades disponibles, se solicitaron {$quantityDelta} adicionales)");
            }

            // Ajustar inventario reservado
            $inventory->reserved = max(0, $inventory->reserved + $quantityDelta);
            $inventory->save();

            // Actualizar candado existente o crear uno nuevo
            if ($existingLock) {
                $existingLock->quantity = $quantity;
                $existingLock->expires_at = now()->addMinutes($durationMinutes);
                $existingLock->save();
                return $existingLock;
            }

            $lock = StockLock::create([
                'cart_id' => $cartId,
                'user_id' => $userId,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'expires_at' => now()->addMinutes($durationMinutes),
                'status' => 'active',
            ]);

            return $lock;
        });
    }

    /**
     * Libera un candado de stock especifico devolviendo las unidades al pool disponible.
     */
    public function releaseStockLock(StockLock|int $stockLock): bool
    {
        return DB::transaction(function () use ($stockLock) {
            $lock = $stockLock instanceof StockLock ? $stockLock : StockLock::find($stockLock);
            if (!$lock || $lock->status !== 'active') {
                return false;
            }

            $inventory = Inventory::where('variant_id', $lock->product_variant_id)
                ->where('location', 'warehouse-1')
                ->lockForUpdate()
                ->first();

            if ($inventory) {
                $inventory->reserved = max(0, $inventory->reserved - $lock->quantity);
                $inventory->save();
            }

            $lock->status = 'released';
            $lock->save();

            return true;
        });
    }

    /**
     * Libera todos los candados de stock activos asociados a un carrito, opcionalmente filtrando por variante.
     */
    public function releaseStockForCart(string $cartId, ?int $variantId = null): int
    {
        return DB::transaction(function () use ($cartId, $variantId) {
            $query = StockLock::where('cart_id', $cartId)
                ->where('status', 'active');

            if ($variantId !== null) {
                $query->where('product_variant_id', $variantId);
            }

            $locks = $query->lockForUpdate()->get();
            $count = 0;

            foreach ($locks as $lock) {
                $inventory = Inventory::where('variant_id', $lock->product_variant_id)
                    ->where('location', 'warehouse-1')
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $inventory->reserved = max(0, $inventory->reserved - $lock->quantity);
                    $inventory->save();
                }

                $lock->status = 'released';
                $lock->save();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Devuelve el resumen de reservas activas para un carrito (TTL, tiempo restante, estado).
     */
    public function getCartReservationSummary(string $cartId): array
    {
        $activeLocks = StockLock::where('cart_id', $cartId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->get();

        if ($activeLocks->isEmpty()) {
            return [
                'has_active_reservation' => false,
                'min_remaining_seconds' => 0,
                'expires_at' => null,
                'locked_variants_count' => 0,
            ];
        }

        $minRemaining = null;
        $latestExpiresAt = null;

        foreach ($activeLocks as $lock) {
            $remaining = max(0, (int) now()->diffInSeconds($lock->expires_at, false));
            if ($minRemaining === null || $remaining < $minRemaining) {
                $minRemaining = $remaining;
            }
            if ($latestExpiresAt === null || $lock->expires_at->gt($latestExpiresAt)) {
                $latestExpiresAt = $lock->expires_at;
            }
        }

        return [
            'has_active_reservation' => true,
            'min_remaining_seconds' => $minRemaining ?? 0,
            'expires_at' => $latestExpiresAt ? $latestExpiresAt->toIso8601String() : null,
            'locked_variants_count' => $activeLocks->count(),
        ];
    }

    /**
     * Convierte la reserva temporal en venta formal tras confirmación exitosa del pago.
     */
    public function commitStockOnPurchase(string $cartId, int $orderId, ?int $userId = null): void
    {
        DB::transaction(function () use ($cartId, $orderId, $userId) {
            $locks = StockLock::where('cart_id', $cartId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($locks as $lock) {
                $inventory = Inventory::where('variant_id', $lock->product_variant_id)
                    ->where('location', 'warehouse-1')
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $prevQty = $inventory->quantity;
                    $qtySold = $lock->quantity;

                    // Descontar físico y descontar reservado
                    $inventory->quantity = max(0, $prevQty - $qtySold);
                    $inventory->reserved = max(0, $inventory->reserved - $qtySold);
                    $inventory->save();

                    // Asentar en Kardex
                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'user_id' => $userId,
                        'quantity' => -$qtySold,
                        'quantity_before' => $prevQty,
                        'quantity_after' => $inventory->quantity,
                        'reason' => "SALIDA_VENTA Orden #{$orderId} - Confirmación Checkout",
                    ]);
                }

                $lock->status = 'purchased';
                $lock->save();
            }

            event(new \App\Events\Inventory\OrderStockCommitted($cartId, $orderId, $userId));
        });
    }

    /**
     * Limpia y libera automáticamente todos los bloqueos expirados por abandono de checkout.
     */
    public function cleanupExpiredLocks(): int
    {
        $expiredLocks = StockLock::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        $releasedCount = 0;
        foreach ($expiredLocks as $lock) {
            if ($this->releaseStockLock($lock)) {
                $releasedCount++;
            }
        }

        return $releasedCount;
    }
}

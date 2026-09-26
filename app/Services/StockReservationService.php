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
     */
    public function reserveStock(string $cartId, int $variantId, int $quantity = 1, ?int $userId = null, int $durationMinutes = 15): StockLock
    {
        return DB::transaction(function () use ($cartId, $variantId, $quantity, $userId, $durationMinutes) {
            $inventory = Inventory::where('variant_id', $variantId)
                ->where('location', 'warehouse-1')
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw new Exception("No existe registro de inventario para la variante #{$variantId}");
            }

            $available = $inventory->quantity - $inventory->reserved;
            if ($available < $quantity) {
                throw new Exception("Stock disponible insuficiente ({$available} unidades disponibles, se solicitaron {$quantity})");
            }

            // Incrementar reserva
            $inventory->reserved += $quantity;
            $inventory->save();

            // Crear o renovar candado de stock
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
     * Libera un candado de stock específico devolviendo las unidades al pool disponible.
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

            if ($inventory && $inventory->reserved >= $lock->quantity) {
                $inventory->reserved -= $lock->quantity;
                $inventory->save();
            }

            $lock->status = 'released';
            $lock->save();

            return true;
        });
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

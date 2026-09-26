<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\StockLock;
use Illuminate\Support\Facades\DB;
use Exception;

class CartService
{
    protected StockReservationService $reservationService;

    public function __construct(StockReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Resuelve o crea la entidad de carrito asociada a una sesion o usuario.
     */
    public function resolveCart(string $cartSessionId, ?int $userId = null): Cart
    {
        $query = Cart::query()->where('status', 'active');

        if ($userId) {
            $cart = $query->where('user_id', $userId)->first();
            if ($cart) {
                if ($cart->session_id !== $cartSessionId) {
                    $cart->session_id = $cartSessionId;
                    $cart->save();
                }
                return $cart;
            }
        }

        $cart = Cart::where('session_id', $cartSessionId)
            ->where('status', 'active')
            ->first();

        if ($cart) {
            if ($userId && !$cart->user_id) {
                $cart->user_id = $userId;
                $cart->save();
            }
            return $cart;
        }

        return Cart::create([
            'session_id' => $cartSessionId,
            'user_id' => $userId,
            'status' => 'active',
            'totals' => [],
        ]);
    }

    /**
     * Obtiene el desglose completo del carrito con clasificacion mixta (Linea A vs Linea B)
     * y telemetria de reserva temporal de stock (TASK-INT-06).
     */
    public function getCartDetails(string $cartSessionId, ?int $userId = null): array
    {
        // Limpiar candados expirados en caliente
        $this->reservationService->cleanupExpiredLocks();

        $cart = $this->resolveCart($cartSessionId, $userId);
        $cart->load(['items.variant.product', 'items.variant.inventories']);

        $lineAItems = [];
        $lineBItems = [];
        $rawSubtotal = 0.0;
        $totalItems = 0;

        $now = now();
        $reservationSummary = $this->reservationService->getCartReservationSummary($cartSessionId);

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            if (!$variant) {
                continue;
            }

            $product = $variant->product;
            $qty = (int) $item->quantity;
            $unitPrice = (float) $item->price;
            $totalItemPrice = $unitPrice * $qty;
            $rawSubtotal += $totalItemPrice;
            $totalItems += $qty;

            // Consultar inventario fisico en warehouse-1
            $inv = $variant->inventories->firstWhere('location', 'warehouse-1');
            $physQty = $inv ? (int) $inv->quantity : 0;
            $resQty = $inv ? (int) $inv->reserved : 0;
            $availStock = max(0, $physQty - $resQty);

            // Verificar si tiene candado activo en stock_locks
            $activeLock = StockLock::where('cart_id', $cartSessionId)
                ->where('product_variant_id', $variant->id)
                ->where('status', 'active')
                ->where('expires_at', '>', $now)
                ->first();

            $hasActiveLock = $activeLock !== null;
            $lockRemaining = $activeLock ? max(0, (int) $now->diffInSeconds($activeLock->expires_at, false)) : 0;

            // Clasificacion: Linea A (Bodega local, entrega 24-48h) vs Linea B (Taller On-Demand, confeccion 3-5 dias)
            $isLineA = $hasActiveLock || ($availStock > 0);
            $lineType = $isLineA ? 'line_a' : 'line_b';
            $lineLabel = $isLineA
                ? 'Bodega Local (Entrega Express 24-48h)'
                : 'Taller On-Demand (Confeccion Artesanal 3-5 dias)';
            $stockBadge = $isLineA ? 'Stock Fisico' : 'Bajo Pedido';

            $size = strtoupper(trim((string) data_get($variant->attributes, 'size', 'M')));
            $color = data_get($variant->attributes, 'color', 'Unico');

            $image = data_get($product->meta, 'image') ?: (data_get($product->meta, 'images.0') ?: '/user-avatar.png');
            $image = str_replace('/./', '/', $image);

            $itemData = [
                'id' => $item->id,
                'cart_id' => $cart->id,
                'variant_id' => $variant->id,
                'product_id' => $product ? $product->id : null,
                'product_name' => $product ? $product->title : 'Prenda Exclusiva',
                'product_slug' => $product ? $product->slug : '',
                'sku' => $variant->sku,
                'size' => $size,
                'color' => $color,
                'image' => $image,
                'quantity' => $qty,
                'price' => $unitPrice,
                'price_formatted' => 'MXN $' . number_format($unitPrice, 2),
                'total' => $totalItemPrice,
                'total_formatted' => 'MXN $' . number_format($totalItemPrice, 2),
                'line_type' => $lineType,
                'line_label' => $lineLabel,
                'stock_badge' => $stockBadge,
                'physical_stock' => $physQty,
                'available_stock' => $availStock,
                'has_active_lock' => $hasActiveLock,
                'lock_expires_at' => $activeLock ? $activeLock->expires_at->toIso8601String() : null,
                'lock_remaining_seconds' => $lockRemaining,
            ];

            if ($isLineA) {
                $lineAItems[] = $itemData;
            } else {
                $lineBItems[] = $itemData;
            }
        }

        $freeShippingThreshold = 999.00;
        $isFreeShipping = $rawSubtotal >= $freeShippingThreshold;
        $amountForFreeShipping = max(0.0, $freeShippingThreshold - $rawSubtotal);
        $freeShippingProgress = min(100, (int) round(($rawSubtotal / $freeShippingThreshold) * 100));

        $hasLineA = count($lineAItems) > 0;
        $hasLineB = count($lineBItems) > 0;
        $isMixedCart = $hasLineA && $hasLineB;

        return [
            'success' => true,
            'cart_id' => (string) $cartSessionId,
            'database_cart_id' => $cart->id,
            'summary' => [
                'total_items' => $totalItems,
                'subtotal' => $rawSubtotal,
                'subtotal_formatted' => 'MXN $' . number_format($rawSubtotal, 2),
                'free_shipping_threshold' => $freeShippingThreshold,
                'free_shipping_eligible' => $isFreeShipping,
                'amount_for_free_shipping' => $amountForFreeShipping,
                'amount_for_free_shipping_formatted' => 'MXN $' . number_format($amountForFreeShipping, 2),
                'free_shipping_progress_pct' => $freeShippingProgress,
                'has_line_a' => $hasLineA,
                'has_line_b' => $hasLineB,
                'is_mixed_cart' => $isMixedCart,
                'line_a_count' => count($lineAItems),
                'line_b_count' => count($lineBItems),
                'reservation' => $reservationSummary,
            ],
            'items' => array_merge($lineAItems, $lineBItems),
            'line_a_items' => $lineAItems,
            'line_b_items' => $lineBItems,
        ];
    }

    /**
     * Agrega un articulo al carrito con reserva temporal automatica de stock si hay inventario fisico.
     */
    public function addItem(string $cartSessionId, int $variantId, int $quantity = 1, ?int $userId = null): array
    {
        $variant = ProductVariant::with(['inventories', 'product'])->findOrFail($variantId);
        $cart = $this->resolveCart($cartSessionId, $userId);

        DB::transaction(function () use ($cart, $variant, $quantity, $cartSessionId, $userId) {
            $item = CartItem::where('cart_id', $cart->id)
                ->where('variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = $item ? ($item->quantity + $quantity) : $quantity;

            if ($item) {
                $item->quantity = $newQuantity;
                $item->price = $variant->price;
                $item->save();
            } else {
                $cart->items()->create([
                    'variant_id' => $variant->id,
                    'quantity' => $newQuantity,
                    'price' => $variant->price,
                    'added_from' => 'storefront_pwa',
                ]);
            }

            // Verificar si tiene stock en warehouse-1
            $inv = $variant->inventories->firstWhere('location', 'warehouse-1');
            if ($inv) {
                $avail = $inv->quantity - $inv->reserved;
                // Si hay stock o ya tenia lock activo, reservamos
                $existingLock = StockLock::where('cart_id', $cartSessionId)
                    ->where('product_variant_id', $variant->id)
                    ->where('status', 'active')
                    ->first();

                if ($avail > 0 || $existingLock) {
                    try {
                        $this->reservationService->reserveStock($cartSessionId, $variant->id, $newQuantity, $userId, 5);
                    } catch (Exception $e) {
                        // Si no hay suficiente para la cantidad completa, se intenta reservar lo disponible o se deja como on_demand
                    }
                }
            }
        });

        return $this->getCartDetails($cartSessionId, $userId);
    }

    /**
     * Actualiza la cantidad de un articulo ajustando el candado de stock correspondiente.
     */
    public function updateItemQuantity(string $cartSessionId, int $cartItemId, int $quantity, ?int $userId = null): array
    {
        $cart = $this->resolveCart($cartSessionId, $userId);

        DB::transaction(function () use ($cart, $cartItemId, $quantity, $cartSessionId, $userId) {
            $item = CartItem::where('cart_id', $cart->id)
                ->where('id', $cartItemId)
                ->lockForUpdate()
                ->first();

            if (!$item) {
                return;
            }

            if ($quantity <= 0) {
                $this->reservationService->releaseStockForCart($cartSessionId, $item->variant_id);
                $item->delete();
                return;
            }

            $item->quantity = $quantity;
            $item->save();

            // Ajustar candado si aplica
            $inv = $item->variant->inventories->firstWhere('location', 'warehouse-1');
            if ($inv) {
                try {
                    $this->reservationService->reserveStock($cartSessionId, $item->variant_id, $quantity, $userId, 5);
                } catch (Exception $e) {
                    // Si excede el disponible fisico, se mantiene el item
                }
            }
        });

        return $this->getCartDetails($cartSessionId, $userId);
    }

    /**
     * Elimina un articulo del carrito y libera cualquier candado de stock activo.
     */
    public function removeItem(string $cartSessionId, int $cartItemId, ?int $userId = null): array
    {
        $cart = $this->resolveCart($cartSessionId, $userId);

        DB::transaction(function () use ($cart, $cartItemId, $cartSessionId) {
            $item = CartItem::where('cart_id', $cart->id)
                ->where('id', $cartItemId)
                ->first();

            if ($item) {
                $this->reservationService->releaseStockForCart($cartSessionId, $item->variant_id);
                $item->delete();
            }
        });

        return $this->getCartDetails($cartSessionId, $userId);
    }

    /**
     * Vacia el carrito completo y libera todos los candados de stock activos.
     */
    public function clearCart(string $cartSessionId, ?int $userId = null): array
    {
        $cart = $this->resolveCart($cartSessionId, $userId);

        DB::transaction(function () use ($cart, $cartSessionId) {
            $this->reservationService->releaseStockForCart($cartSessionId);
            $cart->items()->delete();
        });

        return $this->getCartDetails($cartSessionId, $userId);
    }

    /**
     * Renueva o adquiere los candados de stock para todos los articulos fisicos del carrito.
     */
    public function lockCart(string $cartSessionId, ?int $userId = null, int $durationMinutes = 5): array
    {
        $cart = $this->resolveCart($cartSessionId, $userId);
        $cart->load('items.variant.inventories');

        DB::transaction(function () use ($cart, $cartSessionId, $userId, $durationMinutes) {
            foreach ($cart->items as $item) {
                $variant = $item->variant;
                if (!$variant) continue;

                $inv = $variant->inventories->firstWhere('location', 'warehouse-1');
                if ($inv) {
                    try {
                        $this->reservationService->reserveStock($cartSessionId, $variant->id, $item->quantity, $userId, $durationMinutes);
                    } catch (Exception $e) {
                        // Ignorar items sin disponibilidad
                    }
                }
            }
        });

        return $this->getCartDetails($cartSessionId, $userId);
    }

    /**
     * Libera los candados de stock activos de un carrito sin eliminar las prendas.
     */
    public function releaseCartLocks(string $cartSessionId): array
    {
        $this->reservationService->releaseStockForCart($cartSessionId);
        return $this->getCartDetails($cartSessionId, null);
    }
}

<?php

namespace App\Services;

use App\Models\AllyCouponDirB;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderFinancialMargin;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\StockLock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class OrderProcessingService
{
    protected StockReservationService $reservationService;
    protected CartService $cartService;

    public function __construct(StockReservationService $reservationService, CartService $cartService)
    {
        $this->reservationService = $reservationService;
        $this->cartService = $cartService;
    }

    /**
     * Valida codigo postal y calcula costos, tiempos de flete y autocompletado Sepomex.
     */
    public function validatePostalCode(string $postalCode, float $subtotal = 0.0): array
    {
        $cleanZip = preg_replace('/\D/', '', $postalCode);
        $isValid = strlen($cleanZip) === 5;

        $freeThreshold = 999.00;
        $isFree = $subtotal >= $freeThreshold;
        $cost = $isFree ? 0.00 : 149.00;

        $directory = [
            '06000' => ['state' => 'Ciudad de México', 'city' => 'Cuauhtémoc', 'colonias' => ['Centro']],
            '06600' => ['state' => 'Ciudad de México', 'city' => 'Cuauhtémoc', 'colonias' => ['Juárez', 'Cuauhtémoc', 'Roma Norte']],
            '01000' => ['state' => 'Ciudad de México', 'city' => 'Álvaro Obregón', 'colonias' => ['San Ángel', 'Campestre']],
            '03100' => ['state' => 'Ciudad de México', 'city' => 'Benito Juárez', 'colonias' => ['Del Valle Centro', 'Insurgentes San Borja', 'Tlacoquemecatl']],
            '11000' => ['state' => 'Ciudad de México', 'city' => 'Miguel Hidalgo', 'colonias' => ['Lomas de Chapultepec', 'Polanco']],
            '44100' => ['state' => 'Jalisco', 'city' => 'Guadalajara', 'colonias' => ['Americana', 'Centro', 'Ladrón de Guevara', 'Moderna', 'Vallarta']],
            '45050' => ['state' => 'Jalisco', 'city' => 'Zapopan', 'colonias' => ['Ciudad del Sol', 'La Calma']],
            '64000' => ['state' => 'Nuevo León', 'city' => 'Monterrey', 'colonias' => ['Centro', 'Obrera', 'Industrial', 'Bella Vista']],
            '66220' => ['state' => 'Nuevo León', 'city' => 'San Pedro Garza García', 'colonias' => ['Del Valle', 'Santa Engracia']],
            '72000' => ['state' => 'Puebla', 'city' => 'Puebla', 'colonias' => ['Centro', 'Amor']],
            '76000' => ['state' => 'Querétaro', 'city' => 'Querétaro', 'colonias' => ['Centro', 'Carretas', 'Alamos 1ra Sección']],
            '77500' => ['state' => 'Quintana Roo', 'city' => 'Cancún', 'colonias' => ['Cancún Centro', 'Zona Hotelera']],
            '22000' => ['state' => 'Baja California', 'city' => 'Tijuana', 'colonias' => ['Zona Urbana Río Tijuana', 'Centro']],
        ];

        $location = $directory[$cleanZip] ?? null;

        return [
            'success' => $isValid,
            'postal_code' => $cleanZip,
            'is_valid' => $isValid,
            'state' => $location['state'] ?? null,
            'city' => $location['city'] ?? null,
            'colonias' => $location['colonias'] ?? [],
            'shipping_fee' => $cost,
            'shipping_fee_formatted' => $cost === 0.00 ? 'GRATIS ($0.00 MXN)' : 'MXN $' . number_format($cost, 2),
            'free_shipping_threshold' => $freeThreshold,
            'free_shipping_eligible' => $isFree,
            'estimated_delivery' => '1 a 3 dias habiles (DHL Express)',
            'courier' => 'DHL Express Nacional',
        ];
    }

    /**
     * Valida cupones estándar o cupones de afiliados B2B (Hub 7).
     */
    public function validateCoupon(string $code, float $subtotal): array
    {
        $cleanCode = strtoupper(trim($code));
        if (empty($cleanCode)) {
            return [
                'valid' => false,
                'message' => 'Codigo de cupon no proporcionado.',
            ];
        }

        // 1. Revisar cupones estandar de la tienda
        $coupon = Coupon::where('code', $cleanCode)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($coupon) {
            $minOrder = (float) ($coupon->min_order_amount ?: 0);
            if ($subtotal < $minOrder) {
                return [
                    'valid' => false,
                    'message' => 'El pedido minimo para este cupon es de MXN $' . number_format($minOrder, 2),
                ];
            }

            $discountAmount = 0.0;
            $discountPct = 0.0;

            if ($coupon->discount_type === 'percent') {
                $discountPct = (float) $coupon->discount_value;
                $discountAmount = round(($subtotal * $discountPct) / 100, 2);
            } else {
                $discountAmount = min($subtotal, (float) $coupon->discount_value);
                $discountPct = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100, 2) : 0.0;
            }

            return [
                'valid' => true,
                'type' => 'standard',
                'code' => $coupon->code,
                'discount_pct' => $discountPct,
                'discount_amount' => $discountAmount,
                'discount_amount_formatted' => 'MXN $' . number_format($discountAmount, 2),
                'coupon_id' => $coupon->id,
                'description' => 'Cupon ' . $coupon->code . ' (' . $discountPct . '% descuento)',
            ];
        }

        // 2. Revisar cupones de Aliado Dir B (Hub 7 Convenios)
        $allyCoupon = AllyCouponDirB::where('coupon_code', $cleanCode)
            ->where('status', 'active')
            ->first();

        if ($allyCoupon) {
            $discountPct = (float) $allyCoupon->discount_pct;
            $discountAmount = round(($subtotal * $discountPct) / 100, 2);
            $commissionRate = (float) $allyCoupon->commission_rate;
            $netRevenue = max(0, $subtotal - $discountAmount);
            $commissionEarned = round(($netRevenue * $commissionRate) / 100, 2);

            return [
                'valid' => true,
                'type' => 'ally_b2b',
                'code' => $allyCoupon->coupon_code,
                'discount_pct' => $discountPct,
                'discount_amount' => $discountAmount,
                'discount_amount_formatted' => 'MXN $' . number_format($discountAmount, 2),
                'ally_id' => $allyCoupon->ally_id,
                'ally_coupon_id' => $allyCoupon->id,
                'commission_rate' => $commissionRate,
                'commission_estimated' => $commissionEarned,
                'utm_source' => $allyCoupon->utm_source,
                'description' => 'Cupon de Aliado ' . $allyCoupon->coupon_code . ' (' . $discountPct . '% descuento exclusivo)',
            ];
        }

        return [
            'valid' => false,
            'message' => 'El cupon ingresado no existe o no se encuentra activo.',
        ];
    }

    /**
     * Procesa la transaccion ACID completa de checkout:
     * 1. Transmuta stock_locks a purchased
     * 2. Descuenta inventario fisico y asienta en Kardex (SALIDA_VENTA_ONLINE)
     * 3. Crea la orden con folio univoco (#ORD-2026-XXXX o #MIX-2026-XXXX)
     * 4. Asienta margen financiero en order_financial_margins
     * 5. Emite guia de envio DHL y vacia el carrito
     */
    public function processCheckout(array $data, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            $cartSessionId = $data['cart_id'] ?? $data['session_id'] ?? '';
            if (empty($cartSessionId)) {
                throw new Exception('Identificador de sesion de carrito ausente.');
            }

            $cart = Cart::where('session_id', $cartSessionId)
                ->where('status', 'active')
                ->with(['items.variant.product', 'items.variant.inventories'])
                ->lockForUpdate()
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw new Exception('El carrito esta vacio. Agrega prendas antes de proceder al checkout.');
            }

            // Identificar si el carrito es mixto (Línea A: Stock Físico vs Línea B: Taller On-Demand)
            $hasPhysical = false;
            $hasOnDemand = false;
            $rawSubtotal = 0.0;
            $totalCogs = 0.0;

            foreach ($cart->items as $item) {
                $variant = $item->variant;
                if (!$variant) continue;

                $itemSub = (float) $item->price * (int) $item->quantity;
                $rawSubtotal += $itemSub;

                $isScrapedProduct = $variant->product ? $variant->product->isScraped() : false;
                $inv = $variant->inventories->firstWhere('location', 'warehouse-1');
                $avail = $inv ? ($inv->quantity - $inv->reserved) : 0;

                // Verificar si tiene candado activo
                $hasLock = StockLock::where('cart_id', $cartSessionId)
                    ->where('product_variant_id', $variant->id)
                    ->where('status', 'active')
                    ->exists();

                if (!$isScrapedProduct && ($hasLock || $avail > 0)) {
                    $hasPhysical = true;
                } else {
                    $hasOnDemand = true;
                }

                // Costo de la prenda (COGS)
                $cost = (float) ($variant->cost ?: ($variant->product ? $variant->product->cost : $item->price * 0.4));
                $totalCogs += $cost * (int) $item->quantity;
            }

            $isMixedCart = $hasPhysical && $hasOnDemand;

            // Generar Folio Inmutable (#MIX-2026-XXXX o #ORD-2026-XXXX)
            $prefix = $isMixedCart ? '#MIX' : '#ORD';
            $randomSeq = strtoupper(Str::random(6));
            $orderNumber = "{$prefix}-2026-{$randomSeq}";

            // Validar cupon si fue enviado
            $discountAmount = 0.0;
            $appliedCoupon = null;
            $appliedAllyCoupon = null;
            $couponCode = trim((string) ($data['coupon_code'] ?? ''));

            if (!empty($couponCode)) {
                $couponResult = $this->validateCoupon($couponCode, $rawSubtotal);
                if ($couponResult['valid']) {
                    $discountAmount = $couponResult['discount_amount'];
                    if ($couponResult['type'] === 'standard') {
                        $appliedCoupon = Coupon::find($couponResult['coupon_id']);
                    } elseif ($couponResult['type'] === 'ally_b2b') {
                        $appliedAllyCoupon = AllyCouponDirB::find($couponResult['ally_coupon_id']);
                    }
                }
            }

            $subtotalAfterDiscount = max(0.0, $rawSubtotal - $discountAmount);

            // Calculo de fletes del Hub 6
            $dispatchStrategy = $data['dispatch_strategy'] ?? 'standard';
            $isSplitDispatch = ($isMixedCart && $dispatchStrategy === 'split');
            $baseShipping = ($subtotalAfterDiscount >= 999.00) ? 0.00 : 149.00;
            $splitShippingFee = $isSplitDispatch ? 149.00 : 0.00;
            $shippingTotal = $baseShipping + $splitShippingFee;

            $grandTotal = round($subtotalAfterDiscount + $shippingTotal, 2);

            // Informacion del Comprador y Envio
            $customerData = $data['customer'] ?? [];
            $shippingData = $data['shipping'] ?? [];

            $addressPayload = [
                'name' => $customerData['name'] ?? ($data['name'] ?? 'Cliente Torzo'),
                'email' => $customerData['email'] ?? ($data['email'] ?? ''),
                'phone' => $customerData['phone'] ?? ($data['mobile'] ?? ($data['phone'] ?? '')),
                'street' => $shippingData['street'] ?? ($data['street'] ?? ''),
                'exterior_number' => $shippingData['exterior_number'] ?? ($data['exterior_number'] ?? 'S/N'),
                'interior_number' => $shippingData['interior_number'] ?? ($data['interior_number'] ?? null),
                'colony' => $shippingData['colony'] ?? ($data['neighborhood'] ?? ($data['colony'] ?? '')),
                'postal_code' => $shippingData['postal_code'] ?? ($data['postal_code'] ?? '00000'),
                'city' => $shippingData['city'] ?? ($data['city'] ?? 'CDMX'),
                'state' => $shippingData['state'] ?? ($data['state'] ?? 'CDMX'),
                'references' => $shippingData['references'] ?? ($data['delivery_references'] ?? null),
            ];

            $paymentMethod = $data['payment_method'] ?? 'spei_bbva';
            $isCard = ($paymentMethod === 'credit_debit_card');

            // Registrar atribucion de Aliado B2B si aplica
            $attributionId = null;
            if ($appliedAllyCoupon) {
                $attribution = \App\Models\Attribution::firstOrCreate(
                    ['session_id' => 'ally_' . $cartSessionId],
                    [
                        'source' => $appliedAllyCoupon->utm_source ?: 'ally_b2b',
                        'campaign' => $appliedAllyCoupon->utm_campaign ?: $appliedAllyCoupon->coupon_code,
                        'medium' => 'ally_dir_b',
                        'user_id' => $userId,
                        'first_seen_at' => now(),
                        'last_activity_at' => now(),
                    ]
                );
                $attributionId = $attribution->id;
            }

            // Crear Orden
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'billing_address' => $addressPayload,
                'shipping_address' => $addressPayload,
                'status' => 'pending',
                'total' => $grandTotal,
                'payment_status' => $isCard ? 'paid' : 'pending',
                'payment_method' => $paymentMethod,
                'notes' => $isMixedCart ? 'Pedido Mixto (Bodega Local + Taller)' : 'Pedido E-commerce Directo',
                'created_via' => 'web',
                'attributed_via_id' => $attributionId,
            ]);

            // Consumir candados de stock, descontar fisico y asentar en Kardex
            foreach ($cart->items as $cartItem) {
                $variant = $cartItem->variant;
                if (!$variant) continue;

                $itemQty = (int) $cartItem->quantity;
                $activeLock = StockLock::where('cart_id', $cartSessionId)
                    ->where('product_variant_id', $variant->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                $inventory = Inventory::where('variant_id', $variant->id)
                    ->where('location', 'warehouse-1')
                    ->lockForUpdate()
                    ->first();

                $fulfillmentStatus = 'waiting';
                $isScrapedProduct = $variant->product ? $variant->product->isScraped() : false;

                if (!$isScrapedProduct && $activeLock) {
                    $lockQty = $activeLock->quantity;
                    $activeLock->status = 'purchased';
                    $activeLock->save();

                    if ($inventory) {
                        $prevQty = $inventory->quantity;
                        $inventory->quantity = max(0, $inventory->quantity - $lockQty);
                        $inventory->reserved = max(0, $inventory->reserved - $lockQty);
                        $inventory->save();

                        // Asiento inmutable en Kardex (TASK-INT-02)
                        InventoryMovement::create([
                            'inventory_id' => $inventory->id,
                            'user_id' => $userId,
                            'quantity' => -$lockQty,
                            'quantity_before' => $prevQty,
                            'quantity_after' => $inventory->quantity,
                            'reason' => 'SALIDA_VENTA_ONLINE - Pedido ' . $orderNumber,
                        ]);
                    }
                    $fulfillmentStatus = 'fulfilled';
                } elseif (!$isScrapedProduct && $inventory && ($inventory->quantity - $inventory->reserved) >= $itemQty) {
                    // Si no tenia lock pero habia stock en bodega
                    $prevQty = $inventory->quantity;
                    $inventory->quantity = max(0, $inventory->quantity - $itemQty);
                    $inventory->save();

                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'user_id' => $userId,
                        'quantity' => -$itemQty,
                        'quantity_before' => $prevQty,
                        'quantity_after' => $inventory->quantity,
                        'reason' => 'SALIDA_VENTA_ONLINE - Pedido ' . $orderNumber,
                    ]);
                    $fulfillmentStatus = 'fulfilled';
                } else {
                    // Prenda bajo pedido en taller o proveedor externo (Línea B)
                    $fulfillmentStatus = 'backorder';
                }

                // Crear Item de Orden
                $order->items()->create([
                    'variant_id' => $variant->id,
                    'quantity' => $itemQty,
                    'unit_price' => $cartItem->price,
                    'line_total' => $itemQty * $cartItem->price,
                    'fulfillment_status' => $fulfillmentStatus,
                ]);
            }

            // Desglose Contable y Margen Financiero Real (TASK-FIN-01)
            $gatewayFee = $isCard ? round(($grandTotal * 0.036) + 3.00, 2) : 0.00;
            $packagingCost = $isSplitDispatch ? 32.00 : 16.00; // 1 o 2 empaques
            $shippingCourierCost = $isSplitDispatch ? 250.00 : 125.00; // Costo real logistico
            $netProfit = round($grandTotal - $totalCogs - $gatewayFee - $packagingCost - $shippingCourierCost, 2);
            $netMarginPercent = $grandTotal > 0 ? round(($netProfit / $grandTotal) * 100, 2) : 0.00;
            $healthLevel = $netMarginPercent >= 50.0 ? 'healthy' : ($netMarginPercent >= 30.0 ? 'standard' : 'critical');

            OrderFinancialMargin::create([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'universe' => $data['universe'] ?? 'terra',
                'gross_revenue' => $grandTotal,
                'cogs_total' => $totalCogs,
                'gateway_fee' => $gatewayFee,
                'packaging_cost' => $packagingCost,
                'shipping_courier_cost' => $shippingCourierCost,
                'net_profit' => $netProfit,
                'net_margin_percent' => $netMarginPercent,
                'margin_health_level' => $healthLevel,
            ]);

            // Generacion de Guia de Envio DHL Express (Hub 6 TASK-LOG-01)
            Shipment::create([
                'order_id' => $order->id,
                'tracking_number' => 'DHL-MX-' . strtoupper(Str::random(10)),
                'courier' => 'dhl_express',
                'service_type' => 'express',
                'shipping_cost_declared' => $shippingCourierCost,
                'status' => 'label_created',
            ]);

            // Actualizar metricas de cupones
            if ($appliedCoupon) {
                $appliedCoupon->increment('used_count');
                $appliedCoupon->increment('revenue_generated', $grandTotal);
            }
            if ($appliedAllyCoupon) {
                $appliedAllyCoupon->increment('orders_count');
                $appliedAllyCoupon->increment('revenue_attributed', $grandTotal);
                $comm = round(($subtotalAfterDiscount * (float) $appliedAllyCoupon->commission_rate) / 100, 2);
                $appliedAllyCoupon->increment('commission_earned', $comm);
            }

            // Vaciar carrito y marcarlo como ordenado
            $cart->update(['status' => 'ordered']);
            $cart->items()->delete();

            return $order->load(['items.variant.product', 'financialMargin', 'shipment']);
        });
    }
}

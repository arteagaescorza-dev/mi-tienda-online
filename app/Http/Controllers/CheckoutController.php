<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderFinancialMargin;
use App\Models\Shipment;
use App\Models\Coupon;
use App\Models\AllyCouponDirB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'exterior_number' => ['required', 'string', 'max:30'],
            'interior_number' => ['nullable', 'string', 'max:30'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'digits:5'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'delivery_references' => ['nullable', 'string', 'max:1000'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'birth_date' => ['required', 'date', 'before:today'],
            'delivery_method' => ['required', 'in:paqueteria,personal'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'universe' => ['nullable', 'string', 'in:terra,marte,venus,tiresias'],
        ]);

        $order = DB::transaction(function () use ($request, $validated): Order {
            $cart = Cart::query()
                ->where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->with('items.variant.product')
                ->lockForUpdate()
                ->first();

            if (! $cart || $cart->items->isEmpty()) {
                $this->rejectCart('El carrito esta vacio.');
            }

            $totalCogs = 0.0;

            foreach ($cart->items as $item) {
                $inventories = Inventory::query()
                    ->where('variant_id', $item->variant_id)
                    ->lockForUpdate()
                    ->get();

                $available = $inventories->sum(fn (Inventory $inventory) => $inventory->quantity - $inventory->reserved);

                if ($available < $item->quantity) {
                    $this->rejectCart("Stock insuficiente para la variante {$item->variant_id}.");
                }

                $remainingQuantity = $item->quantity;

                foreach ($inventories as $inventory) {
                    $reservableQuantity = $inventory->quantity - $inventory->reserved;
                    $reservedQuantity = min($remainingQuantity, $reservableQuantity);

                    if ($reservedQuantity > 0) {
                        $inventory->increment('reserved', $reservedQuantity);
                        $remainingQuantity -= $reservedQuantity;
                    }

                    if ($remainingQuantity === 0) {
                        break;
                    }
                }

                // Cálculo de COGS
                $variantCost = $item->variant ? (float) ($item->variant->cost ?: ($item->variant->product ? $item->variant->product->cost : $item->price * 0.4)) : ($item->price * 0.4);
                $totalCogs += $variantCost * $item->quantity;
            }

            $subtotal = (float) $cart->items->sum(fn ($item) => $item->quantity * $item->price);
            $discountAmount = 0.0;
            $appliedCoupon = null;
            $appliedAllyCoupon = null;

            // 1. Evaluación de Cupón Promocional o de Aliado
            if (!empty($validated['coupon_code'])) {
                $code = strtoupper(trim($validated['coupon_code']));

                // Revisar cupones estándar
                $coupon = Coupon::where('code', $code)->where('is_active', true)->first();
                if ($coupon && $subtotal >= (float) $coupon->min_order_amount) {
                    $appliedCoupon = $coupon;
                    if ($coupon->discount_type === 'percent') {
                        $discountAmount = round(($subtotal * (float) $coupon->discount_value) / 100, 2);
                    } elseif ($coupon->discount_type === 'fixed_amount') {
                        $discountAmount = min($subtotal, (float) $coupon->discount_value);
                    }
                    $coupon->increment('used_count');
                    $coupon->increment('revenue_generated', max(0, $subtotal - $discountAmount));
                } else {
                    // Revisar cupones de Aliado Dir B
                    $allyCoupon = AllyCouponDirB::where('coupon_code', $code)->where('status', 'active')->first();
                    if ($allyCoupon) {
                        $appliedAllyCoupon = $allyCoupon;
                        $discountAmount = round(($subtotal * (float) $allyCoupon->discount_pct) / 100, 2);
                        $netOrderRevenue = max(0, $subtotal - $discountAmount);
                        $commissionEarned = round(($netOrderRevenue * (float) $allyCoupon->commission_rate) / 100, 2);

                        $allyCoupon->increment('orders_count');
                        $allyCoupon->increment('revenue_attributed', $netOrderRevenue);
                        $allyCoupon->increment('commission_earned', $commissionEarned);
                    }
                }
            }

            $total = max(0, $subtotal - $discountAmount);

            $shippingAddress = [
                'name' => $validated['name'],
                'street' => $validated['street'],
                'exterior_number' => $validated['exterior_number'],
                'interior_number' => $validated['interior_number'] ?? null,
                'neighborhood' => $validated['neighborhood'],
                'postal_code' => $validated['postal_code'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'delivery_references' => $validated['delivery_references'] ?? null,
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'birth_date' => $validated['birth_date'],
                'delivery_method' => $validated['delivery_method'],
            ];

            $orderNumber = 'ORD-' . strtoupper(Str::random(12));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $request->user()->id,
                'shipping_address' => $shippingAddress,
                'billing_address' => $shippingAddress,
                'total' => $total,
                'payment_method' => 'pending',
                'created_via' => 'web',
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'line_total' => $item->quantity * $item->price,
                ]);
            }

            // 2. Desglose y Auditoría de Margen Neto Real (TASK-FIN-01)
            $gatewayFee = round(($total * 0.036) + 3.00, 2);
            $packagingCost = 16.00; // Caja estándar E-commerce
            $shippingCourierCost = $validated['delivery_method'] === 'paqueteria' ? 125.00 : 0.00;
            $netProfit = round($total - $totalCogs - $gatewayFee - $packagingCost - $shippingCourierCost, 2);
            $marginPercent = $total > 0 ? round(($netProfit / $total) * 100, 2) : 0.0;
            $marginHealth = $marginPercent >= 50.0 ? 'healthy' : ($marginPercent >= 30.0 ? 'standard' : 'critical');

            OrderFinancialMargin::create([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'universe' => $validated['universe'] ?? 'terra',
                'gross_revenue' => $total,
                'cogs_total' => $totalCogs,
                'gateway_fee' => $gatewayFee,
                'packaging_cost' => $packagingCost,
                'shipping_courier_cost' => $shippingCourierCost,
                'net_profit' => $netProfit,
                'net_margin_percent' => $marginPercent,
                'margin_health_level' => $marginHealth,
            ]);

            // 3. Generación automática de Guía de Envío (TASK-LOG-01)
            if ($validated['delivery_method'] === 'paqueteria') {
                Shipment::create([
                    'order_id' => $order->id,
                    'tracking_number' => 'DHL-MX-' . strtoupper(Str::random(10)),
                    'courier' => 'dhl_express',
                    'service_type' => 'express',
                    'shipping_cost_declared' => 125.00,
                    'status' => 'label_created',
                ]);
            }

            $cart->update(['status' => 'ordered']);
            $cart->items()->delete();

            return $order->load(['items.variant', 'financialMargin', 'shipment']);
        });

        return response()->json(['order' => $order], 201);
    }

    private function rejectCart(string $message): never
    {
        throw new HttpResponseException(response()->json([
            'message' => $message,
            'errors' => ['cart' => [$message]],
        ], 422));
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CustomerAccountService;
use App\Services\OrderProcessingService;
use App\Services\WebPushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontApiController extends Controller
{
    protected CartService $cartService;
    protected OrderProcessingService $orderProcessingService;
    protected CustomerAccountService $customerAccountService;
    protected WebPushNotificationService $pushService;

    public function __construct(
        CartService $cartService,
        OrderProcessingService $orderProcessingService,
        CustomerAccountService $customerAccountService,
        WebPushNotificationService $pushService
    ) {
        $this->cartService = $cartService;
        $this->orderProcessingService = $orderProcessingService;
        $this->customerAccountService = $customerAccountService;
        $this->pushService = $pushService;
    }

    /**
     * Resuelve el identificador de sesion del carrito desde cabeceras, input o cookies.
     */
    protected function getCartSessionId(Request $request): string
    {
        $sid = $request->header('X-Cart-Session-Id')
            ?: ($request->input('cart_id')
            ?: ($request->cookie('multiverse_cart_session_id')
            ?: ($request->cookie('cart_session_id'))));

        if (!$sid || strlen($sid) < 3) {
            $sid = 'sess_' . Str::random(12);
        }

        return (string) $sid;
    }
    /**
     * Devuelve el detalle anatómico de un producto para la ficha PDP
     * con su matriz de tallas, stock en vivo (físico vs bajo pedido)
     * y composición textil oficial.
     */
    public function productDetail(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with([
                'variants.inventories',
                'categories',
            ])
            ->where('visible', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('id', $slug)
                    ->orWhere('sku', $slug);
            })
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Prenda no encontrada en el catálogo activo.',
            ], 404);
        }

        $variants = $product->variants;
        $totalPhysical = (int) $variants->sum(fn ($v) => $v->inventories->sum('quantity'));
        $totalReserved = (int) $variants->sum(fn ($v) => $v->inventories->sum('reserved'));
        $totalAvailable = max(0, $totalPhysical - $totalReserved);

        $sizeSubMap = [
            'CH' => ['pants' => '28-30', 'waist_cm' => '70-76 cm', 'label' => 'Chica'],
            'M' => ['pants' => '31-32', 'waist_cm' => '78-84 cm', 'label' => 'Mediana'],
            'G' => ['pants' => '33-34', 'waist_cm' => '86-92 cm', 'label' => 'Grande'],
            'XG' => ['pants' => '35-36', 'waist_cm' => '94-102 cm', 'label' => 'Extra Grande'],
            'XL' => ['pants' => '35-36', 'waist_cm' => '94-102 cm', 'label' => 'Extra Grande'],
            '2XL' => ['pants' => '38-40', 'waist_cm' => '104-110 cm', 'label' => 'Doble Extra Grande'],
            '3XL' => ['pants' => '42-44', 'waist_cm' => '112-120 cm', 'label' => 'Triple Extra Grande'],
        ];

        $variantList = $variants->map(function ($v) use ($sizeSubMap) {
            $qty = (int) $v->inventories->sum('quantity');
            $res = (int) $v->inventories->sum('reserved');
            $avail = max(0, $qty - $res);
            $size = strtoupper(trim((string) data_get($v->attributes, 'size', 'M')));

            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'size' => $size,
                'color' => data_get($v->attributes, 'color', 'Unico'),
                'price' => (float) $v->price,
                'price_formatted' => 'MXN $' . number_format((float) $v->price, 2),
                'stock_physical' => $qty,
                'stock_reserved' => $res,
                'stock_available' => $avail,
                'is_available' => $avail > 0,
                'stock_badge' => $avail > 0 ? 'Stock Físico' : 'Bajo Pedido',
                'sub_info' => $sizeSubMap[$size] ?? ['pants' => '-', 'waist_cm' => '-', 'label' => 'Talla ' . $size],
            ];
        })->values();

        // Categoría slug y universos
        $brandUpper = strtoupper((string) ($product->brand ?: data_get($product->meta, 'category', '')));
        $categorySlug = 'jocks';
        if (str_contains($brandUpper, 'BOXER') || str_contains($brandUpper, 'BRIEF')) {
            $categorySlug = 'boxers';
        } elseif (str_contains($brandUpper, 'TANGA')) {
            $categorySlug = 'tangas';
        } elseif (str_contains($brandUpper, 'BIKINI')) {
            $categorySlug = 'bikinis';
        } elseif (str_contains($brandUpper, 'TANK') || str_contains($brandUpper, 'SHIRT')) {
            $categorySlug = 'tanktops';
        } elseif (str_contains($brandUpper, 'SWIM')) {
            $categorySlug = 'swimwear';
        }

        $universes = ['marte', 'terra'];
        if (in_array($categorySlug, ['bikinis', 'swimwear'])) {
            $universes = ['marte', 'venus', 'terra'];
        } elseif ($categorySlug === 'tanktops') {
            $universes = ['marte', 'tiresias', 'terra'];
        }
        if ($product->meta && isset($product->meta['universes']) && is_array($product->meta['universes'])) {
            $universes = $product->meta['universes'];
        }

        $sizes = $variantList->pluck('size')->unique()->values()->all();
        if (empty($sizes)) {
            $sizes = ['CH', 'M', 'G', 'XG'];
        }

        $image = data_get($product->meta, 'image') ?: (data_get($product->meta, 'images.0') ?: '/user-avatar.png');
        $image = str_replace('/./', '/', $image);
        $images = data_get($product->meta, 'images', [$image]);

        $fabricComposition = data_get($product->meta, 'fabric_composition')
            ?: '95% Modal Pima peruano / 5% Elastano (Spandex) de alta recuperación. Elástico jacquard de 45mm.';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $product->id,
                'sku' => $product->sku,
                'name' => $product->title,
                'slug' => $product->slug,
                'brand' => $product->brand ?: 'Torzo',
                'category' => $categorySlug,
                'category_name' => $product->brand ?: (data_get($product->meta, 'category', 'Prenda')),
                'price' => (float) $product->price,
                'price_formatted' => 'MXN $' . number_format((float) $product->price, 2),
                'cost' => (float) ($product->cost ?: 0),
                'fabric_composition' => $fabricComposition,
                'stock_type' => $totalAvailable > 0 ? 'ready' : 'on_demand',
                'stock_label' => $totalAvailable > 0 ? 'Stock Físico' : 'Bajo Pedido',
                'total_physical_stock' => $totalPhysical,
                'total_reserved_stock' => $totalReserved,
                'total_available_stock' => $totalAvailable,
                'image' => $image,
                'images' => $images,
                'description' => $product->description ?: 'Prenda anatómica confeccionada con textiles transpirables y soporte ergonómico.',
                'universes' => $universes,
                'sizes' => $sizes,
                'variants' => $variantList,
            ],
        ]);
    }

    /**
     * Consulta el estado en vivo del carrito con desglose mixto y telemetria de candados.
     */
    public function getCart(Request $request): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $cartDetails = $this->cartService->getCartDetails($cartSessionId, auth()->id());

        return response()->json($cartDetails);
    }

    /**
     * Agrega un articulo al carrito con reserva de stock temporal inmediata.
     */
    public function addToCart(Request $request): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $quantity = max(1, (int) $request->input('quantity', 1));
        $variantId = $request->input('variant_id');

        // Si se envio product_id y size en vez de variant_id, resolver la variante
        if (!$variantId && $request->has('product_id')) {
            $product = Product::with('variants.inventories')->find($request->input('product_id'));
            if ($product) {
                $requestedSize = strtoupper(trim((string) $request->input('size', 'M')));
                $matchedVariant = $product->variants->first(function ($v) use ($requestedSize) {
                    $attrSize = strtoupper(trim((string) data_get($v->attributes, 'size', '')));
                    return $attrSize === $requestedSize || str_ends_with($v->sku, '-' . $requestedSize);
                }) ?: $product->variants->first();

                if ($matchedVariant) {
                    $variantId = $matchedVariant->id;
                }
            }
        }

        if (!$variantId) {
            return response()->json([
                'success' => false,
                'message' => 'Variante de prenda no especificada o no valida.',
            ], 422);
        }

        $cartDetails = $this->cartService->addItem($cartSessionId, (int) $variantId, $quantity, auth()->id());

        return response()->json($cartDetails);
    }

    /**
     * Actualiza la cantidad de un articulo en el carrito.
     */
    public function updateCartItem(Request $request, int $id): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $quantity = (int) $request->input('quantity', 1);

        $cartDetails = $this->cartService->updateItemQuantity($cartSessionId, $id, $quantity, auth()->id());

        return response()->json($cartDetails);
    }

    /**
     * Elimina un articulo del carrito liberando su reserva temporal de stock.
     */
    public function removeCartItem(Request $request, int $id): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $cartDetails = $this->cartService->removeItem($cartSessionId, $id, auth()->id());

        return response()->json($cartDetails);
    }

    /**
     * Vacia el carrito completo y libera todos los candados de inventario.
     */
    public function clearCart(Request $request): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $cartDetails = $this->cartService->clearCart($cartSessionId, auth()->id());

        return response()->json($cartDetails);
    }

    /**
     * Bloquea o renueva la reserva temporal de stock de los articulos del carrito.
     */
    public function lockCart(Request $request): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $durationMinutes = max(1, min(60, (int) $request->input('duration_minutes', 5)));

        $cartDetails = $this->cartService->lockCart($cartSessionId, auth()->id(), $durationMinutes);

        return response()->json($cartDetails);
    }

    /**
     * Libera los candados de stock del carrito manteniendo los articulos en la lista.
     */
    public function releaseCartLock(Request $request): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $cartDetails = $this->cartService->releaseCartLocks($cartSessionId);

        return response()->json($cartDetails);
    }

    /**
     * Valida codigo postal y calcula costos de envio con Sepomex.
     */
    public function validatePostalCode(Request $request): JsonResponse
    {
        $zip = (string) $request->input('postal_code', '');
        $subtotal = (float) $request->input('subtotal', 0.0);
        $result = $this->orderProcessingService->validatePostalCode($zip, $subtotal);

        return response()->json($result);
    }

    /**
     * Valida cupon de descuento (estandar o aliado B2B).
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $code = (string) ($request->input('code') ?: $request->input('coupon_code', ''));
        $subtotal = (float) $request->input('subtotal', 0.0);
        $result = $this->orderProcessingService->validateCoupon($code, $subtotal);

        return response()->json($result);
    }

    /**
     * Procesa la transaccion de compra integral con creacion de Orden y Kardex.
     */
    public function checkout(Request $request): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $payload = $request->all();
        $payload['cart_id'] = $cartSessionId;

        try {
            $order = $this->orderProcessingService->processCheckout($payload, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Compra formalizada con exito.',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => (float) $order->total,
                    'total_formatted' => 'MXN $' . number_format((float) $order->total, 2),
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'shipping_address' => $order->shipping_address,
                    'created_at' => $order->created_at->toIso8601String(),
                    'tracking_number' => $order->shipment ? $order->shipment->tracking_number : null,
                    'financial_margin' => $order->financialMargin ? [
                        'net_profit' => (float) $order->financialMargin->net_profit,
                        'margin_health' => $order->financialMargin->margin_health_level,
                    ] : null,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Consulta pedidos para el portal del comprador (por sesion/email o folio).
     */
    public function orders(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $email = $request->query('email');
        $folio = $request->query('folio');

        $orders = $this->customerAccountService->getOrders($userId, $email, $folio);

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Consulta telemetria de envio y tracking logistico DHL en tiempo real.
     */
    public function tracking(string $folio): JsonResponse
    {
        $tracking = $this->customerAccountService->getTracking($folio);

        if (!$tracking['found']) {
            return response()->json([
                'success' => false,
                'message' => $tracking['message'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'tracking' => $tracking,
        ]);
    }

    /**
     * Consulta suscripciones recurrentes y planes disponibles MRR.
     */
    public function subscriptions(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $email = $request->query('email');

        $data = $this->customerAccountService->getSubscriptions($userId, $email);

        return response()->json($data);
    }

    /**
     * Pausa una suscripcion mensual activa.
     */
    public function pauseSubscription(int $id): JsonResponse
    {
        try {
            $subscription = $this->customerAccountService->pauseSubscription($id);

            return response()->json([
                'success' => true,
                'message' => 'Suscripcion pausada correctamente.',
                'subscription' => $subscription,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Reactiva una suscripcion mensual previamente pausada.
     */
    public function resumeSubscription(int $id): JsonResponse
    {
        try {
            $subscription = $this->customerAccountService->resumeSubscription($id);

            return response()->json([
                'success' => true,
                'message' => 'Suscripcion reactivada correctamente. Proxima entrega programada.',
                'subscription' => $subscription,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Formaliza una nueva suscripcion mensual MRR.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tier_name' => 'nullable|string',
            'monthly_amount' => 'nullable|numeric|min:0',
            'universe' => 'nullable|string',
            'card_last4' => 'nullable|string|max:4',
            'card_brand' => 'nullable|string',
        ]);

        $subscription = $this->customerAccountService->subscribe($validated, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Suscripcion MRR activada exitosamente.',
            'subscription' => $subscription,
        ], 201);
    }

    /**
     * Actualiza el perfil anatomico del comprador.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'waist_cm' => 'required|integer|min:50|max:160',
            'preferred_cut' => 'nullable|string',
            'silhouettes' => 'nullable|array',
        ]);

        $result = $this->customerAccountService->updateProfile($validated, auth()->id());

        return response()->json($result);
    }

    /**
     * Registra suscripcion a notificaciones Web Push PWA (TASK-STR-06).
     */
    public function pushSubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'nullable|array',
            'p256dh_key' => 'nullable|string',
            'auth_token' => 'nullable|string',
            'topics' => 'nullable|array',
        ]);

        $cartSessionId = $this->getCartSessionId($request);
        $subscription = $this->pushService->subscribe($validated, auth()->id(), $cartSessionId);

        return response()->json([
            'success' => true,
            'message' => 'Suscripcion Web Push activada exitosamente.',
            'subscription' => [
                'id' => $subscription->id,
                'endpoint' => $subscription->endpoint,
                'topics' => $subscription->topics,
                'is_active' => $subscription->is_active,
            ],
        ], 201);
    }

    /**
     * Da de baja suscripcion a notificaciones Web Push PWA.
     */
    public function pushUnsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $success = $this->pushService->unsubscribe($validated['endpoint']);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Suscripcion Web Push dada de baja.' : 'Suscripcion no encontrada.',
        ]);
    }

    /**
     * Envia notificacion de prueba y asienta registro auditable en BD.
     */
    public function pushTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $log = $this->pushService->sendTestNotification($validated['endpoint']);

        return response()->json([
            'success' => true,
            'message' => 'Notificacion push de prueba enviada exitosamente.',
            'log' => [
                'id' => $log->id,
                'type' => $log->notification_type,
                'title' => $log->title,
                'body' => $log->body,
                'delivery_status' => $log->delivery_status,
            ],
        ]);
    }
}

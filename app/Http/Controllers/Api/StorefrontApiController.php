<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CustomerAccountService;
use App\Services\OrderProcessingService;
use App\Services\Storefront\StorefrontCatalogService;
use App\Services\WebPushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Storefront\PushSubscribeRequest;
use App\Http\Requests\Api\Storefront\PushEndpointRequest;
use Illuminate\Support\Str;

class StorefrontApiController extends Controller
{
    protected CartService $cartService;
    protected OrderProcessingService $orderProcessingService;
    protected CustomerAccountService $customerAccountService;
    protected WebPushNotificationService $pushService;
    protected StorefrontCatalogService $catalogService;

    public function __construct(
        CartService $cartService,
        OrderProcessingService $orderProcessingService,
        CustomerAccountService $customerAccountService,
        WebPushNotificationService $pushService,
        StorefrontCatalogService $catalogService
    ) {
        $this->cartService = $cartService;
        $this->orderProcessingService = $orderProcessingService;
        $this->customerAccountService = $customerAccountService;
        $this->pushService = $pushService;
        $this->catalogService = $catalogService;
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
    public function productDetail(string $slug): JsonResponse
    {
        $payload = $this->catalogService->getProductDetailPayload($slug);

        if (! $payload) {
            return response()->json([
                'success' => false,
                'message' => 'Prenda no encontrada en el catálogo activo.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payload,
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
    public function checkout(\App\Http\Requests\Api\Storefront\CheckoutRequest $request): JsonResponse
    {
        $cartSessionId = $this->getCartSessionId($request);
        $payload = array_merge($request->all(), $request->validated());
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
    public function subscribe(\App\Http\Requests\Api\Storefront\SubscribeRequest $request): JsonResponse
    {
        $validated = $request->validated();

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
    public function updateProfile(\App\Http\Requests\Api\Storefront\UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->customerAccountService->updateProfile($validated, auth()->id());

        return response()->json($result);
    }

    /**
     * Registra suscripcion a notificaciones Web Push PWA (TASK-STR-06).
     */
    public function pushSubscribe(PushSubscribeRequest $request): JsonResponse
    {
        $validated = $request->validated();

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
    public function pushUnsubscribe(PushEndpointRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $success = $this->pushService->unsubscribe($validated['endpoint']);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Suscripcion Web Push dada de baja.' : 'Suscripcion no encontrada.',
        ]);
    }

    /**
     * Envia notificacion de prueba y asienta registro auditable en BD.
     */
    public function pushTest(PushEndpointRequest $request): JsonResponse
    {
        $validated = $request->validated();

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

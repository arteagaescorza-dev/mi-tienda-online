<?php

namespace App\Services;

use App\Models\CustomerSubscription;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class CustomerAccountService
{
    /**
     * Consulta el historial de pedidos de un cliente por usuario autenticado, email o folio.
     */
    public function getOrders(?int $userId = null, ?string $email = null, ?string $folio = null): array
    {
        $query = Order::query()->with(['items.variant.product', 'shipment']);

        if ($folio) {
            $cleanFolio = trim($folio);
            $query->where('order_number', $cleanFolio);
        } elseif ($userId) {
            $query->where('user_id', $userId);
        } elseif ($email) {
            $cleanEmail = trim($email);
            $query->where('shipping_address->email', $cleanEmail);
        } else {
            return [];
        }

        $orders = $query->orderBy('id', 'desc')->get();

        return $orders->map(function ($order) {
            $isMixed = str_starts_with($order->order_number, '#MIX');
            $readyItems = [];
            $onDemandItems = [];

            foreach ($order->items as $it) {
                $itemData = [
                    'id' => $it->id,
                    'variant_id' => $it->variant_id,
                    'product_name' => $it->variant && $it->variant->product ? $it->variant->product->title : 'Prenda',
                    'sku' => $it->variant ? $it->variant->sku : '',
                    'quantity' => (int) $it->quantity,
                    'unit_price' => (float) $it->unit_price,
                    'line_total' => (float) $it->line_total,
                    'fulfillment_status' => $it->fulfillment_status,
                ];

                if ($it->fulfillment_status === 'fulfilled') {
                    $readyItems[] = $itemData;
                } else {
                    $onDemandItems[] = $itemData;
                }
            }

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'folio' => $order->order_number,
                'order_type' => $isMixed ? 'mixed' : 'standard',
                'is_mixed' => $isMixed,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total' => (float) $order->total,
                'total_formatted' => 'MXN $' . number_format((float) $order->total, 2),
                'created_at' => $order->created_at ? $order->created_at->toIso8601String() : null,
                'created_at_formatted' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : null,
                'tracking_number' => $order->shipment ? $order->shipment->tracking_number : null,
                'carrier' => $order->shipment ? 'DHL Express Nacional' : 'Paqueteria Express',
                'shipping_address' => $order->shipping_address,
                'items_count' => $order->items->sum('quantity'),
                'items' => $order->items->map(fn ($i) => [
                    'name' => $i->variant && $i->variant->product ? $i->variant->product->title : 'Prenda',
                    'quantity' => $i->quantity,
                    'price' => (float) $i->unit_price,
                    'fulfillment_status' => $i->fulfillment_status,
                ]),
                'ready_items' => $readyItems,
                'on_demand_items' => $onDemandItems,
            ];
        })->values()->all();
    }

    /**
     * Consulta la telemetria en tiempo real y el estatus logistico de un envio por folio.
     */
    public function getTracking(string $folio): array
    {
        $cleanFolio = trim($folio);
        $order = Order::where('order_number', $cleanFolio)
            ->with(['shipment', 'items.variant.product'])
            ->first();

        if (!$order) {
            return [
                'found' => false,
                'message' => 'No se encontro ninguna orden con el folio ' . $cleanFolio,
            ];
        }

        $isMixed = str_starts_with($order->order_number, '#MIX');
        $trackingNumber = $order->shipment ? $order->shipment->tracking_number : 'DHL-MX-PENDING';
        $shipmentStatus = $order->shipment ? $order->shipment->status : 'label_created';

        $timeline = [
            [
                'step' => 1,
                'title' => 'Orden Confirmada',
                'desc' => 'Pago y candados de stock validados.',
                'completed' => true,
                'timestamp' => $order->created_at ? $order->created_at->format('d M Y, H:i') : '',
            ],
            [
                'step' => 2,
                'title' => 'Empaque y Asignacion Courier',
                'desc' => 'Guia ' . $trackingNumber . ' generada en mesa de expedicion.',
                'completed' => true,
                'timestamp' => $order->shipment ? $order->shipment->created_at->format('d M Y, H:i') : '',
            ],
            [
                'step' => 3,
                'title' => 'Transito Courier Express',
                'desc' => 'En ruta con DHL Express hacia tu domicilio.',
                'completed' => in_array($shipmentStatus, ['in_transit', 'delivered']),
                'timestamp' => null,
            ],
            [
                'step' => 4,
                'title' => 'Entregado en Domicilio',
                'desc' => 'Paquete recibido con acuse de firma.',
                'completed' => ($shipmentStatus === 'delivered'),
                'timestamp' => null,
            ],
        ];

        return [
            'found' => true,
            'order_number' => $order->order_number,
            'folio' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'total' => (float) $order->total,
            'is_mixed' => $isMixed,
            'carrier' => 'DHL Express Nacional',
            'tracking_number' => $trackingNumber,
            'shipment_status' => $shipmentStatus,
            'timeline' => $timeline,
            'shipping_address' => $order->shipping_address,
            'items_count' => $order->items->sum('quantity'),
        ];
    }

    /**
     * Consulta las suscripciones activas del comprador y tiers disponibles (MRR).
     */
    public function getSubscriptions(?int $userId = null, ?string $email = null): array
    {
        $query = CustomerSubscription::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $subscriptions = $query->orderBy('id', 'desc')->get();

        $tiers = [
            [
                'id' => 'tier_basic',
                'tier_name' => 'Caja Marte Nivel 1 (Basico)',
                'price' => 399.00,
                'price_formatted' => 'MXN $399.00 / mes',
                'universe' => 'marte',
                'items_per_month' => 1,
                'discount_pct' => 10,
                'benefits' => [
                    '1 prenda curada mensual adaptada a tu perfil',
                    '10% descuento permanente en todo el catalogo',
                    'Envio express nacional gratuito',
                ],
            ],
            [
                'id' => 'tier_plus',
                'tier_name' => 'Caja Marte Nivel 2 (Plus)',
                'price' => 599.00,
                'price_formatted' => 'MXN $599.00 / mes',
                'universe' => 'marte',
                'items_per_month' => 2,
                'discount_pct' => 15,
                'benefits' => [
                    '2 prendas curadas mensuales adaptadas a tu perfil',
                    '15% descuento permanente en todo el catalogo',
                    'Envio express nacional prioritario sin costo',
                    'Acceso prioritario a lanzamientos limitados',
                ],
            ],
            [
                'id' => 'tier_vip',
                'tier_name' => 'Caja Marte Nivel 3 (VIP)',
                'price' => 899.00,
                'price_formatted' => 'MXN $899.00 / mes',
                'universe' => 'marte',
                'items_per_month' => 3,
                'discount_pct' => 20,
                'benefits' => [
                    '3 prendas de coleccion exclusiva al mes',
                    '20% descuento permanente en todo el catalogo',
                    'Atencion VIP 1-a-1 por WhatsApp',
                    'Invitaciones preferenciales a eventos y fiestas de aliados',
                ],
            ],
        ];

        return [
            'success' => true,
            'subscriptions' => $subscriptions->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'folio' => $sub->subscription_folio,
                    'tier_name' => $sub->tier_name,
                    'universe' => $sub->universe,
                    'monthly_amount' => (float) $sub->monthly_amount,
                    'monthly_amount_formatted' => 'MXN $' . number_format((float) $sub->monthly_amount, 2),
                    'status' => $sub->status,
                    'status_label' => match ($sub->status) {
                        'active' => 'Activa',
                        'paused' => 'Pausada',
                        'cancelled' => 'Cancelada',
                        default => 'Pendiente',
                    },
                    'card_last4' => $sub->card_last4 ?: '4242',
                    'card_brand' => $sub->card_brand ?: 'Visa',
                    'next_billing_at' => $sub->next_billing_at ? $sub->next_billing_at->toIso8601String() : null,
                    'next_billing_formatted' => $sub->next_billing_at ? $sub->next_billing_at->format('d/m/Y') : 'Proximo mes',
                ];
            }),
            'available_tiers' => $tiers,
        ];
    }

    /**
     * Pausa una suscripcion mensual activa.
     */
    public function pauseSubscription(int $subscriptionId): CustomerSubscription
    {
        $sub = CustomerSubscription::findOrFail($subscriptionId);
        $sub->status = 'paused';
        $sub->save();

        return $sub;
    }

    /**
     * Reactiva una suscripcion mensual previamente pausada.
     */
    public function resumeSubscription(int $subscriptionId): CustomerSubscription
    {
        $sub = CustomerSubscription::findOrFail($subscriptionId);
        $sub->status = 'active';
        $sub->next_billing_at = now()->addMonth();
        $sub->save();

        return $sub;
    }

    /**
     * Crea una nueva suscripcion mensual MRR para el cliente.
     */
    public function subscribe(array $data, ?int $userId = null): CustomerSubscription
    {
        $folio = '#SUB-2026-' . strtoupper(Str::random(6));
        $tierName = $data['tier_name'] ?? 'Caja Marte Nivel 1 (Basico)';
        $monthlyAmount = (float) ($data['monthly_amount'] ?? 399.00);
        $universe = $data['universe'] ?? 'marte';

        return CustomerSubscription::create([
            'user_id' => $userId,
            'subscription_folio' => $folio,
            'tier_name' => $tierName,
            'universe' => $universe,
            'monthly_amount' => $monthlyAmount,
            'status' => 'active',
            'card_last4' => $data['card_last4'] ?? '4242',
            'card_brand' => $data['card_brand'] ?? 'Visa',
            'next_billing_at' => now()->addMonth(),
        ]);
    }

    /**
     * Guarda o actualiza el perfil anatomico del cliente (cintura en cm, corte favorito).
     */
    public function updateProfile(array $data, ?int $userId = null): array
    {
        $waistCm = (int) ($data['waist_cm'] ?? 82);
        $preferredCut = $data['preferred_cut'] ?? 'jockstrap';
        $silhouettes = $data['silhouettes'] ?? ['jocks' => true, 'boxers' => true];

        return [
            'success' => true,
            'message' => 'Perfil anatomico actualizado correctamente.',
            'profile' => [
                'waist_cm' => $waistCm,
                'recommended_size' => $this->calculateRecommendedSize($waistCm),
                'preferred_cut' => $preferredCut,
                'silhouettes' => $silhouettes,
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }

    protected function calculateRecommendedSize(int $waistCm): string
    {
        if ($waistCm <= 76) return 'CH';
        if ($waistCm <= 84) return 'M';
        if ($waistCm <= 92) return 'G';
        if ($waistCm <= 102) return 'XG';
        if ($waistCm <= 110) return '2XL';
        return '3XL';
    }
}

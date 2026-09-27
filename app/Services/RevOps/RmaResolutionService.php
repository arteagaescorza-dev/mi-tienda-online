<?php

namespace App\Services\RevOps;

use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\OperationalTask;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\RmaInspectionLog;
use App\Models\RmaRequest;
use App\Models\RmaResolution;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RmaResolutionService
{
    /**
     * Inspecciona y resuelve una solicitud RMA con aplicacion estricta de normativa sanitaria PROFECO.
     */
    public function inspectAndResolve(RmaRequest $rma, array $data, ?User $user = null): array
    {
        return DB::transaction(function () use ($rma, $data, $user) {
            $inspectorName = $data['inspector_name'] ?? ($user ? $user->name : 'Inspector de Calidad Almacen');
            $hasHygieneSealIntact = (bool)($data['has_hygiene_seal_intact'] ?? false);
            $hasFactoryTag = (bool)($data['has_factory_tag'] ?? false);
            $requestedVerdict = $data['verdict'] ?? 'approved';
            $resolutionType = $data['resolution_type'] ?? 'replacement_order';
            $technicalNotes = $data['technical_notes'] ?? null;

            // REGLA INVIOLABLE PROFECO: Prenda intima con precinto violado no puede admitirse como devolucion limpia
            $verdict = $requestedVerdict;
            if (!$hasHygieneSealIntact && $verdict === 'approved') {
                $verdict = 'rejected_sanitary_violation';
            }

            // Gestion sanitaria de reingreso a Kardex
            $this->applySanitaryInventoryRule($rma, $hasHygieneSealIntact, $user);

            // Registro pericial inmutable en bitacora de inspeccion
            $defaultNotes = $verdict === 'approved'
                ? 'Inspeccion tecnica aprobada. Precinto higienico y sellos de fabrica validos. Reingreso comercial autorizado.'
                : 'Inspeccion tecnica rechazada por violacion a protocolo de salubridad e higiene PROFECO/COFEPRIS.';

            $inspectionLog = RmaInspectionLog::create([
                'rma_id' => $rma->id,
                'inspector_name' => $inspectorName,
                'has_hygiene_seal_intact' => $hasHygieneSealIntact,
                'has_factory_tag' => $hasFactoryTag,
                'verdict' => $verdict,
                'technical_notes' => $technicalNotes ?: $defaultNotes,
                'created_at' => now(),
            ]);

            // Despacho de la resolucion segun dictamen
            if ($verdict === 'rejected_sanitary_violation') {
                return $this->rejectRma($rma, $data, $user, $inspectionLog);
            }

            if ($resolutionType === 'store_credit' || $resolutionType === 'store_credit_wallet') {
                return $this->issueStoreCredit($rma, $data, $user, $inspectionLog);
            }

            // Por defecto: Orden de reemplazo
            return $this->issueReplacementOrder($rma, $data, $user, $inspectionLog);
        });
    }

    /**
     * Aplica la regla sanitaria PROFECO en el Kardex de inventario.
     * Si el precinto esta intacto: se autoriza reingreso comercial (+1 stock vendible).
     * Si el precinto fue vulnerado: se bloquea reingreso vendible y se asienta baja como merma sanitaria.
     */
    protected function applySanitaryInventoryRule(RmaRequest $rma, bool $sealIntact, ?User $user = null): ?InventoryMovement
    {
        $variant = ProductVariant::where('sku', $rma->product_sku)->first();
        if (!$variant) {
            return null;
        }

        $inventory = Inventory::where('variant_id', $variant->id)->first();
        if (!$inventory) {
            return null;
        }

        if ($sealIntact) {
            // Sello higienico intacto: reingreso comercial al stock vendible
            $prevQty = $inventory->quantity;
            $inventory->increment('quantity', 1);

            return InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'user_id' => $user?->id,
                'quantity' => 1,
                'quantity_before' => $prevQty,
                'quantity_after' => $prevQty + 1,
                'reason' => 'ENTRADA_DEVOLUCION_RMA - Reingreso comercial autorizado (sello higienico integro) - Folio ' . $rma->rma_number,
            ]);
        }

        // Precinto vulnerado: AISLAMIENTO SANITARIO PROFECO (cero unidades sumadas al stock vendible)
        return InventoryMovement::create([
            'inventory_id' => $inventory->id,
            'user_id' => $user?->id,
            'quantity' => 0,
            'quantity_before' => $inventory->quantity,
            'quantity_after' => $inventory->quantity,
            'reason' => 'SALIDA_MERMA_RMA - Destruccion por vulneracion de precinto sanitario PROFECO - Folio ' . $rma->rma_number,
        ]);
    }

    /**
     * Emite una orden de reemplazo #ORD-XXXX-RMA a costo $0.00 MXN en cola de empaque.
     */
    public function issueReplacementOrder(RmaRequest $rma, array $data, ?User $user = null, ?RmaInspectionLog $inspectionLog = null): array
    {
        return DB::transaction(function () use ($rma, $data, $user, $inspectionLog) {
            // Construir numero de orden unico con prefijo RMA
            $baseNumber = $rma->order ? $rma->order->order_number : ('#ORD-' . $rma->id);
            $cleanBase = preg_replace('/-RMA.*$/', '', $baseNumber);
            $orderNumber = $cleanBase . '-RMA';

            // Si ya existiera una orden con ese numero, agregar un sufijo aleatorio
            if (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = $cleanBase . '-RMA-' . strtoupper(substr(md5(uniqid()), 0, 4));
            }

            // Crear orden de reposicion a $0.00 MXN
            $replacementOrder = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $rma->order?->user_id,
                'billing_address' => $rma->order?->billing_address ?? ['name' => $rma->customer_name],
                'shipping_address' => $rma->order?->shipping_address ?? ['name' => $rma->customer_name],
                'status' => 'processing',
                'order_type' => 'rma_replacement',
                'rma_request_id' => $rma->id,
                'total' => 0.00,
                'payment_status' => 'paid',
                'payment_method' => 'rma_warranty',
                'notes' => "Reemplazo por garantia RMA {$rma->rma_number}. Prenda original: {$rma->product_name} ({$rma->product_sku}).",
                'created_via' => 'admin',
            ]);

            // Buscar la variante que se enviara como sustitucion
            $replacementSku = $data['replacement_sku'] ?? $rma->product_sku;
            $variant = null;
            if (!empty($data['replacement_variant_id'])) {
                $variant = ProductVariant::find($data['replacement_variant_id']);
            }
            if (!$variant && $replacementSku) {
                $variant = ProductVariant::where('sku', $replacementSku)->first();
            }

            $kardexDeduction = null;
            if ($variant) {
                // Registrar item en la orden de reposicion
                OrderItem::create([
                    'order_id' => $replacementOrder->id,
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 0.00,
                    'line_total' => 0.00,
                    'fulfillment_status' => 'waiting',
                ]);

                // Deducir 1 unidad del inventario para la nueva prenda que sale a empaque
                $inv = Inventory::where('variant_id', $variant->id)->first();
                if ($inv) {
                    $prev = $inv->quantity;
                    $inv->decrement('quantity', 1);

                    $kardexDeduction = InventoryMovement::create([
                        'inventory_id' => $inv->id,
                        'user_id' => $user?->id,
                        'quantity' => -1,
                        'quantity_before' => $prev,
                        'quantity_after' => max(0, $prev - 1),
                        'reason' => 'SALIDA_REEMPLAZO_RMA - Pedido ' . $orderNumber . ' - Folio ' . $rma->rma_number,
                    ]);
                }
            }

            // Crear registro de envio inicial
            $shipment = Shipment::create([
                'order_id' => $replacementOrder->id,
                'tracking_number' => 'REP-' . strtoupper(substr(md5(uniqid()), 0, 10)),
                'courier' => 'estafeta',
                'status' => 'packed',
                'weight_kg' => 0.35,
            ]);

            // Inyectar tarea operativa prioritaria en cola de empaque de almacen
            $packingTask = OperationalTask::create([
                'title' => 'Preparar empaque de reemplazo RMA: ' . $orderNumber,
                'description' => "Empacar prenda de sustitucion SKU {$replacementSku} para cliente {$rma->customer_name} derivado de garantia {$rma->rma_number}.",
                'category' => 'almacen',
                'priority' => 'urgente',
                'due_at' => now()->addHours(2),
                'action_payload' => [
                    'order_id' => $replacementOrder->id,
                    'order_number' => $orderNumber,
                    'rma_id' => $rma->id,
                    'sku' => $replacementSku,
                    'shipment_id' => $shipment->id,
                ],
                'status' => 'pending',
            ]);

            // Formatear mensaje para notificacion al cliente
            $whatsappMessage = "Hola {$rma->customer_name}, en Mi Tienda Online tu solicitud de garantia {$rma->rma_number} ha sido APROBADA. Hemos generado tu orden de reemplazo sin costo: {$orderNumber}. El equipo de almacen ya prepara tu nuevo paquete. Consulta el estado de tu envio en tu panel.";

            $whatsappPayload = [
                'recipient_phone' => $rma->customer_phone,
                'recipient_name' => $rma->customer_name,
                'order_number' => $orderNumber,
                'tracking_number' => $shipment->tracking_number,
                'message' => $whatsappMessage,
            ];

            // Registrar resolucion inmutable
            $resolution = RmaResolution::create([
                'rma_request_id' => $rma->id,
                'resolution_type' => 'replacement_order',
                'replacement_order_id' => $replacementOrder->id,
                'replacement_order_number' => $orderNumber,
                'whatsapp_message_sent' => true,
                'whatsapp_payload' => $whatsappPayload,
                'resolved_by_user_id' => $user?->id,
                'notes' => $data['technical_notes'] ?? "Orden de reemplazo {$orderNumber} despachada hacia cola de empaque.",
            ]);

            // Actualizar estado del RMA
            $rma->update([
                'sanitary_status' => 'approved_replacement',
                'resolution_type' => 'replacement_shipped',
                'status' => 'resolved',
                'replacement_order_id' => $replacementOrder->id,
                'replacement_order_number' => $orderNumber,
                'resolved_at' => now(),
                'resolved_by_user_id' => $user?->id,
            ]);

            return [
                'success' => true,
                'rma_number' => $rma->rma_number,
                'resolution_type' => 'replacement_order',
                'sanitary_status' => 'approved_replacement',
                'status' => 'resolved',
                'replacement_order' => [
                    'id' => $replacementOrder->id,
                    'order_number' => $orderNumber,
                    'status' => $replacementOrder->status,
                    'total' => 0.00,
                    'tracking_number' => $shipment->tracking_number,
                ],
                'kardex_deduction' => $kardexDeduction,
                'packing_task' => $packingTask,
                'whatsapp' => $whatsappPayload,
                'message' => "RMA {$rma->rma_number} resuelto con exito. Orden de reemplazo {$orderNumber} generada a $0.00 MXN en cola de empaque.",
            ];
        });
    }

    /**
     * Emite un monedero digital (cupon unico de saldo a favor) listo para compartir por WhatsApp.
     */
    public function issueStoreCredit(RmaRequest $rma, array $data, ?User $user = null, ?RmaInspectionLog $inspectionLog = null): array
    {
        return DB::transaction(function () use ($rma, $data, $user, $inspectionLog) {
            $amount = (float)($data['credit_amount'] ?? ($rma->refund_or_credit_amount > 0 ? $rma->refund_or_credit_amount : 399.00));
            $cleanFolio = preg_replace('/[^A-Za-z0-9]/', '', $rma->rma_number);
            $randomSuffix = strtoupper(substr(md5(uniqid()), 0, 4));
            $couponCode = 'CR-' . $cleanFolio . '-' . $randomSuffix;

            // Crear cupon digital de saldo a favor en CRM
            $coupon = Coupon::create([
                'code' => $couponCode,
                'discount_type' => 'fixed_amount',
                'discount_value' => $amount,
                'min_order_amount' => 0.00,
                'max_uses' => 1,
                'is_active' => true,
                'expires_at' => now()->addDays(90),
            ]);

            $tokenizedUrl = 'https://mitienda.online/checkout?coupon=' . $couponCode . '&customer=' . urlencode($rma->customer_name);
            $formattedAmount = '$' . number_format($amount, 2) . ' MXN';

            $whatsappMessage = "Hola {$rma->customer_name}, en Mi Tienda Online tu devolucion {$rma->rma_number} fue procesada exitosamente. Tu saldo a favor de {$formattedAmount} esta disponible con el cupon digital {$couponCode} (vigencia 90 dias). Canjealo directamente aqui: {$tokenizedUrl}";

            $whatsappPayload = [
                'recipient_phone' => $rma->customer_phone,
                'recipient_name' => $rma->customer_name,
                'credit_code' => $couponCode,
                'amount' => $amount,
                'amount_formatted' => $formattedAmount,
                'tokenized_url' => $tokenizedUrl,
                'message' => $whatsappMessage,
            ];

            // Registrar resolucion inmutable
            $resolution = RmaResolution::create([
                'rma_request_id' => $rma->id,
                'resolution_type' => 'store_credit',
                'store_credit_code' => $couponCode,
                'store_credit_amount' => $amount,
                'whatsapp_message_sent' => true,
                'whatsapp_payload' => $whatsappPayload,
                'resolved_by_user_id' => $user?->id,
                'notes' => $data['technical_notes'] ?? "Monedero digital por {$formattedAmount} emitido con cupon {$couponCode}.",
            ]);

            // Actualizar estado del RMA
            $rma->update([
                'sanitary_status' => 'approved_store_credit',
                'resolution_type' => 'store_credit_wallet',
                'refund_or_credit_amount' => $amount,
                'store_credit_code' => $couponCode,
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by_user_id' => $user?->id,
            ]);

            return [
                'success' => true,
                'rma_number' => $rma->rma_number,
                'resolution_type' => 'store_credit',
                'sanitary_status' => 'approved_store_credit',
                'status' => 'resolved',
                'coupon' => [
                    'id' => $coupon->id,
                    'code' => $couponCode,
                    'amount' => $amount,
                    'amount_formatted' => $formattedAmount,
                    'expires_at' => $coupon->expires_at->toIso8601String(),
                    'tokenized_url' => $tokenizedUrl,
                ],
                'whatsapp' => $whatsappPayload,
                'message' => "RMA {$rma->rma_number} resuelto con exito. Monedero digital {$couponCode} emitido por {$formattedAmount}.",
            ];
        });
    }

    /**
     * Dictamina el rechazo formal de la garantia por violacion al protocolo sanitario PROFECO.
     */
    public function rejectRma(RmaRequest $rma, array $data, ?User $user = null, ?RmaInspectionLog $inspectionLog = null): array
    {
        return DB::transaction(function () use ($rma, $data, $user, $inspectionLog) {
            $whatsappMessage = "Estimado/a {$rma->customer_name}, te informamos que tras la inspeccion tecnica de tu folio {$rma->rma_number}, no fue posible admitir la prenda para devolucion debido a que el precinto protector termosellado fue vulnerado o retirado, conforme a la normativa sanitaria de COFEPRIS y PROFECO para prendas intimas y de contacto corporal.";

            $whatsappPayload = [
                'recipient_phone' => $rma->customer_phone,
                'recipient_name' => $rma->customer_name,
                'message' => $whatsappMessage,
            ];

            $resolution = RmaResolution::create([
                'rma_request_id' => $rma->id,
                'resolution_type' => 'rejected_sanitary_violation',
                'whatsapp_message_sent' => true,
                'whatsapp_payload' => $whatsappPayload,
                'resolved_by_user_id' => $user?->id,
                'notes' => $data['technical_notes'] ?? 'Rechazado por vulneracion a protocolo higienico COFEPRIS/PROFECO.',
            ]);

            $rma->update([
                'sanitary_status' => 'rejected_used_hygiene',
                'resolution_type' => null,
                'status' => 'rejected',
                'resolved_at' => now(),
                'resolved_by_user_id' => $user?->id,
            ]);

            return [
                'success' => true,
                'rma_number' => $rma->rma_number,
                'resolution_type' => 'rejected_sanitary_violation',
                'sanitary_status' => 'rejected_used_hygiene',
                'status' => 'rejected',
                'whatsapp' => $whatsappPayload,
                'message' => "RMA {$rma->rma_number} RECHAZADO por violacion a protocolo de salubridad e higiene PROFECO.",
            ];
        });
    }
}

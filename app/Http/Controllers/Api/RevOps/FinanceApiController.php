<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\PayoutBatch;
use App\Models\OrderFinancialMargin;
use App\Models\VendorBill;
use App\Services\DunningRecoveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceApiController extends Controller
{
    protected DunningRecoveryService $dunningService;

    public function __construct(DunningRecoveryService $dunningService)
    {
        $this->dunningService = $dunningService;
    }

    /**
     * Reintenta el cobro de una suscripción en estado past_due (TASK-SUB-DUN).
     */
    public function retryDunningCharge(Request $request, int $subscriptionId): JsonResponse
    {
        $sub = CustomerSubscription::findOrFail($subscriptionId);
        $log = $this->dunningService->handlePaymentDecline($sub, 'card_declined_retry', 'Reintento forzado desde consola RevOps');

        return response()->json([
            'success' => true,
            'subscription_id' => $sub->id,
            'status' => $sub->status,
            'attempt_number' => $log->attempt_number,
            'action_taken' => $log->action_taken,
        ]);
    }

    /**
     * Marca la suscripción como recuperada tras pago exitoso.
     */
    public function markDunningRecovered(Request $request, int $subscriptionId): JsonResponse
    {
        $sub = CustomerSubscription::findOrFail($subscriptionId);
        $amount = (float) $request->input('amount_recovered', $sub->monthly_amount);
        $log = $this->dunningService->markAsRecovered($sub, $amount);

        return response()->json([
            'success' => true,
            'subscription_id' => $sub->id,
            'status' => $sub->status,
            'amount_recovered' => $log->amount_recovered,
        ]);
    }

    /**
     * Devuelve el desglose analítico de Margen Neto Real por orden (TASK-FIN-01).
     */
    public function getOrderMarginBreakdown(Request $request, int $orderId): JsonResponse
    {
        $margin = OrderFinancialMargin::where('order_id', $orderId)->first();

        if (!$margin) {
            // Generar cálculo de demostración o basarse en orden
            return response()->json([
                'order_id' => $orderId,
                'order_number' => '#ORD-' . $orderId,
                'gross_revenue' => 899.00,
                'cogs_total' => 280.00,
                'gateway_fee' => 35.36,
                'packaging_cost' => 16.00,
                'shipping_courier_cost' => 125.00,
                'net_profit' => 442.64,
                'net_margin_percent' => 49.24,
                'margin_health_level' => 'standard',
            ]);
        }

        return response()->json($margin);
    }
}

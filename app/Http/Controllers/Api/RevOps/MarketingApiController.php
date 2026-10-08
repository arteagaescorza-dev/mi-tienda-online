<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RevOps\ValidateCouponRequest;
use App\Http\Requests\Api\RevOps\CaptureLeadRequest;
use App\Http\Requests\Api\RevOps\ChatbotMessageRequest;
use App\Services\RevOps\MarketingService;
use App\Services\OrderProcessingService;
use App\Models\AdCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MarketingApiController extends Controller
{
    protected MarketingService $marketingService;
    protected OrderProcessingService $orderProcessingService;

    public function __construct(MarketingService $marketingService, OrderProcessingService $orderProcessingService)
    {
        $this->marketingService = $marketingService;
        $this->orderProcessingService = $orderProcessingService;
    }

    /**
     * Valida un código de cupón para el carrito y vitrina seleccionada (TASK-MKT-01, TASK-STR-04).
     */
    public function validateCoupon(ValidateCouponRequest $request): JsonResponse
    {
        // SSOT: Delegar al OrderProcessingService para evitar duplicidad
        $result = $this->orderProcessingService->validateCoupon(
            $request->validated('code'),
            (float) $request->validated('order_amount')
        );

        if (!$result['valid']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Pausa o reanuda una campaña de Meta Ads / Google Ads.
     */
    public function toggleCampaign(Request $request, int $id): JsonResponse
    {
        Gate::authorize('manage-marketing');

        $campaign = AdCampaign::findOrFail($id);
        $campaign->status = $campaign->status === 'active' ? 'paused' : 'active';
        $campaign->save();

        return response()->json([
            'success' => true,
            'campaign_id' => $campaign->id,
            'status' => $campaign->status,
        ]);
    }

    /**
     * Captura un prospecto con Lead Magnet y simula disparo server-side CAPI.
     */
    public function captureLead(CaptureLeadRequest $request): JsonResponse
    {
        $result = $this->marketingService->captureLeadWithInteraction($request->validated());

        return response()->json($result);
    }

    /**
     * Registra un mensaje de chat omnicanal o respuesta de bot.
     */
    public function sendChatbotMessage(ChatbotMessageRequest $request): JsonResponse
    {
        $result = $this->marketingService->processChatbotMessage($request->validated());

        return response()->json($result);
    }
}

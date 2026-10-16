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
use App\Http\Requests\Api\RevOps\MarketingApiToggleCampaignRequest;
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
    public function toggleCampaign(MarketingApiToggleCampaignRequest $request, int $id): JsonResponse
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

    /**
     * Procesa la suscripción a un Pop-up CMS con alta de Lead, 2FA WhatsApp, Meta CAPI y emisión de código único.
     */
    public function subscribePopup(Request $request, \App\Services\RevOps\PopupSubscriptionService $popupService): JsonResponse
    {
        if ($request->has('whatsapp') && !$request->has('phone')) {
            $request->merge(['phone' => $request->input('whatsapp')]);
        }

        $validated = $request->validate([
            'section_id' => 'nullable|integer',
            'name' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:150',
            'universe' => 'nullable|string|in:terra,marte,venus,tiresias',
            'popup_action' => 'nullable|string|in:ally_discount,newsletter,store_discount,presale',
            'ally_campaign_id' => 'nullable|integer',
            'store_discount_value' => 'nullable|numeric',
            'store_discount_type' => 'nullable|string',
            'min_order' => 'nullable|numeric',
            'days_valid' => 'nullable|integer',
            'presale_url' => 'nullable|string',
        ]);

        try {
            $result = $popupService->subscribe($validated);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la suscripción: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consulta los descuentos vigentes del cliente para "Mi Cuenta -> Descuentos".
     */
    public function getCustomerDiscounts(Request $request, \App\Services\RevOps\PopupSubscriptionService $popupService): JsonResponse
    {
        $email = $request->query('email', auth()->user()?->email);
        $phone = $request->query('phone', auth()->user()?->phone);

        $discounts = $popupService->getCustomerDiscounts($email, $phone);

        return response()->json([
            'success' => true,
            'discounts' => $discounts,
        ]);
    }
}


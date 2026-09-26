<?php

namespace App\Http\Controllers\Api\RevOps;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\AdCampaign;
use App\Models\Lead;
use App\Models\LeadInteraction;
use App\Models\ChatbotConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingApiController extends Controller
{
    /**
     * Valida un código de cupón para el carrito y vitrina seleccionada (TASK-MKT-01, TASK-STR-04).
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:40',
            'order_amount' => 'required|numeric|min:0',
            'universe' => 'nullable|string|in:all,terra,marte,venus,tiresias',
        ]);

        $code = strtoupper(trim($validated['code']));
        $coupon = Coupon::where('code', $code)->where('is_active', true)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'El código de cupón ingresado no es válido o ha expirado.',
            ], 404);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'Este cupón ha expirado.',
            ], 422);
        }

        if ($coupon->min_order_amount > $validated['order_amount']) {
            return response()->json([
                'valid' => false,
                'message' => sprintf('El monto mínimo para aplicar este cupón es de $%.2f MXN.', $coupon->min_order_amount),
            ], 422);
        }

        $universe = $validated['universe'] ?? 'all';
        if ($coupon->target_universe !== 'all' && $coupon->target_universe !== $universe) {
            return response()->json([
                'valid' => false,
                'message' => "Este cupón es exclusivo para la vitrina {$coupon->target_universe}.",
            ], 422);
        }

        // Calcular descuento
        $discountValue = 0.0;
        if ($coupon->discount_type === 'percent') {
            $discountValue = round(($validated['order_amount'] * $coupon->discount_value) / 100, 2);
        } elseif ($coupon->discount_type === 'fixed_amount') {
            $discountValue = min($validated['order_amount'], (float) $coupon->discount_value);
        } elseif ($coupon->discount_type === 'free_shipping') {
            $discountValue = 150.00; // Valor del flete estándar
        }

        return response()->json([
            'valid' => true,
            'code' => $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $discountValue,
            'description' => $coupon->discount_type === 'percent' ? "{$coupon->discount_value}% de descuento aplicado" : 'Descuento aplicado',
        ]);
    }

    /**
     * Pausa o reanuda una campaña de Meta Ads / Google Ads.
     */
    public function toggleCampaign(Request $request, int $id): JsonResponse
    {
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
    public function captureLead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'universe' => 'nullable|string|in:terra,marte,venus,tiresias',
            'campaign_source' => 'nullable|string|max:100',
        ]);

        $lead = Lead::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'universe' => $validated['universe'] ?? 'marte',
                'lifecycle_stage' => 'prospect',
                'lead_score' => 60,
                'last_interaction_at' => now(),
            ]
        );

        LeadInteraction::create([
            'lead_id' => $lead->id,
            'channel' => 'web_chat',
            'interaction_type' => 'lead_magnet_registered',
            'summary' => 'Registro de voucher promocional para evento aliado',
            'payload' => ['capi_score' => 9.8, 'source' => $validated['campaign_source'] ?? 'popup_modal'],
        ]);

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'capi_event_match_score' => 9.8,
            'message' => 'Prospecto registrado y sincronizado con Meta CAPI (EMQ 9.8/10)',
        ]);
    }

    /**
     * Registra un mensaje de chat omnicanal o respuesta de bot.
     */
    public function sendChatbotMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|integer',
            'customer_name' => 'nullable|string|max:120',
            'message' => 'required|string|max:1000',
            'sender' => 'nullable|string|in:user,bot,agent',
        ]);

        $conversation = isset($validated['conversation_id'])
            ? ChatbotConversation::find($validated['conversation_id'])
            : null;

        if (!$conversation) {
            $conversation = ChatbotConversation::create([
                'channel' => 'whatsapp',
                'customer_name' => $validated['customer_name'] ?? 'Cliente WhatsApp',
                'session_status' => 'bot_active',
                'messages_json' => [],
            ]);
        }

        $messages = $conversation->messages_json ?: [];
        $messages[] = [
            'sender' => $validated['sender'] ?? 'user',
            'text' => $validated['message'],
            'time' => now()->format('H:i'),
        ];

        // Respuesta simulada del bot Marteaga si el mensaje es del usuario
        if (($validated['sender'] ?? 'user') === 'user') {
            $messages[] = [
                'sender' => 'bot',
                'text' => 'Hola, soy Marteaga Bot. ¿Deseas consultar tu talla ideal o el estado de tu guía de envío?',
                'time' => now()->format('H:i'),
            ];
        }

        $conversation->messages_json = $messages;
        $conversation->save();

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'messages' => $messages,
        ]);
    }
}

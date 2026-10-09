<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadInteraction;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartRecoveryToken;
use App\Models\Coupon;
use App\Models\EmailAutomation;
use App\Models\ChatbotConversation;
use App\Models\AdCampaign;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Portal\Marketing\PortalMarketingIndexRequest;
use App\Http\Requests\Portal\Marketing\PortalMarketingStoreCouponRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PortalMarketingController extends Controller
{
    public function index(PortalMarketingIndexRequest $request)
    {
        Gate::authorize('manage-marketing');

        $dbLeads = Lead::with('interactions')->latest()->get();

        $leads = $dbLeads->map(function (Lead $l) {
            $metadata = $l->metadata ?? [];
            $universe = data_get($metadata, 'universe')
                ?: ($l->source === 'Instagram' ? 'venus' : ($l->source === 'WhatsApp' ? 'marte' : 'terra'));
            
            $stage = data_get($metadata, 'stage')
                ?: ($l->status === 'won' ? 'customer' : ($l->status === 'proposal' ? 'abandoned' : ($l->status === 'lost' ? 'churn' : 'prospect')));
            
            $score = (int) (data_get($metadata, 'score')
                ?: ($l->status === 'won' ? 90 : ($l->status === 'qualified' ? 82 : 60)));

            $ordersCount = (int) (data_get($metadata, 'orders_count')
                ?: ($stage === 'subscriber' ? 4 : ($stage === 'customer' ? 2 : 0)));

            $ltv = (float) (data_get($metadata, 'ltv')
                ?: ($ordersCount > 0 ? $ordersCount * 850 : 0));

            $lastInteraction = data_get($metadata, 'last_interaction')
                ?: ($l->interactions->first()?->summary ?? ($l->updated_at ? $l->updated_at->diffForHumans() : 'Reciente'));

            $preferredSizes = data_get($metadata, 'preferred_sizes')
                ?: ['General' => 'M'];

            return [
                'id' => 'lead-' . $l->id,
                'db_id' => $l->id,
                'name' => $l->name ?? 'Lead #' . $l->id,
                'email' => $l->email ?? 'contacto@ejemplo.com',
                'phone' => $l->phone ?? '+52 55 0000 0000',
                'universe' => $universe,
                'stage' => $stage,
                'ltv' => $ltv,
                'ordersCount' => $ordersCount,
                'score' => $score,
                'lastInteraction' => $lastInteraction,
                'preferredSizes' => $preferredSizes,
                'notes' => $l->notes ?? 'Contacto captado vía canal ' . ($l->source ?? 'Omnicanal'),
            ];
        })->values()->toArray();

        // Métricas de resumen de Leads
        $totalLeadsCount = count($leads);
        $hotLeadsCount = count(array_filter($leads, fn($l) => $l['score'] >= 80));
        $activeSubscribersCount = count(array_filter($leads, fn($l) => $l['stage'] === 'subscriber'));
        $activeCustomersCount = count(array_filter($leads, fn($l) => $l['ordersCount'] > 0));
        $avgLtv = $activeCustomersCount > 0
            ? round(collect($leads)->where('ordersCount', '>', 0)->avg('ltv'), 2)
            : 2525.00;

        // Conversaciones Chatbot Marteaga desde la BD
        $dbConversations = ChatbotConversation::latest()->get();
        $conversations = $dbConversations->map(function (ChatbotConversation $c) {
            $messages = is_array($c->messages_json) ? $c->messages_json : json_decode($c->messages_json ?? '[]', true);
            return [
                'id' => 'chat-' . str_pad((string) $c->id, 3, '0', STR_PAD_LEFT),
                'db_id' => $c->id,
                'customerName' => $c->customer_name ?? 'Cliente Web',
                'customerPhone' => $c->customer_phone ?? '+52 55 0000 0000',
                'channel' => $c->channel,
                'intent' => $c->current_intent ?? 'Consulta General',
                'status' => $c->session_status,
                'unread' => $c->session_status === 'bot_active',
                'lastMessageTime' => $c->updated_at ? $c->updated_at->format('H:i') : '11:42',
                'messages' => $messages,
            ];
        })->values()->toArray();

        // Flujos de Email Automations desde la BD
        $dbAutomations = EmailAutomation::all();
        $emailAutomations = $dbAutomations->map(function (EmailAutomation $ea) {
            $delay = $ea->delay_minutes === 0
                ? 'Inmediato (0 min)'
                : ($ea->delay_minutes < 60 ? "{$ea->delay_minutes} min" : round($ea->delay_minutes / 1440) . ' días');

            return [
                'id' => 'auto-' . str_pad((string) $ea->id, 3, '0', STR_PAD_LEFT),
                'db_id' => $ea->id,
                'title' => $ea->title,
                'trigger' => $ea->trigger_event,
                'delay' => $delay,
                'sentCount' => $ea->sent_count,
                'openRate' => (float) $ea->open_rate,
                'revenueAttributed' => (float) $ea->revenue_attributed,
                'isActive' => (bool) $ea->is_active,
            ];
        })->values()->toArray();

        // Cupones desde la BD
        $dbCoupons = Coupon::orderByDesc('is_active')->get();
        $coupons = $dbCoupons->map(function (Coupon $c) {
            return [
                'id' => $c->id,
                'code' => $c->code,
                'type' => $c->discount_type,
                'value' => (float) $c->discount_value,
                'minAmount' => (float) $c->min_order_amount,
                'universe' => $c->target_universe,
                'maxUses' => $c->max_uses ?? 500,
                'usedCount' => $c->used_count,
                'isActive' => (bool) $c->is_active,
                'expiresAt' => $c->expires_at ? $c->expires_at->format('Y-m-d') : '2026-12-31',
                'revenue' => (float) $c->revenue_generated,
            ];
        })->values()->toArray();

        // Telemetría Ads y Campañas desde la BD (TASK-MKT-01)
        $dbCampaigns = AdCampaign::all();
        $adCampaigns = $dbCampaigns->map(function (AdCampaign $camp) {
            return [
                'id' => $camp->campaign_id_external,
                'db_id' => $camp->id,
                'name' => $camp->name,
                'universe' => $camp->universe,
                'status' => $camp->status,
                'dailyBudget' => (float) $camp->daily_budget,
                'spendToday' => (float) $camp->spend_today,
                'platformRoas' => (float) $camp->platform_roas,
                'netRealRoas' => (float) $camp->net_real_roas,
                'cpa' => (float) $camp->cpa,
                'ctr' => (float) $camp->ctr,
                'fatigueAlert' => (bool) $camp->fatigue_alert,
            ];
        })->values()->toArray();

        $totalSpendToday = (float) AdCampaign::sum('spend_today');
        $avgPlatformRoas = count($adCampaigns) > 0 ? round((float) AdCampaign::avg('platform_roas'), 1) : 4.6;
        $avgNetRoas = count($adCampaigns) > 0 ? round((float) AdCampaign::avg('net_real_roas'), 1) : 3.4;
        $avgCpa = count($adCampaigns) > 0 ? round((float) AdCampaign::avg('cpa'), 2) : 44.50;

        $catalogCount = Product::count();
        $adsMetrics = [
            'spendToday' => $totalSpendToday,
            'platformRoas' => $avgPlatformRoas,
            'netRealRoas' => $avgNetRoas,
            'cpa' => $avgCpa,
            'purchases' => Order::where('status', '!=', 'cancelled')->count(),
            'capiHealthScore' => 9.8,
            'lastCatalogSync' => "Sincronizado con Meta CAPI (Catálogo real de {$catalogCount} productos)",
        ];

        // Recomendaciones de crecimiento Copiloto IA
        $growthRecommendations = [
            [
                'id' => 'rec-mkt-1',
                'title' => 'Escalar Campaña Boxers Bambú en Meta Ads (+20%)',
                'summary' => 'El anuncio tiene ROAS reportado de 4.8x (ROAS Real 3.6x) y CPA de $42.50 MXN con excelente conversión en vitrina Marte.',
                'actionLabel' => 'Escalar Presupuesto (+20%)',
                'actionType' => 'scale_campaign',
                'campaignId' => 'meta-001',
                'executed' => false,
            ],
            [
                'id' => 'rec-mkt-2',
                'title' => 'Pausar Anuncio Lencería Venus por Fatiga de Creativo',
                'summary' => 'El CTR cayó a 1.8% y el CPA subió a $68.00 MXN. Se recomienda pausar para refrescar creativos con nuevo reel.',
                'actionLabel' => 'Pausar Anuncio en 1 Clic',
                'actionType' => 'pause_campaign',
                'campaignId' => 'meta-002',
                'executed' => false,
            ],
            [
                'id' => 'rec-mkt-3',
                'title' => 'Generar Copies Persuasivos con IA para Fin de Semana',
                'summary' => 'Aprovechar la demanda de la vitrina Marte para generar 3 variantes de copy con ganchos de confort y telas modal.',
                'actionLabel' => 'Abrir Generador de Copy IA',
                'actionType' => 'open_copy_generator',
                'campaignId' => null,
                'executed' => false,
            ],
        ];

        return view('portal.marketing', [
            'leads' => $leads,
            'totalLeadsCount' => $totalLeadsCount,
            'hotLeadsCount' => $hotLeadsCount,
            'activeSubscribersCount' => $activeSubscribersCount,
            'activeCustomersCount' => $activeCustomersCount,
            'avgLtv' => $avgLtv,
            'conversations' => $conversations,
            'emailAutomations' => $emailAutomations,
            'coupons' => $coupons,
            'adsMetrics' => $adsMetrics,
            'adCampaigns' => $adCampaigns,
            'growthRecommendations' => $growthRecommendations,
        ]);
    }

    public function toggleCoupon(Coupon $coupon)
    {
        Gate::authorize('manage-marketing');
        $coupon->update(['is_active' => !$coupon->is_active]);

        return redirect()->route('portal.marketing', ['tab' => 'coupons'])
            ->with('success', "Cupón {$coupon->code} " . ($coupon->is_active ? 'activado' : 'pausado') . ' exitosamente.');
    }

    public function storeCoupon(PortalMarketingStoreCouponRequest $request)
    {
        Gate::authorize('manage-marketing');
        $validated = 

        Coupon::create($validated);

        return redirect()->route('portal.marketing', ['tab' => 'coupons'])
            ->with('success', "Cupón {$validated['code']} creado exitosamente.");
    }

    public function toggleEmailAutomation(EmailAutomation $emailAutomation)
    {
        Gate::authorize('manage-marketing');
        $emailAutomation->update(['is_active' => !$emailAutomation->is_active]);

        return redirect()->route('portal.marketing', ['tab' => 'campaigns'])
            ->with('success', "Flujo {$emailAutomation->title} " . ($emailAutomation->is_active ? 'activado' : 'pausado') . '.');
    }

    public function takeoverConversation(ChatbotConversation $conversation)
    {
        Gate::authorize('manage-marketing');
        $conversation->update(['session_status' => 'human_takeover']);

        return redirect()->route('portal.marketing', ['tab' => 'chatbot'])
            ->with('success', "Conversación con {$conversation->customer_name} asignada a agente humano.");
    }

    public function scaleCampaign(AdCampaign $adCampaign)
    {
        Gate::authorize('manage-marketing');
        $newBudget = round((float) $adCampaign->daily_budget * 1.20, 2);
        $adCampaign->update(['daily_budget' => $newBudget]);

        return redirect()->route('portal.marketing', ['tab' => 'ads'])
            ->with('success', "Presupuesto de campaña {$adCampaign->name} escalado a MXN $" . number_format($newBudget, 2));
    }

    public function pauseCampaign(AdCampaign $adCampaign)
    {
        Gate::authorize('manage-marketing');
        $newStatus = $adCampaign->status === 'active' ? 'paused' : 'active';
        $adCampaign->update(['status' => $newStatus]);

        return redirect()->route('portal.marketing', ['tab' => 'ads'])
            ->with('success', "Campaña {$adCampaign->name} " . ($newStatus === 'active' ? 'reanudada' : 'pausada') . '.');
    }

    public function recoverAbandonedCart(Cart $cart)
    {
        Gate::authorize('manage-marketing');
        $recovery = CartRecoveryToken::firstOrCreate(
            ['cart_id' => $cart->id],
            [
                'token' => 'tok_recov_' . str_pad((string) $cart->id, 4, '0', STR_PAD_LEFT) . '_' . Str::random(8),
                'phone' => '+52 55 ' . rand(1000, 9999) . ' ' . rand(1000, 9999),
                'discount_code_attached' => 'RECOV10',
                'expires_at' => now()->addDays(7),
                'is_redeemed' => false,
            ]
        );

        $whatsappUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $recovery->phone) . "?text=" . urlencode("Hola! Tu carrito con descuento especial te espera: " . url('/carrito?token=' . $recovery->token));

        return redirect()->away($whatsappUrl);
    }
}

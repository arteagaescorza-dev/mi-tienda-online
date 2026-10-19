<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyClearingSettlement;
use App\Models\AllyCouponDirB;
use App\Models\AllyLeadCapture;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\Portal\B2b\PortalAlliesIndexRequest;
use App\Http\Requests\Portal\B2b\PortalAlliesPartnerPortalRequest;
use App\Http\Requests\Portal\B2b\PortalAlliesValidateTicketRequest;
use App\Http\Requests\Portal\B2b\PortalAlliesExecuteSettlementRequest;
use App\Http\Requests\Portal\B2b\StoreAllyRequest;
use App\Http\Requests\Portal\B2b\UpdateAllyRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PortalAlliesController extends Controller
{
    /**
     * Display the Allies Directory, Campaigns Dir A, Attribution Dir B and Clearing House.
     */
    public function index(Request $request)
    {
        Gate::authorize('manage-b2b');

        $data = $this->getAlliesData($request);

        return view('portal.allies', array_merge($data, [
            'initialMode' => 'admin',
        ]));
    }

    protected function getAlliesData(?Request $request = null): array
    {
        if (Ally::count() === 0) {
            (new \Database\Seeders\AlliesSeeder())->run();
        }

        $allies = Ally::with(['campaignsDirA', 'couponsDirB', 'leadCaptures', 'settlements'])->get();

        $alliesList = $allies->map(function ($a) {
            $leadsReceived = $a->leadCaptures->count() ?: (int)$a->campaignsDirA->sum('leads_count');
            $ordersAttributed = (int)$a->couponsDirB->sum('orders_count');
            $gmvAttributed = (float)$a->couponsDirB->sum('revenue_attributed');
            $commissionEarned = (float)$a->couponsDirB->sum('commission_earned');

            return [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'category' => $a->category,
                'categoryLabel' => $a->category_label ?? ucfirst(str_replace('_', ' ', $a->category)),
                'universe' => $a->universe,
                'contactName' => $a->contact_name,
                'contactWhatsapp' => $a->contact_whatsapp,
                'contactEmail' => $a->contact_email,
                'socialHandle' => $a->social_handle,
                'portalToken' => $a->portal_token,
                'commissionType' => $a->commission_type,
                'commissionRate' => (float)$a->commission_rate,
                'leadAcquisitionCost' => (float)$a->lead_acquisition_cost,
                'bankClabe' => $a->bank_clabe,
                'bankName' => $a->bank_name,
                'clearingBalance' => (float)$a->clearing_balance,
                'status' => $a->status,
                'notes' => $a->notes,
                'allowedDiscountTypes' => $a->allowed_discount_types ?: ['cover', 'cover_2x1', 'free_drink', 'store_discount', 'vip_access'],
                'maxDiscountPct' => (float)($a->max_discount_pct ?: 20.00),
                'stats' => [
                    'leadsReceived' => $leadsReceived,
                    'ordersAttributed' => $ordersAttributed,
                    'gmvAttributed' => $gmvAttributed,
                    'commissionEarned' => $commissionEarned,
                ]
            ];
        })->toArray();

        $campaignsDirA = AllyCampaignDirA::with('ally')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'allyId' => $c->ally_id,
                'allyName' => $c->ally?->name ?? 'Aliado',
                'title' => $c->title,
                'slug' => $c->slug,
                'universe' => $c->universe,
                'benefit' => $c->benefit,
                'discountType' => $c->discount_type ?? 'benefit',
                'instructions' => $c->instructions,
                'displayFormat' => $c->display_format,
                'leadsCount' => (int)$c->leads_count,
                'status' => $c->status,
                'rejectionReason' => $c->rejection_reason,
                'startDate' => $c->start_date ? Carbon::parse($c->start_date)->toDateString() : null,
                'endDate' => $c->end_date ? Carbon::parse($c->end_date)->toDateString() : null,
                'bannerImage' => $c->banner_image ?? 'default_campaign.webp',
            ];
        })->toArray();

        $pendingCampaigns = AllyCampaignDirA::with('ally')
            ->where('status', 'pending_approval')
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'allyId' => $c->ally_id,
                    'allyName' => $c->ally?->name ?? 'Aliado',
                    'allyCategory' => $c->ally?->category ?? 'general',
                    'allyCategoryLabel' => $c->ally?->category_label ?? ucfirst(str_replace('_', ' ', $c->ally?->category ?? 'general')),
                    'allyUniverse' => $c->ally?->universe ?? 'marte',
                    'title' => $c->title,
                    'slug' => $c->slug,
                    'universe' => $c->universe,
                    'benefit' => $c->benefit,
                    'discountType' => $c->discount_type ?? 'benefit',
                    'instructions' => $c->instructions,
                    'displayFormat' => $c->display_format,
                    'leadsCount' => (int)$c->leads_count,
                    'status' => $c->status,
                    'rejectionReason' => $c->rejection_reason,
                    'startDate' => $c->start_date ? Carbon::parse($c->start_date)->toDateString() : null,
                    'endDate' => $c->end_date ? Carbon::parse($c->end_date)->toDateString() : null,
                    'bannerImage' => $c->banner_image ?? '/uploads/allies_posters/default_poster.webp',
                    'createdAt' => $c->created_at ? Carbon::parse($c->created_at)->diffForHumans() : 'Reciente',
                ];
            })->toArray();

        $leadCaptures = AllyLeadCapture::with(['ally', 'campaign'])->latest()->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'voucherCode' => $l->voucher_code,
                'campaignId' => $l->campaign_id,
                'allyId' => $l->ally_id,
                'leadName' => $l->lead_name,
                'leadWhatsapp' => $l->lead_whatsapp,
                'leadEmail' => $l->lead_email,
                'universe' => $l->universe,
                'isRedeemed' => (bool)$l->is_redeemed,
                'redeemedAt' => $l->redeemed_at ? Carbon::parse($l->redeemed_at)->format('H:i \h\r\s') : null,
                'redeemedBy' => $l->redeemed_by,
                'capiScore' => (float)$l->capi_match_score,
                'date' => $l->created_at ? Carbon::parse($l->created_at)->toDateString() : null,
            ];
        })->toArray();

        $couponsDirB = AllyCouponDirB::with('ally')->get()->map(function ($cp) {
            return [
                'id' => $cp->id,
                'allyId' => $cp->ally_id,
                'couponCode' => $cp->coupon_code,
                'discountPct' => (float)$cp->discount_pct,
                'commissionRate' => (float)$cp->commission_rate,
                'universe' => $cp->universe,
                'utmSource' => $cp->utm_source,
                'utmCampaign' => $cp->utm_campaign,
                'clicks' => (int)$cp->clicks_count,
                'orders' => (int)$cp->orders_count,
                'revenue' => (float)$cp->revenue_attributed,
                'commissionEarned' => (float)$cp->commission_earned,
                'status' => $cp->status,
            ];
        })->toArray();

        $clearingSettlements = AllyClearingSettlement::with('ally')->latest()->get()->map(function ($s) {
            $typeLabel = match ($s->settlement_type) {
                'spei_transfer' => 'Transferencia SPEI',
                'in_kind_apparel' => 'Canje en Especie',
                'sponsorship_vip' => 'Patrocinio VIP',
                default => 'Liquidación'
            };

            return [
                'id' => $s->id,
                'code' => $s->code,
                'allyId' => $s->ally_id,
                'allyName' => $s->ally?->name ?? 'Aliado',
                'period' => $s->period_label,
                'leadsCredit' => (float)$s->leads_credit,
                'commissionsDebit' => (float)$s->commissions_debit,
                'netBalance' => (float)$s->net_balance,
                'settlementType' => $s->settlement_type,
                'typeLabel' => $typeLabel,
                'status' => $s->status,
                'reference' => $s->accounting_reference,
                'settledAt' => $s->settled_at ? Carbon::parse($s->settled_at)->format('Y-m-d H:i') : null,
            ];
        })->toArray();

        $totalLeadsCredit = array_sum(array_map(fn($a) => $a['stats']['leadsReceived'] * $a['leadAcquisitionCost'], $alliesList));
        $totalCommissionsDebit = array_sum(array_column(array_column($alliesList, 'stats'), 'commissionEarned'));
        $totalNetBalance = $totalLeadsCredit - $totalCommissionsDebit;

        $selectedAllyId = $request ? $request->query('ally_id') : request()->query('ally_id');
        $selectedAlly = null;
        if ($selectedAllyId) {
            foreach ($alliesList as $ally) {
                if ($ally['id'] == $selectedAllyId) {
                    $selectedAlly = $ally;
                    break;
                }
            }
        }
        if (!$selectedAlly && count($alliesList) > 0) {
            $selectedAlly = $alliesList[0];
        }

        if (!$selectedAlly) {
            $selectedAlly = [
                'id' => 1,
                'code' => 'ALLY-GENERAL',
                'name' => 'Aliado Comercial General',
                'category' => 'general',
                'categoryLabel' => 'General',
                'universe' => 'marte',
                'contactName' => 'Coordinación Comercial',
                'contactWhatsapp' => '+525500000000',
                'contactEmail' => 'aliados@mitienda.com',
                'socialHandle' => '@mitienda',
                'portalToken' => 'tok_general_2026',
                'commissionType' => 'percentage',
                'commissionRate' => 10.0,
                'leadAcquisitionCost' => 20.0,
                'bankClabe' => '000000000000000000',
                'bankName' => 'Banco General',
                'clearingBalance' => 0.0,
                'status' => 'active',
                'notes' => 'Registro base',
                'allowedDiscountTypes' => ['cover', 'cover_2x1', 'free_drink', 'store_discount', 'vip_access'],
                'maxDiscountPct' => 20.0,
                'stats' => [
                    'leadsReceived' => 0,
                    'ordersAttributed' => 0,
                    'gmvAttributed' => 0.0,
                    'commissionEarned' => 0.0,
                ],
            ];
        }

        return [
            'alliesList' => $alliesList,
            'campaignsDirA' => $campaignsDirA,
            'pendingCampaigns' => $pendingCampaigns,
            'leadCaptures' => $leadCaptures,
            'couponsDirB' => $couponsDirB,
            'clearingSettlements' => $clearingSettlements,
            'totalLeadsCredit' => $totalLeadsCredit,
            'totalCommissionsDebit' => $totalCommissionsDebit,
            'totalNetBalance' => $totalNetBalance,
            'selectedAlly' => $selectedAlly,
        ];
    }

    /**
     * Extranet for Commercial Partners (Partner Portal via cryptographic token).
     */
    public function partnerPortal(PortalAlliesPartnerPortalRequest $request, $token = null)
    {
        if (!$token) {
            Gate::authorize('manage-b2b');
        }

        $allies = Ally::with(['campaignsDirA', 'couponsDirB', 'leadCaptures', 'settlements'])->get();
        $selectedAllyModel = null;

        if ($token) {
            $selectedAllyModel = Ally::where('portal_token', $token)->first();
        }

        if (!$selectedAllyModel && $allies->isNotEmpty()) {
            $selectedAllyModel = $allies->first();
        }

        $indexData = $this->getAlliesData($request);

        if ($selectedAllyModel) {
            foreach ($indexData['alliesList'] as $a) {
                if ($a['id'] == $selectedAllyModel->id) {
                    $indexData['selectedAlly'] = $a;
                    break;
                }
            }
        }

        return view('portal.allies', array_merge($indexData, [
            'initialMode' => 'partner',
            'partnerToken' => $token,
        ]));
    }

    /**
     * Validate and redeem a voucher at door / box office (TASK-ALY-05).
     */
    public function validateTicket(PortalAlliesValidateTicketRequest $request)
    {
        Gate::authorize('manage-b2b');

        

        $code = strtoupper(trim($request->input('voucher_code')));
        $lead = AllyLeadCapture::where('voucher_code', $code)->first();

        if (!$lead) {
            return response()->json([
                'success' => false,
                'message' => "Folio de voucher {$code} no existe en la base de datos.",
            ], 404);
        }

        if ($lead->is_redeemed) {
            $time = $lead->redeemed_at ? Carbon::parse($lead->redeemed_at)->format('d/m/Y H:i') : 'previamente';
            return response()->json([
                'success' => false,
                'already_redeemed' => true,
                'message' => "Este voucher ({$code}) ya fue canjeado el {$time} por {$lead->redeemed_by}.",
                'lead_name' => $lead->lead_name,
                'redeemed_at' => $lead->redeemed_at,
                'redeemed_by' => $lead->redeemed_by,
            ], 422);
        }

        $redeemedBy = auth()->user()?->name ?? 'Taquilla RevOps';
        $lead->update([
            'is_redeemed' => true,
            'redeemed_at' => Carbon::now(),
            'redeemed_by' => $redeemedBy,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Acceso autorizado. Voucher {$code} canjeado exitosamente.",
            'lead_name' => $lead->lead_name,
            'voucher_code' => $lead->voucher_code,
            'universe' => $lead->universe,
            'redeemed_at' => Carbon::now()->format('H:i \h\r\s'),
            'redeemed_by' => $redeemedBy,
        ]);
    }

    /**
     * Execute a clearing settlement for an ally (SPEI or in-kind apparel) (TASK-ALY-04).
     */
    public function executeSettlement(PortalAlliesExecuteSettlementRequest $request, Ally $ally)
    {
        Gate::authorize('manage-b2b');

        $validated = $request->validated();

        $type = $validated['settlement_type'] ?? 'spei_transfer';

        $leadsCount = $ally->leadCaptures()->count() ?: (int)$ally->campaignsDirA()->sum('leads_count');
        $leadsCredit = $leadsCount * (float)$ally->lead_acquisition_cost;
        $commissionsDebit = (float)$ally->couponsDirB()->sum('commission_earned');
        $netBalance = $leadsCredit - $commissionsDebit;

        $code = 'SETTL-' . date('Ym') . '-' . sprintf('%02d', $ally->id) . '-' . rand(10, 99);
        $reference = match ($type) {
            'in_kind_apparel' => 'SALIDA_CONVENIO_ALIADO #OC-KARDEX-' . rand(100, 999),
            'sponsorship_vip' => 'CONVENIO_PATROCINIO #PAT-' . rand(100, 999),
            default => 'SPEI-BNX-' . rand(10000000, 99999999),
        };

        $settlement = AllyClearingSettlement::create([
            'code' => $code,
            'ally_id' => $ally->id,
            'period_label' => Carbon::now()->startOfMonth()->format('d/m/Y') . ' - ' . Carbon::now()->format('d/m/Y'),
            'leads_credit' => $leadsCredit,
            'commissions_debit' => $commissionsDebit,
            'net_balance' => $netBalance,
            'settlement_type' => $type,
            'status' => 'executed',
            'accounting_reference' => $reference,
            'settled_at' => Carbon::now(),
        ]);

        $ally->update([
            'clearing_balance' => 0.00,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Liquidación {$code} ejecutada exitosamente.",
                'settlement' => $settlement,
            ]);
        }

        return back()->with('success', "Liquidación {$code} ({$type}) ejecutada y conciliada exitosamente.");
    }

    /**
     * Export door list as CSV for door staff validation with masked phones (TASK-ALY-05).
     */
    public function exportDoorListCsv(Ally $ally)
    {
        return $this->generateCsvForAlly($ally);
    }

    /**
     * Export door list as CSV by partner token.
     */
    public function exportDoorListCsvByToken($token)
    {
        $ally = Ally::where('portal_token', $token)->firstOrFail();
        return $this->generateCsvForAlly($ally);
    }

    /**
     * Helper to stream CSV with UTF-8 BOM.
     */
    protected function generateCsvForAlly(Ally $ally)
    {
        $leads = $ally->leadCaptures()->latest()->get();
        $filename = "lista_taquilla_{$ally->code}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($leads) {
            // UTF-8 BOM
            echo "\xEF\xBB\xBF";
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Folio Voucher',
                'Nombre Asistente',
                'WhatsApp Enmascarado',
                'Email',
                'Universo',
                'Canjeado',
                'Fecha Canje',
                'Validado Por',
                'Score CAPI'
            ]);

            foreach ($leads as $l) {
                // Mask phone: 55 **** 1234
                $phone = $l->lead_whatsapp ?? '';
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($cleanPhone) >= 8) {
                    $maskedPhone = substr($cleanPhone, 0, 2) . ' **** ' . substr($cleanPhone, -4);
                } else {
                    $maskedPhone = $phone;
                }

                fputcsv($handle, [
                    $l->voucher_code,
                    $l->lead_name,
                    $maskedPhone,
                    $l->lead_email,
                    $l->universe,
                    $l->is_redeemed ? 'SI' : 'NO',
                    $l->redeemed_at ? Carbon::parse($l->redeemed_at)->format('Y-m-d H:i:s') : '',
                    $l->redeemed_by ?? '',
                    $l->capi_match_score ?? ''
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Submit an event or benefit campaign from the Ally Extranet with official poster.
     */
    /**
     * Submit an event or benefit campaign from the Ally Extranet with official poster.
     */
    public function submitCampaign(Request $request, $token)
    {
        $ally = Ally::where('portal_token', $token)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'benefit' => 'required|string|max:200',
            'discount_type' => 'nullable|string|max:50',
            'instructions' => 'nullable|string',
            'universe' => 'nullable|string|max:30',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'banner_image' => 'nullable|image|max:5120',
        ]);

        $discountType = $validated['discount_type'] ?? 'store_discount';
        if (is_array($ally->allowed_discount_types) && !empty($ally->allowed_discount_types)) {
            if (!in_array($discountType, $ally->allowed_discount_types, true)) {
                $discountType = $ally->allowed_discount_types[0];
            }
        }

        $posterUrl = null;
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $fileName = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $destPath = public_path('uploads/allies_posters');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $fileName);
            $posterUrl = '/uploads/allies_posters/' . $fileName;
        }

        $slug = Str::slug($validated['title']) . '-' . rand(100, 999);

        $campaign = AllyCampaignDirA::create([
            'ally_id' => $ally->id,
            'title' => $validated['title'],
            'slug' => $slug,
            'universe' => $validated['universe'] ?? $ally->universe ?? 'marte',
            'benefit' => $validated['benefit'],
            'discount_type' => $discountType,
            'instructions' => $validated['instructions'] ?? 'Presentar voucher oficial en taquilla o acceso digital',
            'display_format' => 'popup_modal',
            'status' => 'pending_approval',
            'start_date' => $validated['start_date'] ?? now(),
            'end_date' => $validated['end_date'] ?? now()->addMonths(2),
            'banner_image' => $posterUrl ?? '/uploads/allies_posters/default_poster.webp',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Evento y póster oficial enviados a revisión con Marketing exitosamente.',
                'campaign' => $campaign,
            ]);
        }

        return back()->with('success', 'Solicitud de evento y promoción enviada a revisión. Una vez autorizada se activará automáticamente en Storefront y Pop-ups.');
    }

    /**
     * Marketing/Superadmin approves an ally campaign and activates it for storefront and pop-ups.
     */
    public function approveCampaign(Request $request, $id)
    {
        Gate::authorize('manage-b2b');

        $campaign = AllyCampaignDirA::findOrFail($id);

        $validated = $request->validate([
            'benefit' => 'nullable|string|max:200',
            'discount_type' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,ended,pending_approval,rejected',
        ]);

        $campaign->update([
            'status' => 'active',
            'benefit' => !empty($validated['benefit']) ? $validated['benefit'] : $campaign->benefit,
            'discount_type' => !empty($validated['discount_type']) ? $validated['discount_type'] : $campaign->discount_type,
            'start_date' => !empty($validated['start_date']) ? $validated['start_date'] : $campaign->start_date,
            'end_date' => !empty($validated['end_date']) ? $validated['end_date'] : $campaign->end_date,
            'rejection_reason' => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Campaña de aliado aprobada y sincronizada automáticamente en Storefront y Pop-ups.',
                'campaign' => $campaign,
            ]);
        }

        return back()->with('success', "Campaña '{$campaign->title}' aprobada y sincronizada automáticamente en Storefront y Pop-ups de Hub 2.");
    }

    /**
     * Superadmin rejects an ally campaign request with reason.
     */
    public function rejectCampaign(Request $request, $id)
    {
        Gate::authorize('manage-b2b');

        $campaign = AllyCampaignDirA::findOrFail($id);
        $reason = $request->input('reason', 'La solicitud no cumple con los criterios comerciales vigentes.');

        $campaign->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Solicitud '{$campaign->title}' rechazada exitosamente.",
                'campaign' => $campaign,
            ]);
        }

        return back()->with('success', "Solicitud '{$campaign->title}' rechazada.");
    }

    /**
     * Registra manualmente un nuevo aliado comercial y sus acuerdos comerciales (TASK-HUB-7 / Engranaje Navbar).
     */
    public function storeAlly(StoreAllyRequest $request)
    {
        Gate::authorize('manage-b2b');

        $validated = $request->validated();
        $name = trim($validated['name']);

        $cleanCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 8));
        if ($cleanCode === '') {
            $cleanCode = 'ALLY';
        }
        $code = ! empty($validated['code']) 
            ? strtoupper(trim($validated['code']))
            : 'ALLY-' . $cleanCode . '-' . rand(100, 999);

        $category = $validated['category'] ?? 'circuit_party';
        $categoryLabels = [
            'circuit_party' => 'Festival Circuit & Eventos',
            'onlyfans_creator' => 'Creador OnlyFans',
            'sauna' => 'Sauna & Wellness',
            'beauty_spa' => 'Boutique & Spa',
            'drag_ballroom' => 'Ballroom & Drag House',
            'fitness_gym' => 'Fitness & Gym',
            'bar_nightclub' => 'Bar & Nightclub',
            'general' => 'Alianza Comercial',
        ];
        $categoryLabel = $validated['category_label'] ?? ($categoryLabels[$category] ?? ucfirst(str_replace('_', ' ', $category)));

        $allowedDiscounts = isset($validated['allowed_discount_types']) && is_array($validated['allowed_discount_types'])
            ? array_values(array_unique($validated['allowed_discount_types']))
            : ['cover', 'cover_2x1', 'free_drink', 'store_discount', 'vip_access'];

        $portalToken = 'tok_' . strtolower($cleanCode) . '_' . Str::random(16);

        $ally = Ally::create([
            'name' => $name,
            'code' => $code,
            'category' => $category,
            'category_label' => $categoryLabel,
            'universe' => $validated['universe'] ?? 'marte',
            'contact_name' => $validated['contact_name'] ?? null,
            'contact_whatsapp' => $validated['contact_whatsapp'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'social_handle' => $validated['social_handle'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_clabe' => $validated['bank_clabe'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'portal_token' => $portalToken,
            'allowed_discount_types' => $allowedDiscounts,
            'max_discount_pct' => (float)($validated['max_discount_pct'] ?? 20.00),
            'lead_acquisition_cost' => (float)($validated['lead_acquisition_cost'] ?? 20.00),
            'commission_type' => $validated['commission_type'] ?? 'percentage',
            'commission_rate' => (float)($validated['commission_rate'] ?? 12.00),
            'clearing_balance' => 0.00,
            'status' => 'active',
        ]);

        // Registrar cupón inicial determinista para Atribución Dirección B
        $couponCode = $cleanCode . (int)($ally->max_discount_pct ?: 15);
        if (!AllyCouponDirB::where('coupon_code', $couponCode)->exists()) {
            AllyCouponDirB::create([
                'ally_id' => $ally->id,
                'coupon_code' => $couponCode,
                'discount_pct' => (float)($ally->max_discount_pct ?: 15.00),
                'commission_rate' => (float)($ally->commission_rate ?: 12.00),
                'universe' => $ally->universe,
                'utm_source' => $ally->code,
                'utm_campaign' => strtolower($cleanCode) . '_launch_2026',
                'clicks_count' => 0,
                'orders_count' => 0,
                'revenue_attributed' => 0.00,
                'commission_earned' => 0.00,
                'status' => 'active',
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Aliado comercial '{$ally->name}' registrado exitosamente con acuerdos comerciales.",
                'ally' => $ally,
            ], 201);
        }

        return back()->with('success', "Aliado '{$ally->name}' registrado exitosamente en el Directorio oficial con token de extranet.");
    }

    /**
     * Actualiza la configuración y acuerdos de un aliado comercial existente (Engranaje Navbar / Hub 7).
     */
    public function updateAlly(UpdateAllyRequest $request, Ally $ally)
    {
        Gate::authorize('manage-b2b');

        $validated = $request->validated();

        $category = $validated['category'] ?? $ally->category;
        $categoryLabels = [
            'circuit_party' => 'Festival Circuit & Eventos',
            'onlyfans_creator' => 'Creador OnlyFans',
            'sauna' => 'Sauna & Wellness',
            'beauty_spa' => 'Boutique & Spa',
            'drag_ballroom' => 'Ballroom & Drag House',
            'fitness_gym' => 'Fitness & Gym',
            'bar_nightclub' => 'Bar & Nightclub',
            'general' => 'Alianza Comercial',
        ];
        $categoryLabel = $validated['category_label'] ?? ($categoryLabels[$category] ?? $ally->category_label);

        $allowedDiscounts = isset($validated['allowed_discount_types']) && is_array($validated['allowed_discount_types'])
            ? array_values(array_unique($validated['allowed_discount_types']))
            : ($ally->allowed_discount_types ?: ['cover', 'cover_2x1', 'free_drink', 'store_discount', 'vip_access']);

        $ally->update([
            'name' => trim($validated['name']),
            'code' => !empty($validated['code']) ? strtoupper(trim($validated['code'])) : $ally->code,
            'category' => $category,
            'category_label' => $categoryLabel,
            'universe' => $validated['universe'] ?? $ally->universe,
            'contact_name' => $validated['contact_name'] ?? $ally->contact_name,
            'contact_whatsapp' => $validated['contact_whatsapp'] ?? $ally->contact_whatsapp,
            'contact_email' => $validated['contact_email'] ?? $ally->contact_email,
            'social_handle' => $validated['social_handle'] ?? $ally->social_handle,
            'bank_name' => $validated['bank_name'] ?? $ally->bank_name,
            'bank_clabe' => $validated['bank_clabe'] ?? $ally->bank_clabe,
            'notes' => $validated['notes'] ?? $ally->notes,
            'allowed_discount_types' => $allowedDiscounts,
            'max_discount_pct' => (float)($validated['max_discount_pct'] ?? $ally->max_discount_pct),
            'lead_acquisition_cost' => (float)($validated['lead_acquisition_cost'] ?? $ally->lead_acquisition_cost),
            'commission_type' => $validated['commission_type'] ?? $ally->commission_type,
            'commission_rate' => (float)($validated['commission_rate'] ?? $ally->commission_rate),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Configuración de aliado '{$ally->name}' actualizada exitosamente.",
                'ally' => $ally,
            ]);
        }

        return back()->with('success', "Configuración del aliado '{$ally->name}' actualizada correctamente.");
    }
}

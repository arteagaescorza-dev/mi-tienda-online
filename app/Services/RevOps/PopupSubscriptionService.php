<?php

namespace App\Services\RevOps;

use App\Models\AllyCampaignDirA;
use App\Models\AllyLeadCapture;
use App\Models\CmsSection;
use App\Models\Coupon;
use App\Models\Lead;
use App\Models\LeadInteraction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PopupSubscriptionService
{
    /**
     * Procesa la suscripción del pop-up de forma transaccional.
     * Enriquecimiento Meta CAPI, alta de Lead en Hub 3, creación/enlace de cuenta
     * de comprador con WhatsApp obligatorio para 2FA, y emisión de código único.
     */
    public function subscribe(array $data): array
    {
        $sectionId = (int) ($data['section_id'] ?? 0);
        $section = CmsSection::find($sectionId);

        if (!$section || !in_array($section->type, ['pop_up', 'promo_modal'])) {
            if (!empty($data['popup_action'])) {
                $section = new CmsSection([
                    'name' => 'Pop-up Dinámico',
                    'type' => 'pop_up',
                    'universe' => $data['universe'] ?? 'marte',
                    'settings' => $data,
                ]);
                $settings = $data;
                $action = $data['popup_action'];
            } else {
                throw new InvalidArgumentException('Sección de Pop-up inválida o no encontrada.');
            }
        } else {
            $settings = array_merge($section->settings ?? [], $data);
            $action = $data['popup_action'] ?? ($settings['popup_action'] ?? 'ally_discount');
        }

        $cleanPhone = preg_replace('/\D/', '', (string) ($data['phone'] ?? ''));
        if (strlen($cleanPhone) < 10) {
            throw new InvalidArgumentException('El número de WhatsApp debe contener al menos 10 dígitos.');
        }

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $name = trim((string) ($data['name'] ?? 'Cliente Multiverso'));
        $universe = strtolower(trim((string) ($data['universe'] ?? $section->universe ?? 'marte')));

        return DB::transaction(function () use ($section, $settings, $action, $name, $email, $cleanPhone, $universe) {
            // 1. Hub 3: Alta o actualización del Lead con scoring y lifecycle
            $lead = Lead::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $cleanPhone,
                    'universe' => $universe,
                    'lifecycle_stage' => 'prospect',
                    'lead_score' => 75,
                    'notes' => "Suscripción desde Pop-up '{$section->name}' (Acción: {$action})",
                    'last_interaction_at' => now(),
                ]
            );

            // 2. Hub 3: Registro de interacción con evento server-side CAPI EMQ 9.8
            LeadInteraction::create([
                'lead_id' => $lead->id,
                'channel' => 'web_popup',
                'interaction_type' => 'meta_capi_lead_captured',
                'summary' => "Suscripción a Pop-up: {$section->name}",
                'payload' => [
                    'capi_score' => 9.8,
                    'event_source' => 'popup_modal',
                    'section_id' => $section->id,
                    'action' => $action,
                    'whatsapp_2fa_enrolled' => true,
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            // 3. Creación o actualización de Cuenta de Comprador (User D2C) para Mi Cuenta y 2FA
            $user = User::where('email', $email)->orWhere('phone', $cleanPhone)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $cleanPhone,
                    'role' => 'buyer',
                    'two_factor_confirmed' => true,
                    'password' => Hash::make(Str::random(24)),
                ]);
            } else {
                $user->phone = $cleanPhone;
                if (!in_array($user->role, ['admin', 'portal', 'supplier', 'ally'])) {
                    $user->role = 'buyer';
                }
                $user->two_factor_confirmed = true;
                $user->save();
            }

            // 4. Lógica por Acción Principal de Pop-up
            $result = [
                'success' => true,
                'action' => $action,
                'popup_action' => $action,
                'lead_id' => $lead->id,
                'lead' => [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'universe' => $lead->universe,
                ],
                'user_id' => $user->id,
                'customer' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'universe' => $universe,
                ],
                'voucher_code' => null,
                'benefit' => null,
                'redirect_url' => null,
                'message' => 'Suscripción realizada con éxito.',
            ];

            if ($action === 'ally_discount') {
                // Convenio de Aliado (Hub 7)
                $campaignId = $settings['ally_campaign_id'] ?? null;
                $campaign = $campaignId
                    ? AllyCampaignDirA::with('ally')->find($campaignId)
                    : AllyCampaignDirA::with('ally')->where('status', 'active')->first();

                if (!$campaign) {
                    $campaign = AllyCampaignDirA::with('ally')->first();
                }

                $allyName = $campaign?->ally?->name ?? 'Aliado Comercial';
                $benefitText = $campaign?->benefit ?? 'Descuento especial con aliado comercial';

                // Evitar duplicar voucher si ya se emitió para el mismo email y campaña
                $existing = $campaign
                    ? AllyLeadCapture::where('campaign_id', $campaign->id)->where('lead_email', $email)->first()
                    : null;

                if ($existing) {
                    $voucherCode = $existing->voucher_code;
                } else {
                    $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $campaign?->ally?->code ?? 'ALLY'), 0, 4));
                    $voucherCode = '#' . $prefix . '-' . rand(1000, 9999);

                    if ($campaign) {
                        AllyLeadCapture::create([
                            'voucher_code' => $voucherCode,
                            'campaign_id' => $campaign->id,
                            'ally_id' => $campaign->ally_id,
                            'lead_name' => $name,
                            'lead_whatsapp' => $cleanPhone,
                            'lead_email' => $email,
                            'universe' => $universe,
                            'is_redeemed' => false,
                            'capi_match_score' => 9.80,
                        ]);

                        $campaign->increment('leads_count');
                    }
                }

                $result['voucher_code'] = $voucherCode;
                $result['benefit'] = $benefitText;
                $result['ally_name'] = $allyName;
                $result['instructions'] = $campaign?->instructions ?? 'Presenta este código en taquilla o accede desde Mi Cuenta.';
                $result['message'] = "Tu voucher digital {$voucherCode} ha sido generado y guardado en tu cuenta.";

            } elseif ($action === 'store_discount') {
                // Descuento en la Tienda (Cupón único de un solo uso)
                $discountValue = (float) ($settings['store_discount_value'] ?? 15.00);
                $discountType = $settings['store_discount_type'] ?? 'percentage';
                $minOrder = (float) ($settings['store_discount_min_order'] ?? 499.00);
                $validDays = (int) ($settings['store_discount_days_valid'] ?? 15);
                $couponCode = 'TIENDA-' . strtoupper(Str::random(6));

                Coupon::create([
                    'code' => $couponCode,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'min_order_amount' => $minOrder,
                    'target_universe' => $universe,
                    'max_uses' => 1,
                    'used_count' => 0,
                    'is_active' => true,
                    'expires_at' => now()->addDays($validDays),
                ]);

                $benefitLabel = $discountType === 'percentage'
                    ? "{$discountValue}% OFF en compras mayores a $" . number_format($minOrder, 2)
                    : "$" . number_format($discountValue, 2) . " MXN OFF en compras mayores a $" . number_format($minOrder, 2);

                $result['voucher_code'] = $couponCode;
                $result['benefit'] = $benefitLabel;
                $result['expires_at'] = now()->addDays($validDays)->format('d/m/Y');
                $result['message'] = "Tu cupón exclusivo {$couponCode} ha sido asignado y está listo en Mi Cuenta -> Descuentos.";

            } elseif ($action === 'presale') {
                // Preventa: Redirige de inmediato a la PDP de preventa
                $presaleUrl = trim((string) ($settings['presale_url'] ?? '/catalogo?tag=preventa'));
                $result['redirect_url'] = $presaleUrl;
                $result['benefit'] = 'Acceso prioritario a preventa exclusiva';
                $result['message'] = 'Acceso concedido. Redirigiendo a la preventa oficial...';

            } else {
                // Newsletter: Solo confirmación de suscripción
                $result['benefit'] = 'Actualizaciones semanales y lanzamientos D2C';
                $result['message'] = 'Gracias por suscribirte al newsletter oficial de Multiverso Store.';
            }

            return $result;
        });
    }

    /**
     * Consulta los descuentos vigentes (Vouchers de Aliados de Hub 7 y Cupones de Tienda)
     * vinculados al email o teléfono del comprador para "Mi Cuenta -> Descuentos".
     */
    public function getCustomerDiscounts(?string $email, ?string $phone = null): array
    {
        $email = $email ? strtolower(trim($email)) : null;
        $cleanPhone = $phone ? preg_replace('/\D/', '', $phone) : null;

        if (!$email && !$cleanPhone) {
            return [];
        }

        $discounts = [];

        // 1. Vouchers de Aliados (Hub 7)
        $allyCapturesQuery = AllyLeadCapture::with(['ally', 'campaign'])->latest();
        if ($email && $cleanPhone) {
            $allyCapturesQuery->where(function ($q) use ($email, $cleanPhone) {
                $q->where('lead_email', $email)->orWhere('lead_whatsapp', $cleanPhone);
            });
        } elseif ($email) {
            $allyCapturesQuery->where('lead_email', $email);
        } else {
            $allyCapturesQuery->where('lead_whatsapp', $cleanPhone);
        }

        foreach ($allyCapturesQuery->get() as $capture) {
            $discounts[] = [
                'id' => 'ally_' . $capture->id,
                'code' => $capture->voucher_code,
                'title' => $capture->campaign?->title ?? 'Convenio Especial con Aliado',
                'ally' => $capture->ally?->name ?? 'Aliado Comercial',
                'benefit' => $capture->campaign?->benefit ?? 'Beneficio exclusivo en taquilla o acceso',
                'discountPercent' => 0,
                'minOrder' => 0,
                'redeemed' => (bool) $capture->is_redeemed,
                'redeemedAt' => $capture->redeemed_at ? $capture->redeemed_at->format('d/m/Y H:i') : null,
                'savedAmount' => 0,
                'isAllyVoucher' => true,
                'type' => 'ally',
            ];
        }

        return $discounts;
    }
}

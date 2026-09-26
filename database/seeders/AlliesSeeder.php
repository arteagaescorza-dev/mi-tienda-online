<?php

namespace Database\Seeders;

use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyClearingSettlement;
use App\Models\AllyCouponDirB;
use App\Models\AllyLeadCapture;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AlliesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or Update Allies with realistic data
        $paradiso = Ally::updateOrCreate(
            ['code' => 'ALLY-PARADISO'],
            [
                'name' => 'Paradiso Circuit CDMX',
                'category' => 'circuit_party',
                'category_label' => 'Festival Circuit & Eventos',
                'universe' => 'marte',
                'contact_name' => 'Rodrigo Silva (Promotor)',
                'contact_whatsapp' => '+525544332211',
                'contact_email' => 'contacto@paradisocircuit.mx',
                'social_handle' => '@paradisocdmx',
                'portal_token' => 'tok_paradiso_2026',
                'commission_type' => 'percentage',
                'commission_rate' => 12.00,
                'lead_acquisition_cost' => 20.00,
                'bank_clabe' => '012180015678901234',
                'bank_name' => 'BBVA México',
                'clearing_balance' => -4280.00,
                'status' => 'active',
                'notes' => 'Festival Circuit mensual en Frontón México. Convenio de 20% OFF en cover a cambio de registro CAPI.',
            ]
        );

        $alex = Ally::updateOrCreate(
            ['code' => 'ALLY-ALEX-OF'],
            [
                'name' => 'Alex Rivera (Creator & Model)',
                'category' => 'onlyfans_creator',
                'category_label' => 'Creador OnlyFans',
                'universe' => 'marte',
                'contact_name' => 'Alex Rivera',
                'contact_whatsapp' => '+525533221100',
                'contact_email' => 'alex.of@riveramodels.com',
                'social_handle' => '@alexriverafit',
                'portal_token' => 'tok_alex_of_2026',
                'commission_type' => 'percentage',
                'commission_rate' => 15.00,
                'lead_acquisition_cost' => 25.00,
                'bank_clabe' => '072180002345678901',
                'bank_name' => 'Banorte',
                'clearing_balance' => -2150.00,
                'status' => 'active',
                'notes' => 'Top Creator OnlyFans y embajador de jocks y arneses Marte.',
            ]
        );

        $sodome = Ally::updateOrCreate(
            ['code' => 'ALLY-SODOME'],
            [
                'name' => 'Sodome Spa & Club CDMX',
                'category' => 'sauna',
                'category_label' => 'Sauna & Wellness',
                'universe' => 'marte',
                'contact_name' => 'Gerencia Sodome',
                'contact_whatsapp' => '+525555112233',
                'contact_email' => 'eventos@sodomespa.mx',
                'social_handle' => '@sodomeclub',
                'portal_token' => 'tok_sodome_2026',
                'commission_type' => 'percentage',
                'commission_rate' => 10.00,
                'lead_acquisition_cost' => 18.00,
                'bank_clabe' => '002180098765432109',
                'bank_name' => 'Citibanamex',
                'clearing_balance' => 1450.00,
                'status' => 'active',
                'notes' => 'Sauna premium en Zona Rosa. Trago de cortesía en taquilla para clientes de Marte.',
            ]
        );

        $kiki = Ally::updateOrCreate(
            ['code' => 'ALLY-KIKI-BALL'],
            [
                'name' => 'House of Kiki Ballroom',
                'category' => 'drag_ballroom',
                'category_label' => 'Ballroom & Drag House',
                'universe' => 'tiresias',
                'contact_name' => 'Mother Kiki',
                'contact_whatsapp' => '+525599887766',
                'contact_email' => 'kiki@ballroomcdmx.org',
                'social_handle' => '@houseofkiki_cdmx',
                'portal_token' => 'tok_kiki_2026',
                'commission_type' => 'percentage',
                'commission_rate' => 14.00,
                'lead_acquisition_cost' => 22.00,
                'bank_clabe' => '014180054321098765',
                'bank_name' => 'Santander',
                'clearing_balance' => -1890.00,
                'status' => 'active',
                'notes' => 'Colectivo ballroom y comunidad trans. Promoción de binders afirmativos y patrocinios de categorías.',
            ]
        );

        $bella = Ally::updateOrCreate(
            ['code' => 'ALLY-BELLA'],
            [
                'name' => 'Bella Donna Spa & Boutique',
                'category' => 'beauty_spa',
                'category_label' => 'Boutique & Spa',
                'universe' => 'venus',
                'contact_name' => 'Valeria Rossi',
                'contact_whatsapp' => '+525566778899',
                'contact_email' => 'valeria@belladonnaboutique.mx',
                'social_handle' => '@belladonnaroma',
                'portal_token' => 'tok_bella_2026',
                'commission_type' => 'percentage',
                'commission_rate' => 12.00,
                'lead_acquisition_cost' => 20.00,
                'bank_clabe' => '127180011223344556',
                'bank_name' => 'Banco Azteca',
                'clearing_balance' => -3100.00,
                'status' => 'active',
                'notes' => 'Spa facial y boutique de lencería de autor en Roma Norte.',
            ]
        );

        // 2. Campaigns Dir A
        if ($paradiso) {
            AllyCampaignDirA::updateOrCreate(
                ['slug' => 'cover-paradiso-mayo-2026'],
                [
                    'ally_id' => $paradiso->id,
                    'title' => '20% OFF en Cover Paradiso Circuit Mayo',
                    'universe' => 'marte',
                    'benefit' => '20% de descuento en taquilla para el festival de Mayo',
                    'instructions' => 'Muestra tu código de voucher en taquilla para acceder al precio preferencial.',
                    'display_format' => 'popup_modal',
                    'leads_count' => 142,
                    'status' => 'active',
                    'start_date' => Carbon::now()->subDays(20)->toDateString(),
                    'end_date' => Carbon::now()->addDays(15)->toDateString(),
                    'banner_image' => 'paradiso_cover_banner.webp',
                ]
            );
        }

        if ($sodome) {
            AllyCampaignDirA::updateOrCreate(
                ['slug' => 'trago-cortesia-sodome'],
                [
                    'ally_id' => $sodome->id,
                    'title' => 'Trago de Bienvenida de Cortesía en Sodome',
                    'universe' => 'marte',
                    'benefit' => '1 bebida de cortesía (vodka, ron o cerveza artesanal) en recepción',
                    'instructions' => 'Presenta tu voucher digital al momento de registrar tu casillero en caja.',
                    'display_format' => 'top_marquee',
                    'leads_count' => 62,
                    'status' => 'active',
                    'start_date' => Carbon::now()->subDays(30)->toDateString(),
                    'end_date' => Carbon::now()->addDays(30)->toDateString(),
                    'banner_image' => 'sodome_welcome_banner.webp',
                ]
            );
        }

        if ($kiki) {
            AllyCampaignDirA::updateOrCreate(
                ['slug' => '2x1-kiki-ball-affirmative'],
                [
                    'ally_id' => $kiki->id,
                    'title' => 'Pase 2x1 Kiki Ball: Categoría Affirmative Realness',
                    'universe' => 'tiresias',
                    'benefit' => 'Pase 2x1 para espectador en el Ball de Verano',
                    'instructions' => 'Código válido en taquilla física hasta las 22:00 hrs el día del Ball.',
                    'display_format' => 'pdp_inline_banner',
                    'leads_count' => 35,
                    'status' => 'active',
                    'start_date' => Carbon::now()->subDays(10)->toDateString(),
                    'end_date' => Carbon::now()->addDays(20)->toDateString(),
                    'banner_image' => 'kiki_ball_banner.webp',
                ]
            );
        }

        // 3. Coupons Dir B
        if ($paradiso) {
            AllyCouponDirB::updateOrCreate(
                ['coupon_code' => 'PARADISO15'],
                [
                    'ally_id' => $paradiso->id,
                    'discount_pct' => 15.00,
                    'commission_rate' => 12.00,
                    'universe' => 'marte',
                    'utm_source' => 'paradiso',
                    'utm_campaign' => 'circuit_mayo',
                    'clicks_count' => 680,
                    'orders_count' => 58,
                    'revenue_attributed' => 39400.00,
                    'commission_earned' => 4728.00,
                    'status' => 'active',
                ]
            );
        }

        if ($alex) {
            AllyCouponDirB::updateOrCreate(
                ['coupon_code' => 'ALEXRIVERA'],
                [
                    'ally_id' => $alex->id,
                    'discount_pct' => 15.00,
                    'commission_rate' => 15.00,
                    'universe' => 'marte',
                    'utm_source' => 'alex_of',
                    'utm_campaign' => 'jock_campaign',
                    'clicks_count' => 420,
                    'orders_count' => 32,
                    'revenue_attributed' => 21800.00,
                    'commission_earned' => 3270.00,
                    'status' => 'active',
                ]
            );
        }

        if ($sodome) {
            AllyCouponDirB::updateOrCreate(
                ['coupon_code' => 'SODOME10'],
                [
                    'ally_id' => $sodome->id,
                    'discount_pct' => 10.00,
                    'commission_rate' => 10.00,
                    'universe' => 'marte',
                    'utm_source' => 'sodome',
                    'utm_campaign' => 'wellness_spa',
                    'clicks_count' => 190,
                    'orders_count' => 14,
                    'revenue_attributed' => 9800.00,
                    'commission_earned' => 980.00,
                    'status' => 'active',
                ]
            );
        }

        if ($kiki) {
            AllyCouponDirB::updateOrCreate(
                ['coupon_code' => 'KIKIBIND15'],
                [
                    'ally_id' => $kiki->id,
                    'discount_pct' => 15.00,
                    'commission_rate' => 14.00,
                    'universe' => 'tiresias',
                    'utm_source' => 'kiki_ball',
                    'utm_campaign' => 'binders_affirm',
                    'clicks_count' => 230,
                    'orders_count' => 20,
                    'revenue_attributed' => 13320.00,
                    'commission_earned' => 1864.80,
                    'status' => 'active',
                ]
            );
        }

        if ($bella) {
            AllyCouponDirB::updateOrCreate(
                ['coupon_code' => 'BELLA20'],
                [
                    'ally_id' => $bella->id,
                    'discount_pct' => 20.00,
                    'commission_rate' => 12.00,
                    'universe' => 'venus',
                    'utm_source' => 'bella_donna',
                    'utm_campaign' => 'lingerie_season',
                    'clicks_count' => 210,
                    'orders_count' => 18,
                    'revenue_attributed' => 14200.00,
                    'commission_earned' => 1704.00,
                    'status' => 'active',
                ]
            );
        }

        // 4. Lead Captures
        $campaignParadiso = AllyCampaignDirA::where('slug', 'cover-paradiso-mayo-2026')->first();
        $campaignSodome = AllyCampaignDirA::where('slug', 'trago-cortesia-sodome')->first();
        $campaignKiki = AllyCampaignDirA::where('slug', '2x1-kiki-ball-affirmative')->first();

        $leadsData = [
            [
                'voucher_code' => 'PAR-7821',
                'campaign_id' => $campaignParadiso?->id,
                'ally_id' => $paradiso?->id,
                'lead_name' => 'David Morales',
                'lead_whatsapp' => '+525541908812',
                'lead_email' => 'david.m@gmail.com',
                'universe' => 'marte',
                'is_redeemed' => true,
                'redeemed_at' => Carbon::now()->subDays(3)->setTime(14, 22),
                'redeemed_by' => 'Staff Puerta Taquilla #1',
                'capi_match_score' => 9.80,
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'voucher_code' => 'PAR-7822',
                'campaign_id' => $campaignParadiso?->id,
                'ally_id' => $paradiso?->id,
                'lead_name' => 'Carlos Santillán',
                'lead_whatsapp' => '+525511223344',
                'lead_email' => 'carlos.s@outlook.com',
                'universe' => 'marte',
                'is_redeemed' => false,
                'redeemed_at' => null,
                'redeemed_by' => null,
                'capi_match_score' => 9.65,
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'voucher_code' => 'PAR-7823',
                'campaign_id' => $campaignParadiso?->id,
                'ally_id' => $paradiso?->id,
                'lead_name' => 'Rodrigo Méndez',
                'lead_whatsapp' => '+525588990011',
                'lead_email' => 'rodrigo.m@gmail.com',
                'universe' => 'marte',
                'is_redeemed' => false,
                'redeemed_at' => null,
                'redeemed_by' => null,
                'capi_match_score' => 9.70,
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'voucher_code' => 'SOD-4412',
                'campaign_id' => $campaignSodome?->id,
                'ally_id' => $sodome?->id,
                'lead_name' => 'Eduardo Vera',
                'lead_whatsapp' => '+525599887711',
                'lead_email' => 'eduardo.v@gmail.com',
                'universe' => 'marte',
                'is_redeemed' => true,
                'redeemed_at' => Carbon::now()->subDays(2)->setTime(18, 45),
                'redeemed_by' => 'Caja Sodome',
                'capi_match_score' => 9.90,
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'voucher_code' => 'SOD-4413',
                'campaign_id' => $campaignSodome?->id,
                'ally_id' => $sodome?->id,
                'lead_name' => 'Mauricio Gómez',
                'lead_whatsapp' => '+525522334455',
                'lead_email' => 'mau.gomez@gmail.com',
                'universe' => 'marte',
                'is_redeemed' => false,
                'redeemed_at' => null,
                'redeemed_by' => null,
                'capi_match_score' => 9.60,
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'voucher_code' => 'KIK-9901',
                'campaign_id' => $campaignKiki?->id,
                'ally_id' => $kiki?->id,
                'lead_name' => 'Sasha Mendoza',
                'lead_whatsapp' => '+525577665544',
                'lead_email' => 'sasha.m@ballroom.org',
                'universe' => 'tiresias',
                'is_redeemed' => false,
                'redeemed_at' => null,
                'redeemed_by' => null,
                'capi_match_score' => 9.75,
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'voucher_code' => 'BEL-3321',
                'campaign_id' => null,
                'ally_id' => $bella?->id,
                'lead_name' => 'Fernanda Ortiz',
                'lead_whatsapp' => '+525588776655',
                'lead_email' => 'fer.ortiz@yahoo.com',
                'universe' => 'venus',
                'is_redeemed' => true,
                'redeemed_at' => Carbon::now()->subDays(1)->setTime(12, 10),
                'redeemed_by' => 'Recepción Bella Donna',
                'capi_match_score' => 9.50,
                'created_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($leadsData as $ld) {
            if ($ld['ally_id']) {
                AllyLeadCapture::updateOrCreate(
                    ['voucher_code' => $ld['voucher_code']],
                    $ld
                );
            }
        }

        // 5. Clearing Settlements
        if ($paradiso) {
            AllyClearingSettlement::updateOrCreate(
                ['code' => 'SETTL-202604-01'],
                [
                    'ally_id' => $paradiso->id,
                    'period_label' => '01/04/2026 - 30/04/2026',
                    'leads_credit' => 2400.00,
                    'commissions_debit' => 5200.00,
                    'net_balance' => -2800.00,
                    'settlement_type' => 'spei_transfer',
                    'status' => 'executed',
                    'accounting_reference' => 'SPEI-BNX-99882211',
                    'settled_at' => Carbon::now()->subMonth()->setTime(11, 30),
                ]
            );
        }

        if ($alex) {
            AllyClearingSettlement::updateOrCreate(
                ['code' => 'SETTL-202604-02'],
                [
                    'ally_id' => $alex->id,
                    'period_label' => '01/04/2026 - 30/04/2026',
                    'leads_credit' => 800.00,
                    'commissions_debit' => 3100.00,
                    'net_balance' => -2300.00,
                    'settlement_type' => 'in_kind_apparel',
                    'status' => 'executed',
                    'accounting_reference' => 'SALIDA_CONVENIO_ALIADO #OC-KARDEX-881',
                    'settled_at' => Carbon::now()->subMonth()->addDay()->setTime(16, 15),
                ]
            );
        }

        if ($sodome) {
            AllyClearingSettlement::updateOrCreate(
                ['code' => 'SETTL-202605-01'],
                [
                    'ally_id' => $sodome->id,
                    'period_label' => '01/05/2026 - 31/05/2026',
                    'leads_credit' => 1116.00,
                    'commissions_debit' => 980.00,
                    'net_balance' => 136.00,
                    'settlement_type' => 'spei_transfer',
                    'status' => 'executed',
                    'accounting_reference' => 'SPEI-CITI-44332211',
                    'settled_at' => Carbon::now()->subDays(15)->setTime(10, 0),
                ]
            );
        }
    }
}

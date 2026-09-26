<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PortalAlliesController extends Controller
{
    public function index(Request $request)
    {
        $alliesList = [
            [
                'id' => 1,
                'code' => 'ALLY-PARADISO',
                'name' => 'Paradiso Circuit CDMX',
                'category' => 'circuit_party',
                'categoryLabel' => 'Festival Circuit & Eventos',
                'universe' => 'marte',
                'contactName' => 'Rodrigo Silva (Promotor)',
                'contactWhatsapp' => '+525544332211',
                'contactEmail' => 'contacto@paradisocircuit.mx',
                'socialHandle' => '@paradisocdmx',
                'portalToken' => 'tok_paradiso_2026',
                'commissionType' => 'percentage',
                'commissionRate' => 12.0,
                'leadAcquisitionCost' => 20.0,
                'bankClabe' => '012180015678901234',
                'bankName' => 'BBVA México',
                'clearingBalance' => -4280.00,
                'status' => 'active',
                'notes' => 'Festival Circuit mensual en Frontón México. Convenio de 20% OFF en cover a cambio de registro CAPI.',
                'stats' => ['leadsReceived' => 142, 'ordersAttributed' => 58, 'gmvAttributed' => 39400, 'commissionEarned' => 4728]
            ],
            [
                'id' => 2,
                'code' => 'ALLY-ALEX-OF',
                'name' => 'Alex Rivera (Creator & Model)',
                'category' => 'onlyfans_creator',
                'categoryLabel' => 'Creador OnlyFans',
                'universe' => 'marte',
                'contactName' => 'Alex Rivera',
                'contactWhatsapp' => '+525533221100',
                'contactEmail' => 'alex.of@riveramodels.com',
                'socialHandle' => '@alexriverafit',
                'portalToken' => 'tok_alex_of_2026',
                'commissionType' => 'percentage',
                'commissionRate' => 15.0,
                'leadAcquisitionCost' => 25.0,
                'bankClabe' => '072180002345678901',
                'bankName' => 'Banorte',
                'clearingBalance' => -2150.00,
                'status' => 'active',
                'notes' => 'Top Creator OnlyFans y embajador de jocks y arneses Marte.',
                'stats' => ['leadsReceived' => 45, 'ordersAttributed' => 32, 'gmvAttributed' => 21800, 'commissionEarned' => 3270]
            ],
            [
                'id' => 3,
                'code' => 'ALLY-SODOME',
                'name' => 'Sodome Spa & Club CDMX',
                'category' => 'sauna',
                'categoryLabel' => 'Sauna & Wellness',
                'universe' => 'marte',
                'contactName' => 'Gerencia Sodome',
                'contactWhatsapp' => '+525555112233',
                'contactEmail' => 'eventos@sodomespa.mx',
                'socialHandle' => '@sodomeclub',
                'portalToken' => 'tok_sodome_2026',
                'commissionType' => 'percentage',
                'commissionRate' => 10.0,
                'leadAcquisitionCost' => 18.0,
                'bankClabe' => '002180098765432109',
                'bankName' => 'Citibanamex',
                'clearingBalance' => 1450.00,
                'status' => 'active',
                'notes' => 'Sauna premium en Zona Rosa. Trago de cortesía en taquilla para clientes de Marte.',
                'stats' => ['leadsReceived' => 62, 'ordersAttributed' => 14, 'gmvAttributed' => 9800, 'commissionEarned' => 980]
            ],
            [
                'id' => 4,
                'code' => 'ALLY-KIKI-BALL',
                'name' => 'House of Kiki Ballroom',
                'category' => 'drag_ballroom',
                'categoryLabel' => 'Ballroom & Drag House',
                'universe' => 'tiresias',
                'contactName' => 'Mother Kiki',
                'contactWhatsapp' => '+525599887766',
                'contactEmail' => 'kiki@ballroomcdmx.org',
                'socialHandle' => '@houseofkiki_cdmx',
                'portalToken' => 'tok_kiki_2026',
                'commissionType' => 'percentage',
                'commissionRate' => 14.0,
                'leadAcquisitionCost' => 22.0,
                'bankClabe' => '014180054321098765',
                'bankName' => 'Santander',
                'clearingBalance' => -1890.00,
                'status' => 'active',
                'notes' => 'Colectivo ballroom y comunidad trans. Promoción de binders afirmativos y patrocinios de categorías.',
                'stats' => ['leadsReceived' => 35, 'ordersAttributed' => 20, 'gmvAttributed' => 13320, 'commissionEarned' => 1864]
            ],
            [
                'id' => 5,
                'code' => 'ALLY-BELLA',
                'name' => 'Bella Donna Spa & Boutique',
                'category' => 'beauty_spa',
                'categoryLabel' => 'Boutique & Spa',
                'universe' => 'venus',
                'contactName' => 'Valeria Rossi',
                'contactWhatsapp' => '+525566778899',
                'contactEmail' => 'valeria@belladonnaboutique.mx',
                'socialHandle' => '@belladonnaroma',
                'portalToken' => 'tok_bella_2026',
                'commissionType' => 'percentage',
                'commissionRate' => 12.0,
                'leadAcquisitionCost' => 20.0,
                'bankClabe' => '127180011223344556',
                'bankName' => 'Banco Azteca',
                'clearingBalance' => -3100.00,
                'status' => 'active',
                'notes' => 'Spa facial y boutique de lencería de autor en Roma Norte.',
                'stats' => ['leadsReceived' => 28, 'ordersAttributed' => 18, 'gmvAttributed' => 14200, 'commissionEarned' => 1704]
            ]
        ];

        $campaignsDirA = [
            [
                'id' => 1,
                'allyId' => 1,
                'allyName' => 'Paradiso Circuit CDMX',
                'title' => '20% OFF en Cover Paradiso Circuit Mayo',
                'slug' => 'cover-paradiso-mayo-2026',
                'universe' => 'marte',
                'benefit' => '20% de descuento en taquilla para el festival de Mayo',
                'instructions' => 'Muestra tu código de voucher en taquilla para acceder al precio preferencial.',
                'displayFormat' => 'popup_modal',
                'leadsCount' => 142,
                'status' => 'active',
                'startDate' => '2026-05-01',
                'endDate' => '2026-05-31',
                'bannerImage' => 'paradiso_cover_banner.webp'
            ],
            [
                'id' => 2,
                'allyId' => 3,
                'allyName' => 'Sodome Spa & Club CDMX',
                'title' => 'Trago de Bienvenida de Cortesía en Sodome',
                'slug' => 'trago-cortesia-sodome',
                'universe' => 'marte',
                'benefit' => '1 bebida de cortesía (vodka, ron o cerveza artesanal) en recepción',
                'instructions' => 'Presenta tu voucher digital al momento de registrar tu casillero en caja.',
                'displayFormat' => 'top_marquee',
                'leadsCount' => 62,
                'status' => 'active',
                'startDate' => '2026-05-01',
                'endDate' => '2026-06-30',
                'bannerImage' => 'sodome_welcome_banner.webp'
            ],
            [
                'id' => 3,
                'allyId' => 4,
                'allyName' => 'House of Kiki Ballroom',
                'title' => 'Pase 2x1 Kiki Ball: Categoría Affirmative Realness',
                'slug' => '2x1-kiki-ball-affirmative',
                'universe' => 'tiresias',
                'benefit' => 'Pase 2x1 para espectador en el Ball de Verano',
                'instructions' => 'Código válido en taquilla física hasta las 22:00 hrs el día del Ball.',
                'displayFormat' => 'pdp_inline_banner',
                'leadsCount' => 35,
                'status' => 'active',
                'startDate' => '2026-05-15',
                'endDate' => '2026-06-15',
                'bannerImage' => 'kiki_ball_banner.webp'
            ]
        ];

        $leadCaptures = [
            ['id' => 101, 'voucherCode' => 'PAR-7821', 'campaignId' => 1, 'allyId' => 1, 'leadName' => 'David Morales', 'leadWhatsapp' => '+52 55 4190 8812', 'leadEmail' => 'david.m@gmail.com', 'universe' => 'marte', 'isRedeemed' => true, 'redeemedAt' => '14:22 hrs', 'redeemedBy' => 'Staff Puerta Taquilla #1', 'capiScore' => 9.80, 'date' => '2026-05-12'],
            ['id' => 102, 'voucherCode' => 'PAR-7822', 'campaignId' => 1, 'allyId' => 1, 'leadName' => 'Carlos Santillán', 'leadWhatsapp' => '+52 55 1122 3344', 'leadEmail' => 'carlos.s@outlook.com', 'universe' => 'marte', 'isRedeemed' => false, 'redeemedAt' => null, 'redeemedBy' => null, 'capiScore' => 9.65, 'date' => '2026-05-13'],
            ['id' => 103, 'voucherCode' => 'SOD-4412', 'campaignId' => 2, 'allyId' => 3, 'leadName' => 'Eduardo Vera', 'leadWhatsapp' => '+52 55 9988 7711', 'leadEmail' => 'eduardo.v@gmail.com', 'universe' => 'marte', 'isRedeemed' => true, 'redeemedAt' => '18:45 hrs', 'redeemedBy' => 'Caja Sodome', 'capiScore' => 9.90, 'date' => '2026-05-14'],
            ['id' => 104, 'voucherCode' => 'KIK-9901', 'campaignId' => 3, 'allyId' => 4, 'leadName' => 'Sasha Mendoza', 'leadWhatsapp' => '+52 55 7766 5544', 'leadEmail' => 'sasha.m@ballroom.org', 'universe' => 'tiresias', 'isRedeemed' => false, 'redeemedAt' => null, 'redeemedBy' => null, 'capiScore' => 9.75, 'date' => '2026-05-14'],
            ['id' => 105, 'voucherCode' => 'BEL-3321', 'campaignId' => 1, 'allyId' => 5, 'leadName' => 'Fernanda Ortiz', 'leadWhatsapp' => '+52 55 8877 6655', 'leadEmail' => 'fer.ortiz@yahoo.com', 'universe' => 'venus', 'isRedeemed' => true, 'redeemedAt' => '12:10 hrs', 'redeemedBy' => 'Recepción Bella Donna', 'capiScore' => 9.50, 'date' => '2026-05-15']
        ];

        $couponsDirB = [
            ['id' => 1, 'allyId' => 1, 'couponCode' => 'PARADISO15', 'discountPct' => 15.0, 'commissionRate' => 12.0, 'universe' => 'marte', 'utmSource' => 'paradiso', 'utmCampaign' => 'circuit_mayo', 'clicks' => 680, 'orders' => 58, 'revenue' => 39400, 'commissionEarned' => 4728.00, 'status' => 'active'],
            ['id' => 2, 'allyId' => 2, 'couponCode' => 'ALEXRIVERA', 'discountPct' => 15.0, 'commissionRate' => 15.0, 'universe' => 'marte', 'utmSource' => 'alex_of', 'utmCampaign' => 'jock_campaign', 'clicks' => 420, 'orders' => 32, 'revenue' => 21800, 'commissionEarned' => 3270.00, 'status' => 'active'],
            ['id' => 3, 'allyId' => 3, 'couponCode' => 'SODOME10', 'discountPct' => 10.0, 'commissionRate' => 10.0, 'universe' => 'marte', 'utmSource' => 'sodome', 'utmCampaign' => 'wellness_spa', 'clicks' => 190, 'orders' => 14, 'revenue' => 9800, 'commissionEarned' => 980.00, 'status' => 'active'],
            ['id' => 4, 'allyId' => 4, 'couponCode' => 'KIKIBIND15', 'discountPct' => 15.0, 'commissionRate' => 14.0, 'universe' => 'tiresias', 'utmSource' => 'kiki_ball', 'utmCampaign' => 'binders_affirm', 'clicks' => 230, 'orders' => 20, 'revenue' => 13320, 'commissionEarned' => 1864.80, 'status' => 'active'],
            ['id' => 5, 'allyId' => 5, 'couponCode' => 'BELLA20', 'discountPct' => 20.0, 'commissionRate' => 12.0, 'universe' => 'venus', 'utmSource' => 'bella_donna', 'utmCampaign' => 'lingerie_season', 'clicks' => 210, 'orders' => 18, 'revenue' => 14200, 'commissionEarned' => 1704.00, 'status' => 'active']
        ];

        $clearingSettlements = [
            ['id' => 1, 'code' => 'SETTL-202604-01', 'allyId' => 1, 'allyName' => 'Paradiso Circuit CDMX', 'period' => '01/04/2026 - 30/04/2026', 'leadsCredit' => 2400.00, 'commissionsDebit' => 5200.00, 'netBalance' => -2800.00, 'settlementType' => 'spei_transfer', 'typeLabel' => 'Transferencia SPEI', 'status' => 'executed', 'reference' => 'SPEI-BNX-99882211', 'settledAt' => '2026-05-02 11:30'],
            ['id' => 2, 'code' => 'SETTL-202604-02', 'allyId' => 2, 'allyName' => 'Alex Rivera (Creator & Model)', 'period' => '01/04/2026 - 30/04/2026', 'leadsCredit' => 800.00, 'commissionsDebit' => 3100.00, 'netBalance' => -2300.00, 'settlementType' => 'in_kind_apparel', 'typeLabel' => 'Canje en Especie', 'status' => 'executed', 'reference' => 'SALIDA_CONVENIO_ALIADO #OC-KARDEX-881', 'settledAt' => '2026-05-03 16:15']
        ];

        $totalLeadsCredit = array_sum(array_map(fn($a) => $a['stats']['leadsReceived'] * $a['leadAcquisitionCost'], $alliesList));
        $totalCommissionsDebit = array_sum(array_column(array_column($alliesList, 'stats'), 'commissionEarned'));
        $totalNetBalance = $totalLeadsCredit - $totalCommissionsDebit;

        return view('portal.allies', [
            'alliesList' => $alliesList,
            'campaignsDirA' => $campaignsDirA,
            'leadCaptures' => $leadCaptures,
            'couponsDirB' => $couponsDirB,
            'clearingSettlements' => $clearingSettlements,
            'totalLeadsCredit' => $totalLeadsCredit,
            'totalCommissionsDebit' => $totalCommissionsDebit,
            'totalNetBalance' => $totalNetBalance,
            'selectedAlly' => $alliesList[0],
            'initialMode' => 'admin',
        ]);
    }

    public function partnerPortal(Request $request, $token = null)
    {
        $indexData = $this->index($request)->getData();
        $alliesList = $indexData['alliesList'];
        $selectedAlly = $alliesList[0];

        if ($token) {
            foreach ($alliesList as $ally) {
                if ($ally['portalToken'] === $token) {
                    $selectedAlly = $ally;
                    break;
                }
            }
        }

        return view('portal.allies', array_merge($indexData, [
            'selectedAlly' => $selectedAlly,
            'initialMode' => 'partner',
            'partnerToken' => $token,
        ]));
    }
}

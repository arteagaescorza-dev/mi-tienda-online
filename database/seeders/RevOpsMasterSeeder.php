<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageType;
use App\Models\ApparelFabric;
use App\Models\ApparelColor;
use App\Models\WarehouseSlot;
use App\Models\PaymentGatewayConfig;
use App\Models\Coupon;
use App\Models\Ally;
use App\Models\AllyCampaignDirA;
use App\Models\AllyCouponDirB;
use App\Models\AllyClearingSettlement;

class RevOpsMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Package Types
        $packages = [
            ['code' => 'BOX-P01', 'name' => 'Caja Chica D2C', 'type' => 'box', 'length_cm' => 20, 'width_cm' => 15, 'height_cm' => 8, 'max_weight_kg' => 2.0, 'unit_cost' => 12.50, 'stock_available' => 450],
            ['code' => 'BOX-M02', 'name' => 'Caja Mediana E-commerce', 'type' => 'box', 'length_cm' => 28, 'width_cm' => 20, 'height_cm' => 10, 'max_weight_kg' => 4.0, 'unit_cost' => 16.00, 'stock_available' => 320],
            ['code' => 'BOX-G03', 'name' => 'Caja Grande Multiverso', 'type' => 'box', 'length_cm' => 35, 'width_cm' => 25, 'height_cm' => 15, 'max_weight_kg' => 8.0, 'unit_cost' => 24.50, 'stock_available' => 180],
            ['code' => 'POLY-S01', 'name' => 'Bolsa Polymailer Segura', 'type' => 'polymailer', 'length_cm' => 25, 'width_cm' => 35, 'height_cm' => 2, 'max_weight_kg' => 1.5, 'unit_cost' => 4.50, 'stock_available' => 800],
        ];
        foreach ($packages as $pkg) {
            PackageType::updateOrCreate(['code' => $pkg['code']], $pkg);
        }

        // 2. Apparel Fabrics
        $fabrics = [
            ['name' => 'Algodón Pima Peruano', 'slug' => 'algodon-pima', 'description' => 'Fibras extra largas de alta suavidad y resistencia.'],
            ['name' => 'Micro-Modal Ecológico', 'slug' => 'micro-modal', 'description' => 'Tejido ultra sedoso transpirable a base de haya.'],
            ['name' => 'Microfibra Deportiva Dry-Fit', 'slug' => 'microfibra-dryfit', 'description' => 'Secado rápido para arneses y suspensorios.'],
            ['name' => 'Malla Elástica Mesh Transpirable', 'slug' => 'malla-mesh', 'description' => 'Tejido de red ligera para línea ergonómica.'],
            ['name' => 'Spandex / Elastano 4-Way', 'slug' => 'spandex-elastano', 'description' => 'Compresión afirmativa y ajuste dinámico.'],
        ];
        foreach ($fabrics as $fab) {
            ApparelFabric::updateOrCreate(['slug' => $fab['slug']], $fab);
        }

        // 3. Apparel Colors
        $colors = [
            ['name' => 'Negro Ónix', 'color_type' => 'solid', 'hex_code' => '#0f172a', 'gradient_css' => null],
            ['name' => 'Blanco Puro', 'color_type' => 'solid', 'hex_code' => '#ffffff', 'gradient_css' => null],
            ['name' => 'Azul Medianoche', 'color_type' => 'solid', 'hex_code' => '#1e3a8a', 'gradient_css' => null],
            ['name' => 'Rojo Marte', 'color_type' => 'solid', 'hex_code' => '#dc2626', 'gradient_css' => null],
            ['name' => 'Tornasol Aurora Neón', 'color_type' => 'gradient', 'hex_code' => '#38ef7d', 'gradient_css' => 'linear-gradient(135deg, #38ef7d 0%, #11998e 100%)'],
            ['name' => 'Tornasol Cyberpunk Violet', 'color_type' => 'gradient', 'hex_code' => '#8b5cf6', 'gradient_css' => 'linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%)'],
        ];
        foreach ($colors as $col) {
            ApparelColor::updateOrCreate(['name' => $col['name']], $col);
        }

        // 4. Warehouse Slots
        $slots = [
            ['slot_code' => 'SLOT-A01-N1', 'zone' => 'ZONA_A', 'aisle' => 'P01', 'rack' => 'R01', 'shelf' => 'N1', 'is_occupied' => true],
            ['slot_code' => 'SLOT-A01-N2', 'zone' => 'ZONA_A', 'aisle' => 'P01', 'rack' => 'R01', 'shelf' => 'N2', 'is_occupied' => true],
            ['slot_code' => 'SLOT-A02-N1', 'zone' => 'ZONA_A', 'aisle' => 'P01', 'rack' => 'R02', 'shelf' => 'N1', 'is_occupied' => false],
            ['slot_code' => 'SLOT-B01-N1', 'zone' => 'ZONA_B', 'aisle' => 'P02', 'rack' => 'R01', 'shelf' => 'N1', 'is_occupied' => true],
            ['slot_code' => 'SLOT-C01-N1', 'zone' => 'ZONA_C', 'aisle' => 'P03', 'rack' => 'R01', 'shelf' => 'N1', 'is_occupied' => false],
        ];
        foreach ($slots as $s) {
            WarehouseSlot::updateOrCreate(['slot_code' => $s['slot_code']], $s);
        }

        // 5. Payment Gateway Configs
        $gateways = [
            ['gateway_key' => 'stripe', 'name' => 'Stripe Payments (Tarjetas & Apple Pay)', 'is_active' => true, 'environment' => 'sandbox', 'fee_percent' => 3.60, 'fee_fixed' => 3.00],
            ['gateway_key' => 'mercadopago', 'name' => 'Mercado Pago Checkout Pro', 'is_active' => true, 'environment' => 'sandbox', 'fee_percent' => 3.49, 'fee_fixed' => 4.00],
            ['gateway_key' => 'oxxo_pay', 'name' => 'OXXO Pay (Efectivo en Tiendas)', 'is_active' => true, 'environment' => 'sandbox', 'fee_percent' => 3.00, 'fee_fixed' => 12.00],
            ['gateway_key' => 'spei_transfer', 'name' => 'Transferencia Bancaria SPEI', 'is_active' => true, 'environment' => 'sandbox', 'fee_percent' => 0.00, 'fee_fixed' => 0.00],
        ];
        foreach ($gateways as $gw) {
            PaymentGatewayConfig::updateOrCreate(['gateway_key' => $gw['gateway_key']], $gw);
        }

        // 6. Coupons
        $coupons = [
            ['code' => 'BIENVENIDA10', 'discount_type' => 'percent', 'discount_value' => 10.00, 'min_order_amount' => 499.00, 'target_universe' => 'all', 'max_uses' => 1000, 'used_count' => 142, 'revenue_generated' => 92300, 'is_active' => true],
            ['code' => 'MARTEVIP15', 'discount_type' => 'percent', 'discount_value' => 15.00, 'min_order_amount' => 799.00, 'target_universe' => 'marte', 'max_uses' => 500, 'used_count' => 88, 'revenue_generated' => 78400, 'is_active' => true],
            ['code' => 'ENVIOGRATIS', 'discount_type' => 'free_shipping', 'discount_value' => 150.00, 'min_order_amount' => 999.00, 'target_universe' => 'all', 'max_uses' => 2000, 'used_count' => 310, 'revenue_generated' => 345000, 'is_active' => true],
        ];
        foreach ($coupons as $cp) {
            Coupon::updateOrCreate(['code' => $cp['code']], $cp);
        }

        // 7. Allies & Clearing House
        $allies = [
            [
                'code' => 'ALLY-PARADISO',
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
                'commission_rate' => 12.0,
                'lead_acquisition_cost' => 20.0,
                'bank_clabe' => '012180015678901234',
                'bank_name' => 'BBVA México',
                'clearing_balance' => -4280.00,
                'status' => 'active',
                'notes' => 'Festival Circuit mensual en Frontón México.'
            ],
            [
                'code' => 'ALLY-ALEX-OF',
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
                'commission_rate' => 15.0,
                'lead_acquisition_cost' => 25.0,
                'bank_clabe' => '072180002345678901',
                'bank_name' => 'Banorte',
                'clearing_balance' => -2150.00,
                'status' => 'active',
                'notes' => 'Top Creator OnlyFans y embajador de jocks y arneses Marte.'
            ],
            [
                'code' => 'ALLY-SODOME',
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
                'commission_rate' => 10.0,
                'lead_acquisition_cost' => 18.0,
                'bank_clabe' => '002180098765432109',
                'bank_name' => 'Citibanamex',
                'clearing_balance' => 1450.00,
                'status' => 'active',
                'notes' => 'Sauna premium en Zona Rosa.'
            ],
            [
                'code' => 'ALLY-KIKI-BALL',
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
                'commission_rate' => 14.0,
                'lead_acquisition_cost' => 22.0,
                'bank_clabe' => '014180054321098765',
                'bank_name' => 'Santander',
                'clearing_balance' => -1890.00,
                'status' => 'active',
                'notes' => 'Colectivo ballroom y comunidad trans.'
            ],
            [
                'code' => 'ALLY-BELLA',
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
                'commission_rate' => 12.0,
                'lead_acquisition_cost' => 20.0,
                'bank_clabe' => '127180011223344556',
                'bank_name' => 'Banco Azteca',
                'clearing_balance' => -3100.00,
                'status' => 'active',
                'notes' => 'Spa facial y boutique de lencería de autor.'
            ]
        ];

        foreach ($allies as $allyData) {
            $ally = Ally::updateOrCreate(['code' => $allyData['code']], $allyData);

            // Campaña Dir A
            AllyCampaignDirA::updateOrCreate(
                ['slug' => 'promo-' . strtolower($ally->code)],
                [
                    'ally_id' => $ally->id,
                    'title' => 'Promoción Especial ' . $ally->name,
                    'universe' => $ally->universe,
                    'benefit' => 'Descuento preferencial y pase VIP exclusivo',
                    'display_format' => 'popup_modal',
                    'leads_count' => 35,
                    'status' => 'active',
                ]
            );

            // Cupón Dir B
            AllyCouponDirB::updateOrCreate(
                ['coupon_code' => strtoupper(str_replace('ALLY-', '', $ally->code)) . '15'],
                [
                    'ally_id' => $ally->id,
                    'discount_pct' => 15.00,
                    'commission_rate' => $ally->commission_rate,
                    'universe' => $ally->universe,
                    'utm_source' => strtolower($ally->code),
                    'utm_campaign' => 'convenio_2026',
                    'clicks_count' => 180,
                    'orders_count' => 24,
                    'revenue_attributed' => 18400.00,
                    'commission_earned' => 2208.00,
                    'status' => 'active',
                ]
            );
        }
    }
}

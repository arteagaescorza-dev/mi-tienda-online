<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingManifest;
use App\Models\PackageType;
use App\Models\RmaRequest;
use App\Models\RmaInspectionLog;
use App\Models\FraudRiskAssessment;

class LogisticsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Package Types (Insumos de Empaque y Volumetría IATA 5000)
        $boxes = [
            [
                'code' => 'BOX-P01',
                'name' => 'Caja Micro Marte',
                'type' => 'box',
                'length_cm' => 18,
                'width_cm' => 12,
                'height_cm' => 5,
                'max_weight_kg' => 2.0,
                'unit_cost' => 8.50,
                'stock_available' => 340,
                'is_active' => true,
            ],
            [
                'code' => 'BOX-M02',
                'name' => 'Caja Estándar Venus',
                'type' => 'box',
                'length_cm' => 22,
                'width_cm' => 15,
                'height_cm' => 6,
                'max_weight_kg' => 4.0,
                'unit_cost' => 12.80,
                'stock_available' => 520,
                'is_active' => true,
            ],
            [
                'code' => 'BOX-G03',
                'name' => 'Caja Doble Combo Multiverso',
                'type' => 'box',
                'length_cm' => 30,
                'width_cm' => 20,
                'height_cm' => 10,
                'max_weight_kg' => 8.0,
                'unit_cost' => 18.20,
                'stock_available' => 210,
                'is_active' => true,
            ],
            [
                'code' => 'POLY-S01',
                'name' => 'Sobre Kraft Acolchado / Polymailer',
                'type' => 'polymailer',
                'length_cm' => 25,
                'width_cm' => 18,
                'height_cm' => 3,
                'max_weight_kg' => 1.5,
                'unit_cost' => 6.20,
                'stock_available' => 680,
                'is_active' => true,
            ],
        ];

        foreach ($boxes as $box) {
            PackageType::updateOrCreate(['code' => $box['code']], $box);
        }

        // 2. Shipping Manifests (Manifiestos Diarios de Expedición)
        $manifestDhlSigned = ShippingManifest::updateOrCreate(
            ['manifest_number' => 'MAN-DHL-2026-0925'],
            [
                'courier' => 'dhl_express',
                'total_packages' => 2,
                'driver_name' => 'Roberto Gómez Hernández',
                'driver_badge_or_truck' => 'GAF-9921 / Placas LC-492-X',
                'status' => 'signed',
                'signed_at' => now()->subDays(1)->setTime(14, 15),
            ]
        );

        $manifestEstSigned = ShippingManifest::updateOrCreate(
            ['manifest_number' => 'MAN-EST-2026-0925'],
            [
                'courier' => 'estafeta',
                'total_packages' => 1,
                'driver_name' => 'Guillermo Ruiz Peña',
                'driver_badge_or_truck' => 'GAF-4102 / Camión E-102',
                'status' => 'signed',
                'signed_at' => now()->subDays(1)->setTime(13, 50),
            ]
        );

        $manifestDhlOpen = ShippingManifest::updateOrCreate(
            ['manifest_number' => 'MAN-DHL-2026-0926'],
            [
                'courier' => 'dhl_express',
                'total_packages' => 0,
                'driver_name' => null,
                'driver_badge_or_truck' => null,
                'status' => 'open',
                'signed_at' => null,
            ]
        );

        // 3. Shipments (Bultos en Rampa y Envíos en Tránsito Nacional)
        $orders = Order::orderBy('id')->get()->keyBy('id');

        $shipmentsData = [
            // Bultos en Rampa (Mesa de salida - pendientes de cierre de manifiesto)
            [
                'order_id' => $orders->has(1) ? 1 : null,
                'manifest_id' => null,
                'tracking_number' => 'DHL-8492048192',
                'courier' => 'dhl_express',
                'service_type' => 'express',
                'weight_actual_kg' => 0.65,
                'weight_volumetric_kg' => 0.40,
                'shipping_cost_declared' => 145.00,
                'shipping_cost_invoiced' => 145.00,
                'status' => 'packed',
                'dispatched_at' => null,
                'delivered_at' => null,
            ],
            [
                'order_id' => $orders->has(6) ? 6 : null,
                'manifest_id' => null,
                'tracking_number' => 'EST-9938472910',
                'courier' => 'estafeta',
                'service_type' => 'standard',
                'weight_actual_kg' => 0.45,
                'weight_volumetric_kg' => 0.22,
                'shipping_cost_declared' => 120.00,
                'shipping_cost_invoiced' => 120.00,
                'status' => 'packed',
                'dispatched_at' => null,
                'delivered_at' => null,
            ],
            [
                'order_id' => $orders->has(7) ? 7 : null,
                'manifest_id' => null,
                'tracking_number' => 'DHL-1928374650',
                'courier' => 'dhl_express',
                'service_type' => 'express',
                'weight_actual_kg' => 1.20,
                'weight_volumetric_kg' => 1.20,
                'shipping_cost_declared' => 185.00,
                'shipping_cost_invoiced' => 185.00,
                'status' => 'packed',
                'dispatched_at' => null,
                'delivered_at' => null,
            ],
            [
                'order_id' => $orders->has(8) ? 8 : null,
                'manifest_id' => null,
                'tracking_number' => '99M-4829104829',
                'courier' => '99minutos',
                'service_type' => 'same_day',
                'weight_actual_kg' => 0.35,
                'weight_volumetric_kg' => 0.27,
                'shipping_cost_declared' => 95.00,
                'shipping_cost_invoiced' => 95.00,
                'status' => 'packed',
                'dispatched_at' => null,
                'delivered_at' => null,
            ],

            // Torre de Control (En ruta y tránsito)
            [
                'order_id' => $orders->has(5) ? 5 : null,
                'manifest_id' => $manifestDhlSigned->id,
                'tracking_number' => 'DHL-7749201948',
                'courier' => 'dhl_express',
                'service_type' => 'express',
                'weight_actual_kg' => 0.50,
                'weight_volumetric_kg' => 0.40,
                'shipping_cost_declared' => 150.00,
                'shipping_cost_invoiced' => 150.00,
                'status' => 'out_for_delivery',
                'dispatched_at' => now()->subHours(8),
                'delivered_at' => null,
            ],
            [
                'order_id' => $orders->has(9) ? 9 : null,
                'manifest_id' => $manifestEstSigned->id,
                'tracking_number' => 'EST-8849102941',
                'courier' => 'estafeta',
                'service_type' => 'standard',
                'weight_actual_kg' => 0.80,
                'weight_volumetric_kg' => 0.55,
                'shipping_cost_declared' => 130.00,
                'shipping_cost_invoiced' => 130.00,
                'status' => 'in_transit',
                'dispatched_at' => now()->subHours(18),
                'delivered_at' => null,
            ],
            [
                'order_id' => $orders->has(10) ? 10 : null,
                'manifest_id' => null,
                'tracking_number' => 'FDX-5592810482',
                'courier' => 'fedex',
                'service_type' => 'economy',
                'weight_actual_kg' => 1.10,
                'weight_volumetric_kg' => 0.90,
                'shipping_cost_declared' => 165.00,
                'shipping_cost_invoiced' => 165.00,
                'status' => 'in_transit',
                'dispatched_at' => now()->subHours(22),
                'delivered_at' => null,
            ],
            [
                'order_id' => $orders->has(11) ? 11 : null,
                'manifest_id' => $manifestDhlSigned->id,
                'tracking_number' => 'DHL-3391029481',
                'courier' => 'dhl_express',
                'service_type' => 'express',
                'weight_actual_kg' => 0.40,
                'weight_volumetric_kg' => 0.30,
                'shipping_cost_declared' => 140.00,
                'shipping_cost_invoiced' => 140.00,
                'status' => 'in_transit',
                'dispatched_at' => now()->subHours(12),
                'delivered_at' => null,
            ],
            [
                'order_id' => $orders->has(2) ? 2 : null,
                'manifest_id' => null,
                'tracking_number' => '99M-7718291039',
                'courier' => '99minutos',
                'service_type' => 'same_day',
                'weight_actual_kg' => 0.40,
                'weight_volumetric_kg' => 0.30,
                'shipping_cost_declared' => 95.00,
                'shipping_cost_invoiced' => 95.00,
                'status' => 'delivered',
                'dispatched_at' => now()->subDays(2),
                'delivered_at' => now()->subDays(1),
            ],
            [
                'order_id' => $orders->has(3) ? 3 : null,
                'manifest_id' => null,
                'tracking_number' => 'DHL-2291049281',
                'courier' => 'dhl_express',
                'service_type' => 'express',
                'weight_actual_kg' => 0.70,
                'weight_volumetric_kg' => 0.50,
                'shipping_cost_declared' => 155.00,
                'shipping_cost_invoiced' => 155.00,
                'status' => 'delivered',
                'dispatched_at' => now()->subDays(3),
                'delivered_at' => now()->subDays(2),
            ],
            [
                'order_id' => $orders->has(4) ? 4 : null,
                'manifest_id' => null,
                'tracking_number' => 'EST-1192837465',
                'courier' => 'estafeta',
                'service_type' => 'standard',
                'weight_actual_kg' => 0.50,
                'weight_volumetric_kg' => 0.35,
                'shipping_cost_declared' => 125.00,
                'shipping_cost_invoiced' => 125.00,
                'status' => 'delivered',
                'dispatched_at' => now()->subDays(3),
                'delivered_at' => now()->subDays(2),
            ],
        ];

        foreach ($shipmentsData as $data) {
            Shipment::updateOrCreate(['tracking_number' => $data['tracking_number']], $data);
        }

        // 4. Fraud Risk Assessments (Escudo Antifraude Radar)
        $fraudData = [
            ['order_id' => 1, 'risk_score' => 12, 'risk_level' => 'normal', 'recommendation' => 'allow', 'rule_matches_json' => []],
            ['order_id' => 6, 'risk_score' => 8, 'risk_level' => 'normal', 'recommendation' => 'allow', 'rule_matches_json' => []],
            ['order_id' => 7, 'risk_score' => 18, 'risk_level' => 'normal', 'recommendation' => 'allow', 'rule_matches_json' => []],
            ['order_id' => 8, 'risk_score' => 82, 'risk_level' => 'highest', 'recommendation' => 'manual_review', 'rule_matches_json' => ['IP_PROXY_VPN_DETECTED', 'CARD_VELOCITY_SUSPICIOUS']],
        ];

        foreach ($fraudData as $fd) {
            if ($orders->has($fd['order_id'])) {
                FraudRiskAssessment::updateOrCreate(
                    ['order_id' => $fd['order_id']],
                    $fd
                );
            }
        }

        // 5. RMA Requests & Inspection Logs (Protocolo Sanitario de Ropa Interior)
        $rma1 = RmaRequest::updateOrCreate(
            ['rma_number' => 'RMA-2026-001'],
            [
                'order_id' => $orders->has(2) ? 2 : null,
                'customer_name' => 'Cliente Terra 2',
                'customer_phone' => '+52 55 1234 5678',
                'product_sku' => 'SKU00259-M',
                'product_name' => 'Tanga Licra Roja (Talla M)',
                'reason' => 'sizing_wrong',
                'sanitary_status' => 'approved_replacement',
                'resolution_type' => 'replacement_shipped',
                'refund_or_credit_amount' => 180.00,
                'status' => 'resolved',
            ]
        );

        RmaInspectionLog::firstOrCreate(
            [
                'rma_id' => $rma1->id,
                'verdict' => 'approved',
            ],
            [
                'inspector_name' => 'Validador Calidad Almacén CDMX',
                'has_hygiene_seal_intact' => true,
                'has_factory_tag' => true,
                'technical_notes' => 'Precinto higiénico termosellado de ropa interior 100% intacto. Prenda sin evidencias de uso ni desprendimiento de protector. Se aprueba reposición y reingreso contable a Kardex como ENTRADA_DEVOLUCION.',
                'created_at' => now()->subDays(1),
            ]
        );

        $rma2 = RmaRequest::updateOrCreate(
            ['rma_number' => 'RMA-2026-002'],
            [
                'order_id' => $orders->has(3) ? 3 : null,
                'customer_name' => 'Cliente Venus 3',
                'customer_phone' => '+52 33 9876 5432',
                'product_sku' => 'SKU00259-CH',
                'product_name' => 'Tanga Licra Roja (Talla CH)',
                'reason' => 'sizing_wrong',
                'sanitary_status' => 'rejected_used_hygiene',
                'resolution_type' => null,
                'refund_or_credit_amount' => 0.00,
                'status' => 'rejected',
            ]
        );

        RmaInspectionLog::firstOrCreate(
            [
                'rma_id' => $rma2->id,
                'verdict' => 'rejected_sanitary_violation',
            ],
            [
                'inspector_name' => 'Validador Calidad Almacén CDMX',
                'has_hygiene_seal_intact' => false,
                'has_factory_tag' => false,
                'technical_notes' => 'Precinto de protección íntima desprendido y violado. Por estricta normativa de salubridad Cofepris y política de higiene de lencería íntima no procede devolución ni reingreso al inventario. Se canaliza a baja contable MERMA_DESTRUCCION.',
                'created_at' => now()->subHours(14),
            ]
        );

        $rma3 = RmaRequest::updateOrCreate(
            ['rma_number' => 'RMA-2026-003'],
            [
                'order_id' => $orders->has(4) ? 4 : null,
                'customer_name' => 'Cliente Tiresias 4',
                'customer_phone' => '+52 22 2345 6789',
                'product_sku' => 'SKU00259-G',
                'product_name' => 'Tanga Licra Roja (Talla G)',
                'reason' => 'factory_defect',
                'sanitary_status' => 'pending_inspection',
                'resolution_type' => null,
                'refund_or_credit_amount' => 180.00,
                'status' => 'open',
            ]
        );
    }
}

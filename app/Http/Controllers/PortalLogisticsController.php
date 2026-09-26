<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PortalLogisticsController extends Controller
{
    public function index(Request $request)
    {
        $ordersCount = Order::count();

        $packages = [
            [
                'id' => 'PKG-1048',
                'order_number' => 'ORD-1048',
                'customer' => 'Carlos Mendoza',
                'destination' => 'Monterrey, NL (CP 64000)',
                'weight_kg' => 0.65,
                'dimensions' => '22x15x6 cm',
                'volumetric_kg' => 0.40,
                'box_type' => 'Caja Estándar Venus (BX-02)',
                'courier' => 'DHL Express',
                'tracking' => 'DHL-8492048192',
                'status' => 'packed',
                'items_count' => 3,
                'fraud_score' => 12,
                'created_at' => now()->subMinutes(35)->format('H:i'),
            ],
            [
                'id' => 'PKG-1049',
                'order_number' => 'ORD-1049',
                'customer' => 'Valeria Gómez',
                'destination' => 'Guadalajara, JAL (CP 44100)',
                'weight_kg' => 0.45,
                'dimensions' => '18x12x5 cm',
                'volumetric_kg' => 0.22,
                'box_type' => 'Caja Micro Marte (BX-01)',
                'courier' => 'Estafeta',
                'tracking' => 'EST-9938472910',
                'status' => 'packed',
                'items_count' => 2,
                'fraud_score' => 8,
                'created_at' => now()->subMinutes(28)->format('H:i'),
            ],
            [
                'id' => 'PKG-1050',
                'order_number' => 'ORD-1050',
                'customer' => 'Rodrigo Silva',
                'destination' => 'Mérida, YUC (CP 97000)',
                'weight_kg' => 1.20,
                'dimensions' => '30x20x10 cm',
                'volumetric_kg' => 1.20,
                'box_type' => 'Caja Doble Combo (BX-03)',
                'courier' => 'DHL Express',
                'tracking' => 'DHL-1928374650',
                'status' => 'packed',
                'items_count' => 5,
                'fraud_score' => 18,
                'created_at' => now()->subMinutes(15)->format('H:i'),
            ],
            [
                'id' => 'PKG-1051',
                'order_number' => 'ORD-1051',
                'customer' => 'Alejandro Morales',
                'destination' => 'Puebla, PUE (CP 72000)',
                'weight_kg' => 0.35,
                'dimensions' => '25x18x3 cm',
                'volumetric_kg' => 0.27,
                'box_type' => 'Sobre Kraft Acolchado (SB-01)',
                'courier' => '99 Minutos',
                'tracking' => '99M-4829104829',
                'status' => 'packed',
                'items_count' => 1,
                'fraud_score' => 5,
                'created_at' => now()->subMinutes(8)->format('H:i'),
            ],
        ];

        $packageTypes = [
            [
                'id' => 'BX-01',
                'name' => 'Caja Micro Marte',
                'dimensions' => '18 x 12 x 5 cm',
                'length_cm' => 18,
                'width_cm' => 12,
                'height_cm' => 5,
                'tare_weight_g' => 65,
                'volumetric_weight_kg' => 0.22,
                'volume_liters' => 1.08,
                'cost_mxn' => 8.50,
                'capacity' => '1-2 prendas íntimas',
                'stock' => 340,
                'is_active' => true,
            ],
            [
                'id' => 'BX-02',
                'name' => 'Caja Estándar Venus',
                'dimensions' => '22 x 15 x 6 cm',
                'length_cm' => 22,
                'width_cm' => 15,
                'height_cm' => 6,
                'tare_weight_g' => 95,
                'volumetric_weight_kg' => 0.40,
                'volume_liters' => 1.98,
                'cost_mxn' => 12.80,
                'capacity' => '3-4 prendas o kit cosmética',
                'stock' => 520,
                'is_active' => true,
            ],
            [
                'id' => 'BX-03',
                'name' => 'Caja Doble Combo',
                'dimensions' => '30 x 20 x 10 cm',
                'length_cm' => 30,
                'width_cm' => 20,
                'height_cm' => 10,
                'tare_weight_g' => 160,
                'volumetric_weight_kg' => 1.20,
                'volume_liters' => 6.00,
                'cost_mxn' => 18.20,
                'capacity' => 'Suscripciones & Combos 6+ uds',
                'stock' => 210,
                'is_active' => true,
            ],
            [
                'id' => 'SB-01',
                'name' => 'Sobre Kraft Acolchado',
                'dimensions' => '25 x 18 x 3 cm',
                'length_cm' => 25,
                'width_cm' => 18,
                'height_cm' => 3,
                'tare_weight_g' => 35,
                'volumetric_weight_kg' => 0.27,
                'volume_liters' => 1.35,
                'cost_mxn' => 6.20,
                'capacity' => '1 prenda ligera / accesorios',
                'stock' => 680,
                'is_active' => true,
            ],
        ];

        $trackingList = [
            [
                'tracking_number' => 'DHL-7749201948',
                'order_number' => 'ORD-1042',
                'customer' => 'Mariana Torres',
                'city' => 'Querétaro, QRO',
                'courier' => 'DHL Express',
                'status' => 'out_for_delivery',
                'status_label' => 'En Ruta de Entrega',
                'last_checkpoint' => 'En camioneta de reparto (08:30 hrs)',
                'eta' => 'Hoy antes de las 18:00 hrs',
                'sla_status' => 'ontime',
            ],
            [
                'tracking_number' => 'EST-8849102941',
                'order_number' => 'ORD-1040',
                'customer' => 'Héctor Salazar',
                'city' => 'Tijuana, BC',
                'courier' => 'Estafeta',
                'status' => 'in_transit',
                'status_label' => 'En Tránsito Aéreo',
                'last_checkpoint' => 'Centro Operativo HUB CDMX (03:15 hrs)',
                'eta' => 'Mañana 27 Sep',
                'sla_status' => 'ontime',
            ],
            [
                'tracking_number' => 'FDX-5592810482',
                'order_number' => 'ORD-1036',
                'customer' => 'Fernando Castro',
                'city' => 'Cancún, QROO',
                'courier' => 'FedEx',
                'status' => 'delayed',
                'status_label' => 'Retraso Operativo',
                'last_checkpoint' => 'Reexpedición por zona turística (+24h)',
                'eta' => 'Lunes 29 Sep',
                'sla_status' => 'warning',
            ],
        ];

        $rmaRequests = [
            [
                'rma_number' => 'RMA-2026-004',
                'order_number' => 'ORD-1022',
                'customer' => 'Gabriela Morales',
                'sku' => 'MAR-BOX-001-M',
                'product_name' => 'Boxer Microfibra Marte (Talla M)',
                'reason' => 'Cambio por Talla G',
                'hygiene_seal_status' => 'intact',
                'status' => 'received_inspection',
                'status_label' => 'En Mesa de Inspección',
                'created_at' => now()->subHours(4)->format('d/m H:i'),
            ],
        ];

        return view('portal.logistics', [
            'summary' => [
                ['label' => 'Bultos en rampa', 'value' => count($packages) . ' listos'],
                ['label' => 'Guías emitidas', 'value' => '100% OK'],
                ['label' => 'Corte recolección', 'value' => '14:00 hrs'],
                ['label' => 'Carga en rampa', 'value' => array_sum(array_column($packages, 'weight_kg')) . ' kg totales'],
            ],
            'packages' => $packages,
            'packageTypes' => $packageTypes,
            'trackingList' => $trackingList,
            'rmaRequests' => $rmaRequests,
            'ordersCount' => $ordersCount,
        ]);
    }
}

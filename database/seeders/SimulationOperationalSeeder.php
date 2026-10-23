<?php

namespace Database\Seeders;

use App\Models\Ally;
use App\Models\AllyCouponDirB;
use App\Models\CustomerSubscription;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderFinancialMargin;
use App\Models\OrderItem;
use App\Models\OperationalTask;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShiftLog;
use App\Models\Shipment;
use App\Models\SupportCase;
use App\Models\User;
use App\Models\VendorBill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimulationOperationalSeeder extends Seeder
{
    /**
     * Sembrador de Escenarios Operativos y Transacciones Simuladas (Fase 1 / Hubs 4, 2, 1, 7, 5).
     * Pobla la base de datos con pedidos, inventarios, márgenes y logística reales para calibración.
     */
    public function run(): void
    {
        $now = now();
        $today = today();

        // ---------------------------------------------------------------------
        // 1. PRODUCTOS, VARIANTES E INVENTARIO BASE (HUB 4)
        // ---------------------------------------------------------------------
        $products = Product::where('visible', true)->where('is_archived', false)->get();
        if ($products->isEmpty()) {
            $baseProducts = [
                ['sku' => 'BX-MOD-001', 'title' => 'Boxer Trunks Modal Seamless', 'price' => 420.00, 'cost' => 168.00, 'type' => 'physical'],
                ['sku' => 'BX-PIM-002', 'title' => 'Boxer Algodón Pima Orgánico', 'price' => 390.00, 'cost' => 156.00, 'type' => 'physical'],
                ['sku' => 'JK-ATH-003', 'title' => 'Jockstrap Athletic Ribbed Band', 'price' => 380.00, 'cost' => 133.00, 'type' => 'physical'],
                ['sku' => 'TR-DRP-004', 'title' => 'Torzo Mesh Breathable Trunk', 'price' => 490.00, 'cost' => 245.00, 'type' => 'dropship'],
                ['sku' => 'TG-VLV-005', 'title' => 'Tanga Microfibra Velvet Touch', 'price' => 290.00, 'cost' => 87.00, 'type' => 'physical'],
                ['sku' => 'BK-PRF-008', 'title' => 'Bikini Sport Performance Brief', 'price' => 320.00, 'cost' => 112.00, 'type' => 'physical'],
            ];

            foreach ($baseProducts as $bp) {
                $p = Product::create([
                    'sku' => $bp['sku'],
                    'slug' => Str::slug($bp['title'] . '-' . $bp['sku']),
                    'title' => $bp['title'],
                    'price' => $bp['price'],
                    'cost' => $bp['cost'],
                    'type' => $bp['type'],
                    'visible' => true,
                    'is_archived' => false,
                ]);

                foreach (['CH', 'M', 'G', 'XL'] as $size) {
                    ProductVariant::create([
                        'product_id' => $p->id,
                        'sku' => "{$bp['sku']}-{$size}",
                        'price' => $bp['price'],
                        'cost' => $bp['cost'],
                        'attributes' => ['size' => $size, 'color' => 'Negro'],
                    ]);
                }
            }
            $products = Product::where('visible', true)->where('is_archived', false)->get();
        }

        $allVariants = ProductVariant::all();
        $ropVariantIndex = 0;

        foreach ($allVariants as $variant) {
            $isRopTarget = ($ropVariantIndex < 4);
            $quantity = $isRopTarget ? ($ropVariantIndex + 2) : rand(25, 60);
            $minThreshold = 12;
            $location = 'Anaquel ' . chr(65 + ($variant->id % 4)) . '-0' . (($variant->id % 6) + 1);

            Inventory::updateOrCreate(
                ['variant_id' => $variant->id],
                [
                    'quantity' => $quantity,
                    'reserved' => 0,
                    'min_threshold' => $minThreshold,
                    'location' => $location,
                ]
            );

            $ropVariantIndex++;
        }

        // ---------------------------------------------------------------------
        // 2. CLIENTES CON DIRECCIONES REALES EN MÉXICO (HUB 3 / STOREFRONT)
        // ---------------------------------------------------------------------
        $customersData = [
            [
                'name' => 'Mariana Garza',
                'email' => 'mariana.garza@cliente.test',
                'phone' => '+52 81 8392 1092',
                'address' => [
                    'street' => 'Av. Constitución',
                    'number' => '1420',
                    'colonia' => 'Obispado',
                    'city' => 'Monterrey',
                    'state' => 'Nuevo León',
                    'postal_code' => '64060',
                    'country' => 'MX',
                ],
            ],
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@cliente.test',
                'phone' => '+52 33 1928 3847',
                'address' => [
                    'street' => 'Av. Vallarta',
                    'number' => '2045',
                    'colonia' => 'Americana',
                    'city' => 'Guadalajara',
                    'state' => 'Jalisco',
                    'postal_code' => '44160',
                    'country' => 'MX',
                ],
            ],
            [
                'name' => 'Andrea Ruiz',
                'email' => 'andrea.ruiz@cliente.test',
                'phone' => '+52 55 9182 3341',
                'address' => [
                    'street' => 'Ámsterdam',
                    'number' => '88',
                    'colonia' => 'Condesa',
                    'city' => 'Ciudad de México',
                    'state' => 'CDMX',
                    'postal_code' => '06140',
                    'country' => 'MX',
                ],
            ],
            [
                'name' => 'Valeria Gómez',
                'email' => 'valeria.gomez@cliente.test',
                'phone' => '+52 22 2819 0293',
                'address' => [
                    'street' => 'Calle 3 Poniente',
                    'number' => '502',
                    'colonia' => 'Centro',
                    'city' => 'Puebla',
                    'state' => 'Puebla',
                    'postal_code' => '72000',
                    'country' => 'MX',
                ],
            ],
            [
                'name' => 'Roberto González',
                'email' => 'roberto.gonzalez@cliente.test',
                'phone' => '+52 44 2910 4829',
                'address' => [
                    'street' => 'Paseo de la República',
                    'number' => '10200',
                    'colonia' => 'Juriquilla',
                    'city' => 'Querétaro',
                    'state' => 'Querétaro',
                    'postal_code' => '76230',
                    'country' => 'MX',
                ],
            ],
            [
                'name' => 'Fernando Torres',
                'email' => 'fernando.torres@cliente.test',
                'phone' => '+52 99 9182 3746',
                'address' => [
                    'street' => 'Paseo de Montejo',
                    'number' => '498',
                    'colonia' => 'Centro',
                    'city' => 'Mérida',
                    'state' => 'Yucatán',
                    'postal_code' => '97000',
                    'country' => 'MX',
                ],
            ],
        ];

        $customers = [];
        foreach ($customersData as $cd) {
            $user = User::firstOrCreate(
                ['email' => $cd['email']],
                [
                    'name' => $cd['name'],
                    'password' => bcrypt('password'),
                    'role' => 'customer',
                ]
            );
            $customers[] = ['user' => $user, 'data' => $cd];
        }

        // ---------------------------------------------------------------------
        // 3. GENERACIÓN DE ÓRDENES Y TRANSACCIONES EN TIMELINE (30 DÍAS)
        // ---------------------------------------------------------------------
        $universes = ['terra', 'marte', 'venus', 'terra', 'marte'];
        $orderScenarios = [
            // Órdenes de HOY
            ['days_ago' => 0, 'status' => 'processing', 'type' => 'physical', 'universe' => 'terra', 'customer_idx' => 0],
            ['days_ago' => 0, 'status' => 'verified', 'type' => 'dropship', 'universe' => 'marte', 'customer_idx' => 1],
            ['days_ago' => 0, 'status' => 'processing', 'type' => 'mixed', 'universe' => 'terra', 'customer_idx' => 2],

            // Órdenes de los ÚLTIMOS 7 DÍAS
            ['days_ago' => 1, 'status' => 'shipped', 'type' => 'physical', 'universe' => 'terra', 'customer_idx' => 3],
            ['days_ago' => 2, 'status' => 'shipped', 'type' => 'physical', 'universe' => 'marte', 'customer_idx' => 4],
            ['days_ago' => 3, 'status' => 'delivered', 'type' => 'dropship', 'universe' => 'marte', 'customer_idx' => 5],
            ['days_ago' => 4, 'status' => 'delivered', 'type' => 'mixed', 'universe' => 'venus', 'customer_idx' => 0],
            ['days_ago' => 5, 'status' => 'delivered', 'type' => 'physical', 'universe' => 'terra', 'customer_idx' => 1],
            ['days_ago' => 6, 'status' => 'delivered', 'type' => 'physical', 'universe' => 'marte', 'customer_idx' => 2],

            // Órdenes de los ÚLTIMOS 8 A 30 DÍAS
            ['days_ago' => 9, 'status' => 'delivered', 'type' => 'physical', 'universe' => 'terra', 'customer_idx' => 3],
            ['days_ago' => 12, 'status' => 'delivered', 'type' => 'dropship', 'universe' => 'marte', 'customer_idx' => 4],
            ['days_ago' => 15, 'status' => 'delivered', 'type' => 'physical', 'universe' => 'venus', 'customer_idx' => 5],
            ['days_ago' => 18, 'status' => 'delivered', 'type' => 'mixed', 'universe' => 'terra', 'customer_idx' => 0],
            ['days_ago' => 21, 'status' => 'delivered', 'type' => 'physical', 'universe' => 'marte', 'customer_idx' => 1],
            ['days_ago' => 25, 'status' => 'delivered', 'type' => 'physical', 'universe' => 'terra', 'customer_idx' => 2],
            ['days_ago' => 28, 'status' => 'delivered', 'type' => 'dropship', 'universe' => 'venus', 'customer_idx' => 3],
        ];

        $orderSeq = 1001;

        foreach ($orderScenarios as $scen) {
            $customer = $customers[$scen['customer_idx']];
            $orderDate = ($scen['days_ago'] === 0)
                ? $now->copy()->subMinutes(rand(2, 30))
                : $now->copy()->subDays($scen['days_ago'])->subHours(rand(1, 10));
            $orderNumber = "ORD-{$orderSeq}";
            $orderSeq++;

            // Seleccionar 1 a 3 variantes para la orden
            $pickedVariants = $allVariants->random(rand(1, 3));
            $subtotal = 0;
            $cogsTotal = 0;
            $itemsData = [];

            foreach ($pickedVariants as $v) {
                $qty = rand(1, 2);
                $unitPrice = (float) $v->price;
                $lineTotal = $unitPrice * $qty;
                $lineCost = ((float) $v->cost) * $qty;

                $subtotal += $lineTotal;
                $cogsTotal += $lineCost;

                $itemsData[] = [
                    'variant_id' => $v->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $shippingCost = $subtotal >= 899 ? 0.00 : 129.00;
            $orderTotal = $subtotal + $shippingCost;

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $customer['user']->id,
                'status' => $scen['status'],
                'order_type' => $scen['type'],
                'total' => $orderTotal,
                'payment_status' => 'paid',
                'payment_method' => ($orderSeq % 2 === 0) ? 'stripe' : 'mercadopago',
                'billing_address' => $customer['data']['address'],
                'shipping_address' => $customer['data']['address'],
                'created_via' => 'web',
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'fulfillment_status' => $scen['status'] === 'delivered' ? 'fulfilled' : 'waiting',
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            // Cálculo financiero y margen P&L (Hub 5 & Hub 1)
            $gatewayFee = round(($orderTotal * 0.036) + 3.00, 2);
            $packagingCost = 16.00;
            $shippingCourierCost = 145.00;
            $netProfit = max(0, $orderTotal - ($cogsTotal + $gatewayFee + $packagingCost + $shippingCourierCost));
            $netMarginPercent = $orderTotal > 0 ? round(($netProfit / $orderTotal) * 100, 1) : 0;
            $marginLevel = $netMarginPercent >= 35 ? 'healthy' : ($netMarginPercent >= 20 ? 'warning' : 'critical');

            OrderFinancialMargin::create([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'universe' => $scen['universe'],
                'gross_revenue' => $orderTotal,
                'cogs_total' => $cogsTotal,
                'gateway_fee' => $gatewayFee,
                'packaging_cost' => $packagingCost,
                'shipping_courier_cost' => $shippingCourierCost,
                'net_profit' => $netProfit,
                'net_margin_percent' => $netMarginPercent,
                'margin_health_level' => $marginLevel,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Guía de logística (Hub 6 & Hub 1)
            if (in_array($scen['status'], ['shipped', 'delivered', 'processing'])) {
                $shipmentStatus = match ($scen['status']) {
                    'delivered' => 'delivered',
                    'shipped' => 'in_transit',
                    default => 'staged',
                };

                Shipment::create([
                    'order_id' => $order->id,
                    'tracking_number' => 'DHL-MX-' . rand(1000000, 9999999),
                    'courier' => 'DHL Express México',
                    'service_type' => 'Express Doméstico',
                    'weight_actual_kg' => 0.85,
                    'shipping_cost_declared' => $shippingCourierCost,
                    'status' => $shipmentStatus,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }
        }

        // ---------------------------------------------------------------------
        // 4. SUSCRIPCIONES RECURRENTES ACTIVAS / MRR (HUB 1 & HUB 5)
        // ---------------------------------------------------------------------
        $subscriptionTiers = [
            ['tier' => 'Caja Marte VIP Nivel 2', 'amount' => 599.00, 'universe' => 'marte'],
            ['tier' => 'Caja Terra Integral Nivel 1', 'amount' => 489.00, 'universe' => 'terra'],
            ['tier' => 'Venus Sensorial Box Nivel 3', 'amount' => 749.00, 'universe' => 'venus'],
            ['tier' => 'Caja Marte VIP Nivel 2', 'amount' => 599.00, 'universe' => 'marte'],
            ['tier' => 'Caja Terra Integral Nivel 1', 'amount' => 489.00, 'universe' => 'terra'],
            ['tier' => 'Venus Sensorial Box Nivel 3', 'amount' => 749.00, 'universe' => 'venus'],
        ];

        foreach ($subscriptionTiers as $idx => $st) {
            $customer = $customers[$idx % count($customers)];
            CustomerSubscription::firstOrCreate(
                ['subscription_folio' => "SUB-FOLIO-{$st['universe']}-00" . ($idx + 1)],
                [
                    'user_id' => $customer['user']->id,
                    'tier_name' => $st['tier'],
                    'universe' => $st['universe'],
                    'monthly_amount' => $st['amount'],
                    'status' => 'active',
                    'card_last4' => '4242',
                    'card_brand' => 'Visa',
                    'next_billing_at' => $now->copy()->addDays(rand(5, 25)),
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 5. FACTURAS DE PROVEEDORES / CUENTAS POR PAGAR (HUB 5 & HUB 4)
        // ---------------------------------------------------------------------
        $vendorBillsData = [
            ['bill_number' => 'FAC-TORZO-2026-09', 'vendor_name' => 'Torzo Textil B2B SA de CV', 'amount' => 28450.00, 'due_days' => 5, 'status' => 'pending'],
            ['bill_number' => 'FAC-TEXMEX-8821', 'vendor_name' => 'Confección Textil TexMex S.A.', 'amount' => 14200.00, 'due_days' => 2, 'status' => 'scheduled'],
            ['bill_number' => 'FAC-MODAINT-4410', 'vendor_name' => 'ModaIntima Distribuciones MX', 'amount' => 9800.00, 'due_days' => 10, 'status' => 'pending'],
            ['bill_number' => 'INV-META-ADS-2026', 'vendor_name' => 'Meta Platforms Ireland Ltd (Meta Ads)', 'amount' => 12500.00, 'due_days' => 8, 'status' => 'pending'],
        ];

        foreach ($vendorBillsData as $vb) {
            VendorBill::firstOrCreate(
                ['bill_number' => $vb['bill_number']],
                [
                    'vendor_type' => 'supplier',
                    'vendor_name' => $vb['vendor_name'],
                    'amount' => $vb['amount'],
                    'bank_clabe' => '012180004567890123',
                    'bank_name' => 'BBVA México',
                    'status' => $vb['status'],
                    'due_date' => $today->copy()->addDays($vb['due_days']),
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 6. ALIADOS ACTIVOS Y CUPONES DIR B (HUB 7 & HUB 1)
        // ---------------------------------------------------------------------
        $alliesData = [
            [
                'code' => 'ALPHA',
                'name' => 'Gym Alpha Fitness Polanco',
                'category' => 'fitness',
                'category_label' => 'Club de Alto Rendimiento',
                'universe' => 'marte',
                'contact_name' => 'Rodrigo Morales',
                'contact_whatsapp' => '+525540392817',
                'contact_email' => 'rodrigo@alphafitness.mx',
                'coupon_code' => 'ALPHA10',
                'orders_count' => 84,
                'revenue_attributed' => 34800.00,
                'commission_earned' => 5220.00,
            ],
            [
                'code' => 'GALAN',
                'name' => 'Barbería Tradicional El Galán',
                'category' => 'grooming',
                'category_label' => 'Cuidado Personal Masculino',
                'universe' => 'marte',
                'contact_name' => 'Esteban Cruz',
                'contact_whatsapp' => '+525529184736',
                'contact_email' => 'esteban@elgalan.mx',
                'coupon_code' => 'GALAN15',
                'orders_count' => 46,
                'revenue_attributed' => 19400.00,
                'commission_earned' => 2910.00,
            ],
            [
                'code' => 'SENSUAL',
                'name' => 'Sensual Studio & Beauty Boutique',
                'category' => 'lingerie',
                'category_label' => 'Lencería de Autor',
                'universe' => 'venus',
                'contact_name' => 'Camila Valdés',
                'contact_whatsapp' => '+523319827364',
                'contact_email' => 'camila@sensualstudio.mx',
                'coupon_code' => 'SENSUAL10',
                'orders_count' => 38,
                'revenue_attributed' => 16200.00,
                'commission_earned' => 2430.00,
            ],
        ];

        foreach ($alliesData as $ad) {
            $ally = Ally::firstOrCreate(
                ['code' => $ad['code']],
                [
                    'name' => $ad['name'],
                    'category' => $ad['category'],
                    'category_label' => $ad['category_label'],
                    'universe' => $ad['universe'],
                    'contact_name' => $ad['contact_name'],
                    'contact_whatsapp' => $ad['contact_whatsapp'],
                    'contact_email' => $ad['contact_email'],
                    'portal_token' => 'tok_' . strtolower($ad['code']) . '_2026',
                    'commission_type' => 'percentage',
                    'commission_rate' => 15.00,
                    'status' => 'active',
                ]
            );

            AllyCouponDirB::updateOrCreate(
                ['coupon_code' => $ad['coupon_code']],
                [
                    'ally_id' => $ally->id,
                    'discount_pct' => 10,
                    'commission_rate' => 15.00,
                    'universe' => $ad['universe'],
                    'utm_source' => 'ally_' . strtolower($ad['code']),
                    'utm_campaign' => 'convenio_2026',
                    'clicks_count' => $ad['orders_count'] * 4,
                    'orders_count' => $ad['orders_count'],
                    'revenue_attributed' => $ad['revenue_attributed'],
                    'commission_earned' => $ad['commission_earned'],
                    'status' => 'active',
                ]
            );
        }
    }
}

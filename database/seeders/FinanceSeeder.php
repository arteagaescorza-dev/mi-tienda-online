<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderFinancialMargin;
use App\Models\PayoutBatch;
use App\Models\CustomerSubscription;
use App\Models\DunningRetryLog;
use App\Models\VendorBill;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PackageType;
use App\Models\Supplier;
use App\Models\User;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin?->id;

        // 1. Obtener productos y variantes reales de scraping
        $variants = ProductVariant::with('product')->get();
        if ($variants->isEmpty()) {
            return;
        }

        $torzoSupplier = Supplier::where('name', 'Torzo')->first();
        $torzoId = $torzoSupplier?->id;

        // 2. Crear órdenes reales con márgenes financieros auditados (TASK-FIN-01)
        $universes = ['marte', 'terra', 'venus', 'tiresias'];
        $paymentMethods = [
            ['method' => 'stripe', 'fee_pct' => 0.036, 'fee_fix' => 3.00],
            ['method' => 'mercadopago', 'fee_pct' => 0.0349, 'fee_fix' => 4.00],
            ['method' => 'oxxo_pay', 'fee_pct' => 0.030, 'fee_fix' => 12.00],
            ['method' => 'spei', 'fee_pct' => 0.00, 'fee_fix' => 0.00],
        ];

        $boxPackage = PackageType::where('code', 'BOX-M02')->first() ?? PackageType::first();
        $packagingCostDefault = $boxPackage ? (float) $boxPackage->unit_cost : 16.00;

        // Generar 20 órdenes realistas vinculadas al catálogo activo
        for ($i = 1; $i <= 20; $i++) {
            $orderNum = 'ORD-2026-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $universe = $universes[($i - 1) % count($universes)];
            $pm = $paymentMethods[($i - 1) % count($paymentMethods)];

            // Seleccionar 1 a 3 variantes reales al azar
            $selectedVariants = $variants->random(min(rand(1, 3), $variants->count()));
            $orderTotal = 0;
            $cogsTotal = 0;

            $order = Order::updateOrCreate(
                ['order_number' => $orderNum],
                [
                    'user_id' => $adminId,
                    'billing_address' => [
                        'name' => 'Cliente ' . ucfirst($universe) . ' ' . $i,
                        'street' => 'Av. Insurgentes Sur #' . (100 + $i),
                        'city' => 'Ciudad de México',
                        'state' => 'CDMX',
                        'zip' => '03100',
                    ],
                    'shipping_address' => [
                        'name' => 'Cliente ' . ucfirst($universe) . ' ' . $i,
                        'street' => 'Av. Insurgentes Sur #' . (100 + $i),
                        'city' => 'Ciudad de México',
                        'state' => 'CDMX',
                        'zip' => '03100',
                    ],
                    'status' => $i === 1 ? 'processing' : ($i % 5 === 0 ? 'shipped' : 'delivered'),
                    'total' => 0, // Se recalcula
                    'payment_status' => 'paid',
                    'payment_method' => $pm['method'],
                    'notes' => 'Orden de catálogo real para universo ' . ucfirst($universe),
                    'created_via' => 'web',
                    'created_at' => now()->subDays(20 - $i)->subHours(rand(1, 8)),
                ]
            );

            // Crear items
            foreach ($selectedVariants as $v) {
                $qty = rand(1, 2);
                $unitPrice = (float) ($v->product?->price ?? 499.00);
                $lineTotal = round($qty * $unitPrice, 2);
                $costUnit = (float) ($v->product?->cost ?: ($unitPrice * 0.45));

                OrderItem::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'variant_id' => $v->id,
                    ],
                    [
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'supplier_id' => $v->product?->supplierProducts?->first()?->supplier_id ?? $torzoId,
                        'fulfillment_status' => 'fulfilled',
                    ]
                );

                $orderTotal += $lineTotal;
                $cogsTotal += ($costUnit * $qty);
            }

            // Actualizar total orden
            $order->update(['total' => $orderTotal]);

            // Calcular unit economics exactos
            $gatewayFee = round(($orderTotal * $pm['fee_pct']) + ($pm['fee_fix'] > 0 ? $pm['fee_fix'] : 0), 2);
            $packagingCost = $packagingCostDefault;
            $shippingCost = 125.00; // Tarifa estándar courier DHL/Estafeta

            $netProfit = round($orderTotal - $cogsTotal - $gatewayFee - $packagingCost - $shippingCost, 2);
            $netMarginPct = $orderTotal > 0 ? round(($netProfit / $orderTotal) * 100, 2) : 0;
            $healthLevel = $netMarginPct >= 50 ? 'healthy' : ($netMarginPct >= 30 ? 'standard' : 'critical');

            OrderFinancialMargin::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'order_number' => $order->order_number,
                    'universe' => $universe,
                    'gross_revenue' => $orderTotal,
                    'cogs_total' => $cogsTotal,
                    'gateway_fee' => $gatewayFee,
                    'packaging_cost' => $packagingCost,
                    'shipping_courier_cost' => $shippingCost,
                    'net_profit' => $netProfit,
                    'net_margin_percent' => $netMarginPct,
                    'margin_health_level' => $healthLevel,
                    'created_at' => $order->created_at,
                ]
            );
        }

        // 3. Crear Lotes Bancarios de Conciliación (Payout Batches)
        $payouts = [
            [
                'payout_id_external' => 'po_stripe_8832',
                'gateway' => 'stripe',
                'gross_amount' => 44200.00,
                'fee_amount' => 1591.20,
                'tax_withheld' => 298.80,
                'net_deposited' => 42310.00,
                'bank_account_target' => 'BBVA CLABE *9012',
                'status' => 'reconciled',
                'deposit_date' => now()->subHours(6),
            ],
            [
                'payout_id_external' => 'payout_mp_1904',
                'gateway' => 'mercadopago',
                'gross_amount' => 19800.00,
                'fee_amount' => 691.02,
                'tax_withheld' => 188.98,
                'net_deposited' => 18920.00,
                'bank_account_target' => 'BBVA CLABE *9012',
                'status' => 'pending',
                'deposit_date' => now()->addHours(4),
            ],
            [
                'payout_id_external' => 'po_stripe_8835',
                'gateway' => 'stripe',
                'gross_amount' => 31500.00,
                'fee_amount' => 1134.00,
                'tax_withheld' => 213.00,
                'net_deposited' => 30153.00,
                'bank_account_target' => 'BBVA CLABE *9012',
                'status' => 'reconciled',
                'deposit_date' => now()->subDays(2),
            ],
            [
                'payout_id_external' => 'po_oxxo_5512',
                'gateway' => 'oxxo_pay',
                'gross_amount' => 12450.00,
                'fee_amount' => 373.50,
                'tax_withheld' => 76.50,
                'net_deposited' => 12000.00,
                'bank_account_target' => 'BBVA CLABE *9012',
                'status' => 'reconciled',
                'deposit_date' => now()->subDays(3),
            ],
            [
                'payout_id_external' => 'po_spei_4091',
                'gateway' => 'spei',
                'gross_amount' => 28900.00,
                'fee_amount' => 0.00,
                'tax_withheld' => 0.00,
                'net_deposited' => 28900.00,
                'bank_account_target' => 'BBVA CLABE *9012',
                'status' => 'reconciled',
                'deposit_date' => now()->subDays(4),
            ],
            [
                'payout_id_external' => 'payout_mp_1910',
                'gateway' => 'mercadopago',
                'gross_amount' => 15600.00,
                'fee_amount' => 544.44,
                'tax_withheld' => 155.56,
                'net_deposited' => 14900.00,
                'bank_account_target' => 'BBVA CLABE *9012',
                'status' => 'pending',
                'deposit_date' => now()->addDay(),
            ],
        ];

        foreach ($payouts as $po) {
            PayoutBatch::updateOrCreate(
                ['payout_id_external' => $po['payout_id_external']],
                $po
            );
        }

        // 4. Crear Suscripciones MRR y Dunning Logs (TASK-SUB-DUN)
        $subTiers = [
            ['tier' => 'Caja Marte Nivel 1 (Básico)', 'amount' => 599.00],
            ['tier' => 'Caja Marte Nivel 2 (Plus)', 'amount' => 899.00],
            ['tier' => 'Caja Marte Nivel 3 (VIP)', 'amount' => 1299.00],
        ];

        for ($s = 1; $s <= 12; $s++) {
            $folio = 'SUB-MARTE-' . str_pad((string) $s, 3, '0', STR_PAD_LEFT);
            $tier = $subTiers[($s - 1) % count($subTiers)];
            $status = $s === 11 ? 'past_due' : ($s === 12 ? 'cancelled' : 'active');

            $subscription = CustomerSubscription::updateOrCreate(
                ['subscription_folio' => $folio],
                [
                    'user_id' => $adminId,
                    'tier_name' => $tier['tier'],
                    'universe' => 'marte',
                    'monthly_amount' => $tier['amount'],
                    'status' => $status,
                    'payment_method_token' => 'tok_card_' . rand(10000, 99999),
                    'card_last4' => str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'card_brand' => $s % 3 === 0 ? 'mastercard' : ($s % 4 === 0 ? 'amex' : 'visa'),
                    'failed_attempts_count' => $status === 'past_due' ? 2 : 0,
                    'next_billing_at' => $status === 'active' ? now()->addDays(rand(3, 28)) : null,
                    'cancelled_at' => $status === 'cancelled' ? now()->subDays(5) : null,
                ]
            );

            // Si está en past_due, añadir registro de dunning
            if ($status === 'past_due') {
                DunningRetryLog::firstOrCreate(
                    [
                        'subscription_id' => $subscription->id,
                        'attempt_number' => 1,
                    ],
                    [
                        'decline_code' => 'card_expired',
                        'action_taken' => 'automated_charge_retry',
                        'was_recovered' => false,
                        'amount_recovered' => 0.00,
                        'scheduled_retry_at' => now()->subDay(),
                    ]
                );

                DunningRetryLog::firstOrCreate(
                    [
                        'subscription_id' => $subscription->id,
                        'attempt_number' => 2,
                    ],
                    [
                        'decline_code' => 'insufficient_funds',
                        'action_taken' => 'whatsapp_link_sent',
                        'was_recovered' => false,
                        'amount_recovered' => 0.00,
                        'scheduled_retry_at' => now()->addDays(2),
                    ]
                );
            }
        }

        // Suscripción con dunning rescatado exitosamente
        $recoveredSub = CustomerSubscription::first();
        if ($recoveredSub) {
            DunningRetryLog::firstOrCreate(
                [
                    'subscription_id' => $recoveredSub->id,
                    'attempt_number' => 2,
                    'action_taken' => 'whatsapp_link_sent',
                ],
                [
                    'decline_code' => 'insufficient_funds',
                    'was_recovered' => true,
                    'amount_recovered' => $recoveredSub->monthly_amount,
                    'scheduled_retry_at' => now()->subDays(3),
                ]
            );
        }

        // 5. Crear Cuentas por Pagar (Vendor Bills - TASK-INT-19)
        $bills = [
            [
                'bill_number' => 'BILL-2026-001',
                'vendor_type' => 'apparel_workshop',
                'vendor_name' => 'Torzo B2B Wholesale',
                'amount' => 24200.00,
                'bank_clabe' => '012180015678901234',
                'bank_name' => 'BBVA México',
                'status' => 'scheduled',
                'due_date' => now()->addDays(3),
            ],
            [
                'bill_number' => 'BILL-2026-002',
                'vendor_type' => 'apparel_workshop',
                'vendor_name' => 'Talleres Confección CDMX',
                'amount' => 14500.00,
                'bank_clabe' => '072180002345678901',
                'bank_name' => 'Banorte',
                'status' => 'pending',
                'due_date' => now()->addDays(5),
            ],
            [
                'bill_number' => 'BILL-2026-003',
                'vendor_type' => 'cosmetics_lab',
                'vendor_name' => 'Adrenaline Forte Laboratorios',
                'amount' => 18900.00,
                'bank_clabe' => '002180098765432109',
                'bank_name' => 'Citibanamex',
                'status' => 'scheduled',
                'due_date' => now()->addDays(7),
            ],
            [
                'bill_number' => 'BILL-2026-004',
                'vendor_type' => 'courier_carrier',
                'vendor_name' => 'DHL Express México / Skydropx',
                'amount' => 8750.00,
                'bank_clabe' => '014180054321098765',
                'bank_name' => 'Santander',
                'status' => 'pending',
                'due_date' => now()->addDays(2),
            ],
            [
                'bill_number' => 'BILL-2026-005',
                'vendor_type' => 'apparel_workshop',
                'vendor_name' => 'Meta Ads CAPI (Facturación Ads)',
                'amount' => 14300.00,
                'bank_clabe' => '127180011223344556',
                'bank_name' => 'Banco Azteca',
                'status' => 'pending',
                'due_date' => now()->addDays(4),
            ],
            [
                'bill_number' => 'BILL-2026-006',
                'vendor_type' => 'apparel_workshop',
                'vendor_name' => 'Torzo B2B Reposición Inbound',
                'amount' => 16800.00,
                'bank_clabe' => '012180015678901234',
                'bank_name' => 'BBVA México',
                'status' => 'paid',
                'due_date' => now()->subDays(5),
                'paid_at' => now()->subDays(5),
                'payment_reference' => 'SPEI-TORZO-9921',
            ],
        ];

        foreach ($bills as $bill) {
            VendorBill::updateOrCreate(
                ['bill_number' => $bill['bill_number']],
                $bill
            );
        }
    }
}

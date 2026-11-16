<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\CmsStorefrontConfig;
use App\Models\CmsSection;
use App\Models\CmsPage;
use App\Models\CmsBlogPost;
use App\Models\CmsCatalogFilter;
use App\Models\CmsOfficialDocument;
use App\Models\CmsTrackingSetting;
use App\Models\Cart;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Lead;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseKardex;
use App\Models\Supplier;
use App\Models\SupplierSyncRun;
use App\Models\OrderFinancialMargin;
use App\Models\PayoutBatch;
use App\Models\CustomerSubscription;
use App\Models\DunningRetryLog;
use App\Models\VendorBill;
use App\Models\ProductBundle;
use App\Models\CycleCount;
use App\Models\OperationalTask;
use App\Models\ShiftLog;
use App\Models\SupportCase;
use App\Models\ShippingReconciliation;
use App\Models\ShippingReconciliationItem;
use App\Models\ShippingDiscrepancyClaim;
use App\Models\ThreeWayMatching;
use App\Models\TaxLedgerEntry;
use App\Services\Finance\ShippingInvoiceAuditorService;
use App\Services\Finance\ThreeWayMatchingService;
use App\Services\Finance\TaxCalculationEngineService;
use App\Services\Inventory\VariantDimensionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PortalDashboardController extends Controller
{
    public function portalDashboard(Request $request)
    {
        $roleParam = (string) $request->query('role', '');
        $userRole = $roleParam ?: (auth()->user()?->role ?? 'admin');
        $isWarehouse = in_array($userRole, ['warehouse', 'packer']);

        // Executive Metrics
        $todayOrders = Order::query()->whereDate('created_at', today());
        $todaySales = (clone $todayOrders)->sum('total');
        $todayOrderCount = (clone $todayOrders)->count();
        $newLeads = Lead::query()->whereDate('created_at', today())->count();
        $abandonedCarts = Cart::query()->where('status', 'abandoned')->count();
        $avgTicket = $todayOrderCount > 0 ? ((float) $todaySales / $todayOrderCount) : 0;

        // Operative Metrics (Physical units, ZERO monetary symbols)
        $pendingPackingQuery = Order::query()->whereIn('status', ['pending', 'processing', 'verified']);
        $pendingPacking = (clone $pendingPackingQuery)->count();
        $physicalReady = (clone $pendingPackingQuery)->whereDoesntHave('items.variant.product', function ($q) {
            $q->whereIn('type', ['dropship']);
        })->count();
        $onDemandWaiting = (clone $pendingPackingQuery)->whereHas('items.variant.product', function ($q) {
            $q->whereIn('type', ['dropship']);
        })->count();

        $mixedReady = Order::query()->whereIn('status', ['pending', 'processing', 'verified'])
            ->whereHas('items.variant.product', function ($q) {
                $q->whereIn('type', ['dropship', 'both']);
            })->count();
        $inboundTransit = PurchaseOrder::query()->whereIn('status', ['in_transit', 'confirmed'])->count();
        $ropBreaches = Inventory::query()
            ->where(function ($q) {
                $q->whereRaw('(quantity - reserved) <= COALESCE(min_threshold, 5)');
            })->count();

        // Sales / Attention Metrics
        $hotLeads = Lead::query()->whereIn('status', ['new', 'contacted', 'qualified'])->count();
        $todayAbandoned = Cart::query()->where('status', 'abandoned')->whereDate('updated_at', today())->count();
        $doubtfulAddresses = Order::query()->whereIn('status', ['pending', 'processing'])
            ->where(function ($q) {
                $q->whereNull('shipping_address')
                  ->orWhere('shipping_address', 'like', '%s/n%')
                  ->orWhere('shipping_address', 'like', '%sin numero%');
            })->count();

        $kpis = [
            'executive' => [
                ['label' => 'Ventas hoy', 'value' => 'MXN $' . number_format((float) $todaySales, 2), 'trend' => '+18.4%', 'subtitle' => 'Consolidado de vitrinas D2C'],
                ['label' => 'Leads nuevos', 'value' => (string) $newLeads, 'trend' => 'Hoy', 'subtitle' => 'Prospectos en embudo comercial'],
                ['label' => 'Carritos abandonados', 'value' => (string) $abandonedCarts, 'trend' => 'Alta Urgencia', 'subtitle' => 'Recuperables vía WhatsApp'],
                ['label' => 'Ticket promedio', 'value' => 'MXN $' . number_format($avgTicket, 2), 'trend' => 'Hoy', 'subtitle' => 'Promedio por orden finalizada'],
            ],
            'operative' => [
                ['label' => 'Pedidos por empacar', 'value' => (string) $pendingPacking . ' órdenes', 'trend' => 'Corte 14:00', 'subtitle' => $physicalReady . ' en rack físico · ' . $onDemandWaiting . ' bajo pedido'],
                ['label' => 'Carritos mixtos listos', 'value' => (string) $mixedReady . ' pedidos', 'trend' => 'Desbloqueados', 'subtitle' => 'Inbound recibido en rampa'],
                ['label' => 'Recepciones en tránsito', 'value' => (string) $inboundTransit . ' arribos', 'trend' => 'En camino', 'subtitle' => 'Fleteros con llegada hoy'],
                ['label' => 'Quiebres ROP en anaquel', 'value' => (string) $ropBreaches . ' SKUs', 'trend' => 'Reorden', 'subtitle' => 'Stock físico bajo punto crítico'],
            ],
            'sales' => [
                ['label' => 'Leads activos sin responder', 'value' => (string) $hotLeads . ' prospectos', 'trend' => 'Prioritario', 'subtitle' => 'Tiempo medio espera: 14 min'],
                ['label' => 'Carritos abandonados hoy', 'value' => (string) $todayAbandoned . ' carritos', 'trend' => 'Recuperables', 'subtitle' => 'Gatillo WhatsApp disponible'],
                ['label' => 'Pedidos pausados por dirección', 'value' => (string) $doubtfulAddresses . ' envíos', 'trend' => 'Riesgo Postal', 'subtitle' => 'Falta número o CP dudoso'],
                ['label' => 'Cupones activos canjeados', 'value' => '12 canjes', 'trend' => 'Hoy', 'subtitle' => 'Efectividad comercial: 22.4%'],
            ],
        ];

        $initialTab = match ($userRole) {
            'warehouse', 'packer', 'purchases' => 'operative',
            'sales' => 'sales',
            default => 'executive',
        };

        $dashboardApi = app(\App\Http\Controllers\Api\RevOps\DashboardApiController::class);
        $dashboardApi->ensureDefaultOperationalTasksExist();
        $dashboardApi->ensureDefaultSalesIncidentsExist();

        $operationalTasks = OperationalTask::query()
            ->orderByRaw("CASE WHEN priority = 'critical' THEN 1 WHEN priority = 'high' THEN 2 WHEN priority = 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_at', 'asc')
            ->get();
        $shiftLogs = ShiftLog::query()->latest('id')->limit(15)->get();
        $salesIncidents = SupportCase::query()
            ->orderBy('status', 'asc')
            ->orderByRaw("CASE WHEN priority = 'high' THEN 1 WHEN priority = 'medium' THEN 2 ELSE 3 END")
            ->latest('id')
            ->get();

        $analyticsRequest = \App\Http\Requests\Api\RevOps\DashboardGetAnalyticsChartsRequest::createFrom($request);
        $analyticsCharts = $dashboardApi->getAnalyticsCharts($analyticsRequest)->getData(true);
        
        $healthRequest = \App\Http\Requests\Api\RevOps\DashboardGetSystemHealthRequest::createFrom($request);
        $systemHealth = $dashboardApi->getSystemHealth($healthRequest)->getData(true);

        // Executive Areas Analytics Data (Task-Hub1-Subnav)
        $mrrActive = (float) \App\Models\CustomerSubscription::query()->where('status', 'active')->sum('monthly_amount');
        $totalPiecesInWarehouse = (int) Inventory::query()->sum('quantity');
        $totalInventoryCppValuation = (float) Inventory::query()
            ->join('product_variants', 'inventories.variant_id', '=', 'product_variants.id')
            ->sum(\Illuminate\Support\Facades\DB::raw('inventories.quantity * COALESCE(product_variants.cost, 0)'));

        $registeredCustomersCount = (int) \App\Models\User::query()->where('role', 'customer')->count();
        $activeAgreementsCount = (int) \App\Models\Ally::query()->where('status', 'active')->count();
        $attributedRevenue = (float) \App\Models\AllyCouponDirB::query()->sum('revenue_attributed');
        $pendingCommissions = (float) \App\Models\AllyCouponDirB::query()->sum('commission_earned');
        $redeemedCouponsCount = (int) \App\Models\AllyCouponDirB::query()->sum('orders_count');
        $guidesShippedToday = (int) \App\Models\Shipment::query()->whereDate('created_at', today())->count();
        $grossRevenueMonth = (float) \App\Models\OrderFinancialMargin::query()->whereMonth('created_at', now()->month)->sum('gross_revenue') + (float) $mrrActive;
        $grossMarginAvg = (float) \App\Models\OrderFinancialMargin::query()->avg('net_margin_percent') ?: 58.4;
        $pendingVendorBills = (float) \App\Models\VendorBill::query()->whereIn('status', ['pending', 'scheduled'])->sum('amount');

        $ropTableItems = Inventory::query()
            ->with(['variant.product'])
            ->where(function ($q) {
                $q->whereRaw('(quantity - reserved) <= COALESCE(min_threshold, 5)');
            })
            ->limit(8)
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'sku' => $inv->variant?->sku ?? ('SKU-' . $inv->id),
                    'title' => $inv->variant?->product?->title ?? 'Prenda Básica Textil',
                    'size' => $inv->variant?->attributes['size'] ?? 'M',
                    'available' => max(0, $inv->quantity - $inv->reserved),
                    'min_threshold' => $inv->min_threshold ?? 12,
                    'location' => $inv->location ?? 'warehouse-1',
                    'cost' => (float) ($inv->variant?->cost ?? 120.00),
                ];
            })
            ->values()
            ->all();

        if (empty($ropTableItems)) {
            $ropTableItems = [
                ['id' => 1, 'sku' => 'TOR-DRP-003', 'title' => 'Boxer Modal Negro', 'size' => 'M', 'available' => 4, 'min_threshold' => 12, 'location' => 'Anaquel A-01', 'cost' => 110.00],
                ['id' => 2, 'sku' => 'TOR-JCK-008', 'title' => 'Jockstrap Athletic Azul', 'size' => 'L', 'available' => 2, 'min_threshold' => 10, 'location' => 'Anaquel B-03', 'cost' => 95.00],
                ['id' => 3, 'sku' => 'MOD-BRF-014', 'title' => 'Brief Algodón Pima Blanco', 'size' => 'S', 'available' => 3, 'min_threshold' => 15, 'location' => 'Anaquel A-04', 'cost' => 105.00],
                ['id' => 4, 'sku' => 'VEN-SWI-021', 'title' => 'Bikini Sensorial Ciruela', 'size' => 'M', 'available' => 1, 'min_threshold' => 8, 'location' => 'Anaquel C-02', 'cost' => 140.00],
            ];
        }

        $executiveAreas = [
            'inventory' => [
                'stockValue' => 'MXN $' . number_format($totalInventoryCppValuation > 0 ? $totalInventoryCppValuation : 243040.00, 2),
                'pieces' => number_format($totalPiecesInWarehouse > 0 ? $totalPiecesInWarehouse : 3674),
                'rotationDays' => '4.2',
                'supplierRuptureRate' => '8.4%',
                'ropCount' => max($ropBreaches, count($ropTableItems)),
                'ropItems' => $ropTableItems,
            ],
            'store' => [
                'sessions' => '12,450',
                'conversionRate' => '28.0%',
                'mixedCarts' => '1,280',
                'avgTicket' => 'MXN $' . number_format($avgTicket > 0 ? $avgTicket : 1420.00, 2),
            ],
            'crm' => [
                'registeredCustomers' => number_format($registeredCustomersCount > 0 ? $registeredCustomersCount : 1840),
                'avgLtv' => 'MXN $1,420',
                'cacBlend' => 'MXN $145',
                'churnRate' => '2.1%',
            ],
            'finance' => [
                'grossRevenue' => 'MXN $' . number_format($grossRevenueMonth > 0 ? $grossRevenueMonth : 112400.00, 2),
                'grossMargin' => round($grossMarginAvg, 1) . '%',
                'bankBalance' => 'MXN $340,200',
                'pendingBills' => 'MXN $' . number_format($pendingVendorBills > 0 ? $pendingVendorBills : 48250.00, 2),
            ],
            'logistics' => [
                'shippedToday' => (string) max($guidesShippedToday, 84),
                'onTimeDelivery' => '96.8%',
                'avgTransitDays' => '1.8 días',
                'claimsCount' => (string) max($doubtfulAddresses, 2),
            ],
            'allies' => [
                'activeCount' => (string) max($activeAgreementsCount, 5),
                'attributedRevenue' => 'MXN $' . number_format($attributedRevenue > 0 ? $attributedRevenue : 32500.00, 2),
                'pendingCommissions' => 'MXN $' . number_format($pendingCommissions > 0 ? $pendingCommissions : 4875.00, 2),
                'redeemedCount' => (string) max($redeemedCouponsCount, 240),
            ],
        ];

        $serverDashboardData = [
            'userRole' => $userRole,
            'initialTab' => $initialTab,
            'isWarehouseRole' => $isWarehouse,
            'kpis' => $kpis,
            'alerts' => $this->dashboardAlerts(),
            'operationalTasks' => $operationalTasks,
            'salesIncidents' => $salesIncidents,
            'analyticsCharts' => $analyticsCharts,
            'systemHealth' => $systemHealth,
            'executiveAreas' => $executiveAreas,
            'shiftLogs' => $shiftLogs->map(fn ($l) => [
                'id' => $l->id,
                'event_type' => $l->event_type,
                'message' => $l->message,
                'operator_name' => $l->operator_name,
                'time' => $l->created_at ? $l->created_at->format('h:i A') : now()->format('h:i A'),
                'created_at' => $l->created_at?->toIso8601String(),
            ])->values()->all(),
        ];

        return view('portal.dashboard', [
            'userRole' => $userRole,
            'initialTab' => $initialTab,
            'isWarehouseRole' => $isWarehouse,
            'kpis' => $kpis,
            'legacyKpis' => $kpis['executive'],
            'alerts' => $this->dashboardAlerts(),
            'operationalTasks' => $operationalTasks,
            'salesIncidents' => $salesIncidents,
            'analyticsCharts' => $analyticsCharts,
            'systemHealth' => $systemHealth,
            'shiftLogs' => $shiftLogs,
            'executiveAreas' => $executiveAreas,
            'serverDashboardData' => $serverDashboardData,
        ]);
    }

    public function portalScoring()
    {
        $dbLeads = Lead::all();
        $topLeads = $dbLeads->map(function (Lead $l) {
            $score = (int) (data_get($l->metadata, 'score') ?: ($l->status === 'won' ? 90 : ($l->status === 'qualified' ? 80 : 60)));
            $segment = $score >= 85 ? 'Muy caliente' : ($score >= 70 ? 'Caliente' : 'Nurturing');
            return [
                'name' => $l->name ?? 'Lead #' . $l->id,
                'score' => $score,
                'segment' => $segment,
            ];
        })->sortByDesc('score')->take(5)->values()->toArray();

        return view('portal.scoring', [
            'rules' => [
                ['event' => 'Visita producto premium', 'score' => '+10'],
                ['event' => 'Agrega al carrito', 'score' => '+20'],
                ['event' => 'Abandona checkout', 'score' => '-8'],
                ['event' => 'Responde por WhatsApp', 'score' => '+15'],
                ['event' => 'Compra recurrente completada', 'score' => '+30'],
            ],
            'topLeads' => $topLeads ?: [
                ['name' => 'Carlos Mendoza', 'score' => 95, 'segment' => 'Muy caliente'],
                ['name' => 'Andrea Ruiz', 'score' => 89, 'segment' => 'Muy caliente'],
                ['name' => 'Valeria Gómez', 'score' => 88, 'segment' => 'Caliente'],
            ],
        ]);
    }

    public function portalModule(string $module)
    {
        $redirectMap = [
            'chatbot' => 'portal.marketing',
            'atribucion' => 'portal.marketing',
            'emailing' => 'portal.marketing',
            'cupones' => 'portal.marketing',
            'conciliacion' => 'portal.finance',
            'logistica' => 'portal.logistics',
            'garantias' => 'portal.logistics',
        ];

        abort_unless(isset($redirectMap[$module]), 404);

        return redirect()->route($redirectMap[$module]);
    }

    private function dashboardAlerts(): array
    {
        $lowStock = Inventory::query()
            ->whereNotNull('min_threshold')
            ->whereRaw('(quantity - reserved) <= min_threshold')
            ->count();

        return array_values(array_filter([
            $lowStock > 0 ? "{$lowStock} ubicaciones tienen stock bajo." : null,
            Cart::query()->where('status', 'abandoned')->exists() ? 'Hay carritos abandonados pendientes de seguimiento.' : null,
            SupplierProduct::query()->where('availability', 'out_of_stock')->exists() ? 'Hay referencias de proveedor agotadas.' : null,
        ])) ?: ['No hay alertas operativas pendientes.'];
    }
}

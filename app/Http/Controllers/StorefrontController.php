<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use App\Models\Order;
use App\Models\Product;
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
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    public function home()
    {
        $products = $this->catalogProducts();

        return view('welcome', [
            'featuredProducts' => array_slice($products, 0, 3),
            'preorderProducts' => array_slice($products, 3, 3),
            'outletProducts' => $this->outletProducts(),
            'scrapedProducts' => $this->scrapedLandingProducts(),
            'scrapedBrands' => $this->scrapedBrandLogos(),
        ]);
    }

    public function products()
    {
        $products = $this->catalogProducts();

        return view('store.products', [
            'products' => $products,
            'preorderProducts' => array_slice($products, 3, 3),
            'outletProducts' => $this->outletProducts(),
            'extendedProducts' => $this->scrapedCatalogProducts(),
        ]);
    }

    public function product(string $slug)
    {
        $products = collect($this->catalogProducts())->merge($this->outletProducts());
        $product = $products->firstWhere('slug', $slug) ?? $products->first();

        return view('store.product', [
            'product' => $product,
            'products' => $products->values()->all(),
        ]);
    }

    public function categories()
    {
        $categories = [
            [
                'name' => 'Hombre',
                'image' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=1100&q=80',
                'description' => 'Capsula urbana con piezas funcionales para diario.',
            ],
            [
                'name' => 'Mujer',
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1100&q=80',
                'description' => 'Siluetas modernas con texturas ligeras y premium.',
            ],
            [
                'name' => 'Accesorios',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1100&q=80',
                'description' => 'Complementos clave para elevar cada outfit.',
            ],
            [
                'name' => 'Calzado',
                'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=1100&q=80',
                'description' => 'Comodidad, estilo y durabilidad para todo el dia.',
            ],
        ];

        return view('store.categories', [
            'categories' => $categories,
        ]);
    }

    public function offers()
    {
        return view('store.offers', [
            'products' => $this->sampleProducts(),
        ]);
    }

    public function about()
    {
        return view('store.about');
    }

    public function contact()
    {
        return view('store.contact');
    }

    public function privacy()
    {
        return view('store.privacy');
    }

    public function cookies()
    {
        return view('store.cookies');
    }

    public function cart()
    {
        return view('store.cart', [
            'products' => array_slice($this->sampleProducts(), 0, 3),
            'currentCheckoutStep' => 0,
        ]);
    }

    public function checkout()
    {
        return view('store.checkout-step-1', ['currentCheckoutStep' => 0]);
    }

    public function checkoutSuccess(Request $request)
    {
        return view('store.checkout-success', [
            'orderNumber' => $request->query('order', 'ORD-SIN-NUMERO'),
            'currentCheckoutStep' => 3,
        ]);
    }

    public function profile()
    {
        return view('store.profile', [
            'user' => request()->user(),
        ]);
    }

    public function orders()
    {
        $orders = Order::query()
            ->where('user_id', request()->user()->id)
            ->with(['items.variant.product'])
            ->latest()
            ->limit(20)
            ->get();

        return view('store.orders', [
            'orders' => $orders,
        ]);
    }

    public function portalDashboard()
    {
        $todayOrders = Order::query()->whereDate('created_at', today());
        $todaySales = (clone $todayOrders)->sum('total');
        $todayOrderCount = (clone $todayOrders)->count();
        $newLeads = Lead::query()->whereDate('created_at', today())->count();
        $abandonedCarts = Cart::query()->where('status', 'abandoned')->count();

        return view('portal.dashboard', [
            'kpis' => [
                ['label' => 'Ventas hoy', 'value' => 'MXN $' . number_format((float) $todaySales, 2)],
                ['label' => 'Leads nuevos', 'value' => (string) $newLeads],
                ['label' => 'Carritos abandonados', 'value' => (string) $abandonedCarts],
                ['label' => 'Ticket promedio', 'value' => 'MXN $' . number_format($todayOrderCount ? (float) $todaySales / $todayOrderCount : 0, 2)],
            ],
            'alerts' => $this->dashboardAlerts(),
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

    public function portalInventory(Request $request)
    {
        $productsQuery = Product::query()
            ->with([
                'variants.inventories',
                'supplierProducts.supplier',
            ])
            ->where('visible', true)
            ->orderBy('id');

        $dbProducts = $productsQuery->get();

        $portalProducts = $dbProducts->map(function (Product $product) {
            $variants = $product->variants;
            $totalStock = (int) $variants->sum(fn ($v) => $v->inventories->sum('quantity'));
            $totalReserved = (int) $variants->sum(fn ($v) => $v->inventories->sum('reserved'));
            $availableStock = max(0, $totalStock - $totalReserved);

            $sizes = [];
            $committedSizes = [];
            foreach ($variants as $v) {
                $rawSize = data_get($v->attributes, 'size', 'u');
                $sizeKey = strtolower(trim((string) $rawSize));
                $vQty = (int) $v->inventories->sum('quantity');
                $vRes = (int) $v->inventories->sum('reserved');
                $sizes[$sizeKey] = max(0, $vQty - $vRes);
                $committedSizes[$sizeKey] = $vRes;
            }

            $sp = $product->supplierProducts->first();
            $supplierStock = $sp && $sp->availability === 'in_stock' ? 50 : 0;
            $mainImage = data_get($product->meta, 'image')
                ?: (data_get($product->meta, 'images.0') ?: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80');
            $mainImage = str_replace('/./', '/', $mainImage);

            $cost = (float) ($product->cost ?: ($product->price * 0.5));
            $pvp = (float) $product->price;
            $supplierName = $sp?->supplier?->name ?? ($product->brand ?: 'Torzo');

            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'weightedAverageCost' => $cost,
                'name' => $product->title,
                'brand' => $product->brand ?? $supplierName,
                'category' => data_get($product->meta, 'category', 'Ropa Interior'),
                'type' => $product->type === 'physical' ? 'propio' : ($product->type === 'dropship' ? 'dropship' : 'mixto'),
                'localStock' => $availableStock,
                'committedStock' => $totalReserved,
                'committedSizes' => $committedSizes,
                'supplierStock' => $supplierStock,
                'location' => 'Rack A-01 / Nivel 1',
                'status' => $availableStock > 0 ? 'Saludable' : ($supplierStock > 0 ? 'Solo proveedor' : 'Sin stock'),
                'pvp' => $pvp,
                'cost' => $cost,
                'sizes' => $sizes,
                'image' => $mainImage,
                'adjustType' => 'add',
                'adjustQty' => '',
                'adjustReason' => '',
            ];
        })->values();

        $syncRuns = SupplierSyncRun::query()
            ->with('supplier')
            ->when($request->filled('supplier_id'), fn ($query) => $query->where('supplier_id', $request->integer('supplier_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest('started_at')
            ->limit(20)
            ->get();

        $totalLocalStock = (int) $portalProducts->sum('localStock');
        $totalDropshipRefs = (int) $portalProducts->where('supplierStock', '>', 0)->count();
        $totalSkus = (int) $portalProducts->count();
        $lastScrape = SupplierProduct::max('last_checked_at')
            ? now()->parse(SupplierProduct::max('last_checked_at'))->format('d/m/Y H:i')
            : 'Hace 14 min';

        $dbSuppliers = Supplier::query()->withCount('supplierProducts')->orderBy('name')->get();
        $portalProviders = $dbSuppliers->map(function ($s) {
            $isTorzo = str_contains(strtolower($s->name), 'torzo');
            return [
                'id' => 'prov_' . $s->id,
                'dbId' => $s->id,
                'vendorCode' => 'VND-' . strtoupper(Str::slug($s->name)) . '-001',
                'name' => $s->name . ' B2B',
                'tradeName' => $s->name,
                'type' => 'scraper',
                'typeLabel' => 'Fabricante Mayorista & Scraping B2B',
                'city' => $isTorzo ? 'CDMX' : 'Guadalajara',
                'contactName' => $isTorzo ? 'Carlos Mendoza' : 'Atención Mayoristas',
                'phone' => '+52 55 4182 9901',
                'email' => $s->contact_email ?: ('b2b@' . Str::slug($s->name) . '.com'),
                'extractionMethod' => 'Headless Scraper (Puppeteer)',
                'frequency' => 'Automático (Cada 6 hrs)',
                'lastScrapeTime' => 'Hace 18 min',
                'syncedItemsCount' => (int) $s->supplier_products_count,
                'status' => 'connected',
                'leadTimeDays' => $isTorzo ? 3 : 5,
                'paymentTerms' => 'contado',
                'slaOnTimePct' => 97.5,
                'slaFillRatePct' => 98.0,
                'totalOrders' => 12,
                'completedOrders' => 11,
                'avgDelayDays' => 0.3,
                'isSyncing' => false,
                'notes' => $s->notes ?: 'Proveedor oficial sincronizado.',
            ];
        })->values();

        $dbPurchaseOrders = PurchaseOrder::query()->with(['supplier', 'items.product', 'items.variant'])->latest()->get();
        $portalPurchaseOrders = $dbPurchaseOrders->map(function ($po) {
            $firstItem = $po->items->first();
            $sizes = ['ch' => 0, 'm' => 0, 'g' => 0, 'xl' => 0, '2xl' => 0, '3xl' => 0];
            foreach ($po->items as $it) {
                $szKey = strtolower(trim((string) $it->size_name));
                if (array_key_exists($szKey, $sizes)) {
                    $sizes[$szKey] += $it->quantity_ordered;
                } else {
                    $sizes[$szKey] = $it->quantity_ordered;
                }
            }
            $statusLabel = match ($po->status) {
                'received' => 'Recibida en Almacén',
                'in_transit' => 'En Camino',
                'partially_received' => 'Parcialmente Recibida',
                'confirmed' => 'Por Despachar',
                'cancelled' => 'Cancelada',
                default => 'Borrador',
            };

            return [
                'id' => $po->po_number,
                'dbId' => $po->id,
                'supplier' => $po->supplier?->name ?? 'Proveedor',
                'supplier_id' => $po->supplier_id,
                'carrier' => $po->notes ?: 'Paquetería Express (Guía Pendiente)',
                'eta' => $po->estimated_delivery_at ? $po->estimated_delivery_at->format('d M, Y') : 'En preparación',
                'sku' => $firstItem?->product?->sku ?? 'LOTE-COMPRA',
                'productName' => $firstItem?->product?->title ?? 'Lote de Reabastecimiento',
                'qty' => (int) $po->total_units,
                'sizes' => $sizes,
                'unitCost' => (float) ($firstItem?->unit_cost ?? 0),
                'totalCost' => (float) $po->total_amount,
                'date' => $po->created_at ? $po->created_at->format('d M, Y') : now()->format('d M, Y'),
                'status' => $statusLabel,
                'linkedCartId' => null,
                'linkedCustomer' => null,
                'items' => $po->items->map(fn ($it) => [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'variant_id' => $it->product_variant_id,
                    'size' => $it->size_name,
                    'quantity_ordered' => $it->quantity_ordered,
                    'quantity_received' => $it->quantity_received,
                    'unit_cost' => (float) $it->unit_cost,
                    'total_cost' => (float) $it->total_cost,
                ])->values(),
            ];
        })->values();

        $topProductsToPlan = $portalProducts->sortBy('localStock')->take(3)->values();
        $portalFuturePurchaseOrders = $topProductsToPlan->map(function ($p, $idx) {
            $needed = max(24, 60 - (int) $p['localStock']);
            $cost = (float) $p['cost'];
            $ch = (int) round($needed * 0.15);
            $m = (int) round($needed * 0.35);
            $g = (int) round($needed * 0.30);
            $xl = (int) round($needed * 0.15);
            $xxl = max(0, $needed - ($ch + $m + $g + $xl));

            return [
                'id' => 'FP-2026-0' . ($idx + 1),
                'sku' => $p['sku'],
                'productName' => $p['name'],
                'supplier' => $p['brand'] ?: 'Torzo',
                'qty' => $needed,
                'sizes' => ['ch' => $ch, 'm' => $m, 'g' => $g, 'xl' => $xl, '2xl' => $xxl, '3xl' => 0],
                'costUnit' => $cost,
                'totalCost' => round($needed * $cost, 2),
                'priority' => $p['localStock'] <= 0 ? 'alta' : 'media',
                'status' => 'Planificada',
                'date' => now()->subDays(2 - $idx)->format('d M, Y'),
                '_editingSizes' => false,
            ];
        })->values();

        $portalPurchaseKardex = PurchaseKardex::query()
            ->with(['supplier', 'purchaseOrder'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($k) => [
                'id' => 'KDX-PUR-' . str_pad((string) $k->id, 4, '0', STR_PAD_LEFT),
                'timestamp' => $k->created_at ? \Carbon\Carbon::parse($k->created_at)->format('d M, Y H:i') : now()->format('d M, Y H:i'),
                'type' => $k->movement_type,
                'typeLabel' => match ($k->movement_type) {
                    'PO_CREATED' => 'Emisión de PO',
                    'INBOUND_RECEIPT' => 'Recepción en Rampa',
                    default => 'Movimiento Compras',
                },
                'badgeClass' => $k->movement_type === 'INBOUND_RECEIPT' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'poNumber' => $k->purchaseOrder?->po_number ?? 'PO-GRAL',
                'supplier' => $k->supplier?->name ?? 'Mayorista',
                'sku' => $k->purchaseOrder?->items?->first()?->product?->sku ?? 'MULTIPLE',
                'productName' => $k->purchaseOrder?->items?->first()?->product?->title ?? 'Lote de Compra',
                'deltaQty' => '+' . ($k->purchaseOrder?->total_units ?? 0) . ' uds',
                'totalAmount' => (float) $k->total_amount,
                'user' => 'Elena Compras',
                'notes' => $k->description,
            ])->values();

        return view('portal.inventory', [
            'summary' => [
                'stock_local' => number_format($totalLocalStock) . ' piezas',
                'bajo_pedido' => number_format($totalDropshipRefs) . ' referencias',
                'skus_activos' => number_format($totalSkus) . ' modelos',
                'last_scrape' => (string) $lastScrape,
            ],
            'portalProducts' => $portalProducts,
            'portalProviders' => $portalProviders,
            'portalPurchaseOrders' => $portalPurchaseOrders,
            'portalFuturePurchaseOrders' => $portalFuturePurchaseOrders,
            'portalPurchaseKardex' => $portalPurchaseKardex,
            'syncRuns' => $syncRuns,
            'syncSuppliers' => \App\Models\Supplier::query()->orderBy('name')->get(['id', 'name']),
            'variantMappings' => \App\Models\SupplierVariantMapping::query()
                ->with(['supplierProduct.supplier', 'productVariant.product'])
                ->latest()
                ->limit(50)
                ->get(),
        ]);
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

    public function portalFinance(Request $request)
    {
        $ordersCount = OrderFinancialMargin::count();
        $ordersGross = (float) OrderFinancialMargin::sum('gross_revenue');
        $mrrActive = (float) CustomerSubscription::where('status', 'active')->sum('monthly_amount');
        $facturacionBruta = $ordersGross + $mrrActive;

        $totalCogs = (float) OrderFinancialMargin::sum('cogs_total');
        $totalGatewayFees = (float) OrderFinancialMargin::sum('gateway_fee');
        $totalPackaging = (float) OrderFinancialMargin::sum('packaging_cost');
        $totalShipping = (float) OrderFinancialMargin::sum('shipping_courier_cost');
        $adsSpend = (float) (VendorBill::where('vendor_name', 'like', '%Meta Ads%')->value('amount') ?? 14300.00);

        $totalEgresos = $totalCogs + $totalGatewayFees + $totalPackaging + $totalShipping + $adsSpend;
        $netProfit = max(0, $facturacionBruta - $totalEgresos);
        $netMarginPercent = $facturacionBruta > 0 ? round(($netProfit / $facturacionBruta) * 100, 1) : 0;

        // Top 4 KPI Cards
        $cards = [
            [
                'label' => 'Facturación del mes',
                'value' => 'MXN $' . number_format($facturacionBruta, 2),
                'note' => 'Ventas D2C + Membresías MRR',
                'change' => '+14.2%',
            ],
            [
                'label' => 'Egresos operativos',
                'value' => 'MXN $' . number_format($totalEgresos, 2),
                'note' => 'COGS, pasarelas, empaque y fletes',
                'change' => 'Deducciones',
            ],
            [
                'label' => 'Margen neto real',
                'value' => $netMarginPercent . '%',
                'note' => 'Rentabilidad líquida (EBITDA)',
                'change' => 'EBITDA',
            ],
            [
                'label' => 'MRR Recurrente Activo',
                'value' => 'MXN $' . number_format($mrrActive, 2),
                'note' => CustomerSubscription::where('status', 'active')->count() . ' miembros vigentes',
                'change' => 'MRR',
            ],
        ];

        // Cascada P&L
        $waterfall = [
            'gross_revenue' => $facturacionBruta,
            'cogs' => $totalCogs,
            'cogs_percent' => $facturacionBruta > 0 ? round(($totalCogs / $facturacionBruta) * 100, 1) : 0,
            'gateway_fees' => $totalGatewayFees,
            'gateway_percent' => $facturacionBruta > 0 ? round(($totalGatewayFees / $facturacionBruta) * 100, 1) : 0,
            'packaging' => $totalPackaging,
            'packaging_percent' => $facturacionBruta > 0 ? round(($totalPackaging / $facturacionBruta) * 100, 1) : 0,
            'shipping' => $totalShipping,
            'shipping_percent' => $facturacionBruta > 0 ? round(($totalShipping / $facturacionBruta) * 100, 1) : 0,
            'ads_spend' => $adsSpend,
            'ads_percent' => $facturacionBruta > 0 ? round(($adsSpend / $facturacionBruta) * 100, 1) : 0,
            'net_profit' => $netProfit,
            'net_margin_percent' => $netMarginPercent,
        ];

        // Lotes Bancarios (Payout Batches)
        $payoutBatches = PayoutBatch::orderByDesc('id')->get();
        $payoutKpis = [
            'gross_captured' => (float) PayoutBatch::sum('gross_amount'),
            'fees_retained' => (float) PayoutBatch::sum('fee_amount') + (float) PayoutBatch::sum('tax_withheld'),
            'reconciled_amount' => (float) PayoutBatch::where('status', 'reconciled')->sum('net_deposited'),
            'pending_amount' => (float) PayoutBatch::where('status', 'pending')->sum('net_deposited'),
        ];

        // Suscripciones y Dunning
        $subscriptions = CustomerSubscription::with('dunningLogs')->orderByDesc('id')->get();
        $subscriptionKpis = [
            'mrr_active' => $mrrActive,
            'active_subscribers' => CustomerSubscription::where('status', 'active')->count(),
            'churn_rate' => '2.1%',
            'recovered_dunning' => (float) DunningRetryLog::where('was_recovered', true)->sum('amount_recovered'),
        ];
        $dunningLogs = DunningRetryLog::with('subscription')->latest()->limit(10)->get();

        // Margen Neto por Pedido
        $orderMargins = OrderFinancialMargin::with(['order.items.variant.product'])->latest()->limit(25)->get();
        $marginKpis = [
            'avg_ticket' => $ordersCount > 0 ? round((float) OrderFinancialMargin::avg('gross_revenue'), 2) : 0,
            'avg_margin' => $ordersCount > 0 ? round((float) OrderFinancialMargin::avg('net_margin_percent'), 1) : 0,
            'avg_packaging' => $ordersCount > 0 ? round((float) OrderFinancialMargin::avg('packaging_cost'), 2) : 0,
            'avg_shipping' => $ordersCount > 0 ? round((float) OrderFinancialMargin::avg('shipping_courier_cost'), 2) : 0,
        ];

        // Cuentas por Pagar (Vendor Bills)
        $vendorBills = VendorBill::orderByDesc('id')->get();
        $vendorBillKpis = [
            'pending_week' => (float) VendorBill::whereIn('status', ['pending', 'scheduled'])->sum('amount'),
            'workshops_pending' => (float) VendorBill::where('vendor_type', 'apparel_workshop')->whereIn('status', ['pending', 'scheduled'])->sum('amount'),
            'ads_invoiced' => $adsSpend,
            'avg_payment_days' => '14 días',
        ];

        // Movimientos recientes reales (de orders y vendor_bills)
        $latestOrders = Order::latest()->limit(5)->get();
        $latestBills = VendorBill::latest()->limit(5)->get();
        $movements = collect();
        foreach ($latestOrders as $ord) {
            $movements->push([
                'type' => 'Ingreso',
                'concept' => 'Venta Orden #' . $ord->order_number . ' (' . strtoupper($ord->payment_method ?? 'WEB') . ')',
                'amount' => '+MXN $' . number_format((float) $ord->total, 2),
                'date' => $ord->created_at?->diffForHumans() ?? 'Hoy',
            ]);
        }
        foreach ($latestBills as $vb) {
            $movements->push([
                'type' => 'Egreso',
                'concept' => $vb->bill_number . ' - ' . $vb->vendor_name,
                'amount' => '-MXN $' . number_format((float) $vb->amount, 2),
                'date' => $vb->due_date?->format('d M, Y') ?? 'Próximo',
            ]);
        }

        $activeTab = $request->query('tab', 'cashflow');

        return view('portal.finance', [
            'activeTab' => $activeTab,
            'cards' => $cards,
            'waterfall' => $waterfall,
            'payoutBatches' => $payoutBatches,
            'payoutKpis' => $payoutKpis,
            'subscriptions' => $subscriptions,
            'subscriptionKpis' => $subscriptionKpis,
            'dunningLogs' => $dunningLogs,
            'orderMargins' => $orderMargins,
            'marginKpis' => $marginKpis,
            'vendorBills' => $vendorBills,
            'vendorBillKpis' => $vendorBillKpis,
            'movements' => $movements->take(6)->values(),
        ]);
    }

    public function reconcilePayoutBatch(PayoutBatch $payoutBatch)
    {
        $payoutBatch->update([
            'status' => 'reconciled',
            'deposit_date' => now(),
        ]);

        return redirect()->route('portal.finance', ['tab' => 'reconciliation'])
            ->with('success', "Lote {$payoutBatch->payout_id_external} conciliado exitosamente.");
    }

    public function retrySubscriptionDunning(CustomerSubscription $subscription)
    {
        $subscription->dunningLogs()->create([
            'attempt_number' => $subscription->dunningLogs()->count() + 1,
            'decline_code' => 'card_retry_manual',
            'action_taken' => 'automated_charge_retry',
            'was_recovered' => true,
            'amount_recovered' => $subscription->monthly_amount,
            'scheduled_retry_at' => now(),
        ]);

        $subscription->update([
            'status' => 'active',
            'failed_attempts_count' => 0,
            'next_billing_at' => now()->addMonth(),
        ]);

        return redirect()->route('portal.finance', ['tab' => 'subscriptions'])
            ->with('success', "Cobro reintentado y membresía {$subscription->subscription_folio} rescatada.");
    }

    public function payVendorBill(VendorBill $vendorBill)
    {
        $vendorBill->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => 'SPEI-AUTO-' . rand(100000, 999999),
        ]);

        return redirect()->route('portal.finance', ['tab' => 'expenses'])
            ->with('success', "Cuenta por pagar {$vendorBill->bill_number} liquidada por SPEI.");
    }

    public function portalModule(string $module)
    {
        $modules = [
            'chatbot' => ['title' => 'Chatbot', 'description' => 'Centro visual para conversaciones y soporte al cliente.', 'headline' => 'Conversaciones y derivaciones', 'status' => 'UI lista', 'initial' => 'CH', 'action' => 'Nueva conversacion', 'kpis' => [['label' => 'Conversaciones', 'value' => '0', 'note' => 'Pendiente de conexion'], ['label' => 'Leads derivados', 'value' => '0', 'note' => 'Sin datos aun'], ['label' => 'Tiempo respuesta', 'value' => '-', 'note' => 'Por configurar'], ['label' => 'Estado', 'value' => 'Listo', 'note' => 'Pantalla disponible']], 'table_title' => 'Bandeja de conversaciones', 'features' => ['Historial por cliente', 'Derivacion a lead', 'Asignacion de agente', 'Metricas de respuesta']],
            'atribucion' => ['title' => 'Atribucion', 'description' => 'Vista para revisar origen, campanas y conversiones.', 'headline' => 'Origen de conversiones', 'status' => 'UI lista', 'initial' => 'AT', 'action' => 'Nuevo filtro', 'kpis' => [['label' => 'Sesiones', 'value' => '0', 'note' => 'Pendiente de eventos'], ['label' => 'Conversiones', 'value' => '0', 'note' => 'Sin datos aun'], ['label' => 'Canal principal', 'value' => '-', 'note' => 'Por configurar'], ['label' => 'Estado', 'value' => 'Listo', 'note' => 'Pantalla disponible']], 'table_title' => 'Rendimiento por canal', 'features' => ['Captura UTM', 'Sesiones de cliente', 'Modelo de atribucion', 'Reporte de conversion']],
            'emailing' => ['title' => 'Emailing', 'description' => 'Espacio visual para plantillas, campañas y metricas.', 'headline' => 'Campañas y comunicaciones', 'status' => 'UI lista', 'initial' => 'EM', 'action' => 'Nueva plantilla', 'kpis' => [['label' => 'Campañas activas', 'value' => '0', 'note' => 'Pendiente de conexion'], ['label' => 'Enviados', 'value' => '0', 'note' => 'Sin datos aun'], ['label' => 'Apertura', 'value' => '-', 'note' => 'Por configurar'], ['label' => 'Estado', 'value' => 'Listo', 'note' => 'Pantalla disponible']], 'table_title' => 'Biblioteca de campañas', 'features' => ['Editor de plantillas', 'Segmentos de leads', 'Programacion de envios', 'Metricas de apertura']],
            'cupones' => ['title' => 'Cupones', 'description' => 'Administracion visual de descuentos y promociones.', 'headline' => 'Promociones comerciales', 'status' => 'UI lista', 'initial' => 'CU', 'action' => 'Nuevo cupon', 'kpis' => [['label' => 'Cupones activos', 'value' => '0', 'note' => 'Pendiente de conexion'], ['label' => 'Canjes', 'value' => '0', 'note' => 'Sin datos aun'], ['label' => 'Descuento medio', 'value' => '-', 'note' => 'Por configurar'], ['label' => 'Estado', 'value' => 'Listo', 'note' => 'Pantalla disponible']], 'table_title' => 'Reglas de descuento', 'features' => ['Vigencia', 'Limites de uso', 'Productos aplicables', 'Validacion en checkout']],
            'conciliacion' => ['title' => 'Conciliacion', 'description' => 'Comparacion visual de pedidos, pagos y movimientos.', 'headline' => 'Control de diferencias', 'status' => 'UI lista', 'initial' => 'CO', 'action' => 'Importar movimientos', 'kpis' => [['label' => 'Pendientes', 'value' => '0', 'note' => 'Pendiente de conexion'], ['label' => 'Conciliados', 'value' => '0', 'note' => 'Sin datos aun'], ['label' => 'Diferencias', 'value' => '0', 'note' => 'Por configurar'], ['label' => 'Estado', 'value' => 'Listo', 'note' => 'Pantalla disponible']], 'table_title' => 'Movimientos por conciliar', 'features' => ['Importacion de pagos', 'Cruce con pedidos', 'Diferencias', 'Cierre de periodos']],
            'logistica' => ['title' => 'Logistica', 'description' => 'Seguimiento visual de envios, guias y entregas.', 'headline' => 'Envios y entregas', 'status' => 'UI lista', 'initial' => 'LO', 'action' => 'Nuevo envio', 'kpis' => [['label' => 'Por preparar', 'value' => '0', 'note' => 'Pendiente de conexion'], ['label' => 'En transito', 'value' => '0', 'note' => 'Sin datos aun'], ['label' => 'Entregados', 'value' => '0', 'note' => 'Por configurar'], ['label' => 'Estado', 'value' => 'Listo', 'note' => 'Pantalla disponible']], 'table_title' => 'Cola de envios', 'features' => ['Generacion de guias', 'Estados de entrega', 'Incidencias', 'Rastreo por pedido']],
            'garantias' => ['title' => 'Garantias', 'description' => 'Gestion visual de solicitudes, evidencias y resoluciones.', 'headline' => 'Solicitudes de garantia', 'status' => 'UI lista', 'initial' => 'GA', 'action' => 'Nueva solicitud', 'kpis' => [['label' => 'Abiertas', 'value' => '0', 'note' => 'Pendiente de conexion'], ['label' => 'En revision', 'value' => '0', 'note' => 'Sin datos aun'], ['label' => 'Resueltas', 'value' => '0', 'note' => 'Por configurar'], ['label' => 'Estado', 'value' => 'Listo', 'note' => 'Pantalla disponible']], 'table_title' => 'Casos abiertos', 'features' => ['Evidencias', 'Dictamen', 'Reemplazo o reembolso', 'Historial por pedido']],
        ];

        abort_unless(isset($modules[$module]), 404);

        return view('portal.module', ['module' => $modules[$module]]);
    }

    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $products = collect($this->catalogProducts());

        if ($term !== '') {
            $products = $products->filter(function (array $product) use ($term) {
                return str_contains(strtolower($product['name']), strtolower($term))
                    || str_contains(strtolower($product['brand'] ?? ''), strtolower($term))
                    || str_contains(strtolower($product['category'] ?? ''), strtolower($term));
            });
        }

        return view('store.search', [
            'query' => $term,
            'products' => $products->values()->all(),
        ]);
    }

    private function sampleProducts(): array
    {
        return $this->catalogProducts();
    }

    private function catalogProducts(): array
    {
        return Product::query()
            ->with([
                'variants' => fn ($query) => $query->where('visible', true)->orderBy('id'),
                'categories',
            ])
            ->where('visible', true)
            ->orderBy('id')
            ->get()
            ->map(function (Product $product): array {
                $variant = $product->variants->first();
                $attributes = $variant?->attributes ?? [];

                return [
                    'slug' => $product->slug,
                    'name' => $product->title,
                    'price' => 'MXN $' . number_format((float) ($variant?->price ?? $product->price), 2),
                    'price_value' => (float) ($variant?->price ?? $product->price),
                    'description' => $product->description ?? '',
                    'image' => data_get($product->meta, 'image', 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80'),
                    'brand' => $product->brand ?? '-',
                    'size' => data_get($attributes, 'size', 'M'),
                    'fit' => data_get($attributes, 'fit', 'Regular'),
                    'color' => data_get($attributes, 'color', 'Negro'),
                    'garment_type' => data_get($product->meta, 'garment_type', 'Prenda'),
                    'category' => $product->categories->first()?->name ?? (data_get($product->meta, 'category', 'Catalogo')),
                    'variant_id' => $variant?->id,
                    'variants' => $product->variants->map(function ($productVariant): array {
                        return [
                            'id' => $productVariant->id,
                            'size' => data_get($productVariant->attributes, 'size', 'U'),
                            'color' => data_get($productVariant->attributes, 'color', 'Unico'),
                            'price' => (float) $productVariant->price,
                        ];
                    })->values()->all(),
                ];
            })
            ->all();
    }

    private function outletProducts(): array
    {
        $all = $this->catalogProducts();
        return array_slice($all, 6, 6) ?: array_slice($all, 0, 3);
    }

    private function scrapedLandingProducts(): array
    {
        return SupplierProduct::query()
            ->with('supplier:id,name')
            ->whereNotNull('supplier_url')
            ->whereNotNull('price')
            ->latest('last_checked_at')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(function (SupplierProduct $item) {
                $snapshot = is_array($item->raw_snapshot) ? $item->raw_snapshot : [];

                return [
                    'supplier_name' => $item->supplier?->name ?? 'Proveedor',
                    'supplier_sku' => $item->supplier_sku,
                    'title' => $this->resolveScrapedTitle($snapshot, $item->supplier_sku),
                    'category' => $snapshot['categoria'] ?? 'Categoria general',
                    'image' => $this->resolveScrapedImage($snapshot),
                    'price' => 'MXN $' . number_format((float) $item->price, 2),
                    'availability' => $this->resolveAvailabilityLabel($item->availability),
                    'url' => $item->supplier_url,
                ];
            })
            ->values()
            ->all();
    }

    private function scrapedCatalogProducts(): array
    {
        return SupplierProduct::query()
            ->with('supplier:id,name,website')
            ->whereNotNull('supplier_url')
            ->whereNotNull('price')
            ->latest('last_checked_at')
            ->latest('id')
            ->limit(80)
            ->get()
            ->map(function (SupplierProduct $item) {
                $snapshot = is_array($item->raw_snapshot) ? $item->raw_snapshot : [];
                $brand = $snapshot['brand'] ?? ($item->supplier?->name ?? 'Proveedor');
                $sizes = $this->resolveCardSizes($snapshot, null);

                return [
                    'slug' => 'ext-' . $item->id,
                    'name' => $this->resolveScrapedTitle($snapshot, $item->supplier_sku),
                    'price' => 'MXN $' . number_format((float) $item->price, 2),
                    'price_value' => (float) $item->price,
                    'description' => $snapshot['description'] ?? 'Producto capturado desde el catalogo extendido del proveedor.',
                    'image' => $this->resolveScrapedImage($snapshot),
                    'brand' => $brand,
                    'size' => $sizes[0] ?? '-',
                    'sizes' => $sizes,
                    'fit' => '-',
                    'color' => '-',
                    'category' => $snapshot['categoria'] ?? 'Catalogo extendido',
                    'source' => 'extended',
                    'availability' => $item->availability,
                    'availability_label' => $this->resolveAvailabilityLabel($item->availability),
                    'url' => $item->supplier_url,
                    'supplier_sku' => $item->supplier_sku,
                    'garment_type' => $snapshot['categoria'] ?? 'Catalogo extendido',
                ];
            })
            ->values()
            ->all();
    }

    private function scrapedBrandLogos(): array
    {
        return SupplierProduct::query()
            ->with('supplier:id,name,website')
            ->whereNotNull('supplier_id')
            ->latest('last_checked_at')
            ->latest('id')
            ->limit(80)
            ->get()
            ->pluck('supplier')
            ->filter()
            ->unique('id')
            ->take(12)
            ->map(function ($supplier) {
                $name = (string) ($supplier->name ?? 'Marca');

                return [
                    'name' => $name,
                    'logo' => $this->resolveBrandLogo((string) ($supplier->website ?? '')),
                    'initials' => $this->resolveBrandInitials($name),
                ];
            })
            ->values()
            ->all();
    }

    private function resolveScrapedImage(array $snapshot): string
    {
        $candidate = $snapshot['imagen1'] ?? $snapshot['imagen2'] ?? null;

        if (! is_string($candidate) || trim($candidate) === '') {
            return 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80';
        }

        if (Str::startsWith($candidate, ['http://', 'https://'])) {
            return $candidate;
        }

        return 'https://torzo.kexe.mx' . '/' . ltrim($candidate, '/');
    }

    private function resolveScrapedTitle(array $snapshot, string $sku): string
    {
        $candidate = $snapshot['title']
            ?? $snapshot['name']
            ?? $snapshot['nombreProducto']
            ?? $snapshot['nombre']
            ?? $snapshot['producto']
            ?? null;

        if (is_string($candidate) && trim($candidate) !== '') {
            return trim($candidate);
        }

        $clean = str_replace(['_', '-'], ' ', $sku);
        return Str::title($clean);
    }

    private function resolveAvailabilityLabel(string $availability): string
    {
        return match ($availability) {
            'in_stock' => 'En stock',
            'out_of_stock' => 'Agotado',
            default => 'Disponibilidad por confirmar',
        };
    }

    private function resolveBrandLogo(string $website): ?string
    {
        $domain = $this->resolveWebsiteDomain($website);
        if ($domain === null) {
            return null;
        }

        return 'https://logo.clearbit.com/' . $domain;
    }

    private function resolveWebsiteDomain(string $website): ?string
    {
        $candidate = trim($website);
        if ($candidate === '') {
            return null;
        }

        if (! Str::startsWith($candidate, ['http://', 'https://'])) {
            $candidate = 'https://' . $candidate;
        }

        $host = parse_url($candidate, PHP_URL_HOST);
        if (! is_string($host) || trim($host) === '') {
            return null;
        }

        return preg_replace('/^www\./i', '', strtolower($host));
    }

    private function resolveBrandInitials(string $name): string
    {
        $words = collect(explode(' ', trim($name)))->filter()->values();
        if ($words->isEmpty()) {
            return 'MK';
        }

        $initials = $words->take(2)->map(function (string $word) {
            return Str::upper(Str::substr($word, 0, 1));
        })->implode('');

        return $initials !== '' ? $initials : 'MK';
    }

    private function resolveCardSizes(array $snapshot, ?string $fallbackSize = null): array
    {
        $sizes = collect($snapshot['variations'] ?? [])
            ->filter(function ($variation) {
                return is_array($variation);
            })
            ->map(function (array $variation) {
                $size = $variation['size'] ?? $variation['title'] ?? null;
                return is_string($size) ? trim($size) : null;
            })
            ->filter(function ($size) {
                return is_string($size) && $size !== '';
            })
            ->unique()
            ->values();

        if ($sizes->isEmpty() && is_string($fallbackSize) && trim($fallbackSize) !== '' && trim($fallbackSize) !== '-') {
            $sizes = collect([trim($fallbackSize)]);
        }

        if ($sizes->isEmpty()) {
            return ['U'];
        }

        return $sizes->all();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Lead;
use App\Models\SupplierSyncRun;
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
        return view('portal.scoring', [
            'rules' => [
                ['event' => 'Visita producto premium', 'score' => '+10'],
                ['event' => 'Agrega al carrito', 'score' => '+20'],
                ['event' => 'Abandona checkout', 'score' => '-8'],
                ['event' => 'Responde por WhatsApp', 'score' => '+15'],
            ],
            'topLeads' => [
                ['name' => 'Andrea Ruiz', 'score' => 92, 'segment' => 'Muy caliente'],
                ['name' => 'Luis Cabrera', 'score' => 84, 'segment' => 'Caliente'],
                ['name' => 'Monica Perez', 'score' => 73, 'segment' => 'Nurturing'],
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

        return view('portal.inventory', [
            'summary' => [
                'stock_local' => number_format($totalLocalStock) . ' piezas',
                'bajo_pedido' => number_format($totalDropshipRefs) . ' referencias',
                'skus_activos' => number_format($totalSkus) . ' modelos',
                'last_scrape' => (string) $lastScrape,
            ],
            'portalProducts' => $portalProducts,
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

    public function portalFinance()
    {
        return view('portal.finance', [
            'cards' => [
                ['label' => 'Ingresos del mes', 'value' => 'MXN $418,200'],
                ['label' => 'Egresos del mes', 'value' => 'MXN $263,990'],
                ['label' => 'Margen estimado', 'value' => '36.9%'],
                ['label' => 'Reembolsos', 'value' => 'MXN $12,480'],
            ],
            'movements' => [
                ['type' => 'Ingreso', 'concept' => 'Ventas ecommerce', 'amount' => 'MXN $24,810'],
                ['type' => 'Egreso', 'concept' => 'Compra proveedor dropship', 'amount' => 'MXN $9,640'],
                ['type' => 'Egreso', 'concept' => 'Costo envios nacionales', 'amount' => 'MXN $2,130'],
            ],
        ]);
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

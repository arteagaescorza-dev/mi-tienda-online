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

class StorefrontController extends Controller
{
    public function home(Request $request)
    {
        $activeUniverse = strtolower((string) $request->query('universo', 'marte'));
        if (!in_array($activeUniverse, ['marte', 'venus', 'tiresias', 'terra'])) {
            $activeUniverse = 'marte';
        }

        $defaultStorefronts = [
            'marte' => [
                'brandName' => 'MARTE',
                'subTitle' => 'UNIVERSO MASCULINO',
                'heroTag' => 'MARTE // COLECCIÓN OFICIAL TORZO',
                'heroTitle' => 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA',
                'heroSubtitle' => 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.',
                'pageTitle' => 'MARTE — Universo Masculino & Multiverso Store',
            ],
            'venus' => [
                'brandName' => 'VENUS',
                'subTitle' => 'LENCERÍA & BEAUTY',
                'heroTag' => 'VENUS // LENCERÍA DE AUTOR & SILUETAS',
                'heroTitle' => 'LENCERÍA DE AUTOR & SILUETAS SENSORIALES',
                'heroSubtitle' => 'Encajes finos, satín italiano y fórmulas limpias de cuidado personal pensadas para tu bienestar diario.',
                'pageTitle' => 'VENUS — Lencería de Autor & Beauty',
            ],
            'tiresias' => [
                'brandName' => 'TIRESIAS',
                'subTitle' => 'MODA AFIRMATIVA',
                'heroTag' => 'TIRESIAS // AFIRMATIVA & NO BINARIO',
                'heroTitle' => 'LIBERTAD DE EXPRESIÓN Y AJUSTE SEGURO',
                'heroSubtitle' => 'Prendas afirmativas confeccionadas con ingeniería textil transpirable de compresión médica segura.',
                'pageTitle' => 'TIRESIAS — Moda Afirmativa & Binders',
            ],
            'terra' => [
                'brandName' => 'TERRA',
                'subTitle' => 'ECOSISTEMA GLOBAL',
                'heroTag' => 'TERRA // CATÁLOGO GLOBAL CONSOLIDADO',
                'heroTitle' => 'EL ECOSISTEMA COMPLETO DE MODA ÍNTIMA Y ESTILO',
                'heroSubtitle' => 'Catálogo unificado, membresías mensuales recurrentes y fórmulas de autocuidado para todas las identidades.',
                'pageTitle' => 'TERRA — Ecosistema Multiverso Store',
            ],
        ];

        $storefrontConfig = CmsStorefrontConfig::forUniverse($activeUniverse);
        $dbData = array_filter([
            'brandName' => $storefrontConfig->brand_name,
            'kicker' => $storefrontConfig->kicker,
            'logoUrl' => $storefrontConfig->logo_url,
            'logoMobileUrl' => $storefrontConfig->logo_mobile_url,
            'faviconUrl' => $storefrontConfig->favicon_url,
            'subTitle' => $storefrontConfig->sub_title,
            'heroTag' => $storefrontConfig->hero_tag,
            'heroTitle' => $storefrontConfig->hero_title,
            'heroSubtitle' => $storefrontConfig->hero_subtitle,
            'heroCtaText' => $storefrontConfig->hero_cta_text,
            'heroCtaUrl' => $storefrontConfig->hero_cta_url,
            'pageTitle' => $storefrontConfig->page_title,
            'primaryColor' => $storefrontConfig->primary_color,
            'secondaryColor' => $storefrontConfig->secondary_color,
            'accentColor' => $storefrontConfig->accent_color,
            'backgroundColor' => $storefrontConfig->background_color,
            'surfaceColor' => $storefrontConfig->surface_color,
            'textColor' => $storefrontConfig->text_color,
            'mutedTextColor' => $storefrontConfig->muted_text_color,
            'borderColor' => $storefrontConfig->border_color,
            'customCss' => $storefrontConfig->custom_css,
            'customJs' => $storefrontConfig->custom_js,
        ], fn($val) => !is_null($val) && $val !== '');

        $cachedStorefront = Cache::get('cms_storefront_' . $activeUniverse, []);
        $currentStorefront = array_merge($defaultStorefronts[$activeUniverse], $dbData, $cachedStorefront);

        $shippingRule = Cache::get('cms_shipping_rule', [
            'freeShippingThreshold' => 999.00,
            'flatRate' => 149.00,
            'status' => 'active',
        ]);

        $announcements = Cache::get('cms_announcements', [
            ['id' => 1, 'text' => 'ENVÍOS EXPRESS A TODO MÉXICO', 'active' => true],
            ['id' => 2, 'text' => 'PAGOS 100% SEGUROS CON TARJETA & OXXO', 'active' => true],
            ['id' => 3, 'text' => 'STOCK FÍSICO CON ENTREGA INMEDIATA', 'active' => true],
            ['id' => 4, 'text' => 'ENVÍO GRATIS EN COMPRAS MAYORES A $999', 'active' => true],
            ['id' => 5, 'text' => 'CONCIERGE & ATENCIÓN EXCLUSIVA EN LÍNEA', 'active' => true],
        ]);

        $sections = CmsSection::active()
            ->forUniverse($activeUniverse)
            ->orderBy('sort_order')
            ->get();

        $navbarPages = CmsPage::inNavbar()->get();
        $mobilePages = CmsPage::inMobileNavbar()->get();
        $footerPages = CmsPage::inFooter()->get();
        $explorePages = CmsPage::active()->orderBy('navbar_order')->get();
        $trackingSettings = CmsTrackingSetting::current();
        $officialDocs = CmsOfficialDocument::where('is_active', true)->get()->keyBy('code');

        $pwaProducts = $this->getPwaStoreProducts();

        foreach ($sections as $section) {
            if (in_array($section->type, ['curated_products', 'categories_grid', 'subcategories_slider', 'family_showcase', 'tags_cloud'])) {
                $section->resolved_products = $this->getFilteredProductsForSection($section, $pwaProducts);
            }
        }

        $allConfigs = CmsStorefrontConfig::all()->keyBy('universe');
        $universeMeta = [];
        foreach (['marte', 'venus', 'tiresias', 'terra'] as $u) {
            $conf = $allConfigs->get($u);
            $cached = Cache::get('cms_storefront_' . $u, []);
            $universeMeta[$u] = [
                'brandName' => $conf?->brand_name ?? $defaultStorefronts[$u]['brandName'],
                'subTitle' => $conf?->kicker ?? $conf?->sub_title ?? $defaultStorefronts[$u]['subTitle'],
                'kicker' => $conf?->kicker ?? $defaultStorefronts[$u]['subTitle'],
                'logoUrl' => $conf?->logo_url,
                'logoMobileUrl' => $conf?->logo_mobile_url,
                'faviconUrl' => $conf?->favicon_url,
                'heroTag' => $conf?->hero_tag ?? $defaultStorefronts[$u]['heroTag'],
                'heroTitle' => $conf?->hero_title ?? $defaultStorefronts[$u]['heroTitle'],
                'heroSubtitle' => $conf?->hero_subtitle ?? $defaultStorefronts[$u]['heroSubtitle'],
                'pageTitle' => $conf?->page_title ?? $defaultStorefronts[$u]['pageTitle'],
                'primaryColor' => $conf?->primary_color ?? '#09090b',
                'secondaryColor' => $conf?->secondary_color ?? '#27272a',
                'accentColor' => $conf?->accent_color ?? ($defaultStorefronts[$u]['accentColor'] ?? '#4f46e5'),
                'backgroundColor' => $conf?->background_color ?? ($defaultStorefronts[$u]['backgroundColor'] ?? '#fafafa'),
                'surfaceColor' => $conf?->surface_color ?? '#ffffff',
                'textColor' => $conf?->text_color ?? '#09090b',
                'mutedTextColor' => $conf?->muted_text_color ?? '#71717a',
                'borderColor' => $conf?->border_color ?? '#e4e4e7',
            ];
            if (!empty($cached)) {
                $universeMeta[$u] = array_merge($universeMeta[$u], array_filter($cached));
            }
        }

        return view('store.pwa.home', [
            'activeUniverse' => $activeUniverse,
            'currentStorefront' => $currentStorefront,
            'universeMeta' => $universeMeta,
            'storefrontConfig' => $storefrontConfig,
            'shippingRule' => $shippingRule,
            'announcements' => $announcements,
            'sections' => $sections,
            'navbarPages' => $navbarPages,
            'mobilePages' => $mobilePages,
            'footerPages' => $footerPages,
            'explorePages' => $explorePages,
            'initialTab' => $request->query('tab', 'home'),
            'tracking' => $trackingSettings,
            'trackingSettings' => $trackingSettings,
            'officialDocs' => $officialDocs,
            'products' => $pwaProducts,
            'initialProducts' => $pwaProducts,
            'categories' => \App\Models\Category::all(),
            'categoriesTree' => $this->getCategoriesTree(),
            'suppliers' => $this->getActiveSuppliers(),
        ]);
    }

    /**
     * Página /explorar: renderiza el catálogo de colecciones y páginas de exploración.
     */
    public function explore(Request $request)
    {
        $request->merge(['tab' => 'explore']);
        return $this->home($request);
    }

    public function getFilteredProductsForSection(CmsSection $section, array $allProducts): array
    {
        $rules = $section->query_rules ?? [];
        if (is_string($rules)) {
            $rules = json_decode($rules, true) ?: [];
        }

        $filtered = array_filter($allProducts, function ($p) use ($rules) {
            // Garment type filter
            if (!empty($rules['garment_type']) && $rules['garment_type'] !== 'all') {
                $gtTarget = strtolower(trim($rules['garment_type']));
                $pGt = strtolower(trim($p['garment_type'] ?? ''));
                $pName = strtolower(trim($p['name'] ?? ''));
                $pBrand = strtolower(trim($p['brand'] ?? ''));
                if (!str_contains($pGt, $gtTarget) && !str_contains($pName, $gtTarget) && !str_contains($pBrand, $gtTarget)) {
                    return false;
                }
            }

            // Category filter
            if (!empty($rules['category_id'])) {
                $catId = (int)$rules['category_id'];
                $pCatIds = $p['category_ids'] ?? [];
                if (!empty($pCatIds) && !in_array($catId, $pCatIds)) {
                    return false;
                }
            }

            // Subcategory filter
            if (!empty($rules['subcategory_id'])) {
                $subId = (int)$rules['subcategory_id'];
                $pCatIds = $p['category_ids'] ?? [];
                if (!empty($pCatIds) && !in_array($subId, $pCatIds)) {
                    return false;
                }
            }

            // Brand filter
            if (!empty($rules['brand']) && $rules['brand'] !== 'all') {
                $brandTarget = strtolower(trim($rules['brand']));
                $pBrand = strtolower(trim($p['brand'] ?? ''));
                if (!str_contains($pBrand, $brandTarget)) {
                    return false;
                }
            }

            // Textile family filter
            if (!empty($rules['family']) && $rules['family'] !== 'all') {
                $familyTarget = strtolower(trim($rules['family']));
                $pFabric = strtolower(trim($p['fabric_type'] ?? ''));
                $pDesc = strtolower(trim($p['description'] ?? ''));
                if (!str_contains($pFabric, $familyTarget) && !str_contains($pDesc, $familyTarget)) {
                    return false;
                }
            }

            // Style tag filter
            if (!empty($rules['tag']) && $rules['tag'] !== 'all') {
                $tagTarget = strtolower(trim($rules['tag']));
                $pDesc = strtolower(trim($p['description'] ?? ''));
                $pName = strtolower(trim($p['name'] ?? ''));
                if (!str_contains($pDesc, $tagTarget) && !str_contains($pName, $tagTarget)) {
                    return false;
                }
            }

            // Discount filter
            if (!empty($rules['discount'])) {
                $discPct = (float)($p['discount_pct'] ?? 0);
                if ($rules['discount'] === 'only_sale' && $discPct <= 0) {
                    return false;
                } elseif ($rules['discount'] === 'min_15' && $discPct < 15) {
                    return false;
                } elseif ($rules['discount'] === 'min_25' && $discPct < 25) {
                    return false;
                }
            }

            return true;
        });

        if (empty($filtered)) {
            $filtered = $allProducts;
        }

        $limit = isset($rules['limit']) ? (int)$rules['limit'] : 8;
        if ($limit <= 0) $limit = 8;

        return array_slice(array_values($filtered), 0, $limit);
    }

    public function products()
    {
        $products = $this->catalogProducts();

        $suppliersTree = \App\Models\Supplier::where('status', '!=', 'archived')
            ->get()
            ->map(function ($sup) {
                return [
                    'id' => $sup->id,
                    'name' => $sup->trade_name ?? $sup->name,
                    'brands' => $sup->getBrandsTree(),
                ];
            });

        if ($suppliersTree->isEmpty()) {
            $suppliersTree = collect([
                [
                    'id' => 1,
                    'name' => 'Torzo B2B',
                    'brands' => [
                        ['name' => 'Torzo Denim', 'slug' => 'torzo-denim', 'origin' => 'scraping', 'is_active' => true],
                    ],
                ],
                [
                    'id' => 2,
                    'name' => 'ModaIntima Textil MX',
                    'brands' => [
                        ['name' => 'Marte', 'slug' => 'marte', 'origin' => 'manual', 'is_active' => true],
                    ],
                ],
            ]);
        }

        return view('store.products', [
            'products' => $products,
            'preorderProducts' => array_slice($products, 3, 3),
            'outletProducts' => $this->outletProducts(),
            'extendedProducts' => $this->scrapedCatalogProducts(),
            'suppliersTree' => $suppliersTree,
        ]);
    }

    public function product(string $slug)
    {
        $dbProduct = Product::query()
            ->with(['variants.inventories', 'categories', 'supplierProducts.supplier'])
            ->where('visible', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('id', $slug)
                    ->orWhere('sku', $slug);
            })
            ->first();

        if (! $dbProduct) {
            return redirect()->route('store.home');
        }

        $activeUniverse = strtolower((string) request()->query('universo', 'marte'));
        if (! in_array($activeUniverse, ['marte', 'venus', 'tiresias', 'terra'])) {
            $activeUniverse = 'marte';
        }

        $storefrontConfig = CmsStorefrontConfig::forUniverse($activeUniverse);
        $dbData = array_filter([
            'brandName' => $storefrontConfig->brand_name,
            'kicker' => $storefrontConfig->kicker,
            'logoUrl' => $storefrontConfig->logo_url,
            'logoMobileUrl' => $storefrontConfig->logo_mobile_url,
            'faviconUrl' => $storefrontConfig->favicon_url,
            'subTitle' => $storefrontConfig->sub_title,
            'heroTag' => $storefrontConfig->hero_tag,
            'heroTitle' => $storefrontConfig->hero_title,
            'heroSubtitle' => $storefrontConfig->hero_subtitle,
            'heroCtaText' => $storefrontConfig->hero_cta_text,
            'heroCtaUrl' => $storefrontConfig->hero_cta_url,
            'pageTitle' => $storefrontConfig->page_title,
            'primaryColor' => $storefrontConfig->primary_color,
            'secondaryColor' => $storefrontConfig->secondary_color,
            'accentColor' => $storefrontConfig->accent_color,
            'backgroundColor' => $storefrontConfig->background_color,
            'surfaceColor' => $storefrontConfig->surface_color,
            'textColor' => $storefrontConfig->text_color,
            'mutedTextColor' => $storefrontConfig->muted_text_color,
            'borderColor' => $storefrontConfig->border_color,
            'customCss' => $storefrontConfig->custom_css,
            'customJs' => $storefrontConfig->custom_js,
        ], fn($val) => !is_null($val) && $val !== '');

        $defaultStorefronts = [
            'marte' => [
                'brandName' => 'MARTE',
                'subTitle' => 'UNIVERSO MASCULINO',
                'heroTag' => 'MARTE // COLECCIÓN OFICIAL TORZO',
                'heroTitle' => 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA',
                'heroSubtitle' => 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.',
                'pageTitle' => $dbProduct->title . ' — MARTE Store',
            ],
            'venus' => [
                'brandName' => 'VENUS',
                'subTitle' => 'LENCERÍA & BEAUTY',
                'heroTag' => 'VENUS // LENCERÍA DE AUTOR & SILUETAS',
                'heroTitle' => 'LENCERÍA DE AUTOR & SILUETAS SENSORIALES',
                'heroSubtitle' => 'Encajes finos, satín italiano y fórmulas limpias de cuidado personal pensadas para tu bienestar diario.',
                'pageTitle' => $dbProduct->title . ' — VENUS Store',
            ],
            'tiresias' => [
                'brandName' => 'TIRESIAS',
                'subTitle' => 'MODA AFIRMATIVA',
                'heroTag' => 'TIRESIAS // AFIRMATIVA & NO BINARIO',
                'heroTitle' => 'LIBERTAD DE EXPRESIÓN Y AJUSTE SEGURO',
                'heroSubtitle' => 'Prendas afirmativas confeccionadas con ingeniería textil transpirable de compresión médica segura.',
                'pageTitle' => $dbProduct->title . ' — TIRESIAS Store',
            ],
            'terra' => [
                'brandName' => 'TERRA',
                'subTitle' => 'ECOSISTEMA GLOBAL',
                'heroTag' => 'TERRA // CATÁLOGO GLOBAL CONSOLIDADO',
                'heroTitle' => 'EL ECOSISTEMA COMPLETO DE MODA ÍNTIMA Y ESTILO',
                'heroSubtitle' => 'Catálogo unificado, membresías mensuales recurrentes y fórmulas de autocuidado para todas las identidades.',
                'pageTitle' => $dbProduct->title . ' — TERRA Store',
            ],
        ];

        $cachedStorefront = Cache::get('cms_storefront_' . $activeUniverse, []);
        $currentStorefront = array_merge($defaultStorefronts[$activeUniverse], $dbData, $cachedStorefront);
        $currentStorefront['pageTitle'] = $dbProduct->title . ' — ' . ($currentStorefront['brandName'] ?? 'TORZO');

        $navbarPages = CmsPage::inNavbar()->get();
        $mobilePages = CmsPage::inMobileNavbar()->get();
        $footerPages = CmsPage::inFooter()->get();
        $tracking = CmsTrackingSetting::current();
        $sizeGuideDoc = CmsOfficialDocument::forDocument('torzo_size_guide');
        $careProtocolDoc = CmsOfficialDocument::forDocument('care_protocol');
        $profecoDoc = CmsOfficialDocument::forDocument('profeco_sanitary');

        $shippingRule = Cache::get('cms_shipping_rule', [
            'freeShippingThreshold' => 999.00,
            'flatRate' => 149.00,
            'status' => 'active',
        ]);

        $announcements = Cache::get('cms_announcements', [
            ['id' => 1, 'text' => 'ENVÍOS EXPRESS A TODO MÉXICO', 'active' => true],
            ['id' => 2, 'text' => 'PAGOS 100% SEGUROS CON TARJETA & OXXO', 'active' => true],
            ['id' => 3, 'text' => 'STOCK FÍSICO CON ENTREGA INMEDIATA', 'active' => true],
            ['id' => 4, 'text' => 'ENVÍO GRATIS EN COMPRAS MAYORES A $999', 'active' => true],
            ['id' => 5, 'text' => 'CONCIERGE & ATENCIÓN EXCLUSIVA EN LÍNEA', 'active' => true],
        ]);

        // Cálculo de stock y preparación de variantes
        $variants = $dbProduct->variants;
        $totalStock = (int) $variants->sum(fn ($v) => $v->inventories->sum('quantity'));
        $totalReserved = (int) $variants->sum(fn ($v) => $v->inventories->sum('reserved'));
        $availableStock = max(0, $totalStock - $totalReserved);

        $image = data_get($dbProduct->meta, 'image') ?: (data_get($dbProduct->meta, 'images.0') ?: '/user-avatar.png');
        $images = data_get($dbProduct->meta, 'images') ?: [$image];
        if (!is_array($images) || empty($images)) {
            $images = [$image];
        }

        $colorData = self::resolveProductColorData($dbProduct, $image, $images);
        $supplierName = (string) (
            $dbProduct->supplierProducts->first()?->supplier?->name
            ?? data_get($dbProduct->meta, 'supplier')
            ?? data_get($dbProduct->meta, 'supplier_name')
            ?? $dbProduct->brand
            ?? ''
        );

        $isScrapedProduct = ($dbProduct->type === 'dropship' || $dbProduct->type === 'scraped' || data_get($dbProduct->meta, 'is_scraped') || $dbProduct->supplierProducts->isNotEmpty());

        $variantList = $variants->map(function ($v) use ($isScrapedProduct) {
            $qty = (int) $v->inventories->sum('quantity');
            $res = (int) $v->inventories->sum('reserved');
            $avail = max(0, $qty - $res);
            $variantStockLabel = $isScrapedProduct ? 'Drop Bajo Pedido' : ($avail > 0 ? 'Stock Físico' : 'Bajo Pedido');
            $variantStockType = $isScrapedProduct ? 'drop_on_demand' : ($avail > 0 ? 'ready' : 'on_demand');
            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'size' => strtoupper(trim((string) data_get($v->attributes, 'size', 'M'))),
                'color' => data_get($v->attributes, 'color', 'Único'),
                'price' => (float) $v->price,
                'available' => $avail,
                'stockLabel' => $variantStockLabel,
                'stockType' => $variantStockType,
            ];
        })->values();

        $productPayload = [
            'id' => (string) $dbProduct->id,
            'sku' => $dbProduct->sku,
            'name' => $dbProduct->title,
            'slug' => $dbProduct->slug,
            'brand' => $dbProduct->brand ?: 'TORZO',
            'categoryName' => $dbProduct->brand ?: (data_get($dbProduct->meta, 'category', 'Prenda')),
            'price' => (float) $dbProduct->price,
            'price_formatted' => 'MXN $' . number_format((float) $dbProduct->price, 2),
            'availableStock' => $availableStock,
            'stockType' => $isScrapedProduct ? 'drop_on_demand' : ($availableStock > 0 ? 'ready' : 'on_demand'),
            'stockLabel' => $isScrapedProduct ? 'Drop Bajo Pedido' : ($availableStock > 0 ? 'Stock Físico' : 'Bajo Pedido'),
            'is_scraped' => $isScrapedProduct,
            'image' => $image,
            'images' => $images,
            'colors' => $colorData['colors'],
            'images_by_color' => $colorData['images_by_color'],
            'supplier' => $supplierName,
            'description' => $dbProduct->description ?: 'Prenda anatómica confeccionada con textiles transpirables de alta elasticidad y costuras planas flatlock para confort total.',
            'fabricComposition' => data_get($dbProduct->meta, 'fabric_composition', 'Modal Pima peruano / Spandex / Microfibra transpirable.'),
            'sizes' => $variantList->pluck('size')->unique()->values()->all() ?: ['CH', 'M', 'G', 'XG'],
            'variants' => $variantList->all(),
        ];

        $pwaProducts = $this->getPwaStoreProducts();

        return view('store.pwa.product-detail', [
            'product' => $productPayload,
            'dbProduct' => $dbProduct,
            'activeUniverse' => $activeUniverse,
            'currentStorefront' => $currentStorefront,
            'navbarPages' => $navbarPages,
            'mobilePages' => $mobilePages,
            'footerPages' => $footerPages,
            'tracking' => $tracking,
            'sizeGuideDoc' => $sizeGuideDoc,
            'careProtocolDoc' => $careProtocolDoc,
            'profecoDoc' => $profecoDoc,
            'shippingRule' => $shippingRule,
            'announcements' => $announcements,
            'initialProducts' => $pwaProducts,
            'categories' => \App\Models\Category::all(),
            'categoriesTree' => $this->getCategoriesTree(),
            'suppliers' => $this->getActiveSuppliers(),
            'autoOpenProductId' => null, // PDP dedicado a pantalla completa: cero drawers
        ]);
    }

    /**
     * Catálogo / Feed de artículos de Revista Digital (Redirige a Inicio - Revista Digital Oficial).
     */
    public function magazineIndex(Request $request)
    {
        return redirect()->route('store.home');
    }

    /**
     * Ficha de lectura de Revista Digital (Redirige a Inicio - Revista Digital Oficial).
     */
    public function magazinePost(string $slug)
    {
        return redirect()->route('store.home');
    }

    /**
     * Página institucional CMS sincronizada en el Navbar/Footer.
     */
    public function cmsPage(string $slug)
    {
        $activeUniverse = strtolower((string) request()->query('universo', 'marte'));
        if (! in_array($activeUniverse, ['marte', 'venus', 'tiresias', 'terra'])) {
            $activeUniverse = 'marte';
        }

        $page = CmsPage::active()->where('slug', $slug)->firstOrFail();

        $storefrontConfig = CmsStorefrontConfig::forUniverse($activeUniverse);
        $currentStorefront = [
            'brandName' => $storefrontConfig->brand_name ?? 'TORZO',
            'pageTitle' => ($page->seo_title ?: $page->title) . ' — ' . ($storefrontConfig->brand_name ?? 'TORZO'),
            'primaryColor' => $storefrontConfig->primary_color,
            'accentColor' => $storefrontConfig->accent_color,
            'backgroundColor' => $storefrontConfig->background_color,
            'surfaceColor' => $storefrontConfig->surface_color,
            'textColor' => $storefrontConfig->text_color,
        ];

        $navbarPages = CmsPage::inNavbar()->get();
        $mobilePages = CmsPage::inMobileNavbar()->get();
        $footerPages = CmsPage::inFooter()->get();
        $tracking = CmsTrackingSetting::current();
        $announcements = Cache::get('cms_announcements', [
            ['id' => 1, 'text' => 'ENVÍOS EXPRESS A TODO MÉXICO', 'active' => true],
            ['id' => 2, 'text' => 'PAGOS 100% SEGUROS CON TARJETA & OXXO', 'active' => true],
            ['id' => 3, 'text' => 'STOCK FÍSICO CON ENTREGA INMEDIATA', 'active' => true],
            ['id' => 4, 'text' => 'ENVÍO GRATIS EN COMPRAS MAYORES A $999', 'active' => true],
            ['id' => 5, 'text' => 'CONCIERGE & ATENCIÓN EXCLUSIVA EN LÍNEA', 'active' => true],
        ]);

        return view('store.pwa.page', [
            'page' => $page,
            'activeUniverse' => $activeUniverse,
            'currentStorefront' => $currentStorefront,
            'navbarPages' => $navbarPages,
            'mobilePages' => $mobilePages,
            'footerPages' => $footerPages,
            'announcements' => $announcements,
            'tracking' => $tracking,
        ]);
    }

    /**
     * Mapa del sitio XML dinámico con optimización SEO integral.
     */
    public function sitemap(Request $request)
    {
        $urls = [];

        // 1. Home e Universos
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];
        foreach (['marte', 'venus', 'tiresias', 'terra'] as $u) {
            $urls[] = [
                'loc' => url('/?universo=' . $u),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];
        }

        // 2. Revista digital
        $urls[] = [
            'loc' => url('/revista'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];

        // 3. Artículos de revista publicados
        $posts = CmsBlogPost::published()->get();
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => url('/revista/' . $post->slug),
                'lastmod' => ($post->updated_at ?? $post->created_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        // 4. Páginas institucionales CMS activas
        $pages = CmsPage::active()->get();
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => url('/pagina/' . $page->slug),
                'lastmod' => ($page->updated_at ?? $page->created_at ?? now())->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        // 5. Catálogo de productos visibles
        $products = Product::where('visible', true)->get();
        foreach ($products as $prod) {
            $urls[] = [
                'loc' => url('/producto/' . $prod->slug),
                'lastmod' => ($prod->updated_at ?? $prod->created_at ?? now())->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $item) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $item['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $item['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $item['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
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
        $pendingPacking = Order::query()->whereIn('status', ['processing', 'verified'])->count();
        $mixedReady = Order::query()->whereIn('status', ['processing', 'verified'])
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
                ['label' => 'Pedidos por empacar', 'value' => (string) max(4, $pendingPacking) . ' órdenes', 'trend' => 'Corte 14:00', 'subtitle' => 'Listos para empaque y rotulación'],
                ['label' => 'Carritos mixtos listos', 'value' => (string) max(2, $mixedReady) . ' pedidos', 'trend' => 'Desbloqueados', 'subtitle' => 'Inbound recibido en rampa'],
                ['label' => 'Recepciones en tránsito', 'value' => (string) max(1, $inboundTransit) . ' arribos', 'trend' => 'En camino', 'subtitle' => 'Fleteros con llegada hoy'],
                ['label' => 'Quiebres ROP en anaquel', 'value' => (string) max(3, $ropBreaches) . ' SKUs', 'trend' => 'Reorden', 'subtitle' => 'Stock físico bajo punto crítico'],
            ],
            'sales' => [
                ['label' => 'Leads activos sin responder', 'value' => (string) max(5, $hotLeads) . ' prospectos', 'trend' => 'Prioritario', 'subtitle' => 'Tiempo medio espera: 14 min'],
                ['label' => 'Carritos abandonados hoy', 'value' => (string) max(8, $todayAbandoned) . ' carritos', 'trend' => 'Recuperables', 'subtitle' => 'Gatillo WhatsApp disponible'],
                ['label' => 'Pedidos pausados por dirección', 'value' => (string) max(2, $doubtfulAddresses) . ' envíos', 'trend' => 'Riesgo Postal', 'subtitle' => 'Falta número o CP dudoso'],
                ['label' => 'Cupones activos canjeados', 'value' => '12 canjes', 'trend' => 'Hoy', 'subtitle' => 'Efectividad comercial: 22.4%'],
            ],
        ];

        $initialTab = match ($userRole) {
            'warehouse', 'packer' => 'operative',
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

        $analyticsCharts = $dashboardApi->getAnalyticsCharts($request)->getData(true);
        $systemHealth = $dashboardApi->getSystemHealth($request)->getData(true);

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

    public function portalInventory(Request $request, VariantDimensionService $dimensionService)
    {
        $dimensionService->ensureBootstrapDefaults();
        $portalSizeCurves = $dimensionService->getAllCurves(true);
        $portalFragranceVolumes = $dimensionService->getAllVolumes(true);
        $portalCategories = $dimensionService->getAllCategories(true);

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

        $dbSuppliers = Supplier::query()->withCount('supplierProducts')->with(['users', 'supplierProducts.product'])->orderBy('name')->get();
        $portalProviders = $dbSuppliers->map(function ($s) {
            $hasExtranet = $s->users->where('role', 'supplier')->isNotEmpty();
            return [
                'id' => 'prov_' . $s->id,
                'dbId' => $s->id,
                'vendorCode' => $s->vendor_code ?: ('VND-' . strtoupper(Str::slug($s->name)) . '-001'),
                'name' => $s->name,
                'tradeName' => $s->name,
                'legalName' => $s->legal_name ?: $s->name,
                'taxId' => $s->tax_id ?: '',
                'fiscalRegime' => $s->fiscal_regime ?: '',
                'fiscalAddress' => $s->fiscal_address ?: '',
                'fiscalZip' => $s->fiscal_zip ?: '',
                'invoiceEmail' => $s->invoice_email ?: '',
                'website' => $s->website ?: '',
                'origin' => $s->origin ?: ($s->type === 'dropship_scrape' ? 'scraping' : 'manual'),
                'isManual' => ($s->origin === 'manual' || $s->type !== 'dropship_scrape'),
                'brands' => is_array($s->brands) ? $s->brands : [],
                'brandsTree' => $s->getBrandsTree(),
                'type' => $s->type,
                'typeLabel' => match ($s->type) {
                    'dropship_scrape' => 'Fabricante Mayorista & Scraping B2B',
                    'dropship_api' => 'Proveedor API B2B Integrado',
                    'local' => 'Taller Local & Stock Propio',
                    default => 'Distribuidor Mayorista',
                },
                'city' => $s->city ?: 'CDMX',
                'contactName' => $s->contact_name ?: 'Atencion a Clientes',
                'phone' => $s->phone ?: '+52 55 0000 0000',
                'email' => $s->contact_email ?: ('b2b@' . Str::slug($s->name) . '.com'),
                'extractionMethod' => $s->extraction_method ?: 'Headless Scraper (Puppeteer)',
                'frequency' => 'Automatico (Cada 6 hrs)',
                'lastScrapeTime' => 'Hace 18 min',
                'syncedItemsCount' => (int) $s->supplier_products_count,
                'status' => $s->status ?: 'connected',
                'leadTimeDays' => (int) ($s->lead_time_days ?: 3),
                'paymentTerms' => $s->payment_terms ?: 'contado',
                'slaOnTimePct' => (float) ($s->sla_on_time_pct ?: 97.5),
                'slaFillRatePct' => (float) ($s->sla_fill_rate_pct ?: 98.0),
                'totalOrders' => 12,
                'completedOrders' => 11,
                'avgDelayDays' => (float) ($s->avg_delay_days ?: 0.3),
                'isSyncing' => false,
                'notes' => $s->notes ?: 'Proveedor oficial sincronizado.',
                'hasExtranetAccess' => $hasExtranet,
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
                'poNumber' => $po->po_number,
                'supplier' => $po->supplier?->name ?? 'Proveedor',
                'supplier_id' => $po->supplier_id,
                'carrier' => $po->carrier ?: ($po->notes ?: 'Paquetería Express (Guía Pendiente)'),
                'trackingNumber' => $po->tracking_number,
                'priority' => $po->priority ?: 'media',
                'eta' => $po->estimated_delivery_at ? $po->estimated_delivery_at->format('d M, Y') : 'En preparación',
                'sku' => $firstItem?->product?->sku ?? 'LOTE-COMPRA',
                'productName' => $firstItem?->product?->title ?? 'Lote de Reabastecimiento',
                'qty' => (int) $po->total_units,
                'sizes' => $sizes,
                'unitCost' => (float) ($firstItem?->unit_cost ?? 0),
                'subtotal' => (float) $po->subtotal,
                'tax' => (float) $po->tax,
                'creditDeductionAmount' => (float) ($po->credit_deduction_amount ?? 0),
                'creditAgreementId' => $po->credit_agreement_id,
                'totalCost' => (float) $po->total_amount,
                'date' => $po->created_at ? $po->created_at->format('d M, Y') : now()->format('d M, Y'),
                'status' => $statusLabel,
                'linkedCartId' => null,
                'linkedCustomer' => null,
                'poSnapshot' => $po->po_snapshot,
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

        $portalCreditAgreements = \App\Models\SupplierCreditAgreement::query()
            ->with('supplier')
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn ($ca) => [
                'id' => $ca->id,
                'supplier_id' => $ca->supplier_id,
                'supplier' => $ca->supplier?->name ?? 'Proveedor',
                'ref' => $ca->reference_folio ?: ('NC-' . $ca->id),
                'amount' => (float) $ca->amount,
                'reason' => $ca->resolution_notes ?: 'Acuerdo compensatorio por discrepancia',
                'agreement_type' => $ca->agreement_type,
                'discrepancy_type' => $ca->discrepancy_type,
                'missing_units' => $ca->missing_units_count,
                'damaged_units' => $ca->damaged_units_count,
                'unit_cost' => (float) $ca->unit_cost_applied,
                'authorized_by' => $ca->authorized_by_vendor_contact,
                'minutes' => $ca->discrepancy_minutes,
                'status' => 'Pendiente de Aplicar',
                'date' => $ca->created_at ? $ca->created_at->format('d/m/Y') : now()->format('d/m/Y'),
            ])->values();

        $portalProductBundles = ProductBundle::query()
            ->with(['items.product', 'items.variant.inventories'])
            ->get()
            ->map(function ($bundle) {
                $components = $bundle->items->map(function ($item) {
                    $prod = $item->product;
                    $var = $item->variant;
                    $sku = $var?->sku ?? ($prod?->sku ?? 'SKU-COMP');
                    $name = $prod?->title ?? 'Prenda Componente';
                    $size = $var ? (data_get($var->attributes, 'size') ?? 'm') : 'm';
                    return [
                        'sku' => $sku,
                        'name' => $name,
                        'size' => strtolower((string) $size),
                        'qty' => (int) $item->quantity,
                        'location' => 'A-01-B',
                    ];
                })->values()->toArray();

                $subPrice = (float) $bundle->bundle_price;
                $regPrice = (float) $bundle->regular_price;
                $disc = ($regPrice > $subPrice && $regPrice > 0)
                    ? (int) round((($regPrice - $subPrice) / $regPrice) * 100)
                    : 0;

                return [
                    'id' => (string) ($bundle->slug ?: $bundle->id),
                    'dbId' => $bundle->id,
                    'sku' => (string) ($bundle->slug ?: ('BUNDLE-' . $bundle->id)),
                    'name' => $bundle->name,
                    'type' => $bundle->bundle_type === 'box_subscription' ? 'subscription' : ($bundle->bundle_type === 'pack_multiple' ? 'promo' : $bundle->bundle_type),
                    'tierLevel' => 'custom_pack',
                    'tierName' => $bundle->name,
                    'subscriptionPrice' => $subPrice,
                    'regularPrice' => $regPrice,
                    'discountPct' => $disc,
                    'isActive' => (bool) $bundle->is_active,
                    'badgeColor' => $bundle->bundle_type === 'box_subscription' ? 'indigo' : 'emerald',
                    'description' => 'Receta BOM sincronizada con almacén.',
                    'components' => $components,
                ];
            })->values();

        $portalCycleCounts = CycleCount::query()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($cc) {
                return [
                    'id' => $cc->id,
                    'date' => $cc->count_date ? \Carbon\Carbon::parse($cc->count_date)->format('d/m/Y') : now()->format('d/m/Y'),
                    'skuCount' => (int) $cc->sample_sku_count,
                    'totalCounted' => (int) $cc->total_units_counted,
                    'totalVariance' => (int) $cc->total_variance_units,
                    'iraScore' => (float) $cc->ira_accuracy_score,
                    'auditedBy' => $cc->audited_by,
                    'status' => $cc->status,
                    'details' => $cc->details_json,
                ];
            })->values();

        $portalOnDemandRequests = OrderItem::query()
            ->with(['order.user', 'variant.product.supplierProducts.supplier', 'supplier'])
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['pending', 'processing', 'confirmed', 'paid']);
            })
            ->whereHas('variant.product', function ($q) {
                $q->whereIn('type', ['dropship', 'both']);
            })
            ->where('fulfillment_status', '!=', 'fulfilled')
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($item) {
                $product = $item->variant?->product;
                $order = $item->order;
                $supplier = $item->supplier ?: ($product?->supplierProducts?->first()?->supplier);
                $customerName = $order?->shipping_address['name'] ?? ($order?->user?->name ?? 'Cliente');
                $customerPhone = $order?->shipping_address['phone'] ?? ($order?->user?->phone ?? 'S/T');
                $cartType = ($order?->order_type === 'mixed') ? 'mixto_linea_proveedor' : 'solicitud_pedido';
                $cartLabel = ($order?->order_type === 'mixed') 
                    ? "Carrito Mixto #{$order?->order_number} (Linea On-Demand)"
                    : "Pedido #{$order?->order_number} (Bajo Demanda)";

                $costUnit = (float) ($item->variant?->cost ?? ($product?->cost ?? 0));
                $qty = (int) $item->quantity;

                return [
                    'id' => 'REQ-' . ($order?->order_number ?? $item->id),
                    'order_id' => $order?->id,
                    'customerName' => $customerName,
                    'customerPhone' => $customerPhone,
                    'cartType' => $cartType,
                    'cartLabel' => $cartLabel,
                    'sku' => $product?->sku ?? 'SKU-N/A',
                    'productName' => $product?->title ?? 'Producto Bajo Demanda',
                    'size' => $item->variant?->attributes['size'] ?? ($item->variant?->attributes['volume'] ?? 'M'),
                    'qty' => $qty,
                    'supplier' => $supplier?->name ?? 'Torzo B2B',
                    'costUnit' => $costUnit,
                    'totalCost' => round($qty * $costUnit, 2),
                    'slaMinutes' => 60,
                    'verificationStatus' => 'pendiente',
                    'paymentSent' => false,
                    'date' => $item->created_at ? $item->created_at->diffForHumans() : 'Reciente',
                ];
            })->values();

        $portalBrands = \App\Models\Brand::with('supplier:id,name,legal_name,vendor_code,origin,status')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'supplier_id' => $brand->supplier_id,
                    'supplier_name' => $brand->supplier ? ($brand->supplier->trade_name ?? $brand->supplier->name) : 'Marca Directa / Independiente',
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'description' => $brand->description,
                    'logo_url' => $brand->logo_url,
                    'website' => $brand->website,
                    'is_active' => (bool)$brand->is_active,
                    'is_archived' => (bool)$brand->is_archived,
                    'sort_order' => $brand->sort_order,
                    'sku_count' => \App\Models\Product::where('brand', $brand->name)->count(),
                ];
            });

        $portalScrapedPendingItems = \App\Models\ScrapedPendingItem::query()
            ->with(['supplier', 'supplierProduct'])
            ->where('curation_status', 'pending')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item) {
                $images = is_array($item->images) && count($item->images) ? $item->images : array_values(array_filter([$item->external_image_url]));
                $mainImg = $item->external_image_url ?: ($images[0] ?? 'https://torzo.kexe.mx/imagenes_productos/default.jpg');
                $sizesObj = is_array($item->sizes) ? $item->sizes : ['ch' => 2, 'm' => 4, 'g' => 3, 'xl' => 1];
                $availableSizes = [];
                foreach ($sizesObj as $sz => $qty) {
                    if ($qty > 0) $availableSizes[] = strtoupper($sz);
                }
                if (empty($availableSizes)) $availableSizes = ['CH', 'M', 'G', 'XL'];

                $colorName = $item->detected_color_name ?: ($item->curated_color ?: 'Negro Profundo');
                $imageColorMap = [];
                foreach ($images as $im) {
                    $imageColorMap[$im] = $colorName;
                }

                return [
                    'id' => (string) $item->id,
                    'dbId' => $item->id,
                    'supplierSku' => $item->external_sku,
                    'suggestedSku' => $item->external_sku,
                    'name' => $item->external_title ?: $item->curated_title,
                    'brand' => $item->curated_brand ?: ($item->supplier?->name ?: 'Torzo'),
                    'supplier_id' => $item->supplier_id ?: 4,
                    'suggestedCategory' => $item->suggested_category ?: 'Ropa Interior',
                    'suggestedSubcategory' => $item->suggested_subcategory ?: 'Básicos',
                    'suggestedFabric' => $item->suggested_fabric ?: 'Microfibra Spandex / Licra',
                    'detectedColorName' => $colorName,
                    'detectedColorHex' => $item->detected_color_hex ?: '#0f172a',
                    'colorType' => $item->color_type ?: 'solid',
                    'suggestedDesign' => $item->curated_design_type ?: 'sin_diseno',
                    'suggestedSection' => $item->curated_website_section ?: 'basicos',
                    'scrapedDescription' => $item->scraped_description ?: '',
                    'originUrl' => $item->origin_url ?: 'https://torzo.kexe.mx',
                    'cost' => (float) ($item->scraped_cost ?: ($item->external_price * 0.5)),
                    'scrapedCost' => (float) ($item->scraped_cost ?: ($item->external_price * 0.5)),
                    'suggestedPvp' => (float) ($item->curated_price ?: $item->external_price),
                    'supplierStock' => (int) $item->supplier_stock,
                    'sizes' => $sizesObj,
                    'availableSizes' => $availableSizes,
                    'image' => $mainImg,
                    'images' => $images,
                    'imageColorMap' => $imageColorMap,
                ];
            })->values();

        return view('portal.inventory', [
            'summary' => [
                'stock_local' => number_format($totalLocalStock) . ' piezas',
                'bajo_pedido' => number_format($totalDropshipRefs) . ' referencias',
                'skus_activos' => number_format($totalSkus) . ' modelos',
                'last_scrape' => (string) $lastScrape,
            ],
            'portalProducts' => $portalProducts,
            'portalScrapedPendingItems' => $portalScrapedPendingItems,
            'portalProviders' => $portalProviders,
            'portalBrands' => $portalBrands,
            'portalPurchaseOrders' => $portalPurchaseOrders,
            'portalFuturePurchaseOrders' => $portalFuturePurchaseOrders,
            'portalPurchaseKardex' => $portalPurchaseKardex,
            'portalCreditAgreements' => $portalCreditAgreements,
            'portalProductBundles' => $portalProductBundles,
            'portalCycleCounts' => $portalCycleCounts,
            'portalOnDemandRequests' => $portalOnDemandRequests,
            'portalSizeCurves' => $portalSizeCurves,
            'portalFragranceVolumes' => $portalFragranceVolumes,
            'portalCategories' => $portalCategories,
            'portalFabrics' => \App\Models\ApparelFabric::orderBy('name')->get(),
            'portalApparelColors' => \App\Models\ApparelColor::where('is_active', true)->orderBy('name')->get(),
            'portalStoreSections' => \App\Models\CmsSection::orderBy('sort_order')->orderBy('name')->get(),
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

        // 6. Auditoría de Paqueterías (TASK-INT-08)
        $shippingReconciliations = ShippingReconciliation::with(['items', 'claims'])->latest()->get();
        $shippingClaims = ShippingDiscrepancyClaim::with(['reconciliation', 'items'])->latest()->get();
        $carrierAuditKpis = [
            'total_audited_tracks' => (int) ShippingReconciliation::sum('total_tracks_count'),
            'total_discrepant_tracks' => (int) ShippingReconciliation::sum('discrepant_tracks_count'),
            'total_overcharge_amount' => (float) ShippingReconciliation::sum('total_overcharge_amount'),
            'total_claims_count' => $shippingClaims->count(),
            'total_claimed_amount' => (float) ShippingDiscrepancyClaim::sum('total_disputed_amount'),
        ];

        // 7. Cotejo de 3 Vias Inbound & Motor Fiscal SAT (TASK-INT-07 / TASK-FIN-02)
        $threeWayMatchings = ThreeWayMatching::with('vendorBill')->latest()->get();
        $matchingKpis = [
            'total_inbound_audited' => $threeWayMatchings->count(),
            'matched_count' => $threeWayMatchings->where('match_status', 'matched')->count(),
            'discrepancy_held_count' => $threeWayMatchings->where('match_status', 'discrepancy_held')->count(),
            'total_held_amount' => (float) $threeWayMatchings->where('spei_payout_blocked', true)->sum('invoice_gross_amount'),
        ];
        $taxEngine = app(TaxCalculationEngineService::class);
        $taxSummary = $taxEngine->getPeriodTaxSummary(date('Y-m'));

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
            'shippingReconciliations' => $shippingReconciliations,
            'shippingClaims' => $shippingClaims,
            'carrierAuditKpis' => $carrierAuditKpis,
            'threeWayMatchings' => $threeWayMatchings,
            'matchingKpis' => $matchingKpis,
            'taxSummary' => $taxSummary,
            'movements' => $movements->take(6)->values(),
        ]);
    }

    public function uploadShippingInvoiceReport(Request $request)
    {
        $validated = $request->validate([
            'carrier_id' => 'required|string|in:dhl,dhl_express,skydropx,estafeta,fedex,99minutos,paquetexpress',
            'invoice_folio' => 'required|string|max:100',
            'report_file' => 'nullable|file',
            'raw_csv' => 'nullable|string',
        ]);

        $service = new ShippingInvoiceAuditorService();
        $rows = [];
        $fileName = null;

        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $fileName = $file->getClientOriginalName();
            $content = file_get_contents($file->getRealPath());
            $rows = $service->parseCsvContent($content);
        } elseif (!empty($validated['raw_csv'])) {
            $rows = $service->parseCsvContent($validated['raw_csv']);
        }

        if (empty($rows)) {
            return redirect()->route('portal.finance', ['tab' => 'shipping_audit'])
                ->with('error', 'No se pudieron extraer partidas válidas del reporte de paquetería.');
        }

        $reconciliation = $service->auditCarrierInvoiceReport(
            $validated['carrier_id'],
            $validated['invoice_folio'],
            $rows,
            $request->user() ? $request->user()->name : 'Mickey RevOps',
            $fileName
        );

        return redirect()->route('portal.finance', ['tab' => 'shipping_audit'])
            ->with('success', "Factura {$reconciliation->invoice_folio} auditada contra {$reconciliation->total_tracks_count} guías. {$reconciliation->discrepant_tracks_count} discrepancias detectadas (Sobreprecio: MXN \${$reconciliation->total_overcharge_amount}).");
    }

    public function fileShippingClaim(Request $request, ShippingReconciliation $reconciliation)
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer',
            'claim_notes' => 'nullable|string|max:1000',
        ]);

        $service = new ShippingInvoiceAuditorService();
        $claim = $service->fileCarrierClaimDossier(
            $reconciliation->id,
            $validated['item_ids'],
            $request->user() ? $request->user()->name : 'Mickey RevOps',
            $validated['claim_notes'] ?? null
        );

        return redirect()->route('portal.finance', ['tab' => 'shipping_audit'])
            ->with('success', "Expediente formal {$claim->claim_folio} emitido por MXN \${$claim->total_disputed_amount}. Retención preventiva radicada en Cuentas por Pagar.");
    }

    public function verifyThreeWayMatching(Request $request)
    {
        $validated = $request->validate([
            'po_code' => 'required|string|max:50',
            'supplier_id' => 'required|string|max:50',
            'supplier_name' => 'required|string|max:150',
            'po_qty' => 'required|integer|min:1',
            'po_total_amount' => 'required|numeric|min:0',
            'warehouse_received_qty' => 'required|integer|min:0',
            'warehouse_damaged_qty' => 'nullable|integer|min:0',
            'invoice_number' => 'required|string|max:80',
            'invoice_qty' => 'required|integer|min:1',
            'invoice_gross_amount' => 'required|numeric|min:0',
            'inspected_by' => 'nullable|string|max:100',
        ]);

        $service = app(ThreeWayMatchingService::class);
        $matching = $service->evaluateInboundMatch(
            poCode: $validated['po_code'],
            supplierId: $validated['supplier_id'],
            supplierName: $validated['supplier_name'],
            poQty: (int) $validated['po_qty'],
            poTotal: (float) $validated['po_total_amount'],
            whReceivedQty: (int) $validated['warehouse_received_qty'],
            whDamagedQty: (int) ($validated['warehouse_damaged_qty'] ?? 0),
            invoiceNumber: $validated['invoice_number'],
            invoiceQty: (int) $validated['invoice_qty'],
            invoiceGross: (float) $validated['invoice_gross_amount'],
            inspectedBy: $validated['inspected_by'] ?? ($request->user() ? $request->user()->name : 'Rampa Control Central')
        );

        $msg = $matching->spei_payout_blocked
            ? "Cotejo {$matching->po_code}: Discrepancia detectada. Retención preventiva de pago SPEI activada (Nota de débito sugerida: MXN \${$matching->debit_note_amount})."
            : "Cotejo {$matching->po_code}: 3 Vías conciliadas exitosamente. Pago SPEI habilitado.";

        return redirect()->route('portal.finance', ['tab' => 'expenses'])->with('success', $msg);
    }

    public function issueDebitNoteWeb(Request $request, int $id)
    {
        $validated = $request->validate([
            'debit_note_number' => 'required|string|max:80',
            'debit_amount' => 'nullable|numeric|min:0',
        ]);

        $service = app(ThreeWayMatchingService::class);
        $matching = $service->issueDebitNote(
            matchingId: $id,
            debitNoteNumber: $validated['debit_note_number'],
            customDebitAmount: isset($validated['debit_amount']) ? (float) $validated['debit_amount'] : null
        );

        return redirect()->route('portal.finance', ['tab' => 'expenses'])
            ->with('success', "Nota de débito {$matching->debit_note_number} emitida por MXN \${$matching->debit_note_amount}. Retención SPEI liberada (Monto neto a pagar: MXN \${$matching->payable_net_amount}).");
    }

    public function authorizeSpeiPayoutWeb(Request $request, int $id)
    {
        $paymentRef = $request->input('payment_reference');
        try {
            $service = app(ThreeWayMatchingService::class);
            $matching = $service->authorizeSpeiPayout($id, $paymentRef);

            return redirect()->route('portal.finance', ['tab' => 'expenses'])
                ->with('success', "Dispersión SPEI autorizada para {$matching->po_code} y factura {$matching->invoice_number}.");
        } catch (\DomainException $e) {
            return redirect()->route('portal.finance', ['tab' => 'expenses'])
                ->with('error', $e->getMessage());
        }
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

    public static function detectColorHex(?string $colorName): string
    {
        if (empty($colorName)) {
            return '#0f172a';
        }
        $name = mb_strtolower(trim($colorName), 'UTF-8');
        return match (true) {
            str_contains($name, 'galaxia') || str_contains($name, 'holo') => 'linear-gradient(135deg, #a855f7 0%, #3b82f6 50%, #06b6d4 100%)',
            str_contains($name, 'aurora') || str_contains($name, 'boreal') => 'linear-gradient(135deg, #10b981 0%, #06b6d4 50%, #6366f1 100%)',
            str_contains($name, 'tornasol') => 'linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #3b82f6 100%)',
            str_contains($name, 'negro') || str_contains($name, 'black') => '#0f172a',
            str_contains($name, 'blanco') || str_contains($name, 'white') => '#ffffff',
            str_contains($name, 'azul') || str_contains($name, 'blue') || str_contains($name, 'marino') => '#1e3a8a',
            str_contains($name, 'rojo') || str_contains($name, 'red') || str_contains($name, 'vino') => '#b91c1c',
            str_contains($name, 'verde') || str_contains($name, 'green') || str_contains($name, 'olivo') => '#15803d',
            str_contains($name, 'gris') || str_contains($name, 'gray') || str_contains($name, 'plata') => '#64748b',
            str_contains($name, 'beige') || str_contains($name, 'nude') || str_contains($name, 'arena') => '#d4b996',
            str_contains($name, 'rosa') || str_contains($name, 'pink') => '#f43f5e',
            str_contains($name, 'morado') || str_contains($name, 'purple') => '#7e22ce',
            str_contains($name, 'amarillo') || str_contains($name, 'yellow') => '#eab308',
            default => '#475569',
        };
    }

    public static function resolveProductColorData(Product $product, string $mainImage, array $allImages): array
    {
        $colors = [];
        $imagesByColor = [];
        $variants = $product->variants;

        foreach ($variants as $v) {
            $cName = data_get($v->attributes, 'color');
            if ($cName && trim((string)$cName) !== '' && !in_array(strtolower(trim((string)$cName)), ['único', 'unico'])) {
                $cName = trim((string)$cName);
                if (!isset($colors[$cName])) {
                    $cHex = data_get($v->attributes, 'color_hex') ?: self::detectColorHex($cName);
                    $cType = str_contains(strtolower($cName), 'tornasol') || str_contains(strtolower($cName), 'holo') || str_contains(strtolower($cName), 'aurora') ? 'iridescent' : 'solid';
                    $vImg = data_get($v->attributes, 'image') ?: $mainImage;
                    $colors[$cName] = [
                        'name' => $cName,
                        'hex' => $cHex,
                        'type' => $cType,
                        'image' => $vImg,
                    ];
                    $imagesByColor[$cName] = [$vImg];
                }
            }
        }

        if ($product->meta && isset($product->meta['colors']) && is_array($product->meta['colors'])) {
            foreach ($product->meta['colors'] as $c) {
                $cName = is_array($c) ? ($c['name'] ?? null) : (string)$c;
                if ($cName && !isset($colors[$cName])) {
                    $colors[$cName] = [
                        'name' => $cName,
                        'hex' => is_array($c) && !empty($c['hex']) ? $c['hex'] : self::detectColorHex($cName),
                        'type' => is_array($c) && !empty($c['type']) ? $c['type'] : (str_contains(strtolower($cName), 'tornasol') ? 'iridescent' : 'solid'),
                        'image' => is_array($c) && !empty($c['image']) ? $c['image'] : $mainImage,
                    ];
                    $imagesByColor[$cName] = [$colors[$cName]['image']];
                }
            }
        }

        if ($product->meta && isset($product->meta['images_by_color']) && is_array($product->meta['images_by_color'])) {
            foreach ($product->meta['images_by_color'] as $cName => $cImgs) {
                $imagesByColor[$cName] = (array)$cImgs;
                if (!isset($colors[$cName])) {
                    $colors[$cName] = [
                        'name' => $cName,
                        'hex' => self::detectColorHex($cName),
                        'type' => str_contains(strtolower($cName), 'tornasol') ? 'iridescent' : 'solid',
                        'image' => ((array)$cImgs)[0] ?? $mainImage,
                    ];
                }
            }
        }

        if (empty($colors)) {
            $colors['Negro'] = [
                'name' => 'Negro',
                'hex' => '#0f172a',
                'type' => 'solid',
                'image' => $mainImage,
            ];
            $imagesByColor['Negro'] = array_slice($allImages, 0, max(1, count($allImages)));
        }

        return [
            'colors' => array_values($colors),
            'images_by_color' => $imagesByColor,
        ];
    }

    public function getCategoriesTree()
    {
        return \App\Models\Category::with('children')->whereNull('parent_id')->orderBy('name')->get();
    }

    public function getActiveSuppliers(): array
    {
        $fromDb = \App\Models\Supplier::query()->where('status', '!=', 'inactive')->pluck('name')->toArray();
        $fromProducts = \App\Models\Product::query()
            ->whereNotNull('meta')
            ->get()
            ->map(fn($p) => data_get($p->meta, 'supplier') ?: data_get($p->meta, 'supplier_name'))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        $merged = array_values(array_unique(array_filter(array_merge($fromDb, $fromProducts))));
        sort($merged);
        if (empty($merged)) {
            $merged = ['Torzo B2B', 'ModaIntima Textil MX', 'Silk & Cotton Iberia SL', 'Lingerie Drop Global', 'Seamless Tech Apparel'];
        }
        return $merged;
    }

    public function getPwaStoreProducts(): array
    {
        return Product::query()
            ->with([
                'variants.inventories',
                'categories',
                'supplierProducts.supplier',
            ])
            ->where('visible', true)
            ->orderBy('id')
            ->get()
            ->map(function (Product $product): array {
                $variants = $product->variants;
                $totalStock = (int) $variants->sum(fn ($v) => $v->inventories->sum('quantity'));
                $totalReserved = (int) $variants->sum(fn ($v) => $v->inventories->sum('reserved'));
                $availableStock = max(0, $totalStock - $totalReserved);

                $brandUpper = strtoupper((string) ($product->brand ?: data_get($product->meta, 'category', '')));
                $categorySlug = 'jocks';
                if (str_contains($brandUpper, 'BOXER') || str_contains($brandUpper, 'BRIEF')) {
                    $categorySlug = 'boxers';
                } elseif (str_contains($brandUpper, 'TANGA')) {
                    $categorySlug = 'tangas';
                } elseif (str_contains($brandUpper, 'BIKINI')) {
                    $categorySlug = 'bikinis';
                } elseif (str_contains($brandUpper, 'TANK') || str_contains($brandUpper, 'SHIRT')) {
                    $categorySlug = 'tanktops';
                } elseif (str_contains($brandUpper, 'SWIM')) {
                    $categorySlug = 'swimwear';
                }

                $universes = ['marte', 'terra'];
                if (in_array($categorySlug, ['bikinis', 'swimwear'])) {
                    $universes = ['marte', 'venus', 'terra'];
                } elseif ($categorySlug === 'tanktops') {
                    $universes = ['marte', 'tiresias', 'terra'];
                }
                if ($product->meta && isset($product->meta['universes']) && is_array($product->meta['universes'])) {
                    $universes = $product->meta['universes'];
                }

                $sizes = [];
                foreach ($variants as $v) {
                    $sz = strtoupper(trim((string) data_get($v->attributes, 'size', 'M')));
                    if ($sz !== '' && !in_array($sz, $sizes)) {
                        $sizes[] = $sz;
                    }
                }
                if (empty($sizes)) {
                    $sizes = ['CH', 'M', 'G', 'XG'];
                }

                $image = data_get($product->meta, 'image') ?: (data_get($product->meta, 'images.0') ?: '/user-avatar.png');
                $image = str_replace('/./', '/', $image);
                $allImages = data_get($product->meta, 'images') ?: [$image];
                if (!is_array($allImages) || empty($allImages)) {
                    $allImages = [$image];
                }
                $colorData = self::resolveProductColorData($product, $image, $allImages);
                $supplierName = (string) (
                    $product->supplierProducts->first()?->supplier?->name
                    ?? data_get($product->meta, 'supplier')
                    ?? data_get($product->meta, 'supplier_name')
                    ?? $product->brand
                    ?? ''
                );

                $isScrapedProduct = ($product->type === 'dropship' || $product->type === 'scraped' || data_get($product->meta, 'is_scraped') || $product->supplierProducts->isNotEmpty());

                return [
                    'id' => (string) $product->id,
                    'sku' => $product->sku,
                    'name' => $product->title,
                    'slug' => $product->slug,
                    'category' => $categorySlug,
                    'categoryName' => $product->brand ?: (data_get($product->meta, 'category', 'Prenda')),
                    'price' => (float) $product->price,
                    'price_formatted' => 'MXN $' . number_format((float) $product->price, 2),
                    'universes' => $universes,
                    'stockType' => $isScrapedProduct ? 'drop_on_demand' : ($availableStock > 0 ? 'ready' : 'on_demand'),
                    'stockLabel' => $isScrapedProduct ? 'Drop Bajo Pedido' : ($availableStock > 0 ? 'Stock Físico' : 'Bajo Pedido'),
                    'is_scraped' => $isScrapedProduct,
                    'availableStock' => $availableStock,
                    'image' => $image,
                    'images' => $allImages,
                    'colors' => $colorData['colors'],
                    'images_by_color' => $colorData['images_by_color'],
                    'supplier' => $supplierName,
                    'sizes' => $sizes,
                    'description' => $product->description ?: 'Prenda anatómica confeccionada con textiles transpirables.',
                    'brand' => (string) ($product->brand ?? ''),
                    'garment_type' => (string) data_get($product->meta, 'garment_type', ''),
                    'fabric_type' => (string) data_get($product->meta, 'fabric_type', ''),
                    'discount_pct' => (float) data_get($product->meta, 'discount_pct', 0),
                    'category_ids' => $product->categories->pluck('id')->all(),
                    'variants' => $variants->map(fn ($v) => [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'size' => strtoupper(trim((string) data_get($v->attributes, 'size', 'M'))),
                        'color' => data_get($v->attributes, 'color', 'Unico'),
                        'price' => (float) $v->price,
                        'available' => max(0, $v->inventories->sum('quantity') - $v->inventories->sum('reserved')),
                        'stockType' => $isScrapedProduct ? 'drop_on_demand' : (max(0, $v->inventories->sum('quantity') - $v->inventories->sum('reserved')) > 0 ? 'ready' : 'on_demand'),
                        'stockLabel' => $isScrapedProduct ? 'Drop Bajo Pedido' : (max(0, $v->inventories->sum('quantity') - $v->inventories->sum('reserved')) > 0 ? 'Stock Físico' : 'Bajo Pedido'),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
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

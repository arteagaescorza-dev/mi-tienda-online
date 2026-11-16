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
            'typography_heading' => $storefrontConfig->typography_heading ?? 'Plus Jakarta Sans',
            'typography_subtitle' => $storefrontConfig->typography_subtitle ?? 'Outfit',
            'typography_body' => $storefrontConfig->typography_body ?? 'Plus Jakarta Sans',
            'typography_accent' => $storefrontConfig->typography_accent ?? 'Outfit',
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

        $annDoc = CmsOfficialDocument::getByCode('STORE_ANNOUNCEMENTS_CONFIG');
        $announcements = Cache::get('cms_announcements');
        if (!is_array($announcements) || empty($announcements)) {
            $announcements = (is_array($annDoc) && !empty($annDoc['items']) && is_array($annDoc['items']))
                ? $annDoc['items']
                : [
                    ['id' => 1, 'text' => 'ENVÍOS EXPRESS A TODO MÉXICO', 'active' => true],
                    ['id' => 2, 'text' => 'TARJETAS BANCARIAS & OXXO PAY SIN FRICCIÓN', 'active' => true],
                    ['id' => 3, 'text' => 'EMPAQUE 100% DISCRETO', 'active' => true],
                    ['id' => 4, 'text' => 'PAGOS 100% SEGUROS CON TARJETA & OXXO', 'active' => true],
                    ['id' => 5, 'text' => 'STOCK FÍSICO CON ENTREGA INMEDIATA', 'active' => true],
                    ['id' => 6, 'text' => 'ENVÍO GRATIS EN COMPRAS MAYORES A $999', 'active' => true],
                    ['id' => 7, 'text' => 'CONCIERGE & ATENCIÓN EXCLUSIVA EN LÍNEA', 'active' => true],
                ];
        }

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
            if (in_array($section->type, ['curated_products', 'categories_grid', 'subcategories_slider', 'family_showcase', 'tags_cloud', 'catalog_section'])) {
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

        // Dimensiones Dinámicas del Catálogo Oficial
        $dbColors = \Illuminate\Support\Facades\DB::table('apparel_colors')
            ->where('is_active', true)
            ->get();

        $catalogColors = $dbColors->map(function ($c) {
            $isGrad = in_array($c->color_type ?? '', ['gradient', 'iridescent']) || !empty($c->gradient_css);
            return [
                'key' => strtolower($c->name),
                'name' => $c->name,
                'hex' => $c->hex_code ?: '#0f172a',
                'grad' => $isGrad ? $c->gradient_css : null,
                'type' => $c->color_type ?: 'solid',
                'border' => in_array(strtolower($c->name), ['blanco', 'white', 'hueso', 'perla']) || in_array(strtolower($c->hex_code ?? ''), ['#ffffff', '#fff', '#f8fafc', '#fafafa']),
            ];
        })->values()->all();

        if (empty($catalogColors)) {
            $catalogColors = collect($pwaProducts)
                ->pluck('colors')
                ->flatten(1)
                ->unique('name')
                ->values()
                ->map(fn($c) => [
                    'key' => strtolower($c['name']),
                    'name' => $c['name'],
                    'hex' => $c['hex'] ?? '#0f172a',
                    'grad' => str_contains($c['hex'] ?? '', 'gradient') ? $c['hex'] : null,
                    'type' => $c['type'] ?? 'solid',
                    'border' => false,
                ])->all();
        }

        $catalogBrands = collect($pwaProducts)
            ->pluck('brand')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $catalogFabrics = collect($pwaProducts)
            ->pluck('fabric_type')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (empty($catalogFabrics)) {
            $catalogFabrics = \App\Models\Product::where('visible', true)
                ->pluck('fabric')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();
        }

        $catalogSizes = collect($pwaProducts)
            ->pluck('sizes')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($catalogSizes)) {
            $catalogSizes = \App\Models\ProductVariant::where('visible', true)
                ->get()
                ->map(fn($v) => data_get($v->attributes, 'size'))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }
        $sizeOrder = ['CH' => 1, 'S' => 2, 'M' => 3, 'G' => 4, 'L' => 5, 'XL' => 6, 'XG' => 7, '2XL' => 8, 'XXL' => 9, '3XL' => 10, 'UNI' => 99];
        usort($catalogSizes, fn($a, $b) => ($sizeOrder[$a] ?? 50) <=> ($sizeOrder[$b] ?? 50));

        $catalogGarmentTypes = collect($pwaProducts)
            ->pluck('garment_type')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($catalogGarmentTypes)) {
            $catalogGarmentTypes = \App\Models\Category::whereNotNull('parent_id')
                ->get()
                ->map(fn($c) => ['key' => $c->slug, 'name' => $c->name])
                ->all();
        }

        $selectedCat = $request->query('categoria') ?: $request->query('category');
        $catalogProducts = $pwaProducts;
        if (!empty($selectedCat) && $selectedCat !== 'all') {
            $normCat = preg_replace('/(s|es)$/i', '', strtolower(trim((string)$selectedCat)));
            $catalogProducts = array_values(array_filter($pwaProducts, function ($p) use ($normCat, $selectedCat) {
                $pCat = strtolower(trim((string)($p['category'] ?? '')));
                $pCatName = strtolower(trim((string)($p['categoryName'] ?? '')));
                $pName = strtolower(trim((string)($p['name'] ?? '')));
                $normP = preg_replace('/(s|es)$/i', '', $pCat);
                return $pCat === strtolower($selectedCat)
                    || $normP === $normCat
                    || str_contains($pCat, $normCat)
                    || str_contains($pCatName, $normCat)
                    || str_contains($pName, $normCat);
            }));
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
            'products' => $catalogProducts,
            'initialProducts' => $pwaProducts,
            'categories' => \App\Models\Category::all(),
            'categoriesTree' => $this->getCategoriesTree(),
            'suppliers' => $this->getActiveSuppliers(),
            'catalogFilters' => \App\Models\CmsCatalogFilter::orderBy('sort_order')->get(),
            'catalogColors' => $catalogColors,
            'catalogBrands' => $catalogBrands,
            'catalogFabrics' => $catalogFabrics,
            'catalogSizes' => $catalogSizes,
            'catalogGarmentTypes' => $catalogGarmentTypes,
            'maxCatalogPrice' => (int) (ceil(max(1000, (float) \App\Models\Product::where('visible', true)->max('price')) / 100) * 100),
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

        $filtered = array_filter($allProducts, function ($p) use ($rules, $section) {
            // Section / Vitrina filter
            $sectionTarget = $rules['section'] ?? $rules['slug'] ?? ($section->type === 'catalog_section' ? $section->slug : null);
            if (!empty($sectionTarget) && $sectionTarget !== 'all') {
                $targetSlug = \Illuminate\Support\Str::slug((string) $sectionTarget);
                $ruleSlug = !empty($rules['slug']) ? \Illuminate\Support\Str::slug((string) $rules['slug']) : null;
                $pSec = \Illuminate\Support\Str::slug((string) ($p['section_name'] ?? ''));
                $pName = strtolower(trim((string) ($p['name'] ?? '')));
                $pBrand = strtolower(trim((string) ($p['brand'] ?? '')));
                $targetLower = strtolower(trim((string) $sectionTarget));

                $matchesSection = !empty($pSec) && (
                    $pSec === $targetSlug 
                    || ($ruleSlug && $pSec === $ruleSlug) 
                    || str_contains($pSec, $targetSlug) 
                    || str_contains($targetSlug, $pSec)
                );
                $matchesKeyword = str_contains($pName, $targetLower) || str_contains($pBrand, $targetLower);

                if (!$matchesSection && !$matchesKeyword) {
                    return false;
                }
            }

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
            ->with(['variants.inventories', 'categories', 'supplierProducts.supplier', 'fabricRelation'])
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
        $profecoDoc = CmsOfficialDocument::forDocument('profeco_sanitary');

        // 1. Resolución estricta de Curva de Tallas Oficial de Marca / Proveedor (Hub 4)
        // Regla: Imposible que una curva no esté asociada a una marca/proveedor. Si no existe, es null (sin fallback genérico).
        $brandSizeCurve = null;
        if (!empty($dbProduct->brand)) {
            $brandModel = \App\Models\Brand::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($dbProduct->brand))])->first();
            if ($brandModel) {
                $brandSizeCurve = \App\Models\ApparelSizeCurve::with('activeSizes')
                    ->where('brand_id', $brandModel->id)
                    ->where('is_active', true)
                    ->first();
                if (!$brandSizeCurve && $brandModel->supplier_id) {
                    $brandSizeCurve = \App\Models\ApparelSizeCurve::with('activeSizes')
                        ->where('supplier_id', $brandModel->supplier_id)
                        ->whereNull('brand_id')
                        ->where('is_active', true)
                        ->first();
                }
            }
        }

        if (!$brandSizeCurve) {
            $supplierProduct = $dbProduct->supplierProducts->first();
            if ($supplierProduct && $supplierProduct->supplier_id) {
                $brandSizeCurve = \App\Models\ApparelSizeCurve::with('activeSizes')
                    ->where('supplier_id', $supplierProduct->supplier_id)
                    ->whereNull('brand_id')
                    ->where('is_active', true)
                    ->first();
            }
        }

        if (!$brandSizeCurve) {
            $metaSupplierName = data_get($dbProduct->meta, 'supplier') ?? data_get($dbProduct->meta, 'supplier_name');
            if ($metaSupplierName) {
                $supModel = \App\Models\Supplier::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($metaSupplierName))])->first();
                if ($supModel) {
                    $brandSizeCurve = \App\Models\ApparelSizeCurve::with('activeSizes')
                        ->where('supplier_id', $supModel->id)
                        ->whereNull('brand_id')
                        ->where('is_active', true)
                        ->first();
                }
            }
        }

        // 2. Resolución de Protocolo de Cuidado Textil sincronizado con Fibras de Hub 4
        $careProtocol = null;
        $careDocs = CmsOfficialDocument::forDocument('care_protocol');
        $allCareProtocols = is_array($careDocs?->payload) ? array_values($careDocs->payload) : [];
        $productFabricName = $dbProduct->fabricRelation?->name ?? $dbProduct->fabric ?? null;

        if (!empty($allCareProtocols)) {
            if ($productFabricName) {
                $cleanProductFabric = strtolower(trim($productFabricName));
                foreach ($allCareProtocols as $proto) {
                    $pf = strtolower(trim($proto['fiber'] ?? ''));
                    if ($pf !== '' && (str_contains($cleanProductFabric, $pf) || str_contains($pf, $cleanProductFabric))) {
                        $careProtocol = $proto;
                        break;
                    }
                }
            }

            if (!$careProtocol) {
                $metaComp = strtolower(data_get($dbProduct->meta, 'fabric_composition', '') . ' ' . ($dbProduct->description ?? ''));
                foreach ($allCareProtocols as $proto) {
                    $pf = strtolower(trim($proto['fiber'] ?? ''));
                    if ($pf !== '' && str_contains($metaComp, $pf)) {
                        $careProtocol = $proto;
                        break;
                    }
                }
            }

            if (!$careProtocol && !empty($allCareProtocols[0])) {
                $careProtocol = $allCareProtocols[0];
            }
        }

        if (!$careProtocol && $productFabricName) {
            $careProtocol = [
                'fiber' => $productFabricName,
                'rule' => 'Lavar con agua fría (< 30°C) en ciclo delicado.',
                'warning' => 'Secar a la sombra en superficie horizontal plana. Evitar calor directo.',
                'symbols' => ['wash_30', 'no_bleach', 'dry_shade', 'no_tumble'],
            ];
        }

        $standardCareSymbols = CmsOfficialDocument::getStandardCareSymbols();

        $shippingRule = Cache::get('cms_shipping_rule', [
            'freeShippingThreshold' => 999.00,
            'flatRate' => 149.00,
            'status' => 'active',
        ]);

        $annDoc = CmsOfficialDocument::getByCode('STORE_ANNOUNCEMENTS_CONFIG');
        $announcements = Cache::get('cms_announcements');
        if (!is_array($announcements) || empty($announcements)) {
            $announcements = (is_array($annDoc) && !empty($annDoc['items']) && is_array($annDoc['items']))
                ? $annDoc['items']
                : [
                    ['id' => 1, 'text' => 'ENVÍOS EXPRESS A TODO MÉXICO', 'active' => true],
                    ['id' => 2, 'text' => 'PAGOS 100% SEGUROS CON TARJETA & OXXO', 'active' => true],
                    ['id' => 3, 'text' => 'STOCK FÍSICO CON ENTREGA INMEDIATA', 'active' => true],
                    ['id' => 4, 'text' => 'ENVÍO GRATIS EN COMPRAS MAYORES A $999', 'active' => true],
                    ['id' => 5, 'text' => 'CONCIERGE & ATENCIÓN EXCLUSIVA EN LÍNEA', 'active' => true],
                ];
        }

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

        $isScrapedProduct = $dbProduct->isScraped();

        $variantList = $variants->map(function ($v) use ($isScrapedProduct) {
            $qty = (int) $v->inventories->sum('quantity');
            $res = (int) $v->inventories->sum('reserved');
            $avail = max(0, $qty - $res);
            $variantStockLabel = $isScrapedProduct ? 'Bajo Pedido' : ($avail > 0 ? 'Stock Físico' : 'Bajo Pedido');
            $variantStockType = $isScrapedProduct ? 'on_demand' : ($avail > 0 ? 'ready' : 'on_demand');
            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'size' => strtoupper(trim((string) data_get($v->attributes, 'size', 'M'))),
                'color' => data_get($v->attributes, 'color', 'Único'),
                'price' => (float) $v->price,
                'available' => $isScrapedProduct ? 0 : $avail,
                'stockLabel' => $variantStockLabel,
                'stockType' => $variantStockType,
            ];
        })->values();

        $pwaProducts = $this->getPwaStoreProducts();
        $matchedPwa = collect($pwaProducts)->firstWhere('id', (string) $dbProduct->id);

        $productPayload = [
            'id' => (string) $dbProduct->id,
            'sku' => $dbProduct->sku,
            'name' => $dbProduct->title,
            'slug' => $dbProduct->slug,
            'brand' => $dbProduct->brand ?: 'TORZO',
            'category' => $matchedPwa['category'] ?? ($dbProduct->categories->first()?->slug ?? \Illuminate\Support\Str::slug((string) data_get($dbProduct->meta, 'category', 'general'))),
            'categoryName' => $matchedPwa['categoryName'] ?? ($dbProduct->brand ?: (data_get($dbProduct->meta, 'category', 'Prenda'))),
            'garment_type' => (string) data_get($dbProduct->meta, 'garment_type', ''),
            'section_name' => (string) ($dbProduct->section_name ?? data_get($dbProduct->meta, 'section', '')),
            'abc_class' => $matchedPwa['abc_class'] ?? strtoupper((string) data_get($dbProduct->meta, 'abc_class', 'C')),
            'discount_pct' => (float) data_get($dbProduct->meta, 'discount_pct', 0),
            'compare_at_price' => (float) ($dbProduct->compare_at_price ?? data_get($dbProduct->meta, 'compare_at_price', 0)),
            'price' => (float) $dbProduct->price,
            'price_formatted' => 'MXN $' . number_format((float) $dbProduct->price, 2),
            'availableStock' => $isScrapedProduct ? 0 : $availableStock,
            'stockType' => $isScrapedProduct ? 'on_demand' : ($availableStock > 0 ? 'ready' : 'on_demand'),
            'stockLabel' => $isScrapedProduct ? 'Bajo Pedido' : ($availableStock > 0 ? 'Stock Físico' : 'Bajo Pedido'),
            'is_scraped' => $isScrapedProduct,
            'image' => $image,
            'images' => $images,
            'colors' => $colorData['colors'],
            'images_by_color' => $colorData['images_by_color'],
            'supplier' => $supplierName,
            'description' => $dbProduct->description ?: 'Prenda anatómica confeccionada con textiles transpirables de alta elasticidad y costuras planas flatlock para confort total.',
            'fabricComposition' => $dbProduct->fabricRelation?->name
                ?? data_get($dbProduct->meta, 'fabric_composition')
                ?? data_get($dbProduct->meta, 'fabric')
                ?? $dbProduct->fabric
                ?? 'Modal Pima peruano / Spandex / Microfibra transpirable.',
            'sizes' => $variantList->pluck('size')->unique()->values()->all() ?: ['CH', 'M', 'G', 'XG'],
            'variants' => $variantList->all(),
        ];

        return view('store.pwa.product-detail', [
            'product' => $productPayload,
            'dbProduct' => $dbProduct,
            'activeUniverse' => $activeUniverse,
            'currentStorefront' => $currentStorefront,
            'navbarPages' => $navbarPages,
            'mobilePages' => $mobilePages,
            'footerPages' => $footerPages,
            'tracking' => $tracking,
            'brandSizeCurve' => $brandSizeCurve,
            'careProtocol' => $careProtocol,
            'standardCareSymbols' => $standardCareSymbols,
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
     * Dispatcher reactivo de catálogo filtrado (/catalogo).
     */
    public function catalog(Request $request)
    {
        $query = $request->query();
        $query['tab'] = 'catalog';
        return redirect()->route('store.home', $query);
    }

    /**
     * Página institucional CMS sincronizada en el Navbar/Footer o Repositorio de Convenios/Catálogo Filtrado.
     */
    public function cmsPage(string $slug)
    {
        $activeUniverse = strtolower((string) request()->query('universo', 'marte'));
        if (! in_array($activeUniverse, ['marte', 'venus', 'tiresias', 'terra'])) {
            $activeUniverse = 'marte';
        }

        $page = CmsPage::active()->where('slug', $slug)->firstOrFail();

        // Si la página fue configurada con filtros de catálogo (ej. Tanga, Boxers, etc.), guiar reactivamente al catálogo filtrado
        if ($page->hasCatalogFilters()) {
            return redirect()->route('store.home', $page->getCatalogParams($activeUniverse));
        }

        $isAlliesPage = in_array($page->slug, ['aliados', 'convenios', 'convenios-con-aliados']) || str_contains(strtolower($page->title), 'aliados') || str_contains(strtolower($page->title), 'convenios');
        $allyCampaigns = collect();
        if ($isAlliesPage) {
            $allyCampaigns = \App\Models\AllyCampaignDirA::with('ally')
                ->where('status', 'active')
                ->orderByRaw('COALESCE(start_date, created_at) DESC')
                ->get();
        }

        $isFaqPage = in_array($page->slug, ['q&a', 'faq', 'preguntas-frecuentes']) || str_contains(strtolower($page->title), 'preguntas frecuentes') || str_contains(strtolower($page->title), 'q&a');
        $faqs = collect();
        $faqJsonLd = null;
        if ($isFaqPage) {
            $faqs = \App\Models\CmsFaq::active()->get();
            $faqJsonLd = \App\Models\CmsFaq::buildFaqPageSchema($faqs);
        }

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
        $annDoc = CmsOfficialDocument::getByCode('STORE_ANNOUNCEMENTS_CONFIG');
        $announcements = Cache::get('cms_announcements');
        if (!is_array($announcements) || empty($announcements)) {
            $announcements = (is_array($annDoc) && !empty($annDoc['items']) && is_array($annDoc['items']))
                ? $annDoc['items']
                : [
                    ['id' => 1, 'text' => 'ENVÍOS EXPRESS A TODO MÉXICO', 'active' => true],
                    ['id' => 2, 'text' => 'PAGOS 100% SEGUROS CON TARJETA & OXXO', 'active' => true],
                    ['id' => 3, 'text' => 'STOCK FÍSICO CON ENTREGA INMEDIATA', 'active' => true],
                    ['id' => 4, 'text' => 'ENVÍO GRATIS EN COMPRAS MAYORES A $999', 'active' => true],
                    ['id' => 5, 'text' => 'CONCIERGE & ATENCIÓN EXCLUSIVA EN LÍNEA', 'active' => true],
                ];
        }

        return view('store.pwa.page', [
            'page' => $page,
            'isAlliesPage' => $isAlliesPage,
            'allyCampaigns' => $allyCampaigns,
            'isFaqPage' => $isFaqPage,
            'faqs' => $faqs,
            'faqJsonLd' => $faqJsonLd,
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
            ->where('is_archived', false)
            ->where('visible', true)
            ->where(function ($q) {
                $q->whereNull('meta->approval_status')
                  ->orWhere('meta->approval_status', 'approved');
            })
            ->orderBy('id');

        $draftProductsQuery = Product::query()
            ->with([
                'variants.inventories',
                'supplierProducts.supplier',
            ])
            ->where('is_archived', false)
            ->where('meta->approval_status', 'draft')
            ->orderByDesc('id');

        $portalDraftProducts = $draftProductsQuery->get()->map(function (Product $product) {
            $mainImage = data_get($product->meta, 'image')
                ?: (data_get($product->meta, 'images.0') ?: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80');
            $variants = $product->variants;
            $isScrapedProduct = $product->isScraped() || $product->type === 'dropship';
            $totalStock = $isScrapedProduct ? 0 : (int) $variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('quantity'));
            return [
                'id' => $product->id,
                'dbId' => $product->id,
                'sku' => $product->sku,
                'name' => $product->title,
                'brand' => $product->brand ?: 'ModaIntima Textil MX',
                'category' => data_get($product->meta, 'category', 'Boxers'),
                'subcategory' => data_get($product->meta, 'subcategory', ''),
                'fabricType' => data_get($product->meta, 'fabric_type', $product->fabric),
                'colorName' => data_get($product->meta, 'color_name', 'Negro'),
                'colorHex' => data_get($product->meta, 'color_hex', '#0f172a'),
                'colorType' => data_get($product->meta, 'color_type', 'solid'),
                'designType' => data_get($product->meta, 'design_type', 'sin_diseno'),
                'websiteSection' => data_get($product->meta, 'website_section', 'basicos'),
                'description' => $product->description,
                'pvp' => (float)$product->price,
                'cost' => (float)$product->cost,
                'image' => $mainImage,
                'images' => data_get($product->meta, 'gallery', []),
                'localStock' => $totalStock,
                'approvalStatus' => 'draft',
                'isApproved' => false,
                'createdAt' => $product->created_at ? $product->created_at->format('d/m/Y H:i') : 'Reciente',
            ];
        })->values();

        $archivedProductsQuery = Product::query()
            ->with([
                'variants.inventories',
                'supplierProducts.supplier',
            ])
            ->where(function ($q) {
                $q->where('is_archived', true)
                  ->orWhere(function ($sq) {
                      $sq->where('visible', false)
                         ->where(function ($ssq) {
                             $ssq->whereNull('meta->approval_status')
                                ->orWhere('meta->approval_status', '!=', 'draft');
                         });
                  });
            })
            ->orderByDesc('id');

        $portalArchivedProducts = $archivedProductsQuery->get()->map(function (Product $product) {
            $mainImage = data_get($product->meta, 'image')
                ?: (data_get($product->meta, 'images.0') ?: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80');
            return [
                'id' => $product->id,
                'dbId' => $product->id,
                'sku' => $product->sku,
                'name' => $product->title,
                'brand' => $product->brand ?: 'Torzo',
                'category' => data_get($product->meta, 'category', 'Ropa Interior'),
                'price' => (float)$product->price,
                'cost' => (float)$product->cost,
                'image' => $mainImage,
                'isArchived' => (bool)$product->is_archived,
                'isVisible' => (bool)$product->visible,
                'discontinuedReason' => $product->discontinued_reason ?: ($product->is_archived ? 'Archivado por operador' : 'Retirado de vitrina'),
                'archivedAt' => $product->archived_at ? $product->archived_at->format('d/m/Y H:i') : ($product->updated_at ? $product->updated_at->format('d/m/Y H:i') : 'Reciente'),
            ];
        })->values();

        $dbSuppliers = Supplier::query()->withCount('supplierProducts')->with(['users', 'supplierProducts.product'])->orderBy('name')->get();
        $brandToSupplierMap = [];
        foreach ($dbSuppliers as $s) {
            $brandToSupplierMap[mb_strtolower(trim($s->name))] = $s;
            if (is_array($s->brands)) {
                foreach ($s->brands as $b) {
                    $brandToSupplierMap[mb_strtolower(trim($b))] = $s;
                }
            }
        }

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
                'scrape_frequency' => $s->scrape_frequency ?: 'every_3_days',
                'frequency' => $s->scrape_frequency ?: 'every_3_days',
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

        $dbProducts = $productsQuery->get();

        $allVariantIds = $dbProducts->pluck('variants')->flatten()->pluck('id')->filter()->unique()->all();
        $allActiveLocks = !empty($allVariantIds)
            ? \App\Models\StockLock::whereIn('product_variant_id', $allVariantIds)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->get()
                ->groupBy('product_variant_id')
            : collect();

        $portalProducts = $dbProducts->map(function (Product $product) use ($brandToSupplierMap, $allActiveLocks) {
            $variants = $product->variants;
            $hasVariants = $variants->isNotEmpty();
            $isScrapedProduct = $product->isScraped() || $product->type === 'dropship';

            if ($hasVariants) {
                $totalStock = $isScrapedProduct ? 0 : (int) $variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('quantity'));
                $totalReserved = $isScrapedProduct ? 0 : (int) $variants->sum(fn ($v) => $v->inventories->where('location', 'warehouse-1')->sum('reserved'));

                $sizes = [];
                $physicalSizes = [];
                $committedSizes = [];
                $lockedSizes = [];
                $totalLocked = 0;

                foreach ($variants as $v) {
                    $rawSize = data_get($v->attributes, 'size', 'u');
                    $sizeKey = strtolower(trim((string) $rawSize));
                    $vQty = $isScrapedProduct ? 0 : (int) $v->inventories->where('location', 'warehouse-1')->sum('quantity');
                    $vRes = $isScrapedProduct ? 0 : (int) $v->inventories->where('location', 'warehouse-1')->sum('reserved');
                    $vLocks = $isScrapedProduct ? 0 : (int) ($allActiveLocks->get($v->id)?->sum('quantity') ?? 0);
                    $totalLocked += $vLocks;

                    $physicalSizes[$sizeKey] = $vQty;
                    $committedSizes[$sizeKey] = $vRes;
                    $lockedSizes[$sizeKey] = $vLocks;
                    $sizes[$sizeKey] = max(0, $vQty - $vRes - $vLocks);
                }

                $availableStock = $isScrapedProduct ? 0 : max(0, $totalStock - $totalReserved - $totalLocked);
            } else {
                $rawMetaSizes = data_get($product->meta, 'sizes', []);
                $sizes = [];
                if (is_array($rawMetaSizes)) {
                    foreach ($rawMetaSizes as $k => $v) {
                        $sizes[strtolower(trim((string) $k))] = $isScrapedProduct ? 0 : (int) $v;
                    }
                }
                $physicalSizes = $sizes;
                $committedSizes = [];
                $lockedSizes = [];
                $totalReserved = 0;
                $totalLocked = 0;
                $totalStock = $isScrapedProduct ? 0 : (!empty($sizes) ? array_sum($sizes) : (int) data_get($product->meta, 'local_stock', 0));
                $availableStock = $totalStock;
            }

            $sp = $product->supplierProducts->first();
            $supplierStock = $sp && $sp->availability === 'in_stock' ? 50 : (int) data_get($product->meta, 'supplier_stock', ($isScrapedProduct ? 50 : 0));

            $rawImg = data_get($product->meta, 'image')
                ?: (data_get($product->meta, 'images.0') ?: (data_get($product->meta, 'gallery.0.url') ?: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80'));
            if (is_array($rawImg)) {
                $rawImg = data_get($rawImg, 'url') ?: (data_get($rawImg, 'src') ?: '');
            }
            $mainImage = is_string($rawImg) ? str_replace('/./', '/', $rawImg) : '';

            $rawGallery = data_get($product->meta, 'gallery') ?: data_get($product->meta, 'images', []);
            $images = [];
            $imageColorMap = data_get($product->meta, 'images_by_color') ?: data_get($product->meta, 'image_color_map', []);
            if (is_array($rawGallery)) {
                foreach ($rawGallery as $gItem) {
                    if (is_string($gItem) && !empty($gItem)) {
                        $images[] = $gItem;
                    } elseif (is_array($gItem) && !empty($gItem['url'])) {
                        $images[] = $gItem['url'];
                        if (!empty($gItem['color_name']) && empty($imageColorMap[$gItem['url']])) {
                            $imageColorMap[$gItem['url']] = $gItem['color_name'];
                        }
                    }
                }
            }
            if (empty($images) && !empty($mainImage)) {
                $images = [$mainImage];
            }

            $cost = (float) ($product->cost ?: ($product->price * 0.5));
            $pvp = (float) $product->price;
            $regularPrice = (float) (data_get($product->meta, 'regular_price') ?: $pvp);
            $discountPct = (float) data_get($product->meta, 'discount_pct', 0);

            $rawBrand = $product->brand ?: data_get($product->meta, 'brand', '');
            $matchedSupplier = null;
            if ($sp?->supplier) {
                $matchedSupplier = $sp->supplier;
            } elseif ($rawBrand && isset($brandToSupplierMap[mb_strtolower(trim($rawBrand))])) {
                $matchedSupplier = $brandToSupplierMap[mb_strtolower(trim($rawBrand))];
            } elseif (data_get($product->meta, 'provider') && isset($brandToSupplierMap[mb_strtolower(trim((string) data_get($product->meta, 'provider')))])) {
                $matchedSupplier = $brandToSupplierMap[mb_strtolower(trim((string) data_get($product->meta, 'provider')))];
            }

            $supplierName = $matchedSupplier?->name ?: (data_get($product->meta, 'provider') ?: ($rawBrand ?: 'Torzo'));
            $supplierId = $matchedSupplier ? $matchedSupplier->id : (data_get($product->meta, 'supplier_id') ?: ($sp?->supplier_id ?: 4));
            $supplierSku = data_get($product->meta, 'supplier_sku') ?: ($sp?->supplier_sku ?: '');
            $supplierUrl = data_get($product->meta, 'provider_url')
                ?: (data_get($product->meta, 'supplier_url')
                ?: (data_get($product->meta, 'origin_url')
                ?: (data_get($product->meta, 'source_url')
                ?: (data_get($product->meta, 'supplierUrl')
                ?: ($sp?->source_url ?: '')))));

            $colorName = data_get($product->meta, 'color_name') ?: data_get($product->meta, 'colorName', '');
            $colorHex = data_get($product->meta, 'color_hex') ?: data_get($product->meta, 'colorHex', '');
            $colorType = data_get($product->meta, 'color_type') ?: data_get($product->meta, 'colorType', 'solid');
            $fabricType = $product->fabric ?: (data_get($product->meta, 'fabric') ?: data_get($product->meta, 'fabric_type', data_get($product->meta, 'fabricType', '')));

            // Sincronización fidedigna con curaduría de scraping aprobada si existe desfase
            if ($product->sku) {
                $scrapedItem = \App\Models\ScrapedPendingItem::where('external_sku', $product->sku)->where('curation_status', 'approved')->first();
                if ($scrapedItem) {
                    if (($colorName === '' || $colorName === 'Negro Profundo') && $scrapedItem->curated_color && $scrapedItem->curated_color !== 'Negro Profundo') {
                        $colorName = $scrapedItem->curated_color;
                        if ($scrapedItem->detected_color_hex) {
                            $colorHex = $scrapedItem->detected_color_hex;
                        }
                    }
                    if (($fabricType === '' || $fabricType === 'Modal Seamless') && $scrapedItem->curated_fabric && $scrapedItem->curated_fabric !== 'Modal Seamless') {
                        $fabricType = $scrapedItem->curated_fabric;
                    }
                }
            }

            // Resolver Hex Cromático a través del catálogo maestro ApparelColor si está ausente o es genérico oscuro
            if (strtolower(trim((string)$colorName)) === 'multicolor') {
                $colorHex = 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)';
                $colorType = 'iridescent';
            } elseif (empty($colorHex) || $colorHex === '#0f172a' || $colorHex === '#111827') {
                if (!empty($colorName)) {
                    $matchedCol = \App\Models\ApparelColor::where('name', $colorName)
                        ->orWhere('name', 'like', '%' . explode(' ', $colorName)[0] . '%')
                        ->first();
                    if ($matchedCol) {
                        $colorHex = $matchedCol->hex;
                        $colorType = $matchedCol->type;
                    }
                }
                $colorHex = $colorHex ?: '#0f172a';
            }

            $designType = data_get($product->meta, 'design_type') ?: data_get($product->meta, 'designType', 'sin_diseno');
            $websiteSection = $product->section_name ?: (data_get($product->meta, 'section_name') ?: (data_get($product->meta, 'website_section') ?: data_get($product->meta, 'websiteSection', 'basicos')));
            $location = data_get($product->meta, 'location', 'Rack A-01 / Nivel 1');
            $brand = $rawBrand ?: ($product->brand ?: data_get($product->meta, 'brand', $supplierName));
            $category = data_get($product->meta, 'category', 'Ropa Interior');
            $subcategory = data_get($product->meta, 'subcategory', '');

            $variantDimension = data_get($product->meta, 'variant_dimension', 'size');
            $selectedSizeCurveId = data_get($product->meta, 'selected_size_curve_id', '');
            $shades = data_get($product->meta, 'shades', []);
            $volumes = data_get($product->meta, 'volumes', []);

            if ($isScrapedProduct) {
                $status = 'Sincronizado';
            } elseif ($availableStock > 0) {
                $status = 'Saludable';
            } elseif ($supplierStock > 0) {
                $status = 'Solo proveedor';
            } else {
                $status = 'Agotado';
            }

            return [
                'id' => $product->id,
                'dbId' => $product->id,
                'sku' => $product->sku,
                'weightedAverageCost' => (float) data_get($product->meta, 'weighted_average_cost', $cost),
                'name' => $product->title,
                'brand' => $brand,
                'provider' => $supplierName,
                'supplier_id' => $supplierId,
                'supplierId' => $supplierId,
                'supplierSku' => $supplierSku,
                'supplierUrl' => $supplierUrl,
                'providerUrl' => $supplierUrl,
                'originUrl' => $supplierUrl,
                'category' => $category,
                'subcategory' => $subcategory,
                'type' => ($isScrapedProduct || $product->type === 'dropship') ? 'dropship' : ($product->type === 'physical' ? 'propio' : 'mixto'),
                'isScraped' => (bool) $isScrapedProduct,
                'is_scraped' => (bool) $isScrapedProduct,
                'supplierType' => ($isScrapedProduct || $product->type === 'dropship') ? 'dropship' : 'local',
                'localStock' => $isScrapedProduct ? 0 : $availableStock,
                'physicalStock' => $isScrapedProduct ? 0 : $totalStock,
                'physicalSizes' => $physicalSizes,
                'committedStock' => $isScrapedProduct ? 0 : $totalReserved,
                'committedSizes' => $committedSizes,
                'lockedStock' => $isScrapedProduct ? 0 : $totalLocked,
                'lockedSizes' => $lockedSizes,
                'supplierStock' => $supplierStock,
                'location' => $location,
                'status' => $status,
                'pvp' => $pvp,
                'cost' => $cost,
                'regularPrice' => $regularPrice,
                'discountPct' => $discountPct,
                'sizes' => $sizes,
                'shades' => $shades,
                'volumes' => $volumes,
                'variantDimension' => $variantDimension,
                'selectedSizeCurveId' => $selectedSizeCurveId,
                'image' => $mainImage,
                'images' => $images,
                'gallery' => $rawGallery,
                'imageColorMap' => $imageColorMap,
                'colorName' => $colorName,
                'colorHex' => $colorHex,
                'colorType' => $colorType,
                'fabricType' => $fabricType,
                'designType' => $designType,
                'websiteSection' => $websiteSection,
                'description' => $product->description ?: data_get($product->meta, 'description', ''),
                'isPublished' => (bool) $product->visible,
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

        $portalFuturePurchaseOrders = $portalProducts->map(function ($p, $idx) {
            $needed = max(24, 60 - (int) $p['localStock']);
            $cost = (float) $p['cost'];
            $ch = (int) round($needed * 0.15);
            $m = (int) round($needed * 0.35);
            $g = (int) round($needed * 0.30);
            $xl = (int) round($needed * 0.15);
            $xxl = max(0, $needed - ($ch + $m + $g + $xl));

            return [
                'id' => 'FP-2026-0' . ($idx + 1),
                'productId' => $p['id'],
                'sku' => $p['sku'],
                'productName' => $p['name'],
                'supplier' => $p['provider'] ?: 'Torzo',
                'supplier_id' => $p['supplier_id'] ?: 4,
                'brand' => $p['brand'] ?: ($p['provider'] ?: 'Torzo'),
                'qty' => $needed,
                'sizes' => ['ch' => $ch, 'm' => $m, 'g' => $g, 'xl' => $xl, '2xl' => $xxl, '3xl' => 0],
                'costUnit' => $cost,
                'totalCost' => round($needed * $cost, 2),
                'priority' => $p['localStock'] <= 0 ? 'alta' : 'media',
                'status' => 'Planificada',
                'date' => now()->subDays(2 - ($idx % 5))->format('d M, Y'),
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
            ->with(['supplier', 'purchaseOrder', 'appliedToPurchaseOrder'])
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
                'status' => match ($ca->status) {
                    'pending' => 'Pendiente de Aplicar',
                    'applied' => 'Aplicada',
                    'void', 'cancelled' => 'Anulada',
                    default => ucfirst($ca->status),
                },
                'raw_status' => $ca->status,
                'applied_to_po_id' => $ca->applied_to_po_id,
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
            'portalDraftProducts' => $portalDraftProducts,
            'portalArchivedProducts' => $portalArchivedProducts,
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
            'portalApparelColors' => (function() {
                $colors = \App\Models\ApparelColor::orderBy('name')->get();
                if (!$colors->contains(fn($c) => strtolower($c->name) === 'multicolor')) {
                    \App\Models\ApparelColor::firstOrCreate(
                        ['name' => 'Multicolor'],
                        [
                            'color_type' => 'gradient',
                            'hex_code' => '#ef4444',
                            'gradient_css' => 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)',
                            'is_active' => true,
                        ]
                    );
                    $colors = \App\Models\ApparelColor::orderBy('name')->get();
                }
                return $colors;
            })(),
            'portalStoreSections' => \App\Models\CmsSection::orderBy('sort_order')->orderBy('name')->get(),
            'portalMoqSheets' => \App\Models\SupplierMoqSheet::with('supplier')->orderByDesc('is_default')->orderBy('supplier_name')->get(),
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
            str_contains($name, 'multicolor') || str_contains($name, 'multi') => 'linear-gradient(135deg, #ef4444 0%, #f97316 20%, #eab308 40%, #22c55e 60%, #3b82f6 80%, #a855f7 100%)',
            str_contains($name, 'galaxia') || str_contains($name, 'holo') => 'linear-gradient(135deg, #a855f7 0%, #3b82f6 50%, #06b6d4 100%)',
            str_contains($name, 'aurora') || str_contains($name, 'boreal') => 'linear-gradient(135deg, #10b981 0%, #06b6d4 50%, #6366f1 100%)',
            str_contains($name, 'tornasol') => 'linear-gradient(135deg, #ec4899 0%, #8b5cf6 50%, #3b82f6 100%)',
            str_contains($name, 'tricolor') => 'linear-gradient(135deg, #15803d 0%, #15803d 33%, #ffffff 33%, #ffffff 66%, #b91c1c 66%, #b91c1c 100%)',
            str_contains($name, 'negro') || str_contains($name, 'black') => '#0f172a',
            str_contains($name, 'blanco') || str_contains($name, 'white') => '#ffffff',
            str_contains($name, 'azul') || str_contains($name, 'blue') || str_contains($name, 'marino') => '#1e3a8a',
            str_contains($name, 'naranja') || str_contains($name, 'orange') => '#ea580c',
            str_contains($name, 'rojo') || str_contains($name, 'red') => '#b91c1c',
            str_contains($name, 'vino') || str_contains($name, 'burgundy') || str_contains($name, 'granate') => '#881337',
            str_contains($name, 'cafe') || str_contains($name, 'café') || str_contains($name, 'marron') || str_contains($name, 'marrón') || str_contains($name, 'brown') => '#78350f',
            str_contains($name, 'verde') || str_contains($name, 'green') || str_contains($name, 'olivo') => '#15803d',
            str_contains($name, 'turquesa') || str_contains($name, 'cyan') || str_contains($name, 'aqua') => '#06b6d4',
            str_contains($name, 'gris') || str_contains($name, 'gray') || str_contains($name, 'plata') || str_contains($name, 'silver') => '#64748b',
            str_contains($name, 'beige') || str_contains($name, 'nude') || str_contains($name, 'arena') => '#d4b996',
            str_contains($name, 'rosa') || str_contains($name, 'pink') || str_contains($name, 'coral') => '#f43f5e',
            str_contains($name, 'morado') || str_contains($name, 'purple') || str_contains($name, 'lila') || str_contains($name, 'lavanda') => '#7e22ce',
            str_contains($name, 'magenta') || str_contains($name, 'fucsia') => '#d946ef',
            str_contains($name, 'amarillo') || str_contains($name, 'yellow') || str_contains($name, 'dorado') || str_contains($name, 'gold') => '#eab308',
            str_contains($name, 'caqui') || str_contains($name, 'kaki') => '#a3a375',
            str_contains($name, 'menta') || str_contains($name, 'mint') => '#6ee7b7',
            str_contains($name, 'cielo') || str_contains($name, 'celeste') => '#38bdf8',
            str_contains($name, 'perla') || str_contains($name, 'hueso') || str_contains($name, 'marfil') => '#fefce8',
            default => '#475569',
        };
    }

    public static function cleanStoreImageUrl(?string $url): string
    {
        if (empty($url) || !is_string($url)) {
            return '/user-avatar.png';
        }
        $url = trim($url);
        if (preg_match('#^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?(/storage/.*)$#i', $url, $m)) {
            return $m[1];
        }
        return str_replace('/./', '/', $url);
    }

    public static function resolveProductColorData(Product $product, string $mainImage, array $allImages): array
    {
        $colors = [];
        $imagesByColor = [];
        $mainImage = self::cleanStoreImageUrl($mainImage);
        $allImages = array_map([self::class, 'cleanStoreImageUrl'], $allImages);

        static $crudColors = null;
        if ($crudColors === null || app()->environment('testing')) {
            try {
                $crudColors = \Illuminate\Support\Facades\DB::table('apparel_colors')->where('is_active', true)->get();
            } catch (\Throwable $e) {
                $crudColors = collect();
            }
        }

        $addColor = function (string $rawName, ?string $rawHex, ?string $rawImg) use (&$colors, &$imagesByColor, $mainImage, $crudColors, $product) {
            $name = trim($rawName);
            if (preg_match('#^(https?://|/|\w+\.\w+)#i', $name) || str_contains($name, '/') || strlen($name) > 40) {
                return;
            }
            if (in_array(strtolower($name), ['único', 'unico', 'general', ''])) {
                return;
            }

            // Normalización canónica con CRUD de Colores (apparel_colors)
            $matchedCrud = $crudColors->first(fn($c) => strcasecmp(trim($c->name), $name) === 0);
            if (!$matchedCrud) {
                $metaCol = trim((string) data_get($product->meta, 'color_name', ''));
                if ($metaCol !== '' && (stripos($name, $metaCol) !== false || stripos($metaCol, $name) !== false)) {
                    $matchedCrud = $crudColors->first(fn($c) => strcasecmp(trim($c->name), $metaCol) === 0);
                    if ($matchedCrud) {
                        $name = $matchedCrud->name;
                    }
                } else {
                    $fuzzy = $crudColors->first(function($c) use ($name) {
                        $cLower = strtolower(trim($c->name));
                        $nLower = strtolower(trim($name));
                        return str_contains($nLower, $cLower) || str_contains($cLower, $nLower);
                    });
                    if ($fuzzy) {
                        $name = $fuzzy->name;
                        $matchedCrud = $fuzzy;
                    }
                }
            }

            $img = $rawImg ? self::cleanStoreImageUrl($rawImg) : $mainImage;
            if (!isset($colors[$name])) {
                $isGradientColor = ($matchedCrud && in_array($matchedCrud->color_type ?? '', ['gradient', 'iridescent'])) || str_contains(strtolower($name), 'multicolor') || str_contains(strtolower($name), 'tornasol') || str_contains(strtolower($name), 'holo') || str_contains(strtolower($name), 'aurora');
                $hex = $isGradientColor
                    ? ($matchedCrud?->gradient_css ?: self::detectColorHex($name))
                    : ($rawHex ?: ($matchedCrud?->hex_code ?: self::detectColorHex($name)));
                $type = $isGradientColor ? 'gradient' : ($matchedCrud?->color_type ?: 'solid');
                $colors[$name] = [
                    'name' => $name,
                    'hex' => $hex,
                    'type' => $type,
                    'image' => $img,
                ];
            }
            if (!isset($imagesByColor[$name])) {
                $imagesByColor[$name] = [];
            }
            if ($img && !in_array($img, $imagesByColor[$name])) {
                $imagesByColor[$name][] = $img;
            }
        };

        // 1. Variantes de BD
        foreach ($product->variants as $v) {
            $cName = data_get($v->attributes, 'color');
            if ($cName) {
                $cHex = data_get($v->attributes, 'color_hex');
                $vImg = data_get($v->attributes, 'image');
                $addColor((string)$cName, $cHex ? (string)$cHex : null, $vImg ? (string)$vImg : null);
            }
        }

        // 2. Meta 'gallery' [{ url, color_name, is_primary }]
        if ($product->meta && isset($product->meta['gallery']) && is_array($product->meta['gallery'])) {
            foreach ($product->meta['gallery'] as $gItem) {
                if (is_array($gItem) && !empty($gItem['color_name']) && !empty($gItem['url'])) {
                    $addColor((string)$gItem['color_name'], null, (string)$gItem['url']);
                }
            }
        }

        // 3. Meta 'colors'
        if ($product->meta && isset($product->meta['colors']) && is_array($product->meta['colors'])) {
            foreach ($product->meta['colors'] as $c) {
                $cName = is_array($c) ? ($c['name'] ?? null) : (string)$c;
                if ($cName) {
                    $cHex = is_array($c) ? ($c['hex'] ?? null) : null;
                    $cImg = is_array($c) ? ($c['image'] ?? null) : null;
                    $addColor((string)$cName, $cHex ? (string)$cHex : null, $cImg ? (string)$cImg : null);
                }
            }
        }

        // 4. Meta 'images_by_color' o 'image_color_map'
        $imgColorMap = data_get($product->meta, 'images_by_color') ?: data_get($product->meta, 'image_color_map');
        if (is_array($imgColorMap)) {
            foreach ($imgColorMap as $key => $val) {
                $keyStr = (string)$key;
                if (preg_match('#^(https?://|/|\w+\.\w+)#i', $keyStr) || str_contains($keyStr, '/')) {
                    if (is_string($val) && trim($val) !== '') {
                        $addColor(trim($val), null, $keyStr);
                    }
                } else {
                    if (is_array($val)) {
                        foreach ($val as $subImg) {
                            $addColor($keyStr, null, (string)$subImg);
                        }
                    } elseif (is_string($val)) {
                        $addColor($keyStr, null, $val);
                    }
                }
            }
        }

        // 5. Meta 'color_name' / 'color_hex'
        // Solo añadir si no hay colores derivados de galería/variantes, o si coincide con los colores existentes
        $metaColorName = trim((string) data_get($product->meta, 'color_name', ''));
        if ($metaColorName !== '') {
            $hasExplicitColors = !empty($colors);
            $hasGalleryColors = !empty($product->meta['gallery']) || !empty($product->meta['images_by_color']) || !empty($product->meta['image_color_map']);
            if (!$hasExplicitColors || !$hasGalleryColors || isset($colors[$metaColorName])) {
                $addColor($metaColorName, data_get($product->meta, 'color_hex'), $mainImage);
            }
        }

        // Fallback default color
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

                if ($variants->isEmpty()) {
                    $metaSizes = data_get($product->meta, 'sizes', []);
                    $totalStock = is_array($metaSizes) && !empty($metaSizes) 
                        ? array_sum($metaSizes) 
                        : (int) data_get($product->meta, 'local_stock', 0);
                    $totalReserved = 0;
                    $availableStock = $totalStock;
                }

                // Detección precisa de categoría analizando relaciones de categorías, meta, subcategoría y título
                $metaCategory = (string) data_get($product->meta, 'category', '');
                $metaSubcategory = (string) data_get($product->meta, 'subcategory', '');
                $categoryText = strtoupper(trim(
                    $product->categories->pluck('name')->merge($product->categories->pluck('slug'))->implode(' ')
                    . ' ' . $metaCategory
                    . ' ' . $metaSubcategory
                    . ' ' . $product->title
                ));

                $categorySlug = 'jocks';
                $categoryName = 'Jockstraps';

                if (str_contains($categoryText, 'BOXER') || str_contains($categoryText, 'BRIEF')) {
                    $categorySlug = 'boxers';
                    $categoryName = 'Boxers';
                } elseif (str_contains($categoryText, 'TANGA')) {
                    $categorySlug = 'tangas';
                    $categoryName = 'Tangas';
                } elseif (str_contains($categoryText, 'BIKINI')) {
                    $categorySlug = 'bikinis';
                    $categoryName = 'Bikinis';
                } elseif (str_contains($categoryText, 'TANK') || str_contains($categoryText, 'SHIRT') || str_contains($categoryText, 'TOP')) {
                    $categorySlug = 'tanktops';
                    $categoryName = 'Tank Tops';
                } elseif (str_contains($categoryText, 'SWIM') || str_contains($categoryText, 'BAÑO')) {
                    $categorySlug = 'swimwear';
                    $categoryName = 'Trajes de Baño';
                } elseif (str_contains($categoryText, 'JOCK')) {
                    $categorySlug = 'jocks';
                    $categoryName = 'Jockstraps';
                } elseif ($product->categories->isNotEmpty()) {
                    $categorySlug = $product->categories->first()->slug;
                    $categoryName = $product->categories->first()->name;
                } elseif (!empty($metaCategory)) {
                    $categorySlug = \Illuminate\Support\Str::slug($metaCategory);
                    $categoryName = $metaCategory;
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
                    $metaSizes = data_get($product->meta, 'sizes', []);
                    if (is_array($metaSizes) && !empty($metaSizes)) {
                        foreach (array_keys($metaSizes) as $ms) {
                            $sizes[] = strtoupper(trim((string) $ms));
                        }
                    }
                }
                if (empty($sizes)) {
                    $curve = (new \App\Services\Catalog\ProductCatalogSyncService())->resolveSizeCurveForProduct($product);
                    if ($curve && $curve->sizes->isNotEmpty()) {
                        foreach ($curve->sizes as $cs) {
                            $sizes[] = strtoupper(trim((string) $cs->code));
                        }
                    }
                }
                if (empty($sizes)) {
                    $sizes = ['UNI'];
                }

                $image = data_get($product->meta, 'image') ?: (data_get($product->meta, 'images.0') ?: '/user-avatar.png');
                $image = str_replace('/./', '/', $image);
                $allImages = data_get($product->meta, 'images') ?: [$image];
                if (!is_array($allImages) || empty($allImages)) {
                    $allImages = [$image];
                }
                $colorData = self::resolveProductColorData($product, $image, $allImages);
                $supplierName = (string) (
                    data_get($product->meta, 'provider')
                    ?? data_get($product->meta, 'supplier')
                    ?? $product->supplierProducts->first()?->supplier?->name
                    ?? data_get($product->meta, 'supplier_name')
                    ?? $product->brand
                    ?? ''
                );

                $isScrapedProduct = $product->isScraped();
                $explicitAbc = strtoupper(trim((string) (data_get($product->meta, 'abc_class') ?? data_get($product->meta, 'classification') ?? '')));
                $monthlySales = (int) data_get($product->meta, 'monthly_sales', $availableStock ?: 10);
                if (!in_array($explicitAbc, ['A', 'B', 'C'], true)) {
                    $explicitAbc = $monthlySales >= 120 ? 'A' : ($monthlySales >= 50 ? 'B' : 'C');
                }
                $compareAtPrice = (float) ($product->compare_at_price ?? data_get($product->meta, 'compare_at_price', 0));
                $discountPct = (float) data_get($product->meta, 'discount_pct', 0);
                if ($discountPct <= 0 && $compareAtPrice > (float) $product->price && (float) $product->price > 0) {
                    $discountPct = round((($compareAtPrice - (float) $product->price) / $compareAtPrice) * 100, 1);
                }

                return [
                    'id' => (string) $product->id,
                    'sku' => $product->sku,
                    'name' => $product->title,
                    'slug' => $product->slug,
                    'category' => $categorySlug,
                    'categoryName' => $categoryName,
                    'price' => (float) $product->price,
                    'compare_at_price' => $compareAtPrice,
                    'price_formatted' => 'MXN $' . number_format((float) $product->price, 2),
                    'universes' => $universes,
                    'abc_class' => $explicitAbc,
                    'stockType' => $isScrapedProduct ? 'on_demand' : ($availableStock > 0 ? 'ready' : 'on_demand'),
                    'stockLabel' => $isScrapedProduct ? 'Bajo Pedido' : ($availableStock > 0 ? 'Stock Físico' : 'Bajo Pedido'),
                    'is_scraped' => $isScrapedProduct,
                    'availableStock' => $isScrapedProduct ? 0 : $availableStock,
                    'image' => $image,
                    'images' => $allImages,
                    'colors' => $colorData['colors'],
                    'images_by_color' => $colorData['images_by_color'],
                    'supplier' => $supplierName,
                    'sizes' => $sizes,
                    'description' => $product->description ?: 'Prenda anatómica confeccionada con textiles transpirables.',
                    'brand' => (string) ($product->brand ?? ''),
                    'section_name' => (string) ($product->section_name ?? data_get($product->meta, 'section', '')),
                    'garment_type' => (string) data_get($product->meta, 'garment_type', ''),
                    'fabric_type' => (string) ($product->fabric ?: data_get($product->meta, 'fabric', data_get($product->meta, 'fabric_type', ''))),
                    'discount_pct' => $discountPct,
                    'category_ids' => $product->categories->pluck('id')->all(),
                    'variants' => $variants->map(fn ($v) => [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'size' => strtoupper(trim((string) data_get($v->attributes, 'size', 'M'))),
                        'color' => data_get($v->attributes, 'color', 'Unico'),
                        'price' => (float) $v->price,
                        'available' => $isScrapedProduct ? 0 : max(0, $v->inventories->sum('quantity') - $v->inventories->sum('reserved')),
                        'stockType' => $isScrapedProduct ? 'on_demand' : (max(0, $v->inventories->sum('quantity') - $v->inventories->sum('reserved')) > 0 ? 'ready' : 'on_demand'),
                        'stockLabel' => $isScrapedProduct ? 'Bajo Pedido' : (max(0, $v->inventories->sum('quantity') - $v->inventories->sum('reserved')) > 0 ? 'Stock Físico' : 'Bajo Pedido'),
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
                    'size' => data_get($attributes, 'size', (is_array(data_get($product->meta, 'sizes')) && !empty(data_get($product->meta, 'sizes')) ? strtoupper((string) array_key_first(data_get($product->meta, 'sizes'))) : 'M')),
                    'fit' => data_get($attributes, 'fit', 'Regular'),
                    'color' => data_get($attributes, 'color', data_get($product->meta, 'color_name', 'Negro')),
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

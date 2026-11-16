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

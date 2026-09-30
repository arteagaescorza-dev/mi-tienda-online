<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CmsBlogPost;
use App\Models\CmsCatalogFilter;
use App\Models\CmsOfficialDocument;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsStorefrontConfig;
use App\Models\CmsTrackingSetting;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PortalStoreController extends Controller
{
    /**
     * Display the Storefront CMS and Multiverse vitrinas dashboard.
     */
    public function index(Request $request)
    {
        // 1. Live Catalog Queries per Universe (Single Inventory Pool decoupling)
        $marteProducts = Product::where('visible', true)
            ->whereIn('brand', ['JOCKSTRAPS', 'BRIEF/BOXERS', 'TANGA'])
            ->get();

        $venusProducts = Product::where('visible', true)
            ->whereIn('brand', ['BIKINI', 'SWIMWEAR', 'TANGA'])
            ->get();

        $tiresiasProducts = Product::where('visible', true)
            ->whereIn('brand', ['TANK TOPS', 'T-SHIRTS', 'BRIEF/BOXERS'])
            ->get();

        $terraProducts = Product::where('visible', true)
            ->whereIn('brand', ['COMBOS', 'Adrenaline Forte', 'BRIEF/BOXERS'])
            ->get();

        // Fallbacks if specific brands are empty
        $generalProducts = Product::where('visible', true)->take(12)->get();
        if ($marteProducts->isEmpty()) $marteProducts = $generalProducts->slice(0, 3);
        if ($venusProducts->isEmpty()) $venusProducts = $generalProducts->slice(3, 3);
        if ($tiresiasProducts->isEmpty()) $tiresiasProducts = $generalProducts->slice(6, 3);
        if ($terraProducts->isEmpty()) $terraProducts = $generalProducts->slice(9, 3);

        $formatSample = function ($col) {
            return $col->take(3)->map(function ($p) {
                return [
                    'name' => $p->title,
                    'price' => (float)$p->price,
                    'sku' => $p->sku,
                    'tag' => $p->brand ?? 'Core Multiverso'
                ];
            })->values()->toArray();
        };

        // 2. Multiverse Vitrinas Configuration (Persisted via Cache)
        $defaultStorefronts = [
            'marte' => [
                'id' => 'marte',
                'name' => 'Marte Store',
                'kicker' => 'Moda Masculina & Sensualidad Ergonómica',
                'logoUrl' => null,
                'logoMobileUrl' => null,
                'faviconUrl' => null,
                'primaryColor' => '#09090b',
                'secondaryColor' => '#27272a',
                'accentColor' => '#4f46e5',
                'backgroundColor' => '#fafafa',
                'surfaceColor' => '#ffffff',
                'textColor' => '#09090b',
                'mutedTextColor' => '#71717a',
                'borderColor' => '#e4e4e7',
                'heroTitle' => 'Diseño, Comodidad y Firmeza sin Censura',
                'heroSubtitle' => 'Textiles premium de modal y bambú diseñados para la máxima libertad de movimiento y soporte atlético.',
                'heroCtaText' => 'Ver Colección Masculina',
                'heroCtaUrl' => '/catalogo?universo=marte',
                'activeSkus' => $marteProducts->count(),
                'sampleProducts' => $formatSample($marteProducts)
            ],
            'venus' => [
                'id' => 'venus',
                'name' => 'Venus Lingerie & Beauty',
                'kicker' => 'Lencería de Autor, Bralettes y Cuidado Íntimo',
                'logoUrl' => null,
                'logoMobileUrl' => null,
                'faviconUrl' => null,
                'primaryColor' => '#1c1917',
                'secondaryColor' => '#44403c',
                'accentColor' => '#db2777',
                'backgroundColor' => '#fff1f2',
                'surfaceColor' => '#ffffff',
                'textColor' => '#1c1917',
                'mutedTextColor' => '#78716c',
                'borderColor' => '#fecdd3',
                'heroTitle' => 'Elegancia Sensorial y Suavidad para tu Piel',
                'heroSubtitle' => 'Encajes finos, satín italiano y fórmulas limpias de cuidado personal pensadas para tu bienestar diario.',
                'heroCtaText' => 'Explorar Colección Femenina',
                'heroCtaUrl' => '/catalogo?universo=venus',
                'activeSkus' => $venusProducts->count(),
                'sampleProducts' => $formatSample($venusProducts)
            ],
            'tiresias' => [
                'id' => 'tiresias',
                'name' => 'Tiresias Moda Afirmativa',
                'kicker' => 'Binders de Compresión Segura, Packing y Cuidado Afirmativo',
                'logoUrl' => null,
                'logoMobileUrl' => null,
                'faviconUrl' => null,
                'primaryColor' => '#0f172a',
                'secondaryColor' => '#334155',
                'accentColor' => '#8b5cf6',
                'backgroundColor' => '#faf5ff',
                'surfaceColor' => '#ffffff',
                'textColor' => '#0f172a',
                'mutedTextColor' => '#64748b',
                'borderColor' => '#e9d5ff',
                'heroTitle' => 'Libertad de Expresión y Ajuste Seguro para Todo Cuerpo',
                'heroSubtitle' => 'Prendas afirmativas confeccionadas con ingeniería textil transpirable de compresión médica segura.',
                'heroCtaText' => 'Ver Binders y Accesorios',
                'heroCtaUrl' => '/catalogo?universo=tiresias',
                'activeSkus' => $tiresiasProducts->count(),
                'sampleProducts' => $formatSample($tiresiasProducts)
            ],
            'terra' => [
                'id' => 'terra',
                'name' => 'Terra Multiverse',
                'kicker' => 'Ecosistema Global de Moda Íntima y Cuidado Personal',
                'logoUrl' => null,
                'logoMobileUrl' => null,
                'faviconUrl' => null,
                'primaryColor' => '#09090b',
                'secondaryColor' => '#27272a',
                'accentColor' => '#0284c7',
                'backgroundColor' => '#f8fafc',
                'surfaceColor' => '#ffffff',
                'textColor' => '#09090b',
                'mutedTextColor' => '#71717a',
                'borderColor' => '#e4e4e7',
                'heroTitle' => 'El Ecosistema Completo de Bienestar y Estilo',
                'heroSubtitle' => 'Catálogo unificado, suscripciones mensuales recurrentes y fórmulas de autocuidado para todas las identidades.',
                'heroCtaText' => 'Descubrir Todo el Catálogo',
                'heroCtaUrl' => '/catalogo',
                'activeSkus' => $terraProducts->count(),
                'sampleProducts' => $formatSample($terraProducts)
            ]
        ];

        $dbConfigs = CmsStorefrontConfig::all()->keyBy('universe');
        $storefronts = [];
        foreach ($defaultStorefronts as $key => $defaultData) {
            $cached = Cache::get('cms_storefront_' . $key, []);
            $dbData = [];
            if ($dbConfigs->has($key)) {
                $c = $dbConfigs[$key];
                $dbData = array_filter([
                    'name' => $c->brand_name,
                    'kicker' => $c->kicker,
                    'logoUrl' => $c->logo_url,
                    'logoMobileUrl' => $c->logo_mobile_url,
                    'faviconUrl' => $c->favicon_url,
                    'primaryColor' => $c->primary_color,
                    'secondaryColor' => $c->secondary_color,
                    'accentColor' => $c->accent_color,
                    'backgroundColor' => $c->background_color,
                    'surfaceColor' => $c->surface_color,
                    'textColor' => $c->text_color,
                    'mutedTextColor' => $c->muted_text_color,
                    'borderColor' => $c->border_color,
                    'heroTitle' => $c->hero_title,
                    'heroSubtitle' => $c->hero_subtitle,
                    'heroCtaText' => $c->hero_cta_text,
                    'heroCtaUrl' => $c->hero_cta_url,
                    'customCss' => $c->custom_css,
                    'customJs' => $c->custom_js,
                ], fn($val) => !is_null($val) && $val !== '');
            }
            $merged = array_merge($defaultData, $dbData, $cached);
            $merged['activeSkus'] = $defaultData['activeSkus'];
            $merged['sampleProducts'] = $defaultData['sampleProducts'];
            $storefronts[$key] = $merged;
        }

        // 3. Announcements & Marquesinas
        $defaultAnnouncements = [
            ['id' => 1, 'text' => 'Envío gratis nacional en compras mayores a $999 MXN con paqueterías líderes', 'link' => '/catalogo', 'badge' => 'Envío Gratis', 'active' => true],
            ['id' => 2, 'text' => 'Entrega personal segura en Metro Chabacano CDMX (Líneas 2 y 9) sin costo adicional', 'link' => '/chabacano', 'badge' => 'Punto Físico', 'active' => true],
            ['id' => 3, 'text' => 'Nueva Colección Modal Velvet: Máxima suavidad, transpirabilidad y cero rozaduras', 'link' => '/catalogo?tag=velvet', 'badge' => 'Lanzamiento', 'active' => true]
        ];
        $announcements = Cache::get('cms_announcements', $defaultAnnouncements);
        $announcementActive = Cache::get('cms_announcement_active', true);

        // 4. Hero Banners & Carruseles
        $defaultBanners = [
            ['id' => 1, 'title' => 'Colección Verano Circuit 2026', 'subtitle' => 'Jocks y boxers de secado ultra-rápido en colores neón y tornasol', 'badge' => 'Marte Masculino', 'universe' => 'marte', 'placement' => 'hero_carousel', 'imageUrl' => 'marte_summer_circuit.webp', 'linkUrl' => '/catalogo?universo=marte&col=verano', 'sortOrder' => 1, 'active' => true],
            ['id' => 2, 'title' => 'Lencería de Autor en Seda y Bambú', 'subtitle' => 'Piezas diseñadas para acariciar tu piel con soporte natural sin varillas', 'badge' => 'Venus Femenino', 'universe' => 'venus', 'placement' => 'hero_carousel', 'imageUrl' => 'venus_silk_bamboo.webp', 'linkUrl' => '/catalogo?universo=venus&col=seda', 'sortOrder' => 2, 'active' => true],
            ['id' => 3, 'title' => 'Binders Transpirables de Nueva Generación', 'subtitle' => 'Confección multicapa que distribuye la presión ergonómica sin asfixia', 'badge' => 'Tiresias Afirmativo', 'universe' => 'tiresias', 'placement' => 'hero_carousel', 'imageUrl' => 'tiresias_binders_gen3.webp', 'linkUrl' => '/catalogo?universo=tiresias&cat=binders', 'sortOrder' => 3, 'active' => true],
            ['id' => 4, 'title' => 'Membresía Mensual: Tu Caja Curada en Casa', 'subtitle' => 'Ahorra hasta 35% recibiendo prendas exclusivas y reposición cosmética', 'badge' => 'Terra Suscripciones', 'universe' => 'terra', 'placement' => 'category_promo', 'imageUrl' => 'terra_sub_box.webp', 'linkUrl' => '/suscripciones', 'sortOrder' => 4, 'active' => true],
            ['id' => 5, 'title' => 'Convenio Especial: Descuento en Cover Paradiso Circuit', 'subtitle' => 'Regístrate y recibe tu código VIP exclusivo para la taquilla del festival', 'badge' => 'Alianza Marte', 'universe' => 'marte', 'placement' => 'category_promo', 'imageUrl' => 'paradiso_collab.webp', 'linkUrl' => '/aliados/paradiso', 'sortOrder' => 5, 'active' => true],
            ['id' => 6, 'title' => 'Bolsa de Lavado Terra: Protege tus Prendas Delicadas', 'subtitle' => 'Gratis en compras superiores a $1,200 MXN o en tu primera caja mensual', 'badge' => 'Cuidado Textil', 'universe' => 'all', 'placement' => 'footer_strip', 'imageUrl' => 'wash_bag_promo.webp', 'linkUrl' => '/producto/ACC-WSH-BAG', 'sortOrder' => 6, 'active' => true]
        ];
        $banners = Cache::get('cms_banners', $defaultBanners);

        // 5. Commercial Shipping Rules & Financial Elasticity Simulator (TASK-INT-18)
        $defaultShippingRule = [
            'freeShippingThreshold' => 999.00,
            'flatRate' => 149.00,
            'localPickupEnabled' => true,
            'localPickupLocation' => 'Metro Chabacano CDMX (Líneas 2 y 9 - Pasillo de transbordo)',
            'localPickupSchedule' => 'Lunes a Viernes 16:00 a 19:00 hrs',
            'status' => 'active'
        ];
        $shippingRule = Cache::get('cms_shipping_rule', $defaultShippingRule);

        $threshold = (float)$shippingRule['freeShippingThreshold'];
        $projectedOrders = 120;
        // Dynamic financial projection based on threshold
        $ratio = max(10, min(85, round(75 - (($threshold - 699) / 800) * 45, 1)));
        $ordersAbsorbed = round($projectedOrders * ($ratio / 100));
        $absorbedCost = $ordersAbsorbed * 145.00;
        $projectedGmv = $projectedOrders * 680.00;
        $projectedMargin = round(max(15, 52.0 - (($absorbedCost / $projectedGmv) * 100)), 2);

        $simulator = [
            'currentThreshold' => $threshold,
            'projectedOrders' => $projectedOrders,
            'freeShippingRatio' => $ratio,
            'totalShippingAbsorbed' => $absorbedCost,
            'projectedEbitdaMargin' => $projectedMargin,
            'isHealthy' => $projectedMargin >= 30.00
        ];

        // 6. Size Guide Torzo & Care Protocols from Database (with fallback)
        $officialDocs = CmsOfficialDocument::where('is_active', true)->get()->keyBy('code');
        $sizeGuide = $officialDocs['SIZE_GUIDE_TORZO']->payload ?? [
            ['size' => 'CH', 'waistCm' => '71 - 76 cm', 'hipsCm' => '86 - 91 cm', 'pantsSize' => '28 - 30', 'isCore' => false, 'advice' => 'Ajuste ceñido anatómico'],
            ['size' => 'M', 'waistCm' => '77 - 84 cm', 'hipsCm' => '92 - 99 cm', 'pantsSize' => '31 - 33', 'isCore' => true, 'advice' => 'Talla Core más vendida (42% de demanda)'],
            ['size' => 'G', 'waistCm' => '85 - 92 cm', 'hipsCm' => '100 - 107 cm', 'pantsSize' => '34 - 36', 'isCore' => true, 'advice' => 'Talla Core más vendida (34% de demanda)'],
            ['size' => 'XL', 'waistCm' => '93 - 100 cm', 'hipsCm' => '108 - 115 cm', 'pantsSize' => '36 - 38', 'isCore' => false, 'advice' => 'Soporte holgado y elásticos flexibles'],
            ['size' => '2XL', 'waistCm' => '101 - 109 cm', 'hipsCm' => '116 - 123 cm', 'pantsSize' => '38 - 40', 'isCore' => false, 'advice' => 'Tiro extra confortable'],
            ['size' => '3XL', 'waistCm' => '110 - 120 cm', 'hipsCm' => '124 - 134 cm', 'pantsSize' => '40 - 44', 'isCore' => false, 'advice' => 'Máxima amplitud de cadera']
        ];

        // 7. Care Protocols & PROFECO Health Notice
        $careProtocols = $officialDocs['CARE_PROTOCOLS']->payload ?? [
            ['fiber' => 'Modal & Viscosa de Bambú', 'rule' => 'Lavar con agua fría (< 30°C) en ciclo delicado dentro de la Bolsa de Malla Protectora Terra.', 'warning' => 'Nunca usar secadora ni blanqueadores clorados; secar a la sombra en superficie plana.'],
            ['fiber' => 'Microfibra Velvet & Elásticos de Jockstraps', 'rule' => 'Lavar a mano o máquina en bolsa cerrada para evitar que los elásticos se enganchen con cremalleras.', 'warning' => 'El calor excesivo degrada el elastano; no planchar directamente sobre los elásticos.'],
            ['fiber' => 'Binders de Compresión Médica', 'rule' => 'Lavar cada 2-3 puestas con jabón líquido suave. Secar al aire sin retorcer la tela de compresión.', 'warning' => 'No estirar de forma asimétrica al escurrir para preservar la memoria elástica del tejido.']
        ];

        $profecoNotice = $officialDocs['PROFECO_SANITARY_NOTICE']->payload ?? [
            'legal_basis' => 'Artículo 53 de la Ley Federal de Protección al Consumidor.',
            'statement' => 'Por motivos estrictos de higiene y protección sanitaria, no se admiten devoluciones ni cambios en prendas íntimas con precinto de seguridad vulnerado.',
            'discreet_shipping' => 'Envíos en empaque 100% neutro y discreto.',
        ];

        // 8. Secciones dinámicas y taxonomías de catálogo de Hub 4
        $sections = CmsSection::orderBy('sort_order')->get();
        $categories = Category::whereNull('parent_id')->select('id', 'name', 'slug')->get();
        $subcategories = Category::whereNotNull('parent_id')->select('id', 'name', 'slug', 'parent_id')->get();
        $brands = Product::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand');
        $textileFamilies = ['Modal Velvet', 'Algodón Pima', 'Bambú Orgánico', 'Microfibra Dry-Fit', 'Malla Transpirable', 'Satín & Seda'];
        $garmentTypes = ['Boxer Trunk', 'Jockstrap', 'Brief', 'Bikini / Tanga', 'Harness / Arnés', 'Tank Top / Playera', 'Bodysuit'];
        $styleTags = ['Sensual', 'Deportivo / Gym', 'Afirmativo / Compresión', 'Minimalista', 'Edición Limitada', 'Básico Diario'];
        $discountOptions = [
            'all' => 'Cualquier prenda',
            'only_sale' => 'Solo con descuento / Liquidación',
            'min_15' => 'Descuento mín. 15%',
            'min_25' => 'Descuento mín. 25%',
        ];

        // 9. Páginas CMS, Blog Revista Digital, Tracking y Filtros
        $pages = CmsPage::orderBy('navbar_order')->get();
        $blogPosts = CmsBlogPost::orderByDesc('created_at')->get();
        $tracking = CmsTrackingSetting::current();
        $catalogFilters = CmsCatalogFilter::orderBy('sort_order')->get();

        return view('portal.store', [
            'storefronts' => $storefronts,
            'activeUniverse' => 'marte',
            'announcements' => $announcements,
            'announcementActive' => $announcementActive,
            'banners' => $banners,
            'sections' => $sections,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'brands' => $brands,
            'textileFamilies' => $textileFamilies,
            'garmentTypes' => $garmentTypes,
            'styleTags' => $styleTags,
            'discountOptions' => $discountOptions,
            'shippingRule' => $shippingRule,
            'simulator' => $simulator,
            'sizeGuide' => $sizeGuide,
            'careProtocols' => $careProtocols,
            'profecoNotice' => $profecoNotice,
            'pages' => $pages,
            'blogPosts' => $blogPosts,
            'tracking' => $tracking,
            'catalogFilters' => $catalogFilters,
        ]);
    }

    /**
     * Update shipping rules and free shipping threshold (TASK-INT-18).
     */
    public function updateShippingRule(Request $request)
    {
        $validated = $request->validate([
            'freeShippingThreshold' => 'nullable|numeric|min:0',
            'flatRate' => 'nullable|numeric|min:0',
            'localPickupEnabled' => 'nullable|boolean',
            'localPickupLocation' => 'nullable|string|max:255',
            'localPickupSchedule' => 'nullable|string|max:255',
        ]);

        $current = Cache::get('cms_shipping_rule', [
            'freeShippingThreshold' => 999.00,
            'flatRate' => 149.00,
            'localPickupEnabled' => true,
            'localPickupLocation' => 'Metro Chabacano CDMX (Líneas 2 y 9 - Pasillo de transbordo)',
            'localPickupSchedule' => 'Lunes a Viernes 16:00 a 19:00 hrs',
            'status' => 'active'
        ]);

        if (isset($validated['freeShippingThreshold'])) {
            $current['freeShippingThreshold'] = (float)$validated['freeShippingThreshold'];
        }
        if (isset($validated['flatRate'])) {
            $current['flatRate'] = (float)$validated['flatRate'];
        }
        if ($request->has('localPickupEnabled')) {
            $current['localPickupEnabled'] = (bool)$request->input('localPickupEnabled');
        }
        if (!empty($validated['localPickupLocation'])) {
            $current['localPickupLocation'] = $validated['localPickupLocation'];
        }
        if (!empty($validated['localPickupSchedule'])) {
            $current['localPickupSchedule'] = $validated['localPickupSchedule'];
        }

        Cache::forever('cms_shipping_rule', $current);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'shippingRule' => $current]);
        }

        return back()->with('success', 'Reglas comerciales de flete actualizadas correctamente.');
    }

    /**
     * Toggle banner active / paused status.
     */
    public function toggleBanner(Request $request, $bannerId)
    {
        $defaultBanners = [
            ['id' => 1, 'title' => 'Colección Verano Circuit 2026', 'subtitle' => 'Jocks y boxers de secado ultra-rápido en colores neón y tornasol', 'badge' => 'Marte Masculino', 'universe' => 'marte', 'placement' => 'hero_carousel', 'imageUrl' => 'marte_summer_circuit.webp', 'linkUrl' => '/catalogo?universo=marte&col=verano', 'sortOrder' => 1, 'active' => true],
            ['id' => 2, 'title' => 'Lencería de Autor en Seda y Bambú', 'subtitle' => 'Piezas diseñadas para acariciar tu piel con soporte natural sin varillas', 'badge' => 'Venus Femenino', 'universe' => 'venus', 'placement' => 'hero_carousel', 'imageUrl' => 'venus_silk_bamboo.webp', 'linkUrl' => '/catalogo?universo=venus&col=seda', 'sortOrder' => 2, 'active' => true],
            ['id' => 3, 'title' => 'Binders Transpirables de Nueva Generación', 'subtitle' => 'Confección multicapa que distribuye la presión ergonómica sin asfixia', 'badge' => 'Tiresias Afirmativo', 'universe' => 'tiresias', 'placement' => 'hero_carousel', 'imageUrl' => 'tiresias_binders_gen3.webp', 'linkUrl' => '/catalogo?universo=tiresias&cat=binders', 'sortOrder' => 3, 'active' => true],
            ['id' => 4, 'title' => 'Membresía Mensual: Tu Caja Curada en Casa', 'subtitle' => 'Ahorra hasta 35% recibiendo prendas exclusivas y reposición cosmética', 'badge' => 'Terra Suscripciones', 'universe' => 'terra', 'placement' => 'category_promo', 'imageUrl' => 'terra_sub_box.webp', 'linkUrl' => '/suscripciones', 'sortOrder' => 4, 'active' => true],
            ['id' => 5, 'title' => 'Convenio Especial: Descuento en Cover Paradiso Circuit', 'subtitle' => 'Regístrate y recibe tu código VIP exclusivo para la taquilla del festival', 'badge' => 'Alianza Marte', 'universe' => 'marte', 'placement' => 'category_promo', 'imageUrl' => 'paradiso_collab.webp', 'linkUrl' => '/aliados/paradiso', 'sortOrder' => 5, 'active' => true],
            ['id' => 6, 'title' => 'Bolsa de Lavado Terra: Protege tus Prendas Delicadas', 'subtitle' => 'Gratis en compras superiores a $1,200 MXN o en tu primera caja mensual', 'badge' => 'Cuidado Textil', 'universe' => 'all', 'placement' => 'footer_strip', 'imageUrl' => 'wash_bag_promo.webp', 'linkUrl' => '/producto/ACC-WSH-BAG', 'sortOrder' => 6, 'active' => true]
        ];

        $banners = Cache::get('cms_banners', $defaultBanners);
        $bannerFound = false;

        foreach ($banners as &$b) {
            if ($b['id'] == $bannerId) {
                $b['active'] = !$b['active'];
                $bannerFound = true;
                break;
            }
        }

        if ($bannerFound) {
            Cache::forever('cms_banners', $banners);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Estado del banner actualizado.');
    }

    /**
     * Toggle announcement bar on / off.
     */
    public function toggleAnnouncement(Request $request)
    {
        $current = Cache::get('cms_announcement_active', true);
        $newStatus = !$current;
        Cache::forever('cms_announcement_active', $newStatus);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'active' => $newStatus]);
        }

        return back()->with('success', 'Estado de la marquesina de anuncios actualizado.');
    }

    /**
     * Update visual configuration of a multiverse vitrina.
     */
    public function updateStorefront(Request $request, $universe)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'brandName' => 'nullable|string|max:120',
            'brand_name' => 'nullable|string|max:120',
            'kicker' => 'nullable|string|max:200',
            'logo_url' => 'nullable|string|max:255',
            'logoUrl' => 'nullable|string|max:255',
            'logo_mobile_url' => 'nullable|string|max:255',
            'logoMobileUrl' => 'nullable|string|max:255',
            'favicon_url' => 'nullable|string|max:255',
            'faviconUrl' => 'nullable|string|max:255',
            'primaryColor' => 'nullable|string|max:20',
            'primary_color' => 'nullable|string|max:20',
            'secondaryColor' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'accentColor' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            'backgroundColor' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'surfaceColor' => 'nullable|string|max:20',
            'surface_color' => 'nullable|string|max:20',
            'textColor' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'mutedTextColor' => 'nullable|string|max:20',
            'muted_text_color' => 'nullable|string|max:20',
            'borderColor' => 'nullable|string|max:20',
            'border_color' => 'nullable|string|max:20',
            'heroTitle' => 'nullable|string|max:200',
            'hero_title' => 'nullable|string|max:200',
            'heroSubtitle' => 'nullable|string|max:500',
            'hero_subtitle' => 'nullable|string|max:500',
            'heroCtaText' => 'nullable|string|max:80',
            'hero_cta_text' => 'nullable|string|max:80',
            'heroCtaUrl' => 'nullable|string|max:255',
            'hero_cta_url' => 'nullable|string|max:255',
            'customCss' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'customJs' => 'nullable|string',
            'custom_js' => 'nullable|string',
        ]);

        $config = CmsStorefrontConfig::forUniverse($universe);

        $brandName = $request->input('brand_name') ?? $request->input('brandName') ?? $request->input('name');
        if ($brandName) {
            $config->brand_name = $brandName;
        }
        if ($request->filled('kicker')) {
            $config->kicker = $request->input('kicker');
        }

        // Carga y Gestión de Activos Gráficos (Logos & Favicon)
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'logo_' . $universe . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/branding');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);
            $config->logo_url = '/uploads/branding/' . $filename;
        } elseif ($request->filled('logo_url') || $request->filled('logoUrl')) {
            $config->logo_url = $request->input('logo_url') ?? $request->input('logoUrl');
        }

        if ($request->hasFile('logo_mobile_file')) {
            $file = $request->file('logo_mobile_file');
            $filename = 'logo_mob_' . $universe . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/branding');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);
            $config->logo_mobile_url = '/uploads/branding/' . $filename;
        } elseif ($request->filled('logo_mobile_url') || $request->filled('logoMobileUrl')) {
            $config->logo_mobile_url = $request->input('logo_mobile_url') ?? $request->input('logoMobileUrl');
        }

        if ($request->hasFile('favicon_file')) {
            $file = $request->file('favicon_file');
            $filename = 'fav_' . $universe . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/branding');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);
            $config->favicon_url = '/uploads/branding/' . $filename;
        } elseif ($request->filled('favicon_url') || $request->filled('faviconUrl')) {
            $config->favicon_url = $request->input('favicon_url') ?? $request->input('faviconUrl');
        }

        // Paleta Cromática Completa (8 Tokens)
        if ($request->filled('primary_color') || $request->filled('primaryColor')) {
            $config->primary_color = $request->input('primary_color') ?? $request->input('primaryColor');
        }
        if ($request->filled('secondary_color') || $request->filled('secondaryColor')) {
            $config->secondary_color = $request->input('secondary_color') ?? $request->input('secondaryColor');
        }
        if ($request->filled('accent_color') || $request->filled('accentColor')) {
            $config->accent_color = $request->input('accent_color') ?? $request->input('accentColor');
        }
        if ($request->filled('background_color') || $request->filled('backgroundColor')) {
            $config->background_color = $request->input('background_color') ?? $request->input('backgroundColor');
        }
        if ($request->filled('surface_color') || $request->filled('surfaceColor')) {
            $config->surface_color = $request->input('surface_color') ?? $request->input('surfaceColor');
        }
        if ($request->filled('text_color') || $request->filled('textColor')) {
            $config->text_color = $request->input('text_color') ?? $request->input('textColor');
        }
        if ($request->filled('muted_text_color') || $request->filled('mutedTextColor')) {
            $config->muted_text_color = $request->input('muted_text_color') ?? $request->input('mutedTextColor');
        }
        if ($request->filled('border_color') || $request->filled('borderColor')) {
            $config->border_color = $request->input('border_color') ?? $request->input('borderColor');
        }

        // Campos opcionales de Hero (conservados por retrocompatibilidad)
        if ($request->filled('hero_title') || $request->filled('heroTitle')) {
            $config->hero_title = $request->input('hero_title') ?? $request->input('heroTitle');
        }
        if ($request->filled('hero_subtitle') || $request->filled('heroSubtitle')) {
            $config->hero_subtitle = $request->input('hero_subtitle') ?? $request->input('heroSubtitle');
        }
        if ($request->filled('hero_cta_text') || $request->filled('heroCtaText')) {
            $config->hero_cta_text = $request->input('hero_cta_text') ?? $request->input('heroCtaText');
        }
        if ($request->filled('hero_cta_url') || $request->filled('heroCtaUrl')) {
            $config->hero_cta_url = $request->input('hero_cta_url') ?? $request->input('heroCtaUrl');
        }
        if ($request->has('custom_css') || $request->has('customCss')) {
            $config->custom_css = $request->input('custom_css') ?? $request->input('customCss');
        }
        if ($request->has('custom_js') || $request->has('customJs')) {
            $config->custom_js = $request->input('custom_js') ?? $request->input('customJs');
        }

        $config->save();

        $cached = [
            'name' => $config->brand_name,
            'brandName' => $config->brand_name,
            'kicker' => $config->kicker,
            'logoUrl' => $config->logo_url,
            'logoMobileUrl' => $config->logo_mobile_url,
            'faviconUrl' => $config->favicon_url,
            'primaryColor' => $config->primary_color,
            'secondaryColor' => $config->secondary_color,
            'accentColor' => $config->accent_color,
            'backgroundColor' => $config->background_color,
            'surfaceColor' => $config->surface_color,
            'textColor' => $config->text_color,
            'mutedTextColor' => $config->muted_text_color,
            'borderColor' => $config->border_color,
            'heroTitle' => $config->hero_title,
            'heroSubtitle' => $config->hero_subtitle,
            'heroCtaText' => $config->hero_cta_text,
            'heroCtaUrl' => $config->hero_cta_url,
            'customCss' => $config->custom_css,
            'customJs' => $config->custom_js,
        ];
        Cache::forever('cms_storefront_' . $universe, $cached);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'universe' => $universe, 'storefront' => $cached, 'message' => "Configuración de vitrina {$universe} guardada exitosamente."]);
        }

        return back()->with('success', "Configuración visual de vitrina {$universe} actualizada exitosamente.");
    }

    /**
     * Create a new modular storefront section.
     */
    public function storeSection(Request $request)
    {
        $validated = $request->validate([
            'universe' => 'required|string|in:all,marte,venus,tiresias,terra',
            'name' => 'required|string|max:120',
            'type' => 'required|string|in:hero_carousel,brand_pillars,categories_grid,family_showcase,subcategories_slider,tags_cloud,custom_html_css_js,curated_products,editorial_banner,promo_modal',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'badge' => 'nullable|string|max:80',
            'query_rules' => 'nullable|array',
            'custom_html' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'settings' => 'nullable|array',
            'status' => 'nullable|string|in:active,draft,archived',
            'background_image_file' => 'nullable|image|max:5120',
        ]);

        $settings = $request->input('settings', []);

        // Manejo de imagen de fondo subida
        if ($request->hasFile('background_image_file')) {
            $file = $request->file('background_image_file');
            $filename = 'hero_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/sections'), $filename);
            $settings['background_image'] = '/uploads/sections/' . $filename;
        } elseif ($request->filled('background_image_url')) {
            $settings['background_image'] = $request->input('background_image_url');
        }

        $validated['settings'] = $settings;

        $maxOrder = CmsSection::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;
        $validated['status'] = $validated['status'] ?? 'active';

        // Regla de Negocio: Las secciones de tarjetas de catálogo NO permiten código custom.
        // Solo las secciones de contenido/hero/carrusel/modal/botones pueden tener HTML/CSS/JS.
        $productCardTypes = ['curated_products', 'categories_grid', 'family_showcase', 'tags_cloud', 'subcategories_slider'];
        if (in_array($validated['type'], $productCardTypes)) {
            $validated['custom_html'] = null;
            $validated['custom_css'] = null;
            $validated['custom_js'] = null;
        }

        $section = CmsSection::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'section' => $section, 'message' => "Sección '{$section->name}' creada exitosamente."]);
        }

        return back()->with('success', "Sección '{$section->name}' creada exitosamente.");
    }

    /**
     * Update an existing modular section.
     */
    public function updateSection(Request $request, $id)
    {
        $section = CmsSection::findOrFail($id);

        $validated = $request->validate([
            'universe' => 'required|string|in:all,marte,venus,tiresias,terra',
            'name' => 'required|string|max:120',
            'type' => 'nullable|string|in:hero_carousel,brand_pillars,categories_grid,family_showcase,subcategories_slider,tags_cloud,custom_html_css_js,curated_products,editorial_banner,promo_modal',
            'status' => 'required|string|in:active,draft,archived',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'badge' => 'nullable|string|max:80',
            'query_rules' => 'nullable|array',
            'custom_html' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'settings' => 'nullable|array',
            'background_image_file' => 'nullable|image|max:5120',
        ]);

        if (empty($validated['type'])) {
            $validated['type'] = $section->type;
        }

        $currentSettings = $section->settings ?? [];
        $newSettings = $request->input('settings', []);
        $mergedSettings = array_merge($currentSettings, $newSettings);

        // Manejo de imagen de fondo subida
        if ($request->hasFile('background_image_file')) {
            $file = $request->file('background_image_file');
            $filename = 'hero_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/sections'), $filename);
            $mergedSettings['background_image'] = '/uploads/sections/' . $filename;
        } elseif ($request->filled('background_image_url')) {
            $mergedSettings['background_image'] = $request->input('background_image_url');
        }

        $validated['settings'] = $mergedSettings;

        $productCardTypes = ['curated_products', 'categories_grid', 'family_showcase', 'tags_cloud', 'subcategories_slider'];
        if (in_array($validated['type'], $productCardTypes)) {
            $validated['custom_html'] = null;
            $validated['custom_css'] = null;
            $validated['custom_js'] = null;
        }

        $section->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'section' => $section, 'message' => "Sección '{$section->name}' actualizada correctamente."]);
        }

        return back()->with('success', "Sección '{$section->name}' actualizada correctamente.");
    }

    /**
     * Archive an existing modular section (status = 'archived').
     */
    public function archiveSection(Request $request, $id)
    {
        $section = CmsSection::findOrFail($id);
        $section->status = 'archived';
        $section->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => 'archived', 'message' => "Sección '{$section->name}' archivada correctamente."]);
        }

        return back()->with('success', "Sección '{$section->name}' archivada correctamente.");
    }

    /**
     * Atomically reorder sections using SortableJS order array.
     */
    public function reorderSections(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['order'] as $index => $id) {
                CmsSection::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Orden de secciones actualizado correctamente.']);
    }

    /**
     * Toggle section status between active and draft.
     */
    public function toggleSection(Request $request, $id)
    {
        $section = CmsSection::findOrFail($id);
        $section->status = $section->status === 'active' ? 'draft' : 'active';
        $section->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $section->status]);
        }

        return back()->with('success', "Estado de la sección '{$section->name}' actualizado a {$section->status}.");
    }

    /**
     * Delete/archive a section.
     */
    public function destroySection(Request $request, $id)
    {
        $section = CmsSection::findOrFail($id);
        $name = $section->name;
        $section->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Sección '{$name}' eliminada correctamente.");
    }

    /**
     * Update an official document payload (Guía Torzo, Cuidados, PROFECO).
     */
    public function updateDocument(Request $request, $code)
    {
        $doc = CmsOfficialDocument::where('code', $code)->firstOrFail();
        $payload = $request->input('payload');

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            }
        }

        $doc->payload = $payload;
        if ($request->filled('title')) {
            $doc->title = $request->input('title');
        }
        $doc->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'document' => $doc]);
        }

        return back()->with('success', "Documento oficial '{$doc->title}' actualizado correctamente.");
    }

    /**
     * Crear una página CMS.
     */
    public function storePage(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_pages,slug',
            'icon' => 'nullable|string|max:50',
            'content_html' => 'required|string',
            'show_in_navbar' => 'nullable|boolean',
            'navbar_order' => 'nullable|integer',
            'show_in_footer' => 'nullable|boolean',
            'footer_order' => 'nullable|integer',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'status' => 'nullable|in:active,draft',
        ]);

        $validated['show_in_navbar'] = $request->boolean('show_in_navbar');
        $validated['show_in_footer'] = $request->boolean('show_in_footer');
        $validated['status'] = $validated['status'] ?? 'active';

        $page = CmsPage::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'page' => $page]);
        }

        return back()->with('success', "Página CMS '{$page->title}' creada exitosamente.");
    }

    /**
     * Actualizar una página CMS.
     */
    public function updatePage(Request $request, CmsPage $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_pages,slug,' . $page->id,
            'icon' => 'nullable|string|max:50',
            'content_html' => 'required|string',
            'show_in_navbar' => 'nullable|boolean',
            'navbar_order' => 'nullable|integer',
            'show_in_footer' => 'nullable|boolean',
            'footer_order' => 'nullable|integer',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'status' => 'nullable|in:active,draft',
        ]);

        $validated['show_in_navbar'] = $request->boolean('show_in_navbar');
        $validated['show_in_footer'] = $request->boolean('show_in_footer');
        $page->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'page' => $page]);
        }

        return back()->with('success', "Página CMS '{$page->title}' actualizada exitosamente.");
    }

    /**
     * Eliminar una página CMS.
     */
    public function destroyPage(CmsPage $page)
    {
        $page->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Página CMS eliminada.');
    }

    /**
     * Crear artículo del blog para Revista Digital.
     */
    public function storeBlogPost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_blog_posts,slug',
            'category_name' => 'required|string|max:100',
            'tags' => 'nullable',
            'author_name' => 'nullable|string|max:100',
            'reading_time_minutes' => 'nullable|integer|min:1',
            'excerpt' => 'required|string',
            'body' => 'required|string',
            'status' => 'nullable|in:published,draft',
        ]);

        if (is_string($request->input('tags'))) {
            $validated['tags'] = array_filter(array_map('trim', explode(',', $request->input('tags'))));
        }
        $validated['status'] = $validated['status'] ?? 'published';
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $post = CmsBlogPost::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post' => $post]);
        }

        return back()->with('success', "Artículo '{$post->title}' publicado exitosamente.");
    }

    /**
     * Actualizar artículo del blog.
     */
    public function updateBlogPost(Request $request, CmsBlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_blog_posts,slug,' . $post->id,
            'category_name' => 'required|string|max:100',
            'tags' => 'nullable',
            'author_name' => 'nullable|string|max:100',
            'reading_time_minutes' => 'nullable|integer|min:1',
            'excerpt' => 'required|string',
            'body' => 'required|string',
            'status' => 'nullable|in:published,draft',
        ]);

        if (is_string($request->input('tags'))) {
            $validated['tags'] = array_filter(array_map('trim', explode(',', $request->input('tags'))));
        }

        $post->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post' => $post]);
        }

        return back()->with('success', "Artículo '{$post->title}' actualizado exitosamente.");
    }

    /**
     * Eliminar artículo del blog.
     */
    public function destroyBlogPost(CmsBlogPost $post)
    {
        $post->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Artículo del blog eliminado.');
    }

    /**
     * Actualizar configuración de tracking (GTM, GA4, Meta Pixel & CAPI).
     */
    public function updateTracking(Request $request)
    {
        $validated = $request->validate([
            'gtm_id' => 'nullable|string|max:50',
            'ga4_measurement_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
            'facebook_capi_token' => 'nullable|string',
            'facebook_test_event_code' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $tracking = CmsTrackingSetting::current();
        $validated['is_active'] = $request->boolean('is_active');
        $tracking->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'tracking' => $tracking]);
        }

        return back()->with('success', 'Configuración de tracking y analítica actualizada.');
    }

    /**
     * Actualizar configuración y orden de filtros del catálogo.
     */
    public function updateFilters(Request $request)
    {
        $filtersData = $request->input('filters', []);

        foreach ($filtersData as $id => $data) {
            $filter = CmsCatalogFilter::find($id);
            if ($filter) {
                $filter->update([
                    'label' => $data['label'] ?? $filter->label,
                    'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : $filter->sort_order,
                    'is_visible' => !empty($data['is_visible']),
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Filtros del catálogo actualizados correctamente.');
    }
}

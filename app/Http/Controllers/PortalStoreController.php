<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CmsOfficialDocument;
use App\Models\CmsSection;
use App\Models\CmsStorefrontConfig;
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
                'primaryColor' => '#09090b',
                'accentColor' => '#4f46e5',
                'backgroundColor' => '#fafafa',
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
                'primaryColor' => '#1c1917',
                'accentColor' => '#db2777',
                'backgroundColor' => '#fff1f2',
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
                'primaryColor' => '#0f172a',
                'accentColor' => '#8b5cf6',
                'backgroundColor' => '#faf5ff',
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
                'primaryColor' => '#09090b',
                'accentColor' => '#0284c7',
                'backgroundColor' => '#f8fafc',
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
                    'primaryColor' => $c->primary_color,
                    'accentColor' => $c->accent_color,
                    'backgroundColor' => $c->background_color,
                    'surfaceColor' => $c->surface_color,
                    'textColor' => $c->text_color,
                    'heroTitle' => $c->hero_title,
                    'heroSubtitle' => $c->hero_subtitle,
                    'heroCtaText' => $c->hero_cta_text,
                    'heroCtaUrl' => $c->hero_cta_url,
                    'customCss' => $c->custom_css,
                    'customJs' => $c->custom_js,
                ]);
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
        $categories = Category::select('id', 'name', 'slug')->get();
        $brands = Product::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand');

        return view('portal.store', [
            'storefronts' => $storefronts,
            'activeUniverse' => 'marte',
            'announcements' => $announcements,
            'announcementActive' => $announcementActive,
            'banners' => $banners,
            'sections' => $sections,
            'categories' => $categories,
            'brands' => $brands,
            'shippingRule' => $shippingRule,
            'simulator' => $simulator,
            'sizeGuide' => $sizeGuide,
            'careProtocols' => $careProtocols,
            'profecoNotice' => $profecoNotice,
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
            'primaryColor' => 'nullable|string|max:20',
            'accentColor' => 'nullable|string|max:20',
            'backgroundColor' => 'nullable|string|max:20',
            'surfaceColor' => 'nullable|string|max:20',
            'textColor' => 'nullable|string|max:20',
            'heroTitle' => 'nullable|string|max:200',
            'heroSubtitle' => 'nullable|string|max:500',
            'heroCtaText' => 'nullable|string|max:80',
            'heroCtaUrl' => 'nullable|string|max:255',
            'customCss' => 'nullable|string',
            'customJs' => 'nullable|string',
        ]);

        $config = CmsStorefrontConfig::forUniverse($universe);
        if (!empty($validated['name']) || !empty($validated['brandName'])) {
            $config->brand_name = $validated['brandName'] ?? $validated['name'];
        }
        if (!empty($validated['primaryColor'])) $config->primary_color = $validated['primaryColor'];
        if (!empty($validated['accentColor'])) $config->accent_color = $validated['accentColor'];
        if (!empty($validated['backgroundColor'])) $config->background_color = $validated['backgroundColor'];
        if (!empty($validated['surfaceColor'])) $config->surface_color = $validated['surfaceColor'];
        if (!empty($validated['textColor'])) $config->text_color = $validated['textColor'];
        if (!empty($validated['heroTitle'])) $config->hero_title = $validated['heroTitle'];
        if (!empty($validated['heroSubtitle'])) $config->hero_subtitle = $validated['heroSubtitle'];
        if (!empty($validated['heroCtaText'])) $config->hero_cta_text = $validated['heroCtaText'];
        if (!empty($validated['heroCtaUrl'])) $config->hero_cta_url = $validated['heroCtaUrl'];
        if (isset($validated['customCss'])) $config->custom_css = $validated['customCss'];
        if (isset($validated['customJs'])) $config->custom_js = $validated['customJs'];
        $config->save();

        $cached = [
            'name' => $config->brand_name,
            'primaryColor' => $config->primary_color,
            'accentColor' => $config->accent_color,
            'backgroundColor' => $config->background_color,
            'surfaceColor' => $config->surface_color,
            'textColor' => $config->text_color,
            'heroTitle' => $config->hero_title,
            'heroSubtitle' => $config->hero_subtitle,
            'heroCtaText' => $config->hero_cta_text,
            'heroCtaUrl' => $config->hero_cta_url,
        ];
        Cache::forever('cms_storefront_' . $universe, $cached);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'storefront' => $cached]);
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
            'type' => 'required|string|in:hero_carousel,brand_pillars,categories_grid,family_showcase,subcategories_slider,tags_cloud,custom_html_css_js,curated_products,editorial_banner',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'badge' => 'nullable|string|max:80',
            'query_rules' => 'nullable|array',
            'custom_html' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $maxOrder = CmsSection::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;
        $validated['status'] = 'active';

        $section = CmsSection::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'section' => $section]);
        }

        return back()->with('success', "Sección '{$section->name}' creada exitosamente.");
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
}

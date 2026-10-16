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
use App\Http\Requests\Portal\Store\IndexRequest;
use App\Http\Requests\Portal\Store\ToggleBannerRequest;
use App\Http\Requests\Portal\Store\ToggleAnnouncementRequest;
use App\Http\Requests\Portal\Store\ArchiveSectionRequest;
use App\Http\Requests\Portal\Store\ReorderSectionsRequest;
use App\Http\Requests\Portal\Store\ToggleSectionRequest;
use App\Http\Requests\Portal\Store\DestroySectionRequest;
use App\Http\Requests\Portal\Store\ReorderPagesRequest;
use App\Http\Requests\Portal\Store\UpdateAnnouncementsRequest;
use App\Http\Requests\Portal\Store\StoreBlogPostRequest;
use App\Http\Requests\Portal\Store\UpdateBlogPostRequest;
use App\Http\Requests\Portal\Store\UpdateTrackingRequest;
use App\Http\Requests\Portal\Store\UpdateFiltersRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PortalStoreController extends Controller
{
    /**
     * Display the Storefront CMS and Multiverse vitrinas dashboard.
     */
    public function index(IndexRequest $request)
    {
        Gate::authorize('manage-cms');
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
                'typography_heading' => 'Plus Jakarta Sans',
                'typography_subtitle' => 'Outfit',
                'typography_body' => 'Plus Jakarta Sans',
                'typography_accent' => 'Outfit',
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
                'typography_heading' => 'Playfair Display',
                'typography_subtitle' => 'Outfit',
                'typography_body' => 'Plus Jakarta Sans',
                'typography_accent' => 'Cinzel',
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
                'typography_heading' => 'Space Grotesk',
                'typography_subtitle' => 'Outfit',
                'typography_body' => 'Plus Jakarta Sans',
                'typography_accent' => 'Space Grotesk',
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
                'typography_heading' => 'Plus Jakarta Sans',
                'typography_subtitle' => 'Outfit',
                'typography_body' => 'Plus Jakarta Sans',
                'typography_accent' => 'Outfit',
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
                    'typography_heading' => $c->typography_heading,
                    'typography_subtitle' => $c->typography_subtitle,
                    'typography_body' => $c->typography_body,
                    'typography_accent' => $c->typography_accent,
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
        $annDoc = CmsOfficialDocument::getByCode('STORE_ANNOUNCEMENTS_CONFIG');
        $announcements = Cache::get('cms_announcements');
        if (!is_array($announcements) || empty($announcements)) {
            if (is_array($annDoc) && !empty($annDoc['items']) && is_array($annDoc['items'])) {
                $announcements = $annDoc['items'];
            } else {
                $announcements = $defaultAnnouncements;
            }
            Cache::forever('cms_announcements', $announcements);
        }
        $announcementActive = Cache::has('cms_announcement_active')
            ? (bool) Cache::get('cms_announcement_active')
            : (is_array($annDoc) && array_key_exists('active', $annDoc) ? (bool) $annDoc['active'] : true);
        Cache::forever('cms_announcement_active', $announcementActive);

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
        $fabrics = \App\Models\ApparelFabric::where('is_active', true)->orderBy('name')->get();
        $rawCareProtocols = $officialDocs['CARE_PROTOCOLS']->payload ?? [];
        if (!is_array($rawCareProtocols)) {
            $rawCareProtocols = [];
        }
        $careProtocols = array_values($rawCareProtocols);
        if (empty($careProtocols)) {
            $careProtocols = [
                ['fiber' => 'Modal & Viscosa de Bambú', 'rule' => 'Lavar con agua fría (< 30°C) en ciclo delicado dentro de la Bolsa de Malla Protectora Terra.', 'warning' => 'Nunca usar secadora ni blanqueadores clorados; secar a la sombra en superficie plana.', 'symbols' => ['wash_30', 'no_bleach', 'dry_shade', 'no_tumble']],
                ['fiber' => 'Microfibra Velvet & Elásticos de Jockstraps', 'rule' => 'Lavar a mano o máquina en bolsa cerrada para evitar que los elásticos se enganchen con cremalleras.', 'warning' => 'El calor excesivo degrada el elastano; no planchar directamente sobre los elásticos.', 'symbols' => ['wash_30', 'wash_hand', 'no_bleach', 'dry_shade']],
                ['fiber' => 'Binders de Compresión Médica', 'rule' => 'Lavar cada 2-3 puestas con jabón líquido suave. Secar al aire sin retorcer la tela de compresión.', 'warning' => 'No estirar de forma asimétrica al escurrir para preservar la memoria elástica del tejido.', 'symbols' => ['wash_hand', 'no_bleach', 'dry_flat', 'no_tumble', 'no_iron']]
            ];
        }

        // Sincronizar automáticamente cualquier fibra de Hub 4 activa que no esté aún en la lista de protocolos
        $registeredFibers = collect($careProtocols)->pluck('fiber')->map(fn($f) => strtolower(trim((string)$f)))->all();
        foreach ($fabrics as $fab) {
            $cleanFabName = strtolower(trim($fab->name));
            if (!in_array($cleanFabName, $registeredFibers)) {
                $careProtocols[] = [
                    'fiber' => $fab->name,
                    'rule' => 'Lavar con agua fría (< 30°C) en ciclo delicado.',
                    'warning' => 'Secar a la sombra en superficie horizontal plana. Evitar secadora térmica.',
                    'symbols' => ['wash_30', 'no_bleach', 'dry_shade', 'no_tumble'],
                ];
                $registeredFibers[] = $cleanFabName;
            }
        }

        $profecoNotice = $officialDocs['PROFECO_SANITARY_NOTICE']->payload ?? [
            'legal_basis' => 'Artículo 53 de la Ley Federal de Protección al Consumidor.',
            'statement' => 'Por motivos estrictos de higiene y protección sanitaria, no se admiten devoluciones ni cambios en prendas íntimas con precinto de seguridad vulnerado.',
            'discreet_shipping' => 'Envíos en empaque 100% neutro y discreto.',
        ];

        // 7.1. Footer Configuration (Hub 2 - Submódulo 2 - Sección 4)
        $defaultFooterConfig = [
            'brand_summary' => 'Plataforma contemporánea de moda íntima, streetwear y autocuidado. Envíos nacionales directos vía paquetería express con empaque 100% neutro y precinto higiénico.',
            'whatsapp_contact' => '+52 55 7510 2015',
            'support_email' => 'hola@mitienda.online',
            'shipping_badge_text' => 'Paqueteria nacional express ($149 / Gratis +$999)',
            'packaging_badge_text' => 'Entrega personal discreta y segura en empaque 100% neutro',
            'payments_badge_text' => 'Pagos 100% seguros con Tarjeta, SPEI y OXXO Pay',
            'copyright_text' => '(C) 2026 MULTIVERSO STORE. Todos los derechos reservados.',
            'show_revops_link' => true,
            'show_zero_emojis_badge' => true,
        ];
        $footerConfig = $officialDocs['STORE_FOOTER_CONFIG']->payload ?? $defaultFooterConfig;

        // 8. Secciones dinámicas y taxonomías de catálogo de Hub 4
        $sections = CmsSection::orderBy('sort_order')->get();
        $categories = Category::whereNull('parent_id')->select('id', 'name', 'slug')->get();
        $subcategories = Category::whereNotNull('parent_id')->select('id', 'name', 'slug', 'parent_id')->get();
        $dbBrands = \App\Models\Brand::active()->pluck('name')->toArray();
        $productBrands = Product::whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand')->toArray();
        $supplierBrands = \App\Models\Supplier::whereNotNull('brands')->get()->pluck('brands')->flatten()->toArray();
        $brands = collect(array_values(array_unique(array_filter(array_merge($dbBrands, $productBrands, $supplierBrands)))))->sort()->values();
        if ($brands->isEmpty()) {
            $brands = collect(['Marte', 'Torzo Denim', 'Venus Intimates', 'Seda & Encaje']);
        }
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

        // Asegurar que todas las dimensiones del catálogo existan y estén configuradas como checkbox
        $allCatalogDimensions = [
            ['filter_key' => 'universe', 'label' => 'Universo', 'display_type' => 'checkbox', 'sort_order' => 1, 'is_visible' => true],
            ['filter_key' => 'category', 'label' => 'Categoría', 'display_type' => 'checkbox', 'sort_order' => 2, 'is_visible' => true],
            ['filter_key' => 'subcategory', 'label' => 'Subcategoría', 'display_type' => 'checkbox', 'sort_order' => 3, 'is_visible' => true],
            ['filter_key' => 'brand', 'label' => 'Colección / Marca', 'display_type' => 'checkbox', 'sort_order' => 4, 'is_visible' => true],
            ['filter_key' => 'supplier', 'label' => 'Proveedores & Distribuidores', 'display_type' => 'checkbox', 'sort_order' => 5, 'is_visible' => true],
            ['filter_key' => 'size', 'label' => 'Talla Anatómica', 'display_type' => 'checkbox', 'sort_order' => 6, 'is_visible' => true],
            ['filter_key' => 'color', 'label' => 'Color & Tornasoles', 'display_type' => 'checkbox', 'sort_order' => 7, 'is_visible' => true],
            ['filter_key' => 'textile_family', 'label' => 'Familia Textil / Fibras', 'display_type' => 'checkbox', 'sort_order' => 8, 'is_visible' => true],
            ['filter_key' => 'garment_type', 'label' => 'Silueta / Tipo de Prenda', 'display_type' => 'checkbox', 'sort_order' => 9, 'is_visible' => true],
            ['filter_key' => 'style_tag', 'label' => 'Estilo & Ocasión', 'display_type' => 'checkbox', 'sort_order' => 10, 'is_visible' => true],
            ['filter_key' => 'price', 'label' => 'Rango de Precio', 'display_type' => 'checkbox', 'sort_order' => 11, 'is_visible' => true],
            ['filter_key' => 'discount', 'label' => 'Descuentos & Ofertas', 'display_type' => 'checkbox', 'sort_order' => 12, 'is_visible' => true],
            ['filter_key' => 'volume', 'label' => 'Volúmenes de Perfumería', 'display_type' => 'checkbox', 'sort_order' => 13, 'is_visible' => true],
            ['filter_key' => 'shade', 'label' => 'Tonos Cosméticos & Shading', 'display_type' => 'checkbox', 'sort_order' => 14, 'is_visible' => true],
        ];
        foreach ($allCatalogDimensions as $dim) {
            $existingDim = CmsCatalogFilter::where('filter_key', $dim['filter_key'])->first();
            if (!$existingDim) {
                CmsCatalogFilter::create($dim);
            } elseif ($existingDim->display_type !== 'checkbox') {
                $existingDim->update(['display_type' => 'checkbox']);
            }
        }
        $catalogFilters = CmsCatalogFilter::orderBy('sort_order')->get();
        $suppliers = \App\Models\Supplier::where('status', '!=', 'archived')->select('id', 'name', 'legal_name')->get();
        if ($suppliers->isEmpty()) {
            $suppliers = collect([
                (object)['id' => 1, 'name' => 'Torzo B2B'],
                (object)['id' => 2, 'name' => 'ModaIntima Textil MX'],
                (object)['id' => 3, 'name' => 'Seamless Tech Apparel'],
                (object)['id' => 4, 'name' => 'Silk & Cotton Iberia SL'],
            ]);
        }

        return view('portal.store', [
            'storefronts' => $storefronts,
            'activeUniverse' => 'marte',
            'announcements' => $announcements,
            'announcementActive' => $announcementActive,
            'banners' => $banners,
            'sections' => $sections,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'suppliers' => $suppliers,
            'brands' => $brands,
            'textileFamilies' => $textileFamilies,
            'garmentTypes' => $garmentTypes,
            'styleTags' => $styleTags,
            'discountOptions' => $discountOptions,
            'shippingRule' => $shippingRule,
            'simulator' => $simulator,
            'sizeGuide' => $sizeGuide,
            'careProtocols' => $careProtocols,
            'fabrics' => \App\Models\ApparelFabric::where('is_active', true)->orderBy('name')->get(),
            'profecoNotice' => $profecoNotice,
            'pages' => $pages,
            'blogPosts' => $blogPosts,
            'tracking' => $tracking,
            'catalogFilters' => $catalogFilters,
            'footerConfig' => $footerConfig,
            'cmsMedia' => \App\Models\CmsMedia::latest()->get(),
            'cmsMediaCount' => \App\Models\CmsMedia::count(),
        ]);
    }

    /**
     * Update shipping rules and free shipping threshold (TASK-INT-18).
     */
    public function updateShippingRule(\App\Http\Requests\Portal\Store\UpdateShippingRuleRequest $request)
    {
        Gate::authorize('manage-cms');
        $validated = $request->validated();

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
    public function toggleBanner(ToggleBannerRequest $request, $bannerId)
    {
        Gate::authorize('manage-cms');
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
    public function toggleAnnouncement(ToggleAnnouncementRequest $request)
    {
        Gate::authorize('manage-cms');
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
    public function updateStorefront(\App\Http\Requests\Portal\Store\UpdateStorefrontRequest $request, $universe)
    {
        Gate::authorize('manage-cms');
        $validated = $request->validated();

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

        // Tipografías de Vitrina (Google Fonts)
        if ($request->filled('typography_heading')) {
            $config->typography_heading = $request->input('typography_heading');
        }
        if ($request->filled('typography_subtitle')) {
            $config->typography_subtitle = $request->input('typography_subtitle');
        }
        if ($request->filled('typography_body')) {
            $config->typography_body = $request->input('typography_body');
        }
        if ($request->filled('typography_accent')) {
            $config->typography_accent = $request->input('typography_accent');
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
            'typography_heading' => $config->typography_heading,
            'typography_subtitle' => $config->typography_subtitle,
            'typography_body' => $config->typography_body,
            'typography_accent' => $config->typography_accent,
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
    public function storeSection(\App\Http\Requests\Portal\Store\StoreSectionRequest $request)
    {
        Gate::authorize('manage-cms');
        $validated = $request->validated();

        $settings = $request->input('settings', []);

        $sectionsUploadDir = public_path('uploads/sections');
        if (!file_exists($sectionsUploadDir)) {
            @mkdir($sectionsUploadDir, 0755, true);
        }

        // Manejo de imagen de fondo subida
        if ($request->hasFile('background_image_file')) {
            $file = $request->file('background_image_file');
            $filename = 'hero_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($sectionsUploadDir, $filename);
            $settings['background_image'] = '/uploads/sections/' . $filename;
        } elseif ($request->filled('background_image_url')) {
            $settings['background_image'] = $request->input('background_image_url');
        }

        // Manejo de video de fondo
        if ($request->hasFile('background_video_file')) {
            $file = $request->file('background_video_file');
            $filename = 'hero_vid_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($sectionsUploadDir, $filename);
            $settings['background_video'] = '/uploads/sections/' . $filename;
        }

        // Manejo de carrusel slides archivos
        if ($request->hasFile('carousel_slide_files')) {
            foreach ($request->file('carousel_slide_files') as $index => $file) {
                if ($file) {
                    $filename = 'carousel_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($sectionsUploadDir, $filename);
                    $settings['carousel_slides'][$index]['image_url'] = '/uploads/sections/' . $filename;
                }
            }
        }
        
        // Re-index carousel_slides just in case they were removed
        if (isset($settings['carousel_slides']) && is_array($settings['carousel_slides'])) {
            $settings['carousel_slides'] = array_values($settings['carousel_slides']);
        }

        $validated['settings'] = $settings;

        $maxOrder = CmsSection::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;
        $validated['status'] = $validated['status'] ?? 'active';

        if (($validated['status'] ?? 'active') === 'active') {
            $universe = $validated['universe'] ?? null;
            $query = \App\Models\CmsSection::where('status', 'active');
            if ($universe) {
                $query->where('universe', $universe);
            }
            if ($query->count() >= 8) {
                return back()->withErrors(['error' => 'Límite máximo de 8 secciones activas alcanzado para preservar el rendimiento SEO.']);
            }
        }


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
    public function updateSection(\App\Http\Requests\Portal\Store\UpdateSectionRequest $request, $id)
    {
        Gate::authorize('manage-cms');
        $section = CmsSection::findOrFail($id);

        $validated = $request->validated();

        if (empty($validated['type'])) {
            $validated['type'] = $section->type;
        }

        $currentSettings = $section->settings ?? [];
        $newSettings = $request->input('settings', []);
        $mergedSettings = array_merge($currentSettings, $newSettings);

        $sectionsUploadDir = public_path('uploads/sections');
        if (!file_exists($sectionsUploadDir)) {
            @mkdir($sectionsUploadDir, 0755, true);
        }

        // Manejo de imagen de fondo subida
        if ($request->hasFile('background_image_file')) {
            $file = $request->file('background_image_file');
            $filename = 'hero_bg_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($sectionsUploadDir, $filename);
            $mergedSettings['background_image'] = '/uploads/sections/' . $filename;
        } elseif ($request->filled('background_image_url')) {
            $mergedSettings['background_image'] = $request->input('background_image_url');
        }

        // Manejo de video de fondo
        if ($request->hasFile('background_video_file')) {
            $file = $request->file('background_video_file');
            $filename = 'hero_vid_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($sectionsUploadDir, $filename);
            $mergedSettings['background_video'] = '/uploads/sections/' . $filename;
        }

        // Re-asignar slides
        if (isset($newSettings['carousel_slides'])) {
            $mergedSettings['carousel_slides'] = $newSettings['carousel_slides'];
        }

        // Manejo de carrusel slides archivos
        if ($request->hasFile('carousel_slide_files')) {
            foreach ($request->file('carousel_slide_files') as $index => $file) {
                if ($file) {
                    $filename = 'carousel_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($sectionsUploadDir, $filename);
                    $mergedSettings['carousel_slides'][$index]['image_url'] = '/uploads/sections/' . $filename;
                }
            }
        }
        
        // Re-index carousel_slides just in case they were removed
        if (isset($mergedSettings['carousel_slides']) && is_array($mergedSettings['carousel_slides'])) {
            $mergedSettings['carousel_slides'] = array_values($mergedSettings['carousel_slides']);
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
    public function archiveSection(ArchiveSectionRequest $request, $id)
    {
        Gate::authorize('manage-cms');
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
    public function reorderSections(ReorderSectionsRequest $request)
    {
        Gate::authorize('manage-cms');
        $validated = $request->validated();

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
    public function toggleSection(ToggleSectionRequest $request, $id)
    {
        Gate::authorize('manage-cms');
        $section = CmsSection::findOrFail($id);
        
        if ($section->status === 'active') {
            // It will become draft, which is fine.
            $section->status = 'draft';
        } else {
            // It will become active. Check limit.
            $universe = $section->universe ?? null;
            $query = \App\Models\CmsSection::where('status', 'active');
            if ($universe) {
                $query->where('universe', $universe);
            }
            if ($query->count() >= 8) {
                return back()->withErrors(['error' => 'Límite máximo de 8 secciones activas alcanzado para preservar el rendimiento SEO.']);
            }
            $section->status = 'active';
        }

        // $section->status = $section->status === 'active' ? 'draft' : 'active';
        $section->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $section->status]);
        }

        return back()->with('success', "Estado de la sección '{$section->name}' actualizado a {$section->status}.");
    }

    /**
     * Delete/archive a section.
     */
    public function destroySection(DestroySectionRequest $request, $id)
    {
        Gate::authorize('manage-cms');
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
    public function updateDocument(\App\Http\Requests\Portal\Store\UpdateDocumentRequest $request, $code)
    {
        Gate::authorize('manage-cms');
        $doc = CmsOfficialDocument::where('code', $code)->firstOrFail();
        $payload = $request->input('payload');

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            }
        }

        if (is_array($payload) && in_array($code, ['CARE_PROTOCOLS', 'care_protocol'])) {
            $payload = array_values($payload);
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
    public function storePage(\App\Http\Requests\Portal\Store\StorePageRequest $request)
    {
        Gate::authorize('manage-cms');
        if ($request->filled('content') && ! $request->filled('content_html')) {
            $request->merge(['content_html' => $request->input('content')]);
        }
        if (! $request->filled('slug') && $request->filled('title')) {
            $request->merge(['slug' => \Illuminate\Support\Str::slug($request->input('title'))]);
        }

        $validated = $request->validated();

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'page_explore_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $validated['image_url'] = '/uploads/pages/' . $filename;
        }

        // Galería de imágenes para carrusel automático
        $gallery = [];
        if (!empty($validated['image_url'])) {
            $gallery[] = $validated['image_url'];
        }
        if ($request->filled('gallery_images_urls')) {
            $lines = preg_split('/[\r\n,]+/', $request->input('gallery_images_urls'));
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && (filter_var($line, FILTER_VALIDATE_URL) || str_starts_with($line, '/uploads/'))) {
                    $gallery[] = $line;
                }
            }
        }
        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $gFile) {
                if ($gFile && $gFile->isValid()) {
                    $gName = 'page_gallery_' . time() . '_' . uniqid() . '.' . $gFile->getClientOriginalExtension();
                    $gFile->move(public_path('uploads/pages'), $gName);
                    $gallery[] = '/uploads/pages/' . $gName;
                }
            }
        }
        if (!empty($gallery)) {
            $validated['gallery_images'] = array_values(array_unique($gallery));
            if (empty($validated['image_url'])) {
                $validated['image_url'] = $gallery[0];
            }
        }

        $validated['show_in_navbar'] = $request->boolean('show_in_navbar');
        $validated['show_in_mobile'] = $request->boolean('show_in_mobile', true);
        $validated['show_in_footer'] = $request->boolean('show_in_footer');
        $validated['status'] = in_array($validated['status'] ?? 'active', ['active', 'paused', 'draft', 'archived']) ? $validated['status'] : 'active';
        $validated['text_color'] = $validated['text_color'] ?? '#ffffff';
        $validated['image_source_mode'] = $validated['image_source_mode'] ?? 'custom';

        $page = CmsPage::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'page' => $page]);
        }

        return back()->with('success', "Página de Explorar '{$page->title}' creada exitosamente.");
    }

    /**
     * Actualizar una página CMS.
     */
    public function updatePage(\App\Http\Requests\Portal\Store\UpdatePageRequest $request, CmsPage $page)
    {
        Gate::authorize('manage-cms');
        if ($request->filled('content') && ! $request->filled('content_html')) {
            $request->merge(['content_html' => $request->input('content')]);
        }

        $validated = $request->validated();

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'page_explore_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pages'), $filename);
            $validated['image_url'] = '/uploads/pages/' . $filename;
        }

        // Galería de imágenes para carrusel automático
        $gallery = is_array($page->gallery_images) ? $page->gallery_images : [];
        if (!empty($validated['image_url']) && !in_array($validated['image_url'], $gallery)) {
            $gallery[] = $validated['image_url'];
        }
        if ($request->filled('gallery_images_urls')) {
            $lines = preg_split('/[\r\n,]+/', $request->input('gallery_images_urls'));
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && (filter_var($line, FILTER_VALIDATE_URL) || str_starts_with($line, '/uploads/'))) {
                    $gallery[] = $line;
                }
            }
        }
        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $gFile) {
                if ($gFile && $gFile->isValid()) {
                    $gName = 'page_gallery_' . time() . '_' . uniqid() . '.' . $gFile->getClientOriginalExtension();
                    $gFile->move(public_path('uploads/pages'), $gName);
                    $gallery[] = '/uploads/pages/' . $gName;
                }
            }
        }
        if (!empty($gallery)) {
            $validated['gallery_images'] = array_values(array_unique($gallery));
            if (empty($validated['image_url'])) {
                $validated['image_url'] = $gallery[0];
            }
        }

        $validated['show_in_navbar'] = $request->boolean('show_in_navbar');
        $validated['show_in_mobile'] = $request->boolean('show_in_mobile');
        $validated['show_in_footer'] = $request->boolean('show_in_footer');
        if (!empty($validated['status'])) {
            $validated['status'] = in_array($validated['status'], ['active', 'paused', 'draft', 'archived']) ? $validated['status'] : 'active';
        }
        if (isset($validated['text_color'])) {
            $validated['text_color'] = $validated['text_color'] ?: '#ffffff';
        }
        if (isset($validated['image_source_mode'])) {
            $validated['image_source_mode'] = $validated['image_source_mode'] ?: 'custom';
        }

        $page->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'page' => $page]);
        }

        return back()->with('success', "Página CMS '{$page->title}' actualizada exitosamente.");
    }

    /**
     * Reordenar páginas de explorar con Drag & Drop.
     */
    public function reorderPages(ReorderPagesRequest $request)
    {
        Gate::authorize('manage-cms');
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            foreach ($validated['order'] as $index => $id) {
                CmsPage::where('id', $id)->update(['navbar_order' => $index + 1]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Orden de páginas de explorar sincronizado correctamente.']);
    }

    /**
     * Alternar estado activo / pausado de una página CMS.
     */
    public function togglePageStatus(CmsPage $page)
    {
        Gate::authorize('manage-cms');
        $newStatus = $page->status === 'active' ? 'paused' : 'active';
        $page->update(['status' => $newStatus]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'status' => $newStatus]);
        }

        return back()->with('success', "Estado de página '{$page->title}' cambiado a " . strtoupper($newStatus) . '.');
    }

    /**
     * Archivar una página CMS.
     */
    public function archivePage(CmsPage $page)
    {
        Gate::authorize('manage-cms');
        $page->update(['status' => 'archived']);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'status' => 'archived']);
        }

        return back()->with('success', "Página CMS '{$page->title}' archivada exitosamente.");
    }

    /**
     * Eliminar una página CMS.
     */
    public function destroyPage(CmsPage $page)
    {
        Gate::authorize('manage-cms');
        $page->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Página CMS eliminada.');
    }

    /**
     * Actualizar los anuncios de la cinta banner (Marquesina).
     */
    public function updateAnnouncements(UpdateAnnouncementsRequest $request)
    {
        Gate::authorize('manage-cms');
        $rawAnnouncements = $request->input('announcements', []);
        $announcementsList = [];

        if (is_array($rawAnnouncements)) {
            foreach ($rawAnnouncements as $index => $item) {
                $text = trim(is_array($item) ? ($item['text'] ?? '') : (string) $item);
                if ($text !== '') {
                    $linkVal = is_array($item) ? trim((string) ($item['link'] ?? ($item['url'] ?? ''))) : '';
                    $announcementsList[] = [
                        'id' => count($announcementsList) + 1,
                        'text' => $text,
                        'link' => $linkVal,
                        'url' => $linkVal,
                        'badge' => is_array($item) ? ($item['badge'] ?? 'INFO') : 'INFO',
                        'active' => is_array($item) ? filter_var($item['active'] ?? false, FILTER_VALIDATE_BOOLEAN) : true,
                    ];
                }
            }
        }

        if (empty($announcementsList)) {
            $announcementsList = [
                ['id' => 1, 'text' => 'Envío gratis nacional en compras mayores a $999 MXN con paqueterías líderes', 'link' => '/catalogo', 'url' => '/catalogo', 'badge' => 'Envío Gratis', 'active' => true],
                ['id' => 2, 'text' => 'Entrega personal segura en Metro Chabacano CDMX (Líneas 2 y 9) sin costo adicional', 'link' => '/chabacano', 'url' => '/chabacano', 'badge' => 'Punto Físico', 'active' => true],
                ['id' => 3, 'text' => 'Nueva Colección Modal Velvet: Máxima suavidad, transpirabilidad y cero rozaduras', 'link' => '/catalogo?tag=velvet', 'url' => '/catalogo?tag=velvet', 'badge' => 'Lanzamiento', 'active' => true],
            ];
        }

        $announcementActive = filter_var($request->input('announcement_active', true), FILTER_VALIDATE_BOOLEAN);

        Cache::forever('cms_announcements', $announcementsList);
        Cache::forever('cms_announcement_active', $announcementActive);

        CmsOfficialDocument::updateOrCreate(
            ['code' => 'STORE_ANNOUNCEMENTS_CONFIG'],
            [
                'title' => 'Cinta Banner Superior (Marquesina)',
                'version' => 'v1.0',
                'is_active' => true,
                'payload' => [
                    'active' => $announcementActive,
                    'items' => $announcementsList,
                ],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'announcement_active' => $announcementActive,
                'announcements' => $announcementsList,
            ]);
        }

        return back()->with('success', 'Cinta banner de anuncios del navbar actualizada exitosamente.');
    }

    /**
     * Crear artículo del blog para Revista Digital.
     */
    public function storeBlogPost(StoreBlogPostRequest $request)
    {
        Gate::authorize('manage-cms');
        $validated = $request->validated();

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
    public function updateBlogPost(UpdateBlogPostRequest $request, CmsBlogPost $post)
    {
        Gate::authorize('manage-cms');
        $validated = $request->validated();

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
        Gate::authorize('manage-cms');
        $post->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Artículo del blog eliminado.');
    }

    /**
     * Actualizar configuración de tracking (GTM, GA4, Meta Pixel & CAPI).
     */
    public function updateTracking(UpdateTrackingRequest $request)
    {
        Gate::authorize('manage-cms');
        $validated = $request->validated();

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
    public function updateFilters(UpdateFiltersRequest $request)
    {
        Gate::authorize('manage-cms');
        $filtersData = $request->input('filters', []);

        foreach ($filtersData as $id => $data) {
            $filter = CmsCatalogFilter::find($id);
            if ($filter) {
                $filter->update([
                    'label' => $data['label'] ?? $filter->label,
                    'display_type' => $data['display_type'] ?? 'checkbox',
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
    /**
     * API: Get CMS Media
     */
    public function apiGetMedia(Request $request)
    {
        Gate::authorize('manage-cms');
        $media = \App\Models\CmsMedia::latest()->get();
        return response()->json(['success' => true, 'media' => $media]);
    }

    /**
     * API: Upload CMS Media
     */
    public function apiUploadMedia(Request $request)
    {
        Gate::authorize('manage-cms');
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);
            $uniqueName = time() . '_' . uniqid() . '_' . $safeName;
            $destPath = public_path('uploads/cms_media');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $uniqueName);

            $media = \App\Models\CmsMedia::create([
                'file_name' => $fileName,
                'file_path' => '/uploads/cms_media/' . $uniqueName,
                'mime_type' => $mimeType,
                'size' => $size,
            ]);

            return response()->json(['success' => true, 'media' => $media]);
        }

        return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
    }

    /**
     * API: Delete CMS Media
     */
    public function apiDeleteMedia(Request $request, $id)
    {
        Gate::authorize('manage-cms');
        $media = \App\Models\CmsMedia::findOrFail($id);
        
        $filePath = public_path(ltrim($media->file_path, '/'));
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        
        $media->delete();
        
        return response()->json(['success' => true]);
    }
}

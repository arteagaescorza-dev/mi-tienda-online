<?php

namespace Database\Seeders;

use App\Models\CmsBlogPost;
use App\Models\CmsCatalogFilter;
use App\Models\CmsOfficialDocument;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsStorefrontConfig;
use App\Models\CmsTrackingSetting;
use Illuminate\Database\Seeder;

class CmsHub2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Configuraciones de vitrinas por Universo
        $universes = [
            'marte' => [
                'universe' => 'marte',
                'brand_name' => 'MARTE',
                'sub_title' => 'UNIVERSO MASCULINO',
                'kicker' => 'Moda Masculina & Sensualidad Ergonómica',
                'hero_tag' => 'MARTE // COLECCIÓN OFICIAL TORZO',
                'hero_title' => 'MODA CONTEMPORÁNEA & ROPA INTERIOR ERGONÓMICA',
                'hero_subtitle' => 'Diseño textil anatómico con modal pima, costuras planas y microfibras de alta transpirabilidad. Envíos express a todo México.',
                'hero_cta_text' => 'EXPLORAR CATÁLOGO',
                'hero_cta_url' => '/productos?universo=marte',
                'page_title' => 'MARTE — Universo Masculino & Multiverso Store',
                'primary_color' => '#09090b',
                'secondary_color' => '#27272a',
                'accent_color' => '#4f46e5',
                'background_color' => '#fafafa',
                'surface_color' => '#ffffff',
                'text_color' => '#0f172a',
            ],
            'venus' => [
                'universe' => 'venus',
                'brand_name' => 'VENUS',
                'sub_title' => 'LENCERÍA & BEAUTY',
                'kicker' => 'Lencería de Autor, Bralettes y Cuidado Íntimo',
                'hero_tag' => 'VENUS // LENCERÍA DE AUTOR & SILUETAS',
                'hero_title' => 'LENCERÍA DE AUTOR & SILUETAS SENSORIALES',
                'hero_subtitle' => 'Encajes finos, satín italiano y fórmulas limpias de cuidado personal pensadas para tu bienestar diario.',
                'hero_cta_text' => 'EXPLORAR COLECCIÓN',
                'hero_cta_url' => '/productos?universo=venus',
                'page_title' => 'VENUS — Lencería de Autor & Beauty',
                'primary_color' => '#1c1917',
                'secondary_color' => '#44403c',
                'accent_color' => '#db2777',
                'background_color' => '#fff1f2',
                'surface_color' => '#ffffff',
                'text_color' => '#1c1917',
            ],
            'tiresias' => [
                'universe' => 'tiresias',
                'brand_name' => 'TIRESIAS',
                'sub_title' => 'MODA AFIRMATIVA',
                'kicker' => 'Binders de Compresión Segura, Packing y Cuidado Afirmativo',
                'hero_tag' => 'TIRESIAS // AFIRMATIVA & NO BINARIO',
                'hero_title' => 'LIBERTAD DE EXPRESIÓN Y AJUSTE SEGURO',
                'hero_subtitle' => 'Prendas afirmativas confeccionadas con ingeniería textil transpirable de compresión médica segura.',
                'hero_cta_text' => 'VER BINDERS Y ACCESORIOS',
                'hero_cta_url' => '/productos?universo=tiresias',
                'page_title' => 'TIRESIAS — Moda Afirmativa & Binders',
                'primary_color' => '#0f172a',
                'secondary_color' => '#334155',
                'accent_color' => '#8b5cf6',
                'background_color' => '#faf5ff',
                'surface_color' => '#ffffff',
                'text_color' => '#0f172a',
            ],
            'terra' => [
                'universe' => 'terra',
                'brand_name' => 'TERRA',
                'sub_title' => 'ECOSISTEMA GLOBAL',
                'kicker' => 'Ecosistema Global de Moda Íntima y Cuidado Personal',
                'hero_tag' => 'TERRA // CATÁLOGO GLOBAL CONSOLIDADO',
                'hero_title' => 'EL ECOSISTEMA COMPLETO DE MODA ÍNTIMA Y ESTILO',
                'hero_subtitle' => 'Catálogo unificado, membresías mensuales recurrentes y fórmulas de autocuidado para todas las identidades.',
                'hero_cta_text' => 'DESCUBRIR TODO EL CATÁLOGO',
                'hero_cta_url' => '/productos',
                'page_title' => 'TERRA — Ecosistema Multiverso Store',
                'primary_color' => '#09090b',
                'secondary_color' => '#18181b',
                'accent_color' => '#0284c7',
                'background_color' => '#f8fafc',
                'surface_color' => '#ffffff',
                'text_color' => '#09090b',
            ],
        ];

        foreach ($universes as $data) {
            CmsStorefrontConfig::updateOrCreate(['universe' => $data['universe']], $data);
        }

        // 2. Secciones del Home PWA Modulares (con orden asignado)
        $sections = [
            [
                'universe' => 'all',
                'name' => 'Hero Editorial Principal',
                'type' => 'hero_carousel',
                'title' => 'Hero Banner Dinámico',
                'subtitle' => 'Banner principal con llamados a la acción dinámicos y guía de tallas.',
                'badge' => 'Principal',
                'sort_order' => 1,
                'status' => 'active',
                'settings' => ['show_cta' => true, 'show_size_guide_btn' => true],
            ],
            [
                'universe' => 'all',
                'name' => 'Pilares de Marca & Courier',
                'type' => 'brand_pillars',
                'title' => 'Garantías y Beneficios',
                'subtitle' => 'Envíos express, transferencias SPEI directas y empaque 100% discreto.',
                'badge' => 'Confianza',
                'sort_order' => 2,
                'status' => 'active',
                'settings' => ['columns' => 3],
            ],
            [
                'universe' => 'all',
                'name' => 'Convenios & Aliados Multiverso',
                'type' => 'editorial_banner',
                'title' => 'Cortesía Especial en Paradiso Club & Circuit CDMX',
                'subtitle' => 'Obtén tu voucher digital con acceso preferencial y trago de cortesía en los mejores venues aliados de la comunidad.',
                'badge' => 'CORTESÍA EXCLUSIVA',
                'sort_order' => 3,
                'status' => 'active',
                'settings' => ['campaign' => 'PARADISO_VIP', 'cta_text' => 'OBTENER MI VOUCHER DIGITAL'],
            ],
            [
                'universe' => 'all',
                'name' => 'Prendas Destacadas del Catálogo',
                'type' => 'curated_products',
                'title' => 'PRENDAS DESTACADAS',
                'subtitle' => 'Curaduría activa para tu universo con stock físico verificado.',
                'badge' => 'En Bodega',
                'sort_order' => 4,
                'status' => 'active',
                'query_rules' => ['visible' => true, 'limit' => 12],
            ],
        ];

        foreach ($sections as $sec) {
            CmsSection::updateOrCreate(
                ['name' => $sec['name'], 'universe' => $sec['universe']],
                $sec
            );
        }

        // 3. Documentos Oficiales
        $officialDocs = [
            [
                'code' => 'SIZE_GUIDE_TORZO',
                'title' => 'Guía Anatómica Oficial de Tallas Textiles Torzo',
                'version' => '2026.1',
                'payload' => [
                    ['size' => 'CH', 'waistCm' => '71 - 76 cm', 'hipsCm' => '86 - 91 cm', 'pantsSize' => '28 - 30', 'isCore' => false, 'advice' => 'Ajuste ceñido anatómico'],
                    ['size' => 'M', 'waistCm' => '77 - 84 cm', 'hipsCm' => '92 - 99 cm', 'pantsSize' => '31 - 33', 'isCore' => true, 'advice' => 'Talla Core más vendida (42% de demanda)'],
                    ['size' => 'G', 'waistCm' => '85 - 92 cm', 'hipsCm' => '100 - 107 cm', 'pantsSize' => '34 - 36', 'isCore' => true, 'advice' => 'Talla Core más vendida (34% de demanda)'],
                    ['size' => 'XL', 'waistCm' => '93 - 100 cm', 'hipsCm' => '108 - 115 cm', 'pantsSize' => '36 - 38', 'isCore' => false, 'advice' => 'Soporte holgado y elásticos flexibles'],
                    ['size' => '2XL', 'waistCm' => '101 - 109 cm', 'hipsCm' => '116 - 123 cm', 'pantsSize' => '38 - 40', 'isCore' => false, 'advice' => 'Tiro extra confortable'],
                    ['size' => '3XL', 'waistCm' => '110 - 120 cm', 'hipsCm' => '124 - 134 cm', 'pantsSize' => '40 - 44', 'isCore' => false, 'advice' => 'Máxima amplitud de cadera'],
                ],
            ],
            [
                'code' => 'CARE_PROTOCOLS',
                'title' => 'Protocolos Oficiales de Preservación Textil',
                'version' => '2026.1',
                'payload' => [
                    ['fiber' => 'Modal & Viscosa de Bambú', 'rule' => 'Lavar con agua fría (< 30°C) en ciclo delicado dentro de la Bolsa de Malla Protectora Terra.', 'warning' => 'Nunca usar secadora ni blanqueadores clorados; secar a la sombra en superficie plana.'],
                    ['fiber' => 'Microfibra Velvet & Elásticos de Jockstraps', 'rule' => 'Lavar a mano o máquina en bolsa cerrada para evitar que los elásticos se enganchen con cremalleras.', 'warning' => 'El calor excesivo degrada el elastano; no planchar directamente sobre los elásticos.'],
                    ['fiber' => 'Binders de Compresión Médica', 'rule' => 'Lavar cada 2-3 puestas con jabón líquido suave. Secar al aire sin retorcer la tela de compresión.', 'warning' => 'No estirar de forma asimétrica al escurrir para preservar la memoria elástica del tejido.'],
                ],
            ],
            [
                'code' => 'PROFECO_SANITARY_NOTICE',
                'title' => 'Aviso Sanitario y Normativa de Higiene PROFECO',
                'version' => '2026.1',
                'payload' => [
                    'legal_basis' => 'Artículo 53 de la Ley Federal de Protección al Consumidor y NOM-004-SE-2021.',
                    'statement' => 'Por motivos estrictos de higiene y protección sanitaria, no se admiten devoluciones ni cambios en prendas íntimas y ropa interior cuyo precinto termosellado de seguridad haya sido abierto, removido o vulnerado.',
                    'discreet_shipping' => 'Todos nuestros paquetes se envían en cajas neutras reciclables sin logotipos explícitos, garantizando total privacidad.',
                ],
            ],
        ];

        foreach ($officialDocs as $doc) {
            CmsOfficialDocument::updateOrCreate(['code' => $doc['code']], $doc);
        }

        // 4. Páginas institucionales sincronizadas en el Navbar
        $pages = [
            [
                'slug' => 'guia-tallas',
                'title' => 'Guía de Tallas',
                'icon' => 'ruler',
                'content_html' => '<h2>Guía Anatómica de Tallas Torzo</h2><p>Encuentra tu calce perfecto midiendo tu cintura y cadera con una cinta métrica flexible.</p>',
                'show_in_navbar' => true,
                'navbar_order' => 1,
                'show_in_footer' => true,
                'footer_order' => 1,
                'seo_title' => 'Guía de Tallas Anatómica | Tienda Oficial',
                'seo_description' => 'Consulta la tabla completa de medidas en cm para todas nuestras prendas interiores masculinas y afirmativas.',
                'status' => 'active',
            ],
            [
                'slug' => 'cuidados',
                'title' => 'Cuidado de Prendas',
                'icon' => 'shield-check',
                'content_html' => '<h2>Protocolo de Cuidado de Ropa Delicada</h2><p>Maximiza la durabilidad de tus prendas de modal, bambú y microfibra siguiendo nuestras instrucciones oficiales.</p>',
                'show_in_navbar' => true,
                'navbar_order' => 2,
                'show_in_footer' => true,
                'footer_order' => 2,
                'seo_title' => 'Protocolos de Cuidado Textil | Tienda Oficial',
                'seo_description' => 'Aprende cómo lavar y cuidar tus prendas íntimas de alta tecnología textil para preservar su elasticidad.',
                'status' => 'active',
            ],
            [
                'slug' => 'revista',
                'title' => 'Revista Digital',
                'icon' => 'book-open',
                'content_html' => '<h2>Revista Digital Multiverso</h2><p>Artículos sobre bienestar, tecnología textil y tendencias de diseño.</p>',
                'show_in_navbar' => true,
                'navbar_order' => 3,
                'show_in_footer' => true,
                'footer_order' => 3,
                'seo_title' => 'Revista Digital & Estilo | Tienda Oficial',
                'seo_description' => 'Artículos exclusivos, guías de estilo íntimo y tendencias en diseño textil ergonómico.',
                'status' => 'active',
            ],
            [
                'slug' => 'privacidad',
                'title' => 'Privacidad & Empaque',
                'icon' => 'lock',
                'content_html' => '<h2>Privacidad y Empaque Seguro</h2><p>Tu privacidad es nuestra prioridad absoluta. Todos los envíos se realizan con empaque 100% neutro y precinto de seguridad.</p>',
                'show_in_navbar' => false,
                'navbar_order' => 4,
                'show_in_footer' => true,
                'footer_order' => 4,
                'seo_title' => 'Política de Privacidad & Empaque Discreto',
                'seo_description' => 'Conoce nuestra garantía de empaque 100% neutro y términos de confidencialidad.',
                'status' => 'active',
            ],
        ];

        foreach ($pages as $page) {
            CmsPage::updateOrCreate(['slug' => $page['slug']], $page);
        }

        // 5. Artículos del Blog para Revista Digital
        $blogPosts = [
            [
                'slug' => 'modal-vs-bambu-cual-es-la-mejor-fibra-para-ropa-interior',
                'title' => 'Modal vs. Bambú: ¿Cuál es la fibra definitiva para ropa interior de alto rendimiento?',
                'excerpt' => 'Analizamos la transpirabilidad, suavidad y durabilidad de las dos fibras ecológicas líderes en moda íntima ergonómica.',
                'body' => '<p>Cuando se trata de ropa interior masculina y prendas afirmativas, la elección del tejido determina no solo la comodidad diaria, sino también la salud de la piel y la durabilidad de la prenda frente a constantes ciclos de lavado.</p><h3>¿Por qué el Modal Pima domina el mercado ergonómico?</h3><p>El modal extraído de la madera de haya ofrece un coeficiente de suavidad hasta 3 veces superior al algodón convencional, manteniendo su textura aterciopelada lavado tras lavado.</p>',
                'category_name' => 'Tecnología Textil',
                'tags' => ['modal', 'bambú', 'sostenibilidad', 'cuidado íntimo'],
                'author_name' => 'Equipo Editorial Torzo',
                'reading_time_minutes' => 4,
                'published_at' => now(),
                'status' => 'published',
            ],
            [
                'slug' => 'guia-de-ajuste-anatomico-como-elegir-tu-talla-exacta',
                'title' => 'Guía de Ajuste Anatómico: Cómo medir tu cuerpo para elegir la talla perfecta',
                'excerpt' => 'El error más común es elegir una talla basada únicamente en el pantalón. Te enseñamos a medir cintura y cadera correctamente.',
                'body' => '<p>Un calce ergonómico debe abrazar la anatomía sin generar puntos de presión excesivos en la cintura o las ingles. En esta guía paso a paso te mostramos cómo tomar tus medidas exactas con cinta métrica.</p>',
                'category_name' => 'Guías & Bienestar',
                'tags' => ['tallas', 'ergonomía', 'guía'],
                'author_name' => 'Diseño & Patronaje Torzo',
                'reading_time_minutes' => 3,
                'published_at' => now(),
                'status' => 'published',
            ],
        ];

        foreach ($blogPosts as $post) {
            CmsBlogPost::updateOrCreate(['slug' => $post['slug']], $post);
        }

        // 6. Filtros del Catálogo
        $filters = [
            ['filter_key' => 'universe', 'label' => 'Universo', 'display_type' => 'pills', 'sort_order' => 1, 'is_visible' => true],
            ['filter_key' => 'brand', 'label' => 'Colección / Marca', 'display_type' => 'checkbox', 'sort_order' => 2, 'is_visible' => true],
            ['filter_key' => 'category', 'label' => 'Categoría', 'display_type' => 'checkbox', 'sort_order' => 3, 'is_visible' => true],
            ['filter_key' => 'size', 'label' => 'Talla Anatómica', 'display_type' => 'pills', 'sort_order' => 4, 'is_visible' => true],
            ['filter_key' => 'price', 'label' => 'Rango de Precio', 'display_type' => 'range', 'sort_order' => 5, 'is_visible' => true],
        ];

        foreach ($filters as $filt) {
            CmsCatalogFilter::updateOrCreate(['filter_key' => $filt['filter_key']], $filt);
        }

        // 7. Configuración de Analítica inicial
        CmsTrackingSetting::firstOrCreate([], [
            'gtm_id' => null,
            'ga4_measurement_id' => null,
            'facebook_pixel_id' => null,
            'is_active' => false,
        ]);
    }
}

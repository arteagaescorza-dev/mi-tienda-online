<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PortalStoreController extends Controller
{
    public function index(Request $request)
    {
        $storefronts = [
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
                'activeSkus' => 8,
                'sampleProducts' => [
                    ['name' => 'Boxer Brief Modal Negro', 'price' => 289, 'sku' => 'TOR-DRP-001', 'tag' => 'Core Talla M/G'],
                    ['name' => 'Jockstrap Deportivo Azul Marino', 'price' => 249, 'sku' => 'TOR-DRP-002', 'tag' => 'Elástico Ancho'],
                    ['name' => 'Arnés Pectoral Velvet Elástico', 'price' => 349, 'sku' => 'MAR-ACC-001', 'tag' => 'Ajustable']
                ]
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
                'activeSkus' => 6,
                'sampleProducts' => [
                    ['name' => 'Bralette Encaje Floral Sin Varilla', 'price' => 389, 'sku' => 'VEN-TXT-001', 'tag' => 'Copas Suaves'],
                    ['name' => 'Panty Tiro Alto Satín Palo de Rosa', 'price' => 219, 'sku' => 'VEN-TXT-002', 'tag' => 'Sin Costuras'],
                    ['name' => 'Aceite Corporal Hidratante Jazmín 50ml', 'price' => 299, 'sku' => 'VEN-COS-001', 'tag' => 'PAO 12M']
                ]
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
                'activeSkus' => 5,
                'sampleProducts' => [
                    ['name' => 'Binder Compresión Pecho Cuello V', 'price' => 549, 'sku' => 'TIR-TXT-001', 'tag' => 'Compresión Segura'],
                    ['name' => 'Faja Moldeadora Tucking Transpirable', 'price' => 329, 'sku' => 'TIR-TXT-002', 'tag' => 'Algodón Orgánico'],
                    ['name' => 'Bálsamo Calmante Post-Compresión 60ml', 'price' => 249, 'sku' => 'TIR-COS-001', 'tag' => 'pH Neutro']
                ]
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
                'activeSkus' => 19,
                'sampleProducts' => [
                    ['name' => 'Caja Suscripción Nivel 2 Cuidado & Textil', 'price' => 599, 'sku' => 'SUB-BOX-NV2', 'tag' => 'Combo Mensual'],
                    ['name' => 'Bolsa de Lavado para Ropa Delicada Terra', 'price' => 120, 'sku' => 'ACC-WSH-BAG', 'tag' => 'Malla Protectora'],
                    ['name' => 'Jabón Íntimo Líquido pH Neutro 250ml', 'price' => 189, 'sku' => 'TER-COS-001', 'tag' => 'Unisex Orgánico']
                ]
            ]
        ];

        $announcements = [
            ['id' => 1, 'text' => 'Envío gratis nacional en compras mayores a $999 MXN con paqueterías líderes', 'link' => '/catalogo', 'badge' => 'Envío Gratis', 'active' => true],
            ['id' => 2, 'text' => 'Entrega personal segura en Metro Chabacano CDMX (Líneas 2 y 9) sin costo adicional', 'link' => '/chabacano', 'badge' => 'Punto Físico', 'active' => true],
            ['id' => 3, 'text' => 'Nueva Colección Modal Velvet: Máxima suavidad, transpirabilidad y cero rozaduras', 'link' => '/catalogo?tag=velvet', 'badge' => 'Lanzamiento', 'active' => true]
        ];

        $banners = [
            ['id' => 1, 'title' => 'Colección Verano Circuit 2026', 'subtitle' => 'Jocks y boxers de secado ultra-rápido en colores neón y tornasol', 'badge' => 'Marte Masculino', 'universe' => 'marte', 'placement' => 'hero_carousel', 'imageUrl' => 'marte_summer_circuit.webp', 'linkUrl' => '/catalogo?universo=marte&col=verano', 'sortOrder' => 1, 'active' => true],
            ['id' => 2, 'title' => 'Lencería de Autor en Seda y Bambú', 'subtitle' => 'Piezas diseñadas para acariciar tu piel con soporte natural sin varillas', 'badge' => 'Venus Femenino', 'universe' => 'venus', 'placement' => 'hero_carousel', 'imageUrl' => 'venus_silk_bamboo.webp', 'linkUrl' => '/catalogo?universo=venus&col=seda', 'sortOrder' => 2, 'active' => true],
            ['id' => 3, 'title' => 'Binders Transpirables de Nueva Generación', 'subtitle' => 'Confección multicapa que distribuye la presión ergonómica sin asfixia', 'badge' => 'Tiresias Afirmativo', 'universe' => 'tiresias', 'placement' => 'hero_carousel', 'imageUrl' => 'tiresias_binders_gen3.webp', 'linkUrl' => '/catalogo?universo=tiresias&cat=binders', 'sortOrder' => 3, 'active' => true],
            ['id' => 4, 'title' => 'Membresía Mensual: Tu Caja Curada en Casa', 'subtitle' => 'Ahorra hasta 35% recibiendo prendas exclusivas y reposición cosmética', 'badge' => 'Terra Suscripciones', 'universe' => 'terra', 'placement' => 'category_promo', 'imageUrl' => 'terra_sub_box.webp', 'linkUrl' => '/suscripciones', 'sortOrder' => 4, 'active' => true],
            ['id' => 5, 'title' => 'Convenio Especial: Descuento en Cover Paradiso Circuit', 'subtitle' => 'Regístrate y recibe tu código VIP exclusivo para la taquilla del festival', 'badge' => 'Alianza Marte', 'universe' => 'marte', 'placement' => 'category_promo', 'imageUrl' => 'paradiso_collab.webp', 'linkUrl' => '/aliados/paradiso', 'sortOrder' => 5, 'active' => true],
            ['id' => 6, 'title' => 'Bolsa de Lavado Terra: Protege tus Prendas Delicadas', 'subtitle' => 'Gratis en compras superiores a $1,200 MXN o en tu primera caja mensual', 'badge' => 'Cuidado Textil', 'universe' => 'all', 'placement' => 'footer_strip', 'imageUrl' => 'wash_bag_promo.webp', 'linkUrl' => '/producto/ACC-WSH-BAG', 'sortOrder' => 6, 'active' => true]
        ];

        $shippingRule = [
            'freeShippingThreshold' => 999.00,
            'flatRate' => 149.00,
            'localPickupEnabled' => true,
            'localPickupLocation' => 'Metro Chabacano CDMX (Líneas 2 y 9 - Pasillo de transbordo)',
            'localPickupSchedule' => 'Lunes a Viernes 16:00 a 19:00 hrs',
            'status' => 'active'
        ];

        $simulator = [
            'currentThreshold' => 999.00,
            'projectedOrders' => 120,
            'freeShippingRatio' => 42.0,
            'totalShippingAbsorbed' => 7308.00,
            'projectedEbitdaMargin' => 44.20,
            'isHealthy' => true
        ];

        $sizeGuide = [
            ['size' => 'CH', 'waistCm' => '71 - 76 cm', 'hipsCm' => '86 - 91 cm', 'pantsSize' => '28 - 30', 'isCore' => false, 'advice' => 'Ajuste ceñido anatómico'],
            ['size' => 'M', 'waistCm' => '77 - 84 cm', 'hipsCm' => '92 - 99 cm', 'pantsSize' => '31 - 33', 'isCore' => true, 'advice' => 'Talla Core más vendida (42% de demanda)'],
            ['size' => 'G', 'waistCm' => '85 - 92 cm', 'hipsCm' => '100 - 107 cm', 'pantsSize' => '34 - 36', 'isCore' => true, 'advice' => 'Talla Core más vendida (34% de demanda)'],
            ['size' => 'XL', 'waistCm' => '93 - 100 cm', 'hipsCm' => '108 - 115 cm', 'pantsSize' => '36 - 38', 'isCore' => false, 'advice' => 'Soporte holgado y elásticos flexibles'],
            ['size' => '2XL', 'waistCm' => '101 - 109 cm', 'hipsCm' => '116 - 123 cm', 'pantsSize' => '38 - 40', 'isCore' => false, 'advice' => 'Tiro extra confortable'],
            ['size' => '3XL', 'waistCm' => '110 - 120 cm', 'hipsCm' => '124 - 134 cm', 'pantsSize' => '40 - 44', 'isCore' => false, 'advice' => 'Máxima amplitud de cadera']
        ];

        $careProtocols = [
            ['fiber' => 'Modal & Viscosa de Bambú', 'rule' => 'Lavar con agua fría (< 30°C) en ciclo delicado dentro de la Bolsa de Malla Protectora Terra.', 'warning' => 'Nunca usar secadora ni blanqueadores clorados; secar a la sombra en superficie plana.'],
            ['fiber' => 'Microfibra Velvet & Elásticos de Jockstraps', 'rule' => 'Lavar a mano o máquina en bolsa cerrada para evitar que los elásticos se enganchen con cremalleras.', 'warning' => 'El calor excesivo degrada el elastano; no planchar directamente sobre los elásticos.'],
            ['fiber' => 'Binders de Compresión Médica', 'rule' => 'Lavar cada 2-3 puestas con jabón líquido suave. Secar al aire sin retorcer la tela de compresión.', 'warning' => 'No estirar de forma asimétrica al escurrir para preservar la memoria elástica del tejido.']
        ];

        return view('portal.store', [
            'storefronts' => $storefronts,
            'activeUniverse' => 'marte',
            'announcements' => $announcements,
            'announcementActive' => true,
            'banners' => $banners,
            'shippingRule' => $shippingRule,
            'simulator' => $simulator,
            'sizeGuide' => $sizeGuide,
            'careProtocols' => $careProtocols,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;

class PortalMarketingController extends Controller
{
    public function index(Request $request)
    {
        $dbLeads = Lead::latest()->get();

        $defaultLeads = [
            [
                'id' => 'lead-001',
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.m@example.com',
                'phone' => '+52 81 1234 5678',
                'universe' => 'marte',
                'stage' => 'subscriber',
                'ltv' => 3450,
                'ordersCount' => 4,
                'score' => 95,
                'lastInteraction' => 'Hoy 10:15 - Abrió correo de membresía',
                'preferredSizes' => ['Boxers' => 'M', 'Playeras' => 'L'],
                'notes' => 'Suscriptor recurrente mensual de vitrina Marte. Alta afinidad con textiles de bambú.'
            ],
            [
                'id' => 'lead-002',
                'name' => 'Valeria Gómez',
                'email' => 'valeria.g@example.com',
                'phone' => '+52 33 9876 5432',
                'universe' => 'venus',
                'stage' => 'customer',
                'ltv' => 1890,
                'ordersCount' => 2,
                'score' => 88,
                'lastInteraction' => 'Ayer 18:30 - Consulta de talla por WhatsApp',
                'preferredSizes' => ['Bralette' => '34B', 'Panty' => 'S'],
                'notes' => 'Compró Pack Lencería Venus. Excelente feedback de suavidad.'
            ],
            [
                'id' => 'lead-003',
                'name' => 'Alex Rivera',
                'email' => 'alex.r@example.com',
                'phone' => '+52 55 4567 8901',
                'universe' => 'tiresias',
                'stage' => 'abandoned',
                'ltv' => 699,
                'ordersCount' => 1,
                'score' => 74,
                'lastInteraction' => 'Hace 3 horas - Dejó Binder Ergonómico en carrito',
                'preferredSizes' => ['Binder' => 'M (Compresión Media)'],
                'notes' => 'Abandono en paso de envío. Se disparó flujo de recuperación automatizado (TASK-INT-05).'
            ],
            [
                'id' => 'lead-004',
                'name' => 'Diego Navarro',
                'email' => 'diego.n@example.com',
                'phone' => '+52 44 2345 6789',
                'universe' => 'terra',
                'stage' => 'prospect',
                'ltv' => 0,
                'ordersCount' => 0,
                'score' => 62,
                'lastInteraction' => 'Hace 1 día - Clic en Anuncio Meta Ads',
                'preferredSizes' => ['Esenciales' => 'L'],
                'notes' => 'Registro reciente desde campaña de Meta CAPI. Aún no completa primer checkout.'
            ],
            [
                'id' => 'lead-005',
                'name' => 'Camila Torres',
                'email' => 'camila.t@example.com',
                'phone' => '+52 22 8765 4321',
                'universe' => 'venus',
                'stage' => 'churn',
                'ltv' => 2100,
                'ordersCount' => 3,
                'score' => 45,
                'lastInteraction' => 'Hace 42 días - Tarjeta rechazada en renovación',
                'preferredSizes' => ['Skincare' => 'Tónico 100ml', 'Lencería' => 'M'],
                'notes' => 'Alerta de churn en radar. Pago declinado por banco emisor. Requiere dunning preventivo.'
            ],
            [
                'id' => 'lead-006',
                'name' => 'Mateo Fernández',
                'email' => 'mateo.f@example.com',
                'phone' => '+52 99 3456 7890',
                'universe' => 'marte',
                'stage' => 'customer',
                'ltv' => 1250,
                'ordersCount' => 1,
                'score' => 82,
                'lastInteraction' => 'Hoy 08:45 - Preguntó por reposición de fragancia',
                'preferredSizes' => ['Fragancias' => '50ml', 'Ropa Interior' => 'G'],
                'notes' => 'Hot lead listo para cross-sell de perfume sólido Marte.'
            ]
        ];

        // Format and merge DB leads if available
        $leads = $defaultLeads;
        if ($dbLeads->isNotEmpty()) {
            foreach ($dbLeads as $dbLead) {
                $stageMap = [
                    'new' => 'prospect',
                    'contacted' => 'prospect',
                    'qualified' => 'customer',
                    'proposal' => 'abandoned',
                    'won' => 'subscriber',
                    'lost' => 'churn'
                ];
                $mappedStage = $stageMap[$dbLead->status] ?? 'prospect';
                $leads[] = [
                    'id' => 'lead-db-' . $dbLead->id,
                    'name' => $dbLead->name ?? 'Lead #' . $dbLead->id,
                    'email' => $dbLead->email ?? 'contacto@ejemplo.com',
                    'phone' => $dbLead->phone ?? '+52 55 0000 0000',
                    'universe' => $dbLead->source === 'Instagram' ? 'venus' : ($dbLead->source === 'WhatsApp' ? 'marte' : 'terra'),
                    'stage' => $mappedStage,
                    'ltv' => $mappedStage === 'subscriber' ? 2400 : ($mappedStage === 'customer' ? 1200 : 0),
                    'ordersCount' => $mappedStage === 'subscriber' ? 3 : ($mappedStage === 'customer' ? 1 : 0),
                    'score' => $mappedStage === 'subscriber' ? 90 : ($mappedStage === 'customer' ? 80 : 60),
                    'lastInteraction' => $dbLead->updated_at ? $dbLead->updated_at->diffForHumans() : 'Reciente',
                    'preferredSizes' => ['General' => 'M'],
                    'notes' => $dbLead->notes ?? 'Contacto captado vía canal ' . ($dbLead->source ?? 'Omnicanal')
                ];
            }
        }

        // Summary metrics
        $totalLeadsCount = count($leads);
        $hotLeadsCount = count(array_filter($leads, fn($l) => $l['score'] >= 80));
        $activeSubscribersCount = count(array_filter($leads, fn($l) => $l['stage'] === 'subscriber'));
        $activeCustomersCount = count(array_filter($leads, fn($l) => $l['ordersCount'] > 0));

        // Conversations for Chatbot Marteaga
        $conversations = [
            [
                'id' => 'chat-001',
                'customerName' => 'Rodrigo Méndez',
                'customerPhone' => '+52 55 7890 1234',
                'channel' => 'whatsapp',
                'intent' => 'Asesoría de Talla Boxer',
                'status' => 'bot_active',
                'unread' => true,
                'lastMessageTime' => '11:42',
                'messages' => [
                    ['sender' => 'user', 'text' => 'Hola, mido 1.80 y peso 78kg, ¿qué talla de boxer de bambú me recomiendan?', 'time' => '11:40'],
                    ['sender' => 'bot', 'text' => '¡Hola Rodrigo! Para tu complexión de 1.80m y 78kg (cintura aprox 32-33"), la talla **Mediana (M)** en nuestra fibra de bambú Marte te brindará un ajuste ergonómico perfecto sin apretar.', 'time' => '11:41'],
                    ['sender' => 'user', 'text' => 'Excelente, ¿tienen el pack triple en color negro y gris disponible para envío hoy?', 'time' => '11:42']
                ]
            ],
            [
                'id' => 'chat-002',
                'customerName' => 'Fernanda Castillo',
                'customerPhone' => '+52 81 8765 4321',
                'channel' => 'webchat',
                'intent' => 'Rastreo de Guía DHL',
                'status' => 'resolved',
                'unread' => false,
                'lastMessageTime' => '10:15',
                'messages' => [
                    ['sender' => 'user', 'text' => 'Hola, quiero saber el estatus de mi pedido #ORD-1048', 'time' => '10:12'],
                    ['sender' => 'bot', 'text' => '¡Hola Fernanda! Tu pedido #ORD-1048 con guía DHL Express **DHL-8492048192** se encuentra en ruta de entrega en Monterrey. Llegará hoy antes de las 18:00 hrs.', 'time' => '10:13'],
                    ['sender' => 'user', 'text' => 'Perfecto, muchas gracias por la rapidez.', 'time' => '10:15']
                ]
            ],
            [
                'id' => 'chat-003',
                'customerName' => 'Lucía Alarcón',
                'customerPhone' => '+52 33 6543 2109',
                'channel' => 'whatsapp',
                'intent' => 'Cambio de Talla RMA',
                'status' => 'human_takeover',
                'unread' => false,
                'lastMessageTime' => '09:20',
                'messages' => [
                    ['sender' => 'user', 'text' => 'Buenas tardes, me quedó un poco chica la copa del bralette Venus, ¿puedo cambiarlo?', 'time' => '09:15'],
                    ['sender' => 'bot', 'text' => 'Te transfiero con un asesor de post-venta para gestionar tu garantía con sello higiénico.', 'time' => '09:16'],
                    ['sender' => 'agent', 'text' => 'Hola Lucía, soy Ana de Atención al Cliente. Con gusto te generamos la guía de retorno para el cambio a talla 36B.', 'time' => '09:20']
                ]
            ]
        ];

        // Email Automations
        $emailAutomations = [
            [
                'id' => 'auto-001',
                'title' => 'Bienvenida Multiverso & Doble Opt-In',
                'trigger' => 'Registro de cuenta o Newsletter',
                'delay' => 'Inmediato (0 min)',
                'sentCount' => 1240,
                'openRate' => 58.4,
                'revenueAttributed' => 34200,
                'isActive' => true
            ],
            [
                'id' => 'auto-002',
                'title' => 'Recuperación Carrito Abandonado (TASK-INT-05)',
                'trigger' => 'Carrito con checkout incompleto',
                'delay' => '30 min y 24 hrs tokenizado',
                'sentCount' => 890,
                'openRate' => 64.2,
                'revenueAttributed' => 52400,
                'isActive' => true
            ],
            [
                'id' => 'auto-003',
                'title' => 'Reposición de Cosmética & Cuidado Íntimo',
                'trigger' => 'Día 35 posterior a entrega de orden',
                'delay' => '35 días exactos',
                'sentCount' => 620,
                'openRate' => 42.1,
                'revenueAttributed' => 21800,
                'isActive' => true
            ],
            [
                'id' => 'auto-004',
                'title' => 'Aviso Preventivo de Cobro de Membresía',
                'trigger' => '72 hrs previas al corte mensual (día 1/15)',
                'delay' => '3 días antes de corte',
                'sentCount' => 1590,
                'openRate' => 46.8,
                'revenueAttributed' => 18400,
                'isActive' => true
            ]
        ];

        // Coupons
        $coupons = [
            [
                'id' => 'coup-001',
                'code' => 'BIENVENIDA10',
                'type' => 'percent',
                'value' => 10,
                'minAmount' => 0,
                'universe' => 'terra',
                'maxUses' => 500,
                'usedCount' => 184,
                'isActive' => true,
                'expiresAt' => '2026-12-31',
                'revenue' => 46200
            ],
            [
                'id' => 'coup-002',
                'code' => 'MARTEPACK',
                'type' => 'percent',
                'value' => 15,
                'minAmount' => 699,
                'universe' => 'marte',
                'maxUses' => 200,
                'usedCount' => 89,
                'isActive' => true,
                'expiresAt' => '2026-10-31',
                'revenue' => 78500
            ],
            [
                'id' => 'coup-003',
                'code' => 'VENUSFREE',
                'type' => 'free_shipping',
                'value' => 0,
                'minAmount' => 899,
                'universe' => 'venus',
                'maxUses' => 300,
                'usedCount' => 112,
                'isActive' => true,
                'expiresAt' => '2026-11-15',
                'revenue' => 98400
            ],
            [
                'id' => 'coup-004',
                'code' => 'TIRESIASVIP',
                'type' => 'percent',
                'value' => 20,
                'minAmount' => 1200,
                'universe' => 'tiresias',
                'maxUses' => 100,
                'usedCount' => 42,
                'isActive' => true,
                'expiresAt' => '2026-12-15',
                'revenue' => 11200
            ]
        ];

        // Ads & Meta CAPI Telemetry (TASK-MKT-01)
        $adsMetrics = [
            'spendToday' => 5840,
            'platformRoas' => 4.6,
            'netRealRoas' => 3.4,
            'cpa' => 44.50,
            'purchases' => 48,
            'capiHealthScore' => 9.8,
            'lastCatalogSync' => 'Sincronizado hace 15 min (10 SKUs en sync)'
        ];

        $adCampaigns = [
            [
                'id' => 'meta-001',
                'name' => 'Meta Ads - Boxers Bambú Marte (Feed & Stories)',
                'universe' => 'marte',
                'status' => 'active',
                'dailyBudget' => 2400,
                'spendToday' => 2180,
                'platformRoas' => 4.8,
                'netRealRoas' => 3.6,
                'cpa' => 42.50,
                'ctr' => 4.2,
                'fatigueAlert' => false
            ],
            [
                'id' => 'meta-002',
                'name' => 'Meta Ads - Lencería Sensual Venus (Reels)',
                'universe' => 'venus',
                'status' => 'active',
                'dailyBudget' => 1800,
                'spendToday' => 1650,
                'platformRoas' => 3.2,
                'netRealRoas' => 2.3,
                'cpa' => 68.00,
                'ctr' => 1.8,
                'fatigueAlert' => true
            ],
            [
                'id' => 'google-001',
                'name' => 'Google Ads - Binders Ergonómicos Tiresias (Search)',
                'universe' => 'tiresias',
                'status' => 'active',
                'dailyBudget' => 1000,
                'spendToday' => 890,
                'platformRoas' => 5.4,
                'netRealRoas' => 4.1,
                'cpa' => 38.00,
                'ctr' => 6.8,
                'fatigueAlert' => false
            ],
            [
                'id' => 'meta-003',
                'name' => 'Meta Ads - Esenciales Multiverso Terra (Catálogo)',
                'universe' => 'terra',
                'status' => 'active',
                'dailyBudget' => 600,
                'spendToday' => 540,
                'platformRoas' => 3.9,
                'netRealRoas' => 2.9,
                'cpa' => 52.00,
                'ctr' => 3.8,
                'fatigueAlert' => false
            ]
        ];

        $growthRecommendations = [
            [
                'id' => 'rec-mkt-1',
                'title' => 'Escalar Campaña Boxers Bambú en Meta Ads (+20%)',
                'summary' => 'El anuncio tiene ROAS reportado de 4.8x (ROAS Real 3.6x) y CPA de $42.50 MXN con excelente conversión en vitrina Marte.',
                'actionLabel' => 'Escalar Presupuesto (+20%)',
                'actionType' => 'scale_campaign',
                'campaignId' => 'meta-001',
                'executed' => false
            ],
            [
                'id' => 'rec-mkt-2',
                'title' => 'Pausar Anuncio Lencería Venus por Fatiga de Creativo',
                'summary' => 'El CTR cayó 35% esta semana y el CPA subió a $68.00 MXN. Se recomienda pausar y refrescar con nuevo video reel.',
                'actionLabel' => 'Pausar Anuncio en 1 Clic',
                'actionType' => 'pause_campaign',
                'campaignId' => 'meta-002',
                'executed' => false
            ],
            [
                'id' => 'rec-mkt-3',
                'title' => 'Generar Copies Persuasivos con IA para Campaña Fin de Semana',
                'summary' => 'Aprovechar la demanda de la vitrina Marte para generar 3 variantes de copy con ganchos de confort y telas premium.',
                'actionLabel' => 'Abrir Generador de Copy IA',
                'actionType' => 'open_copy_generator',
                'campaignId' => null,
                'executed' => false
            ]
        ];

        return view('portal.marketing', [
            'leads' => $leads,
            'totalLeadsCount' => $totalLeadsCount,
            'hotLeadsCount' => $hotLeadsCount,
            'activeSubscribersCount' => $activeSubscribersCount,
            'activeCustomersCount' => $activeCustomersCount,
            'conversations' => $conversations,
            'emailAutomations' => $emailAutomations,
            'coupons' => $coupons,
            'adsMetrics' => $adsMetrics,
            'adCampaigns' => $adCampaigns,
            'growthRecommendations' => $growthRecommendations,
        ]);
    }
}

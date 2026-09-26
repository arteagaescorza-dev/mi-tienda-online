<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\LeadInteraction;
use App\Models\AdCampaign;
use App\Models\Coupon;
use App\Models\EmailAutomation;
use App\Models\ChatbotConversation;
use App\Models\Cart;
use App\Models\CartRecoveryToken;
use App\Models\Attribution;
use App\Models\Order;
use Illuminate\Support\Str;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Leads realistas con segmentación multiverso y Scoring RFM
        $leadsData = [
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@example.com',
                'phone' => '+52 81 1234 5678',
                'source' => 'Meta Ads',
                'status' => 'won',
                'notes' => 'Suscriptor mensual recurrente de vitrina Marte. Alta afinidad con textiles de bambú.',
                'metadata' => [
                    'universe' => 'marte',
                    'stage' => 'subscriber',
                    'score' => 95,
                    'ltv' => 3450.00,
                    'orders_count' => 4,
                    'preferred_sizes' => ['Boxers' => 'M', 'Playeras' => 'L'],
                    'last_interaction' => 'Abrió correo de membresía',
                ],
                'last_contacted_at' => now()->subHours(2),
            ],
            [
                'name' => 'Valeria Gómez',
                'email' => 'valeria.gomez@example.com',
                'phone' => '+52 33 9876 5432',
                'source' => 'Instagram',
                'status' => 'qualified',
                'notes' => 'Compró Pack Lencería Venus. Excelente feedback de suavidad en encaje floral.',
                'metadata' => [
                    'universe' => 'venus',
                    'stage' => 'customer',
                    'score' => 88,
                    'ltv' => 1890.00,
                    'orders_count' => 2,
                    'preferred_sizes' => ['Bralette' => '34B', 'Panty' => 'S'],
                    'last_interaction' => 'Consulta de talla por WhatsApp',
                ],
                'last_contacted_at' => now()->subDay(),
            ],
            [
                'name' => 'Alex Rivera',
                'email' => 'alex.rivera@example.com',
                'phone' => '+52 55 4567 8901',
                'source' => 'Google Ads',
                'status' => 'proposal',
                'notes' => 'Dejó Binder Ergonómico en carrito. Token de recuperación activo (TASK-INT-05).',
                'metadata' => [
                    'universe' => 'tiresias',
                    'stage' => 'abandoned',
                    'score' => 74,
                    'ltv' => 699.00,
                    'orders_count' => 1,
                    'preferred_sizes' => ['Binder' => 'M (Compresión Media)'],
                    'last_interaction' => 'Dejó Binder en carrito hace 3 horas',
                ],
                'last_contacted_at' => now()->subHours(3),
            ],
            [
                'name' => 'Diego Navarro',
                'email' => 'diego.navarro@example.com',
                'phone' => '+52 44 2345 6789',
                'source' => 'Meta Ads',
                'status' => 'new',
                'notes' => 'Registro reciente desde campaña de Meta CAPI. Navegó catálogo de básicos.',
                'metadata' => [
                    'universe' => 'terra',
                    'stage' => 'prospect',
                    'score' => 62,
                    'ltv' => 0.00,
                    'orders_count' => 0,
                    'preferred_sizes' => ['Esenciales' => 'L'],
                    'last_interaction' => 'Clic en Anuncio Meta Ads',
                ],
                'last_contacted_at' => now()->subDays(2),
            ],
            [
                'name' => 'Camila Torres',
                'email' => 'camila.torres@example.com',
                'phone' => '+52 22 8765 4321',
                'source' => 'Web',
                'status' => 'lost',
                'notes' => 'Alerta de churn en radar. Tarjeta declinada en renovación mensual.',
                'metadata' => [
                    'universe' => 'venus',
                    'stage' => 'churn',
                    'score' => 45,
                    'ltv' => 2100.00,
                    'orders_count' => 3,
                    'preferred_sizes' => ['Skincare' => 'Tónico 100ml', 'Lencería' => 'M'],
                    'last_interaction' => 'Fallo de pago renovación membresía',
                ],
                'last_contacted_at' => now()->subDays(5),
            ],
            [
                'name' => 'Mateo Fernández',
                'email' => 'mateo.fernandez@example.com',
                'phone' => '+52 99 3456 7890',
                'source' => 'WhatsApp',
                'status' => 'qualified',
                'notes' => 'Hot lead listo para cross-sell de perfume sólido y jocks Marte.',
                'metadata' => [
                    'universe' => 'marte',
                    'stage' => 'customer',
                    'score' => 84,
                    'ltv' => 1250.00,
                    'orders_count' => 1,
                    'preferred_sizes' => ['Fragancias' => '50ml', 'Ropa Interior' => 'G'],
                    'last_interaction' => 'Preguntó por reposición de fragancia',
                ],
                'last_contacted_at' => now()->subHours(5),
            ],
            [
                'name' => 'Rodrigo Silva',
                'email' => 'rodrigo.silva@example.com',
                'phone' => '+52 55 4433 2211',
                'source' => 'Evento Presencial',
                'status' => 'won',
                'notes' => 'Embajador del colectivo circuit y comprador de arneses de autor.',
                'metadata' => [
                    'universe' => 'marte',
                    'stage' => 'subscriber',
                    'score' => 92,
                    'ltv' => 4280.00,
                    'orders_count' => 5,
                    'preferred_sizes' => ['Arneses' => 'Ajustable', 'Boxers' => 'M'],
                    'last_interaction' => 'Orden entregada satisfactoriamente',
                ],
                'last_contacted_at' => now()->subDays(3),
            ],
            [
                'name' => 'Andrea Ruiz',
                'email' => 'andrea.ruiz@example.com',
                'phone' => '+52 55 3344 5566',
                'source' => 'Google Ads',
                'status' => 'won',
                'notes' => 'Compradora frecuente de básicos Terra. Ticket promedio alto.',
                'metadata' => [
                    'universe' => 'terra',
                    'stage' => 'customer',
                    'score' => 89,
                    'ltv' => 2850.00,
                    'orders_count' => 3,
                    'preferred_sizes' => ['Camisetas' => 'CH', 'Joggers' => 'CH'],
                    'last_interaction' => 'Respuesta a encuesta de satisfacción 5 estrellas',
                ],
                'last_contacted_at' => now()->subDays(1),
            ],
        ];

        foreach ($leadsData as $data) {
            $lead = Lead::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Registrar interacción CRM
            LeadInteraction::firstOrCreate(
                [
                    'lead_id' => $lead->id,
                    'interaction_type' => $lead->metadata['stage'] === 'abandoned' ? 'cart_abandoned' : 'order_placed',
                ],
                [
                    'channel' => strtolower($lead->source) === 'whatsapp' ? 'whatsapp' : 'checkout',
                    'summary' => $lead->metadata['last_interaction'] ?? 'Interacción registrada',
                    'payload' => ['score' => $lead->metadata['score'] ?? 70],
                ]
            );
        }

        // 2. Tokens de recuperación para carritos abandonados (TASK-INT-05)
        $abandonedCarts = Cart::latest()->limit(5)->get();
        foreach ($abandonedCarts as $idx => $cart) {
            $cart->update(['status' => 'abandoned']);

            CartRecoveryToken::updateOrCreate(
                ['cart_id' => $cart->id],
                [
                    'token' => 'tok_recov_' . str_pad((string) $cart->id, 4, '0', STR_PAD_LEFT) . '_' . Str::random(8),
                    'user_id' => $cart->user_id,
                    'phone' => '+52 55 ' . rand(1000, 9999) . ' ' . rand(1000, 9999),
                    'discount_code_attached' => 'RECOV10',
                    'expires_at' => now()->addDays(7),
                    'is_redeemed' => $idx === 0,
                    'redeemed_at' => $idx === 0 ? now()->subDay() : null,
                ]
            );
        }

        // 3. Flujos de Emailing Automatizados
        $emailAutomations = [
            [
                'slug' => 'auto-welcome',
                'title' => 'Bienvenida Multiverso & Doble Opt-In',
                'trigger_event' => 'user.registered',
                'delay_minutes' => 0,
                'sent_count' => 1240,
                'open_rate' => 58.4,
                'click_rate' => 12.1,
                'revenue_attributed' => 34200.00,
                'is_active' => true,
            ],
            [
                'slug' => 'auto-cart-abandoned',
                'title' => 'Recuperación Carrito Abandonado (TASK-INT-05)',
                'trigger_event' => 'cart.abandoned',
                'delay_minutes' => 30,
                'sent_count' => 890,
                'open_rate' => 64.2,
                'click_rate' => 18.5,
                'revenue_attributed' => 52400.00,
                'is_active' => true,
            ],
            [
                'slug' => 'auto-cosmetics-replenish',
                'title' => 'Reposición de Cosmética & Cuidado Íntimo (Día 35)',
                'trigger_event' => 'order.delivered',
                'delay_minutes' => 50400, // 35 días
                'sent_count' => 620,
                'open_rate' => 42.1,
                'click_rate' => 9.8,
                'revenue_attributed' => 21800.00,
                'is_active' => true,
            ],
            [
                'slug' => 'auto-subscription-reminder',
                'title' => 'Aviso Preventivo de Cobro de Membresía',
                'trigger_event' => 'subscription.dunning',
                'delay_minutes' => 4320, // 3 días
                'sent_count' => 1590,
                'open_rate' => 46.8,
                'click_rate' => 8.2,
                'revenue_attributed' => 18400.00,
                'is_active' => true,
            ],
        ];

        foreach ($emailAutomations as $ea) {
            EmailAutomation::updateOrCreate(
                ['slug' => $ea['slug']],
                $ea
            );
        }

        // 4. Conversaciones Chatbot Marteaga
        $firstLead = Lead::first();
        $conversations = [
            [
                'channel' => 'whatsapp',
                'session_status' => 'bot_active',
                'customer_name' => 'Rodrigo Méndez',
                'customer_phone' => '+52 55 7890 1234',
                'current_intent' => 'Asesoría de Talla Boxer',
                'messages_json' => [
                    ['sender' => 'user', 'text' => 'Hola, mido 1.80 y peso 78kg, ¿qué talla de boxer de bambú me recomiendan?', 'time' => '11:40'],
                    ['sender' => 'bot', 'text' => 'Para tu complexión de 1.80m y 78kg (cintura aprox 32-33"), la talla Mediana (M) en bambú Marte te brindará un ajuste ergonómico perfecto.', 'time' => '11:41'],
                    ['sender' => 'user', 'text' => 'Excelente, ¿tienen el pack triple disponible para entrega hoy en CDMX?', 'time' => '11:42'],
                ],
                'lead_id' => $firstLead?->id,
            ],
            [
                'channel' => 'web_chat',
                'session_status' => 'resolved',
                'customer_name' => 'Fernanda Castillo',
                'customer_phone' => '+52 81 8765 4321',
                'current_intent' => 'Rastreo de Guía DHL',
                'messages_json' => [
                    ['sender' => 'user', 'text' => 'Hola, quiero saber el estatus de mi pedido #ORD-1048', 'time' => '10:12'],
                    ['sender' => 'bot', 'text' => 'Tu pedido con guía DHL Express DHL-8492048192 se encuentra en ruta de entrega en Monterrey. Llegará hoy antes de las 18:00 hrs.', 'time' => '10:13'],
                    ['sender' => 'user', 'text' => 'Perfecto, muchas gracias por la rapidez.', 'time' => '10:15'],
                ],
                'lead_id' => $firstLead?->id,
            ],
            [
                'channel' => 'whatsapp',
                'session_status' => 'human_takeover',
                'customer_name' => 'Lucía Alarcón',
                'customer_phone' => '+52 33 6543 2109',
                'current_intent' => 'Cambio de Talla RMA',
                'messages_json' => [
                    ['sender' => 'user', 'text' => 'Buenas tardes, me quedó un poco chica la copa del bralette Venus, ¿puedo cambiarlo?', 'time' => '09:15'],
                    ['sender' => 'bot', 'text' => 'Te transfiero con un asesor de post-venta para gestionar tu cambio con sello de higiene.', 'time' => '09:16'],
                    ['sender' => 'agent', 'text' => 'Hola Lucía, soy Ana de Soporte. Con gusto te generamos la guía prepagada para el cambio a talla 36B.', 'time' => '09:20'],
                ],
                'lead_id' => $firstLead?->id,
            ],
        ];

        foreach ($conversations as $c) {
            ChatbotConversation::updateOrCreate(
                ['customer_phone' => $c['customer_phone']],
                $c
            );
        }

        // 5. Telemetría de Campañas Publicitarias Ads & Meta CAPI (TASK-MKT-01)
        $campaigns = [
            [
                'platform' => 'meta_ads',
                'campaign_id_external' => 'meta-001',
                'name' => 'Meta Ads - Boxers Bambú Marte (Feed & Stories)',
                'universe' => 'marte',
                'status' => 'active',
                'daily_budget' => 2400.00,
                'spend_today' => 2180.00,
                'platform_roas' => 4.80,
                'net_real_roas' => 3.60,
                'cpa' => 42.50,
                'ctr' => 4.20,
                'fatigue_alert' => false,
            ],
            [
                'platform' => 'meta_ads',
                'campaign_id_external' => 'meta-002',
                'name' => 'Meta Ads - Lencería Sensual Venus (Reels)',
                'universe' => 'venus',
                'status' => 'active',
                'daily_budget' => 1800.00,
                'spend_today' => 1650.00,
                'platform_roas' => 3.20,
                'net_real_roas' => 2.30,
                'cpa' => 68.00,
                'ctr' => 1.80,
                'fatigue_alert' => true,
            ],
            [
                'platform' => 'google_ads',
                'campaign_id_external' => 'google-001',
                'name' => 'Google Ads - Binders Ergonómicos Tiresias (Search)',
                'universe' => 'tiresias',
                'status' => 'active',
                'daily_budget' => 1000.00,
                'spend_today' => 890.00,
                'platform_roas' => 5.40,
                'net_real_roas' => 4.10,
                'cpa' => 38.00,
                'ctr' => 6.80,
                'fatigue_alert' => false,
            ],
            [
                'platform' => 'meta_ads',
                'campaign_id_external' => 'meta-003',
                'name' => 'Meta Ads - Esenciales Multiverso Terra (Catálogo)',
                'universe' => 'terra',
                'status' => 'active',
                'daily_budget' => 600.00,
                'spend_today' => 540.00,
                'platform_roas' => 3.90,
                'net_real_roas' => 2.90,
                'cpa' => 52.00,
                'ctr' => 3.80,
                'fatigue_alert' => false,
            ],
        ];

        foreach ($campaigns as $camp) {
            AdCampaign::updateOrCreate(
                ['campaign_id_external' => $camp['campaign_id_external']],
                $camp
            );
        }

        // 6. Atribución Comercial para Órdenes
        $orders = Order::all();
        $sources = [
            ['source' => 'meta_ads', 'medium' => 'paid_social', 'campaign' => 'meta-001'],
            ['source' => 'google_ads', 'medium' => 'cpc', 'campaign' => 'google-001'],
            ['source' => 'whatsapp', 'medium' => 'direct_message', 'campaign' => 'marteaga_bot'],
            ['source' => 'newsletter', 'medium' => 'email', 'campaign' => 'auto-welcome'],
        ];

        foreach ($orders as $idx => $ord) {
            $src = $sources[$idx % count($sources)];
            $attribution = Attribution::firstOrCreate(
                ['session_id' => 'sess_' . $ord->order_number],
                [
                    'source' => $src['source'],
                    'medium' => $src['medium'],
                    'campaign' => $src['campaign'],
                    'term' => 'textiles_online',
                    'content' => 'anuncio_carrusel',
                    'landing_url' => 'https://tienda.test/productos',
                    'user_id' => $ord->user_id,
                    'first_seen_at' => $ord->created_at ? $ord->created_at->subHours(2) : now()->subHours(2),
                    'last_activity_at' => $ord->created_at ?? now(),
                ]
            );

            $ord->update(['attributed_via_id' => $attribution->id]);
        }
    }
}

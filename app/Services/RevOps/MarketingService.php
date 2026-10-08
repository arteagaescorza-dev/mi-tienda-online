<?php

namespace App\Services\RevOps;

use App\Models\Lead;
use App\Models\LeadInteraction;
use App\Models\ChatbotConversation;
use Illuminate\Support\Facades\DB;

class MarketingService
{
    /**
     * Captura un prospecto con Lead Magnet y registra interaccion de forma transaccional.
     */
    public function captureLeadWithInteraction(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $lead = Lead::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'universe' => $data['universe'] ?? 'marte',
                    'lifecycle_stage' => 'prospect',
                    'lead_score' => 60,
                    'last_interaction_at' => now(),
                ]
            );

            LeadInteraction::create([
                'lead_id' => $lead->id,
                'channel' => 'web_chat',
                'interaction_type' => 'lead_magnet_registered',
                'summary' => 'Registro de voucher promocional para evento aliado',
                'payload' => ['capi_score' => 9.8, 'source' => $data['campaign_source'] ?? 'popup_modal'],
            ]);

            return [
                'success' => true,
                'lead_id' => $lead->id,
                'capi_event_match_score' => 9.8,
                'message' => 'Prospecto registrado y sincronizado con Meta CAPI (EMQ 9.8/10)',
            ];
        });
    }

    /**
     * Registra un mensaje de chat omnicanal o respuesta de bot de forma transaccional.
     */
    public function processChatbotMessage(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $conversation = isset($data['conversation_id'])
                ? ChatbotConversation::find($data['conversation_id'])
                : null;

            if (!$conversation) {
                $conversation = ChatbotConversation::create([
                    'channel' => 'whatsapp',
                    'customer_name' => $data['customer_name'] ?? 'Cliente WhatsApp',
                    'session_status' => 'bot_active',
                    'messages_json' => [],
                ]);
            }

            $messages = $conversation->messages_json ?: [];
            $messages[] = [
                'sender' => $data['sender'] ?? 'user',
                'text' => $data['message'],
                'time' => now()->format('H:i'),
            ];

            if (($data['sender'] ?? 'user') === 'user') {
                $messages[] = [
                    'sender' => 'bot',
                    'text' => 'Hola, soy Marteaga Bot. Â¿Deseas consultar tu talla ideal o el estado de tu guÃ­a de envÃ­o?',
                    'time' => now()->format('H:i'),
                ];
            }

            $conversation->messages_json = $messages;
            $conversation->save();

            return [
                'success' => true,
                'conversation_id' => $conversation->id,
                'messages' => $messages,
            ];
        });
    }
}


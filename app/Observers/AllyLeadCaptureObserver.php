<?php

namespace App\Observers;

use App\Models\AllyLeadCapture;
use App\Models\Lead;
use App\Models\LeadInteraction;

class AllyLeadCaptureObserver
{
    /**
     * Sincroniza automáticamente la captura de prospectos de convenios (Hub 7)
     * con la bandeja de CRM de Hub 3 e historial de interacciones.
     */
    public function created(AllyLeadCapture $capture): void
    {
        if (empty($capture->lead_email)) {
            return;
        }

        $email = strtolower(trim((string) $capture->lead_email));
        $phone = preg_replace('/\D/', '', (string) ($capture->lead_whatsapp ?? ''));
        $name = trim((string) ($capture->lead_name ?? 'Prospecto Convenio'));
        $universe = strtolower(trim((string) ($capture->universe ?? 'marte')));

        $lead = Lead::where('email', $email)->first();

        if (!$lead) {
            $lead = Lead::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: null,
                'source' => 'ally_campaign',
                'status' => 'new',
                'universe' => $universe,
                'lifecycle_stage' => 'prospect',
                'lead_score' => 85,
                'notes' => sprintf("Captura desde convenio de aliado ID #%s (Voucher: %s)", $capture->ally_id, $capture->voucher_code),
                'metadata' => [
                    'ally_id' => $capture->ally_id,
                    'campaign_id' => $capture->campaign_id,
                    'voucher_code' => $capture->voucher_code,
                    'registered_via' => 'hub7_convenio',
                ],
                'last_interaction_at' => now(),
            ]);
        } else {
            $meta = (array) ($lead->metadata ?? []);
            $meta['last_ally_id'] = $capture->ally_id;
            $meta['last_campaign_id'] = $capture->campaign_id;
            $meta['last_voucher_code'] = $capture->voucher_code;
            $meta['re_engaged_via'] = 'hub7_convenio';

            $lead->update([
                'name' => $lead->name ?: $name,
                'phone' => $phone ?: $lead->phone,
                'source' => 'ally_campaign',
                'status' => in_array($lead->status, ['won', 'lost', 'abandoned'], true) ? 'new' : $lead->status,
                'notes' => trim(($lead->notes ?? '') . sprintf(" | Re-activado por voucher #%s de convenio #%s", $capture->voucher_code, $capture->ally_id)),
                'metadata' => $meta,
                'last_interaction_at' => now(),
            ]);
        }

        if (class_exists(LeadInteraction::class)) {
            LeadInteraction::create([
                'lead_id' => $lead->id,
                'channel' => 'ally_campaign',
                'interaction_type' => 'voucher_issued',
                'summary' => "Emisión de voucher digital {$capture->voucher_code} por convenio",
                'payload' => [
                    'ally_id' => $capture->ally_id,
                    'campaign_id' => $capture->campaign_id,
                    'voucher_code' => $capture->voucher_code,
                    'universe' => $capture->universe,
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        }
    }
}


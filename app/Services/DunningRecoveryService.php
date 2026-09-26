<?php

namespace App\Services;

use App\Models\CustomerSubscription;
use App\Models\DunningRetryLog;
use Illuminate\Support\Facades\DB;

class DunningRecoveryService
{
    /**
     * Registra un pago de membresía declinado e inicia la secuencia de reintentos escalonados (TASK-SUB-DUN).
     */
    public function handlePaymentDecline(CustomerSubscription $subscription, string $declineCode = 'insufficient_funds', ?string $reason = null): DunningRetryLog
    {
        return DB::transaction(function () use ($subscription, $declineCode, $reason) {
            $subscription->failed_attempts_count++;
            $attemptNumber = $subscription->failed_attempts_count;

            // Escalamiento de reintentos: Intento 1 -> +3 días, Intento 2 -> +7 días, Intento 3 -> Cancelación
            if ($attemptNumber === 1) {
                $scheduledRetry = now()->addDays(3);
                $actionTaken = 'automated_charge_retry_scheduled';
                $subscription->status = 'past_due';
            } elseif ($attemptNumber === 2) {
                $scheduledRetry = now()->addDays(7);
                $actionTaken = 'whatsapp_link_sent';
                $subscription->status = 'past_due';
            } else {
                $scheduledRetry = null;
                $actionTaken = 'subscription_cancelled_dunning_limit';
                $subscription->status = 'cancelled';
                $subscription->cancelled_at = now();
            }

            $subscription->save();

            $log = DunningRetryLog::create([
                'subscription_id' => $subscription->id,
                'attempt_number' => $attemptNumber,
                'decline_code' => $declineCode,
                'action_taken' => $actionTaken,
                'was_recovered' => false,
                'amount_recovered' => 0.00,
                'scheduled_retry_at' => $scheduledRetry,
            ]);

            return $log;
        });
    }

    /**
     * Marca la membresía como recuperada con éxito tras el pago forzado o actualización de tarjeta.
     */
    public function markAsRecovered(CustomerSubscription $subscription, float $amountRecovered): DunningRetryLog
    {
        return DB::transaction(function () use ($subscription, $amountRecovered) {
            $subscription->failed_attempts_count = 0;
            $subscription->status = 'active';
            $subscription->next_billing_at = now()->addMonth();
            $subscription->save();

            $log = $subscription->dunningLogs()->latest()->first();
            if ($log) {
                $log->was_recovered = true;
                $log->amount_recovered = $amountRecovered;
                $log->save();
            } else {
                $log = DunningRetryLog::create([
                    'subscription_id' => $subscription->id,
                    'attempt_number' => 1,
                    'decline_code' => 'resolved_manual',
                    'action_taken' => 'payment_recovered_success',
                    'was_recovered' => true,
                    'amount_recovered' => $amountRecovered,
                ]);
            }

            return $log;
        });
    }
}

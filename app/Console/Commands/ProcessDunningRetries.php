<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DunningRetryLog;
use App\Models\CustomerSubscription;
use App\Services\DunningRecoveryService;

class ProcessDunningRetries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:process-dunning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta los reintentos escalonados de cobro para suscripciones past_due (TASK-SUB-DUN)';

    /**
     * Execute the console command.
     */
    public function handle(DunningRecoveryService $dunningService): int
    {
        $this->info('Iniciando procesamiento de reintentos de Dunning para membresías...');

        $pendingRetries = DunningRetryLog::with('subscription')
            ->where('was_recovered', false)
            ->where('scheduled_retry_at', '<=', now())
            ->get();

        $processed = 0;
        foreach ($pendingRetries as $retry) {
            $sub = $retry->subscription;
            if ($sub && $sub->status === 'past_due') {
                $processed++;
                $this->line(" - Procesando reintento para suscripción {$sub->subscription_folio} (Intento #{$retry->attempt_number})");
                
                // Simulación o ejecución de reintento
                $dunningService->handlePaymentDecline($sub, 'card_declined_automatic_retry', 'Reintento automático programado');
            }
        }

        $this->info("Procesamiento de Dunning concluido. {$processed} reintentos ejecutados.");

        return self::SUCCESS;
    }
}

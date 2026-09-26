<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StockReservationService;

class CleanupExpiredStockLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:cleanup-expired-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libera automáticamente las reservas de inventario expiradas (TASK-INT-06)';

    /**
     * Execute the console command.
     */
    public function handle(StockReservationService $reservationService): int
    {
        $this->info('Iniciando limpieza de candados de inventario expirados...');
        $count = $reservationService->cleanupExpiredLocks();

        if ($count > 0) {
            $this->info("Se liberaron con éxito {$count} candados de stock expirados.");
        } else {
            $this->comment('No se encontraron candados de stock expirados pendientes.');
        }

        return self::SUCCESS;
    }
}

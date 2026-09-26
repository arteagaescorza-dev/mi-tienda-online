<?php

namespace App\Console\Commands;

use App\Models\SupplierSyncRun;
use Illuminate\Console\Command;

class PruneSupplierSyncRuns extends Command
{
    protected $signature = 'supplier:prune-runs {--days=90 : Dias de historial a conservar}';
    protected $description = 'Elimina ejecuciones antiguas de sincronizacion de proveedores.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $deleted = SupplierSyncRun::query()
            ->where('started_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Ejecuciones eliminadas: {$deleted}. Historial conservado: {$days} dias.");

        return self::SUCCESS;
    }
}
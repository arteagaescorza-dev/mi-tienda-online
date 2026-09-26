<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;

class SeedLeads extends Command
{
    protected $signature = 'leads:seed-sample {count=25}';
    protected $description = 'Crea leads de ejemplo usando la factory y muestra el total';

    public function handle(): int
    {
        $count = (int) $this->argument('count');
        Lead::factory()->count($count)->create();
        $total = Lead::count();
        $this->info("Leads creados: {$count}. Total en tabla: {$total}");
        return self::SUCCESS;
    }
}

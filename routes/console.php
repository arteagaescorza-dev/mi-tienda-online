<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =========================================================================
// REVOPS CRON SCHEDULES (TASK-DSH-03, TASK-INT-06, TASK-SUB-DUN, TASK-INV-04)
// =========================================================================

// 1. Scraping y Sincronización de Mayoristas (Torzo Bot)
Schedule::command('supplier:scrape')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('supplier:prune-runs')->weeklyOn(0, '03:00')->withoutOverlapping();

// 2. Liberación de Candados de Inventario Expirados (TASK-INT-06)
Schedule::command('stock:cleanup-expired-locks')->everyFiveMinutes()->withoutOverlapping();

// 3. Telemetría y Salud de APIs Externas (TASK-DSH-03)
Schedule::command('telemetry:check-apis')->hourly()->withoutOverlapping();

// 4. Evaluación de Punto de Reorden ROP y Generación de Tareas (TASK-DSH-04)
Schedule::command('inventory:evaluate-rop')->dailyAt('06:00')->withoutOverlapping();

// 5. Procesamiento de Reintentos Dunning para Membresías MRR (TASK-SUB-DUN)
Schedule::command('finance:process-dunning')->dailyAt('09:00')->withoutOverlapping();

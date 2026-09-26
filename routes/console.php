<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('supplier:scrape')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('supplier:prune-runs')->weeklyOn(0, '03:00')->withoutOverlapping();

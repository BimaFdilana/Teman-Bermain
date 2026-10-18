<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PRD §5.4: order yang tidak dibayar dalam 1×24 jam otomatis kadaluarsa.
// Di shared hosting: cron tiap menit -> `php artisan schedule:run`
Schedule::command('order:kadaluarsa')->everyFiveMinutes();

// Fase 6: backup database harian jam 02:00 (disimpan 7 hari terakhir).
Schedule::command('backup:database')->dailyAt('02:00');

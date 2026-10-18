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

// Fase 6: backup database + file harian jam 02:00 (disimpan 7 hari terakhir per jenis).
Schedule::command('backup:database --files')->dailyAt('02:00');

// PRD BR-RC-16: testimoni di sampah > 30 hari dihapus permanen.
Schedule::call(function () {
    \App\Models\Testimonial::onlyTrashed()
        ->where('deleted_at', '<', now()->subDays(30))
        ->chunkById(50, function ($items) {
            foreach ($items as $t) {
                if ($t->foto_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($t->foto_path);
                }
                $t->forceDelete();
            }
        });
})->daily()->name('purge-testimoni');

<?php

namespace App\Jobs;

use App\Services\WablasService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Antrean kirim WA (diproses scheduler tiap menit — lihat routes/console.php).
 * Bila Wablas nonaktif saat job jalan, job selesai diam-diam tanpa retry.
 * Gagal kirim (3x percobaan) masuk failed_jobs dan tercatat di log.
 */
class KirimWaJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 30;

    public function __construct(public string $tujuan, public string $pesan) {}

    public function handle(WablasService $wablas): void
    {
        if (! WablasService::enabled()) {
            return;
        }

        if (! $wablas->kirim($this->tujuan, $this->pesan)) {
            Log::warning('KirimWaJob gagal, akan dicoba ulang.', [
                'tujuan' => $this->tujuan,
                'percobaan' => $this->attempts(),
            ]);
            throw new \RuntimeException('Wablas gagal mengirim.');
        }
    }
}

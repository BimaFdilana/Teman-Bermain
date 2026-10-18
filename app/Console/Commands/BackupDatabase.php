<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Backup database harian (disimpan 7 hari terakhir).';

    public function handle(): int
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/backups'),
        ]);

        $nama = 'backup-'.now()->format('Ymd-His').'.sql'.($this->isMysql() ? '.gz' : '');
        $isi = $this->isMysql() ? $this->dumpMysql() : $this->dumpSqlite();

        if ($isi === null) {
            $this->error('Backup gagal.');
            return self::FAILURE;
        }

        $disk->put($nama, $isi);
        $this->info('Backup tersimpan: '.$nama);

        // Hapus backup lebih dari 7 hari
        $files = collect($disk->files())->sort()->values();
        if ($files->count() > 7) {
            foreach ($files->take($files->count() - 7) as $lama) {
                $disk->delete($lama);
            }
            $this->info('Backup lama dihapus, tersisa 7 terbaru.');
        }

        return self::SUCCESS;
    }

    private function isMysql(): bool
    {
        return config('database.default') === 'mysql';
    }

    private function dumpMysql(): ?string
    {
        $c = config('database.connections.mysql');
        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s 2>/dev/null | gzip',
            escapeshellarg($c['host']), escapeshellarg($c['port'] ?? 3306),
            escapeshellarg($c['username']), escapeshellarg($c['password']),
            escapeshellarg($c['database'])
        );
        $out = shell_exec($cmd);
        return ($out === null || $out === '') ? null : $out;
    }

    private function dumpSqlite(): ?string
    {
        $path = config('database.connections.sqlite.database');
        if (! is_file($path)) {
            return null;
        }
        // Dump SQL agar portable (bukan copy biner)
        $out = shell_exec('sqlite3 '.escapeshellarg($path).' .dump 2>/dev/null');
        return ($out === null || $out === '') ? null : $out;
    }
}

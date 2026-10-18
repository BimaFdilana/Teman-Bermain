<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    private function disk()
    {
        return Storage::build(['driver' => 'local', 'root' => storage_path('app/backups')]);
    }

    /** Daftar backup + tombol backup manual (PRD §10 no. 10). */
    public function index(): View
    {
        $files = collect($this->disk()->files())
            ->map(fn ($f) => [
                'nama' => basename($f),
                'ukuran' => $this->disk()->size($f),
                'waktu' => $this->disk()->lastModified($f),
                'jenis' => str_contains($f, 'backup-files-') ? '📁 File' : '🗄️ Database',
            ])
            ->sortByDesc('waktu')->values();

        return view('admin.backup.index', compact('files'));
    }

    /** Jalankan backup sekarang (database + file). */
    public function store(): RedirectResponse
    {
        $exit = Artisan::call('backup:database', ['--files' => true]);

        if ($exit === 0) {
            ActivityLog::catat('backup', 'Backup manual database + file dijalankan');

            return back()->with('sukses', 'Backup selesai dibuat.');
        }

        return back()->with('gagal', 'Backup gagal dibuat. Cek log server.');
    }

    /** Unduh file backup. */
    public function download(string $nama): StreamedResponse|RedirectResponse
    {
        $nama = basename($nama);
        if (! preg_match('/^backup-(files-)?[\d\-]+\.(sql\.gz|sql|zip)$/', $nama)) {
            abort(404);
        }
        $disk = $this->disk();
        if (! $disk->exists($nama)) {
            return back()->with('gagal', 'File backup tidak ditemukan.');
        }

        return $disk->download($nama);
    }
}

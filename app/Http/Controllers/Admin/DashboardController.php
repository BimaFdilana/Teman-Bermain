<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard admin (PRD §6.3).
     */
    public function index(Request $request): View
    {
        $filter = $request->get('periode', 'minggu');
        $filter = in_array($filter, ['minggu', 'bulan', 'tahun']) ? $filter : 'minggu';

        $stats = [
            'produk_tersedia' => Product::where('status', 'tersedia')->count(),
            'produk_draft' => Product::where('status', 'draft')->count(),
            'produk_terjual' => Order::where('status', 'disetujui')->count(),
            'pendapatan' => (int) Order::where('status', 'disetujui')->sum('total_bayar'),
            'pengguna' => User::where('role', 'pengguna')->count(),
            'menunggu_verifikasi' => Order::where('status', 'verifikasi')->count(),
        ];

        $logTerbaru = LoginLog::with('user')->latest('login_at')->take(5)->get();

        $aktivitas = $this->aktivitasPengguna($filter);

        return view('admin.dashboard', compact('stats', 'logTerbaru', 'aktivitas', 'filter'));
    }

    /**
     * Data chart aktivitas login + insight otomatis (PRD §6.3).
     */
    private function aktivitasPengguna(string $filter): array
    {
        $hari = ['minggu' => 7, 'bulan' => 30, 'tahun' => 365][$filter];
        $mulai = Carbon::now()->subDays($hari)->startOfDay();

        $logs = LoginLog::where('login_at', '>=', $mulai)->get(['login_at']);

        $namaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $perHari = array_fill_keys($namaHari, 0);
        $perJam = array_fill(0, 24, 0);

        foreach ($logs as $log) {
            $w = Carbon::parse($log->login_at);
            $perHari[$namaHari[$w->dayOfWeekIso - 1]]++;
            $perJam[(int) $w->format('H')]++;
        }

        return [
            'per_hari' => $perHari,
            'per_jam' => $perJam,
            'label_hari' => $namaHari,
        ];
    }
}

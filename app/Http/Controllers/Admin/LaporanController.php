<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SalesTarget;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    /**
     * Manage Laporan: pendapatan vs target manual + rincian (PRD §6.8).
     */
    public function index(Request $request): View
    {
        $tipe = $request->get('tipe', 'bulan');
        $tipe = in_array($tipe, ['minggu', 'bulan', 'tahun']) ? $tipe : 'bulan';

        $periodes = $this->daftarPeriode($tipe, 8);

        return view('admin.laporan.index', compact('tipe', 'periodes'));
    }

    /**
     * Simpan/ubah target penjualan manual per periode.
     */
    public function simpanTarget(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tipe' => 'required|in:minggu,bulan,tahun',
            'periode' => 'required|string|max:20',
            'target' => 'required|integer|min:0',
        ]);

        SalesTarget::updateOrCreate(
            ['tipe' => $data['tipe'], 'periode' => $data['periode']],
            ['target' => $data['target']]
        );

        return back()->with('sukses', 'Target periode '.$data['periode'].' disimpan: Rp '.number_format($data['target'], 0, ',', '.'));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $tipe = $request->get('tipe', 'bulan');
        $periodes = $this->daftarPeriode($tipe, 8);

        $rows = [];
        foreach ($periodes as $p) {
            $rows[] = [
                $p['label'],
                $p['pendapatan'],
                $p['target'] ?? 0,
                $p['pendapatan'] - ($p['target'] ?? 0),
                $p['jumlah_order'],
            ];
        }

        $export = new class($rows) implements FromArray, WithHeadings {
            public function __construct(private array $rows) {}
            public function array(): array { return $this->rows; }
            public function headings(): array {
                return ['Periode', 'Pendapatan (Rp)', 'Target (Rp)', 'Selisih (Rp)', 'Jumlah Order'];
            }
        };

        return Excel::download($export, 'laporan-'.$tipe.'-'.date('Ymd').'.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $tipe = $request->get('tipe', 'bulan');
        $periodes = $this->daftarPeriode($tipe, 8);

        $pdf = Pdf::loadView('admin.laporan.pdf', compact('tipe', 'periodes'));
        return $pdf->download('laporan-'.$tipe.'-'.date('Ymd').'.pdf');
    }

    /**
     * Daftar N periode terakhir + pendapatan & target tiap periode.
     */
    private function daftarPeriode(string $tipe, int $jumlah): array
    {
        $hasil = [];
        $sekarang = Carbon::now();

        for ($i = 0; $i < $jumlah; $i++) {
            if ($tipe === 'minggu') {
                $awal = $sekarang->copy()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
                $akhir = $awal->copy()->endOfWeek(Carbon::SUNDAY);
                $key = $awal->format('Y').'-W'.$awal->format('W');
                $label = 'Minggu '.$awal->format('W').' ('.$awal->format('d M').'–'.$akhir->format('d M Y').')';
            } elseif ($tipe === 'bulan') {
                $awal = $sekarang->copy()->subMonths($i)->startOfMonth();
                $akhir = $awal->copy()->endOfMonth();
                $key = $awal->format('Y-m');
                $label = $awal->translatedFormat('F Y');
            } else {
                $awal = $sekarang->copy()->subYears($i)->startOfYear();
                $akhir = $awal->copy()->endOfYear();
                $key = $awal->format('Y');
                $label = 'Tahun '.$key;
            }

            $orders = Order::where('status', 'disetujui')
                ->whereBetween('created_at', [$awal, $akhir]);

            $target = SalesTarget::where('tipe', $tipe)->where('periode', $key)->first();

            $hasil[] = [
                'key' => $key,
                'label' => $label,
                'pendapatan' => (int) $orders->sum('total_bayar'),
                'jumlah_order' => $orders->count(),
                'target' => $target?->target,
            ];
        }

        return $hasil;
    }
}

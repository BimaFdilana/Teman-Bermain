<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class AktivitasController extends Controller
{
    /** Riwayat aksi penting admin (PRD BR-RC-15). */
    public function index(): View
    {
        return view('admin.aktivitas.index', [
            'logs' => ActivityLog::with('admin')->latest()->paginate(20),
        ]);
    }
}

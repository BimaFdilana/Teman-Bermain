<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminIdleTimeout
{
    /**
     * PRD BR-RC-13: sesi admin berakhir setelah 2 jam tidak aktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $batasMenit = 120;
        $terakhir = $request->session()->get('admin_last_activity');

        // diffInMinutes Carbon 3 bertanda: pakai absolut agar perbandingan benar.
        if ($terakhir && now()->diffInMinutes($terakhir, true) > $batasMenit) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Sesi berakhir karena tidak aktif selama 2 jam. Silakan login lagi.']);
        }

        $request->session()->put('admin_last_activity', now());

        return $next($request);
    }
}

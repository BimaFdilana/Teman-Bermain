<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNonAdmin
{
    /**
     * Halaman pengguna hanya untuk role pengguna —
     * admin yang membukanya diarahkan ke dashboard admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}

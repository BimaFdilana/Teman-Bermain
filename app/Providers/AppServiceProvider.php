<?php

namespace App\Providers;

use App\Models\LoginLog;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        // Demo via tunnel: paksa skema https agar asset & signed URL valid.
        if (env('FORCE_HTTPS')) {
            URL::forceScheme('https');
        }
        // PRD BR-RC-13: cookie "ingat saya" berlaku 30 hari (bukan default 400 hari).
        Auth::resolved(function ($guard) {
            if (method_exists($guard, 'setRememberDuration')) {
                $guard->setRememberDuration(60 * 24 * 30);
            }
        });

        // PRD §6.3: catat setiap login untuk dashboard admin.
        Event::listen(Login::class, function (Login $event) {
            LoginLog::create([
                'user_id' => $event->user->id,
                'ip_address' => request()->ip(),
                'login_at' => now(),
            ]);
        });
    }
}

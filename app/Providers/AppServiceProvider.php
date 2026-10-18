<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
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
        // PRD BR-RC-13: cookie "ingat saya" berlaku 30 hari (bukan default 400 hari).
        Auth::resolved(function ($guard) {
            if (method_exists($guard, 'setRememberDuration')) {
                $guard->setRememberDuration(60 * 24 * 30);
            }
        });
    }
}

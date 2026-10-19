<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect ke halaman persetujuan Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Tangani callback dari Google: cari/buat akun lalu login.
     */
    public function callback(Request $request): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            // Tautkan ke akun yang emailnya sama bila sudah ada (daftar via email).
            $user = User::where('email', $googleUser->getEmail())->first();
            if ($user) {
                // PRD §6.1: admin wajib login email + password, tidak via Google.
                if ($user->isAdmin()) {
                    return redirect()->route('admin.login')
                        ->withErrors(['email' => 'Akun admin wajib login dengan email + password.']);
                }
                $user->update(['google_id' => $googleUser->getId()]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Pengguna',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => null,
                ]);
            }
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Akun admin wajib login dengan email + password.']);
        }

        Auth::login($user, true); // ingat saya 30 hari (lihat AppServiceProvider)
        $request->session()->regenerate(); // cegah session fixation (audit 19 Okt 2026)

        // BR-RC-21: hubungkan order tamu yang emailnya cocok ke akun ini
        \App\Models\Order::whereNull('user_id')
            ->where('guest_email', $user->email)
            ->update(['user_id' => $user->id]);

        return redirect()->intended(route('home', absolute: false));
    }
}

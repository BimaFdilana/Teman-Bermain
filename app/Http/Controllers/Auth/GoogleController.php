<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

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
     *
     * State OAuth yang tidak cocok (sesi berubah/hilang selama ke Google dan
     * kembali — umum di HP: browser in-app, tab baru, ketuk ganda) TIDAK
     * boleh jadi 500: arahkan kembali ke login dengan pesan coba lagi.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'google' => 'Sesi login Google kedaluwarsa atau tidak cocok. Silakan ketuk "Masuk dengan Google" sekali lagi.',
            ]);
        }

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

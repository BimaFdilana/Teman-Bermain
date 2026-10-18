<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'whatsapp', 'google_id', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_PENGGUNA = 'pengguna';
    public const ROLE_ADMIN = 'admin';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Normalisasi nomor WhatsApp ke format 62... (Indonesia).
     * "0812..." -> "62812...", "+62812" -> "62812", "62812" tetap.
     */
    public static function normalizeWhatsapp(?string $number): ?string
    {
        if ($number === null || $number === '') {
            return null;
        }
        $digits = preg_replace('/\D/', '', $number);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '62')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        // diasumsikan nomor lokal tanpa kode negara, mis. 812...
        return '62'.$digits;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}

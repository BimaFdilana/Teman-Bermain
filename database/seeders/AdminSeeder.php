<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Buat akun admin. Kredensial via env (ADMIN_EMAIL / ADMIN_PASSWORD).
     * Default hanya untuk development — JANGAN dipakai di produksi.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@rumahcahaya.test');
        $password = env('ADMIN_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin Rumah Cahaya',
                'password' => Hash::make($password),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        $this->command->warn("Admin: {$email} (ubah password setelah login pertama!)");
    }
}

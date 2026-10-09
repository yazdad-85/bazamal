<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@bazar.test');
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            return;
        }

        $password = env('ADMIN_INITIAL_PASSWORD');
        if (! is_string($password) || strlen($password) < 12) {
            if (! app()->environment('testing')) {
                throw new RuntimeException('Isi ADMIN_INITIAL_PASSWORD minimal 12 karakter sebelum membuat akun admin.');
            }

            $password = 'password';
        }

        User::create([
            'name' => 'Admin Panitia',
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);
    }
}

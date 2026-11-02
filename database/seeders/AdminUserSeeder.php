<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Akun admin dari ADMIN_EMAIL / ADMIN_PASSWORD, untuk hosting tanpa shell
 * (Vercel) tempat `php artisan make:filament-user` tidak bisa dijalankan.
 * Dijalankan setiap start:
 *  - akun belum ada → dibuat,
 *  - nilai ADMIN_PASSWORD DIGANTI di Vercel (lalu Redeploy) → password akun ikut
 *    diganti. Selama nilainya tidak berubah, password akun tidak disentuh.
 * Password yang terakhir diterapkan disimpan sebagai hash bcrypt (bukan teks
 * asli) di cache database. Tidak pernah memakai password default.
 */
class AdminUserSeeder extends Seeder
{
    public const APPLIED_PASSWORD_KEY = 'cms:admin-password-applied';

    public function run(): void
    {
        $email = config('cms.admin.email');
        $password = config('cms.admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD belum di-set — akun admin pertama tidak dibuat.');

            return;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            User::create([
                'name' => config('cms.admin.name') ?: 'Admin',
                'email' => $email,
                'password' => $password,
            ]);
            $this->rememberApplied($password);
            $this->command?->info("Akun admin {$email} dibuat.");

            return;
        }

        $applied = Cache::get(self::APPLIED_PASSWORD_KEY);

        if (is_string($applied) && Hash::check($password, $applied)) {
            return; // ADMIN_PASSWORD tidak berubah sejak terakhir diterapkan.
        }

        if (! Hash::check($password, $user->password)) {
            $user->update(['password' => $password]);
            $this->command?->info("Password admin {$email} diganti sesuai ADMIN_PASSWORD.");
        }

        $this->rememberApplied($password);
    }

    private function rememberApplied(string $password): void
    {
        Cache::forever(self::APPLIED_PASSWORD_KEY, Hash::make($password));
    }
}

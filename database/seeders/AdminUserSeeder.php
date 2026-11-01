<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Membuat akun admin pertama dari ADMIN_EMAIL / ADMIN_PASSWORD, untuk hosting
 * tanpa shell (Vercel) tempat `php artisan make:filament-user` tidak bisa
 * dijalankan. Dijalankan setiap start, tetapi tidak pernah mengubah akun yang
 * sudah ada — password yang diganti kemudian tidak ditimpa — dan tidak pernah
 * memakai password default.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('cms.admin.email');
        $password = config('cms.admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD belum di-set — akun admin pertama tidak dibuat.');

            return;
        }

        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        User::create([
            'name' => config('cms.admin.name') ?: 'Admin',
            'email' => $email,
            'password' => $password,
        ]);

        $this->command?->info("Akun admin {$email} dibuat.");
    }
}

<?php

namespace App\Console\Commands;

use App\Models\AboutPage;
use App\Models\User;
use Database\Seeders\AboutSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Langkah instalasi idempoten — aman dijalankan setiap kali server start
 * (dipakai docker/start.sh):
 *  1. migrasi database,
 *  2. isi konten About awal HANYA bila database masih kosong,
 *  3. buat akun admin pertama bila belum ada user,
 *  4. buat symlink storage, lalu impor media dari CMS_ASSETS_SOURCE
 *     (file yang sudah ada dilewati).
 */
#[Signature('cms:install')]
#[Description('Migrasi + isi data awal (sekali) + impor media halaman About')]
class InstallCms extends Command
{
    public function handle(): int
    {
        $this->call('migrate', ['--force' => true]);

        if (! AboutPage::query()->exists()) {
            $this->components->info('Database kosong — mengisi konten About awal.');
            $this->call('db:seed', ['--class' => AboutSeeder::class, '--force' => true]);
        }

        if (Schema::hasTable('users') && ! User::query()->exists()) {
            $this->createAdmin();
        }

        if (! is_link(public_path('storage'))) {
            $this->call('storage:link');
        }

        if ($source = config('cms.assets_source')) {
            $this->call('cms:import-assets', ['source' => $source]);
        }

        return self::SUCCESS;
    }

    private function createAdmin(): void
    {
        $email = config('cms.admin.email');
        $password = config('cms.admin.password');

        // Di produksi jangan pernah memakai password default yang ada di README.
        if (blank($password)) {
            $password = app()->isProduction() ? Str::password(16, symbols: false) : 'iglocms123';
            $this->components->warn("CMS_ADMIN_PASSWORD tidak di-set. Password admin {$email}: {$password}");
        }

        User::create([
            'name' => config('cms.admin.name'),
            'email' => $email,
            'password' => $password,
        ]);

        $this->components->info("Akun admin dibuat: {$email}");
    }
}

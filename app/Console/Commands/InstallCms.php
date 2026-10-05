<?php

namespace App\Console\Commands;

use App\Models\AboutPage;
use App\Models\User;
use Database\Seeders\AboutSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

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
        if (! $this->waitForDatabase()) {
            return self::FAILURE;
        }

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

    /**
     * Di hosting, database (mis. MySQL Railway) bisa belum siap menerima
     * koneksi tepat saat container start — tunggu dulu, lalu beri pesan
     * yang jelas bila konfigurasinya memang salah.
     */
    private function waitForDatabase(int $attempts = 30, int $sleepSeconds = 2): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}", []);

        // DB_URL (mis. ${{MySQL.MYSQL_URL}} di Railway) menimpa host/database.
        if ($url = parse_url((string) ($config['url'] ?? ''))) {
            $config['host'] = $url['host'] ?? $config['host'] ?? null;
            $config['database'] = isset($url['path']) ? ltrim($url['path'], '/') : ($config['database'] ?? null);
        }

        $this->components->info(sprintf(
            'Database: %s (host: %s, db: %s)',
            $connection,
            $config['host'] ?? '-',
            $config['database'] ?? '-',
        ));

        if (app()->isProduction() && $connection === 'sqlite') {
            $this->components->warn('DB_CONNECTION masih sqlite — data akan hilang saat redeploy. Set DB_CONNECTION=mysql dan DB_URL.');
        }

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                DB::connection()->getPdo();

                return true;
            } catch (Throwable $e) {
                if ($i === $attempts) {
                    $this->components->error('Tidak bisa terhubung ke database: '.$e->getMessage());
                    $this->line('  Periksa variabel DB_CONNECTION=mysql dan DB_URL=${{MySQL.MYSQL_URL}} di service ini,');
                    $this->line('  dan pastikan nama service database sama dengan yang dipakai di ${{...}}.');

                    return false;
                }

                $this->line("  Menunggu database siap ({$i}/{$attempts})...");
                DB::purge();
                sleep($sleepSeconds);
            }
        }

        return false;
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

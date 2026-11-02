<?php

namespace App\Console\Commands;

use App\Console\Concerns\WaitsForDatabase;
use App\Models\AboutPage;
use App\Models\HomePage;
use Closure;
use Database\Seeders\AboutSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\HomeSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Start-up untuk hosting stateless (Vercel), dijalankan di background oleh
 * docker/vercel-start.sh sementara web server sudah listen:
 *  1. migrasi database,
 *  2. isi konten About awal, lalu konten Beranda awal, masing-masing sampai
 *     selesai SEKALI (penanda di cache database, jadi start yang terputus
 *     dilanjutkan di start berikutnya). Konten tidak pernah diisi ulang setelah
 *     itu — data yang dihapus editor tidak muncul lagi,
 *  3. pastikan akun admin pertama (ADMIN_EMAIL/ADMIN_PASSWORD) ada,
 *  4. impor media dari CMS_ASSETS_SOURCE bila di-set (file yang ada dilewati).
 *
 * Beberapa instance bisa start bersamaan; di PostgreSQL langkah-langkah ini
 * dikunci dengan advisory lock supaya tidak berjalan dobel.
 */
#[Signature('cms:bootstrap')]
#[Description('Migrasi + selesaikan data awal (sekali) + pastikan admin pertama ada')]
class BootstrapCms extends Command
{
    use WaitsForDatabase;

    public const SEED_MARKER = 'cms:initial-seed-done';

    public const HOME_SEED_MARKER = 'cms:home-seed-done';

    private const LOCK_KEY = 4_815_162_342;

    public function handle(): int
    {
        if (! $this->waitForDatabase()) {
            return self::FAILURE;
        }

        return $this->withStartupLock(function (): int {
            $this->call('migrate', ['--force' => true]);

            if (! Cache::get(self::SEED_MARKER)) {
                // AboutSeeder berjalan dalam satu transaksi: bila terputus, tidak
                // ada AboutPage, sehingga start berikutnya mengulang dari awal.
                // Bila konten sudah ada (mis. database lama), jangan ditimpa.
                if (! AboutPage::query()->exists()) {
                    $this->components->info('Database kosong — mengisi konten About awal.');
                    $this->call('db:seed', ['--class' => AboutSeeder::class, '--force' => true]);
                }

                Cache::forever(self::SEED_MARKER, true);
            }

            // Penanda terpisah agar database yang sudah berisi About (deploy
            // sebelum ada Beranda) tetap mendapat konten Beranda awal sekali.
            if (! Cache::get(self::HOME_SEED_MARKER)) {
                if (! HomePage::query()->exists()) {
                    $this->components->info('Konten Beranda kosong — mengisi konten Beranda awal.');
                    $this->call('db:seed', ['--class' => HomeSeeder::class, '--force' => true]);
                }

                Cache::forever(self::HOME_SEED_MARKER, true);
            }

            $this->call('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true]);

            if ($source = config('cms.assets_source')) {
                $this->call('cms:import-assets', ['source' => $source]);
            }

            $this->components->info('cms:bootstrap selesai.');

            return self::SUCCESS;
        });
    }

    /**
     * @param  Closure(): int  $callback
     */
    private function withStartupLock(Closure $callback): int
    {
        if (DB::getDriverName() !== 'pgsql') {
            return $callback();
        }

        DB::select('select pg_advisory_lock(?)', [self::LOCK_KEY]);

        try {
            return $callback();
        } finally {
            DB::select('select pg_advisory_unlock(?)', [self::LOCK_KEY]);
        }
    }
}

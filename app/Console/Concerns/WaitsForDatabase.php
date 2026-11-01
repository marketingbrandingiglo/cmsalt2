<?php

namespace App\Console\Concerns;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Di hosting, database (MySQL Railway, Neon yang sedang "tidur") bisa belum
 * siap menerima koneksi tepat saat container start — tunggu dulu, lalu beri
 * pesan yang jelas bila konfigurasinya memang salah.
 */
trait WaitsForDatabase
{
    protected function waitForDatabase(int $attempts = 30, int $sleepSeconds = 2): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}", []);

        // DB_URL (connection string) menimpa host/database.
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
            $this->components->warn('DB_CONNECTION masih sqlite — data akan hilang saat redeploy. Set DB_CONNECTION (pgsql/mysql) dan DB_URL.');
        }

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                DB::connection()->getPdo();

                return true;
            } catch (Throwable $e) {
                if ($i === $attempts) {
                    $this->components->error('Tidak bisa terhubung ke database: '.$e->getMessage());
                    $this->line('  Periksa variabel DB_CONNECTION dan DB_URL (tanpa tanda kutip, tercentang untuk Production).');
                    $this->line('  Host 127.0.0.1 berarti DB_URL tidak terbaca. Setelah mengubah variabel, Redeploy.');

                    return false;
                }

                $this->line("  Menunggu database siap ({$i}/{$attempts})...");
                DB::purge();
                sleep($sleepSeconds);
            }
        }

        return false;
    }
}

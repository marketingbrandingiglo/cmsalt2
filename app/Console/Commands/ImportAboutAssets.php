<?php

namespace App\Console\Commands;

use App\Models\AboutPage;
use App\Models\Client;
use App\Models\CompanyValue;
use App\Models\Mascot;
use App\Models\MilestoneLogo;
use App\Models\Partner;
use App\Support\AboutContent;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Menyalin gambar/video halaman About dari folder public/ frontend
 * (mis. ../iglowebsitealt2/public) ke storage CMS, dengan path yang sama
 * seperti yang tersimpan di database. Setelah diimpor, media bisa
 * dipratinjau & diganti dari panel admin dan dilayani langsung oleh CMS.
 */
#[Signature('cms:import-assets {source : Path folder public/ milik frontend Next.js} {--force : Timpa file yang sudah ada}')]
#[Description('Impor media halaman About dari folder public/ frontend ke storage CMS')]
class ImportAboutAssets extends Command
{
    public function handle(): int
    {
        $source = rtrim($this->argument('source'), '/');

        if (! is_dir($source)) {
            $this->error("Folder tidak ditemukan: {$source}");

            return self::FAILURE;
        }

        $disk = Storage::disk(config('cms.media_disk'));
        $copied = $skipped = 0;
        $missing = [];

        foreach ($this->paths() as $path) {
            $path = ltrim($path, '/');
            $file = "{$source}/{$path}";

            if (! is_file($file)) {
                $missing[] = $path;

                continue;
            }

            if ($disk->exists($path) && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            $stream = fopen($file, 'r');
            $disk->writeStream($path, $stream, ['visibility' => 'public']);
            is_resource($stream) && fclose($stream);
            $copied++;
        }

        AboutContent::flush();

        $this->info("Disalin: {$copied}, dilewati (sudah ada): {$skipped}, tidak ditemukan: ".count($missing));

        foreach ($missing as $path) {
            $this->warn("  - {$path}");
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function paths(): array
    {
        $page = AboutPage::singleton();

        return collect([$page->hero_image, $page->i5_logo, $page->video_file, $page->video_poster])
            ->merge(CompanyValue::pluck('icon'))
            ->merge(Mascot::pluck('scene_image'))
            ->merge(Mascot::pluck('profile_image'))
            ->merge(MilestoneLogo::pluck('logo'))
            ->merge(Partner::pluck('logo'))
            ->merge(Client::pluck('logo'))
            ->filter()
            ->reject(fn (string $path) => str_starts_with($path, 'http'))
            ->unique()
            ->values()
            ->all();
    }
}

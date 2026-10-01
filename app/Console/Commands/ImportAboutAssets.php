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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Menyalin gambar/video halaman About dari frontend ke storage CMS, dengan
 * path yang sama seperti yang tersimpan di database. Sumber bisa berupa
 * folder public/ frontend (mis. ../iglowebsitealt2/public) atau URL situs
 * frontend (mis. https://iglowebsitealt2.vercel.app). Setelah diimpor,
 * media bisa dipratinjau & diganti dari panel admin dan dilayani oleh CMS.
 */
#[Signature('cms:import-assets {source : Folder public/ frontend atau URL situs frontend} {--force : Timpa file yang sudah ada}')]
#[Description('Impor media halaman About dari folder public/ frontend ke storage CMS')]
class ImportAboutAssets extends Command
{
    public function handle(): int
    {
        $source = rtrim($this->argument('source'), '/');
        $remote = Str::startsWith($source, ['http://', 'https://']);

        if (! $remote && ! is_dir($source)) {
            $this->error("Folder tidak ditemukan: {$source}");

            return self::FAILURE;
        }

        $disk = Storage::disk(config('cms.media_disk'));
        $copied = $skipped = 0;
        $missing = [];

        foreach ($this->paths() as $path) {
            $path = ltrim($path, '/');

            if ($disk->exists($path) && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            $stream = $remote ? $this->download($source, $path) : $this->open("{$source}/{$path}");

            if (! $stream) {
                $missing[] = $path;

                continue;
            }

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
     * @return resource|null
     */
    private function open(string $file)
    {
        return is_file($file) ? fopen($file, 'r') : null;
    }

    /**
     * @return resource|null
     */
    private function download(string $baseUrl, string $path)
    {
        $url = $baseUrl.'/'.implode('/', array_map('rawurlencode', explode('/', $path)));
        $sink = tmpfile();

        try {
            $response = Http::timeout(120)->retry(2, 1000, throw: false)->sink($sink)->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        rewind($sink);

        return $sink;
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

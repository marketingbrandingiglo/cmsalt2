<?php

namespace App\Console\Commands;

use App\Models\AboutPage;
use App\Models\Client;
use App\Models\CompanyValue;
use App\Models\HomeProduct;
use App\Models\HomeSlide;
use App\Models\Mascot;
use App\Models\MilestoneLogo;
use App\Models\NewsArticle;
use App\Models\Partner;
use App\Support\AboutContent;
use App\Support\HomeContent;
use App\Support\Media;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Menyalin gambar/video halaman About & Beranda dari frontend ke storage CMS, dengan
 * path yang sama seperti yang tersimpan di database. Sumber bisa berupa
 * folder public/ frontend (mis. ../iglowebsitealt2/public) atau URL situs
 * frontend (mis. https://iglowebsitealt2.vercel.app). Setelah diimpor,
 * media bisa dipratinjau & diganti dari panel admin dan dilayani oleh CMS.
 */
#[Signature('cms:import-assets {source : Folder public/ frontend atau URL situs frontend} {--force : Timpa file yang sudah ada}')]
#[Description('Impor media halaman About & Beranda dari folder public/ frontend ke storage CMS')]
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

        $disk = Media::disk();
        $copied = $skipped = 0;
        $missing = $failed = [];

        foreach ($this->paths() as $path) {
            $path = ltrim($path, '/');

            if (! $this->option('force') && Media::exists($path)) {
                $skipped++;

                continue;
            }

            $stream = $remote ? $this->download($source, $path) : $this->open("{$source}/{$path}");

            if (! $stream) {
                $missing[] = $path;

                continue;
            }

            // Tanpa ACL di object storage (R2 menolaknya) — lihat Media::visibility().
            $written = $disk->writeStream($path, $stream, array_filter(['visibility' => Media::visibility()]));
            is_resource($stream) && fclose($stream);

            if ($written === false) {
                $failed[] = $path;

                continue;
            }

            $copied++;
        }

        AboutContent::flush();
        HomeContent::flush();

        $this->info("Disalin: {$copied}, dilewati (sudah ada): {$skipped}, tidak ditemukan: ".count($missing));

        foreach ($missing as $path) {
            $this->warn("  - {$path}");
        }

        foreach ($failed as $path) {
            $this->error("  Gagal menulis ke disk media: {$path}");
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
            ->merge(HomeSlide::pluck('image'))
            ->merge(HomeProduct::pluck('image'))
            ->merge(NewsArticle::pluck('image'))
            ->filter()
            ->reject(fn (string $path) => str_starts_with($path, 'http'))
            ->unique()
            ->values()
            ->all();
    }
}

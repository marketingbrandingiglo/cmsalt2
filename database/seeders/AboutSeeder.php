<?php

namespace Database\Seeders;

use App\Models\AboutHighlight;
use App\Models\AboutPage;
use App\Models\ClientCategory;
use App\Models\CompanyValue;
use App\Models\Mascot;
use App\Models\MilestonePeriod;
use App\Models\Partner;
use App\Support\AboutContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi CMS dengan konten halaman About yang saat ini ada di
 * lib/content.js frontend (iglowebsitealt2). Sumber: data/about.json.
 *
 * Aman dijalankan ulang: seluruh tabel About dikosongkan lalu diisi ulang.
 */
class AboutSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(__DIR__.'/data/about.json'), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data): void {
            foreach ([Partner::class, ClientCategory::class, MilestonePeriod::class, Mascot::class, CompanyValue::class, AboutHighlight::class, AboutPage::class] as $model) {
                $model::query()->each(fn ($record) => $record->delete());
            }

            AboutPage::create($data['page']);

            foreach ($data['highlights'] as $i => $highlight) {
                AboutHighlight::create($highlight + ['sort_order' => $i + 1]);
            }

            foreach ($data['values'] as $i => $value) {
                CompanyValue::create($value + ['sort_order' => $i + 1]);
            }

            foreach ($data['mascots'] as $i => $mascot) {
                Mascot::create($mascot + ['sort_order' => $i + 1]);
            }

            foreach ($data['milestones'] as $i => $milestone) {
                $period = MilestonePeriod::create(['period' => $milestone['period'], 'sort_order' => $i + 1]);

                foreach ($milestone['logos'] as $j => $logo) {
                    $period->logos()->create($logo + ['sort_order' => $j + 1]);
                }
            }

            foreach ($data['partners'] as $i => $partner) {
                Partner::create($partner + ['sort_order' => $i + 1]);
            }

            foreach ($data['clientCategories'] as $i => $category) {
                $record = ClientCategory::create(['name' => $category['name'], 'sort_order' => $i + 1]);

                foreach ($category['clients'] as $j => $client) {
                    $record->clients()->create($client + ['sort_order' => $j + 1]);
                }
            }
        });

        AboutContent::flush();

        if ($source = env('CMS_ASSETS_SOURCE')) {
            $this->command?->call('cms:import-assets', ['source' => $source]);
        }
    }
}

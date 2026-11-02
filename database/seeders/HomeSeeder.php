<?php

namespace Database\Seeders;

use App\Models\HomePage;
use App\Models\HomeProduct;
use App\Models\HomeSlide;
use App\Models\NewsArticle;
use App\Support\HomeContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Mengisi CMS dengan konten halaman Beranda yang saat ini ada di
 * lib/content.js frontend (iglowebsitealt2). Sumber: data/home.json.
 *
 * Aman dijalankan ulang: seluruh tabel Beranda dikosongkan lalu diisi ulang.
 */
class HomeSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(__DIR__.'/data/home.json'), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data): void {
            foreach ([NewsArticle::class, HomeProduct::class, HomeSlide::class, HomePage::class] as $model) {
                $model::query()->each(fn ($record) => $record->delete());
            }

            HomePage::create($data['page']);

            foreach ($data['slides'] as $i => $slide) {
                HomeSlide::create($slide + ['sort_order' => $i + 1]);
            }

            foreach ($data['products'] as $i => $product) {
                HomeProduct::create($product + ['sort_order' => $i + 1]);
            }

            foreach ($data['news'] as $article) {
                NewsArticle::create($article);
            }
        });

        HomeContent::flush();
    }
}

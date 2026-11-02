<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ManageHomeIntro;
use App\Filament\Resources\HomeProducts\HomeProductResource;
use App\Filament\Resources\HomeSlides\HomeSlideResource;
use App\Filament\Resources\NewsArticles\NewsArticleResource;
use App\Models\HomePage;
use App\Models\HomeProduct;
use App\Models\HomeSlide;
use App\Models\NewsArticle;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HomeOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected ?string $heading = 'Halaman Beranda';

    protected function getStats(): array
    {
        return [
            Stat::make('Terakhir diperbarui', HomePage::singleton()->updated_at?->diffForHumans() ?? '—')
                ->description('Konten halaman')
                ->icon(Heroicon::OutlinedHome)
                ->url(ManageHomeIntro::getUrl()),
            Stat::make('Slide aktif', HomeSlide::active()->count())
                ->icon(Heroicon::OutlinedPhoto)
                ->url(HomeSlideResource::getUrl()),
            Stat::make('Produk aktif', HomeProduct::active()->count())
                ->icon(Heroicon::OutlinedSquares2x2)
                ->url(HomeProductResource::getUrl()),
            Stat::make('Berita aktif', NewsArticle::active()->count())
                ->icon(Heroicon::OutlinedNewspaper)
                ->url(NewsArticleResource::getUrl()),
        ];
    }
}

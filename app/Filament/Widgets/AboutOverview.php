<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ManageAboutPage;
use App\Filament\Resources\ClientCategories\ClientCategoryResource;
use App\Filament\Resources\MilestonePeriods\MilestonePeriodResource;
use App\Filament\Resources\Partners\PartnerResource;
use App\Models\AboutPage;
use App\Models\Client;
use App\Models\MilestoneLogo;
use App\Models\Partner;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AboutOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Halaman Tentang Kami';

    protected function getStats(): array
    {
        return [
            Stat::make('Terakhir diperbarui', AboutPage::singleton()->updated_at?->diffForHumans() ?? '—')
                ->description('Konten halaman')
                ->icon(Heroicon::OutlinedDocumentText)
                ->url(ManageAboutPage::getUrl()),
            Stat::make('Logo milestone', MilestoneLogo::count())
                ->icon(Heroicon::OutlinedTrophy)
                ->url(MilestonePeriodResource::getUrl()),
            Stat::make('Partner aktif', Partner::active()->count())
                ->icon(Heroicon::OutlinedBriefcase)
                ->url(PartnerResource::getUrl()),
            Stat::make('Klien aktif', Client::active()->count())
                ->icon(Heroicon::OutlinedUserGroup)
                ->url(ClientCategoryResource::getUrl()),
        ];
    }
}

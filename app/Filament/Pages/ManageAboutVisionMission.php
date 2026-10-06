<?php

namespace App\Filament\Pages;

use App\Filament\Support\Bilingual;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ManageAboutVisionMission extends AboutContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationParentItem = ManageAboutPage::NAVIGATION_LABEL;

    protected static ?string $navigationLabel = 'Visi & Misi';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'about/content/vision-mission';

    protected static ?string $title = 'Visi & Misi';

    protected function fields(): array
    {
        return [
            Section::make('Visi')
                ->schema([
                    Bilingual::input('visionTitle', 'Judul visi', 'content'),
                    Bilingual::textarea('vision', 'Visi', 'content', rows: 3),
                ]),
            Section::make('Misi')
                ->schema([
                    Bilingual::input('missionTitle', 'Judul misi', 'content'),
                    Bilingual::textarea('mission', 'Misi', 'content', rows: 3),
                ]),
        ];
    }
}

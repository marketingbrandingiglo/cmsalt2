<?php

namespace App\Filament\Pages;

use App\Filament\Support\Bilingual;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ManageAboutCompany extends AboutContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationParentItem = ManageAboutPage::NAVIGATION_LABEL;

    protected static ?string $navigationLabel = 'Siapa Kami';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'about/content/who-we-are';

    protected static ?string $title = 'Siapa Kami';

    protected function fields(): array
    {
        return [
            Section::make('Siapa Kami')
                ->description('Panel "Who We Are" di scene logo i5.')
                ->schema([
                    Bilingual::input('company.name', 'Nama perusahaan', 'content', required: true),
                    Bilingual::input('whoWeAreKicker', 'Label "Siapa Kami"', 'content'),
                    Bilingual::textarea('company.p1', 'Paragraf 1', 'content'),
                    Bilingual::textarea('company.p2', 'Paragraf 2', 'content'),
                ]),
        ];
    }
}

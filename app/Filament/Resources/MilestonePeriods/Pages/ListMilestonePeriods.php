<?php

namespace App\Filament\Resources\MilestonePeriods\Pages;

use App\Filament\Concerns\EditsAboutSection;
use App\Filament\Resources\MilestonePeriods\MilestonePeriodResource;
use App\Filament\Support\Bilingual;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMilestonePeriods extends ListRecords
{
    use EditsAboutSection;

    protected static string $resource = MilestonePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function aboutSectionHeading(): string
    {
        return 'Judul seksi Milestone';
    }

    protected function aboutSectionDescription(): ?string
    {
        return 'Judul & narasi seksi Milestone. Periode beserta logonya ada di tabel di bawah.';
    }

    protected function aboutSectionFields(): array
    {
        return [
            Bilingual::input('milestone.title', 'Judul milestone', 'content'),
            Bilingual::textarea('milestone.narrative', 'Narasi', 'content'),
        ];
    }
}

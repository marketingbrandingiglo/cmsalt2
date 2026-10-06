<?php

namespace App\Filament\Resources\Partners\Pages;

use App\Filament\Concerns\EditsAboutSection;
use App\Filament\Resources\Partners\PartnerResource;
use App\Filament\Support\Bilingual;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePartners extends ManageRecords
{
    use EditsAboutSection;

    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function aboutSectionHeading(): string
    {
        return 'Judul seksi Partner';
    }

    protected function aboutSectionDescription(): ?string
    {
        return 'Judul seksi “Our Partner”. Logo partner ada di tabel di bawah.';
    }

    protected function aboutSectionFields(): array
    {
        return [
            Bilingual::input('partner.title', 'Judul seksi partner', 'content'),
        ];
    }
}

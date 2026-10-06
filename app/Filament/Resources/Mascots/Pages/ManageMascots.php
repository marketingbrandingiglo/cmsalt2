<?php

namespace App\Filament\Resources\Mascots\Pages;

use App\Filament\Concerns\EditsAboutSection;
use App\Filament\Resources\Mascots\MascotResource;
use App\Filament\Support\Bilingual;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMascots extends ManageRecords
{
    use EditsAboutSection;

    protected static string $resource = MascotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function aboutSectionHeading(): string
    {
        return 'Judul seksi Maskot';
    }

    protected function aboutSectionDescription(): ?string
    {
        return 'Judul seksi “Kenali Maskot Kami”. Bio & gambar tiap maskot ada di tabel di bawah.';
    }

    protected function aboutSectionFields(): array
    {
        return [
            Bilingual::input('mascotTitle', 'Judul seksi maskot', 'content'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ClientCategories\Pages;

use App\Filament\Concerns\EditsAboutSection;
use App\Filament\Resources\ClientCategories\ClientCategoryResource;
use App\Filament\Support\Bilingual;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientCategories extends ListRecords
{
    use EditsAboutSection;

    protected static string $resource = ClientCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function aboutSectionHeading(): string
    {
        return 'Judul seksi Klien';
    }

    protected function aboutSectionDescription(): ?string
    {
        return 'Judul seksi “Klien Kami”. Tab kategori & logo klien ada di tabel di bawah.';
    }

    protected function aboutSectionFields(): array
    {
        return [
            Bilingual::input('client.title', 'Judul seksi klien', 'content'),
        ];
    }
}

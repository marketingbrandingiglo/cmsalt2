<?php

namespace App\Filament\Resources\HomeProducts\Pages;

use App\Filament\Concerns\EditsHomeSection;
use App\Filament\Resources\HomeProducts\HomeProductResource;
use App\Filament\Support\Bilingual;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHomeProducts extends ManageRecords
{
    use EditsHomeSection;

    protected static string $resource = HomeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function homeSectionHeading(): string
    {
        return 'Judul seksi Produk & Solusi';
    }

    protected function homeSectionDescription(): ?string
    {
        return 'Kicker, judul & deskripsi di atas carousel produk. Kartu produk ada di tabel di bawah.';
    }

    protected function homeSectionFields(): array
    {
        return [
            Bilingual::input('productsSection.kicker', 'Kicker', 'content'),
            Bilingual::input('productsSection.title', 'Judul seksi', 'content'),
            Bilingual::textarea('productsSection.desc', 'Deskripsi', 'content', rows: 2),
        ];
    }
}

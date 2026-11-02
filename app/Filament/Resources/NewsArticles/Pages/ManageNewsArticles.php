<?php

namespace App\Filament\Resources\NewsArticles\Pages;

use App\Filament\Concerns\EditsHomeSection;
use App\Filament\Resources\NewsArticles\NewsArticleResource;
use App\Filament\Support\Bilingual;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNewsArticles extends ManageRecords
{
    use EditsHomeSection;

    protected static string $resource = NewsArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function homeSectionHeading(): string
    {
        return 'Judul seksi Berita & Insight';
    }

    protected function homeSectionDescription(): ?string
    {
        return 'Kicker & judul di atas mosaik berita. Daftar berita ada di tabel di bawah.';
    }

    protected function homeSectionFields(): array
    {
        return [
            Bilingual::input('news.kicker', 'Kicker', 'content'),
            Bilingual::input('news.title', 'Judul seksi', 'content'),
        ];
    }
}

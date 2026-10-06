<?php

namespace App\Filament\Pages;

use App\Filament\Support\Bilingual;
use App\Filament\Support\MediaUpload;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

/**
 * Menu "Konten Halaman" — bagian Hero. Sub-menunya: Siapa Kami,
 * Visi & Misi, Statistik & Keunggulan. Judul seksi lain (Nilai i5, Maskot,
 * Milestone, Partner, Klien) ada di atas tabel pada menu masing-masing;
 * Video punya menu sendiri.
 */
class ManageAboutPage extends AboutContentPage
{
    public const NAVIGATION_LABEL = 'Konten Halaman';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = self::NAVIGATION_LABEL;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'about/content';

    protected static ?string $title = 'Hero Halaman Tentang Kami';

    protected function successMessage(): string
    {
        return 'Hero halaman Tentang Kami tersimpan.';
    }

    protected function fields(): array
    {
        return [
            Section::make('Hero')
                ->description('Banner paling atas halaman /about.')
                ->schema([
                    MediaUpload::image('hero_image', 'hero')
                        ->label('Gambar banner hero')
                        ->helperText('Rekomendasi 1920×607 px. Dipakai PageHeroBanner di bagian paling atas.'),
                    Bilingual::input('hero.kicker', 'Kicker', 'content'),
                    Bilingual::input('hero.title', 'Judul', 'content', required: true),
                    Bilingual::textarea('hero.subtitle', 'Subjudul', 'content'),
                ]),
        ];
    }
}

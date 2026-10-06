<?php

namespace App\Filament\Pages;

use App\Filament\Support\Bilingual;
use App\Filament\Support\MediaUpload;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ManageAboutVideo extends AboutContentPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?string $navigationParentItem = ManageAboutPage::NAVIGATION_LABEL;

    protected static ?string $navigationLabel = 'Video';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'about/content/video';

    protected static ?string $title = 'Video Perusahaan';

    protected function fields(): array
    {
        return [
            Section::make('Video perusahaan')
                ->schema([
                    MediaUpload::video('video_file', 'video')
                        ->label('File video perusahaan (MP4/WebM, maks. 100 MB)'),
                    MediaUpload::image('video_poster', 'video')
                        ->label('Poster / thumbnail video (opsional)'),
                    Bilingual::input('video.title', 'Judul', 'content'),
                    Bilingual::textarea('video.text', 'Deskripsi', 'content'),
                ]),
        ];
    }
}

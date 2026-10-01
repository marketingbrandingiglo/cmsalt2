<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;

class MediaUpload
{
    public static function image(string $name, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk(config('cms.media_disk'))
            ->directory("about/{$directory}")
            ->visibility('public')
            ->maxSize(5120)
            ->imagePreviewHeight('120')
            ->downloadable()
            ->openable();
    }

    public static function video(string $name, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->acceptedFileTypes(['video/mp4', 'video/webm'])
            ->disk(config('cms.media_disk'))
            ->directory("about/{$directory}")
            ->visibility('public')
            ->maxSize(102400)
            ->downloadable()
            ->openable();
    }
}

<?php

namespace App\Filament\Support;

use App\Support\Media;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Arr;

/**
 * Upload media ke disk CMS, dikelompokkan per halaman: {page}/{directory},
 * mis. about/partners atau home/slides.
 */
class MediaUpload
{
    public static function image(string $name, string $directory, string $page = 'about'): FileUpload
    {
        return self::keepFrontendAssets(FileUpload::make($name))
            ->image()
            ->disk(config('cms.media_disk'))
            ->directory("{$page}/{$directory}")
            ->visibility(Media::visibility())
            ->maxSize(5120)
            ->imagePreviewHeight('120')
            ->downloadable()
            ->openable();
    }

    public static function video(string $name, string $directory, string $page = 'about'): FileUpload
    {
        return self::keepFrontendAssets(FileUpload::make($name))
            ->acceptedFileTypes(['video/mp4', 'video/webm'])
            ->disk(config('cms.media_disk'))
            ->directory("{$page}/{$directory}")
            ->visibility(Media::visibility())
            ->maxSize(102400)
            ->downloadable()
            ->openable();
    }

    /**
     * Path hasil seed (mis. "img/Banner/banner_aboutus.png") bisa menunjuk ke
     * aset milik frontend yang belum diimpor ke disk media (mis. bucket R2 baru).
     * Bawaan Filament membuang path seperti itu saat form dimuat, sehingga
     * menyimpan teks di halaman yang sama ikut menghapus gambarnya. Di sini path
     * tetap dipertahankan, dengan pratinjau dari URL frontend.
     */
    private static function keepFrontendAssets(FileUpload $upload): FileUpload
    {
        return $upload
            ->afterStateHydrated(static function (FileUpload $component): void {
                $component->rawState(array_filter(Arr::wrap($component->getRawState()), filled(...)));
            })
            ->getUploadedFileUsing(static function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                if (Media::exists($file)) {
                    return $component->getUploadedFile($file, $storedFileNames);
                }

                return [
                    'name' => basename($file),
                    'size' => 0,
                    'type' => null,
                    'url' => Media::url($file),
                ];
            });
    }
}

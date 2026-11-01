<?php

namespace App\Providers;

use App\Support\AboutContent;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Di balik proxy hosting, pastikan semua URL (aset Filament, upload,
        // media API) memakai https bila APP_URL https.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Atur urutan (drag & drop) di tabel Filament menulis langsung ke
        // database tanpa event model — bersihkan cache API secara eksplisit.
        Table::configureUsing(fn (Table $table) => $table->afterReordering(fn () => AboutContent::flush()));

        // Bila media disimpan di object storage (R2), upload sementara Livewire
        // juga ke sana — langsung dari browser lewat URL bertanda tangan. Di
        // Vercel tiap request bisa jatuh ke instance berbeda (tanpa disk
        // bersama) dan body request dibatasi ±4,5 MB.
        $mediaDisk = config('cms.media_disk');

        if (config("filesystems.disks.{$mediaDisk}.driver") === 's3') {
            config(['livewire.temporary_file_upload.disk' => $mediaDisk]);
        }
    }
}

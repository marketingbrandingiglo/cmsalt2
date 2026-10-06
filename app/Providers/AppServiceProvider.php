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
    }
}

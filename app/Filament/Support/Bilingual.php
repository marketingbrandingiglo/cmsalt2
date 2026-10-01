<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

/**
 * Field bilingual berdampingan: kolom kiri Bahasa Indonesia, kanan English.
 *
 * Dua bentuk penyimpanan didukung:
 * - per kolom JSON  : Bilingual::input('text', 'Teks')               → text.id / text.en
 * - per locale (root): Bilingual::input('hero.title', 'Judul', 'content') → content.id.hero.title / content.en.hero.title
 */
class Bilingual
{
    public static function input(string $name, string $label, ?string $root = null, bool $required = false): Grid
    {
        return self::make(fn (string $path) => TextInput::make($path)->maxLength(255), $name, $label, $root, $required);
    }

    public static function textarea(string $name, string $label, ?string $root = null, bool $required = false, int $rows = 4): Grid
    {
        return self::make(fn (string $path) => Textarea::make($path)->rows($rows)->autosize(), $name, $label, $root, $required);
    }

    /**
     * @param  callable(string): Field  $factory
     */
    public static function make(callable $factory, string $name, string $label, ?string $root = null, bool $required = false): Grid
    {
        $fields = [];

        foreach (config('cms.locales') as $locale => $language) {
            $path = $root ? "{$root}.{$locale}.{$name}" : "{$name}.{$locale}";

            $fields[] = $factory($path)
                ->label($label.' ('.strtoupper($locale).')')
                ->hint($language)
                // Hanya locale utama yang wajib; locale lain jatuh ke fallback bila kosong.
                ->required($required && $locale === config('cms.fallback_locale'));
        }

        return Grid::make(count($fields))->schema($fields)->columnSpanFull();
    }
}

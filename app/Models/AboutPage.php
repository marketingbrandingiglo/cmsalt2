<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['hero_image', 'i5_logo', 'video_file', 'video_poster', 'content'])]
class AboutPage extends Model
{
    use FlushesAboutCache;

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    /**
     * Halaman About hanya satu — ambil baris pertama atau buat baris kosong.
     */
    public static function singleton(): self
    {
        return static::query()->oldest('id')->firstOr(
            fn () => static::create(['content' => ['id' => [], 'en' => []]]),
        );
    }

    /**
     * Teks per locale dengan fallback per-kunci ke Bahasa Indonesia.
     */
    public function contentFor(string $locale): array
    {
        $content = $this->content ?? [];
        $fallback = $content[config('cms.fallback_locale')] ?? [];

        return self::mergeFallback($fallback, $content[$locale] ?? []);
    }

    private static function mergeFallback(array $fallback, array $value): array
    {
        foreach ($fallback as $key => $default) {
            if (is_array($default)) {
                $value[$key] = self::mergeFallback($default, is_array($value[$key] ?? null) ? $value[$key] : []);
            } elseif (blank($value[$key] ?? null)) {
                $value[$key] = $default;
            }
        }

        return $value;
    }
}

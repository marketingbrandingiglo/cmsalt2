<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['content'])]
class HomePage extends Model
{
    use FlushesHomeCache;

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    /**
     * Halaman Beranda hanya satu — ambil baris pertama atau buat baris kosong.
     */
    public static function singleton(): self
    {
        return static::query()->oldest('id')->firstOr(
            fn () => static::create(['content' => ['id' => [], 'en' => []]]),
        );
    }

    /**
     * Simpan sebagian konten (mis. hanya Intro) tanpa menghapus bagian lain:
     * teks digabung per kunci ke JSON `content` yang ada.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateContent(array $data): void
    {
        if (array_key_exists('content', $data)) {
            $data['content'] = array_replace_recursive($this->content ?? [], (array) $data['content']);
        }

        $this->update($data);
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

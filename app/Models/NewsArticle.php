<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tag', 'published_at', 'title', 'excerpt', 'image', 'is_active'])]
class NewsArticle extends Model
{
    use FlushesHomeCache, HasTranslations;

    // Kategori = kunci t.news.filters di frontend (label badge di kartu berita).
    public const TAGS = [
        'announcement' => ['id' => 'Pengumuman', 'en' => 'Announcement'],
        'product' => ['id' => 'Produk', 'en' => 'Product'],
        'event' => ['id' => 'Acara', 'en' => 'Event'],
        'insight' => ['id' => 'Wawasan', 'en' => 'Insight'],
    ];

    // Format tanggal tampilan seperti lib/content.js: "18 Agu 2026" / "Aug 18, 2026".
    public const DATE_FORMATS = [
        'id' => 'j M Y',
        'en' => 'M j, Y',
    ];

    // Singkatan bulan versi website ("Agu"), bukan bawaan Carbon ("Agt").
    private const ID_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'title' => 'array',
            'excerpt' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function tagOptions(): array
    {
        return array_map(fn (array $label) => $label['id'], self::TAGS);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function displayDate(string $locale): string
    {
        $date = $this->published_at;

        if ($locale === 'id') {
            return $date->day.' '.self::ID_MONTHS[$date->month - 1].' '.$date->year;
        }

        return $date->locale($locale)->translatedFormat(self::DATE_FORMATS[$locale] ?? self::DATE_FORMATS['en']);
    }
}

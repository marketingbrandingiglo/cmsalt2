<?php

namespace App\Support;

use App\Models\HomePage;
use App\Models\HomeProduct;
use App\Models\HomeSlide;
use App\Models\NewsArticle;
use Illuminate\Support\Facades\Cache;

/**
 * Menyusun konten halaman Beranda dengan bentuk yang sama seperti kunci
 * `hero`, `intro`, `productsSection`, `products` dan `news` pada
 * `content[lang]` di lib/content.js frontend, plus URL media.
 */
class HomeContent
{
    private const CACHE_KEY = 'cms.home.payload';

    /**
     * @return array<string, array<string, mixed>> keyed by locale
     */
    public static function all(): array
    {
        $build = fn () => collect(array_keys(config('cms.locales')))
            ->mapWithKeys(fn (string $locale) => [$locale => self::build($locale)])
            ->all();

        $ttl = config('cms.cache_ttl');

        return $ttl > 0 ? Cache::remember(self::CACHE_KEY, $ttl, $build) : $build();
    }

    public static function forLocale(string $locale): array
    {
        return self::all()[$locale];
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function build(string $locale): array
    {
        $text = HomePage::singleton()->contentFor($locale);

        return [
            'hero' => HomeSlide::active()->ordered()->get()->map(fn (HomeSlide $s) => [
                'image' => Media::url($s->image),
                'title' => $s->translate('title', $locale),
                'desc' => $s->translate('description', $locale),
                'ctaHref' => $s->cta_url,
            ])->all(),
            'intro' => [
                'kicker' => $text['intro']['kicker'] ?? null,
                'title' => $text['intro']['title'] ?? null,
                'cta' => $text['intro']['cta'] ?? null,
            ],
            'productsSection' => [
                'kicker' => $text['productsSection']['kicker'] ?? null,
                'title' => $text['productsSection']['title'] ?? null,
                'desc' => $text['productsSection']['desc'] ?? null,
            ],
            'products' => HomeProduct::active()->ordered()->get()->map(fn (HomeProduct $p) => [
                'slug' => $p->slug,
                'name' => $p->name,
                'short' => $p->translate('short', $locale),
                'long' => $p->translate('long', $locale),
                'pitch' => $p->translate('pitch', $locale),
                'image' => Media::url($p->image),
            ])->all(),
            'news' => [
                'hero' => [
                    'kicker' => $text['news']['kicker'] ?? null,
                    'title' => $text['news']['title'] ?? null,
                ],
                'filters' => array_map(
                    fn (array $label) => $label[$locale] ?? $label[config('cms.fallback_locale')],
                    NewsArticle::TAGS,
                ),
                'articles' => NewsArticle::active()->latestFirst()->get()->map(fn (NewsArticle $a) => [
                    'tag' => $a->tag,
                    'date' => $a->displayDate($locale),
                    'iso' => $a->published_at->toDateString(),
                    'title' => $a->translate('title', $locale),
                    'excerpt' => $a->translate('excerpt', $locale),
                    'image' => Media::url($a->image),
                ])->all(),
            ],
        ];
    }
}

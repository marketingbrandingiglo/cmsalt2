<?php

namespace App\Support;

use App\Models\AboutHighlight;
use App\Models\AboutPage;
use App\Models\ClientCategory;
use App\Models\CompanyValue;
use App\Models\Mascot;
use App\Models\MilestonePeriod;
use App\Models\Partner;
use Illuminate\Support\Facades\Cache;

/**
 * Menyusun konten halaman About dengan bentuk yang sama persis seperti
 * `content[lang].about` di lib/content.js frontend, ditambah URL media
 * (gambar hero, logo i5, ikon nilai, gambar maskot, video).
 */
class AboutContent
{
    private const CACHE_KEY = 'cms.about.payload';

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
        $page = AboutPage::singleton();
        $text = $page->contentFor($locale);

        $highlights = AboutHighlight::active()->ordered()->get();
        $mascots = Mascot::active()->ordered()->get();
        $categories = ClientCategory::active()->ordered()
            ->with(['clients' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $about = [
            'hero' => [
                'kicker' => $text['hero']['kicker'] ?? null,
                'title' => $text['hero']['title'] ?? null,
                'subtitle' => $text['hero']['subtitle'] ?? null,
                'image' => Media::url($page->hero_image),
            ],
            'company' => [
                'name' => $text['company']['name'] ?? null,
                'p1' => $text['company']['p1'] ?? null,
                'p2' => $text['company']['p2'] ?? null,
            ],
            'whoWeAreKicker' => $text['whoWeAreKicker'] ?? null,
            'visionTitle' => $text['visionTitle'] ?? null,
            'vision' => $text['vision'] ?? null,
            'missionTitle' => $text['missionTitle'] ?? null,
            'mission' => $text['mission'] ?? null,
            'valuesIcon' => $text['valuesIcon'] ?? null,
            'i5Logo' => Media::url($page->i5_logo),

            'stats' => $highlights->where('type', 'stat')->values()->map(fn (AboutHighlight $h) => [
                'value' => $h->value,
                'label' => $h->translate('text', $locale),
                'icon' => $h->icon,
            ])->all(),
            'statsExtra' => $highlights->where('type', 'feature')->values()->map(fn (AboutHighlight $h) => [
                'icon' => $h->icon,
                'text' => $h->translate('text', $locale),
            ])->all(),

            'valuesTitle' => $text['valuesTitle'] ?? null,
            'valuesKicker' => $text['valuesKicker'] ?? null,
            'values' => CompanyValue::active()->ordered()->get()->map(fn (CompanyValue $v) => [
                'title' => $v->title,
                'desc' => $v->translate('description', $locale),
                'icon' => Media::url($v->icon),
            ])->all(),

            'mascotTitle' => $text['mascotTitle'] ?? null,
            'mascots' => $mascots->map(fn (Mascot $m) => self::mascot($m, $locale))->all(),
        ];

        // Kunci mascotZenith / mascotElio / mascotAero / mascotNova
        // yang langsung dipakai sebagai prop MascotScene.js.
        foreach ($mascots as $mascot) {
            $about['mascot'.ucfirst($mascot->key)] = self::mascot($mascot, $locale);
        }

        return $about + [
            'milestone' => [
                'title' => $text['milestone']['title'] ?? null,
                'narrative' => $text['milestone']['narrative'] ?? null,
                'items' => MilestonePeriod::active()->ordered()->with('logos')->get()
                    ->map(fn (MilestonePeriod $p) => [
                        'period' => $p->period,
                        'logos' => $p->logos->map(fn ($logo) => [
                            'src' => Media::url($logo->logo),
                            'alt' => $logo->name,
                        ])->all(),
                    ])->all(),
            ],
            'video' => [
                'title' => $text['video']['title'] ?? null,
                'text' => $text['video']['text'] ?? null,
                'src' => Media::url($page->video_file),
                'poster' => Media::url($page->video_poster),
            ],
            'partner' => [
                'title' => $text['partner']['title'] ?? null,
                'logos' => Partner::active()->ordered()->get()->map(fn (Partner $p) => [
                    'src' => Media::url($p->logo),
                    'alt' => $p->name,
                    'url' => $p->url,
                ])->all(),
            ],
            'client' => [
                'title' => $text['client']['title'] ?? null,
                'tabs' => $categories->map(fn (ClientCategory $c) => $c->translate('name', $locale))->all(),
                'logos' => $categories->map(fn (ClientCategory $c) => $c->clients->map(fn ($client) => [
                    'src' => Media::url($client->logo),
                    'alt' => $client->name,
                ])->all())->all(),
            ],
        ];
    }

    private static function mascot(Mascot $mascot, string $locale): array
    {
        return [
            'key' => $mascot->key,
            'name' => $mascot->name,
            'bio' => $mascot->bioFor($locale),
            'sceneImage' => Media::url($mascot->scene_image),
            'profileImage' => Media::url($mascot->profile_image),
        ];
    }
}

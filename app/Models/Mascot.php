<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'bio', 'scene_image', 'profile_image', 'sort_order', 'is_active'])]
class Mascot extends Model
{
    use FlushesAboutCache, HasTranslations, IsSortable;

    // Kunci yang dipakai MascotScene.js (prop zenith/elio/aero/nova).
    public const KEYS = ['zenith', 'elio', 'aero', 'nova'];

    protected function casts(): array
    {
        return [
            'bio' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Paragraf bio untuk satu locale (fallback ke Bahasa Indonesia).
     *
     * @return array<int, string>
     */
    public function bioFor(string $locale): array
    {
        $paragraphs = array_values(array_filter((array) $this->translate('bio', $locale), 'filled'));

        return $paragraphs ?: array_values(array_filter((array) ($this->bio[config('cms.fallback_locale')] ?? []), 'filled'));
    }
}

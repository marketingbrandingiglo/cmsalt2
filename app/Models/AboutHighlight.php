<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'icon', 'value', 'text', 'sort_order', 'is_active'])]
class AboutHighlight extends Model
{
    use FlushesAboutCache, HasTranslations, IsSortable;

    public const TYPES = [
        'stat' => 'Statistik (angka)',
        'feature' => 'Keunggulan (teks)',
    ];

    // Kunci ikon SVG yang tersedia di app/about/page.js (STAT_ICONS).
    public const ICONS = [
        'client' => 'Client (orang)',
        'developer' => 'Developer (kode)',
        'speed' => 'Speed (petir)',
        'layers' => 'Layers (tumpukan)',
    ];

    protected function casts(): array
    {
        return [
            'text' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

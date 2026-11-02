<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['image', 'title', 'description', 'cta_url', 'sort_order', 'is_active'])]
class HomeSlide extends Model
{
    use FlushesHomeCache, HasTranslations, IsSortable;

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

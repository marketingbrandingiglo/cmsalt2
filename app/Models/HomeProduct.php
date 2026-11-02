<?php

namespace App\Models;

use App\Models\Concerns\FlushesHomeCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name', 'short', 'long', 'pitch', 'image', 'sort_order', 'is_active'])]
class HomeProduct extends Model
{
    use FlushesHomeCache, HasTranslations, IsSortable;

    protected function casts(): array
    {
        return [
            'short' => 'array',
            'long' => 'array',
            'pitch' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

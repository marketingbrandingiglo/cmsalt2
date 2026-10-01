<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'icon', 'sort_order', 'is_active'])]
class CompanyValue extends Model
{
    use FlushesAboutCache, HasTranslations, IsSortable;

    protected function casts(): array
    {
        return [
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

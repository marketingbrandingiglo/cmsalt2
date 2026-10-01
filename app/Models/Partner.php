<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'logo', 'url', 'sort_order', 'is_active'])]
class Partner extends Model
{
    use FlushesAboutCache, IsSortable;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

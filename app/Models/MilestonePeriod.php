<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['period', 'sort_order', 'is_active'])]
class MilestonePeriod extends Model
{
    use FlushesAboutCache, IsSortable;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function logos(): HasMany
    {
        return $this->hasMany(MilestoneLogo::class)->orderBy('sort_order')->orderBy('id');
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['milestone_period_id', 'name', 'logo', 'sort_order'])]
class MilestoneLogo extends Model
{
    use FlushesAboutCache, IsSortable;

    public function period(): BelongsTo
    {
        return $this->belongsTo(MilestonePeriod::class, 'milestone_period_id');
    }
}

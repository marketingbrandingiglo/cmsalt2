<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_category_id', 'name', 'logo', 'sort_order', 'is_active'])]
class Client extends Model
{
    use FlushesAboutCache, IsSortable;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClientCategory::class, 'client_category_id');
    }
}

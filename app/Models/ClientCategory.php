<?php

namespace App\Models;

use App\Models\Concerns\FlushesAboutCache;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\IsSortable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'sort_order', 'is_active'])]
class ClientCategory extends Model
{
    use FlushesAboutCache, HasTranslations, IsSortable;

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class)->orderBy('sort_order')->orderBy('id');
    }
}

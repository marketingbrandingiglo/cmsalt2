<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait IsSortable
{
    /**
     * Item baru otomatis ditaruh di urutan paling akhir.
     */
    public static function bootIsSortable(): void
    {
        static::creating(function ($model): void {
            $model->sort_order ??= (int) static::query()->max('sort_order') + 1;
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models\Concerns;

use App\Support\AboutContent;

/**
 * Setiap perubahan konten About menghapus cache respons API,
 * sehingga frontend langsung mendapat data terbaru.
 */
trait FlushesAboutCache
{
    public static function bootFlushesAboutCache(): void
    {
        static::saved(fn () => AboutContent::flush());
        static::deleted(fn () => AboutContent::flush());
    }
}

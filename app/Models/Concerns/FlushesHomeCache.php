<?php

namespace App\Models\Concerns;

use App\Support\HomeContent;

/**
 * Setiap perubahan konten Beranda menghapus cache respons API,
 * sehingga frontend langsung mendapat data terbaru.
 */
trait FlushesHomeCache
{
    public static function bootFlushesHomeCache(): void
    {
        static::saved(fn () => HomeContent::flush());
        static::deleted(fn () => HomeContent::flush());
    }
}

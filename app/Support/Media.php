<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media
{
    /**
     * Ubah path media yang tersimpan di database menjadi URL siap pakai.
     *
     * - URL absolut (http/https) dikembalikan apa adanya.
     * - File yang ada di disk media CMS → URL storage CMS.
     * - Selain itu dianggap aset frontend → FRONTEND_URL + path (atau "/path").
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        $path = ltrim($path, '/');
        $disk = Storage::disk(config('cms.media_disk'));

        if ($disk->exists($path)) {
            return self::encode($disk->url($path));
        }

        return rtrim((string) config('cms.frontend_url'), '/').'/'.$path;
    }

    /**
     * Encode spasi & karakter khusus pada nama folder seperti "Our Partner".
     */
    private static function encode(string $url): string
    {
        $parts = parse_url($url);
        $path = implode('/', array_map('rawurlencode', explode('/', $parts['path'] ?? '')));

        return (isset($parts['scheme']) ? $parts['scheme'].'://' : '')
            .($parts['host'] ?? '')
            .(isset($parts['port']) ? ':'.$parts['port'] : '')
            .$path;
    }
}

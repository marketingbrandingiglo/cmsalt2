<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class Media
{
    /**
     * Daftar file di disk media object storage (R2), dikumpulkan sekali per
     * request: satu request LIST, bukan satu request HEAD per gambar.
     *
     * @var array<string, array<string, int>|null>
     */
    private static array $remoteFiles = [];

    /**
     * Ubah path media yang tersimpan di database menjadi URL siap pakai.
     *
     * - URL absolut (http/https) dikembalikan apa adanya.
     * - File yang ada di disk media CMS → URL storage CMS (atau R2).
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

        if (self::exists($path)) {
            return self::encode(self::disk()->url($path));
        }

        return rtrim((string) config('cms.frontend_url'), '/').'/'.$path;
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(config('cms.media_disk'));
    }

    /**
     * Disk media berupa object storage (driver s3, mis. Cloudflare R2)?
     */
    public static function isRemote(): bool
    {
        return config('filesystems.disks.'.config('cms.media_disk').'.driver') === 's3';
    }

    /**
     * Visibility untuk file yang disimpan. R2 tidak mendukung ACL per objek
     * (akses publik diatur di level bucket), jadi jangan kirim visibility.
     */
    public static function visibility(): ?string
    {
        return self::isRemote() ? null : 'public';
    }

    public static function exists(string $path): bool
    {
        $path = ltrim($path, '/');

        if (! self::isRemote()) {
            return self::disk()->exists($path);
        }

        $name = config('cms.media_disk');

        if (! array_key_exists($name, self::$remoteFiles)) {
            try {
                self::$remoteFiles[$name] = array_flip(self::disk()->allFiles());
            } catch (Throwable) {
                self::$remoteFiles[$name] = null;
            }
        }

        return self::$remoteFiles[$name] === null
            ? self::disk()->exists($path)
            : isset(self::$remoteFiles[$name][$path]);
    }

    /**
     * Lupakan daftar file yang tersimpan (setelah upload/impor/hapus).
     */
    public static function forgetListing(): void
    {
        self::$remoteFiles = [];
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

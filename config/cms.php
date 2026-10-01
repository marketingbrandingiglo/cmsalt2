<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locale konten
    |--------------------------------------------------------------------------
    | Mengikuti frontend Next.js: "id" (utama) dan "en".
    */
    'locales' => [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
    ],

    'fallback_locale' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Disk upload media
    |--------------------------------------------------------------------------
    */
    'media_disk' => env('CMS_MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | URL frontend
    |--------------------------------------------------------------------------
    | Path media hasil seed (mis. "img/Our Partner/ico_sap.png") yang belum
    | diimpor ke storage CMS dianggap aset milik frontend (folder public/
    | Next.js). Bila FRONTEND_URL kosong, API mengembalikan path relatif
    | "/img/..." — persis seperti lib/content.js saat ini.
    */
    'frontend_url' => env('FRONTEND_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Cache respons API (detik). 0 = tanpa cache.
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => (int) env('CMS_CACHE_TTL', 3600),

];

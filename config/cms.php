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
    | Cache respons API (detik). 0 = tanpa cache (default).
    |--------------------------------------------------------------------------
    | Default 0 agar setiap perubahan di admin langsung terbaca API. Beberapa
    | aksi Filament (atur urutan, hapus massal) menulis langsung ke database
    | tanpa event model, jadi cache yang lama bisa menahan perubahan. Beban
    | ke CMS sudah dibatasi di sisi frontend.
    */
    'cache_ttl' => (int) env('CMS_CACHE_TTL', 0),

    /*
    |--------------------------------------------------------------------------
    | Instalasi (php artisan cms:install / db:seed)
    |--------------------------------------------------------------------------
    | admin       : akun admin pertama.
    | assets_source: folder public/ atau URL situs frontend tempat media
    |               About diimpor saat instalasi (kosong = lewati).
    */
    'admin' => [
        'name' => env('CMS_ADMIN_NAME', 'Admin IGLO'),
        'email' => env('CMS_ADMIN_EMAIL', 'design@indocyber.id'),
        'password' => env('CMS_ADMIN_PASSWORD'),
    ],

    'assets_source' => env('CMS_ASSETS_SOURCE'),

];

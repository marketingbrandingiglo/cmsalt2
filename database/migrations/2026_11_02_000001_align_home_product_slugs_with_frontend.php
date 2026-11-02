<?php

use App\Support\HomeContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Slug produk hasil seed pertama tidak sama dengan halaman produk di
 * frontend iglowebsitealt2 (/products/{slug}), sehingga tombol produk di
 * Beranda menuju 404. Hanya slug yang masih persis bawaan seed yang diganti —
 * slug yang sudah diubah editor dibiarkan.
 */
return new class extends Migration
{
    private const SLUGS = [
        'layanan-it-profesional' => 'it-professional-service',
        'platform-integrasi-magic-xpi' => 'magic-xpi',
        'magic-xpa-low-code-platform' => 'magic-xpa',
        'pembiayaan-keuangan' => 'leasing-finance',
    ];

    public function up(): void
    {
        foreach (self::SLUGS as $old => $new) {
            DB::table('home_products')->where('slug', $old)->update(['slug' => $new]);
        }

        HomeContent::flush();
    }

    public function down(): void
    {
        foreach (self::SLUGS as $old => $new) {
            DB::table('home_products')->where('slug', $new)->update(['slug' => $old]);
        }
    }
};

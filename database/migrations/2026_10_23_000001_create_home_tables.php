<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema konten halaman Beranda / Home (app/page.js frontend).
 *
 * Sama seperti tabel About: kolom JSON berisi teks per locale
 * {"id": "...", "en": "..."} — mengikuti struktur lib/content.js.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Singleton: teks seksi Intro, judul seksi Produk & Solusi dan Berita.
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();
            $table->json('content');
            $table->timestamps();
        });

        // HeroSlider: banner berganti otomatis di bagian paling atas.
        Schema::create('home_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->json('title');
            $table->json('description');
            $table->string('cta_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Kartu carousel "Produk & Solusi".
        Schema::create('home_products', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->json('short'); // kartu carousel mobile (ProductCarousel)
            $table->json('long');  // cadangan bila pitch kosong
            $table->json('pitch'); // kartu kaca desktop (ProductGlassCarousel)
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // "Berita & Insight" — 4 terbaru tampil sebagai mosaik di Beranda.
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();
            $table->string('tag', 20); // announcement | product | event | insight
            $table->date('published_at');
            $table->json('title');
            $table->json('excerpt');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_articles');
        Schema::dropIfExists('home_products');
        Schema::dropIfExists('home_slides');
        Schema::dropIfExists('home_pages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema konten halaman "Tentang Kami" / About Us.
 *
 * Kolom bertipe JSON yang berisi teks disimpan per locale:
 * {"id": "...", "en": "..."} — mengikuti struktur lib/content.js di frontend.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Singleton: teks halaman + media utama (banner hero, logo i5, video).
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_image')->nullable();
            $table->string('i5_logo')->nullable();
            $table->string('video_file')->nullable();
            $table->string('video_poster')->nullable();
            $table->json('content');
            $table->timestamps();
        });

        // Panel "company points": 2 counter (stat) + 2 callout (feature).
        Schema::create('about_highlights', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('stat'); // stat | feature
            $table->string('icon', 30);                  // client | developer | speed | layers
            $table->string('value', 30)->nullable();     // angka untuk type=stat, mis. "1100"
            $table->json('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Nilai perusahaan i5 (Integrity, Involved, Integrated, Impressive, Innovative).
        Schema::create('company_values', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('description');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Maskot: Zenith, Elio, Aero, Nova.
        Schema::create('mascots', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30)->unique();
            $table->string('name');
            $table->json('bio'); // {"id": ["paragraf", ...], "en": [...]}
            $table->string('scene_image')->nullable();
            $table->string('profile_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Milestone per periode, masing-masing berisi logo partner/penghargaan.
        Schema::create('milestone_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period', 50); // mis. "2021 - Present"
            $table->unsignedInteger('sort_order')->default(0); // 0 = terbaru
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('milestone_logos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_period_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('logo');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Grid "Mitra Kami" / Our Partner.
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // "Klien Kami" / Our Client — tab kategori + logo klien.
        Schema::create('client_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('logo');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('client_categories');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('milestone_logos');
        Schema::dropIfExists('milestone_periods');
        Schema::dropIfExists('mascots');
        Schema::dropIfExists('company_values');
        Schema::dropIfExists('about_highlights');
        Schema::dropIfExists('about_pages');
    }
};

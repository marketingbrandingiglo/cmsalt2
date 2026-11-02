<?php

namespace Tests\Feature;

use App\Models\HomePage;
use App\Models\HomeProduct;
use App\Models\HomeSlide;
use App\Models\NewsArticle;
use Database\Seeders\HomeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config(['cms.frontend_url' => '']);
        $this->seed(HomeSeeder::class);
    }

    public function test_it_returns_both_locales_with_the_frontend_shape(): void
    {
        $this->getJson('/api/home')
            ->assertOk()
            ->assertJsonStructure([
                'id' => [
                    'hero' => [['image', 'title', 'desc', 'ctaHref']],
                    'intro' => ['kicker', 'title', 'cta'],
                    'productsSection' => ['kicker', 'title', 'desc'],
                    'products' => [['slug', 'name', 'short', 'long', 'pitch', 'image']],
                    'news' => [
                        'hero' => ['kicker', 'title'],
                        'filters' => ['announcement', 'product', 'event', 'insight'],
                        'articles' => [['tag', 'date', 'iso', 'title', 'excerpt', 'image']],
                    ],
                ],
                'en',
            ])
            ->assertJsonCount(4, 'id.hero')
            ->assertJsonCount(6, 'en.products')
            ->assertJsonCount(28, 'id.news.articles')
            ->assertJsonPath('id.intro.title', 'Kami Menyediakan Solusi Terdepan untuk Kebutuhan Anda')
            ->assertJsonPath('en.productsSection.title', 'Our Featured Services')
            ->assertJsonPath('id.hero.0.image', '/img/Banner/banner_1.webp')
            ->assertJsonPath('en.news.filters.event', 'Event');
    }

    public function test_news_is_newest_first_with_localized_dates(): void
    {
        $this->getJson('/api/home/id')
            ->assertJsonPath('news.articles.0.iso', '2026-08-18')
            ->assertJsonPath('news.articles.0.date', '18 Agu 2026')
            ->assertJsonPath('news.articles.3.title', 'Cloud ERP vs On-Premise ERP: Mana yang Tepat untuk Implementasi SAP Pertama Anda?');

        $this->getJson('/api/home/en')->assertJsonPath('news.articles.0.date', 'Aug 18, 2026');

        NewsArticle::create([
            'tag' => 'event',
            'published_at' => '2026-10-01',
            'title' => ['id' => 'Acara baru', 'en' => ''],
            'excerpt' => ['id' => 'Ringkasan', 'en' => 'Summary'],
        ]);

        $this->getJson('/api/home/en')
            ->assertJsonPath('news.articles.0.title', 'Acara baru')
            ->assertJsonPath('news.articles.0.excerpt', 'Summary');
    }

    public function test_single_locale_endpoint_and_unknown_locale(): void
    {
        $this->getJson('/api/home/en')->assertOk()->assertJsonPath('intro.cta', 'Our Journey');
        $this->getJson('/api/home/fr')->assertNotFound();
    }

    public function test_empty_english_text_falls_back_to_indonesian(): void
    {
        $page = HomePage::singleton();
        $content = $page->content;
        $content['en']['intro']['title'] = '';
        $page->update(['content' => $content]);

        $this->getJson('/api/home/en')->assertJsonPath('intro.title', $content['id']['intro']['title']);
    }

    public function test_inactive_items_are_hidden(): void
    {
        HomeSlide::ordered()->first()->update(['is_active' => false]);
        HomeProduct::ordered()->first()->update(['is_active' => false]);
        NewsArticle::latestFirst()->first()->update(['is_active' => false]);

        $this->getJson('/api/home/id')
            ->assertJsonCount(3, 'hero')
            ->assertJsonCount(5, 'products')
            ->assertJsonCount(27, 'news.articles')
            ->assertJsonPath('products.0.slug', 'magic-xpi')
            ->assertJsonPath('news.articles.0.iso', '2026-08-12');
    }

    public function test_about_endpoint_is_untouched(): void
    {
        $this->getJson('/api/about')->assertOk();
    }

    public function test_product_slugs_match_the_frontend_product_pages(): void
    {
        // Halaman produk iglowebsitealt2: /products/{slug} (t.productPages di lib/content.js).
        $this->getJson('/api/home/en')->assertJsonPath('products.*.slug', [
            'it-professional-service', 'magic-xpi', 'robotic-process-automation',
            'magic-xpa', 'leasing-finance', 'data-governance',
        ]);
    }

    public function test_old_seeded_slugs_are_migrated_but_editor_slugs_are_kept(): void
    {
        HomeProduct::query()->where('slug', 'it-professional-service')->update(['slug' => 'layanan-it-profesional']);
        HomeProduct::query()->where('slug', 'magic-xpi')->update(['slug' => 'slug-dari-editor']);

        $migration = require database_path('migrations/2026_11_02_000001_align_home_product_slugs_with_frontend.php');
        $migration->up();

        $this->assertTrue(HomeProduct::query()->where('slug', 'it-professional-service')->exists());
        $this->assertFalse(HomeProduct::query()->where('slug', 'layanan-it-profesional')->exists());
        $this->assertTrue(HomeProduct::query()->where('slug', 'slug-dari-editor')->exists());
    }
}

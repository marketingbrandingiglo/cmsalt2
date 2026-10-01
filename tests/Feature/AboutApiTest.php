<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Models\ClientCategory;
use App\Models\Mascot;
use App\Models\Partner;
use Database\Seeders\AboutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AboutApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config(['cms.frontend_url' => '']);
        $this->seed(AboutSeeder::class);
    }

    public function test_it_returns_both_locales_with_the_frontend_shape(): void
    {
        $this->getJson('/api/about')
            ->assertOk()
            ->assertJsonStructure([
                'id' => [
                    'hero' => ['kicker', 'title', 'subtitle', 'image'],
                    'company' => ['name', 'p1', 'p2'],
                    'whoWeAreKicker', 'visionTitle', 'vision', 'missionTitle', 'mission',
                    'valuesIcon', 'i5Logo', 'valuesTitle', 'valuesKicker',
                    'stats' => [['value', 'label', 'icon']],
                    'statsExtra' => [['icon', 'text']],
                    'values' => [['title', 'desc', 'icon']],
                    'mascotTitle', 'mascotZenith' => ['name', 'bio'], 'mascotElio', 'mascotAero', 'mascotNova',
                    'milestone' => ['title', 'narrative', 'items' => [['period', 'logos' => [['src', 'alt']]]]],
                    'video' => ['title', 'text', 'src'],
                    'partner' => ['title', 'logos' => [['src', 'alt']]],
                    'client' => ['title', 'tabs', 'logos'],
                ],
                'en',
            ])
            ->assertJsonPath('id.hero.title', 'Kami Memberikan Solusi Terbaik untuk Anda')
            ->assertJsonPath('en.hero.title', 'We Give You The Best Solution')
            ->assertJsonPath('en.client.tabs.1', 'Insurance')
            ->assertJsonCount(5, 'id.milestone.items')
            ->assertJsonCount(12, 'id.partner.logos')
            ->assertJsonCount(5, 'id.client.logos');
    }

    public function test_seeded_media_falls_back_to_frontend_paths_until_imported(): void
    {
        $this->getJson('/api/about/id')
            ->assertJsonPath('partner.logos.0.src', '/img/Our Partner/ico_sap.png')
            ->assertJsonPath('hero.image', '/img/Banner/banner_aboutus.png');

        Storage::disk('public')->put('img/Our Partner/ico_sap.png', 'png');
        Partner::first()->touch(); // flush cache

        $this->assertStringEndsWith(
            '/storage/img/Our%20Partner/ico_sap.png',
            $this->getJson('/api/about/id')->json('partner.logos.0.src'),
        );
    }

    public function test_single_locale_endpoint_and_unknown_locale(): void
    {
        $this->getJson('/api/about/en')->assertOk()->assertJsonPath('visionTitle', 'Our Vision');
        $this->getJson('/api/about/fr')->assertNotFound();
    }

    public function test_empty_english_text_falls_back_to_indonesian(): void
    {
        $page = AboutPage::singleton();
        $content = $page->content;
        $content['en']['vision'] = '';
        $page->update(['content' => $content]);

        Mascot::where('key', 'nova')->first()->update(['bio' => ['id' => ['Paragraf ID'], 'en' => []]]);

        $this->getJson('/api/about/en')
            ->assertJsonPath('vision', $content['id']['vision'])
            ->assertJsonPath('mascotNova.bio', ['Paragraf ID']);
    }

    public function test_inactive_items_are_hidden_and_changes_bust_the_cache(): void
    {
        $this->getJson('/api/about/id')->assertJsonCount(12, 'partner.logos');

        Partner::first()->update(['is_active' => false]);
        ClientCategory::first()->update(['is_active' => false]);

        $this->getJson('/api/about/id')
            ->assertJsonCount(11, 'partner.logos')
            ->assertJsonCount(4, 'client.tabs')
            ->assertJsonPath('client.tabs.0', 'Asuransi');
    }

    public function test_uploaded_media_is_served_from_cms_storage(): void
    {
        $path = UploadedFile::fake()->image('logo.png')->store('about/partners', 'public');
        Partner::create(['name' => 'Baru', 'logo' => $path]);

        $logos = $this->getJson('/api/about/id')->json('partner.logos');

        $this->assertSame('Baru', end($logos)['alt']);
        $this->assertStringContainsString('/storage/about/partners/', end($logos)['src']);
    }
}

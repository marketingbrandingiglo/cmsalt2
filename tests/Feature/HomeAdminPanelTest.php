<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageHomeIntro;
use App\Filament\Resources\HomeProducts\Pages\ManageHomeProducts;
use App\Filament\Resources\HomeSlides\Pages\ManageHomeSlides;
use App\Filament\Resources\NewsArticles\Pages\ManageNewsArticles;
use App\Models\AboutPage;
use App\Models\HomePage;
use App\Models\HomeSlide;
use App\Models\User;
use Database\Seeders\AboutSeeder;
use Database\Seeders\HomeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([AboutSeeder::class, HomeSeeder::class]);
        $this->actingAs(User::factory()->create());
    }

    public function test_all_home_pages_render(): void
    {
        foreach (['/admin', '/admin/home/content', '/admin/home/slides', '/admin/home/products', '/admin/home/news'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_intro_saves_without_touching_section_headings_or_about(): void
    {
        $before = HomePage::singleton()->content;
        $about = AboutPage::singleton()->content;

        Livewire::test(ManageHomeIntro::class)
            ->assertSet('data.content.id.intro.cta', 'Perjalanan Kami')
            ->set('data.content.en.intro.title', 'New intro')
            ->call('save')
            ->assertHasNoErrors();

        $before['en']['intro']['title'] = 'New intro';
        $this->assertSame($before, HomePage::singleton()->content);
        $this->assertSame($about, AboutPage::singleton()->content);
        $this->getJson('/api/home/en')->assertJsonPath('intro.title', 'New intro');
    }

    public function test_intro_title_is_required_in_primary_locale(): void
    {
        Livewire::test(ManageHomeIntro::class)
            ->set('data.content.id.intro.title', '')
            ->call('save')
            ->assertHasErrors(['data.content.id.intro.title' => 'required']);
    }

    public function test_section_headings_are_edited_above_each_table(): void
    {
        Livewire::test(ManageHomeProducts::class)
            ->assertSet('sectionData.content.en.productsSection.kicker', 'PRODUCT & SOLUTIONS')
            ->set('sectionData.content.en.productsSection.title', 'Our Solutions')
            ->call('saveSection')
            ->assertHasNoErrors()
            ->assertCountTableRecords(6);

        Livewire::test(ManageNewsArticles::class)
            ->set('sectionData.content.id.news.title', 'Kabar Terbaru')
            ->call('saveSection')
            ->assertHasNoErrors()
            ->assertCountTableRecords(28);

        $this->getJson('/api/home/en')
            ->assertJsonPath('productsSection.title', 'Our Solutions')
            ->assertJsonPath('intro.cta', 'Our Journey');
        $this->getJson('/api/home/id')->assertJsonPath('news.hero.title', 'Kabar Terbaru');
    }

    public function test_slides_can_be_created_and_reordered(): void
    {
        Livewire::test(ManageHomeSlides::class)
            ->assertCountTableRecords(4)
            ->callAction('create', data: [
                'image' => null,
                'title' => ['id' => 'Judul', 'en' => 'Title'],
            ])
            ->assertHasFormErrors(['image' => 'required']);

        $ids = HomeSlide::ordered()->pluck('id')->all();
        $first = $this->getJson('/api/home/id')->json('hero.0.title');

        Livewire::test(ManageHomeSlides::class)
            ->call('reorderTable', array_map('strval', array_reverse($ids)));

        $this->assertNotSame($first, $this->getJson('/api/home/id')->json('hero.0.title'));
    }

    public function test_news_can_be_created(): void
    {
        Livewire::test(ManageNewsArticles::class)
            ->callAction('create', data: [
                'tag' => 'announcement',
                'published_at' => '2026-10-20',
                'title' => ['id' => 'Pengumuman baru', 'en' => 'New announcement'],
                'excerpt' => ['id' => 'Isi', 'en' => 'Body'],
            ])
            ->assertHasNoFormErrors();

        $this->getJson('/api/home/en')
            ->assertJsonPath('news.articles.0.title', 'New announcement')
            ->assertJsonPath('news.articles.0.date', 'Oct 20, 2026');
    }
}

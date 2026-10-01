<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageAboutPage;
use App\Filament\Resources\AboutHighlights\Pages\ManageAboutHighlights;
use App\Filament\Resources\ClientCategories\Pages\EditClientCategory;
use App\Filament\Resources\Partners\Pages\ManagePartners;
use App\Models\AboutPage;
use App\Models\ClientCategory;
use App\Models\User;
use Database\Seeders\AboutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AboutSeeder::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_guests_are_redirected_to_login(): void
    {
        auth()->logout();

        $this->get('/admin/about/content')->assertRedirect('/admin/login');
    }

    public function test_all_about_pages_render(): void
    {
        $category = ClientCategory::first();

        foreach ([
            '/admin', '/admin/about/content', '/admin/about/highlights', '/admin/about/values',
            '/admin/about/mascots', '/admin/about/milestones', '/admin/about/milestones/1/edit',
            '/admin/about/partners', '/admin/about/clients', "/admin/about/clients/{$category->id}/edit",
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_content_page_saves_bilingual_text(): void
    {
        Livewire::test(ManageAboutPage::class)
            ->assertSet('data.content.id.hero.title', 'Kami Memberikan Solusi Terbaik untuk Anda')
            ->set('data.content.en.vision', 'New vision')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New vision', AboutPage::singleton()->content['en']['vision']);
        $this->getJson('/api/about/en')->assertJsonPath('vision', 'New vision');
    }

    public function test_primary_locale_title_is_required(): void
    {
        Livewire::test(ManageAboutPage::class)
            ->set('data.content.id.hero.title', '')
            ->call('save')
            ->assertHasErrors(['data.content.id.hero.title' => 'required']);
    }

    public function test_highlight_value_is_required_for_stats(): void
    {
        Livewire::test(ManageAboutHighlights::class)
            ->callAction('create', data: ['type' => 'stat', 'icon' => 'client', 'value' => '', 'text' => ['id' => 'Teks', 'en' => '']])
            ->assertHasFormErrors(['value' => 'required']);

        Livewire::test(ManageAboutHighlights::class)
            ->callAction('create', data: ['type' => 'feature', 'icon' => 'speed', 'text' => ['id' => 'Keunggulan baru', 'en' => 'New feature']])
            ->assertHasNoFormErrors();

        $this->getJson('/api/about/en')->assertJsonPath('statsExtra.2.text', 'New feature');
    }

    public function test_partner_and_client_tables_list_seeded_records(): void
    {
        Livewire::test(ManagePartners::class)->assertCountTableRecords(12);

        $category = ClientCategory::first();
        Livewire::test(EditClientCategory::class, ['record' => $category->getRouteKey()])
            ->assertSet('data.name.en', 'Multifinance');
    }
}

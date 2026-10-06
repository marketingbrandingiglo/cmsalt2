<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageAboutCompany;
use App\Filament\Pages\ManageAboutPage;
use App\Filament\Pages\ManageAboutVideo;
use App\Filament\Pages\ManageAboutVisionMission;
use App\Filament\Resources\AboutHighlights\Pages\ManageAboutHighlights;
use App\Filament\Resources\ClientCategories\Pages\EditClientCategory;
use App\Filament\Resources\ClientCategories\Pages\ListClientCategories;
use App\Filament\Resources\CompanyValues\Pages\ManageCompanyValues;
use App\Filament\Resources\Mascots\Pages\ManageMascots;
use App\Filament\Resources\MilestonePeriods\Pages\ListMilestonePeriods;
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
            '/admin', '/admin/about/content', '/admin/about/content/who-we-are',
            '/admin/about/content/vision-mission', '/admin/about/content/video', '/admin/about/highlights', '/admin/about/values',
            '/admin/about/mascots', '/admin/about/milestones', '/admin/about/milestones/1/edit',
            '/admin/about/partners', '/admin/about/clients', "/admin/about/clients/{$category->id}/edit",
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_content_page_saves_bilingual_text(): void
    {
        Livewire::test(ManageAboutVisionMission::class)
            ->assertSet('data.content.id.visionTitle', 'Visi Kami')
            ->set('data.content.en.vision', 'New vision')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New vision', AboutPage::singleton()->content['en']['vision']);
        $this->getJson('/api/about/en')->assertJsonPath('vision', 'New vision');
    }

    public function test_saving_one_sub_page_keeps_the_rest_of_the_content(): void
    {
        $before = AboutPage::singleton()->content;

        Livewire::test(ManageAboutCompany::class)
            ->set('data.content.id.company.p1', 'Paragraf baru')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(ManageAboutVideo::class)->call('save')->assertHasNoErrors();
        Livewire::test(ManageAboutPage::class)->call('save')->assertHasNoErrors();

        $after = AboutPage::singleton()->content;
        $this->assertSame('Paragraf baru', $after['id']['company']['p1']);

        $before['id']['company']['p1'] = 'Paragraf baru';
        $this->assertSame($before, $after);
        $this->assertSame('img/Banner/banner_aboutus.png', AboutPage::singleton()->hero_image);
    }

    public function test_section_headings_are_edited_above_each_table(): void
    {
        Livewire::test(ManageCompanyValues::class)
            ->assertSet('sectionData.content.en.valuesKicker', 'OUR VALUES i5')
            ->set('sectionData.content.en.valuesTitle', 'Core Values')
            ->call('saveSection')
            ->assertHasNoErrors()
            ->assertCountTableRecords(5);

        Livewire::test(ManageMascots::class)
            ->set('sectionData.content.id.mascotTitle', 'Maskot Kami')
            ->call('saveSection');

        Livewire::test(ListMilestonePeriods::class)
            ->set('sectionData.content.en.milestone.narrative', 'Our story')
            ->call('saveSection');

        Livewire::test(ManagePartners::class)
            ->set('sectionData.content.en.partner.title', 'Partners')
            ->call('saveSection');

        Livewire::test(ListClientCategories::class)
            ->set('sectionData.content.en.client.title', 'Clients')
            ->call('saveSection')
            ->assertCountTableRecords(5);

        $this->getJson('/api/about/en')
            ->assertJsonPath('valuesTitle', 'Core Values')
            ->assertJsonPath('milestone.narrative', 'Our story')
            ->assertJsonPath('milestone.title', 'Our Milestones')
            ->assertJsonPath('partner.title', 'Partners')
            ->assertJsonPath('client.title', 'Clients')
            ->assertJsonPath('hero.title', 'We Give You The Best Solution');
        $this->getJson('/api/about/id')->assertJsonPath('mascotTitle', 'Maskot Kami');
        $this->assertSame('logo/i5-fixed.png', AboutPage::singleton()->i5_logo);
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

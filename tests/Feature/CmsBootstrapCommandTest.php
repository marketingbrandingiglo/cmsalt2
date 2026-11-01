<?php

namespace Tests\Feature;

use App\Console\Commands\BootstrapCms;
use App\Models\AboutPage;
use App\Models\Partner;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Database\Seeders\AboutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsBootstrapCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config([
            'cms.assets_source' => null,
            'cms.admin.email' => 'design@indocyber.id',
            'cms.admin.password' => 'RahasiaKuat123',
        ]);
    }

    public function test_seeds_a_fresh_database_once_and_keeps_editor_deletions(): void
    {
        $this->artisan('cms:bootstrap')->assertSuccessful();

        $this->assertSame(1, AboutPage::count());
        $this->assertSame(12, Partner::count());
        $this->assertTrue(Cache::get(BootstrapCms::SEED_MARKER));
        $this->assertTrue(Hash::check('RahasiaKuat123', User::sole()->password));

        Partner::query()->delete();
        $this->artisan('cms:bootstrap')->assertSuccessful();

        $this->assertSame(0, Partner::count());
        $this->assertSame(1, User::count());
    }

    public function test_an_interrupted_initial_seed_is_finished_on_the_next_start(): void
    {
        // Start sebelumnya berhenti sebelum transaksi seed selesai: belum ada
        // konten dan belum ada penanda.
        $this->assertNull(Cache::get(BootstrapCms::SEED_MARKER));

        $this->artisan('cms:bootstrap')->assertSuccessful();

        $this->assertSame(1, AboutPage::count());
        $this->assertSame(12, Partner::count());
    }

    public function test_existing_content_without_the_marker_is_never_overwritten(): void
    {
        $this->seed(AboutSeeder::class);
        Partner::first()->delete();

        $this->artisan('cms:bootstrap')->assertSuccessful();

        $this->assertSame(11, Partner::count());
        $this->assertTrue(Cache::get(BootstrapCms::SEED_MARKER));
    }

    public function test_admin_is_created_once_and_a_changed_password_is_kept(): void
    {
        $this->artisan('cms:bootstrap')->assertSuccessful();
        User::sole()->update(['password' => 'DiGantiEditor456']);

        $this->artisan('cms:bootstrap')->assertSuccessful();

        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('DiGantiEditor456', User::sole()->password));
    }

    public function test_no_admin_and_no_default_password_without_admin_password(): void
    {
        config(['cms.admin.password' => null]);
        $this->app['env'] = 'production';

        $this->artisan('cms:bootstrap')->assertSuccessful();

        $this->assertSame(0, User::count());
    }

    public function test_admin_env_names_from_the_vercel_guide_are_read(): void
    {
        $this->assertSame(
            "env('ADMIN_EMAIL', env('CMS_ADMIN_EMAIL', 'design@indocyber.id'))",
            $this->configSource('cms.php', "'email' => "),
        );
        $this->assertStringContainsString("env('ADMIN_PASSWORD'", $this->configSource('cms.php', "'password' => "));
        $this->assertStringContainsString("env('MEDIA_DISK'", $this->configSource('cms.php', "'media_disk' => "));
    }

    public function test_livewire_temporary_uploads_follow_an_object_storage_media_disk(): void
    {
        config(['cms.media_disk' => 'r2', 'livewire.temporary_file_upload.disk' => null]);
        (new AppServiceProvider($this->app))->boot();
        $this->assertSame('r2', config('livewire.temporary_file_upload.disk'));

        config(['cms.media_disk' => 'public', 'livewire.temporary_file_upload.disk' => null]);
        (new AppServiceProvider($this->app))->boot();
        $this->assertNull(config('livewire.temporary_file_upload.disk'));
    }

    private function configSource(string $file, string $key): string
    {
        $line = collect(file(config_path($file)))->first(fn (string $line) => str_contains($line, $key));

        return rtrim(trim(str_replace($key, '', $line)), ',');
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Resources\Partners\Pages\ManagePartners;
use App\Models\Partner;
use App\Models\User;
use App\Support\Media;
use Database\Seeders\AboutSeeder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaDiskTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function tearDown(): void
    {
        Media::forgetListing();

        parent::tearDown();
    }

    public function test_media_disk_defaults_to_public(): void
    {
        $this->assertSame('public', config('cms.media_disk'));
        $this->assertSame('public', Media::visibility());
    }

    public function test_r2_disk_sends_no_object_acls(): void
    {
        $disk = config('filesystems.disks.r2');

        $this->assertSame('s3', $disk['driver']);
        $this->assertSame('auto', $disk['region']);
        $this->assertTrue($disk['use_path_style_endpoint']);
        $this->assertFalse($disk['retain_visibility']);
        $this->assertArrayNotHasKey('visibility', $disk);

        config(['cms.media_disk' => 'r2']);
        $this->assertTrue(Media::isRemote());
        $this->assertNull(Media::visibility());
    }

    public function test_uploads_and_api_urls_use_the_r2_media_disk(): void
    {
        config(['cms.media_disk' => 'r2', 'cms.frontend_url' => '']);
        Storage::fake('r2');
        Storage::fake('public');
        $this->seed(AboutSeeder::class);
        $this->actingAs(User::factory()->create());

        Livewire::test(ManagePartners::class)
            ->callAction('create', [
                'name' => 'Partner Baru',
                'logo' => [UploadedFile::fake()->createWithContent('logo.png', base64_decode(self::PNG))],
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $partner = Partner::where('name', 'Partner Baru')->sole();
        Storage::disk('r2')->assertExists($partner->logo);
        Storage::disk('public')->assertMissing($partner->logo);

        $partners = collect($this->getJson('/api/about/en')->assertOk()->json('partner.logos'));

        // File yang diunggah dilayani dari R2; logo hasil seed tetap dari frontend.
        $this->assertContains(Storage::disk('r2')->url($partner->logo), $partners->pluck('src'));
        $this->assertContains('/img/Our Partner/ico_sap.png', $partners->pluck('src'));
    }

    public function test_remote_disk_is_listed_once_instead_of_checked_per_file(): void
    {
        config(['cms.media_disk' => 'r2', 'cms.frontend_url' => 'https://frontend.test']);
        Storage::fake('r2');
        Storage::disk('r2')->put('about/a.png', 'x');

        $disk = Storage::disk('r2');
        $spy = new class($disk->getDriver(), $disk->getAdapter(), $disk->getConfig()) extends FilesystemAdapter
        {
            public int $exists = 0;

            public int $listings = 0;

            public function exists($path)
            {
                $this->exists++;

                return parent::exists($path);
            }

            public function allFiles($directory = null)
            {
                $this->listings++;

                return parent::allFiles($directory);
            }
        };
        Storage::set('r2', $spy);

        $this->assertStringEndsWith('/about/a.png', Media::url('about/a.png'));
        $this->assertSame('https://frontend.test/img/b.png', Media::url('img/b.png'));
        Media::url('img/c.png');

        $this->assertSame(1, $spy->listings);
        $this->assertSame(0, $spy->exists);
    }
}

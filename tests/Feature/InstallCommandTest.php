<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_seeds_once_and_keeps_edits_on_rerun(): void
    {
        config(['cms.admin.password' => 'RahasiaKuat123', 'cms.assets_source' => null]);

        $this->artisan('cms:install')->assertSuccessful();

        $this->assertSame(1, AboutPage::count());
        $this->assertSame(12, Partner::count());
        $this->assertTrue(Hash::check('RahasiaKuat123', User::sole()->password));

        Partner::first()->delete();

        $this->artisan('cms:install')->assertSuccessful();

        $this->assertSame(1, AboutPage::count());
        $this->assertSame(11, Partner::count());
        $this->assertSame(1, User::count());
    }

    public function test_production_without_password_never_uses_the_default(): void
    {
        config(['cms.admin.password' => null, 'cms.assets_source' => null]);
        $this->app['env'] = 'production';

        $this->artisan('cms:install')->assertSuccessful();

        $this->assertFalse(Hash::check('iglocms123', User::sole()->password));
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => config('cms.admin.email')],
            [
                'name' => config('cms.admin.name'),
                'password' => config('cms.admin.password') ?: 'iglocms123',
            ],
        );

        // Beranda dulu: AboutSeeder diakhiri impor media (bila CMS_ASSETS_SOURCE
        // di-set) yang ikut menyalin media Beranda.
        $this->call([HomeSeeder::class, AboutSeeder::class]);
    }
}

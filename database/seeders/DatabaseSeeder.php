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
            ['email' => env('CMS_ADMIN_EMAIL', 'design@indocyber.id')],
            [
                'name' => env('CMS_ADMIN_NAME', 'Admin IGLO'),
                'password' => env('CMS_ADMIN_PASSWORD', 'iglocms123'),
            ],
        );

        $this->call(AboutSeeder::class);
    }
}

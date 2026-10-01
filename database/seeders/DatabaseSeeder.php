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

        $this->call(AboutSeeder::class);
    }
}

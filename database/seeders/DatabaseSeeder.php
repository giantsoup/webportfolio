<?php

namespace Database\Seeders;

use App\Models\PortfolioSetting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        PortfolioSetting::query()->firstOrCreate(
            [],
            PortfolioSetting::defaults(),
        );

        $this->call([
            PortfolioSkillSeeder::class,
            PortfolioProjectSeeder::class,
        ]);

        User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Portfolio Admin',
            'email_verified_at' => now(),
            'is_admin' => true,
            'password' => Hash::make('password'),
        ]);
    }
}

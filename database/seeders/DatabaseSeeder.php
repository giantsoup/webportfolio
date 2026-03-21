<?php

namespace Database\Seeders;

use App\Models\PortfolioSetting;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    }
}

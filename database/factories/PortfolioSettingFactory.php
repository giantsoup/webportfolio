<?php

namespace Database\Factories;

use App\Models\PortfolioSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioSetting>
 */
class PortfolioSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return PortfolioSetting::defaults();
    }
}

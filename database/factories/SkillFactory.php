<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = sprintf(
            '%s %s',
            fake()->randomElement(['Laravel', 'Livewire', 'Tailwind', 'MySQL', 'PostgreSQL', 'Redis', 'AWS', 'Alpine']),
            fake()->unique()->word(),
        );

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(['Backend', 'Frontend', 'Data', 'Infrastructure']),
            'icon' => fake()->randomElement(['code-bracket', 'server-stack', 'circle-stack', 'cloud']),
            'accent_color' => fake()->randomElement(['#df8eff', '#00eefc', '#ff6e81']),
            'sort_order' => fake()->numberBetween(0, 20),
            'is_featured' => fake()->boolean(50),
        ];
    }
}

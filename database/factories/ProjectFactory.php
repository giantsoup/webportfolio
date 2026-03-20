<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(14),
            'body' => implode("\n\n", fake()->paragraphs(4)),
            'featured_image_path' => null,
            'category' => fake()->randomElement(['Custom Laravel', 'Legacy Modernization', 'APIs', 'Internal Tools']),
            'repo_url' => fake()->boolean(70) ? fake()->url() : null,
            'live_url' => fake()->boolean(60) ? fake()->url() : null,
            'case_study_url' => fake()->boolean(40) ? fake()->url() : null,
            'is_featured' => false,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 20),
            'published_at' => now()->subDays(fake()->numberBetween(1, 120)),
        ];
    }

    /**
     * Indicate that the project is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the project is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}

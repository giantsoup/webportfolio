<?php

use App\Models\Skill;
use Database\Seeders\PortfolioSkillSeeder;

test('portfolio skill seeder creates curated examples without duplicates', function () {
    $this->seed(PortfolioSkillSeeder::class);
    $this->seed(PortfolioSkillSeeder::class);

    expect(Skill::query()->count())->toBe(10);
    expect(Skill::query()->where('is_featured', true)->count())->toBe(6);
    expect(
        Skill::query()
            ->orderBy('sort_order')
            ->pluck('slug')
            ->all()
    )->toBe([
        'laravel',
        'livewire',
        'mysql',
        'tailwind-css',
        'queue-systems',
        'automated-testing',
        'ci-cd-pipelines',
        'pdf-generation',
        'data-normalization',
        'api-integrations',
    ]);

    $this->assertDatabaseHas('skills', [
        'slug' => 'laravel',
        'category' => 'Framework',
        'is_featured' => true,
    ]);

    $this->assertDatabaseHas('skills', [
        'slug' => 'api-integrations',
        'category' => 'Backend',
        'is_featured' => false,
    ]);
});

<?php

use App\Models\Project;
use Database\Seeders\PortfolioProjectSeeder;
use Database\Seeders\PortfolioSkillSeeder;

test('portfolio project seeder creates curated github-backed projects without duplicates', function () {
    $this->seed([
        PortfolioSkillSeeder::class,
        PortfolioProjectSeeder::class,
    ]);

    $this->seed(PortfolioProjectSeeder::class);

    expect(Project::query()->count())->toBe(3);
    expect(
        Project::query()
            ->ordered()
            ->pluck('slug')
            ->all()
    )->toBe([
        'print-for-me',
        'arbor-xml-viewer',
        'dbgold',
    ]);

    $this->assertDatabaseHas('projects', [
        'slug' => 'print-for-me',
        'repo_url' => 'https://github.com/giantsoup/print-for-me',
        'is_featured' => true,
    ]);

    $this->assertDatabaseHas('projects', [
        'slug' => 'arbor-xml-viewer',
        'repo_url' => 'https://github.com/giantsoup/xmlviewer',
        'is_featured' => true,
    ]);

    $this->assertDatabaseHas('projects', [
        'slug' => 'dbgold',
        'repo_url' => 'https://github.com/giantsoup/sql-cloner',
        'is_published' => true,
    ]);

    expect(
        Project::query()->whereSlug('print-for-me')->firstOrFail()->skills->pluck('slug')->all()
    )->toBe([
        'laravel',
        'tailwind-css',
        'automated-testing',
    ]);

    $this->assertDatabaseMissing('projects', [
        'slug' => 'trench-crusade-campaign-tracker',
    ]);

    $this->assertDatabaseMissing('projects', [
        'slug' => 'gametracker',
    ]);
});

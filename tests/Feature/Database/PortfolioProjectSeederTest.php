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

    expect(Project::query()->count())->toBe(4);
    expect(
        Project::query()
            ->ordered()
            ->pluck('slug')
            ->all()
    )->toBe([
        'arbor-xml-viewer',
        'dbgold',
        'trench-crusade-campaign-tracker',
        'gametracker',
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
        Project::query()->whereSlug('gametracker')->firstOrFail()->skills->pluck('slug')->all()
    )->toBe([
        'laravel-application-architecture',
        'testing-regression-coverage-and-release-hardening',
    ]);
});

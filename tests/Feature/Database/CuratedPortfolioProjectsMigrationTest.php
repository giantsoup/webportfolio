<?php

use App\Models\Project;
use Database\Seeders\PortfolioSkillSeeder;

test('curated portfolio migration keeps only the requested github projects', function () {
    $this->seed(PortfolioSkillSeeder::class);

    Project::factory()->create([
        'title' => 'Legacy Seeded Project',
        'slug' => 'trench-crusade-campaign-tracker',
    ]);

    Project::factory()->create([
        'title' => 'Older Seeded Project',
        'slug' => 'gametracker',
    ]);

    $migration = require database_path('migrations/2026_03_23_193305_publish_curated_portfolio_projects.php');

    $migration->up();

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

    $this->assertDatabaseMissing('projects', [
        'slug' => 'trench-crusade-campaign-tracker',
    ]);

    $this->assertDatabaseMissing('projects', [
        'slug' => 'gametracker',
    ]);

    expect(
        Project::query()->whereSlug('dbgold')->firstOrFail()->skills->pluck('slug')->all()
    )->toBe([
        'mysql',
        'automated-testing',
    ]);
});

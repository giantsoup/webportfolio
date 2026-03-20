<?php

use App\Models\Skill;
use Database\Seeders\PortfolioSkillSeeder;

test('portfolio skill seeder creates curated examples without duplicates', function () {
    $this->seed(PortfolioSkillSeeder::class);
    $this->seed(PortfolioSkillSeeder::class);

    expect(Skill::query()->count())->toBe(8);
    expect(Skill::query()->where('is_featured', true)->count())->toBe(4);
    expect(
        Skill::query()
            ->orderBy('sort_order')
            ->pluck('slug')
            ->all()
    )->toBe([
        'laravel-application-architecture',
        'affordable-housing-compliance-workflows',
        'regulatory-reporting-and-exports',
        'document-management-and-notice-delivery',
        'waitlist-and-eligibility-systems',
        'legacy-data-imports-and-normalization',
        'queue-backed-processing-and-horizon-operations',
        'testing-regression-coverage-and-release-hardening',
    ]);

    $this->assertDatabaseHas('skills', [
        'slug' => 'affordable-housing-compliance-workflows',
        'category' => 'Domain Systems',
        'is_featured' => true,
    ]);

    $this->assertDatabaseHas('skills', [
        'slug' => 'testing-regression-coverage-and-release-hardening',
        'category' => 'Quality',
        'is_featured' => false,
    ]);
});

<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class PortfolioSkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Skill::query()->upsert([
            [
                'name' => 'Laravel Application Architecture',
                'slug' => 'laravel-application-architecture',
                'category' => 'Backend',
                'icon' => 'server-stack',
                'accent_color' => '#00eefc',
                'sort_order' => 10,
                'is_featured' => true,
            ],
            [
                'name' => 'Affordable Housing Compliance Workflows',
                'slug' => 'affordable-housing-compliance-workflows',
                'category' => 'Domain Systems',
                'icon' => 'clipboard-document-check',
                'accent_color' => '#ff6e81',
                'sort_order' => 20,
                'is_featured' => true,
            ],
            [
                'name' => 'Regulatory Reporting and Exports',
                'slug' => 'regulatory-reporting-and-exports',
                'category' => 'Reporting',
                'icon' => 'chart-bar-square',
                'accent_color' => '#df8eff',
                'sort_order' => 30,
                'is_featured' => true,
            ],
            [
                'name' => 'Document Management and Notice Delivery',
                'slug' => 'document-management-and-notice-delivery',
                'category' => 'Operations',
                'icon' => 'document-text',
                'accent_color' => '#00eefc',
                'sort_order' => 40,
                'is_featured' => true,
            ],
            [
                'name' => 'Waitlist and Eligibility Systems',
                'slug' => 'waitlist-and-eligibility-systems',
                'category' => 'Product Workflows',
                'icon' => 'queue-list',
                'accent_color' => '#ff6e81',
                'sort_order' => 50,
                'is_featured' => false,
            ],
            [
                'name' => 'Legacy Data Imports and Normalization',
                'slug' => 'legacy-data-imports-and-normalization',
                'category' => 'Data',
                'icon' => 'circle-stack',
                'accent_color' => '#df8eff',
                'sort_order' => 60,
                'is_featured' => false,
            ],
            [
                'name' => 'Queue-Backed Processing and Horizon Operations',
                'slug' => 'queue-backed-processing-and-horizon-operations',
                'category' => 'Infrastructure',
                'icon' => 'bolt',
                'accent_color' => '#00eefc',
                'sort_order' => 70,
                'is_featured' => false,
            ],
            [
                'name' => 'Testing, Regression Coverage, and Release Hardening',
                'slug' => 'testing-regression-coverage-and-release-hardening',
                'category' => 'Quality',
                'icon' => 'shield-check',
                'accent_color' => '#ff6e81',
                'sort_order' => 80,
                'is_featured' => false,
            ],
        ], uniqueBy: ['slug'], update: [
            'name',
            'category',
            'icon',
            'accent_color',
            'sort_order',
            'is_featured',
            'updated_at',
        ]);
    }
}

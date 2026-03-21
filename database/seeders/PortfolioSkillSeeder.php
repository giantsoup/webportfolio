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
        $slugs = [
            'laravel', 'livewire', 'mysql', 'tailwind-css', 'queue-systems',
            'automated-testing', 'ci-cd-pipelines', 'pdf-generation',
            'data-normalization', 'api-integrations',
        ];

        Skill::query()->whereNotIn('slug', $slugs)->delete();

        Skill::query()->upsert([
            [
                'name' => 'Laravel',
                'slug' => 'laravel',
                'category' => 'Framework',
                'icon' => 'server-stack',
                'accent_color' => '#00eefc',
                'sort_order' => 10,
                'is_featured' => true,
            ],
            [
                'name' => 'Livewire',
                'slug' => 'livewire',
                'category' => 'Frontend',
                'icon' => 'bolt',
                'accent_color' => '#ff6e81',
                'sort_order' => 20,
                'is_featured' => true,
            ],
            [
                'name' => 'MySQL',
                'slug' => 'mysql',
                'category' => 'Database',
                'icon' => 'circle-stack',
                'accent_color' => '#df8eff',
                'sort_order' => 30,
                'is_featured' => true,
            ],
            [
                'name' => 'Tailwind CSS',
                'slug' => 'tailwind-css',
                'category' => 'Frontend',
                'icon' => 'code-bracket',
                'accent_color' => '#00eefc',
                'sort_order' => 40,
                'is_featured' => true,
            ],
            [
                'name' => 'Queue Systems',
                'slug' => 'queue-systems',
                'category' => 'Infrastructure',
                'icon' => 'queue-list',
                'accent_color' => '#ff6e81',
                'sort_order' => 50,
                'is_featured' => true,
            ],
            [
                'name' => 'Automated Testing',
                'slug' => 'automated-testing',
                'category' => 'Quality',
                'icon' => 'shield-check',
                'accent_color' => '#df8eff',
                'sort_order' => 60,
                'is_featured' => true,
            ],
            [
                'name' => 'CI/CD Pipelines',
                'slug' => 'ci-cd-pipelines',
                'category' => 'DevOps',
                'icon' => 'arrow-path',
                'accent_color' => '#00eefc',
                'sort_order' => 70,
                'is_featured' => false,
            ],
            [
                'name' => 'PDF Generation',
                'slug' => 'pdf-generation',
                'category' => 'Documents',
                'icon' => 'document-text',
                'accent_color' => '#ff6e81',
                'sort_order' => 80,
                'is_featured' => false,
            ],
            [
                'name' => 'Data Normalization',
                'slug' => 'data-normalization',
                'category' => 'Data',
                'icon' => 'circle-stack',
                'accent_color' => '#df8eff',
                'sort_order' => 90,
                'is_featured' => false,
            ],
            [
                'name' => 'API Integrations',
                'slug' => 'api-integrations',
                'category' => 'Backend',
                'icon' => 'cloud',
                'accent_color' => '#00eefc',
                'sort_order' => 100,
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

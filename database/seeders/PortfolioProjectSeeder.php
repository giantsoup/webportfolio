<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PortfolioProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Arbor XML Viewer',
                'slug' => 'arbor-xml-viewer',
                'summary' => 'Local-only XML inspection tool with a collapsible tree, search, structure stats, keyboard shortcuts, and a polished browser-first workflow.',
                'body' => implode("\n\n", [
                    'Arbor XML Viewer is a local-only web app for manually inspecting XML documents in the browser. It supports pasted XML, drag-and-drop imports, local file selection, searchable tree inspection, structure statistics, and keyboard-driven navigation.',
                    'The repository is built with Preact, TypeScript, Vite, Tailwind CSS, Playwright, and Vitest. It is a focused frontend utility project with a strong emphasis on local-first workflows and developer ergonomics.',
                    'This entry is based on the public GitHub repository and README for giantsoup/xmlviewer.',
                ]),
                'category' => 'TypeScript Tooling',
                'repo_url' => 'https://github.com/giantsoup/xmlviewer',
                'live_url' => null,
                'case_study_url' => null,
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 10,
                'published_at' => Carbon::parse('2026-03-16'),
            ],
            [
                'title' => 'dbgold',
                'slug' => 'dbgold',
                'summary' => 'Go-based TUI for creating, managing, and restoring local MySQL snapshots with safer workflows than a basic dump-and-replay process.',
                'body' => implode("\n\n", [
                    'dbgold is a Go terminal UI for creating and restoring local MySQL golden snapshots with MySQL Shell. It is positioned as a faster and friendlier alternative to traditional mysqldump and SQL replay workflows for larger development databases.',
                    'The repo includes first-run onboarding, persistent settings, snapshot and restore safety checks, CLI subcommands, and live job log streaming. It targets day-to-day local development workflows where database reset speed and reliability matter.',
                    'This entry is based on the public GitHub repository and README for giantsoup/sql-cloner.',
                ]),
                'category' => 'Developer Tooling',
                'repo_url' => 'https://github.com/giantsoup/sql-cloner',
                'live_url' => null,
                'case_study_url' => null,
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 20,
                'published_at' => Carbon::parse('2026-03-13'),
            ],
            [
                'title' => 'Trench Crusade Campaign Tracker',
                'slug' => 'trench-crusade-campaign-tracker',
                'summary' => 'Campaign management app for Trench Crusade warbands, units, equipment, progression, and post-battle workflows.',
                'body' => implode("\n\n", [
                    'This project is a comprehensive web application for managing Trench Crusade warbands and campaign progression. The public README describes warband management, unit tracking, equipment records, campaign progression, and guided post-battle sequences.',
                    'The stack called out in the repository is Laravel 12, Vue 3, Inertia.js, TypeScript, Tailwind CSS 4, SQLite, and Pest. It reads like a larger full-stack application with both gameplay workflow depth and modern tooling choices.',
                    'This entry is based on the public GitHub repository and README for giantsoup/trenchtracker.',
                ]),
                'category' => 'Full-Stack Laravel',
                'repo_url' => 'https://github.com/giantsoup/trenchtracker',
                'live_url' => null,
                'case_study_url' => null,
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 30,
                'published_at' => Carbon::parse('2025-07-26'),
            ],
            [
                'title' => 'GameTracker',
                'slug' => 'gametracker',
                'summary' => 'Laravel and Livewire app for tracking board game events, players, participation, played games, and organizer workflows.',
                'body' => implode("\n\n", [
                    'GameTracker is a Laravel application for tracking board game events, players, and games played during those events. The public README highlights event management, player participation, game duration tracking, role-based permissions, admin tooling, and responsive Livewire and Flux-based UI work.',
                    'The repository also calls out a comprehensive Pest test suite, which makes it a good representation of a larger app with both product workflow scope and verification discipline.',
                    'This entry is based on the public GitHub repository and README for giantsoup/gametracker.',
                ]),
                'category' => 'Laravel + Livewire',
                'repo_url' => 'https://github.com/giantsoup/gametracker',
                'live_url' => null,
                'case_study_url' => null,
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 40,
                'published_at' => Carbon::parse('2025-07-12'),
            ],
        ];

        Project::query()->upsert($projects, uniqueBy: ['slug'], update: [
            'title',
            'summary',
            'body',
            'category',
            'repo_url',
            'live_url',
            'case_study_url',
            'is_featured',
            'is_published',
            'sort_order',
            'published_at',
            'updated_at',
        ]);

        $skillIdsBySlug = Skill::query()
            ->whereIn('slug', [
                'laravel',
                'automated-testing',
            ])
            ->pluck('id', 'slug');

        $projectSkillMap = [
            'arbor-xml-viewer' => [
                'automated-testing',
            ],
            'trench-crusade-campaign-tracker' => [
                'laravel',
                'automated-testing',
            ],
            'gametracker' => [
                'laravel',
                'automated-testing',
            ],
        ];

        Project::query()
            ->whereIn('slug', array_keys($projectSkillMap))
            ->get()
            ->each(function (Project $project) use ($projectSkillMap, $skillIdsBySlug): void {
                $skillIds = collect($projectSkillMap[$project->slug] ?? [])
                    ->map(fn (string $skillSlug): ?int => $skillIdsBySlug[$skillSlug] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                $project->skills()->sync($skillIds);
            });
    }
}

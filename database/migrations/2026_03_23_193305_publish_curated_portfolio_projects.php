<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $obsoleteProjectSlugs = [
            'trench-crusade-campaign-tracker',
            'gametracker',
        ];

        $timestamp = now();

        DB::table('projects')->whereIn('slug', $obsoleteProjectSlugs)->delete();

        DB::table('projects')->upsert([
            [
                'title' => 'Print for Me',
                'slug' => 'print-for-me',
                'summary' => 'Private 3D print request platform with magic-link access, secure uploads, mobile-first intake, and an admin board for managing request status end to end.',
                'body' => implode("\n\n", [
                    'Print for Me is a private workflow for managing 3D print requests from intake through fulfillment. The product is built around invite-only access, passwordless magic-link authentication, and private file handling so requesters can submit work without exposing files or project details publicly.',
                    'The application includes a mobile-first request form, source-link support, admin-side board management, inline status updates, and lifecycle tracking across pending, accepted, printing, and complete states. Queued notifications, session controls, and cleanup commands round out the operational side of the product.',
                    'Technically, the repository demonstrates current Laravel application design with Laravel 13, Inertia v2, Vue 3, Tailwind CSS v4, Vite, SQLite for local workflows, and Pest-backed verification.',
                ]),
                'category' => 'Laravel + Vue',
                'repo_url' => 'https://github.com/giantsoup/print-for-me',
                'live_url' => null,
                'case_study_url' => null,
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 10,
                'published_at' => CarbonImmutable::parse('2026-03-23'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'title' => 'Arbor XML Viewer',
                'slug' => 'arbor-xml-viewer',
                'summary' => 'Local-only XML inspection app with drag-and-drop import, searchable tree navigation, structure stats, keyboard shortcuts, and a polished browser-first workflow.',
                'body' => implode("\n\n", [
                    'Arbor XML Viewer is a browser-based XML inspection tool designed for local-only use. It supports pasted XML, drag-and-drop imports, local file selection, and a collapsible tree view for exploring elements, attributes, text nodes, comments, and CDATA without routing content through a backend.',
                    'The product experience emphasizes clarity and speed: search can jump through tags, attributes, and text content, a floating dock surfaces structural statistics, and keyboard shortcuts make the interface feel closer to a desktop utility than a throwaway developer aid.',
                    'The repository is built with Preact, TypeScript, Vite, Tailwind CSS, Playwright, and Vitest, making it a strong example of thoughtful frontend tooling and local-first product design.',
                ]),
                'category' => 'TypeScript Tooling',
                'repo_url' => 'https://github.com/giantsoup/xmlviewer',
                'live_url' => null,
                'case_study_url' => null,
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 20,
                'published_at' => CarbonImmutable::parse('2026-03-22'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'title' => 'dbgold',
                'slug' => 'dbgold',
                'summary' => 'Go-based TUI for creating and restoring local MySQL snapshots with guided setup, safer validation, live logs, and faster workflows than dump-and-replay.',
                'body' => implode("\n\n", [
                    'dbgold is a keyboard-driven Go TUI for creating and restoring local MySQL golden snapshots with MySQL Shell. It targets teams that need repeatable local database resets but want a safer and more usable alternative to ad hoc dump-and-replay scripts.',
                    'The tool includes first-run onboarding, persistent configuration, snapshot validation before destructive restore steps, automatic local_infile handling, direct CLI subcommands, and live job-log streaming. Those decisions make it practical both for interactive use and for scripted workflows.',
                    'The repository demonstrates infrastructure-focused product thinking: it is opinionated about safety, tuned for local developer ergonomics, and intentionally designed to reduce friction around large-database workflows.',
                ]),
                'category' => 'Go Developer Tooling',
                'repo_url' => 'https://github.com/giantsoup/sql-cloner',
                'live_url' => null,
                'case_study_url' => null,
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 30,
                'published_at' => CarbonImmutable::parse('2026-03-21'),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], uniqueBy: ['slug'], update: [
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

        $projectSkillMap = [
            'print-for-me' => [
                'laravel',
                'tailwind-css',
                'automated-testing',
            ],
            'arbor-xml-viewer' => [
                'tailwind-css',
                'automated-testing',
            ],
            'dbgold' => [
                'mysql',
                'automated-testing',
            ],
        ];

        $projectIdsBySlug = DB::table('projects')
            ->whereIn('slug', array_keys($projectSkillMap))
            ->pluck('id', 'slug');

        if ($projectIdsBySlug->isEmpty()) {
            return;
        }

        $skillIdsBySlug = DB::table('skills')
            ->whereIn('slug', [
                'laravel',
                'mysql',
                'tailwind-css',
                'automated-testing',
            ])
            ->pluck('id', 'slug');

        DB::table('project_skill')
            ->whereIn('project_id', $projectIdsBySlug->values()->all())
            ->delete();

        $projectSkills = [];

        foreach ($projectSkillMap as $projectSlug => $skillSlugs) {
            $projectId = $projectIdsBySlug[$projectSlug] ?? null;

            if (! $projectId) {
                continue;
            }

            foreach ($skillSlugs as $skillSlug) {
                $skillId = $skillIdsBySlug[$skillSlug] ?? null;

                if (! $skillId) {
                    continue;
                }

                $projectSkills[] = [
                    'project_id' => $projectId,
                    'skill_id' => $skillId,
                ];
            }
        }

        if ($projectSkills !== []) {
            DB::table('project_skill')->insert($projectSkills);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $currentProjectSlugs = [
            'print-for-me',
            'arbor-xml-viewer',
            'dbgold',
        ];

        $projectIds = DB::table('projects')
            ->whereIn('slug', $currentProjectSlugs)
            ->pluck('id')
            ->all();

        if ($projectIds !== []) {
            DB::table('project_skill')->whereIn('project_id', $projectIds)->delete();
        }

        DB::table('projects')->whereIn('slug', $currentProjectSlugs)->delete();
    }
};

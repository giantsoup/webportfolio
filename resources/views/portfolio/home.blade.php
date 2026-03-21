<x-layouts::public :title="__('Home')" :$settings>
    @php
        $compliancePrograms = [
            'CDLAC',
            'CSCDA',
            'HOPWA',
            'HOME',
            'TIRC',
            'Bond workflows',
            'Agency reporting',
        ];

        $platformBuildHighlights = [
            [
                'title' => 'Compliance workflows',
                'summary' => 'Built multi-step workflows for report periods, certifications, status transitions, and internal review cycles.',
            ],
            [
                'title' => 'Program-specific logic',
                'summary' => 'Translated regulatory rules into working forms, calculations, and reporting outputs across multiple housing programs.',
            ],
            [
                'title' => 'Reporting and documents',
                'summary' => 'Created export pipelines, PDF generation, email blasts, postal workflows, and document management tooling.',
            ],
            [
                'title' => 'Data operations',
                'summary' => 'Managed waitlists, imports, and data normalization across projects, contacts, units, and rent and income limits.',
            ],
        ];

        $platformImprovementHighlights = [
            [
                'title' => 'Architecture cleanup',
                'summary' => 'Refactored services and controllers to reduce complexity and support long-term growth.',
            ],
            [
                'title' => 'Automated testing',
                'summary' => 'Expanded test coverage with unit, feature, integration, and regression suites for critical paths.',
            ],
            [
                'title' => 'Validation and resilience',
                'summary' => 'Hardened validation, permissions, and error handling across high-risk business processes.',
            ],
            [
                'title' => 'Operations and performance',
                'summary' => 'Optimized queue processing, CI/CD pipelines, and performance for large datasets and document generation.',
            ],
        ];

    @endphp

    <section class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-7xl items-center gap-14 px-6 py-20 lg:grid-cols-[1.3fr_0.9fr] lg:px-8">
        <div class="space-y-8">
            <x-public.status-pulse :text="$settings->hero_kicker" />

            <div class="space-y-6">
                <h1 class="font-headline text-5xl font-bold leading-none tracking-tight text-portfolio-copy md:text-7xl">
                    {{ $settings->hero_title }}
                    <span class="portfolio-text-gradient block">{{ $settings->hero_emphasis }}</span>
                    Digital Systems.
                </h1>

                <p class="max-w-2xl text-lg leading-8 text-portfolio-copy-muted">
                    {{ $settings->hero_summary }}
                </p>
            </div>

            <div class="flex flex-wrap gap-4">
                <a href="{{ route('projects.index') }}" class="portfolio-button-primary">View Work</a>
                <a href="{{ route('contact.create') }}" class="portfolio-button-secondary">Contact Me</a>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="portfolio-panel md:col-span-2">
                <div class="rounded-[calc(1.5rem-1px)] bg-portfolio-surface-low p-6">
                    <p class="font-label text-[10px] uppercase tracking-[0.22em] text-portfolio-copy-muted">Current Focus</p>
                    <p class="mt-4 font-headline text-3xl font-bold text-portfolio-copy">{{ $settings->availability_text }}</p>
                    @if ($featuredSkills->take(3)->isNotEmpty())
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach ($featuredSkills->take(3) as $skill)
                                <span class="rounded-full bg-black/30 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: {{ $skill->accent_color }};">
                                    {{ $skill->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <x-public.stat-card label="Experience" :value="$settings->years_experience" suffix="yrs" />
            <x-public.stat-card label="Projects" :value="$settings->projects_completed" suffix="+" accent="secondary" />

            <div class="portfolio-panel md:col-span-2">
                <div class="rounded-[calc(1.5rem-1px)] bg-black/50 p-6 font-mono text-sm leading-7 text-portfolio-copy-muted">
                    <div class="mb-4 flex gap-2">
                        <span class="size-3 rounded-full bg-portfolio-tertiary/40"></span>
                        <span class="size-3 rounded-full bg-portfolio-secondary/40"></span>
                        <span class="size-3 rounded-full bg-portfolio-primary/40"></span>
                    </div>
                    <p><span class="text-portfolio-secondary">public function</span> <span class="text-portfolio-copy">deliverOutcome</span>()</p>
                    <p class="pl-4">return <span class="text-portfolio-tertiary">Laravel</span>::ship([</p>
                    <p class="pl-8">'performance' => <span class="text-portfolio-secondary">true</span>,</p>
                    <p class="pl-8">'clarity' => <span class="text-portfolio-secondary">true</span>,</p>
                    <p class="pl-4">]);</p>
                </div>
            </div>
        </div>
    </section>

    <x-public.section title="Housing Compliance Platform" eyebrow="Current Work" intro="Since 2020, I have served as the sole in-house developer responsible for the continued growth, maintenance, and modernization of a Laravel-based affordable housing compliance platform used for reporting, occupancy workflows, documents, waitlists, and stakeholder communication." class="py-20">
        <div class="portfolio-panel p-8">
            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">The Project</p>
                    <h3 class="mt-4 font-headline text-3xl font-bold text-portfolio-copy md:text-4xl">Housing Compliance Platform</h3>
                    <p class="mt-3 text-sm uppercase tracking-[0.18em] text-portfolio-copy-muted">Sole In-House Developer, 2020 &ndash; Present</p>
                    <p class="mt-6 max-w-3xl text-base leading-8 text-portfolio-copy-muted">
                        I own the long-term technical direction and daily engineering execution for a business-critical Laravel platform, serving as the lead engineer, maintainer, and primary technical decision-maker.
                    </p>
                </div>

                <div>
                    <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-primary">Program Coverage</p>
                    <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                        The platform supports evolving regulatory and operational requirements across multiple affordable housing programs while staying reliable for internal teams.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach ($compliancePrograms as $program)
                            <span class="rounded-full bg-portfolio-surface-low px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-portfolio-copy-muted">
                                {{ $program }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-tertiary">The Challenge</p>
                <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                    More than feature delivery. The system must reflect changing business rules, support multiple housing programs, absorb legacy data, and remain usable as complexity grows.
                </p>
            </div>

            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">My Role</p>
                <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                    Full-stack ownership across backend architecture, workflow design, reporting, document handling, queue processing, testing, deployments, and production maintenance.
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <div class="portfolio-panel p-8">
                <div class="flex flex-wrap items-start justify-between gap-4 pb-8">
                    <div class="max-w-2xl">
                        <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-primary">What I Built</p>
                        <h3 class="mt-3 font-headline text-2xl font-bold text-portfolio-copy sm:text-3xl">Core systems I delivered</h3>
                        <p class="mt-3 text-sm leading-7 text-portfolio-copy-muted">
                            Key areas of ownership spanning the full platform lifecycle.
                        </p>
                    </div>
                    <span class="rounded-full bg-portfolio-primary/10 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-portfolio-primary">
                        4 delivery areas
                    </span>
                </div>
                <ul class="grid gap-1 text-sm leading-7 text-portfolio-copy-muted sm:grid-cols-2">
                    @foreach ($platformBuildHighlights as $highlight)
                        <li class="rounded-2xl bg-portfolio-surface-low p-5 transition duration-300 hover:-translate-y-0.5 hover:bg-portfolio-surface-high">
                            <p class="font-label text-[10px] font-semibold uppercase tracking-[0.2em] text-portfolio-primary">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                            <h4 class="mt-3 font-headline text-lg font-semibold leading-6 text-portfolio-copy">{{ $highlight['title'] }}</h4>
                            <p class="mt-2 text-sm leading-7 text-portfolio-copy-muted">{{ $highlight['summary'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="portfolio-panel p-8">
                <div class="flex flex-wrap items-start justify-between gap-4 pb-8">
                    <div class="max-w-xl">
                        <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">How I Improved It</p>
                        <h3 class="mt-3 font-headline text-2xl font-bold text-portfolio-copy">Modernization efforts</h3>
                        <p class="mt-3 text-sm leading-7 text-portfolio-copy-muted">
                            Ongoing work to make the platform safer to operate, easier to extend, and less fragile over time.
                        </p>
                    </div>
                    <span class="rounded-full bg-portfolio-secondary/10 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-portfolio-secondary">
                        4 upgrade themes
                    </span>
                </div>
                <ul class="grid gap-1 text-sm leading-7 text-portfolio-copy-muted sm:grid-cols-2">
                    @foreach ($platformImprovementHighlights as $highlight)
                        <li class="rounded-2xl bg-portfolio-surface-low p-5 transition duration-300 hover:-translate-y-0.5 hover:bg-portfolio-surface-high">
                            <p class="font-label text-[10px] font-semibold uppercase tracking-[0.2em] text-portfolio-secondary">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                            <h4 class="mt-3 font-headline text-lg font-semibold leading-6 text-portfolio-copy">{{ $highlight['title'] }}</h4>
                            <p class="mt-2 text-sm leading-7 text-portfolio-copy-muted">{{ $highlight['summary'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-tertiary">Outcome</p>
                <h3 class="mt-3 font-headline text-2xl font-bold text-portfolio-copy">Sustained impact over time</h3>
                <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                    Through continuous iteration, the platform has become more capable, stable, and maintainable year over year, supporting deeper program requirements and more reliable daily operations.
                </p>
                <ul class="mt-6 grid gap-1 sm:grid-cols-2">
                    <li class="rounded-2xl bg-portfolio-surface-low p-5 transition duration-300 hover:-translate-y-0.5 hover:bg-portfolio-surface-high">
                        <p class="font-headline text-2xl font-bold text-portfolio-tertiary">5+</p>
                        <p class="mt-1 text-sm leading-7 text-portfolio-copy-muted">Years of continuous development on a single production codebase</p>
                    </li>
                    <li class="rounded-2xl bg-portfolio-surface-low p-5 transition duration-300 hover:-translate-y-0.5 hover:bg-portfolio-surface-high">
                        <p class="font-headline text-2xl font-bold text-portfolio-tertiary">Solo</p>
                        <p class="mt-1 text-sm leading-7 text-portfolio-copy-muted">Primary engineer responsible for architecture, delivery, and maintenance</p>
                    </li>
                    <li class="rounded-2xl bg-portfolio-surface-low p-5 transition duration-300 hover:-translate-y-0.5 hover:bg-portfolio-surface-high">
                        <p class="font-headline text-2xl font-bold text-portfolio-tertiary">Stable</p>
                        <p class="mt-1 text-sm leading-7 text-portfolio-copy-muted">Reduced production incidents through testing, validation, and hardened deployments</p>
                    </li>
                    <li class="rounded-2xl bg-portfolio-surface-low p-5 transition duration-300 hover:-translate-y-0.5 hover:bg-portfolio-surface-high">
                        <p class="font-headline text-2xl font-bold text-portfolio-tertiary">Modern</p>
                        <p class="mt-1 text-sm leading-7 text-portfolio-copy-muted">Migrated legacy patterns to current Laravel conventions, queues, and CI/CD</p>
                    </li>
                </ul>
            </div>

            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">Delivery Stack</p>
                <h3 class="mt-3 font-headline text-2xl font-bold text-portfolio-copy">Tools behind the work</h3>
                @if ($featuredSkills->isNotEmpty())
                    <div class="mt-6 grid gap-1 sm:grid-cols-2">
                        @foreach ($featuredSkills as $skill)
                            <x-public.skill-tile :skill="$skill" />
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                        Add featured skills from the admin to showcase the tools behind this work.
                    </p>
                @endif
            </div>
        </div>
    </x-public.section>

    <x-public.section title="Projects" eyebrow="Portfolio" intro="Builds, migrations, and technical work that demonstrate the depth and range of my engineering experience." class="py-20">
        <x-slot:actions>
            <a href="{{ route('projects.index') }}" class="portfolio-button-secondary">Browse All</a>
        </x-slot:actions>

        @if ($featuredProjects->isNotEmpty())
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($featuredProjects as $project)
                    <x-public.project-card :project="$project" />
                @endforeach
            </div>
        @else
            <div class="portfolio-panel p-8 text-portfolio-copy-muted">
                Publish a few projects from the admin to feature them here.
            </div>
        @endif
    </x-public.section>
</x-layouts::public>

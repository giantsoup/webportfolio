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
            'Designed and refined compliance workflows across report periods, unit compliance, certifications, requirement reviews, status transitions, and internal review.',
            'Implemented program-specific business logic that translated regulatory requirements into forms, calculations, reporting outputs, and operational software behavior.',
            'Built reporting, document, and communications tooling spanning exports, PDFs, attachments, upload and recovery flows, eblasts, postal workflows, and notice planning.',
            'Owned waitlist systems, data imports, and normalization for projects, contacts, units, HIMS records, and rent and income limits while resolving legacy inconsistencies.',
        ];

        $platformImprovementHighlights = [
            'Refactored services and controllers to improve maintainability and support long-term growth.',
            'Expanded automated coverage with unit, feature, integration, and regression tests around critical workflows.',
            'Strengthened validation, permissions, error handling, and resilience across high-risk business processes.',
            'Improved queue-backed processing, deployment workflows, CI and CD behavior, and performance in large-data and document-heavy areas.',
        ];

        $platformCallouts = [
            'Sole in-house developer for a multi-year Laravel housing compliance platform',
            'Owned compliance workflows, reporting, documents, waitlists, and communications',
            'Supported complex affordable housing program logic across multiple agencies',
            'Led modernization through refactoring, testing, queueing, deployment, and operational improvements',
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
                    <p class="font-label text-[10px] uppercase tracking-[0.22em] text-portfolio-copy-muted">Availability</p>
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

    <x-public.section title="Housing Compliance Platform" eyebrow="Featured Engagement" intro="For the past five years, I have been the sole in-house developer responsible for the continued growth, maintenance, and modernization of a Laravel-based affordable housing compliance platform used for reporting, occupancy workflows, documents, waitlists, and stakeholder communication." class="py-20">
        <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">Project Title</p>
                <h3 class="mt-4 font-headline text-3xl font-bold text-portfolio-copy md:text-4xl">Housing Compliance Platform</h3>
                <p class="mt-3 text-sm uppercase tracking-[0.18em] text-portfolio-copy-muted">Sole In-House Developer, 2020 to Present</p>
                <p class="mt-6 max-w-3xl text-base leading-8 text-portfolio-copy-muted">
                    I owned the long-term technical direction and day-to-day engineering execution for a business-critical internal system with only minimal contractor support, functioning as the lead engineer, maintainer, and primary technical decision-maker.
                </p>
            </div>

            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-primary">Program Coverage</p>
                <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                    The platform had to support evolving operational and regulatory requirements while remaining reliable for internal staff and compatible with legacy data and older workflows.
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($compliancePrograms as $program)
                        <span class="rounded-full border border-portfolio-outline/20 bg-black/30 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-portfolio-copy-muted">
                            {{ $program }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-tertiary">The Challenge</p>
                <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                    This was not just a feature-delivery problem. The system had to accurately reflect changing business rules, support multiple affordable housing programs, stay usable for internal teams, absorb legacy data, and continue operating as the platform grew in complexity.
                </p>
            </div>

            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">My Role</p>
                <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                    I worked across the full stack on backend architecture, workflow design, reporting, document handling, queue-based processing, testing, deployment support, and ongoing production maintenance over a sustained multi-year lifecycle.
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-primary">What I Built</p>
                <ul class="mt-6 space-y-4 text-sm leading-7 text-portfolio-copy-muted">
                    @foreach ($platformBuildHighlights as $highlight)
                        <li class="rounded-2xl border border-portfolio-outline/10 bg-black/20 px-4 py-4">
                            {{ $highlight }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="space-y-6">
                <div class="portfolio-panel p-8">
                    <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">How I Improved the System</p>
                    <ul class="mt-6 space-y-4 text-sm leading-7 text-portfolio-copy-muted">
                        @foreach ($platformImprovementHighlights as $highlight)
                            <li class="rounded-2xl border border-portfolio-outline/10 bg-black/20 px-4 py-4">
                                {{ $highlight }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="portfolio-panel p-8">
                    <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-tertiary">Outcome</p>
                    <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">
                        Over five years, I helped turn the platform into a more capable, stable, and maintainable system that supported deeper housing-program requirements and more reliable daily operations for staff who depend on it.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-primary">Short Callout Version</p>
                <ul class="mt-6 space-y-3 text-sm leading-7 text-portfolio-copy-muted">
                    @foreach ($platformCallouts as $callout)
                        <li class="rounded-2xl border border-portfolio-outline/10 bg-black/20 px-4 py-3">
                            {{ $callout }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="portfolio-panel p-8">
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">Delivery Stack</p>
                @if ($featuredSkills->isNotEmpty())
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
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

    <x-public.section title="Selected Artifacts" eyebrow="More Work" intro="Additional builds, migrations, and systems work alongside long-term platform ownership." class="py-20">
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

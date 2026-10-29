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


        <section aria-labelledby="cover-title" class="relative isolate overflow-hidden">

            <div class="mx-auto grid max-w-screen-2xl gap-10 px-4 pt-10 pb-20 sm:px-8 xl:min-h-[calc(100dvh-4.5rem)] xl:grid-cols-[auto_minmax(0,1fr)_18rem] xl:items-center xl:gap-12">
                <p class="portfolio-vertical hidden font-ui text-[11px] font-bold tracking-[0.3em] text-portfolio-fog uppercase xl:block" aria-hidden="true">Taylor Portfolio &mdash; Senior Full Stack Developer &mdash; {{ $settings->location_label }}</p>

                <div class="grid gap-8">
                    <p class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-signal uppercase">{{ $settings->hero_kicker }}</p>
                    <h1 id="cover-title" class="font-display text-[clamp(3rem,8.5vw,10.5rem)] leading-[0.82] font-black break-words uppercase">
                        <span class="relative z-10 block">{{ $settings->hero_title }}</span>
                        <span class="relative isolate block py-[0.06em] text-portfolio-night before:absolute before:-top-[0.1em] before:-bottom-[0.04em] before:-right-[100vw] before:-left-4 before:-z-10 before:origin-left before:-rotate-2 before:bg-portfolio-signal">{{ $settings->hero_emphasis }}</span>
                        <span class="relative z-10 block">Digital Systems.</span>
                    </h1>
                    <p class="max-w-xl text-lg leading-relaxed text-portfolio-fog">{{ $settings->hero_summary }}</p>
                    <p class="max-w-xl text-sm leading-relaxed text-portfolio-fog"><strong class="font-ui text-portfolio-bone">Current focus:</strong> {{ $settings->availability_text }}</p>
                </div>
                <aside aria-labelledby="toc-title" class="relative z-10 grid gap-6 border-t-4 border-portfolio-signal bg-portfolio-night p-5 md:grid-cols-2 xl:grid-cols-1 xl:self-end">
                    <h2 id="toc-title" class="font-ui text-xs font-bold tracking-[0.24em] uppercase md:col-span-2 xl:col-span-1">In this issue</h2>
                    <ol class="grid gap-4">
                        <li><a href="#feature" class="group grid grid-cols-[2.5rem_minmax(0,1fr)] gap-3"><span class="font-display text-3xl leading-none font-black text-portfolio-signal">02</span><span class="font-medium leading-snug group-hover:text-portfolio-signal">Cover story: five years, one codebase</span></a></li>
                        <li><a href="#built" class="group grid grid-cols-[2.5rem_minmax(0,1fr)] gap-3"><span class="font-display text-3xl leading-none font-black text-portfolio-signal">03</span><span class="font-medium leading-snug group-hover:text-portfolio-signal">Built, then rebuilt better</span></a></li>
                        <li><a href="#work" class="group grid grid-cols-[2.5rem_minmax(0,1fr)] gap-3"><span class="font-display text-3xl leading-none font-black text-portfolio-signal">04</span><span class="font-medium leading-snug group-hover:text-portfolio-signal">Selected work</span></a></li>
                        <li><a href="{{ route('contact.create') }}" class="group grid grid-cols-[2.5rem_minmax(0,1fr)] gap-3"><span class="font-display text-3xl leading-none font-black text-portfolio-signal">05</span><span class="font-medium leading-snug group-hover:text-portfolio-signal">Letters: get in touch</span></a></li>
                    </ol>
                    <dl class="grid grid-cols-2 content-start gap-3 border-t border-portfolio-night-line pt-5 md:border-t-0 md:pt-0 xl:border-t xl:pt-5">
                        <div class="flex flex-col-reverse"><dt class="font-ui text-[11px] font-bold tracking-[0.16em] text-portfolio-fog uppercase">Years</dt><dd class="font-display text-5xl leading-none font-black">{{ $settings->years_experience }}</dd></div>
                        <div class="flex flex-col-reverse"><dt class="font-ui text-[11px] font-bold tracking-[0.16em] text-portfolio-fog uppercase">Projects</dt><dd class="font-display text-5xl leading-none font-black">{{ $settings->projects_completed }}+</dd></div>
                    </dl>
                    <div class="flex flex-wrap gap-2">
                        <x-public.button :href="route('projects.index')">View Work</x-public.button>
                        <x-public.button :href="route('contact.create')" variant="bone">Contact Me</x-public.button>
                    </div>
                </aside>
            </div>
        </section>
        <article id="feature" aria-labelledby="feature-title" class="portfolio-slant scroll-mt-4 bg-portfolio-bone py-[calc(4vw+5rem)] text-portfolio-night">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-8">
                <header class="grid gap-6 border-b-4 border-portfolio-night pb-10 lg:grid-cols-[minmax(0,8fr)_minmax(0,4fr)] lg:items-end">
                    <div>
                        <p class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-signal-deep uppercase">Cover story &middot; The project</p>
                        <h2 id="feature-title" class="mt-4 font-display text-[clamp(3rem,9vw,8rem)] leading-[0.85] font-black uppercase">Five years, one codebase.</h2>
                    </div>
                    <p class="text-xl leading-snug font-medium text-portfolio-bone-muted"><strong class="text-portfolio-night">Housing Compliance Platform</strong>. Since 2020, I have served as the sole in-house developer responsible for the continued growth, maintenance, and modernization of a Laravel-based affordable housing compliance platform used for reporting, occupancy workflows, documents, waitlists, and stakeholder communication.</p>
                </header>

                <div class="mt-12 grid gap-12 lg:grid-cols-[minmax(0,8fr)_minmax(0,4fr)]">
                    <div class="grid gap-10">
                        <div class="gap-10 text-lg leading-relaxed md:columns-2">
                            <p class="first-letter:float-left first-letter:mt-1 first-letter:mr-3 first-letter:font-display first-letter:text-8xl first-letter:leading-[0.75] first-letter:font-black first-letter:text-portfolio-signal-deep">I own the long-term technical direction and daily engineering execution for a business-critical Laravel platform, serving as the lead engineer, maintainer, and primary technical decision-maker.</p>
                            <p class="mt-6">The platform supports evolving regulatory and operational requirements across multiple affordable housing programs while staying reliable for internal teams.</p>
                            <p class="mt-6">Full-stack ownership across backend architecture, workflow design, reporting, document handling, queue processing, testing, deployments, and production maintenance.</p>
                        </div>

                        <figure class="-rotate-2 border-l-8 border-portfolio-signal pl-6">
                            <blockquote class="font-display text-[clamp(2.5rem,6vw,5rem)] leading-[0.9] font-black uppercase">
                                <p>&ldquo;More than feature delivery.&rdquo;</p>
                            </blockquote>
                            <figcaption class="mt-4 max-w-2xl text-portfolio-bone-muted">The system must reflect changing business rules, support multiple housing programs, absorb legacy data, and remain usable as complexity grows.</figcaption>
                        </figure>
                    </div>

                    <aside aria-labelledby="facts-title" class="h-fit bg-portfolio-night p-6 text-portfolio-bone lg:sticky lg:top-8">
                        <h3 id="facts-title" class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-signal uppercase">Fact box</h3>
                        <dl class="mt-5 grid gap-4 border-t border-portfolio-night-line pt-5">
                            <x-public.fact label="Role">Sole In-House Developer, 2020 &ndash; Present</x-public.fact>
                            <x-public.fact label="Program coverage">
                                <ul class="flex flex-wrap gap-1.5 font-ui text-xs font-bold uppercase">
                                    @foreach ($compliancePrograms as $program)
                                        <li class="portfolio-para bg-portfolio-signal px-3 py-1 text-portfolio-night">{{ $program }}</li>
                                    @endforeach
                                </ul>
                            </x-public.fact>
                        </dl>
                    </aside>
                </div>
            </div>
        </article>
        <section id="built" aria-labelledby="built-title" class="scroll-mt-4 py-24">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-8">
                <h2 id="built-title" class="font-display text-[clamp(3rem,8vw,7rem)] leading-[0.85] font-black uppercase">Built, <span class="text-portfolio-signal">then rebuilt better.</span></h2>

                <div class="relative mt-14 grid gap-14 lg:grid-cols-2 lg:gap-24">
                    <div class="absolute inset-y-0 left-1/2 hidden w-2 -translate-x-1/2 -skew-x-12 bg-portfolio-signal lg:block" aria-hidden="true"></div>

                    <div>
                        <h3 class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-fog uppercase">Core systems I delivered</h3>
                        <ol class="mt-8 grid gap-8">
                            @foreach ($platformBuildHighlights as $highlight)
                                <li class="grid grid-cols-[5rem_minmax(0,1fr)] gap-4">
                                    <span class="portfolio-text-outline font-display text-6xl leading-none font-black" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div><h4 class="font-display text-3xl font-black uppercase">{{ $highlight['title'] }}</h4><p class="mt-1 text-portfolio-fog">{{ $highlight['summary'] }}</p></div>
                                </li>
                            @endforeach
                        </ol>
                    </div>

                    <div class="lg:pt-24">
                        <h3 class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-fog uppercase">Modernization efforts</h3>
                        <ol class="mt-8 grid gap-8">
                            @foreach ($platformImprovementHighlights as $highlight)
                                <li class="grid grid-cols-[5rem_minmax(0,1fr)] gap-4">
                                    <span class="portfolio-text-outline portfolio-text-outline-bone font-display text-6xl leading-none font-black" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div><h4 class="font-display text-3xl font-black uppercase">{{ $highlight['title'] }}</h4><p class="mt-1 text-portfolio-fog">{{ $highlight['summary'] }}</p></div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section aria-labelledby="outcome-title" class="portfolio-slant bg-portfolio-signal py-[calc(4vw+4rem)] text-portfolio-night">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-8">
                <h2 id="outcome-title" class="font-ui text-xs font-bold tracking-[0.24em] uppercase">Outcome &middot; Sustained impact over time</h2>
                <dl class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                    <div class="flex flex-col-reverse gap-3 border-t-4 border-portfolio-night pt-4 lg:translate-y-24">
                        <dt class="font-medium">Years of continuous development on a single production codebase</dt>
                        <dd class="font-display text-[clamp(3.5rem,5.5vw,6rem)] leading-[0.8] font-black uppercase">5+</dd>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t-4 border-portfolio-night pt-4 lg:translate-y-16">
                        <dt class="font-medium">Primary engineer responsible for architecture, delivery, and maintenance</dt>
                        <dd class="font-display text-[clamp(3.5rem,5.5vw,6rem)] leading-[0.8] font-black uppercase">Solo</dd>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t-4 border-portfolio-night pt-4 lg:translate-y-8">
                        <dt class="font-medium">Reduced production incidents through testing, validation, and hardened deployments</dt>
                        <dd class="font-display text-[clamp(3.5rem,5.5vw,6rem)] leading-[0.8] font-black uppercase">Stable</dd>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t-4 border-portfolio-night pt-4">
                        <dt class="font-medium">Migrated legacy patterns to current Laravel conventions, queues, and CI/CD</dt>
                        <dd class="font-display text-[clamp(3.5rem,5.5vw,6rem)] leading-[0.8] font-black uppercase">Modern</dd>
                    </div>
                </dl>
                <div class="h-0 lg:h-24" aria-hidden="true"></div>
            </div>
        </section>
        @if ($featuredProjects->isNotEmpty())
        <section id="work" aria-labelledby="work-title" class="scroll-mt-4 py-24">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-8">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-signal uppercase">Selected artifacts</p>
                        <h2 id="work-title" class="mt-3 font-display text-[clamp(3rem,8vw,7rem)] leading-[0.85] font-black uppercase">The index</h2>
                    </div>
                    <x-public.button :href="route('projects.index')" variant="bone">Browse all work →</x-public.button>
                </div>

                <ol class="mt-12 border-t border-portfolio-night-line">
                    @foreach ($featuredProjects as $project)
                        <x-public.project-row :$project :index="$loop->iteration" />
                    @endforeach
                </ol>
            </div>
        </section>
        @endif

        @if ($featuredSkills->isNotEmpty())
        <section aria-labelledby="stack-title" class="border-y border-portfolio-night-line bg-portfolio-night-raised py-20">
            <div class="mx-auto grid max-w-screen-2xl gap-8 px-4 sm:px-8 lg:grid-cols-[auto_minmax(0,1fr)] lg:gap-16">
                <h2 id="stack-title" class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-signal uppercase lg:portfolio-vertical lg:self-start">Tools behind the work</h2>
                <ul class="flex flex-wrap gap-x-4 font-display text-[clamp(2rem,5vw,4.5rem)] leading-[0.95] font-black uppercase [&>li:not(:last-child)]:after:ml-4 [&>li:not(:last-child)]:after:text-portfolio-signal [&>li:not(:last-child)]:after:content-['/']">
                    @foreach ($featuredSkills as $skill)
                        <li>{{ $skill->name }}</li>
                    @endforeach
                </ul>
            </div>
        </section>

        @endif

        <section aria-labelledby="letters-title" class="portfolio-slant-top bg-portfolio-signal py-[calc(4vw+4rem)] text-portfolio-night [&_:focus-visible]:outline-portfolio-night">
            <div class="mx-auto flex max-w-screen-2xl flex-wrap items-end justify-between gap-8 px-4 sm:px-8">
                <div>
                    <x-public.kicker tone="night">Letters · Get in touch</x-public.kicker>
                    <h2 id="letters-title" class="mt-4 font-display text-[clamp(3.5rem,8vw,7.5rem)] leading-[0.82] font-black uppercase">Write in.</h2>
                    <p class="mt-6 max-w-xl text-lg font-medium">Reach out for relevant introductions, product conversations, or thoughtful professional inquiries.</p>
                </div>
                <x-public.button :href="route('contact.create')" variant="night">Get in touch →</x-public.button>
            </div>
        </section>
</x-layouts::public>

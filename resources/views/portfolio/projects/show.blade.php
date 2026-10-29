<x-layouts::public :title="$project->title" :$settings>
    <article class="portfolio-slant bg-portfolio-bone py-[calc(4vw+4rem)] text-portfolio-night [&_:focus-visible]:outline-portfolio-night">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-8">
            <a href="{{ route('projects.index') }}" class="font-ui text-xs font-bold tracking-[0.14em] uppercase underline underline-offset-4">← Back to the index</a>
            <header class="mt-10 grid gap-6 border-b-4 border-portfolio-night pb-10 lg:grid-cols-[minmax(0,8fr)_minmax(0,4fr)] lg:items-end">
                <div class="min-w-0">
                    <x-public.kicker tone="signal-deep">{{ $project->category }}</x-public.kicker>
                    <h1 class="mt-4 font-display text-[clamp(3rem,9vw,8rem)] leading-[0.85] font-black break-words wrap-anywhere uppercase">{{ $project->title }}</h1>
                </div>
                <p class="text-xl leading-snug font-medium text-portfolio-bone-muted">{{ $project->summary }}</p>
            </header>

            @if (filled($project->featured_image_path))
                <div class="portfolio-slant-top mt-12 bg-portfolio-signal px-3 pt-[calc(4vw+0.75rem)] pb-3 sm:px-6 sm:pb-6">
                    <img src="{{ asset('storage/'.$project->featured_image_path) }}" alt="{{ $project->title }}" loading="lazy" class="aspect-video w-full object-cover">
                </div>
            @endif

            <div class="mt-12 grid gap-12 lg:grid-cols-[minmax(0,8fr)_minmax(0,4fr)]">
                <div data-project-body class="min-w-0 space-y-6 text-lg leading-relaxed break-words wrap-anywhere">
                    @foreach ($project->body_paragraphs as $paragraph)
                        <p @class(['first-letter:float-left first-letter:mt-1 first-letter:mr-3 first-letter:font-display first-letter:text-8xl first-letter:leading-[0.75] first-letter:font-black first-letter:text-portfolio-signal-deep' => $loop->first])>{{ $paragraph }}</p>
                    @endforeach
                </div>

                <aside aria-labelledby="project-facts-title" class="h-fit bg-portfolio-night p-6 text-portfolio-bone lg:sticky lg:top-8 [&_:focus-visible]:outline-portfolio-signal">
                    <h2 id="project-facts-title" class="font-ui text-xs font-bold tracking-[0.24em] text-portfolio-signal uppercase">Fact box</h2>
                    <dl class="mt-5 grid gap-6 border-t border-portfolio-night-line pt-5">
                        <x-public.fact label="Category">{{ $project->category }}</x-public.fact>
                        @if ($project->skills->isNotEmpty())
                            <x-public.fact label="Stack">
                                <ul class="flex flex-wrap gap-1.5 font-ui text-xs font-bold uppercase">
                                    @foreach ($project->skills as $skill)
                                        <li class="portfolio-para bg-portfolio-signal px-3 py-1 text-portfolio-night">{{ $skill->name }}</li>
                                    @endforeach
                                </ul>
                            </x-public.fact>
                        @endif
                        @if (filled($project->live_url) || filled($project->repo_url) || filled($project->case_study_url))
                            <x-public.fact label="Links">
                                <div class="flex flex-col items-start gap-3">
                                    @if (filled($project->live_url))
                                        <x-public.button :href="$project->live_url" target="_blank" rel="noreferrer">Visit site ↗</x-public.button>
                                    @endif
                                    @if (filled($project->repo_url))
                                        <x-public.button :href="$project->repo_url" target="_blank" rel="noreferrer">Source code ↗</x-public.button>
                                    @endif
                                    @if (filled($project->case_study_url))
                                        <x-public.button :href="$project->case_study_url" target="_blank" rel="noreferrer">Case study ↗</x-public.button>
                                    @endif
                                </div>
                            </x-public.fact>
                        @endif
                    </dl>
                </aside>
            </div>
        </div>
    </article>

    @if ($relatedProjects->isNotEmpty())
        <section aria-labelledby="related-title" class="mx-auto max-w-screen-2xl px-4 py-20 sm:px-8">
            <x-public.kicker>Related work</x-public.kicker>
            <h2 id="related-title" class="mt-4 font-display text-[clamp(3rem,8vw,7rem)] leading-[0.85] font-black uppercase">More from the index</h2>
            <ol class="mt-12 border-t border-portfolio-night-line">
                @foreach ($relatedProjects as $relatedProject)
                    <x-public.project-row :project="$relatedProject" :index="$loop->iteration" />
                @endforeach
            </ol>
        </section>
    @endif
</x-layouts::public>

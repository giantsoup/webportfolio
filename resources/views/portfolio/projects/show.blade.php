<x-layouts::public :title="$project->title" :$settings>
    <article class="mx-auto max-w-5xl px-6 py-20 lg:px-8">
        <div class="space-y-6">
            <a href="{{ route('projects.index') }}" class="font-label text-[10px] font-semibold uppercase tracking-[0.2em] text-portfolio-secondary">← Back to work</a>
            <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-primary">{{ $project->category }}</p>
            <h1 class="font-headline text-5xl font-bold tracking-tight text-portfolio-copy md:text-7xl">{{ $project->title }}</h1>
            <p class="max-w-3xl text-lg leading-8 text-portfolio-copy-muted">{{ $project->summary }}</p>
        </div>

        <div class="portfolio-panel mt-12 overflow-hidden">
            <div class="aspect-video bg-linear-to-br from-portfolio-primary/15 via-portfolio-surface-low to-portfolio-secondary/10">
                @if (filled($project->featured_image_path))
                    <img src="{{ asset('storage/'.$project->featured_image_path) }}" alt="{{ $project->title }}" class="size-full object-cover opacity-90">
                @endif
            </div>
        </div>

        <div class="mt-12 grid gap-10 lg:grid-cols-[0.7fr_0.3fr]">
            <div class="rounded-3xl bg-portfolio-surface-card p-8">
                <div class="prose prose-invert max-w-none whitespace-pre-line text-portfolio-copy-muted">
                    {{ $project->body }}
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-3xl bg-portfolio-surface-card p-6">
                    <p class="font-label text-[10px] uppercase tracking-[0.22em] text-portfolio-copy-muted">Links</p>
                    <div class="mt-4 flex flex-col gap-3">
                        @if (filled($project->live_url))
                            <a href="{{ $project->live_url }}" target="_blank" rel="noreferrer" class="portfolio-button-secondary">Visit Site</a>
                        @endif
                        @if (filled($project->repo_url))
                            <a href="{{ $project->repo_url }}" target="_blank" rel="noreferrer" class="portfolio-button-secondary">Source Code</a>
                        @endif
                        @if (filled($project->case_study_url))
                            <a href="{{ $project->case_study_url }}" target="_blank" rel="noreferrer" class="portfolio-button-secondary">Case Study</a>
                        @endif
                    </div>
                </div>

                <div class="rounded-3xl bg-portfolio-surface-card p-6">
                    <p class="font-label text-[10px] uppercase tracking-[0.22em] text-portfolio-copy-muted">Stack</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($project->skills as $skill)
                            <span class="rounded-full bg-black/30 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: {{ $skill->accent_color }};">
                                {{ $skill->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </article>

    @if ($relatedProjects->isNotEmpty())
        <x-public.section title="Related Work" eyebrow="More" class="pb-20">
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($relatedProjects as $relatedProject)
                    <x-public.project-card :project="$relatedProject" />
                @endforeach
            </div>
        </x-public.section>
    @endif
</x-layouts::public>

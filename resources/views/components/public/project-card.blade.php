@props([
    'project',
])

<article class="group overflow-hidden rounded-3xl border border-portfolio-outline/20 bg-portfolio-surface-card transition duration-300 hover:-translate-y-1 hover:border-portfolio-tertiary/40 hover:bg-portfolio-surface-high">
    <div class="aspect-video bg-linear-to-br from-portfolio-primary/15 via-portfolio-surface-low to-portfolio-secondary/10">
        @if (filled($project->featured_image_path))
            <img src="{{ asset('storage/'.$project->featured_image_path) }}" alt="{{ $project->title }}" class="size-full object-cover opacity-80 transition duration-500 group-hover:scale-105 group-hover:opacity-100">
        @endif
    </div>

    <div class="space-y-5 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.2em] text-portfolio-secondary">{{ $project->category }}</p>
                <h3 class="mt-2 font-headline text-2xl font-bold text-portfolio-copy">{{ $project->title }}</h3>
            </div>

            @if ($project->is_featured)
                <span class="rounded-full border border-portfolio-primary/30 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-portfolio-primary">Featured</span>
            @endif
        </div>

        <p class="text-sm leading-7 text-portfolio-copy-muted">{{ $project->summary }}</p>

        @if ($project->skills->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach ($project->skills as $skill)
                    <span class="rounded-full bg-black/30 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-portfolio-copy-muted" wire:key="project-card-skill-{{ $project->id }}-{{ $skill->id }}">
                        {{ $skill->name }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('projects.show', $project) }}" class="portfolio-button-secondary">View Details</a>

            @if (filled($project->live_url))
                <a href="{{ $project->live_url }}" target="_blank" rel="noreferrer" class="text-sm font-semibold uppercase tracking-[0.16em] text-portfolio-copy/70 transition hover:text-portfolio-secondary">Live</a>
            @endif

            @if (filled($project->repo_url))
                <a href="{{ $project->repo_url }}" target="_blank" rel="noreferrer" class="text-sm font-semibold uppercase tracking-[0.16em] text-portfolio-copy/70 transition hover:text-portfolio-secondary">Repo</a>
            @endif
        </div>
    </div>
</article>

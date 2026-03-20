<x-layouts::public :title="__('Work')" :$settings>
    <section class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
        <div class="max-w-3xl space-y-6">
            <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-primary">Architecture & Engineering</p>
            <h1 class="font-headline text-5xl font-bold tracking-tight text-portfolio-copy md:text-7xl">
                Selected <span class="portfolio-text-gradient">Artifacts</span>
            </h1>
            <p class="text-lg leading-8 text-portfolio-copy-muted">
                A curated collection of Laravel systems, modernization work, APIs, and product delivery engagements.
            </p>
        </div>

        <div class="mt-14 grid gap-6 lg:grid-cols-3">
            @forelse ($projects as $project)
                <x-public.project-card :project="$project" />
            @empty
                <div class="portfolio-panel p-8 text-portfolio-copy-muted lg:col-span-3">
                    No published projects yet. Add one from the admin to populate the portfolio.
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $projects->links() }}
        </div>
    </section>
</x-layouts::public>

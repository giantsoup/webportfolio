<x-layouts::public :title="__('Work')" :$settings>
    <section aria-labelledby="work-title" class="mx-auto max-w-screen-2xl px-4 py-20 sm:px-8">
        <header class="max-w-3xl">
            <x-public.kicker>Selected artifacts</x-public.kicker>
            <h1 id="work-title" class="mt-4 font-display text-[clamp(3.5rem,9vw,8rem)] leading-[0.85] font-black uppercase">The index</h1>
            <p class="mt-6 text-lg leading-relaxed text-portfolio-fog">A curated collection of Laravel systems, modernization work, APIs, and product delivery engagements.</p>
        </header>

        @if ($projects->isNotEmpty())
            <ol class="mt-12 border-t border-portfolio-night-line" start="{{ $projects->firstItem() }}">
                @foreach ($projects as $project)
                    <x-public.project-row :$project :index="$projects->firstItem() + $loop->index" />
                @endforeach
            </ol>
        @else
            <p class="mt-12 border-t border-portfolio-night-line py-8 text-portfolio-fog">New work is being written up. Check back soon.</p>
        @endif

        {{ $projects->links('portfolio.partials.pagination') }}
    </section>
</x-layouts::public>

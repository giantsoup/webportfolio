@props([
    'title',
    'intro' => null,
    'eyebrow' => null,
])

<section {{ $attributes->class(['mx-auto max-w-7xl px-6 lg:px-8']) }}>
    <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-2xl">
            @if (filled($eyebrow))
                <p class="font-label text-[10px] font-semibold uppercase tracking-[0.22em] text-portfolio-primary">{{ $eyebrow }}</p>
            @endif

            <h2 class="mt-3 font-headline text-3xl font-bold uppercase tracking-tight text-portfolio-copy md:text-4xl">
                {{ $title }}
            </h2>

            @if (filled($intro))
                <p class="mt-4 text-base leading-8 text-portfolio-copy-muted">{{ $intro }}</p>
            @endif
        </div>

        @if (isset($actions))
            <div>{{ $actions }}</div>
        @endif
    </div>

    {{ $slot }}
</section>

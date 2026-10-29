@props([
    'heading' => null,
    'description' => null,
])

<flux:card {{ $attributes->class('space-y-6 dark:bg-zinc-900 dark:border-zinc-700') }}>
    @if ($heading)
        <div class="flex items-start justify-between gap-4 border-b border-zinc-200 pb-4 dark:border-zinc-700">
            <div class="min-w-0">
                <h2 class="flex items-center gap-2 font-ui text-xs font-bold tracking-[0.18em] text-zinc-900 uppercase dark:text-white">
                    <span class="portfolio-para h-2 w-3.5 shrink-0 bg-accent" aria-hidden="true"></span>
                    {{ $heading }}
                </h2>

                @if ($description)
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
                @endif
            </div>

            {{ $actions ?? '' }}
        </div>
    @endif

    {{ $slot }}
</flux:card>

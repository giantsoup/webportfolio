@props([
    'heading',
    'kicker' => null,
    'description' => null,
])

<header {{ $attributes->class('flex flex-col gap-4 border-b border-zinc-200 pb-6 sm:flex-row sm:items-end sm:justify-between dark:border-zinc-700') }}>
    <div class="min-w-0">
        @if ($kicker)
            <p class="font-ui text-[11px] font-bold tracking-[0.2em] text-accent-content uppercase">{{ $kicker }}</p>
        @endif

        <h1 class="mt-1 font-display text-4xl leading-none font-black tracking-wide text-zinc-900 uppercase break-words dark:text-white">{{ $heading }}</h1>

        @if ($description)
            <p class="mt-2 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</header>

@props([
    'title',
    'description',
])

<div class="flex w-full flex-col">
    <p class="font-ui text-[11px] font-bold tracking-[0.2em] text-accent-content uppercase">{{ __('Back office') }}</p>
    <h1 class="mt-1 font-display text-4xl leading-none font-black tracking-wide break-words text-zinc-900 uppercase dark:text-white">{{ $title }}</h1>
    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
</div>

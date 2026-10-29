@props([
    'sidebar' => false,
])

@if($sidebar)
    <a {{ $attributes->class('flex min-w-0 items-center gap-3 rounded-lg px-2 py-1') }}>
        <x-public.logo-mark class="h-7" />
        <span class="grid min-w-0 leading-none">
            <span class="truncate font-display text-lg font-black tracking-wide text-zinc-900 uppercase dark:text-white">Taylor Portfolio</span>
            <span class="mt-1 font-ui text-[10px] font-bold tracking-[0.2em] text-zinc-500 uppercase dark:text-zinc-400">{{ __('Back office') }}</span>
        </span>
    </a>
@else
    <flux:brand name="Taylor Portfolio" {{ $attributes }}>
        <x-slot name="logo" class="flex h-8 items-center justify-center">
            <x-public.logo-mark class="h-6" />
        </x-slot>
    </flux:brand>
@endif

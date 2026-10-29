@props(['href' => null, 'variant' => 'signal'])

@php
    $classes = [
        'portfolio-para inline-flex cursor-pointer items-center justify-center gap-2 px-6 py-3 font-ui text-sm font-bold tracking-[0.14em] uppercase transition-transform hover:translate-x-1',
        'bg-portfolio-signal text-portfolio-night' => $variant === 'signal',
        'bg-portfolio-bone text-portfolio-night' => $variant === 'bone',
        'bg-portfolio-night text-portfolio-bone' => $variant === 'night',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->class($classes)->merge(['type' => 'submit']) }}>{{ $slot }}</button>
@endif

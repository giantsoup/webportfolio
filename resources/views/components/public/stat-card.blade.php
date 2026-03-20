@props([
    'label',
    'value',
    'suffix' => null,
    'accent' => 'primary',
])

@php
    $accentClass = match ($accent) {
        'secondary' => 'border-portfolio-secondary/40 text-portfolio-secondary',
        'tertiary' => 'border-portfolio-tertiary/40 text-portfolio-tertiary',
        default => 'border-portfolio-primary/40 text-portfolio-primary',
    };
@endphp

<div class="rounded-2xl border-l-2 bg-black/50 p-5 {{ $accentClass }}">
    <p class="font-label text-[10px] uppercase tracking-[0.22em] text-portfolio-copy-muted">{{ $label }}</p>
    <p class="mt-2 font-headline text-4xl font-bold text-portfolio-copy">
        {{ $value }}
        @if (filled($suffix))
            <span class="text-lg {{ $accentClass }}">{{ $suffix }}</span>
        @endif
    </p>
</div>

@props([
    'text',
])

<div class="flex items-center gap-3">
    <span class="relative flex size-3">
        <span class="absolute inline-flex size-full animate-ping rounded-full bg-portfolio-secondary opacity-75"></span>
        <span class="relative inline-flex size-3 rounded-full bg-portfolio-secondary"></span>
    </span>

    <span class="font-label text-[11px] font-semibold uppercase tracking-[0.22em] text-portfolio-secondary">
        {{ $text }}
    </span>
</div>

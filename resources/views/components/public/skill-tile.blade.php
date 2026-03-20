@props([
    'skill',
])

<article class="flex aspect-video flex-col justify-between rounded-2xl bg-portfolio-surface-card p-5 transition hover:-translate-y-0.5 hover:bg-portfolio-surface-high">
    <span class="font-headline text-lg font-semibold" style="color: {{ $skill->accent_color }};">
        {{ $skill->name }}
    </span>
    <div class="space-y-2">
        <p class="font-label text-[10px] uppercase tracking-[0.22em] text-portfolio-copy-muted">{{ $skill->category }}</p>
        @if (filled($skill->icon))
            <p class="text-sm text-portfolio-copy/80">{{ $skill->icon }}</p>
        @endif
    </div>
</article>

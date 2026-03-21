@props([
    'skill',
])

<article class="flex aspect-video flex-col justify-between rounded-2xl bg-portfolio-surface-card p-5 transition hover:-translate-y-0.5 hover:bg-portfolio-surface-high">
    <div>
        <span class="font-headline text-lg font-semibold" style="color: {{ $skill->accent_color }};">
            {{ $skill->name }}
        </span>
        <p class="mt-1 font-label text-[10px] uppercase tracking-[0.22em] text-portfolio-copy-muted">{{ $skill->category }}</p>
    </div>
    @if (filled($skill->icon))
        @svg('heroicon-o-' . $skill->icon, 'size-8 text-portfolio-copy-muted/40')
    @endif
</article>

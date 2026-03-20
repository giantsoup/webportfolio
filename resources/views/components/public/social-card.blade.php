@props([
    'href',
    'label',
    'description',
])

<a href="{{ $href }}" target="_blank" rel="noreferrer" class="block rounded-3xl border border-portfolio-outline/20 bg-portfolio-surface-card p-6 transition hover:-translate-y-0.5 hover:border-portfolio-secondary/40 hover:bg-portfolio-surface-high">
    <p class="font-headline text-2xl font-bold text-portfolio-copy">{{ $label }}</p>
    <p class="mt-3 text-sm leading-7 text-portfolio-copy-muted">{{ $description }}</p>
</a>

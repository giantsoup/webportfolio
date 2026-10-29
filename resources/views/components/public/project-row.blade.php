@props(['project', 'index'])

<li {{ $attributes->class(['group relative isolate grid gap-4 border-b border-portfolio-night-line py-8 md:grid-cols-[4rem_minmax(0,1.1fr)_minmax(0,1fr)] md:items-center md:gap-8']) }}>
    <span class="absolute inset-y-3 -right-4 -left-4 -z-10 origin-left scale-x-0 -skew-x-12 bg-portfolio-signal transition-transform duration-300 group-focus-within:scale-x-100 group-hover:scale-x-100" aria-hidden="true"></span>
    <span class="font-ui text-sm font-bold text-portfolio-signal group-focus-within:text-portfolio-night group-hover:text-portfolio-night">{{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}</span>
    <h3 class="min-w-0 font-display text-[clamp(2.75rem,6vw,5.5rem)] leading-[0.85] font-black break-words wrap-anywhere uppercase group-focus-within:text-portfolio-night group-hover:text-portfolio-night">
        <a href="{{ route('projects.show', $project) }}" class="after:absolute after:inset-0 focus-visible:after:outline-2 focus-visible:after:outline-offset-4 focus-visible:after:outline-portfolio-bone">{{ $project->title }}</a>
    </h3>
    <div class="grid min-w-0 gap-2 break-words wrap-anywhere group-focus-within:text-portfolio-night group-hover:text-portfolio-night">
        <p class="font-ui text-xs font-bold tracking-[0.16em] uppercase">{{ $project->category }} · Read the project ↗</p>
        <p class="text-portfolio-fog group-focus-within:text-portfolio-night group-hover:text-portfolio-night">{{ $project->summary }}</p>
    </div>
</li>

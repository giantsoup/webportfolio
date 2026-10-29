@if ($paginator->hasPages())
    <nav aria-label="Pagination" class="mt-12 flex flex-wrap items-center justify-between gap-4 font-ui text-sm font-bold">
        @if ($paginator->onFirstPage())
            <span class="px-6 py-3 text-portfolio-fog" aria-disabled="true">← Previous</span>
        @else
            <x-public.button :href="$paginator->previousPageUrl()" variant="bone" rel="prev">← Previous</x-public.button>
        @endif

        <div class="flex flex-wrap gap-1">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-2 text-portfolio-fog">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span aria-current="page" aria-label="Page {{ $page }}" class="portfolio-para bg-portfolio-signal px-4 py-2 text-portfolio-night">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Go to page {{ $page }}" class="portfolio-para bg-portfolio-night-raised [--portfolio-control-focus:var(--color-portfolio-bone)] hover:[--portfolio-control-focus:var(--color-portfolio-night)] px-4 py-2 hover:bg-portfolio-bone hover:text-portfolio-night">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <x-public.button :href="$paginator->nextPageUrl()" variant="bone" rel="next">Next →</x-public.button>
        @else
            <span class="px-6 py-3 text-portfolio-fog" aria-disabled="true">Next →</span>
        @endif
    </nav>
@endif

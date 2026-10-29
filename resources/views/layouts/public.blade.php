<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <meta name="description" content="{{ $settings->hero_summary }}">
    </head>
    <body class="portfolio min-h-dvh overflow-x-clip bg-portfolio-night text-portfolio-bone antialiased selection:bg-portfolio-signal selection:text-portfolio-night">
        <a href="#main" class="sr-only z-50 bg-portfolio-signal px-4 py-2 font-ui text-sm font-bold text-portfolio-night uppercase focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

        <header class="border-b border-portfolio-night-line">
            <div class="mx-auto flex max-w-screen-2xl items-center justify-between gap-4 px-4 py-4 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Taylor Portfolio, home">
                    <x-public.logo-mark />
                    <span class="hidden font-display text-2xl leading-none font-black tracking-wide uppercase sm:block">Taylor Portfolio</span>
                </a>
                <nav aria-label="Primary">
                    <ul class="flex gap-1 font-ui text-xs font-bold tracking-[0.14em] uppercase">
                        @foreach (['home' => 'Home', 'projects.index' => 'Work', 'contact.create' => 'Contact'] as $route => $label)
                            @php
                                $active = request()->routeIs(match ($route) {
                                    'projects.index' => 'projects.*',
                                    'contact.create' => 'contact.*',
                                    default => 'home',
                                });
                            @endphp
                            <li>
                                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif @class([
                                    'portfolio-para block px-4 py-2 transition-colors',
                                    'bg-portfolio-signal text-portfolio-night' => $active,
                                    'bg-portfolio-night-raised [--portfolio-control-focus:var(--color-portfolio-bone)] hover:bg-portfolio-bone hover:text-portfolio-night hover:[--portfolio-control-focus:var(--color-portfolio-night)]' => ! $active,
                                ])>{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
        </header>

        <main id="main" tabindex="-1">{{ $slot }}</main>

        <footer class="border-t border-portfolio-night-line">
            <div class="mx-auto flex max-w-screen-2xl flex-col gap-4 px-4 py-10 font-ui text-[11px] font-bold tracking-[0.18em] text-portfolio-fog uppercase sm:px-8 md:flex-row md:flex-wrap md:justify-between">
                <p>© {{ now()->year }} Taylor Portfolio. All rights reserved.</p>
                <div class="flex gap-5">
                    @foreach (['github_url' => 'GitHub', 'linkedin_url' => 'LinkedIn', 'x_url' => 'X'] as $field => $label)
                        @if (filled($settings->$field))
                            <a href="{{ $settings->$field }}" target="_blank" rel="noreferrer" class="transition-colors hover:text-portfolio-signal">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
                <div class="grid gap-2">
                    @if (filled($settings->location_label))
                        <p>{{ $settings->location_label }}</p>
                    @endif
                    @if (filled($settings->schedule_label))
                        <p>{{ $settings->schedule_label }}</p>
                    @endif
                </div>
            </div>
        </footer>
    </body>
</html>

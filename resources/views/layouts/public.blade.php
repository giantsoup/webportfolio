<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-portfolio-surface text-portfolio-copy selection:bg-portfolio-primary/30 selection:text-portfolio-copy">
        <div class="portfolio-grid fixed inset-0 opacity-50"></div>
        <div class="portfolio-glow-primary pointer-events-none fixed left-[-10%] top-24 size-72 rounded-full bg-portfolio-primary/10 blur-3xl"></div>
        <div class="portfolio-glow-secondary pointer-events-none fixed bottom-16 right-[-10%] size-80 rounded-full bg-portfolio-secondary/10 blur-3xl"></div>

        <div class="relative z-10">
            <header class="sticky top-0 z-40 border-b border-portfolio-outline/20 bg-portfolio-surface/80 backdrop-blur-xl">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                    <a href="{{ route('home') }}" class="font-headline text-lg font-bold uppercase tracking-[0.24em] text-portfolio-primary">
                        Taylor Portfolio
                    </a>

                    <nav class="hidden items-center gap-6 font-headline text-sm font-semibold uppercase tracking-[0.16em] md:flex">
                        <a href="{{ route('home') }}" @class([
                            'transition hover:text-portfolio-primary',
                            'text-portfolio-secondary' => request()->routeIs('home'),
                            'text-portfolio-copy/70' => ! request()->routeIs('home'),
                        ])>Home</a>
                        <a href="{{ route('projects.index') }}" @class([
                            'transition hover:text-portfolio-primary',
                            'text-portfolio-secondary' => request()->routeIs('projects.*'),
                            'text-portfolio-copy/70' => ! request()->routeIs('projects.*'),
                        ])>Work</a>
                        <a href="{{ route('contact.create') }}" @class([
                            'transition hover:text-portfolio-primary',
                            'text-portfolio-secondary' => request()->routeIs('contact.*'),
                            'text-portfolio-copy/70' => ! request()->routeIs('contact.*'),
                        ])>Contact</a>
                    </nav>
                </div>
            </header>

            <main>
                {{ $slot }}
            </main>

            <footer class="border-t border-portfolio-outline/20 bg-portfolio-surface/95">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-10 text-xs uppercase tracking-[0.2em] text-portfolio-copy-muted md:flex-row md:items-center md:justify-between lg:px-8">
                    <p>© {{ now()->year }} Taylor Portfolio. All rights reserved.</p>

                    <div class="flex gap-5">
                        @if (filled($settings->github_url ?? null))
                            <a href="{{ $settings->github_url }}" target="_blank" rel="noreferrer" class="transition hover:text-portfolio-secondary">GitHub</a>
                        @endif

                        @if (filled($settings->linkedin_url ?? null))
                            <a href="{{ $settings->linkedin_url }}" target="_blank" rel="noreferrer" class="transition hover:text-portfolio-secondary">LinkedIn</a>
                        @endif

                        @if (filled($settings->x_url ?? null))
                            <a href="{{ $settings->x_url }}" target="_blank" rel="noreferrer" class="transition hover:text-portfolio-secondary">X</a>
                        @endif
                    </div>
                </div>
            </footer>
        </div>

        @fluxScripts
    </body>
</html>

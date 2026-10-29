<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="admin min-h-svh bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="h-1 bg-accent" aria-hidden="true"></div>

        <main class="flex min-h-[calc(100svh-0.25rem)] flex-col items-center justify-center gap-8 px-4 py-10 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ __('Taylor Portfolio, home') }}">
                <x-public.logo-mark class="h-8" />
                <span class="font-display text-2xl leading-none font-black tracking-wide uppercase">Taylor Portfolio</span>
            </a>

            <div class="w-full max-w-sm rounded-xl border border-zinc-200 bg-white p-6 sm:p-8 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>

            <a href="{{ route('home') }}" class="font-ui text-[11px] font-bold tracking-[0.2em] text-zinc-500 uppercase transition-colors hover:text-accent-content dark:text-zinc-400 dark:hover:text-accent-content">← {{ __('Back to the site') }}</a>
        </main>

        @fluxScripts
    </body>
</html>

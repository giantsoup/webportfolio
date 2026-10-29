@props(['tone' => 'signal'])

<p {{ $attributes->class([
    'font-ui text-xs font-bold tracking-[0.24em] uppercase',
    'text-portfolio-signal' => $tone === 'signal',
    'text-portfolio-signal-deep' => $tone === 'signal-deep',
    'text-portfolio-fog' => $tone === 'fog',
    'text-portfolio-night' => $tone === 'night',
]) }}>{{ $slot }}</p>

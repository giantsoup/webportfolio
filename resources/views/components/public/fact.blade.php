@props(['label'])

<div {{ $attributes->class(['grid gap-3']) }}>
    <dt class="font-ui text-xs font-bold tracking-[0.14em] text-portfolio-fog uppercase">{{ $label }}</dt>
    <dd class="min-w-0 break-words wrap-anywhere">{{ $slot }}</dd>
</div>

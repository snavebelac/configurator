@props([
    'title' => null,
    'meta' => null,
])
<header {{ $attributes->class(['flex items-baseline justify-between border-b border-rule-soft px-[22px] pb-3.5 pt-[18px]']) }}>
    @if ($title)
        <h3 class="font-display text-[18px] font-medium text-ink" style="font-variation-settings: 'opsz' 24;">
            {{ $title }}
        </h3>
    @else
        {{ $slot }}
    @endif
    @if ($meta)
        <span class="text-xs text-slate">{{ $meta }}</span>
    @endif
</header>

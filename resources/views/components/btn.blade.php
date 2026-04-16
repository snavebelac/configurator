@props([
    'variant' => 'quiet',
    'href' => null,
    'type' => 'button',
])
@php
    $base = 'inline-flex items-center gap-2 rounded-lg text-[13px] font-medium transition-colors';
    $sizing = match ($variant) {
        'accent' => 'px-4 py-[9px] font-semibold',
        'ghost'  => 'px-4 py-[9px] border border-rule bg-white text-ink hover:bg-paper-2',
        'row'    => 'px-2.5 py-1.5 text-xs',
        default  => 'px-3 py-[7px] text-slate hover:bg-paper-2 hover:text-ink',
    };
    $tone = match ($variant) {
        'accent'      => 'border border-fox bg-fox text-ink hover:bg-fox-deep hover:border-fox-deep',
        'ghost'       => '',
        'destructive' => 'text-status-rejected-fg hover:bg-status-rejected-bg',
        default       => '',
    };
    $classes = trim("$base $sizing $tone");
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif

@props([
    'value' => 0,
    'size' => 'body',
    'precise' => false,
    'symbol' => '£',
])
@php
    $decimals = $precise ? 2 : 0;
    $amount = number_format((float) $value, $decimals, '.', ',');

    [$container, $symbolClass] = match ($size) {
        'kpi'     => ['font-display text-[38px] leading-none tracking-[-0.025em] text-ink', 'mr-0.5 align-[5px] text-2xl text-slate-soft'],
        'kpi-fox' => ['font-display text-[38px] leading-none tracking-[-0.025em] text-fox', 'mr-0.5 align-[5px] text-2xl text-fox-deep'],
        'row'     => ['font-display text-[17px] text-ink', 'mr-0.5 text-sm text-slate-soft'],
        'mono'    => ['font-mono text-[13px] text-ink', 'mr-0.5 text-slate-soft'],
        default   => ['text-ink', 'mr-0.5 text-slate-soft'],
    };
@endphp
<span {{ $attributes->class($container) }}>
    <span class="{{ $symbolClass }}">{{ $symbol }}</span><span class="tnum">{{ $amount }}</span>
</span>

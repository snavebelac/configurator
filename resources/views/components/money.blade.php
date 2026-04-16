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
        'kpi'     => ['font-display text-[38px] font-[450] leading-none tracking-[-0.025em] text-ink', 'mr-0.5 align-[5px] text-2xl text-slate-soft'],
        'kpi-fox' => ['font-display text-[38px] font-[450] leading-none tracking-[-0.025em] text-fox', 'mr-0.5 align-[5px] text-2xl text-fox-deep'],
        'row'     => ['font-display text-[17px] font-medium text-ink', 'mr-0.5 text-sm text-slate-soft'],
        'mono'    => ['font-mono text-[13px] text-ink', 'mr-0.5 text-slate-soft'],
        default   => ['text-ink', 'mr-0.5 text-slate-soft'],
    };
    $styleAttr = in_array($size, ['kpi', 'kpi-fox']) ? " style=\"font-variation-settings: 'opsz' 96;\"" : ($size === 'row' ? " style=\"font-variation-settings: 'opsz' 36;\"" : '');
@endphp
<span {!! $attributes->class($container) !!}{!! $styleAttr !!}>
    <span class="{{ $symbolClass }}">{{ $symbol }}</span><span class="tnum">{{ $amount }}</span>
</span>

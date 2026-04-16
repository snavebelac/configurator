@props([
    'title',
    'eyebrow' => null,
    'lede' => null,
])
<div {{ $attributes->class(['mb-9 flex items-end justify-between gap-8 border-b border-rule pb-7']) }}>
    <div class="flex max-w-2xl flex-col gap-1.5">
        @if ($eyebrow)
            <span class="text-[11px] font-medium uppercase tracking-[0.14em] text-slate">{{ $eyebrow }}</span>
        @endif
        <h1 class="font-display text-[clamp(34px,3.4vw,46px)] font-[450] leading-[1.04] tracking-[-0.022em] text-ink"
            style="font-variation-settings: 'opsz' 144, 'SOFT' 50;">
            {{ $title }}
        </h1>
        @if ($lede)
            <p class="max-w-xl text-[14.5px] text-slate">{{ $lede }}</p>
        @endif
    </div>
    @if (isset($actions))
        <div class="flex gap-2">{{ $actions }}</div>
    @endif
</div>

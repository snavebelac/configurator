@props([
    'title' => null,
    'subtitle' => null,
])
<div class="overflow-hidden rounded-2xl border border-rule bg-white shadow-xl">
    @if ($title)
        <header class="border-b border-rule-soft px-8 pt-6 pb-4">
            <h2 class="font-display text-[22px] font-medium leading-tight text-ink" style="font-variation-settings: 'opsz' 96;">
                {{ $title }}
            </h2>
            @if ($subtitle)
                <p class="mt-1 text-[13px] text-slate">{{ $subtitle }}</p>
            @endif
        </header>
    @endif
    {{ $slot }}
</div>

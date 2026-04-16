@props([
    'eyebrow',
    'heading',
    'lede' => null,
])
<div class="grid min-h-screen place-items-center px-6 py-12">
    <div class="w-full max-w-[420px]">
        <a href="{{ route('home') }}" class="mx-auto flex size-12 items-center justify-center rounded-xl bg-ink text-fox" title="Configurator">
            <x-logo class="size-[22px]" />
        </a>

        <div class="mt-8 text-center">
            <p class="text-[11px] font-medium uppercase tracking-[0.14em] text-slate">{{ $eyebrow }}</p>
            <h1 class="mt-3 font-display text-[34px] italic leading-[1.1] tracking-[-0.04em] text-ink">{{ $heading }}</h1>
            @if ($lede)
                <p class="mx-auto mt-3 max-w-[34ch] text-[14px] leading-[1.55] text-slate">{{ $lede }}</p>
            @endif
        </div>

        <div class="mt-8 rounded-2xl border border-rule bg-white p-7 shadow-[0_1px_0_rgba(0,0,0,0.02)]">
            {{ $slot }}
        </div>

        @isset ($footer)
            <div class="mt-6 text-center text-[13px] text-slate">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>

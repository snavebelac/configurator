@php
    $isOptional = (bool) $feature->optional;
    $lineTotal = (float) ($feature->price * $feature->quantity);
@endphp
<div wire:key="preview-row-{{ $feature->id }}"
     @if ($isOptional)
        x-bind:class="isOn({{ $feature->id }}) ? 'opacity-100' : 'opacity-45'"
     @endif
     @class([
        'grid gap-6 py-5 transition-opacity duration-200 sm:grid-cols-[1fr_auto]',
        'pl-8' => $isChild,
     ])>

    {{-- Left: name + description + marker --}}
    <div class="min-w-0">
        <div class="flex items-baseline gap-3">
            @if ($isChild)
                <x-phosphor-arrow-elbow-down-right class="mt-1 size-3 shrink-0 text-slate-soft" />
            @endif
            <h3 class="font-display text-[18px] leading-[1.25] text-ink">{{ $feature->name }}</h3>
        </div>
        @if ($feature->description)
            <p class="mt-2 max-w-[52ch] text-[14px] leading-[1.55] text-slate">{{ $feature->description }}</p>
        @endif
        @if ($feature->quantity > 1)
            <p class="mt-2 text-[11.5px] uppercase tracking-[0.14em] text-slate-soft">
                Quantity <span class="font-mono text-ink tnum">× {{ $feature->quantity }}</span>
            </p>
        @endif
    </div>

    {{-- Right: price + toggle / marker --}}
    <div class="flex flex-col items-end gap-3">
        <div class="flex items-baseline gap-1 font-mono leading-none tnum">
            <span class="text-[14px] text-slate-soft">£</span>
            <span class="text-[22px] text-ink">{{ number_format($lineTotal, 0) }}</span>
        </div>

        @if ($isOptional)
            <button type="button"
                    x-on:click="toggle({{ $feature->id }})"
                    x-bind:class="isOn({{ $feature->id }}) ? 'bg-fox border-fox-deep' : 'bg-white border-rule'"
                    class="relative h-7 w-[52px] rounded-full border transition-colors duration-200"
                    x-bind:aria-pressed="isOn({{ $feature->id }})"
                    aria-label="Toggle {{ $feature->name }}">
                <span class="absolute top-[2px] left-[2px] size-[22px] rounded-full bg-ink shadow-sm transition-transform duration-200"
                      x-bind:style="isOn({{ $feature->id }}) ? 'transform: translateX(24px)' : 'transform: translateX(0)'"></span>
            </button>
            <p class="text-[10.5px] font-medium uppercase tracking-[0.2em]"
               x-bind:class="isOn({{ $feature->id }}) ? 'text-ink' : 'text-slate-soft'">
                <span x-text="isOn({{ $feature->id }}) ? 'Included' : 'Excluded'"></span>
                <span class="text-slate-soft"> · Optional</span>
            </p>
        @else
            <div class="flex items-center gap-1.5 text-[10.5px] font-medium uppercase tracking-[0.2em] text-slate">
                <x-phosphor-lock-simple class="size-3 text-slate-soft" />
                Included
            </div>
        @endif
    </div>
</div>

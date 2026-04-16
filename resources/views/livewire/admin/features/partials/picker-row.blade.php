@php
    $isDisabled = in_array($feature->id, $disabledIds, true);
@endphp
<button type="button"
        wire:key="pick-{{ $feature->id }}"
        wire:click="pick({{ $feature->id }})"
        @disabled($isDisabled)
        @class([
            'group flex items-center justify-between gap-3 px-5 py-3 text-left transition-colors',
            'pl-10' => $isChild,
            'hover:bg-paper-2' => ! $isDisabled,
            'bg-paper-2 cursor-default' => $isDisabled,
        ])>
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            @if ($isChild)
                <x-phosphor-arrow-elbow-down-right class="size-3.5 shrink-0 text-slate-soft" />
            @endif
            <span class="truncate text-[13.5px] font-medium text-ink">{{ $feature->name }}</span>
            @if ($feature->optional)
                <span class="shrink-0 rounded-full bg-fox-soft px-1.5 py-0.5 text-[9.5px] font-medium uppercase tracking-wider text-ink">Opt</span>
            @endif
            @if (! $isChild && $feature->children->count())
                <span class="shrink-0 rounded-full border border-rule bg-white px-1.5 py-0.5 text-[9.5px] font-medium uppercase tracking-wider text-slate">
                    +{{ $feature->children->count() }}
                </span>
            @endif
        </div>
        <div class="mt-0.5 text-xs text-slate">
            <span class="font-mono tnum">£{{ number_format($feature->price, 2) }}</span>
            <span class="text-slate-soft">·</span>
            <span>qty {{ $feature->quantity }}</span>
            @if ($showParentHint && $feature->parent_id)
                <span class="text-slate-soft">·</span>
                <span>under {{ $feature->parent?->name }}</span>
            @endif
        </div>
    </div>
    @if ($isDisabled)
        <x-phosphor-check class="size-4 text-status-accepted-dot" />
    @else
        <x-phosphor-arrow-right class="size-4 text-slate-soft transition-transform group-hover:translate-x-0.5 group-hover:text-ink" />
    @endif
</button>

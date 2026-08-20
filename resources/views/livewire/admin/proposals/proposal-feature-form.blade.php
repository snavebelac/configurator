@php
    $cellInput = 'w-full rounded-md border border-transparent bg-transparent px-2 py-1 text-[13.5px] text-ink hover:border-rule focus:border-ink focus:bg-paper-2 focus:outline-none transition-colors';
    $numberInput = $cellInput.' text-right font-mono text-[13px] tabular-nums';
@endphp
<div @class([
    'group grid items-center gap-3 px-4 py-2.5 transition-colors',
    'bg-paper-2/40 hover:bg-paper-2' => $isChild,
    'hover:bg-paper-2/60' => ! $isChild,
]) style="{{ $gridTemplate }}">

    {{-- Drag handle. Only fixed parent rows reorder: a percentage is a share
         of everything above it and a recurring charge isn't part of the
         one-off document at all, so neither has a position to move. --}}
    <div class="flex justify-center">
        @if (! $isChild && ! $this->isPercentage() && ! $this->isRecurring())
            <button type="button"
                    x-sort:handle
                    class="cursor-grab rounded p-1 text-slate-soft transition-colors hover:text-ink active:cursor-grabbing"
                    title="Drag to reorder"
                    aria-label="Drag to reorder">
                <x-phosphor-dots-six-vertical class="size-3.5" />
            </button>
        @endif
    </div>

    {{-- Name --}}
    <div @class(['flex min-w-0 items-center gap-2', 'pl-6' => $isChild])>
        @if ($isChild)
            <x-phosphor-arrow-elbow-down-right class="size-3.5 shrink-0 text-slate-soft" />
        @endif
        <input type="text"
               wire:model.blur="name"
               class="{{ $cellInput }} font-medium">
    </div>

    @if ($this->isPercentage())
        {{-- Rate --}}
        <div class="flex items-center justify-end gap-1">
            <input type="number"
                   min="0"
                   max="100"
                   step="0.01"
                   wire:model.blur="percentage"
                   class="{{ $numberInput }}">
            <span class="text-[13px] text-slate-soft">%</span>
        </div>
    @else
        {{-- Qty --}}
        <div class="text-right">
            <input type="number"
                   min="1"
                   step="1"
                   wire:model.blur="quantity"
                   class="{{ $numberInput }}">
        </div>

        {{-- Unit price --}}
        <div class="flex items-center justify-end gap-1">
            <span class="text-[13px] text-slate-soft">£</span>
            <input type="number"
                   min="0"
                   step="0.01"
                   wire:model.blur="price"
                   class="{{ $numberInput }}">
        </div>
    @endif

    @if ($this->isRecurring())
        {{-- Billing period --}}
        <div>
            <select wire:model.live="billingPeriod"
                    class="w-full rounded-md border border-transparent bg-transparent px-2 py-1 text-[13px] text-ink hover:border-rule focus:border-ink focus:bg-paper-2 focus:outline-none transition-colors">
                @foreach ($billingPeriods as $period)
                    <option value="{{ $period->value }}">{{ $period->label() }}</option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Required / optional --}}
    <div>
        <label class="inline-flex cursor-pointer items-center gap-2">
            <input type="checkbox"
                   wire:model.live="optional"
                   class="size-3.5 rounded border-rule bg-paper-2 accent-ink focus:ring-1 focus:ring-ink focus:ring-offset-0">
            <span @class([
                'text-[11px] font-medium uppercase tracking-wider',
                'text-ink'   => $optional,
                'text-slate-soft' => ! $optional,
            ])>
                {{ $optional ? 'Optional' : 'Required' }}
            </span>
        </label>
    </div>

    {{-- Line total --}}
    <div class="text-right font-mono text-[13px] text-ink tabular-nums">
        @if ($this->isPercentage())
            {{-- Of the fixed lines with every optional counted in, matching the
                 running total at the foot of the card. --}}
            £{{ number_format($this->percentageAmount(), 2) }}
        @elseif ($this->isRecurring())
            £{{ number_format((float) $price * (int) $quantity, 2) }}<span class="text-slate-soft">{{ $this->finalFeature->billingSuffix() }}</span>
        @else
            £{{ number_format((float) $price * (int) $quantity, 2) }}
        @endif
    </div>

    {{-- Remove --}}
    <div class="flex justify-end">
        <button type="button"
                wire:click="removeFinalFeature"
                wire:confirm="Remove {{ $name }} from this proposal?{{ $isChild ? '' : ' Any child features will be removed with it.' }}"
                class="rounded-md p-1.5 text-slate-soft opacity-0 transition-all hover:bg-status-rejected-bg hover:text-status-rejected-fg group-hover:opacity-100"
                aria-label="Remove {{ $name }}">
            <x-phosphor-x class="size-3.5" />
        </button>
    </div>
</div>

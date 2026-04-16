<div @class([
    'group grid items-center gap-3 px-4 py-2.5 transition-colors',
    'bg-paper-2/40 hover:bg-paper-2' => $isChild,
    'hover:bg-paper-2/60' => ! $isChild,
]) style="{{ $gridTemplate }}">

    {{-- Drag handle (parent rows only) --}}
    <div class="flex justify-center">
        @unless ($isChild)
            <button type="button"
                    x-sort:handle
                    class="cursor-grab rounded p-1 text-slate-soft transition-colors hover:text-ink active:cursor-grabbing"
                    title="Drag to reorder"
                    aria-label="Drag to reorder">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="6" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="18" r="1"/></svg>
            </button>
        @endunless
    </div>

    {{-- Name --}}
    <div @class(['flex min-w-0 items-center gap-2', 'pl-6' => $isChild])>
        @if ($isChild)
            <svg class="size-3.5 shrink-0 text-slate-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4v10a3 3 0 0 0 3 3h9"/><path d="m16 13 3 4-3 4"/></svg>
        @endif
        <input type="text"
               wire:model.blur="name"
               class="w-full rounded-md border border-transparent bg-transparent px-2 py-1 text-[13.5px] font-medium text-ink hover:border-rule focus:border-ink focus:bg-paper-2 focus:outline-none transition-colors">
    </div>

    {{-- Qty --}}
    <div class="text-right">
        <input type="number"
               min="1"
               step="1"
               wire:model.blur="quantity"
               class="w-full rounded-md border border-transparent bg-transparent px-2 py-1 text-right font-mono text-[13px] text-ink tabular-nums hover:border-rule focus:border-ink focus:bg-paper-2 focus:outline-none transition-colors">
    </div>

    {{-- Unit price --}}
    <div class="flex items-center justify-end gap-1">
        <span class="text-slate-soft text-[13px]">£</span>
        <input type="number"
               min="0"
               step="0.01"
               wire:model.blur="price"
               class="w-full rounded-md border border-transparent bg-transparent px-2 py-1 text-right font-mono text-[13px] text-ink tabular-nums hover:border-rule focus:border-ink focus:bg-paper-2 focus:outline-none transition-colors">
    </div>

    {{-- Type (required / optional) --}}
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
        £{{ number_format((float) $price * (int) $quantity, 2) }}
    </div>

    {{-- Remove --}}
    <div class="flex justify-end">
        <button type="button"
                wire:click="removeFinalFeature"
                wire:confirm="Remove {{ $name }} from this proposal?{{ $isChild ? '' : ' Any child features will be removed with it.' }}"
                class="rounded-md p-1.5 text-slate-soft opacity-0 transition-all hover:bg-status-rejected-bg hover:text-status-rejected-fg group-hover:opacity-100"
                aria-label="Remove {{ $name }}">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M6 18 18 6"/></svg>
        </button>
    </div>
</div>

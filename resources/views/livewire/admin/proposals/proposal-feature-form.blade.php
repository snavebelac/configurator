<tr class="group last:[&>td]:border-b-0">
    <td class="border-b border-rule-soft px-4 py-3 align-middle">
        <input type="text"
               wire:model.blur="name"
               class="w-full rounded-md border border-transparent bg-transparent px-2 py-1 text-[13.5px] font-medium text-ink hover:border-rule focus:border-ink focus:bg-paper-2 focus:outline-none transition-colors">
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle">
        <input type="number"
               min="1"
               step="1"
               wire:model.blur="quantity"
               class="w-20 rounded-md border border-transparent bg-transparent px-2 py-1 text-right font-mono text-[13px] text-ink tabular-nums hover:border-rule focus:border-ink focus:bg-paper-2 focus:outline-none transition-colors">
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle">
        <div class="inline-flex items-center gap-1">
            <span class="text-slate-soft text-[13px]">£</span>
            <input type="number"
                   min="0"
                   step="0.01"
                   wire:model.blur="price"
                   class="w-24 rounded-md border border-transparent bg-transparent px-2 py-1 text-right font-mono text-[13px] text-ink tabular-nums hover:border-rule focus:border-ink focus:bg-paper-2 focus:outline-none transition-colors">
        </div>
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle">
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
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle font-mono text-[13px] text-ink tabular-nums">
        £{{ number_format((float) $price * (int) $quantity, 2) }}
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle text-right">
        <button type="button"
                wire:click="removeFinalFeature"
                wire:confirm="Remove {{ $name }} from this proposal?"
                class="rounded-md p-1.5 text-slate-soft opacity-0 transition-all hover:bg-status-rejected-bg hover:text-status-rejected-fg group-hover:opacity-100"
                aria-label="Remove {{ $name }}">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M6 18 18 6"/></svg>
        </button>
    </td>
</tr>

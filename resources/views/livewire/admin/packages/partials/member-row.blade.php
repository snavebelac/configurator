@php
    $pivot = $feature->pivot;
    $defaultQty = $feature->quantity;
    $defaultPrice = $feature->price;
    $defaultOptional = (bool) $feature->optional;

    $optionalValue = 'inherit';
    if ($pivot->optional === true) {
        $optionalValue = 'optional';
    } elseif ($pivot->optional === false) {
        $optionalValue = 'required';
    }

    $qtyValue = $pivot->quantity;
    $priceValue = $pivot->price; // already run through accessor: null or float
@endphp
<tr wire:key="member-{{ $feature->id }}" class="group last:[&>td]:border-b-0">
    <td class="border-b border-rule-soft px-4 py-3 align-middle text-[13.5px] text-ink">
        <div class="font-medium">{{ $feature->name }}</div>
        <div class="mt-0.5 text-xs text-slate-soft">
            Default: <span class="font-mono tnum">£{{ number_format($defaultPrice, 2) }}</span>
            <span class="text-slate-soft">·</span>
            qty {{ $defaultQty }}
            <span class="text-slate-soft">·</span>
            {{ $defaultOptional ? 'Optional' : 'Required' }}
        </div>
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle">
        <input type="number"
               min="1"
               step="1"
               value="{{ $qtyValue }}"
               placeholder="{{ $defaultQty }}"
               wire:change="updatePivot({{ $feature->id }}, 'quantity', $event.target.value)"
               class="w-20 rounded-md border border-rule bg-paper-2 px-2 py-1 text-right font-mono text-[13px] text-ink tabular-nums focus:border-ink focus:bg-white focus:outline-none transition-colors">
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle">
        <div class="inline-flex items-center gap-1">
            <span class="text-slate-soft text-[13px]">£</span>
            <input type="number"
                   min="0"
                   step="0.01"
                   value="{{ $priceValue !== null ? number_format($priceValue, 2, '.', '') : '' }}"
                   placeholder="{{ number_format($defaultPrice, 2) }}"
                   wire:change="updatePivot({{ $feature->id }}, 'price', $event.target.value)"
                   class="w-24 rounded-md border border-rule bg-paper-2 px-2 py-1 text-right font-mono text-[13px] text-ink tabular-nums focus:border-ink focus:bg-white focus:outline-none transition-colors">
        </div>
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle">
        <select wire:change="updatePivot({{ $feature->id }}, 'optional', $event.target.value)"
                class="rounded-md border border-rule bg-paper-2 px-2 py-1 text-[12.5px] text-ink focus:border-ink focus:bg-white focus:outline-none transition-colors">
            <option value="inherit" @selected($optionalValue === 'inherit')>Inherit ({{ $defaultOptional ? 'Optional' : 'Required' }})</option>
            <option value="required" @selected($optionalValue === 'required')>Force required</option>
            <option value="optional" @selected($optionalValue === 'optional')>Force optional</option>
        </select>
    </td>
    <td class="border-b border-rule-soft px-4 py-3 align-middle text-right">
        <button type="button"
                wire:click="removeFeature({{ $feature->id }})"
                class="rounded-md p-1.5 text-slate-soft opacity-0 transition-all hover:bg-status-rejected-bg hover:text-status-rejected-fg group-hover:opacity-100"
                aria-label="Remove {{ $feature->name }} from package">
            <x-phosphor-x class="size-3.5" />
        </button>
    </td>
</tr>

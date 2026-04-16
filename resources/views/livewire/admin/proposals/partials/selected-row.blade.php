<tr wire:key="sel-{{ $feature->id }}" @class([
    'group last:[&>td]:border-b-0',
    'bg-paper-2/40' => $isChild,
])>
    <td @class([
        'border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink',
        'pl-10' => $isChild,
    ])>
        <div class="flex items-center gap-2">
            @if ($isChild)
                <x-phosphor-arrow-elbow-down-right class="size-3.5 shrink-0 text-slate-soft" />
            @endif
            <span class="font-medium">{{ $feature->name }}</span>
            @if ($feature->optional)
                <span class="rounded-full bg-fox-soft px-1.5 py-0.5 text-[9.5px] font-medium uppercase tracking-wider text-ink">Optional</span>
            @endif
        </div>
    </td>
    <td class="border-b border-rule-soft px-4 py-3.5 align-middle font-mono text-[13px] text-ink tnum">{{ $feature->quantity }}</td>
    <td class="border-b border-rule-soft px-4 py-3.5 align-middle"><x-money :value="$feature->price" size="mono" :precise="true" /></td>
    <td class="border-b border-rule-soft px-4 py-3.5 align-middle"><x-money :value="$feature->price * $feature->quantity" size="mono" :precise="true" /></td>
    <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-right">
        <button type="button"
                wire:click="removeFeature({{ $feature->id }})"
                class="rounded-md p-1.5 text-slate-soft opacity-0 transition-all hover:bg-status-rejected-bg hover:text-status-rejected-fg group-hover:opacity-100"
                aria-label="Remove {{ $feature->name }}">
            <x-phosphor-x class="size-3.5" />
        </button>
    </td>
</tr>

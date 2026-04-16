@php
    $isChild = $depth > 0;
    $parentLabel = $searching && $feature->parent_id ? $feature->parent?->name : null;
@endphp
<tr wire:key="feature-{{ $feature->id }}" @class([
    'group transition-colors last:[&>td]:border-b-0',
    'hover:bg-paper-2' => ! $isChild,
    'bg-paper-2/50 hover:bg-paper-2' => $isChild,
])>
    <td @class([
        'border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink',
        'pl-10' => $isChild,
    ])>
        <div class="flex items-start gap-2">
            @if ($isChild)
                <svg class="mt-1 size-3.5 shrink-0 text-slate-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4v10a3 3 0 0 0 3 3h9"/><path d="m16 13 3 4-3 4"/></svg>
            @endif
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-medium">{{ $feature->name }}</span>
                    @if (! $isChild && $feature->children->count())
                        <span class="rounded-full border border-rule bg-white px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wider text-slate">
                            {{ $feature->children->count() }} {{ Str::plural('child', $feature->children->count()) }}
                        </span>
                    @endif
                </div>
                @if ($feature->description)
                    <div class="mt-0.5 line-clamp-1 text-xs text-slate">{{ $feature->description }}</div>
                @endif
                @if ($parentLabel)
                    <div class="mt-0.5 text-[11px] uppercase tracking-wider text-slate-soft">Under {{ $parentLabel }}</div>
                @endif
            </div>
        </div>
    </td>
    <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
        @if ($feature->optional)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-fox-soft px-2 py-0.5 text-[11px] font-medium uppercase tracking-wider leading-5 text-ink">
                <span class="size-1.5 rounded-full bg-fox-deep"></span>
                Optional
            </span>
        @else
            <span class="text-xs text-slate-soft">Required</span>
        @endif
    </td>
    <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
        <x-money :value="$feature->price" size="mono" :precise="true" />
    </td>
    <td class="border-b border-rule-soft px-4 py-3.5 align-middle font-mono text-[13px] text-ink tnum">
        {{ $feature->quantity }}
    </td>
    <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
        <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
            <x-btn variant="row" wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal', arguments: {featureId: {{ $feature->id }} }})">
                Edit
            </x-btn>
            <x-btn variant="row" class="text-status-rejected-fg hover:bg-status-rejected-bg"
                   wire:click="delete({{ $feature->id }})"
                   wire:confirm="Are you sure you wish to delete [{{ $feature->name }}]?">
                Delete
            </x-btn>
        </div>
    </td>
</tr>

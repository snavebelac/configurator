<div>
    <div class="border-b border-rule-soft px-5 py-3">
        <div class="relative flex items-center">
            <svg class="pointer-events-none absolute left-3 size-3.5 text-slate-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
            <input type="text"
                   wire:model.live.debounce.250ms="search"
                   placeholder="Filter by name…"
                   class="w-full rounded-lg border border-rule bg-paper-2 py-[7px] pl-8 pr-3 text-[13px] text-ink placeholder:text-slate-soft focus:border-ink focus:outline-none focus:bg-white transition-colors">
            @if ($search !== '')
                <button type="button" wire:click="$set('search', '')" class="absolute right-2 rounded p-1 text-slate-soft hover:text-ink" aria-label="Clear search">
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M6 18 18 6"/></svg>
                </button>
            @endif
        </div>
    </div>

    <div class="flex flex-col divide-y divide-rule-soft">
        @forelse ($features as $feature)
            @include('livewire.admin.features.partials.picker-row', [
                'feature' => $feature,
                'isChild' => $searching ? (bool) $feature->parent_id : false,
                'disabledIds' => $disabledIds,
                'showParentHint' => $searching,
            ])

            @if (! $searching)
                @foreach ($feature->children as $child)
                    @include('livewire.admin.features.partials.picker-row', [
                        'feature' => $child,
                        'isChild' => true,
                        'disabledIds' => $disabledIds,
                        'showParentHint' => false,
                    ])
                @endforeach
            @endif
        @empty
            <div class="px-5 py-10 text-center text-sm text-slate">
                @if ($searching)
                    No features match "{{ $search }}".
                @else
                    The feature library is empty. Add features first.
                @endif
            </div>
        @endforelse
    </div>

    @if ($features->hasPages())
        <div class="border-t border-rule-soft px-5 py-3">
            {{ $features->links() }}
        </div>
    @endif
</div>

@php
    $intro = $total === 0
        ? 'Your feature library is empty. Add your first feature — once it\'s here you can pull it into any proposal.'
        : 'The building blocks you drag into proposals. Mark any one as optional to let the client toggle it at presentation time.';
@endphp
<div class="mx-auto max-w-[1480px]">

    <x-page-header
        title="Features."
        :eyebrow="$total . ' ' . Str::plural('feature', $total) . ' · ' . $optionalCount . ' optional'"
        :lede="$intro">
        <x-slot:actions>
            <x-btn variant="accent" wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Add feature
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-rule-soft px-5 py-3.5">
            <span class="text-xs text-slate">{{ $features->total() }} {{ Str::plural('result', $features->total()) }}</span>

            <div class="relative flex items-center">
                <svg class="pointer-events-none absolute left-3 size-3.5 text-slate-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                <input type="text"
                       wire:model.live.debounce.250ms="search"
                       placeholder="Filter by name…"
                       class="w-64 rounded-lg border border-rule bg-paper-2 py-[7px] pl-8 pr-3 text-[13px] text-ink placeholder:text-slate-soft focus:border-ink focus:outline-none focus:bg-white transition-colors">
                @if ($search !== '')
                    <button type="button" wire:click="$set('search', '')" class="absolute right-2 rounded p-1 text-slate-soft hover:text-ink" aria-label="Clear search">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M6 18 18 6"/></svg>
                    </button>
                @endif
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr>
                    <x-th style="width:44%">Feature</x-th>
                    <x-th>Optional</x-th>
                    <x-th>Price</x-th>
                    <x-th>Qty</x-th>
                    <x-th></x-th>
                </tr>
            </thead>
            <tbody>
                @forelse ($features as $feature)
                    <tr wire:key="feature-{{ $feature->id }}" class="group transition-colors hover:bg-paper-2 last:[&>td]:border-b-0">
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            <div class="font-medium">{{ $feature->name }}</div>
                            @if ($feature->description)
                                <div class="mt-0.5 line-clamp-1 text-xs text-slate">{{ $feature->description }}</div>
                            @endif
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
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-14 text-center text-sm text-slate">
                            @if ($search !== '')
                                No features match "{{ $search }}".
                            @else
                                No features yet — add your first one.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($features->hasPages())
            <div class="border-t border-rule-soft px-5 py-3">
                {{ $features->links() }}
            </div>
        @endif
    </x-card>

</div>

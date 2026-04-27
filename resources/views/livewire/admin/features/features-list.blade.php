@php
    $intro = $total === 0
        ? 'Your feature library is empty. Add your first feature — once it\'s here you can pull it into any proposal.'
        : 'The building blocks you drag into proposals. Group related add-ons under a parent feature to let clients see their total change as they toggle them on.';
    $eyebrow = $total.' '.Str::plural('feature', $total)
        .' · '.$optionalCount.' optional'
        .($parentCount > 0 ? ' · '.$parentCount.' with children' : '');
@endphp
<div class="max-w-[1480px]">

    <x-page-header
        title="Features."
        :eyebrow="$eyebrow"
        :lede="$intro">
        <x-slot:actions>
            <x-btn variant="accent" wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})">
                <x-phosphor-plus class="size-3.5" />
                Add feature
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-rule-soft px-5 py-3.5">
            <span class="text-xs text-slate">{{ $features->total() }} {{ Str::plural('result', $features->total()) }}</span>

            <div class="relative flex items-center">
                <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 size-3.5 text-slate-soft" />
                <input type="text"
                       wire:model.live.debounce.250ms="search"
                       placeholder="Filter by name…"
                       class="w-64 rounded-lg border border-rule bg-paper-2 py-[7px] pl-8 pr-3 text-[13px] text-ink placeholder:text-slate-soft focus:border-ink focus:outline-none focus:bg-white transition-colors">
                @if ($search !== '')
                    <button type="button" wire:click="$set('search', '')" class="absolute right-2 rounded p-1 text-slate-soft hover:text-ink" aria-label="Clear search">
                        <x-phosphor-x class="size-3" />
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
                    @include('livewire.admin.features.partials.row', [
                        'feature' => $feature,
                        'depth' => 0,
                        'searching' => $searching,
                    ])

                    @if (! $searching)
                        @foreach ($feature->children as $child)
                            @include('livewire.admin.features.partials.row', [
                                'feature' => $child,
                                'depth' => 1,
                                'searching' => $searching,
                            ])
                        @endforeach
                    @endif
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

@php
    $clientOptions = $clients->mapWithKeys(fn ($client) => [$client->id => $client->name])->all();
    $selectedCount = count($selectedFeatureIds);
@endphp
<div class="mx-auto max-w-[1480px]">

    <x-page-header
        title="New proposal."
        eyebrow="Step 1 · Pick features"
        lede="Assemble the quote from your feature library. You'll fine-tune quantities and pricing on the next step.">
        <x-slot:actions>
            <x-btn variant="ghost" :href="route('dashboard.proposals')">Cancel</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form wire:submit.prevent="createProposal">

        {{-- Proposal meta (client + name) --}}
        <x-card class="mb-6">
            <div class="grid grid-cols-1 gap-6 px-8 py-7 sm:grid-cols-5">
                <x-select-field
                    label="Client"
                    name="clientId"
                    :options="$clientOptions"
                    placeholder="Choose a client…"
                    class="sm:col-span-2" />

                <x-field
                    label="Proposal name"
                    name="name"
                    placeholder="E.g. Zocalo market refresh — stage one"
                    class="sm:col-span-3" />
            </div>
        </x-card>

        {{-- Two-pane feature picker --}}
        <div class="grid grid-cols-[1fr_1.6fr] gap-5">

            {{-- Library --}}
            <x-card>
                <x-card-header>
                    <div class="flex items-baseline gap-3">
                        <h3 class="font-display text-[18px] text-ink">Feature library</h3>
                        <span class="text-xs text-slate">{{ $features->total() }} available</span>
                    </div>
                    <button type="button"
                            wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})"
                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium text-slate hover:bg-paper-2 hover:text-ink">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        New feature
                    </button>
                </x-card-header>

                <div class="border-b border-rule-soft px-5 py-3">
                    <div class="relative flex items-center">
                        <svg class="pointer-events-none absolute left-3 size-3.5 text-slate-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                        <input type="text"
                               wire:model.live.debounce.250ms="featureSearch"
                               placeholder="Filter by name…"
                               class="w-full rounded-lg border border-rule bg-paper-2 py-[7px] pl-8 pr-3 text-[13px] text-ink placeholder:text-slate-soft focus:border-ink focus:outline-none focus:bg-white transition-colors">
                    </div>
                </div>

                <div class="flex flex-col divide-y divide-rule-soft">
                    @forelse ($features as $feature)
                        @php $isSelected = in_array($feature->id, $selectedFeatureIds, true); @endphp
                        <button type="button"
                                wire:key="lib-{{ $feature->id }}"
                                wire:click="selectFeature({{ $feature->id }})"
                                @disabled($isSelected)
                                @class([
                                    'group flex items-center justify-between gap-3 px-5 py-3 text-left transition-colors',
                                    'hover:bg-paper-2'         => ! $isSelected,
                                    'bg-paper-2 cursor-default' => $isSelected,
                                ])>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="truncate text-[13.5px] font-medium text-ink">{{ $feature->name }}</span>
                                    @if ($feature->optional)
                                        <span class="shrink-0 rounded-full bg-fox-soft px-1.5 py-0.5 text-[9.5px] font-medium uppercase tracking-wider text-ink">Opt</span>
                                    @endif
                                </div>
                                <div class="mt-0.5 text-xs text-slate">
                                    <span class="font-mono tnum">£{{ number_format($feature->price, 2) }}</span>
                                    <span class="text-slate-soft">·</span>
                                    <span>qty {{ $feature->quantity }}</span>
                                </div>
                            </div>
                            @if ($isSelected)
                                <svg class="size-4 text-status-accepted-dot" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-11"/></svg>
                            @else
                                <svg class="size-4 text-slate-soft transition-transform group-hover:translate-x-0.5 group-hover:text-ink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            @endif
                        </button>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate">No features match "{{ $featureSearch }}".</div>
                    @endforelse
                </div>

                @if ($features->hasPages())
                    <div class="border-t border-rule-soft px-5 py-3">
                        {{ $features->links() }}
                    </div>
                @endif
            </x-card>

            {{-- Selected features --}}
            <x-card>
                <x-card-header>
                    <div class="flex items-baseline gap-3">
                        <h3 class="font-display text-[18px] text-ink">Selected features</h3>
                        <span class="text-xs text-slate">{{ $selectedCount }} {{ Str::plural('item', $selectedCount) }}</span>
                    </div>
                    <div class="flex items-baseline gap-2 text-slate">
                        <span class="text-[11px] font-medium uppercase tracking-[0.12em]">Running total</span>
                        <x-money :value="$selectedTotal" size="row" :precise="true" />
                    </div>
                </x-card-header>

                <table class="w-full">
                    <thead>
                        <tr>
                            <x-th style="width:50%">Feature</x-th>
                            <x-th>Qty</x-th>
                            <x-th>Unit</x-th>
                            <x-th>Line total</x-th>
                            <x-th></x-th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($selectedFeatures as $feature)
                            <tr wire:key="sel-{{ $feature->id }}" class="group last:[&>td]:border-b-0">
                                <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                                    <div class="flex items-center gap-2">
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
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M6 18 18 6"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-16 text-center text-sm text-slate">
                                    <div class="font-display text-[18px] text-ink">Nothing selected yet</div>
                                    <p class="mt-1.5 text-slate">Pick features from the library on the left.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @error('selectedFeatureIds')
                    <div class="border-t border-rule-soft bg-status-rejected-bg px-5 py-2.5 text-[12.5px] text-status-rejected-fg">{{ $message }}</div>
                @enderror

                <div class="flex items-center justify-between gap-3 border-t border-rule-soft bg-paper-2 px-5 py-4">
                    <span class="text-[12.5px] text-slate">You can customise quantities and pricing on the next step.</span>
                    <x-btn variant="accent" type="submit">
                        Create proposal
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </x-btn>
                </div>
            </x-card>

        </div>
    </form>
</div>

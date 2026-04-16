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

            {{-- Library (reusable FeaturePicker component) --}}
            <x-card>
                <x-card-header>
                    <div class="flex items-baseline gap-3">
                        <h3 class="font-display text-[18px] text-ink">Feature library</h3>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button"
                                wire:click="$dispatch('openModal', {component: 'admin.proposals.package-picker-modal'})"
                                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium text-slate hover:bg-paper-2 hover:text-ink">
                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5z"/><path d="M3.5 7.5 12 12l8.5-4.5"/><path d="M12 12v9"/></svg>
                            Add package
                        </button>
                        <button type="button"
                                wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})"
                                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium text-slate hover:bg-paper-2 hover:text-ink">
                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                            New feature
                        </button>
                    </div>
                </x-card-header>

                <livewire:admin.features.feature-picker
                    :disabled-ids="$selectedFeatureIds"
                    :key="'proposal-create-picker'" />
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
                        @forelse ($selectedGroups as $group)
                            @include('livewire.admin.proposals.partials.selected-row', [
                                'feature' => $group['root'],
                                'isChild' => false,
                            ])
                            @foreach ($group['children'] as $child)
                                @include('livewire.admin.proposals.partials.selected-row', [
                                    'feature' => $child,
                                    'isChild' => true,
                                ])
                            @endforeach
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

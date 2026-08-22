@php
    $memberCount = $features->count();
@endphp
<div class="max-w-[1480px]" wire:key="package-edit-{{ $package->id }}">

    <x-page-header
        :title="$package->name ?: 'Untitled package'"
        :eyebrow="$memberCount . ' ' . Str::plural('feature', $memberCount)">
        <x-slot:actions>
            <x-btn variant="ghost" :href="route('dashboard.packages')">Back to list</x-btn>
        </x-slot:actions>
    </x-page-header>

    {{-- Package meta --}}
    <x-card class="mb-6">
        <div class="grid gap-6 px-8 py-7 sm:grid-cols-[1fr_2fr]">
            <x-field
                label="Name"
                name="name"
                placeholder="Standard brochure website" />
            <x-field
                label="Description"
                name="description"
                placeholder="What this bundle typically covers." />
        </div>
    </x-card>

    {{-- Two-pane: library + members --}}
    <div class="grid grid-cols-[1fr_1.6fr] gap-5">

        {{-- Library --}}
        <x-card>
            <x-card-header>
                <div class="flex items-baseline gap-3">
                    <h3 class="font-display text-[18px] text-ink">Feature library</h3>
                </div>
                <button type="button"
                        wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})"
                        class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium text-slate hover:bg-paper-2 hover:text-ink">
                    <x-phosphor-plus class="size-3" />
                    New feature
                </button>
            </x-card-header>

            <livewire:admin.features.feature-picker
                :disabled-ids="$disabledIds"
                :key="'package-edit-picker-'.$package->id" />
        </x-card>

        {{-- Members --}}
        <x-card>
            <x-card-header>
                <div class="flex items-baseline gap-3">
                    <h3 class="font-display text-[18px] text-ink">Package features</h3>
                    <span class="text-xs text-slate">
                        {{ $memberCount }} {{ Str::plural('feature', $memberCount) }}
                    </span>
                </div>
                <span class="text-[11px] text-slate">
                    Override quantity, price, or required/optional on a per-package basis — or leave blank to inherit from the feature library.
                </span>
            </x-card-header>

            @if ($memberCount === 0)
                <div class="px-4 py-16 text-center text-sm text-slate">
                    <div class="font-display text-[18px] text-ink">No features yet</div>
                    <p class="mt-1.5 text-slate">Pick features from the library on the left to build this package.</p>
                </div>
            @else
                <table class="w-full">
                    <thead>
                        <tr>
                            <x-th style="width:40%">Feature</x-th>
                            <x-th>Qty override</x-th>
                            <x-th>Price override</x-th>
                            <x-th>Required / optional</x-th>
                            <x-th></x-th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($features as $feature)
                            @include('livewire.admin.packages.partials.member-row', ['feature' => $feature])
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-card>
    </div>
</div>

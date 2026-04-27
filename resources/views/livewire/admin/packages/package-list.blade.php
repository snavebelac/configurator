@php
    $intro = $total === 0
        ? 'Packages are pre-curated sets of features you can drop onto a proposal in one go. Create one to get started.'
        : 'Preset bundles of features. Drop any package onto a proposal to add all of its features at once.';
@endphp
<div class="max-w-[1480px]">

    <x-page-header
        title="Packages."
        :eyebrow="$total . ' ' . Str::plural('package', $total)"
        :lede="$intro">
        <x-slot:actions>
            <x-btn variant="accent" :href="route('dashboard.package.create')">
                <x-phosphor-plus class="size-3.5" />
                New package
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-rule-soft px-5 py-3.5">
            <span class="text-xs text-slate">{{ $packages->total() }} {{ Str::plural('result', $packages->total()) }}</span>

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
                    <x-th style="width:55%">Package</x-th>
                    <x-th>Features</x-th>
                    <x-th></x-th>
                </tr>
            </thead>
            <tbody>
                @forelse ($packages as $package)
                    <tr wire:key="package-{{ $package->id }}" class="group transition-colors hover:bg-paper-2 last:[&>td]:border-b-0">
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            <div class="font-medium">{{ $package->name }}</div>
                            @if ($package->description)
                                <div class="mt-0.5 line-clamp-1 text-xs text-slate">{{ $package->description }}</div>
                            @endif
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <span class="font-mono text-[13px] text-ink tnum">{{ $package->features_count }}</span>
                            <span class="ml-0.5 text-xs text-slate-soft">{{ Str::plural('feature', $package->features_count) }}</span>
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
                                <x-btn variant="row" :href="route('dashboard.package.edit', ['package' => $package->id])">
                                    Edit
                                </x-btn>
                                <x-btn variant="row" class="text-status-rejected-fg hover:bg-status-rejected-bg"
                                       wire:click="delete({{ $package->id }})"
                                       wire:confirm="Delete the package [{{ $package->name }}]? Existing proposals won't be affected.">
                                    Delete
                                </x-btn>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-14 text-center text-sm text-slate">
                            @if ($search !== '')
                                No packages match "{{ $search }}".
                            @else
                                No packages yet — create your first one.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($packages->hasPages())
            <div class="border-t border-rule-soft px-5 py-3">
                {{ $packages->links() }}
            </div>
        @endif
    </x-card>

</div>

<x-modal
    title="Add a package"
    subtitle="Pick a package to drop all of its features onto this proposal at once.">

    <div class="border-b border-rule-soft px-5 py-3">
        <div class="relative flex items-center">
            <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 size-3.5 text-slate-soft" />
            <input type="text"
                   wire:model.live.debounce.250ms="search"
                   placeholder="Filter packages by name…"
                   class="w-full rounded-lg border border-rule bg-paper-2 py-[7px] pl-8 pr-3 text-[13px] text-ink placeholder:text-slate-soft focus:border-ink focus:outline-none focus:bg-white transition-colors">
        </div>
    </div>

    <div class="flex flex-col divide-y divide-rule-soft max-h-[480px] overflow-y-auto">
        @forelse ($packages as $package)
            @php
                $estimatedTotal = $package->features->sum(function ($feature) {
                    $qty = $feature->pivot->quantity ?? $feature->quantity;
                    $price = $feature->pivot->price ?? $feature->price;
                    return $qty * $price;
                });
            @endphp
            <button type="button"
                    wire:key="pkg-{{ $package->id }}"
                    wire:click="addPackage({{ $package->id }})"
                    class="group flex items-center justify-between gap-4 px-5 py-4 text-left transition-colors hover:bg-paper-2">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="truncate text-[14px] font-medium text-ink">{{ $package->name }}</span>
                        <span class="shrink-0 rounded-full border border-rule bg-white px-1.5 py-0.5 text-[9.5px] font-medium uppercase tracking-wider text-slate">
                            {{ $package->features_count }} {{ Str::plural('feature', $package->features_count) }}
                        </span>
                    </div>
                    @if ($package->description)
                        <div class="mt-0.5 line-clamp-1 text-xs text-slate">{{ $package->description }}</div>
                    @endif
                    <div class="mt-1 text-xs text-slate">
                        <span class="font-mono tnum">£{{ number_format($estimatedTotal, 2) }}</span>
                        <span class="text-slate-soft"> · total with overrides applied</span>
                    </div>
                </div>
                <x-phosphor-arrow-right class="size-4 shrink-0 text-slate-soft transition-transform group-hover:translate-x-0.5 group-hover:text-ink" />
            </button>
        @empty
            <div class="px-5 py-10 text-center text-sm text-slate">
                @if ($search !== '')
                    No packages match "{{ $search }}".
                @else
                    <div class="font-display text-[18px] text-ink">No packages yet</div>
                    <p class="mt-1.5">Create a package from the Packages page to start using them here.</p>
                @endif
            </div>
        @endforelse
    </div>

    <div class="flex items-center justify-between gap-2 border-t border-rule-soft bg-paper-2 px-8 py-4">
        <span class="text-[12.5px] text-slate">Duplicates and already-added features are skipped automatically.</span>
        <x-btn variant="ghost" wire:click="$dispatch('closeModal')">Cancel</x-btn>
    </div>
</x-modal>

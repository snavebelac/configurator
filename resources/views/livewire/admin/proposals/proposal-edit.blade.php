<div class="mx-auto max-w-[1480px]" wire:key="proposal-edit-{{ $proposal->id }}">

    <x-page-header
        :title="$proposal->name ?: 'Untitled proposal'"
        :eyebrow="'Editing · ' . ucfirst($proposal->status->value)">
        <x-slot:actions>
            <x-btn variant="ghost" :href="route('dashboard.proposals')">Back to list</x-btn>
            <x-btn variant="ghost" :href="route('dashboard.proposal.preview', ['proposal' => $proposal->uuid])" target="_blank">
                Preview
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/></svg>
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    {{-- Meta strip --}}
    <div class="mb-6 flex flex-wrap items-center gap-x-8 gap-y-2 rounded-2xl border border-rule bg-paper-2 px-6 py-3.5 text-[13px]">
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate">Status</span>
            <x-pill :status="$proposal->status->value" />
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate">Client</span>
            <span class="text-ink">{{ $proposal->client?->name ?? '—' }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate">Owner</span>
            <span class="text-ink">{{ $proposal->user?->full_name ?? '—' }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-medium uppercase tracking-[0.12em] text-slate">Updated</span>
            <span class="text-ink">{{ $proposal->updated_at->diffForHumans() }}</span>
        </div>
    </div>

    <x-card>
        <x-card-header
            title="Features"
            :meta="$proposal->features->count() . ' ' . Str::plural('line', $proposal->features->count())" />

        <table class="w-full">
            <thead>
                <tr>
                    <x-th style="width:40%">Name</x-th>
                    <x-th>Qty</x-th>
                    <x-th>Unit price</x-th>
                    <x-th>Type</x-th>
                    <x-th>Line total</x-th>
                    <x-th></x-th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proposal->features as $feature)
                    <livewire:admin.proposals.proposal-feature-form
                        :final-feature-id="$feature->id"
                        :key="'feature-'.$feature->id" />
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="font-display text-[18px] font-medium text-ink" style="font-variation-settings: 'opsz' 24;">This proposal has no features</div>
                            <p class="mt-1.5 text-sm text-slate">Return to the list and start a new proposal to pick features from your library.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-rule-soft bg-paper-2 px-6 py-4">
            <livewire:admin.proposals.proposal-total-on-the-fly :proposal-id="$proposal->id" />
            <div class="flex items-center gap-2">
                <x-btn variant="ghost" :href="route('dashboard.proposal.preview', ['proposal' => $proposal->uuid])" target="_blank">Preview</x-btn>
                <x-btn variant="accent">Finalise</x-btn>
            </div>
        </div>
    </x-card>
</div>

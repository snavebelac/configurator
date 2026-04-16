@php
    $gridTemplate = 'grid-template-columns: 24px minmax(0,2fr) 80px 120px 130px 110px 40px;';
@endphp
<div class="mx-auto max-w-[1480px]" wire:key="proposal-edit-{{ $proposal->id }}">

    <x-page-header
        :title="$proposal->name ?: 'Untitled proposal'"
        :eyebrow="'Editing · ' . ucfirst($proposal->status->value)">
        <x-slot:actions>
            <x-btn variant="ghost" :href="route('dashboard.proposals')">Back to list</x-btn>
            <x-btn variant="ghost" :href="route('dashboard.proposal.preview', ['proposal' => $proposal->uuid])" target="_blank">
                Preview
                <x-phosphor-arrow-square-out class="size-3.5" />
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
        <x-card-header>
            <div class="flex items-baseline gap-3">
                <h3 class="font-display text-[18px] text-ink">Features</h3>
                <span class="text-xs text-slate">{{ $proposal->features->count() }} {{ Str::plural('line', $proposal->features->count()) }}</span>
            </div>
            <div class="flex items-center gap-2">
                <x-btn variant="ghost"
                       wire:click="$dispatch('openModal', {component: 'admin.proposals.add-package-modal', arguments: {proposalId: {{ $proposal->id }} }})">
                    <x-phosphor-cube class="size-3.5" />
                    Add package
                </x-btn>
                <x-btn variant="accent"
                       wire:click="$dispatch('openModal', {component: 'admin.proposals.add-features-modal', arguments: {proposalId: {{ $proposal->id }} }})">
                    <x-phosphor-plus class="size-3.5" />
                    Add features
                </x-btn>
            </div>
        </x-card-header>

        @if ($featureGroups->isNotEmpty())
            <div class="grid items-center gap-3 border-b border-rule px-4 py-2.5 text-[11px] font-medium uppercase tracking-[0.08em] text-slate"
                 style="{{ $gridTemplate }}">
                <div aria-hidden="true"></div>
                <div>Name</div>
                <div class="text-right">Qty</div>
                <div class="text-right">Unit price</div>
                <div>Type</div>
                <div class="text-right">Line total</div>
                <div aria-hidden="true"></div>
            </div>

            <ul x-sort="$wire.reorderParents($item, $position)"
                x-sort:config="{ ghostClass: 'opacity-40' }"
                class="flex flex-col">
                @foreach ($featureGroups as $group)
                    <li x-sort:item="{{ $group['root']->id }}"
                        wire:key="group-{{ $group['root']->id }}"
                        class="border-b border-rule-soft last:border-b-0">
                        <livewire:admin.proposals.proposal-feature-form
                            :final-feature-id="$group['root']->id"
                            :is-child="false"
                            :grid-template="$gridTemplate"
                            :key="'feature-'.$group['root']->id" />
                        @foreach ($group['children'] as $child)
                            <livewire:admin.proposals.proposal-feature-form
                                :final-feature-id="$child->id"
                                :is-child="true"
                                :grid-template="$gridTemplate"
                                :key="'feature-'.$child->id" />
                        @endforeach
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-4 py-16 text-center">
                <div class="font-display text-[18px] text-ink">This proposal has no features</div>
                <p class="mt-1.5 text-sm text-slate">Return to the list and start a new proposal to pick features from your library.</p>
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-rule-soft bg-paper-2 px-6 py-4">
            <livewire:admin.proposals.proposal-total-on-the-fly :proposal-id="$proposal->id" />
            <x-btn variant="accent" :href="route('dashboard.proposal.preview', ['proposal' => $proposal->uuid])" target="_blank">
                Preview (client view)
                <x-phosphor-arrow-square-out class="size-3.5" />
            </x-btn>
        </div>
    </x-card>
</div>

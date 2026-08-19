@php
    $gridTemplate = 'grid-template-columns: 24px minmax(0,2fr) 80px 120px 130px 110px 40px;';
@endphp
<div class="mx-auto max-w-[1480px]" wire:key="proposal-edit-{{ $proposal->id }}">

    <x-page-header
        :title="$proposal->name ?: 'Untitled proposal'"
        :eyebrow="'Editing · ' . ucfirst($proposal->status->value)">
        <x-slot:actions>
            <x-btn variant="ghost" :href="route('dashboard.proposals')">Back to list</x-btn>
            <x-btn variant="ghost" :href="route('proposal.view', ['proposal' => $proposal->uuid])" target="_blank">
                Preview
                <x-phosphor-arrow-square-out class="size-3.5" />
            </x-btn>
            @if ($proposal->canBeDelivered())
                <x-btn variant="accent" type="button"
                       wire:click="markAsDelivered"
                       wire:confirm="Mark this proposal as delivered? Your client will be able to accept or decline it from the share link.">
                    Mark as delivered
                </x-btn>
            @endif
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
            <x-btn variant="accent" :href="route('proposal.view', ['proposal' => $proposal->uuid])" target="_blank">
                Preview (client view)
                <x-phosphor-arrow-square-out class="size-3.5" />
            </x-btn>
        </div>
    </x-card>

    @if ($proposal->response)
        {{-- The client has answered. --}}
        @php $clientResponse = $proposal->response; @endphp
        <div @class([
            'mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border px-6 py-4',
            'border-status-accepted-dot/40 bg-status-accepted-bg' => $clientResponse->wasAccepted(),
            'border-status-rejected-dot/40 bg-status-rejected-bg' => ! $clientResponse->wasAccepted(),
        ])>
            <div>
                <p @class([
                    'flex items-center gap-2 text-[13px] font-medium',
                    'text-status-accepted-fg' => $clientResponse->wasAccepted(),
                    'text-status-rejected-fg' => ! $clientResponse->wasAccepted(),
                ])>
                    @if ($clientResponse->wasAccepted())
                        <x-phosphor-check-circle class="size-4" />
                        Client accepted this proposal
                    @else
                        <x-phosphor-x-circle class="size-4" />
                        Client declined this proposal
                    @endif
                </p>
                <p class="mt-1 text-[12.5px] text-slate">
                    {{ $clientResponse->responded_at->format('j M Y, H:i') }}
                    @if ($clientResponse->wasAccepted())
                        · {{ count($clientResponse->selected_feature_ids ?? []) }} of
                        {{ $proposal->features->where('optional', true)->count() }} optional items kept
                        · <span class="font-mono tnum">{!! $clientResponse->acceptedTotalForHumans !!}</span>
                    @endif
                </p>
            </div>
            <x-btn variant="ghost" type="button"
                   wire:click="reopen"
                   wire:confirm="Reopen this proposal? The recorded response will be deleted and the client can answer again.">
                Reopen
            </x-btn>
        </div>
    @endif

    {{-- Proposal copy — all three of these render on the client-facing view --}}
    <div class="mt-6 max-w-[640px]">
        <x-card>
            <x-card-header title="Proposal copy" />
            <form wire:submit="saveDetails">
                <div class="space-y-6 px-[22px] py-6">
                    <x-field
                        label="Title"
                        name="name"
                        hint="The heading your client sees at the top of the proposal."
                        required />

                    <x-textarea-field
                        label="Introduction"
                        name="description"
                        rows="4"
                        placeholder="Set the scene — what this piece of work is, and why."
                        hint="Shown as the opening paragraph. Leave blank to omit it." />

                    <x-textarea-field
                        label="Closing notes"
                        name="additional"
                        rows="4"
                        placeholder="Anything worth adding — assumptions, timings, what happens next."
                        hint="Shown under a “Notes” heading at the end. Leave blank to omit it." />
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-[22px] py-4">
                    <x-btn variant="accent" type="submit">
                        <span wire:loading.remove wire:target="saveDetails">Save details</span>
                        <span wire:loading wire:target="saveDetails">Saving…</span>
                    </x-btn>
                </div>
            </form>
        </x-card>
    </div>

    <div class="mt-6 max-w-[640px]">
        <livewire:admin.proposals.client-access :proposal="$proposal" :wire:key="'client-access-'.$proposal->id" />
    </div>
</div>

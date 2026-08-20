@php
    // One template per pricing type: a percentage line has a rate where a
    // fixed line has quantity and unit price, and a recurring line needs a
    // billing period column that neither of the others has.
    $fixedGrid = 'grid-template-columns: 24px minmax(0,2fr) 80px 120px 130px 110px 40px;';
    $percentageGrid = 'grid-template-columns: 24px minmax(0,2fr) 120px 130px 110px 40px;';
    $recurringGrid = 'grid-template-columns: 24px minmax(0,2fr) 80px 120px 120px 130px 110px 40px;';

    $sectionHead = 'flex flex-wrap items-baseline justify-between gap-3 border-y border-rule-soft bg-paper-2/60 px-4 py-2.5 first:border-t-0';
    $sectionTitle = 'text-[11px] font-medium uppercase tracking-[0.08em] text-ink';
    $sectionNote = 'text-[11.5px] text-slate';
    $columnHead = 'grid items-center gap-3 border-b border-rule px-4 py-2 text-[11px] font-medium uppercase tracking-[0.08em] text-slate';
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

    @if ($proposal->percentagesHaveNoBase())
        {{-- Percentage lines with nothing to be a percentage of. The proposal
             totals zero, so this needs saying before it reaches a client. --}}
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-status-rejected-dot/40 bg-status-rejected-bg px-6 py-4">
            <x-phosphor-warning class="mt-0.5 size-4 shrink-0 text-status-rejected-fg" />
            <div>
                <p class="text-[13px] font-medium text-status-rejected-fg">
                    The percentage lines here come to nothing
                </p>
                <p class="mt-1 text-[12.5px] leading-[1.5] text-slate">
                    There's no fixed-price work on this proposal for them to be a percentage of, so
                    each one calculates to {{ $currencySymbol }}0. Add the work they apply to and
                    they'll calculate against it. (Recurring costs don't count — a percentage takes
                    its share of one-off work only.) It can't be delivered until then.
                </p>
            </div>
        </div>
    @endif

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

        @if ($proposal->features->isEmpty())
            <div class="px-4 py-16 text-center">
                <div class="font-display text-[18px] text-ink">This proposal has no features</div>
                <p class="mt-1.5 text-sm text-slate">Return to the list and start a new proposal to pick features from your library.</p>
            </div>
        @else
            {{-- One-off work. The only section that reorders: these are the
                 things being bought, and the order they're read in is the
                 order the client sees. --}}
            @if ($featureGroups->isNotEmpty())
                <div class="{{ $sectionHead }}">
                    <span class="{{ $sectionTitle }}">One-off work</span>
                    <span class="{{ $sectionNote }}">Drag to reorder — this is the order the client reads.</span>
                </div>

                <div class="{{ $columnHead }}" style="{{ $fixedGrid }}">
                    <div aria-hidden="true"></div>
                    <div>Name</div>
                    <div class="text-right">Qty</div>
                    <div class="text-right">Unit price</div>
                    <div>Included</div>
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
                                :grid-template="$fixedGrid"
                                :key="'feature-'.$group['root']->id" />
                            @foreach ($group['children'] as $child)
                                <livewire:admin.proposals.proposal-feature-form
                                    :final-feature-id="$child->id"
                                    :is-child="true"
                                    :grid-template="$fixedGrid"
                                    :key="'feature-'.$child->id" />
                            @endforeach
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Percentage lines. Kept apart because they aren't things being
                 bought — they're a share of everything above, and they move
                 whenever the one-off work does. --}}
            @if ($percentageFeatures->isNotEmpty())
                <div class="{{ $sectionHead }}">
                    <span class="{{ $sectionTitle }}">Percentage of the work</span>
                    <span class="{{ $sectionNote }}">A share of the one-off lines above. Never of each other, so two of these don't compound.</span>
                </div>

                <div class="{{ $columnHead }}" style="{{ $percentageGrid }}">
                    <div aria-hidden="true"></div>
                    <div>Name</div>
                    <div class="text-right">Rate</div>
                    <div>Included</div>
                    <div class="text-right">Amount</div>
                    <div aria-hidden="true"></div>
                </div>

                <div class="flex flex-col">
                    @foreach ($percentageFeatures as $feature)
                        <div wire:key="percentage-{{ $feature->id }}" class="border-b border-rule-soft last:border-b-0">
                            <livewire:admin.proposals.proposal-feature-form
                                :final-feature-id="$feature->id"
                                :is-child="false"
                                :grid-template="$percentageGrid"
                                :fixed-base="$fixedBase"
                                :key="'feature-'.$feature->id" />
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Ongoing costs. Never summed into the one-off figure — a build
                 fee and a monthly fee are different commitments. --}}
            @if ($recurringFeatures->isNotEmpty())
                <div class="{{ $sectionHead }}">
                    <span class="{{ $sectionTitle }}">Ongoing costs</span>
                    <span class="{{ $sectionNote }}">Totalled per billing period, and kept out of the one-off figure.</span>
                </div>

                <div class="{{ $columnHead }}" style="{{ $recurringGrid }}">
                    <div aria-hidden="true"></div>
                    <div>Name</div>
                    <div class="text-right">Qty</div>
                    <div class="text-right">Amount</div>
                    <div>Billed</div>
                    <div>Included</div>
                    <div class="text-right">Per period</div>
                    <div aria-hidden="true"></div>
                </div>

                <div class="flex flex-col">
                    @foreach ($recurringFeatures as $feature)
                        <div wire:key="recurring-{{ $feature->id }}" class="border-b border-rule-soft last:border-b-0">
                            <livewire:admin.proposals.proposal-feature-form
                                :final-feature-id="$feature->id"
                                :is-child="false"
                                :grid-template="$recurringGrid"
                                :key="'feature-'.$feature->id" />
                        </div>
                    @endforeach
                </div>
            @endif
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
                        @if ($clientResponse->hasRecurring())
                            {{-- Kept visually separate: a one-off fee and an ongoing
                                 commitment aren't the same kind of number. --}}
                            <span class="text-slate-soft">one-off, then</span>
                            @if ($clientResponse->accepted_monthly > 0)
                                <span class="font-mono tnum">{{ $currencySymbol }}{{ number_format($clientResponse->accepted_monthly / 100, 2) }}</span>/month
                            @endif
                            @if ($clientResponse->accepted_monthly > 0 && $clientResponse->accepted_annually > 0)
                                <span class="text-slate-soft">and</span>
                            @endif
                            @if ($clientResponse->accepted_annually > 0)
                                <span class="font-mono tnum">{{ $currencySymbol }}{{ number_format($clientResponse->accepted_annually / 100, 2) }}</span>/year
                            @endif
                        @endif
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

    {{-- Terms — pinned on delivery, overridable here --}}
    <div class="mt-6 max-w-[640px]">
        <x-card>
            <x-card-header title="Terms &amp; conditions" />
            <div class="px-[22px] py-6">
                @if ($termsOptions->isEmpty())
                    <p class="text-[13px] text-slate">
                        No published terms yet.
                        <a href="{{ route('dashboard.terms') }}" class="font-medium text-ink underline underline-offset-2">Create a set</a>
                        and publish a version to attach terms to your proposals.
                    </p>
                @else
                    @if ($proposal->termsVersion)
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-3 rounded-lg border border-rule bg-paper-2 px-4 py-3">
                            <div>
                                <p class="text-[13.5px] font-medium text-ink">{{ $proposal->termsVersion->terms->name }}</p>
                                <p class="mt-0.5 text-[12px] text-slate">
                                    <span class="font-mono tnum">{{ $proposal->termsVersion->label() }}</span>
                                    · published {{ $proposal->termsVersion->published_at->format('j M Y') }}
                                </p>
                            </div>
                            <span class="text-[11px] uppercase tracking-wider text-slate-soft">Pinned</span>
                        </div>
                    @else
                        <p class="mb-5 text-[13px] text-slate">
                            Nothing attached yet. The default set is pinned automatically when you mark this
                            proposal delivered, or choose one now.
                        </p>
                    @endif

                    <p class="mb-2 text-[11px] font-medium uppercase tracking-[0.08em] text-slate">
                        {{ $proposal->termsVersion ? 'Change to' : 'Attach' }}
                    </p>
                    <div class="flex flex-col gap-1.5">
                        @foreach ($termsOptions as $set)
                            <button type="button"
                                    wire:key="terms-option-{{ $set->id }}"
                                    wire:click="setTermsVersion({{ $set->currentVersion->id }})"
                                    @disabled($proposal->terms_version_id === $set->currentVersion->id)
                                    @class([
                                        'flex items-center justify-between gap-3 rounded-lg border px-3.5 py-2.5 text-left text-[13px] transition-colors',
                                        'border-ink bg-paper-2 text-ink' => $proposal->terms_version_id === $set->currentVersion->id,
                                        'border-rule text-ink hover:border-slate-faint hover:bg-paper-2' => $proposal->terms_version_id !== $set->currentVersion->id,
                                    ])>
                                <span class="flex items-center gap-2">
                                    {{ $set->name }}
                                    @if ($set->is_default)
                                        <span class="rounded-full bg-fox-soft px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wider text-ink">Default</span>
                                    @endif
                                </span>
                                <span class="font-mono text-[12px] text-slate tnum">{{ $set->currentVersion->label() }}</span>
                            </button>
                        @endforeach
                    </div>

                    @if ($proposal->termsVersion)
                        <button type="button"
                                wire:click="setTermsVersion(null)"
                                wire:confirm="Send this proposal without any terms attached?"
                                class="mt-3 text-[12.5px] font-medium text-status-rejected-fg underline-offset-4 hover:underline">
                            Remove terms
                        </button>
                    @endif
                @endif
            </div>
        </x-card>
    </div>

    <div class="mt-6 max-w-[640px]">
        <livewire:admin.proposals.client-access :proposal="$proposal" :wire:key="'client-access-'.$proposal->id" />
    </div>
</div>

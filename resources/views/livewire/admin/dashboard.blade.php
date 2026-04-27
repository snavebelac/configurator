@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $intro = $needsAttention->isNotEmpty()
        ? trans_choice(':count item is waiting on you|:count items are waiting on you', $needsAttention->count(), ['count' => $needsAttention->count()])
        : 'Nothing waiting on you — a clean slate.';
@endphp
<div class="mx-auto max-w-[1480px]">

    <x-page-header
        :title="$greeting . ', ' . $user->name . '.'"
        :eyebrow="now()->format('l, j F')"
        :lede="$intro">
        <x-slot:actions>
            <x-btn variant="ghost"
                   wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})">
                <x-phosphor-stack class="size-3.5" />
                New feature
            </x-btn>
            <x-btn variant="ghost" :href="route('dashboard.package.create')">
                <x-phosphor-cube class="size-3.5" />
                New package
            </x-btn>
            <x-btn variant="accent" :href="route('dashboard.proposal.create')">
                <x-phosphor-plus class="size-3.5" />
                New proposal
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    {{-- =========================================================
         KPI STRIP
         ========================================================= --}}
    <div class="mb-9 grid grid-cols-4 gap-px overflow-hidden rounded-2xl border border-rule bg-rule">

        {{-- Open pipeline (accent) --}}
        <div class="flex flex-col gap-3.5 bg-ink p-6 pb-[22px] text-sage">
            <div class="flex items-center justify-between gap-3">
                <span class="text-[10.5px] font-medium uppercase tracking-[0.13em] text-sage-soft">Open pipeline</span>
            </div>
            <x-money :value="$openValue" size="kpi-fox" />
            <div class="flex items-center justify-between text-xs text-sage-soft">
                <span>{{ $counts['draft'] + $counts['delivered'] }} live</span>
                <span>across drafts &amp; delivered</span>
            </div>
        </div>

        {{-- Won this month --}}
        <div class="flex flex-col gap-3.5 bg-white p-6 pb-[22px]">
            <span class="text-[10.5px] font-medium uppercase tracking-[0.13em] text-slate">Won this month</span>
            <x-money :value="$wonThisMonth" size="kpi" />
            <div class="flex items-center justify-between text-xs text-slate">
                <span>{{ $monthAccepted }} accepted</span>
                <span>{{ now()->format('F') }}</span>
            </div>
        </div>

        {{-- Conversion --}}
        <div class="flex flex-col gap-3.5 bg-white p-6 pb-[22px]">
            <span class="text-[10.5px] font-medium uppercase tracking-[0.13em] text-slate">Conversion</span>
            <div class="font-display text-[38px] leading-none tracking-[-0.025em] text-ink">
                @if ($conversion === null)
                    <span class="text-slate-soft">—</span>
                @else
                    <span class="tnum">{{ number_format($conversion, 0) }}</span><span class="ml-1 align-[8px] text-lg text-slate-soft">%</span>
                @endif
            </div>
            <div class="flex items-center justify-between text-xs text-slate">
                <span>{{ $closedCount }} closed</span>
                <span>accepted vs declined</span>
            </div>
        </div>

        {{-- Avg proposal --}}
        <div class="flex flex-col gap-3.5 bg-white p-6 pb-[22px]">
            <span class="text-[10.5px] font-medium uppercase tracking-[0.13em] text-slate">Avg. proposal</span>
            <x-money :value="$avgValue" size="kpi" />
            <div class="flex items-center justify-between text-xs text-slate">
                <span>{{ $closedCount }} closed deals</span>
                <span>average value</span>
            </div>
        </div>

    </div>

    {{-- =========================================================
         SPLIT: NEEDS ATTENTION + RECENT
         ========================================================= --}}
    <div class="mb-9 grid grid-cols-[1.3fr_1fr] gap-5">

        {{-- Needs attention --}}
        <x-card>
            <x-card-header
                title="Needs your attention"
                :meta="$needsAttention->count() . ' ' . Str::plural('item', $needsAttention->count())" />
            <div class="py-1.5">
                @forelse ($needsAttention as $item)
                    @php
                        $isDraft = $item->status === \App\Enums\Status::DRAFT;
                        $label = $isDraft
                            ? 'Draft, untouched for '.$item->updated_at->diffForHumans([
                                'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                'parts' => 1,
                            ])
                            : 'Delivered '.$item->updated_at->diffForHumans();
                    @endphp
                    <a href="{{ route('dashboard.proposal.edit', ['proposal' => $item]) }}"
                       wire:key="attn-{{ $item->id }}"
                       class="grid grid-cols-[1fr_auto] items-center gap-x-4 gap-y-2 border-b border-rule-soft px-[22px] py-3.5 transition-colors hover:bg-paper-2 last:border-b-0">
                        <div class="flex items-center gap-2.5 text-sm font-medium text-ink">
                            {{ $item->name }}
                            <x-pill :status="$item->status->value" :label="$label" />
                        </div>
                        <x-money :value="$item->total()" size="row" class="row-span-2 self-center" />
                        <div class="text-[12.5px] text-slate">
                            {{ $item->client?->name ?? 'No client' }} · last edited by {{ $item->user?->name ?? 'unknown' }}
                        </div>
                    </a>
                @empty
                    <div class="px-[22px] py-10 text-center text-sm text-slate">Nothing's stuck. Quiet shift.</div>
                @endforelse
            </div>
        </x-card>

        {{-- Lately — activity feed --}}
        <x-card>
            <x-card-header title="Lately" :meta="'last '.$activities->count().' '.Str::plural('event', $activities->count())" />
            <div class="py-1">
                @forelse ($activities as $activity)
                    @php
                        $action = $activity->action;
                        $subjectType = $activity->subjectTypeLabel();
                        $href = match (true) {
                            $activity->subject instanceof \App\Models\Proposal
                                => route('dashboard.proposal.edit', ['proposal' => $activity->subject]),
                            $activity->subject instanceof \App\Models\Package
                                => route('dashboard.package.edit', ['package' => $activity->subject]),
                            $activity->subject instanceof \App\Models\Client
                                => route('dashboard.clients'),
                            default => null,
                        };
                        $iconWrapper = match (true) {
                            $action === \App\Enums\ActivityAction::ProposalStatusChanged
                                && ($activity->payload['to'] ?? null) === \App\Enums\Status::ACCEPTED->value
                                => 'bg-status-accepted-bg text-status-accepted-fg',
                            $action === \App\Enums\ActivityAction::ProposalStatusChanged
                                && ($activity->payload['to'] ?? null) === \App\Enums\Status::REJECTED->value
                                => 'bg-status-rejected-bg text-status-rejected-fg',
                            $action === \App\Enums\ActivityAction::ProposalStatusChanged
                                && ($activity->payload['to'] ?? null) === \App\Enums\Status::DELIVERED->value
                                => 'bg-status-delivered-bg text-status-delivered-fg',
                            $subjectType === 'Package' => 'bg-fox-soft text-ink',
                            $subjectType === 'Client' => 'bg-sage-paper text-ink',
                            default => 'bg-paper-2 text-slate',
                        };
                    @endphp
                    @if ($href)
                        <a href="{{ $href }}" wire:key="activity-{{ $activity->id }}"
                           class="grid grid-cols-[26px_1fr_auto] items-start gap-x-3 px-[22px] py-3 transition-colors hover:bg-paper-2">
                    @else
                        <div wire:key="activity-{{ $activity->id }}"
                             class="grid grid-cols-[26px_1fr_auto] items-start gap-x-3 px-[22px] py-3">
                    @endif
                        <span @class([
                            'flex size-[26px] items-center justify-center rounded-full',
                            $iconWrapper,
                        ])>
                            @switch ($action)
                                @case (\App\Enums\ActivityAction::ProposalCreated)
                                    <x-phosphor-plus class="size-3" />
                                    @break
                                @case (\App\Enums\ActivityAction::ProposalStatusChanged)
                                    @switch ($activity->payload['to'] ?? null)
                                        @case (\App\Enums\Status::ACCEPTED->value) <x-phosphor-check class="size-3" /> @break
                                        @case (\App\Enums\Status::REJECTED->value) <x-phosphor-x class="size-3" /> @break
                                        @default <x-phosphor-envelope class="size-3" />
                                    @endswitch
                                    @break
                                @case (\App\Enums\ActivityAction::ClientCreated)
                                    <x-phosphor-user class="size-3" />
                                    @break
                                @case (\App\Enums\ActivityAction::PackageCreated)
                                    <x-phosphor-cube class="size-3" />
                                    @break
                            @endswitch
                        </span>
                        <div class="text-[13.5px] leading-snug text-ink">
                            <div class="font-medium">{{ $activity->headline() }}</div>
                            <div class="text-xs text-slate">{{ $subjectType }}</div>
                        </div>
                        <span class="whitespace-nowrap text-xs text-slate">{{ $activity->created_at->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true) }}</span>
                    @if ($href)
                        </a>
                    @else
                        </div>
                    @endif
                @empty
                    <div class="px-[22px] py-10 text-center text-sm text-slate">No activity yet — things will show up here as you work.</div>
                @endforelse
            </div>
        </x-card>
    </div>

    {{-- =========================================================
         RECENT PROPOSALS TABLE
         ========================================================= --}}
    <x-card>
        <div class="flex items-center gap-2.5 border-b border-rule-soft px-5 py-3.5">
            <div class="inline-flex gap-0.5 rounded-[9px] border border-rule bg-paper-2 p-[3px]">
                @foreach ([['all', 'All'], ['draft', 'Drafts'], ['delivered', 'Delivered'], ['accepted', 'Accepted'], ['rejected', 'Rejected']] as [$key, $label])
                    <button type="button"
                            class="inline-flex items-center gap-[7px] rounded-md px-3 py-[5px] text-[12.5px] font-medium leading-[1.6] text-slate hover:text-ink {{ $key === 'all' ? 'bg-white text-ink shadow-sm' : '' }}">
                        {{ $label }} <span class="font-mono text-[10.5px] text-slate-soft">{{ $counts[$key] ?? 0 }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr>
                    <x-th style="width:38%">Proposal</x-th>
                    <x-th>Client</x-th>
                    <x-th>Owner</x-th>
                    <x-th>Status</x-th>
                    <x-th>Value</x-th>
                    <x-th>Updated</x-th>
                    <x-th></x-th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recent as $item)
                    <tr wire:key="row-{{ $item->id }}" class="group transition-colors hover:bg-paper-2 last:[&>td]:border-b-0">
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            <div class="font-medium">{{ $item->name }}</div>
                            <div class="mt-0.5 text-xs text-slate">{{ $item->features->count() }} {{ Str::plural('feature', $item->features->count()) }}</div>
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">{{ $item->client?->name ?? '—' }}</td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">{{ $item->user?->full_name ?? '—' }}</td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle"><x-pill :status="$item->status->value" /></td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle"><x-money :value="$item->total()" size="mono" /></td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-slate">{{ $item->updated_at->diffForHumans(short: true) }}</td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
                                <x-btn variant="row" :href="route('dashboard.proposal.edit', ['proposal' => $item])">Open</x-btn>
                                <x-btn variant="row" :href="route('dashboard.proposal.preview', ['proposal' => $item->uuid])">Preview</x-btn>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate">No proposals yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

</div>

@php
    use Carbon\Carbon;
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $intro = $needsAttention->isNotEmpty()
        ? trans_choice(':count item is waiting on you|:count items are waiting on you', $needsAttention->count(), ['count' => $needsAttention->count()])
        : 'Nothing waiting on you — a clean slate.';
@endphp
<div class="mx-auto max-w-[1480px]">

    {{-- =========================================================
         PAGE HEADER
         ========================================================= --}}
    <div class="mb-9 flex items-end justify-between gap-8 border-b border-rule pb-7">
        <div class="flex max-w-2xl flex-col gap-1.5">
            <span class="text-[11px] font-medium uppercase tracking-[0.14em] text-slate">{{ now()->format('l, j F') }}</span>
            <h1 class="font-display text-[clamp(34px,3.4vw,46px)] font-[450] leading-[1.04] tracking-[-0.022em] text-ink"
                style="font-variation-settings: 'opsz' 144, 'SOFT' 50;">
                {{ $greeting }}, {{ $user->name }}.
            </h1>
            <p class="max-w-xl text-[14.5px] text-slate">{{ $intro }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard.proposal.create') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-fox bg-fox px-4 py-[9px] text-[13px] font-semibold text-ink transition-colors hover:bg-fox-deep hover:border-fox-deep">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                New proposal
            </a>
        </div>
    </div>

    {{-- =========================================================
         KPI STRIP
         ========================================================= --}}
    <div class="mb-9 grid grid-cols-4 gap-px overflow-hidden rounded-2xl border border-rule bg-rule">

        {{-- Open pipeline (accent) --}}
        <div class="flex flex-col gap-3.5 bg-ink p-6 pb-[22px] text-sage">
            <div class="flex items-center justify-between gap-3">
                <span class="text-[10.5px] font-medium uppercase tracking-[0.13em] text-sage-soft">Open pipeline</span>
            </div>
            <div class="font-display text-[38px] font-[450] leading-none tracking-[-0.025em] text-fox"
                 style="font-variation-settings: 'opsz' 96;">
                <span class="mr-0.5 align-[5px] text-2xl text-fox-deep">£</span><span class="tnum">{{ number_format($openValue, 0) }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-sage-soft">
                <span>{{ $counts['draft'] + $counts['delivered'] }} live</span>
                <span>across drafts &amp; delivered</span>
            </div>
        </div>

        {{-- Won this month --}}
        <div class="flex flex-col gap-3.5 bg-white p-6 pb-[22px]">
            <span class="text-[10.5px] font-medium uppercase tracking-[0.13em] text-slate">Won this month</span>
            <div class="font-display text-[38px] font-[450] leading-none tracking-[-0.025em] text-ink"
                 style="font-variation-settings: 'opsz' 96;">
                <span class="mr-0.5 align-[5px] text-2xl text-slate-soft">£</span><span class="tnum">{{ number_format($wonThisMonth, 0) }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate">
                <span>{{ $monthAccepted }} accepted</span>
                <span>{{ now()->format('F') }}</span>
            </div>
        </div>

        {{-- Conversion --}}
        <div class="flex flex-col gap-3.5 bg-white p-6 pb-[22px]">
            <span class="text-[10.5px] font-medium uppercase tracking-[0.13em] text-slate">Conversion</span>
            <div class="font-display text-[38px] font-[450] leading-none tracking-[-0.025em] text-ink"
                 style="font-variation-settings: 'opsz' 96;">
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
            <div class="font-display text-[38px] font-[450] leading-none tracking-[-0.025em] text-ink"
                 style="font-variation-settings: 'opsz' 96;">
                <span class="mr-0.5 align-[5px] text-2xl text-slate-soft">£</span><span class="tnum">{{ number_format($avgValue, 0) }}</span>
            </div>
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
        <section class="overflow-hidden rounded-2xl border border-rule bg-white">
            <header class="flex items-baseline justify-between border-b border-rule-soft px-[22px] pb-3.5 pt-[18px]">
                <h3 class="font-display text-[18px] font-medium text-ink"
                    style="font-variation-settings: 'opsz' 24;">
                    Needs your attention
                </h3>
                <span class="text-xs text-slate">{{ $needsAttention->count() }} {{ Str::plural('item', $needsAttention->count()) }}</span>
            </header>
            <div class="py-1.5">
                @forelse ($needsAttention as $item)
                    @php
                        $days = $item->updated_at->diffInDays(now());
                        $isDraft = $item->status === \App\Enums\Status::DRAFT;
                        $label = $isDraft
                            ? "Draft · {$days}d untouched"
                            : "Delivered · {$days}d";
                    @endphp
                    <a href="{{ route('dashboard.proposal.edit', ['proposal' => $item]) }}"
                       wire:key="attn-{{ $item->id }}"
                       class="grid grid-cols-[1fr_auto] items-center gap-x-4 gap-y-2 border-b border-rule-soft px-[22px] py-3.5 transition-colors hover:bg-paper-2 last:border-b-0">
                        <div class="flex items-center gap-2.5 text-sm font-medium text-ink">
                            {{ $item->name }}
                            <x-pill :status="$item->status->value" :label="$label" />
                        </div>
                        <div class="row-span-2 self-center font-display text-[17px] font-medium text-ink"
                             style="font-variation-settings: 'opsz' 36;">
                            <span class="mr-0.5 text-sm text-slate-soft">£</span><span class="tnum">{{ number_format($item->total(), 0) }}</span>
                        </div>
                        <div class="text-[12.5px] text-slate">
                            {{ $item->client?->name ?? 'No client' }} · last edited by {{ $item->user?->name ?? 'unknown' }}
                        </div>
                    </a>
                @empty
                    <div class="px-[22px] py-10 text-center text-sm text-slate">Nothing's stuck. Quiet shift.</div>
                @endforelse
            </div>
        </section>

        {{-- Recent updates --}}
        <section class="overflow-hidden rounded-2xl border border-rule bg-white">
            <header class="flex items-baseline justify-between border-b border-rule-soft px-[22px] pb-3.5 pt-[18px]">
                <h3 class="font-display text-[18px] font-medium text-ink"
                    style="font-variation-settings: 'opsz' 24;">
                    Lately
                </h3>
                <span class="text-xs text-slate">last 8 changes</span>
            </header>
            <div class="py-1">
                @forelse ($recent as $item)
                    <a href="{{ route('dashboard.proposal.edit', ['proposal' => $item]) }}"
                       wire:key="recent-{{ $item->id }}"
                       class="grid grid-cols-[26px_1fr_auto] items-start gap-x-3 px-[22px] py-3 transition-colors hover:bg-paper-2">
                        <span @class([
                            'flex size-[26px] items-center justify-center rounded-full',
                            'bg-status-accepted-bg text-status-accepted-fg'   => $item->status === \App\Enums\Status::ACCEPTED,
                            'bg-status-delivered-bg text-status-delivered-fg' => $item->status === \App\Enums\Status::DELIVERED,
                            'bg-status-draft-bg text-status-draft-fg'         => $item->status === \App\Enums\Status::DRAFT,
                            'bg-status-rejected-bg text-status-rejected-fg'   => $item->status === \App\Enums\Status::REJECTED,
                            'bg-status-archived-bg text-status-archived-fg'   => $item->status === \App\Enums\Status::ARCHIVED,
                        ])>
                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                @switch ($item->status)
                                    @case (\App\Enums\Status::ACCEPTED) <path d="m5 12 5 5 9-11"/> @break
                                    @case (\App\Enums\Status::REJECTED) <path d="M6 6l12 12M6 18 18 6"/> @break
                                    @case (\App\Enums\Status::DELIVERED) <path d="M3 7l9 6 9-6"/><rect x="3" y="5" width="18" height="14" rx="1"/> @break
                                    @default <path d="M4 20h4l11-11-4-4L4 16z"/>
                                @endswitch
                            </svg>
                        </span>
                        <div class="text-[13.5px] leading-snug text-ink">
                            <strong class="font-medium">{{ $item->name }}</strong>
                            <span class="text-slate"> · {{ $item->status->value }}</span>
                            <div class="text-xs text-slate">{{ $item->client?->name ?? 'No client' }}</div>
                        </div>
                        <span class="whitespace-nowrap text-xs text-slate">{{ $item->updated_at->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true) }}</span>
                    </a>
                @empty
                    <div class="px-[22px] py-10 text-center text-sm text-slate">No proposals yet — start your first one.</div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- =========================================================
         RECENT PROPOSALS TABLE
         ========================================================= --}}
    <section class="overflow-hidden rounded-2xl border border-rule bg-white">
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
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate" style="width:38%">Proposal</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Client</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Owner</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Status</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Value</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Updated</th>
                    <th class="border-b border-rule bg-paper px-4 py-3"></th>
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
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle font-mono text-[13px] text-ink"><span class="mr-0.5 text-slate-soft">£</span><span class="tnum">{{ number_format($item->total(), 0) }}</span></td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-slate">{{ $item->updated_at->diffForHumans(short: true) }}</td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
                                <a href="{{ route('dashboard.proposal.edit', ['proposal' => $item]) }}"
                                   class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate hover:bg-paper-2 hover:text-ink">Open</a>
                                <a href="{{ route('dashboard.proposal.preview', ['proposal' => $item->uuid]) }}"
                                   class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate hover:bg-paper-2 hover:text-ink">Preview</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate">No proposals yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

</div>

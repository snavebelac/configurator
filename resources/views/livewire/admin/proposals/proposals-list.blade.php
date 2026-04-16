@php
    use App\Enums\Status;

    $segments = [
        ['key' => 'all',       'label' => 'All'],
        ['key' => 'draft',     'label' => 'Drafts'],
        ['key' => 'delivered', 'label' => 'Delivered'],
        ['key' => 'accepted',  'label' => 'Accepted'],
        ['key' => 'rejected',  'label' => 'Rejected'],
        ['key' => 'archived',  'label' => 'Archived'],
    ];

    $total = $statusCounts['all'];
    $intro = $total === 0
        ? 'Nothing here yet. Start your first proposal and it will land in this list.'
        : 'Every quote you\'ve sent and every draft you\'ve started. Open any row to keep editing or hand it to a client.';
@endphp
<div class="mx-auto max-w-[1480px]">

    {{-- =========================================================
         PAGE HEADER
         ========================================================= --}}
    <div class="mb-9 flex items-end justify-between gap-8 border-b border-rule pb-7">
        <div class="flex max-w-2xl flex-col gap-1.5">
            <span class="text-[11px] font-medium uppercase tracking-[0.14em] text-slate">
                {{ $total }} {{ Str::plural('proposal', $total) }} in flight
            </span>
            <h1 class="font-display text-[clamp(34px,3.4vw,46px)] font-[450] leading-[1.04] tracking-[-0.022em] text-ink"
                style="font-variation-settings: 'opsz' 144, 'SOFT' 50;">
                Proposals.
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
         TABLE
         ========================================================= --}}
    <section class="overflow-hidden rounded-2xl border border-rule bg-white">

        {{-- Toolbar: segmented status + search --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-rule-soft px-5 py-3.5">
            <div class="inline-flex gap-0.5 rounded-[9px] border border-rule bg-paper-2 p-[3px]">
                @foreach ($segments as $segment)
                    <button type="button"
                            wire:click="$set('filter', '{{ $segment['key'] }}')"
                            @class([
                                'inline-flex items-center gap-[7px] rounded-md px-3 py-[5px] text-[12.5px] font-medium leading-[1.6] transition-colors',
                                'bg-white text-ink shadow-sm'     => $filter === $segment['key'],
                                'text-slate hover:text-ink'       => $filter !== $segment['key'],
                            ])>
                        {{ $segment['label'] }}
                        <span @class([
                            'font-mono text-[10.5px]',
                            'text-slate'      => $filter === $segment['key'],
                            'text-slate-soft' => $filter !== $segment['key'],
                        ])>{{ $statusCounts[$segment['key']] ?? 0 }}</span>
                    </button>
                @endforeach
            </div>

            <div class="relative flex items-center">
                <svg class="pointer-events-none absolute left-3 size-3.5 text-slate-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                <input type="text"
                       wire:model.live.debounce.250ms="search"
                       placeholder="Filter proposals, clients…"
                       class="w-64 rounded-lg border border-rule bg-paper-2 py-[7px] pl-8 pr-3 text-[13px] text-ink placeholder:text-slate-soft focus:border-ink focus:outline-none focus:bg-white transition-colors">
                @if ($search !== '')
                    <button type="button" wire:click="$set('search', '')" class="absolute right-2 rounded p-1 text-slate-soft hover:text-ink" aria-label="Clear search">
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M6 18 18 6"/></svg>
                    </button>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <table class="w-full">
            <thead>
                <tr>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate" style="width:34%">Proposal</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Client</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Owner</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Status</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Value</th>
                    <th class="border-b border-rule bg-paper px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-slate">Updated</th>
                    <th class="border-b border-rule bg-paper px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($proposals as $proposal)
                    <tr wire:key="row-{{ $proposal->id }}" class="group transition-colors hover:bg-paper-2 last:[&>td]:border-b-0">
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            <div class="font-medium">{{ $proposal->name }}</div>
                            <div class="mt-0.5 text-xs text-slate">
                                {{ $proposal->features->count() }} {{ Str::plural('feature', $proposal->features->count()) }}
                            </div>
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            @if ($proposal->client)
                                <div>{{ $proposal->client->name }}</div>
                                <div class="mt-0.5 text-xs text-slate">{{ $proposal->client->contact }}</div>
                            @else
                                <span class="text-slate-soft">—</span>
                            @endif
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">{{ $proposal->user?->full_name ?? '—' }}</td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <x-pill :status="$proposal->status->value" />
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle font-mono text-[13px] text-ink">
                            <span class="mr-0.5 text-slate-soft">£</span><span class="tnum">{{ number_format($proposal->total(), 0) }}</span>
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-slate">
                            {{ $proposal->updated_at->diffForHumans(short: true) }}
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
                                <a href="{{ route('dashboard.proposal.edit', ['proposal' => $proposal->id]) }}"
                                   class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate hover:bg-paper-2 hover:text-ink">Edit</a>
                                <a href="{{ route('dashboard.proposal.preview', ['proposal' => $proposal->uuid]) }}"
                                   class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate hover:bg-paper-2 hover:text-ink">Preview</a>
                                <button type="button"
                                        wire:click="delete({{ $proposal->id }})"
                                        wire:confirm="Are you sure you wish to delete [{{ $proposal->name }}]?"
                                        class="rounded-md px-2.5 py-1.5 text-xs font-medium text-status-rejected-fg hover:bg-status-rejected-bg">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-14 text-center text-sm text-slate">
                            @if ($search !== '' || $filter !== 'all')
                                Nothing matches those filters.
                            @else
                                No proposals yet — start your first one.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($proposals->hasPages())
            <div class="border-t border-rule-soft px-5 py-3">
                {{ $proposals->links() }}
            </div>
        @endif
    </section>

</div>

@php
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
<div class="max-w-[1480px]">

    <x-page-header
        title="Proposals."
        :eyebrow="$total . ' ' . Str::plural('proposal', $total) . ' in flight'"
        :lede="$intro">
        <x-slot:actions>
            <x-btn variant="accent" :href="route('dashboard.proposal.create')">
                <x-phosphor-plus class="size-3.5" />
                New proposal
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>

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
                <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 size-3.5 text-slate-soft" />
                <input type="text"
                       wire:model.live.debounce.250ms="search"
                       placeholder="Filter proposals, clients…"
                       class="w-64 rounded-lg border border-rule bg-paper-2 py-[7px] pl-8 pr-3 text-[13px] text-ink placeholder:text-slate-soft focus:border-ink focus:outline-none focus:bg-white transition-colors">
                @if ($search !== '')
                    <button type="button" wire:click="$set('search', '')" class="absolute right-2 rounded p-1 text-slate-soft hover:text-ink" aria-label="Clear search">
                        <x-phosphor-x class="size-3" />
                    </button>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <table class="w-full">
            <thead>
                <tr>
                    <x-th style="width:34%">Proposal</x-th>
                    <x-th>Client</x-th>
                    <x-th>Owner</x-th>
                    <x-th>Status</x-th>
                    <x-th>Value</x-th>
                    <x-th>Updated</x-th>
                    <x-th></x-th>
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
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <x-money :value="$proposal->total()" size="mono" />
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-slate">
                            {{ $proposal->updated_at->diffForHumans(short: true) }}
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
                                <x-btn variant="row" :href="route('dashboard.proposal.edit', ['proposal' => $proposal->id])">Edit</x-btn>
                                <x-btn variant="row" :href="route('dashboard.proposal.preview', ['proposal' => $proposal->uuid])">Preview</x-btn>
                                <x-btn variant="row"
                                       class="text-status-rejected-fg hover:bg-status-rejected-bg"
                                       wire:click="delete({{ $proposal->id }})"
                                       wire:confirm="Are you sure you wish to delete [{{ $proposal->name }}]?">
                                    Delete
                                </x-btn>
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
    </x-card>

</div>

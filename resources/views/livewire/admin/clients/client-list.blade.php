@php
    $intro = $total === 0
        ? 'You haven\'t added a client yet. Add one and they\'ll show up here — ready to attach to proposals.'
        : 'Every person or company you\'ve ever sent a proposal to, in one place.';
@endphp
<div class="mx-auto max-w-[1480px]">

    <x-page-header
        title="Clients."
        :eyebrow="$total . ' ' . Str::plural('client', $total) . ' on file'"
        :lede="$intro">
        <x-slot:actions>
            <x-btn variant="accent" wire:click="$dispatch('openModal', {component: 'admin.clients.client-modal'})">
                <x-phosphor-plus class="size-3.5" />
                Add client
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-card>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-rule-soft px-5 py-3.5">
            <span class="text-xs text-slate">{{ $clients->total() }} {{ Str::plural('result', $clients->total()) }}</span>

            <div class="relative flex items-center">
                <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 size-3.5 text-slate-soft" />
                <input type="text"
                       wire:model.live.debounce.250ms="search"
                       placeholder="Filter by name, contact, email…"
                       class="w-72 rounded-lg border border-rule bg-paper-2 py-[7px] pl-8 pr-3 text-[13px] text-ink placeholder:text-slate-soft focus:border-ink focus:outline-none focus:bg-white transition-colors">
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
                    <x-th style="width:32%">Client</x-th>
                    <x-th>Contact</x-th>
                    <x-th>Email</x-th>
                    <x-th>Phone</x-th>
                    <x-th></x-th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr wire:key="client-{{ $client->id }}" class="group transition-colors hover:bg-paper-2 last:[&>td]:border-b-0">
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            <div class="font-medium">{{ $client->name }}</div>
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            {{ $client->contact ?: '—' }}
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            @if ($client->contact_email)
                                <x-email-link :email="$client->contact_email" />
                            @else
                                <span class="text-slate-soft">—</span>
                            @endif
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            @if ($client->contact_phone)
                                <x-tel-link :number="$client->contact_phone" />
                            @else
                                <span class="text-slate-soft">—</span>
                            @endif
                        </td>
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
                                <x-btn variant="row" wire:click="$dispatch('openModal', {component: 'admin.clients.client-modal', arguments: {clientId: {{ $client->id }} }})">
                                    Edit
                                </x-btn>
                                <x-btn variant="row" class="text-status-rejected-fg hover:bg-status-rejected-bg"
                                       wire:click="delete({{ $client->id }})"
                                       wire:confirm="Are you sure you wish to delete [{{ $client->name }}]?">
                                    Delete
                                </x-btn>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-14 text-center text-sm text-slate">
                            @if ($search !== '')
                                No clients match "{{ $search }}".
                            @else
                                No clients yet — add your first one.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($clients->hasPages())
            <div class="border-t border-rule-soft px-5 py-3">
                {{ $clients->links() }}
            </div>
        @endif
    </x-card>

</div>

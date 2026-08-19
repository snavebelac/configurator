<div class="mx-auto max-w-[1100px]">

    <x-page-header
        title="Terms &amp; conditions."
        :eyebrow="$termsSets->count() . ' ' . Str::plural('set', $termsSets->count())"
        lede="Each set is versioned. A proposal is pinned to whichever version was current when you sent it, so editing your terms never rewrites what a client already agreed to.">
        <x-slot:actions>
            @unless ($creating)
                <x-btn variant="accent" type="button" wire:click="startCreating">
                    <x-phosphor-plus class="size-3.5" />
                    New set
                </x-btn>
            @endunless
        </x-slot:actions>
    </x-page-header>

    @if ($creating)
        <x-card class="mb-6">
            <form wire:submit="create">
                <div class="px-[22px] py-6">
                    <x-field
                        label="Set name"
                        name="name"
                        placeholder="Standard build"
                        hint="Something you'll recognise when choosing between sets on a proposal."
                        required />
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-rule-soft bg-paper-2 px-[22px] py-4">
                    <x-btn variant="ghost" type="button" wire:click="cancelCreating">Cancel</x-btn>
                    <x-btn variant="accent" type="submit">Create set</x-btn>
                </div>
            </form>
        </x-card>
    @endif

    <x-card>
        <table class="w-full">
            <thead>
                <tr>
                    <x-th style="width:45%">Set</x-th>
                    <x-th>Current version</x-th>
                    <x-th>Status</x-th>
                    <x-th></x-th>
                </tr>
            </thead>
            <tbody>
                @forelse ($termsSets as $set)
                    <tr wire:key="terms-{{ $set->id }}" class="group transition-colors hover:bg-paper-2 last:[&>td]:border-b-0">
                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            <div class="flex items-center gap-2.5">
                                <x-row-title :href="route('dashboard.terms.edit', ['terms' => $set])">{{ $set->name }}</x-row-title>
                                @if ($set->is_default)
                                    <span class="rounded-full bg-fox-soft px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider text-ink">
                                        Default
                                    </span>
                                @endif
                            </div>
                            <div class="mt-0.5 text-xs text-slate">
                                {{ $set->published_versions_count }} published
                                {{ Str::plural('version', $set->published_versions_count) }}
                            </div>
                        </td>

                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle text-[13.5px] text-ink">
                            @if ($set->currentVersion)
                                <span class="font-mono tnum">{{ $set->currentVersion->label() }}</span>
                                <span class="ml-1.5 text-xs text-slate">
                                    {{ $set->currentVersion->published_at->format('j M Y') }}
                                </span>
                            @else
                                <span class="text-slate-soft">—</span>
                            @endif
                        </td>

                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            @if ($set->currentVersion)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-status-accepted-bg px-2 py-0.5 text-[11px] font-medium uppercase tracking-wider leading-5 text-status-accepted-fg">
                                    <span class="size-1.5 rounded-full bg-status-accepted-dot"></span>
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-status-archived-bg px-2 py-0.5 text-[11px] font-medium uppercase tracking-wider leading-5 text-status-archived-fg">
                                    <span class="size-1.5 rounded-full bg-status-archived-dot"></span>
                                    Draft only
                                </span>
                            @endif
                        </td>

                        <td class="border-b border-rule-soft px-4 py-3.5 align-middle">
                            <div class="flex justify-end gap-1.5 opacity-55 transition-opacity group-hover:opacity-100">
                                @unless ($set->is_default)
                                    <x-btn variant="row" wire:click="makeDefault({{ $set->id }})">Make default</x-btn>
                                @endunless
                                <x-btn variant="row" :href="route('dashboard.terms.edit', ['terms' => $set])">Edit</x-btn>
                                <x-btn variant="row" class="text-status-rejected-fg hover:bg-status-rejected-bg"
                                       wire:click="delete({{ $set->id }})"
                                       wire:confirm="Delete “{{ $set->name }}” and all its versions?">
                                    Delete
                                </x-btn>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-14 text-center text-sm text-slate">
                            No terms yet — create your first set to attach terms to proposals.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

</div>

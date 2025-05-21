<div class="mt-1 max-w-7xl">
    <div class="sm:flex sm:items-center px-3">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold text-gray-900">Edit Draft Proposal</h1>
        </div>
    </div>

    <div class="mt-8 flow-root px-3">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">

            <x-tables.table>
                <x-slot:thead>
                    <x-tables.table-row :is-head="true">
                        <x-tables.table-head-cell>Name</x-tables.table-head-cell>
                        <x-tables.table-head-cell>Quantity</x-tables.table-head-cell>
                        <x-tables.table-head-cell>Cost</x-tables.table-head-cell>
                        <x-tables.table-head-cell>Optional</x-tables.table-head-cell>
                        <x-tables.table-head-cell>Remove</x-tables.table-head-cell>
                    </x-tables.table-row>
                </x-slot:thead>
                <x-slot:tbody>
                    @forelse ($proposal->features  as $feature)
                        <livewire:admin.proposals.proposal-feature-form :final-feature-id="$feature->id" wire:key="$feature->id" />
                    @empty
                        <x-tables.table-row>
                            <x-tables.table-cell colspan="5">This proposal has no features</x-tables.table-cell>
                        </x-tables.table-row>
                    @endforelse
                </x-slot:tbody>
            </x-tables.table>
            <div class="flex justify-between items-center">
                <livewire:admin.proposals.proposal-total-on-the-fly :proposal-id="$proposal->id" />
                <button class="button">Finalise</button>
            </div>
        </div>
    </div>
</div>

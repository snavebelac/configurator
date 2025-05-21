<div class="mt-1 max-w-7xl">
    <div class="sm:flex sm:items-center px-3">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold text-gray-900">Create Proposal</h1>
        </div>
    </div>

    <div class="mt-8 flow-root px-3">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="my-6">
                    <livewire:admin.shared.select :items="$clients" placeholder="Choose client" label="Client" wire:model.live="clientId"/>
                    @error('clientId')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="my-6">
                    <div class=" flex items-center gap-6">
                        <label for="proposal-name" class="block shrink-0 grow-0">Proposal title</label>
                        <input type="text" id="proposal-name" wire:model="name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm"/>
                    </div>
                    @error('name')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-start gap-6">
                    <div class="w-1/4 bg-white rounded-md shadow px-3 py-2">
                        <div class="flex items-center justify-between gap-3 my-3">
                            <h2>Select features to add</h2>
                            <button type="button"
                                wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})"
                                class="button button-icon button-small">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex flex-col gap-2">
                            @foreach($features as $feature)
                                <div wire:key="feature_{{ $feature->id }}">
                                    <button wire:click="selectFeature({{ $feature->id }})" type="button"
                                        class="flex w-full items-center justify-between px-3 py-2 text-sm rounded-md bg-gray-50 hover:bg-primary-100 group">
                                        <span class="text-left w-2/3">{{ $feature->name }}</span>
                                        <span>
                                            {!! $feature->price_for_humans !!} / {{ $feature->quantity }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="size-4 group-hover:translate-x-1 transition-all duration-200">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                        </svg>

                                    </button>
                                </div>
                            @endforeach
                        </div>
                        {{ $features->links() }}
                    </div>
                    <div class="w-3/4 bg-white rounded-md shadow px-3 py-2">
                        <div class="flex items-center justify-between">
                            <h2 class="font-medium my-3 text-lg">Selected features</h2>
                            <div class="flex items-center gap-4 mr-8 font-bold text-xl">
                                Total
                                <span>{!! $totalForSelectedFeatures !!}</span>
                            </div>
                        </div>
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
                                @forelse ($selectedFeatures  as $selectedFeature)
                                    <x-tables.table-row wire:key="selected_{{ $selectedFeature->id }}">
                                        <x-tables.table-cell>{{ $selectedFeature->name }}</x-tables.table-cell>
                                        <x-tables.table-cell>{{ $selectedFeature->quantity }}</x-tables.table-cell>
                                        <x-tables.table-cell>&pound;{{ $selectedFeature->price }}</x-tables.table-cell>
                                        <x-tables.table-cell>
                                            @if ($selectedFeature->optional)
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor"
                                                    class="size-6 text-primary-600">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                </svg>
                                            @endif
                                        </x-tables.table-cell>
                                        <x-tables.table-cell>
                                            <button wire:click="removeFeature({{ $selectedFeature->id }})"
                                                class="button button-warning">X<span
                                                    class="sr-only">, Remove {{ $selectedFeature->name }}</span>
                                            </button>
                                        </x-tables.table-cell>
                                    </x-tables.table-row>
                                @empty
                                    <x-tables.table-row>
                                        <x-tables.table-cell colspan="5">Select some features...</x-tables.table-cell>
                                    </x-tables.table-row>
                                @endforelse
                            </x-slot:tbody>
                        </x-tables.table>
                        @error('selectedFeatureIds')<p class="text-red-600 my-3">{{ $message }}</p>@enderror
                        <div class="mt-6 mb-1 flex justify-end">
                            <button class="button" type="button" wire:click="createProposal()">Create</button>
                        </div>
                        <span class="flex justify-end text-sm text-gray-400 font-medium">You can customise the quantities and pricing in the next step</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

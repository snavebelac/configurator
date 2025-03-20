<div class="mt-1 max-w-7xl">
    <div class="flex flex-col gap-2 md:flex-row items-start md:items-center px-3">
        <div class="md:flex-auto">
            <h1 class="text-base font-semibold text-gray-900">Clients</h1>
            <p class="mt-2 text-sm text-gray-700">A list of your clients</p>
        </div>
        <div class="md:flex-auto flex items-center gap-2">
            <label for="search">Search</label>
            <input id="search" name="search" type="text" wire:model.live.debounce="search" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
            <button class="button button-round" wire:click="$set('search', '')" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="mt-4 sm:mt-0 md:ml-16 sm:flex-none">
            <button type="button" wire:click="$dispatch('openModal', {component: 'admin.clients.client-modal'})" class="button">Add Client</button>
        </div>
    </div>
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <x-tables.table>
                    <x-slot:thead>
                        <x-tables.table-row :is-head="true">
                            <x-tables.table-head-cell>Client</x-tables.table-head-cell>
                            <x-tables.table-head-cell>Contact</x-tables.table-head-cell>
                            <x-tables.table-head-cell>Email</x-tables.table-head-cell>
                            <x-tables.table-head-cell>Phone</x-tables.table-head-cell>
                            <x-tables.table-head-cell>Actions</x-tables.table-head-cell>
                        </x-tables.table-row>
                    </x-slot:thead>
                    <x-slot:tbody>
                        @forelse ($clients as $client)
                            <x-tables.table-row wire:key="{{ $client->id }}">
                                <x-tables.table-cell>{{ $client->name }}</x-tables.table-cell>
                                <x-tables.table-cell>{{ $client->contact }}</x-tables.table-cell>
                                <x-tables.table-cell><x-email-link :email="$client->contact_email" /></x-tables.table-cell>
                                <x-tables.table-cell><x-tel-link :number="$client->contact_phone" /></x-tables.table-cell>
                                <x-tables.table-cell>
                                    <button wire:click="$dispatch('openModal', {component: 'admin.clients.client-modal', arguments: {clientId: {{ $client->id }} }})" class="button">Edit<span class="sr-only">, {{ $client->name }}</span></button>
                                    <button wire:click="delete({{ $client->id }})" wire:confirm="Are you sure you wish to delete [{{ $client->name }}]?" class="button button-warning">Delete<span class="sr-only">, {{ $client->name }}</span></button>
                                    </x-tables.table-cell>
                            </x-tables.table-row>
                        @empty
                            <x-tables.table-row-empty>No clients found</x-tables.table-row-empty>
                        @endforelse
                    </x-slot:tbody>
                </x-tables.table>

                <div class="mt-3">
                    {{ $clients->links() }}
                </div>
            </div>


        </div>
    </div>
</div>




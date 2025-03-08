<div class="mt-1 max-w-7xl">
    <div class="flex flex-col gap-2 md:flex-row items-start md:items-center px-3">
        <div class="md:flex-auto">
            <h1 class="text-base font-semibold text-gray-900">Features</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all your avilable features</p>
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
            <button type="button" wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})" class="button">Add Feature</button>
        </div>
    </div>
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden ring-1 shadow-sm ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">Optional</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Price</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Quantity</th>
                            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                                <span class="sr-only">Edit &amp; Delete</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($features as $feature)
                            <tr class="even:bg-gray-50 hover:bg-primary-50" wire:key="{{ $feature->id }}">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">
                                    @if ($feature->optional)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-primary-600">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    @endif
                                </td>

                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $feature->name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap  text-gray-900 sm:pl-3">
                                    &pound;{{ $feature->price }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $feature->quantity }}
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3 flex items-center gap-2 justify-end">
                                    <button wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal', arguments: {featureId: {{ $feature->id }} }})" class="button">Edit<span class="sr-only">, {{ $feature->name }}</span></button>
                                    <button wire:click="delete({{ $feature->id }})" wire:confirm="Are you sure you wish to delete [{{ $feature->name }}]?" class="button button-warning">Delete<span class="sr-only">, {{ $feature->name }}</span></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">No features found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $features->links() }}
                </div>
            </div>


        </div>
    </div>
</div>



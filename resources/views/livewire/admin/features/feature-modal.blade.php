<x-modal>
    <form wire:submit.prevent="save">
        <div class="space-y-12">
            <h2 class="text-lg font-bold mb-4">{{ $featureId ? 'Edit feature' : 'Add feature' }}</h2>
            <div class="border-b border-gray-900/10 pb-12">
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm/6 font-medium text-gray-900">Name</label>
                        <div class="mt-2">
                            <input type="text" name="name" id="name" wire:model="name" autocomplete="honorific-prefix" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                        @error('name')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label for="description" class="block text-sm/6 font-medium text-gray-900">Description</label>
                        <div class="mt-2">
                            <input type="text" name="description" id="description" wire:model="description" autocomplete="given-name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                        @error('description')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="price" class="block text-sm/6 font-medium text-gray-900">Price</label>
                        <div class="mt-2">
                            <input id="price" name="price" type="number" step="0.01" min="0" wire:model="price" autocomplete="price" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                        @error('price')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="quantity" class="block text-sm/6 font-medium text-gray-900">Quantity</label>
                        <div class="mt-2">
                            <input id="quantity" name="quantity" type="number" step="1" min="1" wire:model="quantity" autocomplete="quantity" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                        @error('quantity')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label for="optional" class="flex items-center gap-2 text-sm/6 font-medium text-gray-900">
                            <input type="checkbox" name="optional" id="optional"  wire:model="optional" class="bg-white px-3 py-1.5 text-base text-gray-900 outline -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                            Optional
                        </label>
                        @error('optional')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>
        </div>


        <div class="mt-6 flex items-center justify-end gap-x-6">
            <button type="button" wire:click="$dispatch('closeModal')" class="button button-secondary">Cancel</button>
            <button type="submit" class="button">{{ $featureId ? 'Update' : 'Create' }}</button>
        </div>
    </form>
</x-modal>

<x-modal>
    <form wire:submit.prevent="save">
        <div class="space-y-12">
            <h2 class="text-lg font-bold mb-4">{{ $clientId ? 'Edit client' : 'Add client' }}</h2>
            <div class="border-b border-gray-900/10 pb-12">
                <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm/6 font-medium text-gray-900">Company Name</label>
                        <div class="mt-2">
                            <input type="text" name="name" id="name" wire:model="name" autocomplete="honorific-prefix" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                        @error('name')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label for="contact" class="block text-sm/6 font-medium text-gray-900">Contact Name</label>
                        <div class="mt-2">
                            <input type="text" name="contact" id="contact" wire:model="contact" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                        @error('contact')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label for="contact-email" class="block text-sm/6 font-medium text-gray-900">Contact Email</label>
                        <div class="mt-2">
                            <input id="contact-email" name="contact-email" type="email"  wire:model="contactEmail" autocomplete="price" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                        @error('contactEmail')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="contact-phone" class="block text-sm/6 font-medium text-gray-900">Contact Phone</label>
                        <div class="mt-2">
                            <input id="contact-phone" name="contact-phone" type="text"  wire:model="contactPhone" autocomplete="price" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                        @error('contactPhone')<p class="text-warning-600">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>
        </div>


        <div class="mt-6 flex items-center justify-end gap-x-6">
            <button type="button" wire:click="$dispatch('closeModal')" class="button button-secondary">Cancel</button>
            <button type="submit" class="button">{{ $clientId ? 'Update' : 'Create' }}</button>
        </div>
    </form>
</x-modal>

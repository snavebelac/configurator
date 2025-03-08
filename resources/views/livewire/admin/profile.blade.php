<div class="mt-1 max-w-7xl">
    <div class="mb-6 ml-3">
        <h2 class="text-lg font-semibold text-gray-900">Profile</h2>
        <p class="mt-1 text-sm/6 text-gray-600">Update you name and email address</p>
    </div>
    <div class="bg-white px-6 pt-1 pb-6 shadow-sm sm:rounded-lg sm:px-12">
        <form wire:submit="updateUser({{ $userId }})">
            <div class="space-y-12">
                <div class="border-b border-gray-900/10 pb-12">

                    @if (session('profile.updated'))
                        <x-info :message="session('profile.updated')" />
                    @endif

                    <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="first-name" class="block text-sm/6 font-medium text-gray-900">First name</label>
                            <div class="mt-2">
                                <input type="text" name="first-name" id="first-name" wire:model="name" autocomplete="given-name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            </div>
                            @error('name')<p class="text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Last name</label>
                            <div class="mt-2">
                                <input type="text" name="last-name" id="last-name" wire:model="lastName" autocomplete="family-name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            </div>
                            @error('lastName')<p class="text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-4">
                            <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                            <div class="mt-2">
                                <input id="email" name="email" type="email" wire:model="email" autocomplete="email" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            </div>
                            @error('email')<p class="text-red-600">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>
            </div>


            <div class="mt-6 flex items-center justify-end gap-x-6">
                <a href="{{ route('dashboard') }}" class="text-sm/6 font-semibold text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
            </div>
        </form>
    </div>
</div>

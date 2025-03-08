<x-modal>
<form wire:submit.prevent="save">
    <div class="space-y-12">
        <h2 class="text-lg font-bold mb-4">{{ $user ? 'Edit User' : 'Add User' }}</h2>
        <div class="border-b border-gray-900/10 pb-12">
            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                <div class="sm:col-span-3">
                    <label for="first-name" class="block text-sm/6 font-medium text-gray-900">First name</label>
                    <div class="mt-2">
                        <input type="text" name="first-name" id="first-name" wire:model="name" autocomplete="given-name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                    </div>
                    @error('name')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-3">
                    <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Last name</label>
                    <div class="mt-2">
                        <input type="text" name="last-name" id="last-name" wire:model="lastName" autocomplete="family-name" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                    </div>
                    @error('lastName')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-6">
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" wire:model="email" autocomplete="email" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                    </div>
                    @error('email')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-6">
                    <label for="active" class="flex items-center gap-2 text-sm/6 font-medium text-gray-900">
                        <input type="checkbox" name="active" id="active"  wire:model="active" class="bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                        Active
                    </label>
                    @error('active')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-6">
                    <label for="role" class="block text-sm/6 font-medium text-gray-900">Role</label>
                    <div class="mt-2">
                        <select id="role" name="role" type="role" wire:model="role" autocomplete="email" required class="uppercase block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                            <option value="">Please select...</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}"{{ $user && $user->hasRole($role->name) ? ' selected' : '' }}>{{ $role->name }}</option>@endforeach
                        </select>
                    </div>
                    @error('role')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                @if ($user)
                <div class="sm:col-span-6">
                     <p class="text-sm">Leave passwords blank to retain the user's existing password</p>
                </div>
                @endif

                <div class="sm:col-span-6">
                    <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" autocomplete="current-password" wire:model="password" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                    </div>
                    @error('password')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-6">
                    <label for="confirm-password" class="block text-sm/6 font-medium text-gray-900">Confirm Password</label>
                    <div class="mt-2">
                        <input type="password" name="confirm-password" id="confirm-password" autocomplete="current-password" wire:model="password_confirmation" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                    </div>
                    @error('password_confirmation')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>
    </div>


    <div class="mt-6 flex items-center justify-end gap-x-6">
        <button type="button" wire:click="$dispatch('closeModal')" class="rounded-md px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-100">Cancel</button>
        <button type="submit" class="rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">{{ $user ? 'Update' : 'Create' }}</button>
    </div>
</form>
</x-modal>

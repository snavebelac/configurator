<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-logo class="block mx-auto h-12"/>

        <h2 class="mt-6 mb-6 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Enter your new password</h2>
        <div class="text-gray-900">Complete the form below to update your password</div>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
            @if (isset($message))
                <div class="mb-3 text-red-600">{{ $message }}</div>
            @endif
            <form class="space-y-6" wire:submit="updatePassword">
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="email" autocomplete="email" wire:model="email" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                        <input type="hidden" wire:model="token" value="{{ $token }}" />
                    </div>
                    @error('email')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" autocomplete="current-password" wire:model="password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                    </div>
                    @error('password')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="confirm-password" class="block text-sm/6 font-medium text-gray-900">Confirm Password</label>
                    <div class="mt-2">
                        <input type="password" name="confirm-password" id="confirm-password" autocomplete="current-password" wire:model="passwordConfirmation" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                    </div>
                    @error('passwordConfirmation')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-primary-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Update password</button>
                </div>
            </form>

            <div class="flex items-center justify-between mt-6">
                <div class="text-sm/6">
                    <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-500">Login</a>
                </div>
            </div>

        </div>
    </div>

</div>
</div>


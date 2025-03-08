<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-logo class="block mx-auto h-12"/>

        <h2 class="mt-6 mb-6 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Reset you password</h2>
        <div class="text-gray-900">Complete the form below and a password rest link will be sent to you.</div>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-[480px]">
        <div class="bg-white px-6 py-12 shadow-sm sm:rounded-lg sm:px-12">
            <div class="mb-3" wire:loading>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" fill="none" class="size-4 animate-spin">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a9 9 0 0 1 7.794 4.5 9 9 0 0 1 0 9"></path>
                </svg>
            </div>
            @if (isset($errorMessage))
                <div class="mb-3 text-red-600">{{ $errorMessage }}</div>
            @endif
            @if (isset($successMessage))
                <div class="mb-3 text-green-600">{{ $successMessage }}</div>
            @endif
            <form class="space-y-6" wire:submit="resetPassword">
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="email" autocomplete="email" wire:model="email" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 sm:text-sm/6">
                    </div>
                    @error('email')<p class="text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-primary-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Reset password</button>
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

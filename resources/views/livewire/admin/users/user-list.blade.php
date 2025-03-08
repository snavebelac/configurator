{{--<div class="mt-1 max-w-7xl">--}}
{{--        <div class="sm:flex sm:items-center mb-6 mx-3">--}}
{{--            <div class="sm:flex-auto">--}}
{{--                <h1 class="text-lg font-semibold text-gray-900">Users</h1>--}}
{{--                <p class="mt-2 text-sm text-gray-700">A list of all the users in your account including their name, title, email and role.</p>--}}
{{--            </div>--}}
{{--            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">--}}
{{--                <button type="button" wire:click="$dispatch('openModal', {component: 'admin.users.user-modal'})" class="block rounded-md bg-primary-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">--}}
{{--                    Add user--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="bg-white px-6 pb-6 pt-0.5 shadow-sm sm:rounded-lg sm:px-12">--}}
{{--            <div class="mt-8 flow-root">--}}
{{--                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">--}}
{{--                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">--}}
{{--                        <table class="min-w-full divide-y divide-gray-300">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">Active</th>--}}
{{--                                <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">Name</th>--}}
{{--                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>--}}
{{--                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>--}}
{{--                                <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">--}}
{{--                                    <span class="sr-only">Edit</span>--}}
{{--                                </th>--}}
{{--                                <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-3">--}}
{{--                                    <span class="sr-only">Delete</span>--}}
{{--                                </th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                            <tbody class="divide-y divide-gray-200">--}}
{{--                            @forelse ($users as $user)--}}
{{--                            <tr class="even:bg-gray-50 hover:bg-primary-50">--}}
{{--                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">--}}
{{--                                    @if ($user->active)--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-green-800">--}}
{{--                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />--}}
{{--                                        </svg>--}}
{{--                                    @else--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-rose-800">--}}
{{--                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />--}}
{{--                                        </svg>--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3{{ !$user->active ? ' opacity-40' : '' }}">{{ $user->full_name }}</td>--}}
{{--                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500{{ !$user->active ? ' opacity-60' : '' }}">{{ $user->email }}</td>--}}
{{--                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500{{ !$user->active ? ' opacity-60' : '' }}">--}}
{{--                                    <span class="uppercase">--}}
{{--                                        @if (!$user->roles->isEmpty())--}}
{{--                                        {{ $user->roles->pluck('name')->implode(', ') }}--}}
{{--                                        @else--}}
{{--                                        No roles assigned--}}
{{--                                        @endif--}}
{{--                                    </span>--}}
{{--                                </td>--}}
{{--                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">--}}
{{--                                    <button wire:click="$dispatch('openModal', {component: 'admin.users.user-modal', arguments: {userId: {{ $user->id }} }})" class="text-primary-600 hover:text-primary-900">Edit<span class="sr-only">, {{ $user->full_name }}</span></button>--}}
{{--                                </td>--}}
{{--                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">--}}
{{--                                    <button wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you wish to delete [{{ $user->full_name }}]?" class="text-rose-400 hover:text-rose-500">Delete<span class="sr-only">, {{ $user->full_name }}</span></button>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                            @empty--}}
{{--                                <tr>--}}
{{--                                    <td colspan="4" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">No users found</td>--}}
{{--                                </tr>--}}
{{--                            @endforelse--}}
{{--                            <!-- More people... -->--}}
{{--                            </tbody>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--</div>--}}

<div class="mt-1 max-w-7xl">
    <div class="sm:flex sm:items-center px-3">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold text-gray-900">Users</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all the users in your account including their name, email, and role.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <button type="button" wire:click="$dispatch('openModal', {component: 'admin.users.user-modal'})" class="block rounded-md bg-primary-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">Add user</button>
        </div>
    </div>
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden ring-1 shadow-sm ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">Active</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>
                            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                                <span class="sr-only">Edit</span>
                            </th>
                            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                                <span class="sr-only">Delete</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($users as $user)
                            <tr class="even:bg-gray-50 hover:bg-primary-50">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">
                                    @if ($user->active)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-green-800">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-rose-800">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    @endif
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3{{ !$user->active ? ' opacity-40' : '' }}">{{ $user->full_name }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500{{ !$user->active ? ' opacity-60' : '' }}">{{ $user->email }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500{{ !$user->active ? ' opacity-60' : '' }}">
                                    <span class="uppercase">
                                        @if (!$user->roles->isEmpty())
                                            {{ $user->roles->pluck('name')->implode(', ') }}
                                        @else
                                            No roles assigned
                                        @endif
                                    </span>
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                    <button wire:click="$dispatch('openModal', {component: 'admin.users.user-modal', arguments: {userId: {{ $user->id }} }})" class="text-primary-600 hover:text-primary-900">Edit<span class="sr-only">, {{ $user->full_name }}</span></button>
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                                    <button wire:click="delete({{ $user->id }})" wire:confirm="Are you sure you wish to delete [{{ $user->full_name }}]?" class="text-rose-400 hover:text-rose-500">Delete<span class="sr-only">, {{ $user->full_name }}</span></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">No users found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

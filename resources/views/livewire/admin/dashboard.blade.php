<div class="mt-1 max-w-7xl">
    <div class="flex flex-col gap-2 md:flex-row items-start md:items-center px-3">
        <div class="md:flex-auto">
            <h1 class="text-base font-semibold text-gray-900">Dashboard</h1>
        </div>
    </div>
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <h2 class="font-bold text-sm text-gray-600 mt-10 mb-3 ml-3">Drafts</h2>
                <div class="overflow-hidden ring-1 shadow-sm ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-lg max-w-lg">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">By</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Value</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Modified</th>
                            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                                <span class="sr-only">Edit or Preview</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($drafts as $draft)
                            <tr class="even:bg-gray-50 hover:bg-primary-50" wire:key="{{ $draft->id }}">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">
                                    {{ $draft->name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->user->full_name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {!! $draft->total_for_humans !!}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap  text-gray-900 sm:pl-3">
                                    {{ $draft->created_for_humans }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->updated_for_humans }}
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3 flex items-center gap-2 justify-end">
                                    <button class="button">Edit<span class="sr-only">, {{ $draft->name }}</span></button>
                                    <a href="{{ route('dashboard.proposal.preview', ['proposal' => $draft->uuid]) }}" class="button button-secondary">Preview<span class="sr-only">, {{ $draft->name }}</span></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">No draft proposals found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <h2 class="font-bold text-sm text-primary-600 mt-10 mb-3 ml-3">Delivered</h2>
                <div class="overflow-hidden ring-1 shadow-sm ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-lg max-w-lg">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">By</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Value</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Modified</th>
                            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                                <span class="sr-only">Continue</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($delivered as $draft)
                            <tr class="even:bg-gray-50 hover:bg-primary-50" wire:key="{{ $draft->id }}">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">
                                    {{ $draft->name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->user->full_name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {!! $draft->total_for_humans !!}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap  text-gray-900 sm:pl-3">
                                    {{ $draft->created_for_humans }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->updated_for_humans }}
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3 flex items-center gap-2 justify-end">
                                    <button class="button">Edit<span class="sr-only">, {{ $draft->name }}</span></button>
                                    <a href="{{ route('dashboard.proposal.preview', ['proposal' => $draft->uuid]) }}" class="button button-secondary">Preview<span class="sr-only">, {{ $draft->name }}</span></a>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">No draft proposals found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <h2 class="font-bold text-sm text-success-600 mt-10 mb-3 ml-3">Accepted</h2>
                <div class="overflow-hidden ring-1 shadow-sm ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-lg max-w-lg">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">By</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Value</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Modified</th>
                            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                                <span class="sr-only">Continue</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($accepted as $draft)
                            <tr class="even:bg-gray-50 hover:bg-primary-50" wire:key="{{ $draft->id }}">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">
                                    {{ $draft->name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->user->full_name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {!! $draft->total_for_humans !!}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap  text-gray-900 sm:pl-3">
                                    {{ $draft->created_for_humans }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->updated_for_humans }}
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3 flex items-center gap-2 justify-end">
                                    <button class="button">Edit<span class="sr-only">, {{ $draft->name }}</span></button>
                                    <a href="{{ route('dashboard.proposal.preview', ['proposal' => $draft->uuid]) }}" class="button button-secondary">Preview<span class="sr-only">, {{ $draft->name }}</span></a>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">No accepted proposals found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <h2 class="font-bold text-sm text-warning-600 mt-10 mb-3 ml-3">Rejected</h2>
                <div class="overflow-hidden ring-1 shadow-sm ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-lg max-w-lg">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">By</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Value</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Modified</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($rejected as $draft)
                            <tr class="even:bg-gray-50 hover:bg-primary-50" wire:key="{{ $draft->id }}">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">
                                    {{ $draft->name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->user->full_name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {!! $draft->total_for_humans !!}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap  text-gray-900 sm:pl-3">
                                    {{ $draft->created_for_humans }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->updated_for_humans }}
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3 flex items-center gap-2 justify-end">
                                    <button class="button">Edit<span class="sr-only">, {{ $draft->name }}</span></button>
                                    <a href="{{ route('dashboard.proposal.preview', ['proposal' => $draft->uuid]) }}" class="button button-secondary">Preview<span class="sr-only">, {{ $draft->name }}</span></a>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">No rejected proposals found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <h2 class="font-bold text-sm text-gray-600 mt-10 mb-3 ml-3">Archived</h2>
                <div class="overflow-hidden ring-1 shadow-sm ring-black/5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-lg max-w-lg">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">By</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Value</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Created</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Modified</th>
                            <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                                <span class="sr-only">Continue</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($archived as $draft)
                            <tr class="even:bg-gray-50 hover:bg-primary-50" wire:key="{{ $draft->id }}">
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">
                                    {{ $draft->name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->user->full_name }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap  text-gray-900 sm:pl-3">
                                    {{ $draft->created_for_humans }}
                                </td>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                    {{ $draft->updated_for_humans }}
                                </td>
                                <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3 flex items-center gap-2 justify-end">
                                    <button class="button">Edit<span class="sr-only">, {{ $draft->name }}</span></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">No draft proposals found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>




<div class="mt-1 max-w-7xl">
    <div class="sm:flex sm:items-center px-3">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold text-gray-900">Create Proposal</h1>
        </div>
    </div>

    <livewire:admin.shared.progress :stages="$stages" :current-stage="$stage" />

    <div class="mt-8 flow-root px-3">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="flex items-start gap-6">
                    <div class="w-1/4 bg-white rounded-md shadow px-3 py-2">
                        <div class="flex items-center justify-between gap-3 my-3">
                            <h2>Select features to add</h2>
                            <button type="button"
                                    wire:click="$dispatch('openModal', {component: 'admin.features.feature-modal'})"
                                    class="button button-icon button-small">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="2.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex flex-col gap-2">
                            @foreach($features as $feature)
                                <div wire:key="feature_{{ $feature->id }}">
                                    <button wire:click="selectFeature({{ $feature->id }})" type="button"
                                            class="flex w-full items-center justify-between px-3 py-2 text-sm rounded-md bg-gray-50 hover:bg-primary-100 group">
                                        <span class="text-left w-2/3">{{ $feature->name }}</span>
                                        <span>
                                    {!! $feature->price_for_humans !!} / {{ $feature->quantity }}
                                    </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                             class="size-4 group-hover:translate-x-1 transition-all duration-200">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>

                                    </button>
                                </div>
                            @endforeach
                        </div>
                        {{ $features->links() }}
                    </div>
                    <div class="w-3/4">
                        <h2>Selected features</h2>
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    Optional
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Quantity
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    Price
                                </th>
                                <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                                    <span class="sr-only">Remove</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($selectedFeatures as $selectedFeature)
                                <tr class="even:bg-gray-50 hover:bg-primary-50"
                                    wire:key="selected_{{ $selectedFeature->id }}">
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-6">
                                        @if ($selectedFeature->optional)
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor"
                                                 class="size-6 text-primary-600">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                            </svg>
                                        @endif
                                    </td>

                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                        {{ $selectedFeature->name }}
                                    </td>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                        {{ $selectedFeature->quantity }}
                                    </td>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap  text-gray-900 sm:pl-3">
                                        &pound;{{ $selectedFeature->price }}
                                    </td>
                                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3 flex items-center gap-2 justify-end">
                                        <button wire:click="removeFeature({{ $selectedFeature->id }})"
                                                class="button button-warning">X<span
                                                class="sr-only">, Remove {{ $selectedFeature->name }}</span></button>
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="text-right font-bold uppercase"></td>
                                <td class="px-3 py-4 font-bold uppercase">Total</td>
                                <td class="px-3 py-4 text-xl whitespace-nowrap text-gray-800 font-bold">{!! $totalForSelectedFeatures !!}</td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto sm:px-6 lg:px-8 mt-10">
        <a href="{{ route('dashboard') }}" class="button button-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Close preview and return to Dashboard
        </a>
    <div class="mt-4 px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold text-gray-900">{{ $proposal->name }}</h1>
            </div>
        </div>
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                        <tr>
                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-0">Feature</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Quantity</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Price</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Line Total</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @if (!$features->isEmpty())
                            @foreach ($features as $feature)
                                <tr>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">
                                        <h2 class="font-semibold">{{ $feature->name }}</h2>
                                        <div class="text-sm font-gray-600">
                                            {{ $feature->description }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $feature->quantity }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{!! $feature->price_for_humans !!}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">&pound;{{ $feature->quantity * $feature->price }}</td>
                                </tr>
                            @endforeach
                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="text-right font-bold uppercase"></td>
                                <td class="px-3 py-4 font-bold uppercase">Total</td>
                                <td class="px-3 py-4 text-xl whitespace-nowrap text-gray-800 font-bold">{!! $totalForHumans !!}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">
                                    This proposal does not yet have any features
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
